<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-19
 * Time: 下午5:18
 */

namespace HomeTest\ControllerTest;

use Zend\Stdlib\Parameters;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class HomeControllerTest extends AbstractHttpControllerTestCase{

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

    public function testGetCartInfoAction(){
        $url = "/home/home/getCartInfo";

        //success
        $this->dispatch( $url, "POST", array(
            "items" => array(
                array(
                    "id" => "220071",
                    "count" => "2",
                ),
                array(
                    "id" => "4",
                    "count" => "1",
                ),
            ),
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => true,
            "items" => array(
                array(
                    "className" => "小吃",
                    "count" => "1",
                    "image" => "16-1-1-asdasd345-1405230064.jpg",
                    "mainClassName" => "快餐",
                    "name" => "鸡爪",
                    "price" => "12",
                    "sellerName" => "喜事多便利店",
                ),
                array(
                    "className" => "小炒",
                    "count" => "2",
                    "image" => "16-3-5-宫保鸡丁-1405345065.jpg",
                    "mainClassName" => "快餐",
                    "name" => "宫保鸡丁",
                    "price" => "8",
                    "sellerName" => "喜事多便利店",
                ),
            ),
        )),$this->getResponse()->getContent());
    }


} 