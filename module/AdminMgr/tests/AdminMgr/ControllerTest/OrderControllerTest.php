<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-29
 * Time: 上午10:32
 */

namespace AdminMgrTest\ControllerTest;


use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class OrderControllerTest extends AbstractHttpControllerTestCase{

    protected $traceError = true;
    protected $dbAdapter;

    public function setUp(){
        $this->setApplicationConfig(
            include 'application.config.php'
        );
        $sl = $this->getApplicationServiceLocator();
        $this->dbAdapter = $sl->get('Zend\Db\Adapter\Adapter');

        parent::setUp();
    }

    public function tearDown(){

    }

    public function testSearchAction(){
        $url = "/admin/order/order/search";

        //success
        $this->dispatch($url, "POST", array(
            "state" => "0",
            "searchText" => "",
            "curPage" => "1",
            "pageCount" => "10",
            "domain" => "",
            "domain2" => "",
            "domain3" => "",
            "address" => ""
        ));
        $this->assertResponseStatusCode(200);
        printf($this->getResponse()->getContent());

        // 10001 参数不全
        $this->dispatch($url, "POST", array(
//            "state" => "0",
            "searchText" => "",
            "curPage" => "1",
            "pageCount" => "10",
            "domain" => "",
            "domain2" => "",
            "domain3" => "",
            "address" => ""
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 10001,
            "message" => "参数不全"
        )),$this->getResponse()->getContent());

        $this->dispatch($url, "POST", array(
            "state" => "0",
//            "searchText" => "",
            "curPage" => "1",
            "pageCount" => "10",
            "domain" => "",
            "domain2" => "",
            "domain3" => "",
            "address" => ""
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 10001,
            "message" => "参数不全"
        )),$this->getResponse()->getContent());

        $this->dispatch($url, "POST", array(
            "state" => "0",
            "searchText" => "",
//            "curPage" => "1",
            "pageCount" => "10",
            "domain" => "",
            "domain2" => "",
            "domain3" => "",
            "address" => ""
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 10001,
            "message" => "参数不全"
        )),$this->getResponse()->getContent());

        $this->dispatch($url, "POST", array(
            "state" => "0",
            "searchText" => "",
            "curPage" => "1",
//            "pageCount" => "10",
            "domain" => "",
            "domain2" => "",
            "domain3" => "",
            "address" => ""
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 10001,
            "message" => "参数不全"
        )),$this->getResponse()->getContent());

        $this->dispatch($url, "POST", array(
            "state" => "0",
            "searchText" => "",
            "curPage" => "1",
            "pageCount" => "10",
//            "domain" => "",
            "domain2" => "",
            "domain3" => "",
            "address" => ""
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 10001,
            "message" => "参数不全"
        )),$this->getResponse()->getContent());

        $this->dispatch($url, "POST", array(
            "state" => "0",
            "searchText" => "",
            "curPage" => "1",
            "pageCount" => "10",
            "domain" => "",
//            "domain2" => "",
            "domain3" => "",
            "address" => ""
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 10001,
            "message" => "参数不全"
        )),$this->getResponse()->getContent());

        $this->dispatch($url, "POST", array(
            "state" => "0",
            "searchText" => "",
            "curPage" => "1",
            "pageCount" => "10",
            "domain" => "",
            "domain2" => "",
//            "domain3" => "",
            "address" => ""
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 10001,
            "message" => "参数不全"
        )),$this->getResponse()->getContent());

        $this->dispatch($url, "POST", array(
            "state" => "0",
            "searchText" => "",
            "curPage" => "1",
            "pageCount" => "10",
            "domain" => "",
            "domain2" => "",
            "domain3" => "",
//            "address" => ""
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 10001,
            "message" => "参数不全"
        )),$this->getResponse()->getContent());

        //wrong regex state  11  状态格式错误
        $this->dispatch($url, "POST", array(
            "state" => "wrong",
            "searchText" => "",
            "curPage" => "1",
            "pageCount" => "10",
            "domain" => "",
            "domain2" => "",
            "domain3" => "",
            "address" => ""
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 11,
            "message" => "状态格式错误"
        )),$this->getResponse()->getContent());

        //wrong regex searchTest  5  搜索信息格式错误
        $this->dispatch($url, "POST", array(
            "state" => "0",
            "searchText" => "$#",
            "curPage" => "1",
            "pageCount" => "10",
            "domain" => "",
            "domain2" => "",
            "domain3" => "",
            "address" => ""
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
            "code" => 5,
            "message" => "搜索信息格式错误"
        )),$this->getResponse()->getContent());
    }


} 