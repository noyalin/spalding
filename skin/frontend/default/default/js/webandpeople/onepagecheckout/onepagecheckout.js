// --- checkout ---
var Checkout = Class.create();
Checkout.prototype = {
    winErrorPopup: false,
    saveNextStep: false,

    initialize: function(urls) {
        this.saveMethodUrl          = urls.saveMethod;
        this.failureUrl             = urls.failure;
        this.billingForm            = false;
        this.shippingForm           = false;
        this.syncBillingShipping    = false;
        this.method                 = '';
        this.payment                = '';
        this.loadWaiting            = false;
    },

    ajaxFailure: function() {
        location.href = this.failureUrl;
    },

    _disableEnableAll: function(element, isDisabled) {
        var descendants = element.descendants();
        for (var k in descendants) {
            descendants[k].disabled = isDisabled;
        }
        element.disabled = isDisabled;
    },

    setLoadWaiting: function(step, keepDisabled) {
        if (step) {
            if (this.loadWaiting) {
                this.setLoadWaiting(false);
            }
            var container = $(step+'-buttons-container');
            container.addClassName('disabled');
            container.setStyle({opacity:.5});
            this._disableEnableAll(container, true);
            Element.show(step+'-please-wait');
        } else {
            if (this.loadWaiting) {
                var container = $(this.loadWaiting+'-buttons-container');
                var isDisabled = (keepDisabled ? true : false);
                if (!isDisabled) {
                    container.removeClassName('disabled');
                    container.setStyle({opacity:1});
                }
                this._disableEnableAll(container, isDisabled);
                Element.hide(this.loadWaiting+'-please-wait');
            }
        }
        this.loadWaiting = step;
    },

    setMethod: function() {
        if ($('login:guest') && $('login:guest').checked)
        {
            this.method = 'guest';
            var request = new Ajax.Request(
                this.saveMethodUrl, {
                    method: 'post',
                    onFailure: this.ajaxFailure.bind(this),
                    parameters: {method:'guest'}
                }
            );
            Element.hide('register-customer-password');
        }
        else if($('login:register') && ($('login:register').checked || $('login:register').type == 'hidden'))
        {
            this.method = 'register';
            var request = new Ajax.Request(
                this.saveMethodUrl, {
                    method: 'post',
                    onFailure: this.ajaxFailure.bind(this),
                    parameters: {method:'register'}
                }
            );
            Element.show('register-customer-password');
        }
        else
        {
            checkout.showErrorMessage(Translator.translate('Please choose to register or to checkout as a guest'));
            return false;
        }
    },

    setBilling: function() {
        if (($('billing:use_for_shipping')) && ($('billing:use_for_shipping').checked) && (typeof shipping != 'undefined'))
        {
            shipping.syncWithBilling();
        }
        else if (($('billing:use_for_shipping')) && (!$('billing:use_for_shipping').checked))
        {
            if ($('shipping:same_as_billing'))
                $('shipping:same_as_billing').checked = false;
        }
        else
        {
            if ($('shipping:same_as_billing'))
                $('shipping:same_as_billing').checked = true;
        }
    },

    showErrorMessage: function(text) {
        if (!this.winErrorPopup)
        {
            this.winErrorPopup = new OnePageCheckoutPopup({
                id: 'popup-onepagecheckout-error',
                html: text,
                title: Translator.translate('Checkout'),
                width: 500
            });
        }
        else
        {
            this.winErrorPopup.setContent(text);
        }
        this.winErrorPopup.open();
    },

    updateInfo: function(url) {
        var updateInfoText = Translator.translate('Updating information, please wait...');
        $$('div.onepagecheckout')[0].insert('<div id="onepagecheckout-update-info" class="updateInfo">' + updateInfoText + '</div>');
        $('review-buttons-container').addClassName('disabled');

        payment.saveFormValues();

        new Ajax.Request(
            url, {
                asynchronous: true,
                method: 'post',
                onFailure: checkout.ajaxFailure.bind(checkout),
                onSuccess: checkout.onUpdateInfo,
                parameters: Form.serialize($('checkoutSteps'))
            }
        );
    },

    onUpdateInfo: function(transport) {
        var updateInfoBlock = $('onepagecheckout-update-info');
        updateInfoBlock.parentNode.removeChild(updateInfoBlock);
        $('review-buttons-container').removeClassName('disabled');

        if (transport && transport.responseText)
        {
            try
            {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e)
            {
                response = {};
            }
        }

        if (response.redirect)
        {
            location.href = response.redirect;
            return;
        }

        if (response.error)
        {
            checkout.showErrorMessage(response.message);
            return;
        }

        if (response.duplicateBillingInfo && (typeof shipping != 'undefined'))
        {
            shipping.setSameAsBilling(true);
        }

        checkout.updateSections(response);

        payment.fillFormValues();
        payment.initWhatIsCvvListeners();

        if (response.message)
        {
            checkout.showErrorMessage(response.message);
        }
    },

    updateSections: function(response) {
        var arrayUpdateSections = null;
        if (Object.prototype.toString.call(response.update_section) == '[object Array]') arrayUpdateSections = response.update_section;
            else arrayUpdateSections = [response.update_section];

        var cnt = arrayUpdateSections.length;
        for (var i=0;i<cnt;i++)
            $(arrayUpdateSections[i].name).update(arrayUpdateSections[i].html);
    }

}

// --- billing ---
var Billing = Class.create();
Billing.prototype = {
    initialize: function(form, addressUrl) {
        this.form = form;
        this.addressUrl = addressUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
    },

    setAddress: function(addressId) {
        if (addressId)
        {
            request = new Ajax.Request(
                this.addressUrl+addressId, {
                    method:'get',
                    onSuccess: this.onAddressLoad,
                    onFailure: checkout.ajaxFailure.bind(checkout)
                }
            );
        }
        else
        {
            this.fillForm(false);
        }
    },

    newAddress: function(isNew) {
        if (isNew)
        {
            this.resetSelectedAddress();
            Element.show('billing-new-address-form');
        }
        else
        {
            Element.hide('billing-new-address-form');
        }
    },

    resetSelectedAddress: function(){
        var selectElement = $('billing-address-select')
        if (selectElement)
        {
            selectElement.value='';
        }
    },

    fillForm: function(transport) {
        var elementValues = {};
        if (transport && transport.responseText)
        {
            try
            {
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e)
            {
                elementValues = {};
            }
        }
        else
        {
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements)
        {
            if (arrElements[elemIndex].id)
            {
                var fieldName = arrElements[elemIndex].id.replace(/^billing:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && billingForm)
                {
                    billingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },

    setUseForShipping: function(flag) {
        $('shipping:same_as_billing').checked = flag;
    },

    validateOnly: function() {
        var validator = new Validation(this.form);
        if (validator.validate())
        {
            if ($('billing:use_for_shipping') && $('billing:use_for_shipping').checked)
            {
                $('billing:use_for_shipping').value = 1;
            }
            return true;
        }
        checkout.saveNextStep = false;
        return;
    }
}

// --- shipping ---
var Shipping = Class.create();
Shipping.prototype = {
    initialize: function(form, addressUrl) {
        this.form = form;
        this.addressUrl = addressUrl;
        this.onAddressLoad = this.fillForm.bindAsEventListener(this);
    },

    setAddress: function(addressId) {
        if (addressId)
        {
            request = new Ajax.Request(
                this.addressUrl+addressId, {
                    method: 'get',
                    onSuccess: this.onAddressLoad,
                    onFailure: checkout.ajaxFailure.bind(checkout)
                }
            );
        }
        else
        {
            this.fillForm(false);
        }
    },

    newAddress: function(isNew) {
        if (isNew)
        {
            this.resetSelectedAddress();
            Element.show('shipping-new-address-form');
        }
        else
        {
            Element.hide('shipping-new-address-form');
        }
        shipping.setSameAsBilling(false);
    },

    resetSelectedAddress: function() {
        var selectElement = $('shipping-address-select')
        if (selectElement)
        {
            selectElement.value='';
        }
    },

    fillForm: function(transport)
    {
        var elementValues = {};
        if (transport && transport.responseText)
        {
            try
            {
                elementValues = eval('(' + transport.responseText + ')');
            }
            catch (e)
            {
                elementValues = {};
            }
        }
        else
        {
            this.resetSelectedAddress();
        }
        arrElements = Form.getElements(this.form);
        for (var elemIndex in arrElements)
        {
            if (arrElements[elemIndex].id)
            {
                var fieldName = arrElements[elemIndex].id.replace(/^shipping:/, '');
                arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
                if (fieldName == 'country_id' && shippingForm)
                {
                    shippingForm.elementChildLoad(arrElements[elemIndex]);
                }
            }
        }
    },

    setSameAsBilling: function(flag) {
        $('shipping:same_as_billing').checked = flag;
        if (flag)
        {
            this.syncWithBilling();
        }
    },

    syncWithBilling: function () {
        $('billing-address-select') && this.newAddress(!$('billing-address-select').value);
        $('shipping:same_as_billing').checked = true;
        if (!$('billing-address-select') || !$('billing-address-select').value)
        {
            arrElements = Form.getElements(this.form);
            for (var elemIndex in arrElements)
            {
                if (arrElements[elemIndex].id)
                {
                    var sourceField = $(arrElements[elemIndex].id.replace(/^shipping:/, 'billing:'));
                    if (sourceField)
                    {
                        arrElements[elemIndex].value = sourceField.value;
                    }
                }
            }
            shippingRegionUpdater.update();
            $('shipping:region_id').value = $('billing:region_id').value;
            $('shipping:region').value = $('billing:region').value;
        }
        else
        {
            $('shipping-address-select').value = $('billing-address-select').value;
        }
    },

    setRegionValue: function() {
        $('shipping:region').value = $('billing:region').value;
    },

    validateOnly: function() {
        var validator = new Validation(this.form);
        if (validator.validate()) {
            return true;
        }
        return;
    }
}

// --- shipping method ---
var ShippingMethod = Class.create();
ShippingMethod.prototype = {
    initialize: function(form) {
        this.form = form;
        this.validator = new Validation(this.form);
    },

    validate: function() {
        var methods = document.getElementsByName('shipping_method');
        if (methods.length==0)
        {
            checkout.showErrorMessage(Translator.translate('Your order cannot be completed at this time as there is no shipping methods available for it. Please make necessary changes in your shipping address.'));
            return false;
        }

        if (!this.validator.validate())
        {
            return false;
        }

        for (var i=0; i<methods.length; i++)
        {
            if (methods[i].checked)
            {
                return true;
            }
        }
        checkout.showErrorMessage(Translator.translate('Please specify shipping method.'));
        return false;
    },

    validateOnly: function() {
        if (this.validate())
        {
            return true;
        }
        return;
    }
}


// --- payment ---
var Payment = Class.create();
Payment.prototype = {
    beforeInitFunc:$H({}),
    afterInitFunc:$H({}),
    beforeValidateFunc:$H({}),
    afterValidateFunc:$H({}),
    data : false,

    initialize: function(form) {
        this.form = form;
    },

    addBeforeInitFunction : function(code, func) {
        this.beforeInitFunc.set(code, func);
    },

    beforeInit : function() {
        (this.beforeInitFunc).each(function(init){
           (init.value)();;
        });
    },

    init : function () {
        this.beforeInit();
        var elements = Form.getElements(this.form);
        if ($(this.form)) // ???
        {
            $(this.form).observe('submit', function(event){this.save();Event.stop(event);}.bind(this));
        }
        var method = null;
        for (var i=0; i<elements.length; i++)
        {
            if (elements[i].name=='payment[method]')
            {
                if (elements[i].checked)
                {
                    method = elements[i].value;
                }
            }
            else
            {
                elements[i].disabled = true;
            }
            elements[i].setAttribute('autocomplete','off');
        }
        if (method) this.switchMethod(method);
        this.afterInit();
    },

    addAfterInitFunction : function(code, func) {
        this.afterInitFunc.set(code, func);
    },

    afterInit : function() {
        (this.afterInitFunc).each(function(init) {
            (init.value)();
        });
    },

    switchMethod: function(method) {
        if (this.currentMethod && $('payment_form_'+this.currentMethod))
        {
            this.changeVisible(this.currentMethod, true);
        }
        if ($('payment_form_'+method))
        {
            this.changeVisible(method, false);
            $('payment_form_'+method).fire('payment-method:switched', {method_code : method});
        }
        else
        {
            // --- Event fix for payment methods without form like "Check / Money order" ---
            document.body.fire('payment-method:switched', {method_code : method});
        }
        this.currentMethod = method;
    },

    changeVisible: function(method, mode) {
        var block = 'payment_form_' + method;
        [block + '_before', block, block + '_after'].each(function(el) {
            element = $(el);
            if (element)
            {
                element.style.display = (mode) ? 'none' : '';
                element.select('input', 'select', 'textarea').each(function(field) {
                    field.disabled = mode;
                });
            }
        });
    },

    addBeforeValidateFunction : function(code, func) {
        this.beforeValidateFunc.set(code, func);
    },

    beforeValidate : function() {
        var validateResult = true;
        var hasValidation = false;
        (this.beforeValidateFunc).each(function(validate) {
            hasValidation = true;
            if ((validate.value)() == false)
            {
                validateResult = false;
            }
        }.bind(this));
        if (!hasValidation)
        {
            validateResult = false;
        }
        return validateResult;
    },

    validate: function() {
        var result = this.beforeValidate();
        if (result)
        {
            return true;
        }
        var methods = document.getElementsByName('payment[method]');
        if (methods.length==0)
        {
            checkout.showErrorMessage(Translator.translate('Your order cannot be completed at this time as there is no payment methods available for it.'));
            return false;
        }
        for (var i=0; i<methods.length; i++)
        {
            if (methods[i].checked)
            {
                return true;
            }
        }
        result = this.afterValidate();
        if (result)
        {
            return true;
        }
        checkout.showErrorMessage(Translator.translate('Please specify payment method.'));
        return false;
    },

    addAfterValidateFunction : function(code, func) {
        this.afterValidateFunc.set(code, func);
    },

    afterValidate : function() {
        var validateResult = true;
        var hasValidation = false;
        (this.afterValidateFunc).each(function(validate){
            hasValidation = true;
            if ((validate.value)() == false)
            {
                validateResult = false;
            }
        }.bind(this));
        if (!hasValidation)
        {
            validateResult = false;
        }
        return validateResult;
    },

    validateOnly: function() {
        var validator = new Validation(this.form);
        if (this.validate() && validator.validate())
        {
            return true;
        }
        return;
    },

    initWhatIsCvvListeners: function() {
        $$('.cvv-what-is-this').each(function(element) {
            Event.observe(element, 'click', toggleToolTip);
        });
    },

    fillFormValues: function() {
        if (this.data && this.form)
        {
            var xForm = this.form;
            $H(this.data.toQueryParams()).each(function(pair) {
                try
                {
                    $(xForm)[pair.key].setValue(pair.value);
                }
                catch (e)
                {
                    // ---
                }
            });
        }
    },

    saveFormValues: function() {
        if (this.form)
        {
            this.data = Form.serialize(this.form);
        }
    }
}

var Review = Class.create();
Review.prototype = {
    initialize: function(form, saveUrl, successUrl) {
        this.form = form;
        this.saveUrl = saveUrl;
        this.successUrl = successUrl;
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = null;
    },

    validateAll: function() {
        checkout.setBilling();

        if ($('discount-coupon-form'))
        {
            var validator = new Validation('discount-coupon-form');
            validator.reset();
        }
        var validator = new Validation(billing.form);
        validator.reset();
        if (typeof shipping != 'undefined')
        {
            var validator = new Validation(shipping.form);
            validator.reset();
        }
        if (typeof shippingMethod != 'undefined')
        {
            var validator = new Validation(shippingMethod.form);
            validator.reset();
        }
        var validator = new Validation(payment.form);
        validator.reset();

        if (!billing.validateOnly()) return;
        if ((typeof shipping != 'undefined') && !shipping.validateOnly()) return;
        if (!checkout.saveNextStep) return;
        if ((typeof shippingMethod != 'undefined') && !shippingMethod.validateOnly()) return;
        if (!payment.validateOnly()) return;

        return true;
    },

    save: function() {
        if (checkout.loadWaiting != false) return;
        checkout.setLoadWaiting('review');
        checkout.saveNextStep = true;
        if (this.validateAll() && checkout.saveNextStep)
        {
            var params = Form.serialize(this.form);
            params.save = true;
            new Ajax.Request(
                this.saveUrl, {
                    asynchronous: true,
                    method: 'post',
                    parameters: params,
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout)
                }
            );
        }
        else
        {
            checkout.setLoadWaiting(false);
        }
    },

    nextStep: function(transport) {
        if (transport && transport.responseText)
        {
            try
            {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e)
            {
                response = {};
            }

            if (response.redirect)
            {
                location.href = response.redirect;
                return;
            }

            if (response.success)
            {
                this.isSuccess = true;
                window.location = this.successUrl;
                return;
            }
            else
            {
                var msg = response.error_messages;
                if (typeof(msg)=='object') msg = msg.join("\n");
                if (msg)
                {
                    checkout.showErrorMessage(msg);
                    checkout.setLoadWaiting(false);
                    return;
                }
            }

            if (response.error)
            {
                if ((typeof response.message) == 'string')
                {
                    checkout.showErrorMessage(response.message);
                }
                else
                {
                    checkout.showErrorMessage(response.message.join("\n"));
                }
                checkout.setLoadWaiting(false);
                return;
            }

            checkout.updateSections(response);
        }
    },

    isSuccess: false
}

OnePageCheckoutPopup = Class.create({
    id       : false,
    title    : false,
    popup    : false,
    popupObj : false,

    initialize:function(data) {
        this.id = data.id;
        this.title = data.title;
        winPopup = $(this.id);

        if (!winPopup){
            popupHtml = '' +
            '<div id="' + this.id + '" class="wp-one-page-checkout-popup opc">' +
                '<div class="section active">' +
                    '<div class="step-title">' +
                        '<h2>' + this.title + '</h2>' +
                        '<span id="' + this.id + '_btn_close" class="number"></span>' +
                    '</div>' +
                    '<div class="step a-item" id="' + this.id + '_content_html">' +
                        data.html +
                    '</div>' +
                '</div>' +
            '</div>';

            $$('body')[0].insert(popupHtml);

            var winPopup = $(this.id);
        }

        winPopup.style.position = 'absolute';
        winPopup.style.display = 'block';
        if (typeof data.width != 'undefined') winPopup.style.width = data.width + 'px';
        if (typeof data.height != 'undefined') winPopup.style.height = data.height + 'px';

        this.popup = winPopup;

        this.setPosition();

        $(this.id + '_btn_close').onclick = function(){ this.close(); }.bind(this);

        var overlay = this.showOverlay();
        overlay.onclick = function(){ this.close(); }.bind(this);
    },

    open: function() {
        var overlay = this.showOverlay();
        overlay.onclick = function(){ this.close(); }.bind(this);

        this.setPosition();

        this.popup.show();
    },

    close: function() {
        this.hideOverlay();

        this.popup.hide();
    },

    setPosition: function() {
        this.popup.style.display = 'block';

        var left    = this.popup.offsetWidth / 2;
        var top     = this.getClientHeight() / 2 + this.getScrollY() - 170;

        this.popup.style.left = '50%';
        this.popup.style.marginLeft = '-' + left + 'px';
        this.popup.style.top  = top +'px';
    },

    setContent: function(html) {
        $(this.id + '_content_html').update(html);
    },

    showOverlay:function() {
        var overlay = $('onepagecheckout-x-overlay');

        if (overlay)
        {
            overlay.show();
        }
        else
        {
            overlay = document.createElement('div');
            overlay.id = 'onepagecheckout-x-overlay';
            overlay.className = 'onepagecheckout-overlay';
            document.body.appendChild(overlay);
        }
        return overlay;
    },

    hideOverlay:function() {
        var overlay = $('onepagecheckout-x-overlay');
        if (overlay) overlay.hide();
    },

    getClientHeight: function() {
        return this.filterResults (
            window.innerHeight ? window.innerHeight : 0,
            document.documentElement ? document.documentElement.clientHeight : 0,
            document.body ? document.body.clientHeight : 0
        );
    },

    filterResults: function(n_win, n_docel, n_body) {
        var n_result = n_win ? n_win : 0;
        if (n_docel && (!n_result || (n_result > n_docel))) n_result = n_docel;
        return n_body && (!n_result || (n_result > n_body)) ? n_body : n_result;
    },

    getScrollY: function() {
        scrollY = 0;
        if (typeof window.pageYOffset == "number")
        {
            scrollY = window.pageYOffset;
        }
        else if (document.documentElement && document.documentElement.scrollTop)
        {
            scrollY = document.documentElement.scrollTop;
        }
        else if (document.body && document.body.scrollTop)
        {
            scrollY = document.body.scrollTop;
        }
        else if (window.scrollY)
        {
            scrollY = window.scrollY;
        }
        return scrollY;
    }
});
