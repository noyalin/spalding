<?php
class Task_Tools_Test_Model_Order extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Task_Tools_Model_FlushCache();
    }
    public function testExecute(){
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(21);
        $customer->loadByEmail("davis.du@sneakerhead.com");

        $flatOrderDataObject = new Varien_Object();
        $order = new Task_Tools_Model_Sale_OrderCreate();
        $order->setOrderInfo($flatOrderDataObject,$customer);
        $newOrder = $order->create();
        echo $newOrder->getStatus()."   \n";
        echo $newOrder->getId()."   \n";

    }
    /*
    public function testCreateUser(){
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->setStore(Mage::app()->getStore());
        $customer->setFirstname("Douglas");
        $customer->setLastname("Radburn");
        $customer->setEmail("davis.du@sneakerhead.com");
        $customer->setPasswordHash(md5("111111"));
        var_dump($customer->save());

        echo "\n website ".Mage::app()->getWebsite()->getId();
        var_dump(Mage::app()->getStore());
    }

    public function testLoadCustomer(){
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(0);
        $customer->loadByEmail("davis.du@sneakerhead.com"); //load customer by email id
        echo $customer->getId();
        echo $customer->getFirstname();
        echo $customer->getLastname();
    }


    public function testFunction(){
//        Mage :: log("Test m");
        $thing_1 = new Varien_Object();
        $thing_1->setName('Richard');
        $thing_1->setAge(24);
//        echo $thing_1->getLastname();
//        Mage :: log( $thing_1->getData() );
        $thing_2 = new Varien_Object();
        $thing_2->setName('Jane');
        $thing_2->setAge(12);

        $thing_3 = new Varien_Object();
        $thing_3->setName('Spot');
        $thing_3->setLastName('The Dog');
        $thing_3->setAge(7);

        $collection_of_things = new Varien_Data_Collection();
        $collection_of_things
            ->addItem($thing_1)
            ->addItem($thing_2)
            ->addItem($thing_3);

//        foreach($collection_of_things as $thing)
//        {
//            var_dump($thing->getData());
//        }
//        var_dump($collection_of_things->getFirstItem()->getData());
//        var_dump( $collection_of_things->toXml() );
//        var_dump($collection_of_things->getColumnValues('name'));
//        var_dump($collection_of_things->getItemsByColumnValue('name','Spot'));

    }

    public function testAction(){
        $collection_of_products = Mage::getModel('catalog/product')->getCollection();
//        var_dump($collection_of_products->getFirstItem()->getData());
//        var_dump($collection_of_products->getSelect()); //might cause a segmentation fault
//        var_dump(
//            (string) $collection_of_products->getSelect()
//        );
        $collection_of_products = Mage::getModel('catalog/product')
            ->getCollection();
        $collection_of_products->addAttributeToSelect('meta_title');


    }

    public function testFilterAction()
    {
        $collection_of_products = Mage::getModel('catalog/product')
            ->getCollection();
        $collection_of_products->addFieldToFilter('sku','10001377-grey-6');

        //another neat thing about collections is you can pass them into the count      //function.  More PHP5 powered goodness
        echo "Our collection now has " . count($collection_of_products) . ' item(s)';
//        var_dump($collection_of_products->getFirstItem()->getData());
        var_dump(
            (string)
            Mage::getModel('catalog/product')
                ->getCollection()
                ->addFieldToFilter('sku','10001377-grey-6')
                ->getSelect());
    }


*/
}
