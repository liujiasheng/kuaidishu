<?php

namespace Authenticate\Controller;

use Application\Model\Regex;
use Authenticate\AssistantClass\ResponseCode;
use Authenticate\AssistantClass\SessionInfoKey;
use Ratchet\Wamp\Exception;
use Zend\Captcha\ReCaptcha;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;


class RegisterController extends AbstractActionController {

    protected $logger;
    public function __construct(){
        $this->logger = new \Application\Model\Logger();
    }

    public function registerPageAction(){
        return array();
    }

    /**
	 * The default action - show the home page
	 */
	public function registerAction() {
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $post_data = $request->getPost();

        if(!array_key_exists("regUserName",$post_data)||
            !array_key_exists("regEmail",$post_data)||
            !array_key_exists("regPWD1",$post_data)||
            !array_key_exists("regCaptcha",$post_data)){
            $response->setContent(\Zend\Json\Json::encode(array(
                'state' => false,
                "code" => ResponseCode::WRONG_ARGUMENT,
                "message" => array('message'=>"参数错误")
            )));
            return $response;
        }
        $regUsername = $post_data['regUserName'];
        $regPwd = $post_data['regPWD1'];
        $regCaptcha = $post_data['regCaptcha'];
        $regEmail = $post_data['regEmail'];

        //TODO to check the captcha

        $session = new \Zend\Session\Container("Captcha");
        $captcha = new \Zend\Captcha\Image();
        $captcha->setTimeout(0);
        $captcha->setExpiration(0);
        $captchaValid = $captcha->isValid(array("id"=>$session->offsetGet(SessionInfoKey::captchaId),"input"=>$regCaptcha));
        if(!$captchaValid){
            $response->setContent(\Zend\Json\Json::encode(array(
                'state' => false,
                'code' => ResponseCode::WRONG_CAPTCHA,
                "message" => array('message'=>"验证码错误")
            )));
            return $response;
        }
        $regex = new Regex();
        $regex->checkUsername($regUsername)
            ->checkPassword($regPwd)
            ->checkEmail($regEmail);

        $code = $regex->getCode();
        $message = $regex->getMessage();
        do{
        //code = 0 no error
        if($code===0){
            //TODO save the user?

            /** @var $userTable \Authenticate\Model\UserTable */
            $userTable = $this->getServiceLocator()->get('Authenticate\Model\UserTable');
            $exist = $userTable->select(array("username" => $regUsername))->current();
            if(!$exist){
                $userTable->saveUser($regUsername,$regPwd,$regEmail);
                $response->setContent(\Zend\Json\Json::encode(array(
                    'state' => true,
                    "message" =>array("message"=>"注册成功")
                )));
            }else{
                $response->setContent(\Zend\Json\Json::encode(array(
                    'state' => false,
                    'code' => ResponseCode::USER_ALREADY_EXIST,
                    "message" => array("message"=>"用户名已存在")
                )));
            }


        }else{
            $response->setContent(\Zend\Json\Json::encode(array(
                'state' => false,
                'code' => 301,
                "message" => array("message"=>$message)
            )));
        }

        }while(false);
        return $response;
	}

    public function checkUserAction(){

        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $post = $request->getPost();
        $username = $post->get("regUserName");

        try{
        $regex = new Regex();
        $regex->checkUsername($username);

        $code = $regex->getCode();
        $message = $regex->getMessage();
            //code = 0 no error
            if($code===0){
                //TODO save the user?

                /** @var $userTable \Authenticate\Model\UserTable */
                $userTable = $this->getServiceLocator()->get('Authenticate\Model\UserTable');
                $exist = $userTable->select(array("username" => $username))->current();
                if($exist){
                    $response->setContent(\Zend\Json\Json::encode(false));
                }else{
                    $response->setContent(\Zend\Json\Json::encode(true));
                }

            }

        }catch (Exception $e){
            $this->logger->err($e->getCode(),$e->getMessage());
            $response->setContent(\Zend\Json\Json::encode(array(
                'state' => false,
                "message" => array("data"=>false)
            )));
        }
        return $response;
    }

    public function checkCaptchaAction(){
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $post = $request->getPost();
        $regCaptcha = $post->get("regCaptcha");

        try{
            $session = new \Zend\Session\Container("Captcha");
            $captcha = new \Zend\Captcha\Image();
            $captchaValid = $captcha->isValid(array("id"=>$session->offsetGet(SessionInfoKey::captchaId),"input"=>$regCaptcha));

                if($captchaValid){
                    $response->setContent(\Zend\Json\Json::encode(true));
                }else{
                    $response->setContent(\Zend\Json\Json::encode(false));
                }
            return $response;

        }catch (Exception $e){
            $this->logger->err($e->getCode(),$e->getMessage());
        }

    }


}