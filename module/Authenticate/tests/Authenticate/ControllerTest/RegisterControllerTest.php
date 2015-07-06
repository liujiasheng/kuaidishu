<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-7-11
 * Time: 下午4:04
 */

namespace AuthenticateTest\ControllerTest;


use Authenticate\AssistantClass\PasswordEncrypt;
use Authenticate\AssistantClass\ResponseCode;
use Authenticate\Model\UserTable;
use Zend\File\Transfer\Adapter\Http;
use Zend\Http\Response;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class RegisterControllerTest extends AbstractHttpControllerTestCase {

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
        $_SERVER['CONTEXT_DOCUMENT_ROOT'] = 'G:/zendLearn/kuaidishu';
        $_SERVER['Captcha_Path'] = 'G:/zendLearn/kuaidishu';
        $this->_serviceLocator = $this->getApplicationServiceLocator();
        $this->_dbAdapter = $this->_serviceLocator->get('Zend\Db\Adapter\Adapter');
        $this->setModuleName('authenticate');
        $this->setControllerName('Authenticate\Controller\Authenticate');
        $this->setControllerClass('AuthenticateController');
        $this->_ids = array();

        parent::setUp();
    }

    public function tearDown()
    {
        $table = new UserTable($this->_dbAdapter);
        $table->delete(array('Username'=>'kuaidishu'));

    }

    public function testRegisterAction(){


        $exceptRs = array(
            'state' => false,
            'code' => ResponseCode::WRONG_ARGUMENT,
            "message" => array("message"=>'参数错误'));
        $this->dispatch('/authenticate/Register/register');
        $this->assertModuleName('authenticate');
        $this->assertControllerName('Authenticate\Controller\register');
        $this->assertControllerClass('registercontroller');
        $this->assertMatchedRouteName('authenticate/default');
        $this->assertActionName("register");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "参数错误");
        $this->dispatch('/authenticate/Authenticate/getCaptcha');

        $captcha = 1234;
        $input = array(
            'regUserName'=>$username = "kuaidishu",
            'regPWD1'=>md5($username),
            'regEmail'=>$username.'@qq.com',
            'regCaptcha'=>$captcha);
        $exceptRs = array(
            'state' => false,
            'code' => ResponseCode::WRONG_CAPTCHA,
            "message" => array("message"=>'验证码错误'));
        $this->dispatch('/authenticate/Register/register','POST',$input);
        $this->assertModuleName('authenticate');
        $this->assertControllerName('Authenticate\Controller\register');
        $this->assertControllerClass('registercontroller');
        $this->assertMatchedRouteName('authenticate/default');
        $this->assertActionName("register");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "验证码错误");

        $session = new \Zend\Session\Container("Authenticate");
        $captcha = $session->offsetGet('captcha');
        $input = array(
            'regUserName'=>$username = "kuaidishu",
            'regPWD1'=>md5($username),
            'regEmail'=>$username.'@qq.com',
            'regCaptcha'=>$captcha);
        $exceptRs = array(
            'state' => true,
            "message" => array("message"=>'注册成功'));
        $this->dispatch('/authenticate/Register/register','POST',$input);
        $this->assertModuleName('authenticate');
        $this->assertControllerName('Authenticate\Controller\register');
        $this->assertControllerClass('registercontroller');
        $this->assertMatchedRouteName('authenticate/default');
        $this->assertActionName("register");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "注册成功");

        $session = new \Zend\Session\Container("Authenticate");
        $captcha = $session->offsetGet('captcha');
        $input = array(
            'regUserName'=>$username = "kuaidishu",
            'regPWD1'=>md5($username),
            'regEmail'=>$username.'@qq.com',
            'regCaptcha'=>$captcha);
        $exceptRs = array(
            'state' => false,
            'code'=>ResponseCode::USER_ALREADY_EXIST,
            "message" => array("message"=>'用户名已存在'));
        $this->dispatch('/authenticate/Register/register','POST',$input);
        $this->assertModuleName('authenticate');
        $this->assertControllerName('Authenticate\Controller\register');
        $this->assertControllerClass('registercontroller');
        $this->assertMatchedRouteName('authenticate/default');
        $this->assertActionName("register");
        $this->assertEquals(Json::encode($exceptRs), $this->getResponse()->getContent(), "用户名已存在");
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->_ModuleName;
    }

    /**
     * @param mixed $ModuleName
     */
    public function setModuleName($ModuleName)
    {
        $this->_ModuleName = $ModuleName;
    }

    /**
     * @return mixed
     */
    public function getControllerName()
    {
        return $this->_ControllerName;
    }

    /**
     * @param mixed $ControllerName
     */
    public function setControllerName($ControllerName)
    {
        $this->_ControllerName = $ControllerName;
    }

    /**
     * @return mixed
     */
    public function getControllerClass()
    {
        return $this->_ControllerClass;
    }

    /**
     * @param mixed $ControllerClass
     */
    public function setControllerClass($ControllerClass)
    {
        $this->_ControllerClass = $ControllerClass;
    }

    private function generatePassword($username, $password)
    {
        return (new PasswordEncrypt())->getPasswordMd5($username, md5($password));
    }
    private function getResponseDump()
    {
        var_dump(Json::decode($this->getResponse()->getContent()));
    }
}
 