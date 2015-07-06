<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-21
 * Time: 下午8:54
 */

namespace Home\Controller;


use Application\Model\WeChatCallback;
use Application\Model\WeChatCallbackConfig;
use Authenticate\AssistantClass\PasswordEncrypt;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class WeixinController extends AbstractActionController {

    protected $_userTable;
    protected $_weixinUserTable;
    protected $_orderTable;
    protected $_orderDetailTable;

    //微信监听接口
    public function listenAction(){
        $response = $this->getResponse();
        $str = "";
        try{
            $wechat = new WeChatCallback();
            if($wechat->checkSignature()){
                $str = $wechat->responseMsg($this);
            }else{
                $str = "";
            }
        }catch (\Exception $e){

        }
        return $response->setContent($str);
    }

    public function indexAction(){

        return array();
    }

    public function userBindingAction(){
        $request = $this->getRequest();
        $openid = $request->getQuery('oi');
        if(!$openid){
            $this->redirect()->toUrl("/");
        }
        $res = $this->getWeixinUserTable()->select(array(
            "openid" => $openid,
            "type" => 1
        ))->current();
        if($res){
            $this->redirect()->toUrl("/weixin/weixin/userbinded?msg=此微信号已经绑定了一个快递鼠帐号!");
        }

        $this->setRenderer();
        return array();
    }

    public function userUnBindingAction(){
        $request = $this->getRequest();
        $openid = $request->getQuery('oi');
        if(!$openid){
            $this->redirect()->toUrl("/");
        }
        $res = $this->getWeixinUserTable()->select(array(
            "openid" => $openid,
            "type" => 1
        ))->current();
        if(!$res){
            $this->redirect()->toUrl("/weixin/weixin/userbinded?msg=此微信号没有绑定任何一个快递鼠帐号!");
        }

        $this->setRenderer();
        return array();
    }

    public function userBindedAction(){

        $request = $this->getRequest();
        $msg = $request->getQuery("msg");
        $message = "";
        if($msg){
            $message = $msg;
        }

        $this->setRenderer();
        return new ViewModel(array(
            "msg" => $message
        ));
    }

    public function userToBindAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try {
            do {
                if ($request->isPost()) {
                    $data = $request->getPost();
                    $openid = $data["openid"];
                    $username = $data["username"];
                    $password = $data["password"];

                    if($openid == null || $username == null || $password == null){
                        $code = 20001;
                        $message = "参数不全";
                        break;
                    }

                    $auth = new PasswordEncrypt();
                    $user = $this->getUserTable()->getUserByUsername($username);
                    if( !$user || !$auth->verifyPassword($username, $password, $user->getPassword()) ){
                        $code = 20002;
                        $message = "密码错误";
                        break;
                    }

                    //check exist
                    $exist = $this->getWeixinUserTable()->select(array(
                        "openid" => $openid,
                        "type" => 1
                    ))->current();
                    if($exist){
                        $code = 20003;
                        $message = "此微信号已经绑定了一个帐号";
                        break;
                    }
                    $exist = $this->getWeixinUserTable()->select(array(
                        "userid" => $user->getID(),
                        "type" => 1
                    ))->current();
                    if($exist){
                        $code = 20004;
                        $message = "此帐号已经绑定了一个微信号";
                        break;
                    }

                    $rsl = $this->getWeixinUserTable()->insert(array(
                        "UserID" => $user->getID(),
                        "OpenID" => $openid,
                        "Type" => 1,
                    ));
                    if(!$rsl){
                        $code = 20005;
                        $message = "绑定失败";
                        break;
                    }


                    $response->setContent(Json::encode(array(
                        "state" => true,
                    )));
                }
            }while(false);
        }catch (\Exception $ex){

        }
        if($code != 0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message
            )));
        }
        return $response;
    }

    public function userToUnBindAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try {
            do {
                if ($request->isPost()) {
                    $data = $request->getPost();
                    $openid = $data["openid"];

                    if($openid == null ){
                        $code = 20001;
                        $message = "参数不全";
                        break;
                    }

                    $rsl = $this->getWeixinUserTable()->delete(array(
                        "openid" => $openid,
                        "type" => 1
                    ));

                    if(!$rsl){
                        $code = 20005;
                        $message = "解除绑定失败";
                        break;
                    }


                    $response->setContent(Json::encode(array(
                        "state" => true,
                    )));
                }
            }while(false);
        }catch (\Exception $ex){

        }
        if($code != 0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message
            )));
        }
        return $response;
    }

    public function testAction(){
        $response = $this->getResponse();


        return $response;
    }


    private function setRenderer(){
        $this->layout('layout/WeixinLayout');
        $baseUrl = $this->getRequest()->getBaseUrl();
        $renderer = $this->getServiceLocator()->get( 'Zend\View\Renderer\PhpRenderer');
        $renderer->headScript()->prependFile($baseUrl . '/js/weixin/userbinding.js')
            ->prependFile($baseUrl . '/js/md5.js');
    }


    /**
     * @return \User\Model\UserTable
     */
    public function getUserTable() {
        if(!$this->_userTable) {
            $t = $this->getServiceLocator();
            $this->_userTable = $t->get('User\Model\UserTable');
        }
        return $this->_userTable;
    }

    /**
     * @return \Home\Model\WeixinUserTable
     */
    public function getWeixinUserTable() {
        if(!$this->_weixinUserTable) {
            $t = $this->getServiceLocator();
            $this->_weixinUserTable = $t->get('Home\Model\WeixinUserTable');
        }
        return $this->_weixinUserTable;
    }

    /**
     * @return \Home\Model\OrderTable
     */
    public function getOrderTable()
    {
        if (!$this->_orderTable) {
            $t = $this->getServiceLocator();
            $this->_orderTable = $t->get('Home\Model\OrderTable');
        }
        return $this->_orderTable;
    }

    /**
     * @return \Home\Model\OrderDetailTable
     */
    public function getOrderDetailTable()
    {
        if (!$this->_orderDetailTable) {
            $t = $this->getServiceLocator();
            $this->_orderDetailTable = $t->get('Home\Model\OrderDetailTable');
        }
        return $this->_orderDetailTable;
    }
} 