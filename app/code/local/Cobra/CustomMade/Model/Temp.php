<?php

class Cobra_CustomMade_Model_Temp extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('custommade/temp');
    }

    public function deleteByCustomerId($customerId)
    {
        $ids = $this->getResource()
            ->loadByField('customer_id', $customerId);
        foreach ($ids as $id) {
            $this->load($id)->delete();
        }
    }

    public function loadByCustomerId($customerId)
    {
        $id = $this->getResource()
            ->loadByField('customer_id', $customerId);
        if($id){
        	return $this->load($id[0]);
        }else{
        	return array();
        }
        
    }
    public function saveCustomMadeTemp($customerId, $sub_sku)
    {
        $sku = $_POST['sku'];
        $subSku = $sub_sku;
        $customerIdsession = Mage::getSingleton('core/session')->getCustomerId();
        if (!$customerIdsession) {
            Mage::log('saveCustomMadeTemp--Error--customerId='.$customerId.',customerIdsession='.$customerIdsession);
            return;
        }
        $session = Mage::getModel('custommade/session')->getOrderSession($customerIdsession, $sku);

        $this->deleteByCustomerId($customerId);

        //保存定制信息
        Mage::getModel('custommade/temp')->setCustomerId($customerId)
            ->setSku($session->getSku())
            ->setSubSku($subSku)
            ->setTypeP1($session->getTypeP1())
            ->setMsg1P1($session->getContent1P1())
            ->setMsg2P1($session->getContent2P1())
            ->setMsg3P1($session->getContent3P1())
            ->setMsg4P1($session->getContent4P1())
            ->setTypeP2($session->getTypeP2())
            ->setMsg1P2($session->getContent1P2())
            ->setMsg2P2($session->getContent2P2())
            ->setMsg3P2($session->getContent3P2())
            ->setMsg4P2($session->getContent4P2())
            ->setCreateTime(time())
            ->save();
        $this->clearSession($customerIdsession, $sku);
        Mage::log('saveCustomMadeTemp-----customerId='.$customerId.',customerIdsession='.$customerIdsession);
    }

    private function clearSession($customerIdsession, $sku)
    {
//        $session = Mage::getModel('custommade/session');
//        $sessionId = $session->getCollection()
//            ->addFieldToFilter('customer_id', $customerIdsession)
//            ->addFieldToFilter('sku', $sku)
//            ->getFirstItem()
//            ->getSessionId();
//        $session->load($sessionId)->delete();
//
//        $item = $session->getCollection()
//            ->addFieldToFilter('customer_id', $customerIdsession)
//            ->getFirstItem();
//        if ($item->getData() == null) {
//            Mage::getSingleton('core/session')->setCustomerId(null);
//            Mage::getModel('custommade/customer')->load($customerIdsession)->delete();
//        }
        Mage::getSingleton('core/session')->setCustomerId(null);
        Mage::getSingleton('core/session')->setCustomermadeAgree(null);
        Mage::log('clearSession CustomerId =null');
    }
}
