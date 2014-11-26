<?php
$installer = $this;

$installer->startSetup();

mage :: log ("weixin status");
$status = Mage::getModel('sales/order_status');
//担保交易 交易创建 等待买家付款
$status->setStatus('weixin_wait_buyer_pay')->setLabel('WEIXIN WAIT BUYER PAY')
    ->assignState(Mage_Sales_Model_Order::STATE_NEW) //for example, use any available existing state
    ->save();
    

$status = Mage::getModel('sales/order_status');
//担保交易 买家付款成功,等待卖家发货
$status->setStatus('weixin_wait_seller_send_goods')->setLabel('WEIXIN WAIT SELLER SEND GOODS')
    ->assignState(Mage_Sales_Model_Order::STATE_NEW) //for example, use any available existing state
    ->save();



    
$status = Mage::getModel('sales/order_status');
//担保交易 退款状态-退款关闭
$status->setStatus('weixin_trade_success')->setLabel('WEIXIN TRADE SUCCESS')
    ->assignState(Mage_Sales_Model_Order::STATE_NEW) //for example, use any available existing state
    ->save();
    
$status = Mage::getModel('sales/order_status');
//担保交易 退款状态-退款关闭
$status->setStatus('weixin_trade_finished')->setLabel('WEIXIN TRADE FINISHED')
    ->assignState(Mage_Sales_Model_Order::STATE_NEW) //for example, use any available existing state
    ->save();
    
    
    
$installer->endSetup();
