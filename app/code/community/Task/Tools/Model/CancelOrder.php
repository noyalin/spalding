<?php

class Task_Tools_Model_CancelOrder extends Mage_Core_Model_Abstract
{


    public function execute()
    {
        //grab orders in the past 7 days that are NOT complete.
        $yesterday = date('Y-m-d', strtotime("-1 day")-8*3600);
        $orders = Mage::getModel('sales/order')
            ->getCollection()->addAttributeToFilter('created_at', array('lt' => $yesterday))
            ->addAttributeToFilter('status', array('eq' => 'alipay_wait_buyer_pay'));
        //get data from each order
        foreach ($orders as $i => $order)
        {
            $id = $order->getIncrementId();
            if($id == 800000074){
                $order->setStatus( Mage_Sales_Model_Order::STATE_CANCELED );
                $order->setState( Mage_Sales_Model_Order::STATE_CANCELED )->save();
            }

               $order_data[$i] = array($order->getIncrementId());

                //do manipulation, array walks or creating CSV
                $i++;
        }
        var_dump($order_data);
    }

    public function validate()
    {
        return false;
    }

}

?>