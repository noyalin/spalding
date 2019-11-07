<?php

class Cobra_CustomMade_Model_Info extends Mage_Core_Model_Abstract
{
    const STATUS_NON_PAYMENT = 0;
    const STATUS_APPROVING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_NOTAPPROVED = 3;
    const STATUS_CANCEL = 4;
    const STATUS_EXPORT = 5;

    protected function _construct()
    {
        $this->_init('custommade/info');
    }

    public function nonpayment()
    {
        $this->setStatus(self::STATUS_NON_PAYMENT)->save();
    }

    public function approving()
    {
        if (!$this->checkCustomByOrderId($this->getOrderId(), $this->getSku())) {
            Mage::log('approving Error : id=' . $this->getId() . ', order_id=' . $this->getOrderId() . ', SKU=' . $this->getSku());
            return;
        }
        if (intval($this->getStatus()) === self::STATUS_NON_PAYMENT) {
            $this->setStatus(self::STATUS_APPROVING)
                ->setUser1Approve(0)
                ->setUser2Approve(0)
                ->setUser3Approve(0)
                ->setUser4Approve(0)
                ->setUser1Reason(null)
                ->setUser2Reason(null)
                ->setUser3Reason(null)
                ->setUser4Reason(null)
                ->save();
        } else {
            Mage::log('custommade approving Error : Order_id=' . $this->getOrderId() . 'Status : ' .  $this->getStatus() . " -> Approving" );
        }
    }

    public function approved()
    {
        if (!$this->checkCustomByOrderId($this->getOrderId(), $this->getSku())) {
            Mage::log('approved Error : id=' . $this->getId() . ', order_id=' . $this->getOrderId() . ', SKU=' . $this->getSku());
            return;
        }
        $this->setStatus(self::STATUS_APPROVED)->save();
    }
    public function autoApproved()
    {
        if (!$this->checkCustomByOrderId($this->getOrderId(), $this->getSku())) {
            Mage::log('approved Error : id=' . $this->getId() . ', order_id=' . $this->getOrderId() . ', SKU=' . $this->getSku());
            return;
        }
        $this->setStatus(2)//2.审批通过
            ->setUser2Approve(1)//1.审批通过
            ->save();
    }
    public function OrderAutoApprove($satus1, $satus2)
    {
        $items = $this->getResource()
            ->OrderAutoApprove($satus1, $satus2);
        return $items;
    }

    public function notapproved()
    {
        $this->setStatus(self::STATUS_NOTAPPROVED)->save();
    }

    public function cancel()
    {
        $this->setStatus(self::STATUS_CANCEL)->save();
    }

    public function export()
    {
        $this->setStatus(self::STATUS_EXPORT)->save();
    }

    public function loadByIncrementId($incrementId)
    {
        $id = $this->getResource()
            ->loadByField('order_id', $incrementId);
        return $this->load($id);
    }

    public function loadByConditions($time, $status)
    {
        $items = $this->getResource()
            ->loadByConditions($time, $status);
        return $items;
    }

    public function loadByConditions2($time,$status,$user1_approve,$user2_approve)
{
    $items = $this->getResource()
        ->loadByConditions2($time,$status,$user1_approve,$user2_approve);
    return $items;
}

    public function loadByTime($time1, $time2, $status)
    {
        $items = $this->getResource()
            ->loadByTime($time1, $time2, $status);
        return $items;
    }

    public function saveCustomMade($order)
    {
        Mage::log("saveCustomMade before ".$order->getRealOrderId());
        $orderId = $order->getRealOrderId();
        $customerId = $order->getCustomerId();
        $customMsg = Mage::getModel('custommade/temp')->loadByCustomerId($customerId);

        $path = Mage::getBaseDir() . '/media/custommade/production/'.$orderId;
        if(file_exists($path)){
        	Mage::log('order path exists:'.$path);
        	return;
        }

        if($customMsg){
        	if (!$customMsg->getId()) {
        		return;
        	}
        }else{
        	return;
        }

        $new_sku = $this->transformSku($customMsg);
        if (!$new_sku) {
            Mage::log('saveCustomMade Error : new_sku=' . $new_sku);
            return;
        }

        if (!$this->checkCustomByOrderId($orderId, $new_sku)) {
            Mage::log('saveCustomMade Error : id=NewId, order_id=' . $orderId . ', SKU=' . $new_sku);
            return;
        }

        // 如果订单存在则不保存
        $infoOrder = Mage::getModel('custommade/info')->loadByIncrementId($orderId);
        if ($infoOrder->getId()) {
            Mage::log('saveCustomMade Error : order is exist, order_id=' . $orderId . ', SKU=' . $new_sku);
            return;
        }

        try {
            if ($customMsg->getTypeP1() != null ||
                $customMsg->getTypeP2() != null
            ) {
            	//将tmp中的图片转到正式文件夹
            	$viewUrl = Mage::getBaseUrl() . 'media/custommade/production/'.$orderId .'/';
            	if(!file_exists($path)){
            		$this->moveImagePath($path,$customMsg,$viewUrl);
            	}
                //保存定制信息
                Mage::getModel('custommade/info')->setOrderId($orderId)
                    ->setSku($new_sku)
                    ->setTypeP1($customMsg->getTypeP1())
                    ->setMsg1P1($customMsg->getMsg1P1())
                    ->setMsg2P1($customMsg->getMsg2P1())
                    ->setMsg3P1($customMsg->getMsg3P1())
                    ->setMsg4P1($customMsg->getMsg4P1())
                    ->setMsg5P1($this->createP1Url($customMsg->getTypeP1(), $customMsg->getMsg1P1(), $customMsg->getMsg2P1(), $customMsg->getMsg3P1(), $customMsg->getMsg4P1(), 'show', $customMsg->getSku()))
                    ->setMsg6P1($this->createP1Url($customMsg->getTypeP1(), $customMsg->getMsg1P1(), $customMsg->getMsg2P1(), $customMsg->getMsg3P1(), $customMsg->getMsg4P1(), 'print', $customMsg->getSku()))
                    ->setTypeP2($customMsg->getTypeP2())
                    ->setMsg1P2($customMsg->getMsg1P2())
                    ->setMsg2P2($customMsg->getMsg2P2())
                    ->setMsg3P2($customMsg->getMsg3P2())
                    ->setMsg4P2($customMsg->getMsg4P2())
                    ->setMsg5P2($this->createP2Url($customMsg->getTypeP2(), $customMsg->getMsg1P2(), $customMsg->getMsg2P2(), $customMsg->getMsg3P2(), $customMsg->getMsg4P2(), 'show', $customMsg->getSku()))
                    ->setMsg6P2($this->createP2Url($customMsg->getTypeP2(), $customMsg->getMsg1P2(), $customMsg->getMsg2P2(), $customMsg->getMsg3P2(), $customMsg->getMsg4P2(), 'print', $customMsg->getSku()))
                    ->setCreateTime(time())
                    ->setStatus(self::STATUS_NON_PAYMENT)
                    ->save();
                Mage::getModel('custommade/temp')->deleteByCustomerId($customerId);
                Mage::log('saveCustomMade------customerId=' . $customerId . ',order id=' . $orderId);
                return;
            } else {
                Mage::log('saveCustomMade Error ( P1 P2 ALL NULL): id=NewId, order_id=' . $orderId . ', SKU=' . $new_sku);
            }

        } catch (Exception $e) {
            Mage::log('Save CustomMade Error,Order Id=' . $orderId . ',Customer Id=' . $customerId);
            Mage::log($e->getMessage());

        }
    }

    public function moveImagePath($path,&$customMsg,$viewUrl){
		if($customMsg->getTypeP1() == 1 || $customMsg->getTypeP2() == 1){
			mkdir($path);
			$path .= '/';
		}
    	$oldPath = Mage::getBaseDir() . '/media/custommade/tmp/';
    	if($customMsg->getTypeP1() == 1){
    		$this->moveImage($customMsg,$oldPath,$path,$viewUrl,1);
    	}
    	if($customMsg->getTypeP2() == 1){
    		$this->moveImage($customMsg,$oldPath,$path,$viewUrl,2);
    	}
    }

    public function moveImage(&$customMsg,$oldPath,$newPath,$viewUrl,$switchPage){
    	if($switchPage == 1){
    		$imagePath = $customMsg->getMsg1P1();
    	}elseif($switchPage == 2){
    		$imagePath = $customMsg->getMsg1P2();
    	}
    	//分别对应数据库中的
    	//第一面  effect => msg1_p1 show => msg2_p1 print => msg3_p1
    	//第二面  effect => msg1_p2 show => msg2_p2 print => msg3_p2
     	$typeArray = array('effect','show','print','original');
    	$imageArray = explode('/', $imagePath);
    	$image = end($imageArray);
    	$imageTmp = explode('-', $image);
    	$imagePrex = $imageTmp[0];
    	foreach ($typeArray as $key => $type){
    		$image = $imagePrex."-".$type.".jpg";
    		$nPath = $newPath . $image;
    		$oPath = $oldPath . $image;
    		$vPath = $viewUrl . $image;
    		rename($oPath, $nPath);
    		if($switchPage == 1){
    			if($type == 'effect'){
    				$customMsg->setMsg1P1($vPath);
    			}elseif($type == 'show'){
    				$customMsg->setMsg2P1($vPath);
    			}elseif($type == 'print'){
    				$customMsg->setMsg3P1($vPath);
    			}
    		}elseif($switchPage == 2){
    			if($type == 'effect'){
    				$customMsg->setMsg1P2($vPath);
    			}elseif($type == 'show'){
    				$customMsg->setMsg2P2($vPath);
    			}elseif($type == 'print'){
    				$customMsg->setMsg3P2($vPath);
    			}
    		}
    	}
    }

    //定制文字type=2时：content1是文字1， content2是文字2（双行文字才会有），content3是字体类型1小、2中、3大、4小双， content4是字体3宋体4楷体，p5是预览图，p6是打印图
    //定制队徽type=4时：content1是队徽名字，content5是预览图，content6是打印图
    public function createP1Url($typeP1, $content1P1, $content2P1, $content3P1, $content4P1, $imgType, $sku)
    {
        $url = null;
        switch ($typeP1) {
            case 1:
                if ($imgType == 'show') {
                    $url = $content2P1;
                } else {
                    $url = $content3P1;
                }
                return $url;
            case 2:
                switch ($content4P1) {
                    case 2:
                        $font = '-arial';
                        break;
                    case 3:
                        $font = '-heiti';
                        break;
                    case 4:
                        $font = '-simkai';
                        break;
                    default:
                }

                if ($content3P1 == 1) {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p1-small-one'.$font.'?$1980pxx544px$'.'&$text=' . urlencode($content1P1);
                }
                else if ($content3P1 == 2) {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p1-middle'.$font.'?$1980pxx544px$'.'&$text=' . urlencode($content1P1);
                }
                else if ($content3P1 == 3) {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p1-big'.$font.'?$1980pxx544px$'.'&$text=' . urlencode($content1P1);
                }
                else if ($content3P1 == 4) {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p1-small-two'.$font.'?$1980pxx544px$'.'&$text1=' . urlencode($content1P1).'&$text2=' . urlencode($content2P1);
                }
                else {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p1-small-one'.$font.'?$1980pxx544px$'.'&$text=' . urlencode($content1P1);
                }

                break;
            case 4:
                $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p1-logo?$1980pxx544px$=&$logo=sneakerhead/80pxx544px$=&$logo=sneakerhead/'.$content1P1;
                break;
            default;
                return $url;
        }

        return $url;

    /*
        $url = null;
        switch ($typeP1) {
            case 1:
                if ($imgType == 'show') {
                    $url = $content2P1;
                } else {
                    $url = $content3P1;
                }
                return $url;
            case 2:
                if ($content4P1 == 3 || $content4P1 == 4) {
                    if ($sku == '74-604yc') {
                        $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-74-604-';
                    } else {
                        $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-74-602-';
                    }
                } else {
                    if ($sku == '74-604yc') {
                        $url = 'http://s7d5.scene7.com/is/image/sneakerhead/74-604-spalding-';
                    } else {
                        $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-';
                    }
                }
                break;
            default;
                return $url;
        }

        switch ($content4P1) {
            case 2:
                $imgType .= '-arial';
                break;
            case 3:
                $imgType .= '-simsun';
                break;
            case 4:
                $imgType .= '-simkai';
                break;
            default:
        }

        switch ($content3P1) {
            case 1:
                $url .= 'p1-small_one-' . $imgType . '?$1980pxx544px$&$textone=' . urlencode($content1P1);
                break;
            case 2:
                $url .= 'p1-middle-' . $imgType . '?$1980pxx544px$&$text=' . urlencode($content1P1);
                break;
            case 3:
                $url .= 'p1-big-' . $imgType . '?$1980pxx544px$&$text=' . urlencode($content1P1);
                break;
            case 4:
                $url .= 'p1-small_two-' . $imgType . '?$1980pxx544px$&$texttwo=' . urlencode($content2P1) . '&$textone=' . urlencode($content1P1);
                break;
            default:
        }
        return $url;
        */
    }

    //定制文字type=2时：p1是文字1， p2是文字2（双行文字才会有），p3是字体类型1小单、2中、3大、4小双， p4是字体3宋体4楷体，p5是预览图，p6是打印图
    //定制队徽type=4时：p1是队徽名字，p5是预览图，p6是打印图
    public function createP2Url($typeP2, $content1P2, $content2P2, $content3P2, $content4P2, $imgType, $sku)
    {
        $url = null;
        switch ($typeP2) {
            case 1:
                if ($imgType == 'show') {
                    $url = $content2P2;
                } else {
                    $url = $content3P2;
                }
                return $url;
            case 2:
                switch ($content4P2) {
                    case 2:
                        $font = '-arial';
                        break;
                    case 3:
                        $font = '-heiti';
                        break;
                    case 4:
                        $font = '-simkai';
                        break;
                    default:
                }

                if ($content3P2 == 1) {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p2-small-one'.$font.'?$1980pxx544px$'.'&$text=' . urlencode($content1P2);
                }
                else if ($content3P2 == 2) {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p2-middle'.$font.'?$1980pxx544px$'.'&$text=' . urlencode($content1P2);
                }
                else if ($content3P2 == 3) {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p2-big'.$font.'?$1980pxx544px$'.'&$text=' . urlencode($content1P2);
                }
                else if ($content3P2 == 4) {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p2-small-two'.$font.'?$1980pxx544px$'.'&$text1=' . urlencode($content1P2).'&$text2=' . urlencode($content2P2);
                }
                else {
                    $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p2-small-one'.$font.'?$1980pxx544px$'.'&$text=' . urlencode($content1P2);
                }

                break;
            case 4:
                $url = 'http://s7d5.scene7.com/is/image/sneakerhead/spalding-p2-logo?$1980pxx544px$=&$logo=sneakerhead/'.$content1P2;
                break;
            default;
                return $url;
        }

        return $url;
    }

    private function clearSession($customerId, $sku)
    {
        $session = Mage::getModel('custommade/session');
        $sessionId = $session->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('sku', $sku)
            ->getFirstItem()
            ->getSessionId();
        $session->load($sessionId)->delete();

        $item = $session->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->getFirstItem();
        if ($item->getData() == null) {
            Mage::getSingleton('core/session')->setCustomerId(null);
            Mage::getModel('custommade/customer')->load($customerId)->delete();
        }
        Mage::getSingleton('core/session')->setCustomermadeAgree(null);
    }

    private function checkCustomByOrderId($orderId, $customSku)
    {
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($orderId);
        if (!$order) {
            return false;
        }
        $_items = $order->getItemsCollection();
        foreach ($_items as $_item) {
            if ($_item->getProductType() == 'simple') {
                if ($_item->getSku() == $customSku) {
                    return true;
                }
            }
        }
        return false;
    }

    private function checkCustomByOrder($order, $customSku)
    {
        if (!$order) {
            return false;
        }
        $_items = $order->getItemsCollection();
        foreach ($_items as $_item) {
            if ($_item->getProductType() == 'simple') {
                if ($_item->getSku() == $customSku) {
                    return true;
                }
            }
        }
        return false;
    }

    private function transformSku($customMsg)
    {
        $sub_sku = $customMsg->getSubSku();
        if ($sub_sku) {
            return $customMsg->getSku() . $sub_sku;
        } else {
            Mage::log('transformSku Error : sub_sku == null');
            return false;
        }
    }

    public function getMsg4PositionResult($customMsg,$position = 1){
        $fontArray = array(0 => 'NBA字体',2 => 'Aril',3 => '字体一',4 => '字体二');
        if(trim($customMsg->getMsg4P1()) != '' && $position == 1){
            return $fontArray[$customMsg->getMsg4P1()];
        }
        if(trim($customMsg->getMsg4P2()) != '' && $position == 2){
            return $fontArray[$customMsg->getMsg4P2()];
        }
    }

    public function getMsg4SizeResult($customMsg,$position = 1){
        $sizeArray = array(1 => '小号单行',2 => '中号',3 => '大号',4 => '小号双行');
        if(trim($customMsg->getMsg3P1()) != '' && $position == 1){
            return $sizeArray[$customMsg->getMsg3P1()];
        }
        if(trim($customMsg->getMsg3P2()) != '' && $position == 2){
            return $sizeArray[$customMsg->getMsg3P2()];
        }
    }

}
