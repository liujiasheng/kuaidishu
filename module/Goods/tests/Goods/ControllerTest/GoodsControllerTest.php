<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-10
 * Time: 下午8:59
 */

namespace GoodsTest\ControllerTest;


use Application\Entity\GoodsMainClass;
use Goods\Model\ClassTable;
use Goods\Model\GoodsTable;
use Goods\Model\MainClassTable;
use Seller\Model\SellerTable;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class GoodsControllerTest extends AbstractHttpControllerTestCase {

    protected $traceError = true;
    protected $dbAdapter;

    /**
     * @var \Goods\Model\MainClassTable
     */
    protected $mainClassTable;
    /**
     * @var \Goods\Model\ClassTable
     */
    protected $classTable;
    /**
     * @var \Goods\Model\GoodsTable
     */
    protected $goodsTable;
    /**
     * @var \Seller\Model\SellerTable
     */
    protected $sellerTable;

    protected $goodsTableLastInsertValue;

    public function setUp()
    {
        $this->setApplicationConfig(
            include 'application.config.php'
        );

        $sl = $this->getApplicationServiceLocator();
        $this->dbAdapter = $sl->get('Zend\Db\Adapter\Adapter');

        $this->mainClassTable = new MainClassTable($this->dbAdapter);
        $this->classTable = new ClassTable($this->dbAdapter);
        $this->goodsTable = new GoodsTable($this->dbAdapter);
        $this->sellerTable = new SellerTable($this->dbAdapter);


        $this->mainClassTable->insert(array(
            "id" => 200001,
            "name" => "测试主分类1"
        ));

        $this->classTable->insert(array(
            "id" => 200002,
            "name" => "测试分类1-1",
            "mainClassId" => 200001
        ));

        $this->sellerTable->insert(array(
            "id" => 210001,
            "name" => "测试卖家",
        ));

        $this->goodsTable->insert(array(
            "ID" => 220001,
            "MainClassID" => 200001,
            "ClassID" => 200002,
            "SellerID" => 210001,
            "Name" => "测试商品",
            "Price" => 18.8,
            "Image" => "image",
            "Comment" => "comment",
            "Barcode" => "barcode",
            "State" => 1,
            "Remain" => 2,
        ));
        $this->goodsTable->insert(array(
            "MainClassID" => 200001,
            "ClassID" => 200002,
            "SellerID" => 210001,
            "Name" => "测试商品1",
        ));
        $this->goodsTableLastInsertValue = $this->goodsTable->getLastInsertValue();

        parent::setUp();
    }

    public function tearDown(){

        //goods
        $where = new Where();
        $where->in("id",array(
            220001,
        ));
        $this->goodsTable->delete($where);
        $where = new Where();
        $where->in("name",array(
            "测试商品",
            "测试商品1",
        ));
        $this->goodsTable->delete($where);

        //seller
        $where = new Where();
        $where->in("id",array(
            210001,
        ));
        $this->sellerTable->delete($where);

        //class
        $where = new Where();
        $where->in("name", array(
            "测试分类2",
            "测试分类3",
            "测试分类1-1",
        ));
        $this->classTable->delete($where);

        //main class
        $where = new Where();
        $where->in("name", array(
            "测试主分类1",
            "测试主分类",
        ));
        $this->mainClassTable->delete($where);


    }

    public function testGetClassAction(){
        $url = "/admin/goods/goods/getClass";

        //success
        $this->dispatch($url, "GET", array(
            "id" => "1",
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => true,
            "classTable" => array(
                array(
                    "id" => 1,
                    "name" => "洗漱用品",
                    "mainClassId" => 1,
                ),
                array(
                    "id" => 2,
                    "name" => "纸巾",
                    "mainClassId" => 1,
                ),
            ),
        )),$this->getResponse()->getContent());

        //no result
        $this->dispatch($url, "GET", array(
            "id" => 789789
        ));
        $this->assertEquals(Json::encode(array(
            "state" => true,
            "classTable" => array(),
        )),$this->getResponse()->getContent());

        //not number
        $this->dispatch($url, "GET", array(
            "id" => "qwe"
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 1,
            "message" => "参数错误",
        )),$this->getResponse()->getContent());

        //have no param
        $this->getResponse()->setContent("");
        $this->dispatch($url, "GET");
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 1,
            "message" => "参数错误",
        )),$this->getResponse()->getContent());

    }

    public function testAddMainClass(){
        $url = "/admin/goods/goods/addMainClass";

        //success
        $this->dispatch($url, "POST", array(
            "name" => "测试主分类"
        ));
        $this->assertResponseStatusCode(200);

        //fail exist
        $this->dispatch($url, "POST", array(
            "name" => "测试主分类"
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 101,
            "message" => "此分类已存在",
        )),$this->getResponse()->getContent());

        //fail regex 分类名格式错误
        $this->dispatch($url, "POST", array(
            "name" => ""
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 14,
            "message" => "分类名格式错误",
        )),$this->getResponse()->getContent());

        $this->dispatch($url, "POST", array(
            "name" => "1234567890123"
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 14,
            "message" => "分类名格式错误",
        )),$this->getResponse()->getContent());

        $this->dispatch($url, "POST", array(
            "name" => "@"
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 14,
            "message" => "分类名格式错误",
        )),$this->getResponse()->getContent());


    }

    public function testAddClass(){
        $url = "/admin/goods/goods/addClass";

        //success
        $this->dispatch($url, "POST", array(
            "mainClassId" => 200001,
            "name" => "测试分类2",
        ));
        $this->assertResponseStatusCode(200);

        //fail wrong id
        $this->getResponse()->setContent("wrong id");
        $this->dispatch($url, "POST", array(
            "mainClassId" => "123w",
            "name" => "123123",
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 15,
            "message" => "ID格式错误",
        )),$this->getResponse()->getContent());

        //fail wrong name
        $this->getResponse()->setContent("wrong name");
        $this->dispatch($url, "POST", array(
            "mainClassId" => 200001,
            "name" => "#123123",
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 14,
            "message" => "分类名格式错误",
        )),$this->getResponse()->getContent());

        //fail 不存在此上级分类
        $this->getResponse()->setContent("不存在此上级分类");
        $this->dispatch($url, "POST", array(
            "mainClassId" => 300001,
            "name" => "测试分类3",
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 101,
            "message" => "不存在此上级分类",
        )),$this->getResponse()->getContent());

        //fail 此分类已存在
        $this->getResponse()->setContent("此分类已存在");
        $this->dispatch($url, "POST", array(
            "mainClassId" => 200001,
            "name" => "测试分类2",
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 102,
            "message" => "此分类已存在",
        )),$this->getResponse()->getContent());
    }

    public function testAddGoodsAction(){
        $url = "/admin/goods/goods/addGoods";

        //success
        $this->dispatch($url, "POST", array(
            "MainClassID" => 200001,
            "ClassID" => 200002,
            "SellerID" => 210001,
            "Name" => "测试商品",
            "Price" => 19.9,
            "Comment" => "comment",
            "Barcode" => "barcode",
            "State" => 1,
            "Remain" => 3,
        ));
        $this->assertEquals(Json::encode(array(
            "state" => true,
            "id" => (string)++$this->goodsTableLastInsertValue,
        )),$this->getResponse()->getContent());

        //fail class id wrong
        $this->dispatch($url, "POST", array(
            "MainClassID" => 200001,
            "ClassID" => 123123,
            "SellerID" => 210001,
            "Name" => "测试商品",
            "Price" => 20.9,
            "Comment" => "comment",
            "Barcode" => "barcode",
            "State" => 1,
            "Remain" => 3,
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 101,
            "message" => "分类ID错误",
        )),$this->getResponse()->getContent());

        //fail mainClassId wrong
        $this->dispatch($url, "POST", array(
            "MainClassID" => 123123,
            "ClassID" => 200002,
            "SellerID" => 210001,
            "Name" => "测试商品",
            "Price" => 21.9,
            "Comment" => "comment",
            "Barcode" => "barcode",
            "State" => 1,
            "Remain" => 3,
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 102,
            "message" => "主分类ID错误",
        )),$this->getResponse()->getContent());

        //fail sellerIdWrong
        $this->dispatch($url, "POST", array(
            "MainClassID" => 200001,
            "ClassID" => 200002,
            "SellerID" => 123123,
            "Name" => "测试商品",
            "Price" => 22.9,
            "Comment" => "comment",
            "Barcode" => "barcode",
            "State" => 1,
            "Remain" => 3,
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 103,
            "message" => "不存在此商家",
        )),$this->getResponse()->getContent());

        //fail name wrong
        $this->dispatch($url, "POST", array(
            "MainClassID" => 200001,
            "ClassID" => 200002,
            "SellerID" => 210001,
            "Name" => "测试商品@",
            "Price" => 22.9,
            "Comment" => "comment",
            "Barcode" => "barcode",
            "State" => 1,
            "Remain" => 3,
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 16,
            "message" => "商品名格式错误",
        )),$this->getResponse()->getContent());

        //fail price wrong
        $this->dispatch($url, "POST", array(
            "MainClassID" => 200001,
            "ClassID" => 200002,
            "SellerID" => 210001,
            "Name" => "测试商品",
            "Price" => "a",
            "Comment" => "comment",
            "Barcode" => "barcode",
            "State" => 1,
            "Remain" => 3,
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 17,
            "message" => "商品价格格式错误",
        )),$this->getResponse()->getContent());

        //fail state fail
        $this->dispatch($url, "POST", array(
            "MainClassID" => 200001,
            "ClassID" => 200002,
            "SellerID" => 210001,
            "Name" => "测试商品",
            "Price" => 34,
            "Comment" => "comment",
            "Barcode" => "barcode",
            "State" => "a",
            "Remain" => 3,
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 11,
            "message" => "状态格式错误",
        )),$this->getResponse()->getContent());
    }

    public function testViewAction(){
        $url = "/admin/goods/goods/view";

        //success
        $this->dispatch($url, "POST", array(
            "id" => 220001
        ));
        $this->assertEquals(Json::encode(array(
            "state" => true,
            "goods" => array(
                "Name" => "测试商品",
                "Price" => 18.8,
                "Image" => "image",
                "Comment" => "comment",
                "Barcode" => "barcode",
                "State" => 1,
                "Remain" => 2,
                "SellerName" => "测试卖家",
                "MainClassName" => "测试主分类1",
                "ClassName" => "测试分类1-1",
            ),
        )),$this->getResponse()->getContent());

        //fail not number
        $this->dispatch($url, "POST", array(
            "id" => "12aa"
        ));
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 15,
            "message" => "ID格式错误",
        )),$this->getResponse()->getContent());

    }



} 