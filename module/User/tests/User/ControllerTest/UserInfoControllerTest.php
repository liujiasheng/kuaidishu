<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-7-12
 * Time: 下午3:06
 */

namespace UserControllerTest\ControllerTest;


use Application\Model\AdminState;
use Application\Model\SellerState;
use Application\Model\UserState;
use Application\Model\WorkerState;
use Authenticate\AssistantClass\PasswordEncrypt;
use Authenticate\AssistantClass\UserType;
use Authenticate\Model\AdminTable;
use Authenticate\Model\SellerTable;
use Authenticate\Model\UserTable;
use Authenticate\Model\WorkerTable;
use DateTime;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class UserInfoControllerTest extends AbstractHttpControllerTestCase {

    protected $traceError = true;
    protected $_dbAdapter;
    protected $_ids;
    public function setUp(){
        $this->setApplicationConfig(
            include 'application.config.php'
        );

        $sl = $this->getApplicationServiceLocator();
        $this->_dbAdapter = $sl->get('Zend\Db\Adapter\Adapter');
        $this->_ids = array(
            "admin" => array(),
            "user" => array(),
            "seller" => array(),
            "worker" => array(),
        );
        $table = new UserTable($this->_dbAdapter);
        array_push($this->_ids["user"], $id = 100101);
        $table->insert(array('ID' => $id,
            'Username' => $username = "activeUser",
            'Password' => $this->generatePassword($username,$username),
            'Nickname' => $username,
            'Email' => $username."@qq.com",
            'Phone' => "15920376933",
            'RegisterTime' => (new Datetime())->format("Y-m-d H:i:s"),
            'LoginIP' => null,
            'LoginTime' => null,
            'State' => UserState::Active,));

        $table = new AdminTable($this->_dbAdapter);
        array_push($this->_ids["admin"], $id = 100002);
        $table->insert(array('ID' => $id,
            'Username' => $username = "activeAdmin",
            'Password' => $this->generatePassword($username,$username),
            'Nickname' => $username,
            'Email' => $username."@qq.com",
            'LoginTime' => null,
            'LoginIP' => null,
            'State' => AdminState::Active,));
        $table = new SellerTable($this->_dbAdapter);
        array_push($this->_ids["seller"], $id = 100201);
        $table->insert(array('ID'=>$id,
            'Username'=>$username = "activeSeller",
            'Password'=>$this->generatePassword($username,$username),
            'Name'=>$username,
            'Email'=>$username."@qq.com",
            'Address'=>$username."Address",
            'Phone'=>"15920376933",
            'ContactPhone'=>"no",
            'Logo'=>"path_of_image",
            'LoginTime'=>null,
            'LoginIP'=>null,
            'State'=>SellerState::Active,));
        $table = new WorkerTable($this->_dbAdapter);
        array_push($this->_ids["worker"], $id = 100301);
        $table->insert(array(
            'ID'=>$id,
            'Username'=>$username = "activeWorker",
            'Password'=>$this->generatePassword($username,$username),
            'Name'=>$username,
            'CertNumber'=>"41546835159",
            'Sex'=>"男",
            'SelfPhone'=>"15920376933",
            'Phone'=>"无",
            'LoginTime'=>null,
            'LoginIP'=>null,
            'State'=>WorkerState::Active,
        ));
        parent::setUp();
    }

    public function tearDown(){
        //TODO to clear database

        $where = new Where();
        $where->lessThan("id","110000")
            ->greaterThan("id","100000");
        $table = new AdminTable($this->_dbAdapter);
        $table->delete($where);
        $table = new UserTable($this->_dbAdapter);
        $table->delete($where);
        $table = new SellerTable($this->_dbAdapter);
        $table->delete($where);
        $table = new WorkerTable($this->_dbAdapter);
        $table->delete($where);

    }
    private function getResponseDump()
    {
        var_dump(Json::decode($this->getResponse()->getContent()));
    }


    private function generatePassword($username, $password)
    {
        return (new PasswordEncrypt())->getPasswordMd5($username, md5($password));
    }

    public function testIndexNologinAction(){
        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertResponseStatusCode(200);

        $this->dispatch('/user/userinfo');

        $this->assertResponseStatusCode(302);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('index');
        $rs = $this->getResponse()->getContent();
        $this->assertNotNull($rs);



    }

    public function testIndexLoginedAction(){
        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertResponseStatusCode(200);
        //前提，先登陆
        $post = array(
            'loginUserName' => 'activeUser',
            "loginPWD" => md5("activeUser"),
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");


        $this->dispatch('/user/userinfo');

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('index');
        $rs = $this->getResponse()->getContent();
        $this->assertNotNull($rs);
    }

    public function testUserInfoAction(){
        $this->dispatch('/user/userinfo/userinfo/userinfo');
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('userinfo');
        $rs = $this->getResponse()->getContent();
        $this->assertNotNull($rs);
    }

    public function testModifyPwdAction(){
        $this->dispatch('/user/userinfo/userinfo/modifypwd');
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('modifypwd');
        $rs = $this->getResponse()->getContent();
        $this->assertNotNull($rs);
    }

    public function testDeliveryAction(){
        $this->dispatch('/user/userinfo/userinfo/delivery');
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('delivery');
        $rs = $this->getResponse()->getContent();
        $this->assertNotNull($rs);
    }

    public function testUpdateUserInfoAction(){


        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertResponseStatusCode(200);
        //前提，先登陆
        $post = array(
            'loginUserName' => 'activeUser',
            "loginPWD" => md5("activeUser"),
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");

        $post = array(
            "nickName"=>"kuaidishu",
            'email'=>'kuaidishu@qq.com',
            'phone'=>'15920376933'
        );
        $exceptRs = array(
            'state'=>true,
            'message'=>array(
                'message'=>"保存信息成功"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updateuserinfo','POST',$post);

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updateuserinfo');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "保存信息成功");

        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertResponseStatusCode(200);

        $post = array(
            'loginUserName' => 'activeAdmin',
            "loginPWD" => md5("activeAdmin"),
            "userType" => UserType::Admin);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");


        $post = array(
            "nickName"=>"kuaidishu",
            'email'=>'kuaidishu@qq.com',
            'phone'=>'15920376933'
        );
        $exceptRs = array(
            'state'=>false,
            'code' =>-1,
            'message'=>array(
                'message'=>"该接口仅供用户修改个人信息使用"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updateuserinfo','POST',$post);
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updateuserinfo');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "该接口仅供用户修改个人信息使用");


        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertResponseStatusCode(200);

        $post = array(
            'loginUserName' => 'activeSeller',
            "loginPWD" => md5("activeSeller"),
            "userType" => UserType::Seller);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");


        $post = array(
            "nickName"=>"kuaidishu",
            'email'=>'kuaidishu@qq.com',
            'phone'=>'15920376933'
        );
        $exceptRs = array(
            'state'=>false,
            'code' =>-1,
            'message'=>array(
                'message'=>"该接口仅供用户修改个人信息使用"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updateuserinfo','POST',$post);
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updateuserinfo');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "该接口仅供用户修改个人信息使用");

        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertResponseStatusCode(200);

        $post = array(
            'loginUserName' => 'activeWorker',
            "loginPWD" => md5("activeWorker"),
            "userType" => UserType::Worker);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");


        $post = array(
            "nickName"=>"kuaidishu",
            'email'=>'kuaidishu@qq.com',
            'phone'=>'15920376933'
        );
        $exceptRs = array(
            'state'=>false,
            'code' =>-1,
            'message'=>array(
                'message'=>"该接口仅供用户修改个人信息使用"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updateuserinfo','POST',$post);
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updateuserinfo');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "该接口仅供用户修改个人信息使用");

        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertResponseStatusCode(200);
    }

    public function testUserInfoInvalidAction(){
        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertResponseStatusCode(200);
        //前提，先登陆
        $post = array(
            'loginUserName' => 'activeUser',
            "loginPWD" => md5("activeUser"),
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");

        $post = array(
            "nickName"=>"activeUser",
            'phone'=>''
        );
        $exceptRs = array(
            'state'=>false,
            'code'=>-6,
            'message'=>array(
                'message'=>"参数错误"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updateuserinfo','POST',$post);

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updateuserinfo');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "参数错误");

    }

    public function testUserInfoNoUpdateAction(){
        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertResponseStatusCode(200);
        //前提，先登陆
        $post = array(
            'loginUserName' => 'activeUser',
            "loginPWD" => md5("activeUser"),
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");

        $post = array(
            "nickName"=>"activeUser",
            'email'=>'activeUser@qq.com',
            'phone'=>'15920376933'
        );
        $exceptRs = array(
            'state'=>false,
            'code'=>-3,
            'message'=>array(
                'message'=>"信息没有更新"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updateuserinfo','POST',$post);

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updateuserinfo');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "信息没有更新");

    }

    public function testUpdatePasswordAction(){


        $post = array(
            'oripwd'=>md5("wrongpassword"),
            'newpwd'=>md5('123123')
        );
        $exceptRs = array(
            'state'=>false,
            'code' =>-2,
            'message'=>array(
                'message'=>"请先登录再操作"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updatePassword','POST',$post);
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updatePassword');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "请先登录再操作");

        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertResponseStatusCode(200);
        //前提，先登陆
        $post = array(
            'loginUserName' => 'activeUser',
            "loginPWD" => md5("activeUser"),
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");

        $post = array(
            'newpwd'=>md5('123123')
        );
        $exceptRs = array(
            'state'=>false,
            'code' =>-1,
            'message'=>array(
                'message'=>"缺少参数"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updatePassword','POST',$post);
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updatePassword');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "缺少参数");



        $post = array(
            'oripwd'=>md5("wrongpassword"),
            'newpwd'=>md5('123123')
        );
        $exceptRs = array(
            'state'=>false,
            'code' =>-3,
            'message'=>array(
                'message'=>"原密码错误"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updatePassword','POST',$post);
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updatePassword');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "原密码错误");

        $post = array(
            'oripwd'=>md5("activeUser"),
            'newpwd'=>'12312'
        );
        $exceptRs = array(
            'state'=>false,
            'code' =>-5,
            'message'=>array(
                'message'=>"新密码不符合格式"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updatePassword','POST',$post);
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updatePassword');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "新密码不符合格式");

        $post = array(
            'oripwd'=>md5("activeUser"),
            'newpwd'=>md5("activeUser")
        );
        $exceptRs = array(
            'state'=>false,
            'code' =>-4,
            'message'=>array(
                'message'=>"新密码不能和原密码相同"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updatePassword','POST',$post);
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updatePassword');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "新密码不能和原密码相同");

        $post = array(
            'oripwd'=>md5("activeUser"),
            'newpwd'=>md5("123123")
        );
        $exceptRs = array(
            'state'=>true,
            'message'=>array(
                'message'=>"密码修改成功"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/updatePassword','POST',$post);
        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('updatePassword');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "密码修改成功");
    }

    public function testAddDeliveryAddressAction(){

        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertResponseStatusCode(200);
        //前提，先登陆
        $post = array(
            'loginUserName' => 'activeUser',
            "loginPWD" => md5("activeUser"),
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");


        $post = array(
            "UserID"=>"", //是否登陆
            'Domain'=>'', //是否存在区域
            'Address'=>'',//不能为空
            'Phone'=>'',//不能为空
            'Name'=>'',//不能为空
        );
        $exceptRs = array(
            'state'=>false,
            'code'=>10011,
            'message'=>array(
                'message'=>"地域不能为空"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/addDeliveryAddress','POST',$post);

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('adddeliveryaddress');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "地域不能为空");

        $post = array(
            "UserID"=>"", //是否登陆
            'Domain'=>'广州大学', //是否存在区域
            'Address'=>'',//不能为空
            'Phone'=>'',//不能为空
            'Name'=>'',//不能为空
        );
        $exceptRs = array(
            'state'=>false,
            'code'=>10012,
            'message'=>array(
                'message'=>"地址不能为空"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/addDeliveryAddress','POST',$post);

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('adddeliveryaddress');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "地址不能为空");

        $post = array(
            "UserID"=>"", //是否登陆
            'Domain'=>'广州大学', //是否存在区域
            'Address'=>'测试地址',//不能为空
            'Phone'=>'',//不能为空
            'Name'=>'',//不能为空
        );
        $exceptRs = array(
            'state'=>false,
            'code'=>10013,
            'message'=>array(
                'message'=>"电话不能为空"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/addDeliveryAddress','POST',$post);

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('adddeliveryaddress');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "电话不能为空");

        $post = array(
            "UserID"=>"", //是否登陆
            'Domain'=>'广州大学', //是否存在区域
            'Address'=>'测试地址',//不能为空
            'Phone'=>'1',//不能为空
            'Name'=>'',//不能为空
        );
        $exceptRs = array(
            'state'=>false,
            'code'=>10014,
            'message'=>array(
                'message'=>"电话格式错误"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/addDeliveryAddress','POST',$post);

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('adddeliveryaddress');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "电话格式错误");


        $post = array(
            "UserID"=>"", //是否登陆
            'Domain'=>'广州大学', //是否存在区域
            'Address'=>'测试地址',//不能为空
            'Phone'=>'15920376933',//不能为空
            'Name'=>'',//不能为空
        );
        $exceptRs = array(
            'state'=>false,
            'code'=>10015,
            'message'=>array(
                'message'=>"收货人不能为空"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/addDeliveryAddress','POST',$post);

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('adddeliveryaddress');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "收货人不能为空");

        $post = array(
            "UserID"=>"", //是否登陆
            'Domain'=>'广州大学', //是否存在区域
            'Address'=>'测试地址',//不能为空
            'Phone'=>'15920376933',//不能为空
            'Name'=>'*测试用户',//不能为空
        );
        $exceptRs = array(
            'state'=>false,
            'code'=>10016,
            'message'=>array(
                'message'=>"用户名只能是中文、字母和下划线"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/addDeliveryAddress','POST',$post);

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('adddeliveryaddress');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户名只能是中文、字母和下划线");

        $post = array(
            "UserID"=>"", //是否登陆
            'Domain'=>'广州大学1', //是否存在区域
            'Address'=>'测试地址',//不能为空
            'Phone'=>'15920376933',//不能为空
            'Name'=>'测试用户',//不能为空
        );
        $exceptRs = array(
            'state'=>false,
            'code'=>10017,
            'message'=>array(
                'message'=>"所选地域不存在"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/addDeliveryAddress','POST',$post);

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('adddeliveryaddress');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "所选地域不存在");

        $post = array(
            "UserID"=>100101, //是否登陆
            'Domain'=>'广州大学', //是否存在区域
            'Address'=>'测试地址',//不能为空
            'Phone'=>'15920376933',//不能为空
            'Name'=>'测试用户',//不能为空
        );
        $exceptRs = array(
            'state'=>false,
            'code'=>10018,
            'message'=>array(
                'message'=>"保存成功"
            )
        );
        $this->dispatch('/user/userinfo/userinfo/addDeliveryAddress','POST',$post);

        $this->assertResponseStatusCode(200);
        $this->assertControllerClass('UserInfoController');
        $this->assertActionName('adddeliveryaddress');
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "保存成功");
    }

}
 