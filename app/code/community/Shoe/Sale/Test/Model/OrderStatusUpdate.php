<?php
class Shoe_Sale_Test_Model_OrderStatusUpdate extends EcomDev_PHPUnit_Test_Case{
    public $model;

    public function __construct(){
        parent :: __construct();
        $this->model = new Shoe_Sale_Model_OrderStatusUpdate;
    }

//    public function testValidate(){
//        $return = $this->model->validate();
//        $this->assertFalse($return);
//    }

    public function testRebuildXml(){
        $contents = <<<HTML
<?xml version="1.0" encoding="utf-8"?>
<Orders>
	<Order>
		<OrderNumber>600573748</OrderNumber>
		<Status>Shipped</Status>
		<Carrier>FEDEX</Carrier>
		<TrackingNumber>420774699400110200882132814853</TrackingNumber>
		<Items>
			<Item>
				<Sku>316383-400-11</Sku>
				<QuantityOrdered>2</QuantityOrdered>
				<QuantityShipped>1</QuantityShipped>
				<QuantityReturned>0</QuantityReturned>
				<QuantityNeeded>1</QuantityNeeded>
			</Item>
		</Items>
	</Order>
</Orders>
HTML;
        $this->model->contents = $contents;
        list($elements,$orderUpdates) = $this->model->rebuildXml();
        $this->assertTrue(array_key_exists(600573748,$orderUpdates));
        $this->assertEquals($elements,'Orders');
//        var_dump(isset($elements['Orders']));
//        echo "\n --- \n";
//        var_dump( gettype($elements) );
        return array($elements,$orderUpdates);
    }

    /**
     * @depends testRebuildXml
     *
     */
    public function testUpdate($arr){
        // Get resource
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');

        list($elements,$orderUpdates) = $arr;
        foreach ($orderUpdates as $orderUpdate) {
//            $this->assertEquals(600573748,$orderUpdate['orderNumber']);
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderUpdate['orderNumber']);
//            echo $order->getStatus();
            $this->assertGreaterThan(0, $order->getId());
            $this->assertTrue(in_array($order->getState(),$this->model->orderStatusNeedCheck));
            $totalQtyOrdered = 0;
            foreach ($order->getAllVisibleItems() as $orderItem) {
                $totalQtyOrdered = $totalQtyOrdered + $orderItem->getQtyOrdered();
            }
            //Set customer and store ids for validation and reward transaction adjustment
            $customerId = $order->getCustomerId();
            $storeId = $order->getStoreId();
            $orderCreatedAt = $order->getCreatedAt();

//                var_dump( $customerId );
//                var_dump( $storeId );
//                var_dump( $orderCreatedAt );
//                echo "\n --- \n";
            //Add all order items to devicom_order_item table
            $query = "SELECT * FROM `devicom_order_item` WHERE `increment_id` = '" . $orderUpdate['orderNumber'] . "'";
            $getOrderResults = $writeConnection->fetchAll($query);
            $orderFound = NULL;

            if(count($getOrderResults) > 0 ){
                $orderFound = 1;
            }
            if (is_null($orderFound)) {
                $this->model->addItemToTableDevicomOrderItem($order,$orderUpdate,$writeConnection);
            }else{
                $this->model->updateTableDevicomOrderItem($order,$orderUpdate,$writeConnection);
            }

            $newOrderResults = $writeConnection->fetchAll($query);
            $this->assertGreaterThan(0,count($newOrderResults));
           return array($customerId, $order, $orderUpdate, $readConnection, $writeConnection,$totalQtyOrdered);
        }
    }
    /**
     * @depends testUpdate
     *
     */
    public function testCreateOrderShipment($arr){
        list($customerId, $order, $orderUpdate, $readConnection, $writeConnection,$totalQtyOrdered) = $arr;
        //delete shipment to create scenario
//        $shipmentId = 0;
//        foreach($order->getShipmentsCollection() as $shipment) {
//            $shipment->delete();
//            break;
//        }

        $shipmentCreated = $this->model->createOrderShipment($customerId, $order, $orderUpdate, $readConnection, $writeConnection,$totalQtyOrdered);
        return array($customerId, $order, $orderUpdate, $readConnection, $writeConnection,$totalQtyOrdered,$shipmentCreated);
    }
    /**
     * @depends testCreateOrderShipment
     *
     */
    public function testCreateOrderInvoice($arr){
        list($customerId, $order, $orderUpdate, $readConnection, $writeConnection,$totalQtyOrdered,$shipmentCreated) = $arr;
        // Create invoice only if we create a shipment (regardless of status) and haven't already invoiced
//        echo $shipmentCreated." shipcreated \n";
        if (!$order->hasInvoices()) {//$shipmentCreated in order to test invoice
//            echo $totalQtyOrdered. " Total \n";
//            echo $orderUpdate['items']['totalQuantityShipped']. " items \n";
            //only run one time
            //$this->model->createOrderInvoice($order, $orderUpdate, $totalQtyOrdered);
        }
//        echo $orderUpdate['status'];
        $this->model->checkStatus($orderUpdate,$readConnection,$writeConnection,$customerId,$order,$shipmentCreated,$totalQtyOrdered);
        $storeId = $order->getStoreId();
        $this->model->updateCustomerRewardPoints($customerId, $orderUpdate, $order, $storeId, $writeConnection);

//        echo "\n status ".$order->getStatus();
//        echo "\n state ".$order->getState();
        $this->assertEquals($order->getStatus(),"partially_shipped");

    }

}
