<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-10
 * Time: 下午3:08
 */

namespace UserControllerTest\ControllerTest;
use Zend\Json\Json;
use Zend\Stdlib\Parameters;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class UserControllerTest extends AbstractHttpControllerTestCase{

    protected $traceError = true;
    protected $dbAdapter;

    public function setUp()
    {
        $this->setApplicationConfig(
            include 'application.config.php'
        );

        $sl = $this->getApplicationServiceLocator();
        $this->dbAdapter = $sl->get('Zend\Db\Adapter\Adapter');

        parent::setUp();
    }


    public function testUserTable(){
        $table = new \User\Model\UserTable($this->dbAdapter);
        $user = $table->getUser(25);
        $this->assertNotNull($user, "not null");
    }

    public function testIndexAction(){
        $this->dispatch("/admin/user");
        $this->assertResponseStatusCode(200);

        $this->assertModuleName("User");
        $this->assertControllerName('User\Controller\User');
        $this->assertControllerClass('UserController');
        $this->assertMatchedRouteName("user");
        $this->assertActionName("index");

    }

    public function testView(){
        $url = "/admin/user/user/view";
        //success
        $this->dispatch($url, "POST",array(
                "id" => 25,
            ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => true,
            "user" => array(
                "id" => "25",
                "username" => "keson1",
                "nickname" => "keson1",
                "email" => "keson1@qq.com",
                "phone" => "15626098303",
                "registerTime" => "2014-07-04 16:59:31",
                "loginTime" => null,
                "loginIP" => null,
                "state" => "1",
            )
        )),$this->getResponse()->getContent());

        //fail user not found
        $this->getResponse()->setContent("");
        $this->dispatch($url, "POST",array(
            "id" => 987987,
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
        )),$this->getResponse()->getContent());

        //fail id is not number
        $this->getResponse()->setContent("");
        $this->dispatch($url, "POST",array(
            "id" => "qwe",
        ));
        $this->assertResponseStatusCode(200);
        $this->assertEquals(Json::encode(array(
            "state" => false,
        )),$this->getResponse()->getContent());

    }

}























