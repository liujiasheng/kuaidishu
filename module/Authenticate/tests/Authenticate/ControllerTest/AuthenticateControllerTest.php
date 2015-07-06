<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-7-7
 * Time: 下午10:06
 */

namespace AuthenticateTest\ControllerTest;

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
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Authentication\Storage\Session;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;


class AuthenticateControllerTest extends AbstractHttpControllerTestCase
{

    protected $traceError = true;
    protected $_serviceLocator;
    protected $_dbAdapter;
    protected $_ModuleName;
    protected $_ControllerName;
    protected $_ControllerClass;
    protected $_ids;

    public function setUp()
    {


        $this->setApplicationConfig(
            include 'application.config.php'
        );
        $this->_serviceLocator = $this->getApplicationServiceLocator();
        $this->_dbAdapter = $this->_serviceLocator->get('Zend\Db\Adapter\Adapter');
        $this->setModuleName('authenticate');
        $this->setControllerName('Authenticate\Controller\Authenticate');
        $this->setControllerClass('AuthenticateController');
        $this->_ids = array(
            "admin" => array(),
            "user" => array(),
            "seller" => array(),
            "worker" => array(),
        );
        $_SERVER['CONTEXT_DOCUMENT_ROOT'] = 'G:/zendLearn/kuaidishu';
        $_SERVER['Captcha_Path'] = 'G:/zendLearn/kuaidishu';
        $this->insertAdminTable();

        parent::setUp();
    }

    public function tearDown()
    {
        $table = new AdminTable($this->_dbAdapter);
        $where = new Where();
        $where->lessThan("id","110000")
              ->greaterThan("id","100000");
        $table->delete($where);
        $table = new UserTable($this->_dbAdapter);
        $table->delete($where);
        $table = new SellerTable($this->_dbAdapter);
        $table->delete($where);
        $table = new WorkerTable($this->_dbAdapter);
        $table->delete($where);

    }


    public function insertAdminTable()
    {
        //admin data
        $table = new AdminTable($this->_dbAdapter);
        array_push($this->_ids["admin"], $id = 100001);
        $table->insert(array('ID' => $id,
            'Username' => $username = "forbiddenAdmin",
            'Password' => $this->generatePassword($username,$username),
            'Nickname' => $username,
            'Email' => $username."@qq.com",
            'LoginTime' => null,
            'LoginIP' => null,
            'State' => AdminState::Forbidden,));
        array_push($this->_ids["admin"], $id = 100002);
        $table->insert(array('ID' => $id,
            'Username' => $username = "activeAdmin",
            'Password' => $this->generatePassword($username,$username),
            'Nickname' => $username,
            'Email' => $username."@qq.com",
            'LoginTime' => null,
            'LoginIP' => null,
            'State' => AdminState::Active,));
        array_push($this->_ids["admin"], $id = 100003);
        $table->insert(array('ID' => $id,
            'Username' => $username = "invalidAdmin",
            'Password' => $this->generatePassword($username,$username),
            'Nickname' => $username,
            'Email' => $username."@qq.com",
            'LoginTime' => null,
            'LoginIP' => null,
            'State' => -1,));
        //user data
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
        array_push($this->_ids["user"], $id = 100102);
        $table->insert(array('ID' => $id,
            'Username' => "InactiveUser",
            'Password' => $this->generatePassword($username,$username),
            'Nickname' => $username,
            'Email' => $username."@qq.com",
            'Phone' => "15920376933",
            'RegisterTime' => (new Datetime())->format("Y-m-d H:i:s"),
            'LoginIP' => null,
            'LoginTime' => null,
            'State' => UserState::Inactive,));
        array_push($this->_ids["user"], $id = 100103);
        $table->insert(array('ID' => $id,
            'Username' => "ForbiddenUser",
            'Password' => $this->generatePassword($username,$username),
            'Nickname' => $username,
            'Email' => $username."@qq.com",
            'Phone' => "15920376933",
            'RegisterTime' => (new Datetime())->format("Y-m-d H:i:s"),
            'LoginIP' => null,
            'LoginTime' => null,
            'State' => UserState::Forbidden,));
        array_push($this->_ids["user"], $id = 100104);
        $table->insert(array('ID' => $id,
            'Username' => "InvalidUser",
            'Password' => $this->generatePassword($username,$username),
            'Nickname' => $username,
            'Email' => $username."@qq.com",
            'Phone' => "15920376933",
            'RegisterTime' => (new Datetime())->format("Y-m-d H:i:s"),
            'LoginIP' => null,
            'LoginTime' => null,
            'State' => -1,));
        //seller data
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
        array_push($this->_ids["seller"], $id = 100202);
        $table->insert(array('ID'=>$id,
            'Username'=>$username = "DismissionSeller",
            'Password'=>$this->generatePassword($username,$username),
            'Name'=>$username,
            'Email'=>$username."@qq.com",
            'Address'=>$username."Address",
            'Phone'=>"15920376933",
            'ContactPhone'=>"no",
            'Logo'=>"path_of_image",
            'LoginTime'=>null,
            'LoginIP'=>null,
            'State'=>SellerState::Dismission,));
        array_push($this->_ids["seller"], $id = 100203);
        $table->insert(array('ID'=>$id,
            'Username'=>$username = "ForbiddenSeller",
            'Password'=>$this->generatePassword($username,$username),
            'Name'=>$username,
            'Email'=>$username."@qq.com",
            'Address'=>$username."Address",
            'Phone'=>"15920376933",
            'ContactPhone'=>"no",
            'Logo'=>"path_of_image",
            'LoginTime'=>null,
            'LoginIP'=>null,
            'State'=>SellerState::Forbidden,));
        array_push($this->_ids["seller"], $id = 100204);
        $table->insert(array('ID'=>$id,
            'Username'=>$username = "InvalidSeller",
            'Password'=>$this->generatePassword($username,$username),
            'Name'=>$username,
            'Email'=>$username."@qq.com",
            'Address'=>$username."Address",
            'Phone'=>"15920376933",
            'ContactPhone'=>"no",
            'Logo'=>"path_of_image",
            'LoginTime'=>null,
            'LoginIP'=>null,
            'State'=>-1,));
        //worker data
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
        array_push($this->_ids["worker"], $id = 100302);
        $table->insert(array(
            'ID'=>$id,
            'Username'=>$username = "DismissionWorker",
            'Password'=>$this->generatePassword($username,$username),
            'Name'=>$username,
            'CertNumber'=>"41546835159",
            'Sex'=>"男",
            'SelfPhone'=>"15920376933",
            'Phone'=>"无",
            'LoginTime'=>null,
            'LoginIP'=>null,
            'State'=>WorkerState::Dismission,
        ));
        array_push($this->_ids["worker"], $id = 100303);
        $table->insert(array(
            'ID'=>$id,
            'Username'=>$username = "ForbiddenWorker",
            'Password'=>$this->generatePassword($username,$username),
            'Name'=>$username,
            'CertNumber'=>"41546835159",
            'Sex'=>"男",
            'SelfPhone'=>"15920376933",
            'Phone'=>"无",
            'LoginTime'=>null,
            'LoginIP'=>null,
            'State'=>WorkerState::Forbidden,
        ));
        array_push($this->_ids["worker"], $id = 100304);
        $table->insert(array(
            'ID'=>$id,
            'Username'=>$username = "InvalidWorker",
            'Password'=>$this->generatePassword($username,$username),
            'Name'=>$username,
            'CertNumber'=>"41546835159",
            'Sex'=>"男",
            'SelfPhone'=>"15920376933",
            'Phone'=>"无",
            'LoginTime'=>null,
            'LoginIP'=>null,
            'State'=>-1,
        ));


    }
    //test login logout  action
    public function testAuthenticateAction()
    {
        //into authenticate module
        $this->dispatch('/authenticate');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('authenticate');
        $this->assertControllerName('Authenticate\Controller\Authenticate');
        $this->assertControllerClass('AuthenticateController');
        $this->assertMatchedRouteName('Authenticate');
        $this->assertActionName("index");


        //login success admin part
        $post = array(
            'loginUserName' => 'activeAdmin',
            "loginPWD" => md5("activeAdmin"),
            "userType" => UserType::Admin);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");
        /** @var $auSvr AuthenticationService */
        $auSvr = $this->getApplicationServiceLocator()->get("Session");
        $this->assertEquals(true, $auSvr->hasIdentity(), "记录Session成功");
        $auSvr->getIdentity();


        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "退出成功")
        );
        $this->dispatch('/authenticate/Authenticate/logout');
        $this->assertModuleName('authenticate');
        $this->assertControllerName('Authenticate\Controller\Authenticate');
        $this->assertControllerClass('AuthenticateController');
        $this->assertMatchedRouteName('authenticate/default');
        $this->assertActionName("logout");
        $this->assertEquals(false, $auSvr->hasIdentity(), "退出成功");

        //login success user part
        $post = array(
            'loginUserName' => 'activeUser',
            "loginPWD" => md5("activeUser"),
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");
        $auSvr = new AuthenticationService();
        //Session name Authenticate
        $auSvr->setStorage(new Session("Authenticate"));
        $this->assertEquals(true, $auSvr->hasIdentity(), "记录Session成功");
        //login success seller part
        $post = array(
            'loginUserName' => 'activeSeller',
            "loginPWD" => md5("activeSeller"),
            "userType" => UserType::Seller);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");
        $auSvr = new AuthenticationService();
        //Session name Authenticate
        $auSvr->setStorage(new Session("Authenticate"));
        $this->assertEquals(true, $auSvr->hasIdentity(), "记录Session成功");
        //login success worker part
        $post = array(
            'loginUserName' => 'activeWorker',
            "loginPWD" => md5("activeWorker"),
            "userType" => UserType::Worker);
        $exceptRs = array(
            'state' => true,
            "message" => array('message' => "登陆成功")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "登陆成功");
        $auSvr = new AuthenticationService();
        //Session name Authenticate
        $auSvr->setStorage(new Session("Authenticate"));
        $this->assertEquals(true, $auSvr->hasIdentity(), "记录Session成功");

        /*
         * Failure part
         * lack of argument
         */
        $post = array(
            'loginUserName' => 'user_not_exist',
            "loginPWD" => "123123");
        $exceptRs = array(
            'state' => false,
            "code" => -1,
            "message" => array('message' => "参数错误")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "参数错误");
        /*
         * user not exist
         * admin part
         */
        $post = array(
            'loginUserName' => 'user_not_exist',
            "loginPWD" => "123123",
            "userType" => UserType::Admin);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_IDENTITY_NOT_FOUND,
            "message" => array('message' => "用户不存在")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户不存在");

        /*
         * user not exist
         * user part
         */
        $post = array(
            'loginUserName' => 'user_not_exist',
            "loginPWD" => "123123",
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_IDENTITY_NOT_FOUND,
            "message" => array('message' => "用户不存在")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户不存在");

        /*
         * user not exist
         * seller part
         */
        $post = array(
            'loginUserName' => 'user_not_exist',
            "loginPWD" => "123123",
            "userType" => UserType::Seller);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_IDENTITY_NOT_FOUND,
            "message" => array('message' => "用户不存在")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户不存在");

        /*
         * user not exist
         * worker part
         */
        $post = array(
            'loginUserName' => 'user_not_exist',
            "loginPWD" => "123123",
            "userType" => UserType::Worker);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_IDENTITY_NOT_FOUND,
            "message" => array('message' => "用户不存在")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户不存在");


        /*
         * wrong password
         * admin part
         */

        $post = array(
            'loginUserName' => 'activeAdmin',
            "loginPWD" => "123123",
            "userType" => UserType::Admin);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户密码错误")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户密码错误");

        /*
         * wrong password
         * user part
         */

        $post = array(
            'loginUserName' => 'activeUser',
            "loginPWD" => "wrongpassword",
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户密码错误")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户密码错误");

        /*
       * wrong password
       * seller part
       */

        $post = array(
            'loginUserName' => 'activeSeller',
            "loginPWD" => "wrongpassword",
            "userType" => UserType::Seller);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户密码错误")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户密码错误");

        /*
       * wrong password
       * worker part
       */

        $post = array(
            'loginUserName' => 'activeWorker',
            "loginPWD" => "wrongpassword",
            "userType" => UserType::Worker);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户密码错误")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户密码错误");

        //wrong usertype
        $post = array(
            'loginUserName' => 'kuaidishu',
            "loginPWD" => md5("kuaidishu"),
            "userType" => -1);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE,
            "message" => array('message' => "用户类型错误")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户类型错误");

        //user Inactive user part
        $post = array(
            'loginUserName' => 'InactiveUser',
            "loginPWD" => md5("InactiveUser"),
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户未激活")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户未激活");

        //user dismission seller part
        $post = array(
            'loginUserName' => $username = 'DismissionSeller',
            "loginPWD" => md5($username),
            "userType" => UserType::Seller);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户已解约")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户已解约");

        //user dismission worker part
        $post = array(
            'loginUserName' => $username = 'DismissionWorker',
            "loginPWD" => md5($username),
            "userType" => UserType::Worker);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户已解约")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户已解约");

        //user forbidden admin part
        $post = array(
            'loginUserName' => $username = 'forbiddenAdmin',
            "loginPWD" => md5($username),
            "userType" => UserType::Admin);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户被禁用")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户被禁用");

        //user forbidden user part
        $post = array(
            'loginUserName' => $username = 'ForbiddenUser',
            "loginPWD" => md5($username),
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户被禁用")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户被禁用");

        //user forbidden seller part
        $post = array(
            'loginUserName' => $username = 'ForbiddenSeller',
            "loginPWD" => md5($username),
            "userType" => UserType::Seller);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户被禁用")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户被禁用");

        //user forbidden worker part
        $post = array(
            'loginUserName' => $username = 'ForbiddenWorker',
            "loginPWD" => md5($username),
            "userType" => UserType::Worker);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户被禁用")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户被禁用");

        //user invalid admin part
        $post = array(
            'loginUserName' => $username = 'invalidAdmin',
            "loginPWD" => md5($username),
            "userType" => UserType::Admin);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户状态错误")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户状态错误");

        //user invalid user part
        $post = array(
            'loginUserName' => $username = 'InvalidUser',
            "loginPWD" => md5($username),
            "userType" => UserType::User);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户状态错误")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户状态错误");

        //user invalid seller part
        $post = array(
            'loginUserName' => $username = 'InvalidSeller',
            "loginPWD" => md5($username),
            "userType" => UserType::Seller);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户状态错误")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户状态错误");

        //user invalid worker part
        $post = array(
        'loginUserName' => $username = 'InvalidWorker',
        "loginPWD" => md5($username),
        "userType" => UserType::Worker);
        $exceptRs = array(
            'state' => false,
            "code" => Result::FAILURE_CREDENTIAL_INVALID,
            "message" => array('message' => "用户状态错误")
        );
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);
        $this->assertResponseStatusCode(200);
        $this->normalCheck();
        $this->assertMatchedRouteName('Authenticate/default');
        $this->assertActionName("login");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户状态错误");


    }


    /*
     *
     */

    public function testGetCaptchaAction(){

        $this->dispatch('/authenticate/Authenticate/getCaptcha');
        $this->assertModuleName('authenticate');
        $this->assertControllerName('Authenticate\Controller\Authenticate');
        $this->assertControllerClass('AuthenticateController');
        $this->assertMatchedRouteName('authenticate/default');
        $this->assertActionName("getCaptcha");
        $rs = Json::decode($this->getResponse()->getContent());
        $this->assertObjectHasAttribute('state',$rs,"the state of the request for getCaptcha");
        $this->assertObjectHasAttribute('captcha',$rs,"the captcha img of the request for getCaptcha");
        $this->assertEquals(true,$rs->state,"the state should be true only");
    }
    /**
     * @param mixed $ModuleName
     * @return $this
     */
    public function setModuleName($ModuleName)
    {
        $this->_ModuleName = $ModuleName;
        return $this;
    }

    /**
     * @param mixed $ControllerName
     * @return $this
     */
    public function setControllerName($ControllerName)
    {
        $this->_ControllerName = $ControllerName;
        return $this;
    }

    /**
     * @param mixed $ControllerClass
     * @return $this
     */
    public function setControllerClass($ControllerClass)
    {
        $this->_ControllerClass = $ControllerClass;
        return $this;
    }

    private function normalCheck()
    {
        $this->assertModuleName($this->_ModuleName);
        $this->assertControllerName($this->_ControllerName);
        $this->assertControllerClass($this->_ControllerClass);
    }

    private function getResponseDump()
    {
        var_dump(Json::decode($this->getResponse()->getContent()));
    }


    private function generatePassword($username, $password)
    {
        return (new PasswordEncrypt())->getPasswordMd5($username, md5($password));
    }
}
