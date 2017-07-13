<?php

class Cobra_CustomClothes_Model_Order extends Mage_Core_Model_Abstract
{
    const STATUS_NON_PAYMENT = 1;
    const STATUS_ALREADY_PAY = 2;
    const STATUS_EXPORT = 3;
    const STATUS_CANCEL = 4;
    const STATUS_ERROR = 5;

    protected function _construct()
    {
        $this->_init('customclothes/order');
    }

    public function loadByIncrementId($incrementId)
    {
        $id = $this->getResource()
            ->loadByField('order_id', $incrementId);
        if($id){
        	return $this->load($id);
        }else{
        	return array();
        }
        
    }
    
    public function saveCustomClothes($order)
    {
		Mage::getSingleton('core/session')->setCustomerClothesAgree(null);

        Mage::log("saveCustomClothes before ".$order->getRealOrderId());
        
        $orderId = $order->getRealOrderId();
		
        $customerId = $order->getCustomerId();
        
        $mainData = Mage::getModel('customclothes/temp')->getDataByCustomerId($customerId);
        $secondData = Mage::getModel('customclothes/tempInfo')->getDataByCustomerId($customerId);
        
        if(!$mainData || !$secondData){
        	return;
        }
        
        $skuArray = array();
        foreach ($secondData as $data){
        	if(trim($data['clothes_sku'])){
        		$skuArray[] = $data['clothes_sku'];
        	}
        	if(trim($data['pants_sku'])){
        		$skuArray[] = $data['pants_sku'];
        	}
        }
        
        if(!$skuArray){
        	Mage::log('saveCustomClothes Error sku is not exist in database, order_id=' . $orderId);
        	return;
        }
        
        if (!$this->checkSkuByOrderId($orderId,$skuArray)) {
            Mage::log('saveCustomClothes Error : id=NewId, order_id=' . $orderId);
            return;
        }

        // 如果订单存在则不保存
        $orderInfo = Mage::getModel('customclothes/order')->loadByIncrementId($orderId);
        if ($orderInfo) {
            Mage::log('saveCustomClothes Error : order is exist, order_id=' . $orderId);
            return;
        }
        try {
        	if ($mainData['result_image']){
        		$data = $mainData;
        		unset($data['id']);
        		$data['order_id'] = $orderId;
        		$data['status'] = self::STATUS_NON_PAYMENT;
        		$data['create_time'] = time();
        		$data['update_time'] = $data['create_time'];
        		$orderModel = Mage::getModel('customclothes/order');
        		$orderModel->setData($data);
        		$orderModel->save();
        		
        		$orderInfoModel = Mage::getModel('customclothes/orderInfo');
        		foreach ($secondData as $sData){
        			unset($sData['id']);
        			$sData['create_time'] = time();
        			$sData['order_id'] = $orderId;
        			$orderInfoModel->setData($sData);
        			$orderInfoModel->save();
        		}
        		
        		//按照userId 删除临时表中的定制内容
        		$tempModel = Mage::getModel('customclothes/temp');
        		$tempModel->deleteByCustomerId($customerId);
        		
        		$tempInfoModel = Mage::getModel('customclothes/tempInfo');
        		$tempInfoModel->deleteByCustomerId($customerId);

        		Mage::log('saveCustomClothes------customerId=' . $customerId . ',order id=' . $orderId);
        		return;
        	} else {
        		Mage::log('saveCustomMade Error ( Image URL NULL): id=NewId, order_id=' . $orderId);
        	}

        } catch (Exception $e) {
            Mage::log('Save CustomClothes Error,Order Id=' . $orderId . ',Customer Id=' . $customerId);
            Mage::log($e->getMessage());

        }
    }

    private function checkSkuByOrderId($orderId, $skuArray)
    {
    	$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
    	if (!$order) {
    		return false;
    	}
    	$_items = $order->getItemsCollection();
    	$orderSkuArray = array();
    	foreach ($_items as $_item) {
    		if ($_item->getProductType() == 'simple') {
    			$orderSkuArray[] = $_item->getSku();
    		}
    	}
    	
    	foreach ($skuArray as $sku){
    		if(!in_array($sku, $orderSkuArray)){
    			return false;
    		}
    	}
    	
    	return true;
    }
    
    public function cancel()
    {
    	$this->setUpdateTime(time());
    	$this->setStatus(self::STATUS_CANCEL)->save();
    }
    
    public function pay()
    {	
    	$this->setUpdateTime(time());
    	$this->setStatus(self::STATUS_ALREADY_PAY)->save();
    }
    
    public function error()
    {
    	$this->setUpdateTime(time());
    	$this->setStatus(self::STATUS_ERROR)->save();
    }
    
    public function export($dir)
    {
    	$orderId = $this->getOrderId();
    	
    	$mainData = $this->getData();
    	$secondData = Mage::getModel('customclothes/orderInfo')->getDataByOrderId($orderId);
    	$data = array('mainData' => $mainData,'secondData' => $secondData);
    	$this->exportOrderCsv($data, $dir);
    	$this->setUpdateTime(time());
    	$this->setStatus(self::STATUS_EXPORT)->save();
    }
    
    
    
    public function chooseOrderRow($data){
    	$removeRow = array('id','status');
    	foreach ($data as $key => $value){
    		if(in_array($key, $removeRow)){
    			unset($data[$key]);
    		}
    	}
    	return $data;
    }
    
    public function renameOrderRow($data){
    	$renameRow = array('order_id' => '订单号'
    						,'font' => '字体'
    						,'font_style' => '字体样式'
    						,'double'=>'是否定制裤子'
    						,'create_time'=>'下单时间'
    						,'update_time'=>'最后更新时间'
    						,'color'=>'球衣颜色'
    						,'font_color'=>'字体颜色'
    						,'order_count'=>'定制数量'
    						,'result_image'=>'定制浏览图'
    						,'sku'=>'款式'
    						,'team_name'=>'球队名称'
    						,'clothes_sku'=>'球衣款式'
    						,'pants_sku'=>'球裤款号'
    						,'member_name'=>'球员名称'
    						,'member_number'=>'球员号码'
    						,'clothes_size'=>'球衣尺码 '
    						,'pants_size'=>'球裤尺码'	
    					);
    	$data = $this->getRowValueByKey($data);
    	foreach ($data as $key => $value){
    		if(isset($renameRow[$key])){
    			$data[$key] = $renameRow[$key];
    		}
    	}
    	return $data;
    }
    
    public function getRowValueByKey($data){
    	$valueArray = array('font','font_style');
    	foreach ($data as $key => $value){
    		if(in_array($key, $valueArray)){
    			
    		}
    	}
    	return $data;
    }
    
    public function exportOrderCsv($data,$dir){

    	header("Content-Type:text/html;charset=utf-8");
    	$mainData = $data['mainData'];
    	$resultRow = array();
    	$row = array();
    	foreach ($mainData as $key => $value){
    		$row[$key] = $key;
    	}
    	$resultRow[] = $this->renameOrderRow($this->chooseOrderRow($row));
    	$resultRow[] = $this->chooseOrderRow($mainData);

    	if($resultRow[1]['font'] == 1){
			$fontStr = '汉仪楷体－简';
    		$fontNumberStr = 'Helvetica Neue';
    	}
		else if($resultRow[1]['font'] == 2){
			$fontStr = '汉仪超粗圆体';
    		$fontNumberStr = 'Pop Warner';
    	}
		else if($resultRow[1]['font'] == 3){
			$fontStr = '宋体';
			$fontNumberStr = 'Helvetica Neue';
		}
		$resultRow[1]['font'] = $fontStr." (".$fontNumberStr.")";

		if($resultRow[1]['font_style'] == 1){
			$fontStyleShow = '常规';
		}
		else if($resultRow[1]['font_style'] == 2){
			$fontStyleShow = '曲线';
		}

    	$resultRow[1]['font_style'] = $fontStyleShow;

    	$orderFile = $dir . '/order.csv';
    	$fp = fopen($orderFile, 'a+');
    	fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // 添加 BOM
    	foreach ($resultRow as $dataRow)
    	{
    		fputcsv($fp,$dataRow);
    	}
    	fclose($fp);
    	
    	$orderInfoFile = $dir . '/orderInfo.csv';
    	$fp = fopen($orderInfoFile, 'a+');
    	fwrite($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // 添加 BOM
    	$secondData = $data['secondData'];
    	foreach ($secondData as $key => $dataRow){
    		$secondData[$key] = $this->chooseOrderRow($dataRow);
    	}
    	
    	$resultRow = '';
    	$row = array();
    	$rowDataTmp = $secondData[0];
    	$rowData = array();
    	foreach ($rowDataTmp as $key => $value){
    		$rowData[$key] = $key;
    	}
    	$resultRow = $this->renameOrderRow($this->chooseOrderRow($rowData));
    	fputcsv($fp,$resultRow);
    	foreach ($secondData as $dataRow)
    	{
    		fputcsv($fp,$dataRow);
    	}
    	fclose($fp);
    }
}
