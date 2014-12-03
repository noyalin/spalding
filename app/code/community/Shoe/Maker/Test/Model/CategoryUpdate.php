<?php
class Shoe_Maker_Test_Model_CategoryUpdate extends EcomDev_PHPUnit_Test_Case{
    public $model = null;

    public function __construct(){
        $this->model = new Shoe_Maker_Model_CategoryUpdate();
    }

    public function testGetXmlElementFromString(){
        $contents = <<<HTML
<?xml version="1.0" encoding="UTF-8"?><root updateType="3"><Categories><Category><Id>2</Id><ParentId>1</ParentId><CategoryPath>1/2</CategoryPath><DisplayOrder>1</DisplayOrder><Name>Default Category</Name><IsSneakerheadOnly>0</IsSneakerheadOnly><IsPublished>1</IsPublished><UrlKey></UrlKey><HighlightedProductCode></HighlightedProductCode><HeaderTitle></HeaderTitle><SEO_Title></SEO_Title><SEO_Keywords></SEO_Keywords><SEO_Description></SEO_Description><SEO_Canonical></SEO_Canonical><IsVisibleOnMenu>1</IsVisibleOnMenu><IsEnableFilter>0</IsEnableFilter></Category><Category><Id>2798</Id><ParentId>125</ParentId><CategoryPath>1/2/75/125/2798</CategoryPath><DisplayOrder>1</DisplayOrder><Name>9FIVE</Name><IsSneakerheadOnly>0</IsSneakerheadOnly><IsPublished>1</IsPublished><UrlKey>9five-eyewear</UrlKey><HighlightedProductCode></HighlightedProductCode><HeaderTitle>9FIVE Eyewear</HeaderTitle><SEO_Title>Shop 9FIVE Eyewear - 9FIVE Collection</SEO_Title><SEO_Keywords></SEO_Keywords><SEO_Description>Shop for 9FIVE Eyewear.  At Sneakerhead.com we have the latest 9FIVE Eyewear.</SEO_Description><SEO_Canonical></SEO_Canonical><IsVisibleOnMenu>1</IsVisibleOnMenu><IsEnableFilter>0</IsEnableFilter></Category></Categories></root>
HTML;
        list($rootXmlElement,$entityTypes) = Shoe_Maker_Model_CategoryUpdate :: getXmlElementFromString($contents);
        $this->assertNotNull($rootXmlElement);
        $this->assertNotNull($entityTypes);
        return $contents;
    }

    /**
     * @depends testGetXmlElementFromString
     */
    public function testGetCategory($content){
        $categories = $this->model->getCategoryFromFile($content);
        $this->assertNotNull($categories);
        return $categories;
    }
    /**
     * @depends testGetCategory
     */
    public function testExecuteCategory($categories){
        $this->model->executeCategory($categories);
        foreach ($categories as $categoryArray) {
            $category = Mage::getModel('catalog/category');
            $category->setStoreId(0); // 0 = default/all store view.
            $category->load($categoryArray['category_id']);
            if($categoryArray['category_id'] == 2){
                $this->assertEquals($category->getName(),'Default Category');
                $this->assertEquals($category->getParentId(),1);
                $this->assertEquals($category->getId(),2);
//                var_dump($category->getParentId());
            }
        }
    }
    /**
     * @depends testGetCategory
     */
    public function testUpdateChildCount($categories){
        //TODO do not know what is used for this function
        $this->model->updateChildCount($categories);
    }
}
