<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Authenticate for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Authenticate\Controller;


//require_once getcwd().'/API/QQAPI/qqConnectAPI.php';
use Application\Model\CopUserType;
use Application\Model\Regex;
use Authenticate\AssistantClass\PasswordEncrypt;
use Authenticate\AssistantClass\ResponseCode;
use Authenticate\AssistantClass\SessionInfoKey;
use Authenticate\AssistantClass\UserType;
use Authenticate\Model\CopLoginTable;
use Authenticate\Model\UserTable;
use QC;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Authentication\Storage\Session;
use Zend\Http\Request;
use Zend\Log\Logger;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;



class AuthenticateController extends AbstractActionController
{

    protected $auth;
    protected $logger;
    public function __construct(){
        $this->logger = new \Application\Model\Logger();
    }
    /**
     * @return \Zend\Captcha\Image
     */
    public function getCaptcha(){
        return $this->getServiceLocator()->get('Authenticate\getCaptcha');
    }
    public function indexAction()
    {
        //top navbar
        $fw = $this->forward();
        $r = $fw->dispatch('Application\Controller\Plugin',array(
            "action" => "topnavbar",
        ));
        $this->layout()->setVariable("topNavbar",$r);
        return new ViewModel(array("hrefs"=>array(
            'register',
            'adminlogin',
            'userlogin',
            'workerlogin',
            'sellerlogin')));
    }




    public function adminLoginPageAction(){
        if($this->getAuthenticationService()->hasIdentity()){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl()."/admin/user");
        }
        $this->loadCommonSection();

    }
    public function userLoginPageAction(){
        if($this->getAuthenticationService()->hasIdentity()){
            return $this->redirect()->toRoute('home');
        }

        $this->loadCommonSection();

    }
    public function workerLoginPageAction(){
        if($this->getAuthenticationService()->hasIdentity()){
            $this->redirect()->toUrl($this->getRequest()->getBaseUrl()."/");
        }
        $this->loadCommonSection();
    }
    public function sellerLoginPageAction(){
        //TODO redirect to proper route
        if($this->getAuthenticationService()->hasIdentity()){
            return $this->redirect()->toRoute('sellerGoodsMgr');
        }
        $this->loadCommonSection();
    }
    public function loginAction(){
        $request = $this->getRequest();
        $response = $this->getResponse();
        $post_data = $request->getPost();


        if(!array_key_exists("loginUserName",$post_data)||
        !array_key_exists("loginPWD",$post_data)||
        !array_key_exists("userType",$post_data)){
            $response->setContent(\Zend\Json\Json::encode(array(
                'state' => false,
                "code" => -1,
                "message" => array('message'=>"参数错误")
            )));
            return $response;
        }
        $username = $post_data['loginUserName'];
        $password = $post_data['loginPWD'];
        $userType = $post_data['userType'];



        // Set up the authentication adapter
        $authAdapter = new \Authenticate\AssistantClass\CommonAuthenticateAdapter($username, $password,$userType,$this->getServiceLocator());

        // inside of AuthController / loginAction
        $result = $this->getAuthenticationService()->authenticate($authAdapter);

        switch ($result->getCode()) {

            case Result::FAILURE_IDENTITY_NOT_FOUND:
                $response->setContent(\Zend\Json\Json::encode(array(
                    'state' => false,
                    "code" => $result->getCode(),
                    "message" => $result->getMessages()
                )));
                return $response;
                break;

            case Result::FAILURE_CREDENTIAL_INVALID:
            case Result::FAILURE:
                $response->setContent(\Zend\Json\Json::encode(array(
                    'state' => false,
                    "code" => $result->getCode(),
                    "message" => $result->getMessages()
                )));
                return $response;
                break;

            case Result::SUCCESS:
                //TODO To store the info of the user
                $response->setContent(\Zend\Json\Json::encode(array(
                    'state' => true,
                    "message" => $result->getMessages()
                )));
                return $response;
                break;


            default:
                $response->setContent(\Zend\Json\Json::encode(array(
                    'state' => false,
                    "code" => $result->getCode(),
                    "message" => '系统错误'
                )));
                return $response;
                break;
        }
    }


    public function logoutAction(){

        try{
            $response = $this->getResponse();
            $this->getAuthenticationService()->clearIdentity();
            unset($_SESSION["Authenticate"]);
                $response->setContent(\Zend\Json\Json::encode(array(
                    'state' => true,
                    "message" => array("message"=>"退出成功")
                )));
//            return $this->redirect()->toRoute('home');
            return $this->redirect()->toRoute('userlogin');
        }catch(\Exception $e){
            $this->logger->err($e->getCode(),$e->getMessage());
        }

    }




    public function getCaptchaAction(){
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $captcha = $this->getCaptcha();
        $response->setContent(\Zend\Json\Json::encode(array(
            "state"=>true,
            "captcha"=>$captcha->getImgUrl().$captcha->getId().$captcha->getSuffix(),
        )));
        return $response;
    }


    /**
     * @return AuthenticationService
     */
    public function getAuthenticationService(){

        if(!$this->auth){

        $this->auth = new AuthenticationService();
            //Session name Authenticate
        $this->auth->setStorage(new Session("Authenticate"));

        }
        return $this->auth;
    }

    private function loadCommonSection()
    {
        $baseUrl = $this->getRequest()->getBaseUrl();
        /** @var $renderer PhpRenderer */
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
        $renderer->inlineScript()->prependFile($baseUrl . '/js/authenticateModule.js');
        //top navbar
        $fw = $this->forward();
        $r = $fw->dispatch('Application\Controller\Plugin',array(
            "action" => "topnavbar",
        ));

        $this->layout()->setVariable("topNavbar",$r);
    }


    public function loginQQAction(){
        if(!(isset($_GET["code"])&&isset($_GET["state"]))){
            return $this->redirect()->toRoute('home');
        }
        //通过腾讯的callback请求获取openid

        $qc = new QC();
        $qc->qq_callback();
        $openid = $qc->get_openid();
        $accessToken = $qc->get_access_token();
        $qcSession = new Container('QC');
        $qcSession->offsetSet('openid',$openid);
        $qcSession->offsetSet('access_token',$accessToken);
        $this->logger->info(0,"here get qq openid=".$openid." and access_token=".$accessToken);
        /** @var $table CopLoginTable */
        $table = $this->getServiceLocator()->get('Authenticate\Model\CopLoginTable');
        $rs = $table->checkOpenId($openid);
//        $this->logger->info(0,$rs==null?"checkOpenId and return null":"checkOpenId and return an array");
        if($rs!=null){//如果openid已经绑定，则直接登陆用户
            /** @var $userTable UserTable */
            $userTable = $this->getServiceLocator()->get('Authenticate\Model\UserTable');
            $userEntity = $userTable->getUserByUsername($rs['UserName']);
            $identity = array();
            $identity[SessionInfoKey::role] = UserType::User;
            $identity[SessionInfoKey::ID] = $userEntity->getID();
//            $this->logger->info(0,"user login ,userID=".$userEntity->getID());
            $userTable->update(array("LoginIP"=>$this->GetIP(),"LoginTime"=>(new \DateTime())->format("Y-m-d H:i:s")),array("id" => $userEntity->getID()));
            //Session name Authenticate
            $authSvr = new AuthenticationService();
            $authSvr->setStorage(new Session("Authenticate"));
            $authSvr->getStorage()->write($identity);
            $session = new Container('Redirect');
            $redirectURL = $session->offsetGet('redirectURL');
            if($redirectURL!==null){
                $session->offsetSet('redirectURL',null);
                return $this->redirect()->toUrl(urldecode($redirectURL));

            }

            return $this->redirect()->toRoute('home');
        }else{ //如果openid没有绑定，去到绑定页面，和新注册的账号绑定或者和已有账号绑定
//            $this->logger->info(0,"user no bind qq account,access_token".$qcSession->offsetGet('access_token')." openid".$qcSession->offsetGet('openid'));
            $qc = new QC($qcSession->offsetGet('access_token'),$qcSession->offsetGet('openid'));
            $data = $qc->get_user_info();
            if($data["ret"]==0){
                $nickname = $data["nickname"];
            }else{
                $this->logger->err(-1,"get qq user nickname fail");
                return $this->errorPage("出错啦！");
            }
            $prefix = "kds_";
            //loop a name not exist in the database
            /** @var $userTable \Authenticate\Model\UserTable */
            $userTable = $this->getServiceLocator()->get('Authenticate\Model\UserTable');

            //检查账号是否已经绑定
            do{
                $name =  substr($prefix.round(microtime(true) * 100),0,16);
                $exist = $userTable->select(array("username" => $name))->current();
            }while($exist);
            $username = $name;
            if($nickname==""){
                $nickname = $username;
            }
            $userTable->saveCopUser($username,"","",$nickname);
            /** @var $copTable CopLoginTable */
            $copTable = $this->getServiceLocator()->get('Authenticate\Model\CopLoginTable');
            $this->logger->info(0,"here get qq user openid=".$qcSession->offsetGet('openid')." and token ".$qcSession->offsetGet('access_token'));
            $copTable->saveCopUser($username,$qcSession->offsetGet('openid'),CopUserType::QQ);
            $userEntity = $userTable->getUserByUsername($username);
            $identity = array();
            $identity[SessionInfoKey::role] = UserType::User;
            $identity[SessionInfoKey::ID] = $userEntity->getID();
            $userTable->update(array("LoginIP"=>$this->GetIP(),"LoginTime"=>(new \DateTime())->format("Y-m-d H:i:s")),array("id" => $userEntity->getID()));
            //Session name Authenticate
            $authSvr = new AuthenticationService();
            $authSvr->setStorage(new Session("Authenticate"));
            $authSvr->getStorage()->write($identity);
            $session = new Container('Redirect');
            $redirectURL = $session->offsetGet('redirectURL');
            if($redirectURL!==null){
                $session->offsetSet('redirectURL',null);
                return $this->redirect()->toUrl(urldecode($redirectURL));

            }
            $this->redirect()->toRoute('home');
        }
    }

    public function bindPageAction(){
        $qc = new QC();
        if(isset($qc->keysArr["openid"])&&$qc->keysArr["openid"]!=null){
            return array();
        }else{
            return $this->redirect()->toRoute('home');
        }
        //生成绑定页面，openid不能公开


    }

    public function bindNewUserAction(){


        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $post = $request->getPost();
        $email = $post->get('regEmail');
        $pwd = $post->get('regPWD1');
        if(null==$email||null==$pwd){
            return $this->errorPage("出错啦！");
        }
        $regex = new Regex();
        $regex->checkEmail($email)
            ->checkPassword($pwd);

        $code = $regex->getCode();
        $message = $regex->getMessage();
        //如果正确
        if($code===0){
            //生成一个username
            $qc = new QC();
            $prefix = "kds_";
            //loop a name not exist in the database
            /** @var $userTable \Authenticate\Model\UserTable */
            $userTable = $this->getServiceLocator()->get('Authenticate\Model\UserTable');

            //检查账号是否已经绑定
            do{
                $name =  substr($prefix.round(microtime(true) * 100),0,16);
                $regex->flush()->checkUsername($name);
                if($regex->getCode()!=0){
                    return $this->errorPage("系统出错了！");
                }
                $exist = $userTable->select(array("username" => $name))->current();
            }while($exist);
                $username = $name;
                $userTable->saveUser($username,$pwd,$email);
                /** @var $copTable CopLoginTable */
                $copTable = $this->getServiceLocator()->get('Authenticate\Model\CopLoginTable');
                $copTable->saveCopUser($username,$qc->get_openid(),CopUserType::QQ);

                $userTable = $this->getServiceLocator()->get('Authenticate\Model\UserTable');
                $userEntity = $userTable->getUserByUsername($username);
                $identity = array();
                $identity[SessionInfoKey::role] = UserType::User;
                $identity[SessionInfoKey::ID] = $userEntity->getID();
                //Session name Authenticate
                $authSvr = new AuthenticationService();
                $authSvr->setStorage(new Session("Authenticate"));
                $authSvr->getStorage()->write($identity);
                $this->redirect()->toRoute('userinfo');


        }else{//如果错误
            //提示账号密码错误
            $response->setContent(\Zend\Json\Json::encode(array(
            'state' => false,
                "code" => $code,
                "message" => array('message'=>$message)
            )));
            return $response;
        }

    }

    public function bindUserAction(){
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $post = $request->getPost();
        $username = $post->get('userName');
        $pwd = $post->get('pwd');
        $cap = $post->get('bindCaptcha');
        $session = new \Zend\Session\Container("Captcha");
        $captcha = new \Zend\Captcha\Image();
        $captcha->setTimeout(0);
        $captcha->setExpiration(0);
        $captchaValid = $captcha->isValid(array("id"=>$session->offsetGet(SessionInfoKey::captchaId),"input"=>$cap));

        if(!$captchaValid){
            return $this->errorPage("验证码错误");
        }
        //检查账号密码是否正确
        /** @var $userTable UserTable */
        $userTable = $this->getServiceLocator()->get('Authenticate\Model\UserTable');
        $userEntity = $userTable->getUserByUsername($username);
        if(!$userEntity){
            return $this->errorPage("账号或密码错误");
        }
        if(!(new PasswordEncrypt())->verifyPassword($username,$pwd,$userEntity->__get("Password"))){
            return $this->errorPage("账号或密码错误");
        }
        //过程：检查用户是否已经被绑定
        /** @var $copTable CopLoginTable */
        $copTable = $this->getServiceLocator()->get('Authenticate\Model\CopLoginTable');
        $qc = new QC();
        $openid = $qc->get_openid();
        $isBind = $copTable->checkOpenId($openid);
        //如果已经被绑定，则提示用户已绑定
        if($isBind){
            return $this->errorPage("该QQ已经被绑定了！");
        }else{
            //如果没有被绑定，则直接和qq绑定

            $last = $copTable->saveCopUser($username,$openid,CopUserType::QQ);
            if($last){
                //提示已经绑定，请用账号登陆
                $identity = array();
                $identity[SessionInfoKey::role] = UserType::User;
                $identity[SessionInfoKey::ID] = $userEntity->getID();
                //Session name Authenticate
                $authSvr = new AuthenticationService();
                $authSvr->setStorage(new Session("Authenticate"));
                $authSvr->getStorage()->write($identity);
                $this->redirect()->toRoute('userinfo');
            }else{
                return $this->errorPage("系统错误，绑定失败了。");
            }

        }
    }

    public function oauthQQAction(){


        /** @var $request Request */
        $request = $this->getRequest();
        $qc = new QC();
        $qc->qq_login();
        $session = new Container('Redirect');
        $redirect = $request->getQuery('redirect');
        $session->offsetSet('redirectURL',$redirect);
        $view = new ViewModel();
        $view->setTerminal(true);
        return $view;
    }

    private function errorPage($message)
    {
        $view = new ViewModel();
        $view->setTemplate("errorTemplate");
        $view->setVariable('message',$message);
        return $view;
    }

    function GetIP(){
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return($ip);
    }
}
