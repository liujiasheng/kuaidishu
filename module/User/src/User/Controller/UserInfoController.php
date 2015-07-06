<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-7-8
 * Time: 下午8:40
 */

namespace User\Controller;

use Application\Entity\DeliveryAddress;
use Application\Entity\User;
use Application\Model\DomainList;
use Application\Model\Regex;
use Authenticate\AssistantClass\CommonAuthenticateAdapter;
use Authenticate\AssistantClass\PasswordEncrypt;
use Authenticate\AssistantClass\SessionInfoKey;
use Authenticate\AssistantClass\UserType;
use Authenticate\Model\UserTable;
use User\Model\DeliveryAddressTable;
use User\Model\UserCommon;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session;
use Zend\Db\Sql\Where;
use Zend\Http\Request;
use Zend\Json\Json;
use Zend\Json\Server\Cache;
use Zend\Mail\Storage;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Service\ViewHelperManagerFactory;
use Zend\View\Helper\InlineScript;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class UserInfoController extends AbstractActionController
{

    protected $logger;

    public function __construct()
    {
        $this->logger = new \Application\Model\Logger();
    }



    public function userInfoPageAction(){
        $view = $this->initBase();
        $content = $this->userInfoPageContentAction();
        $view->addChild($content,'content');
        return $view;
    }

    public function userPwdPageAction(){
        $view = $this->initBase();
        $content = $this->userPwdPageContentAction();
        $view->addChild($content,'content');
        return $view;
    }

    public function userDeliveryAddressPageAction(){
        $view = $this->initBase();
        $content = $this->userDeliveryAddressPageContentAction();
        $view->addChild($content,'content');
        return $view;
    }



    private function localNavView($links){


        $view = new ViewModel();
        $view->setVariable('links',$links);
        $view->setTemplate('localNavTemplate');
        return $view;

    }

    public function footerAction(){

                /** @var $viewRender phpRenderer */
        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $view = new ViewModel();
        $view->setTemplate('UserModuleFooterTemplate');
        $view->setTerminal(true);
        $html =$viewRender->render($view);
        return $html;
    }

    public function indexAction()
    {
//
        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');
        if (!$authSvr->hasIdentity()) {
            $this->redirect()->toRoute('userlogin');
        }

        $menu = (new UserCommon())->getUserInfoMenu();
        $view = new ViewModel(array(
            "menu" => $menu,
        ));
        //top navbar
        $fw = $this->forward();
        $r = $fw->dispatch('Application\Controller\Plugin', array(
            "action" => "topnavbar",
        ));
        $this->layout()->setVariable("topNavbar", $r);
        return $view;
    }

//页面跳转和页面数据初始化
    public function userinfoAction()
    {

        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');
        if (null != ($identified = $authSvr->getIdentity())) {
//            if(isset($identified['role'])&&$identified['role'])
            /** @var $userEntity User */
            $userEntity = $this->getUserTable()->getUserById($identified[SessionInfoKey::ID]);
            if(!$userEntity){
                return $this->redirect()->toRoute('home');
            }
            $username = $userEntity->getUsername();
            $email = $userEntity->getEmail();
            $phone = $userEntity->getPhone();
            $nickname = $userEntity->getNickname();
        }else{
            return $this->redirect()->toRoute('home');
        }
        $menu = (new UserCommon())->getUserInfoMenu();
        $view = new ViewModel(array(
            "menu" => $menu,
            'userinfo' => array(
                'username' => $username,
                'nickname' => $nickname,
                'email' => $email,
                'phone' => $phone
            )));
        $view->setTemplate('UserMainPageTemplate');
        $view->setTerminal(true);

        $renderer = $this->getServiceLocator()->get(
            'Zend\View\Renderer\PhpRenderer');
        $renderer->headTitle('Head Title');


            // Only add the script in the "production" environment.
        /** @var $script InlineScript */
        $script = $this->getServiceLocator()->get('viewhelpermanager')
                ->get('inlineScript');
            // No need to add beginning or ending <script> tags, as they
            // will be automatically inserted by the appendScript method.
        $script->prependFile($this->getRequest()->getBaseUrl() . '/js/somejs.js'); // Disable CDATA comments



        return $view;
    }

    public function modifypwdAction()
    {
        $view = new ViewModel();
        $view->setTemplate('UserModifyPasswordTemplate');
        $view->setTerminal(true);
        return $view;
    }

    public function deliveryAction()
    {
        $view = new ViewModel();
        $view->setTemplate('UserDeliveryAddressTemplate');
        $view->setTerminal(true);
        return $view;
    }
//各个页面的功能实现
//个人信息修改
    public function updateuserinfoAction()
    {

        /* @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();

        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');
        $identified = $authSvr->getIdentity();

        if (!isset($identified[SessionInfoKey::role]) && $identified[SessionInfoKey::role] !== UserType::User) {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => -1,
                'message' => array(
                    'message' => "该接口仅供用户修改个人信息使用"
                )
            )));
            return $response;
        }
        $post = $request->getPost();
        $nickName = $post->get('nickName');
        $email = $post->get('email');
        $phone = $post->get('phone');
        if ($nickName == null || $email == null) {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => -6,
                'message' => array(
                    'message' => "参数错误"
                )
            )));
            return $response;
        }
        $rs = (new Regex())
            ->checkNickname($nickName)
            ->checkEmail($email);
        if ($phone != null)
            $rs->checkPhone($phone);
        $code = $rs->getCode();
        $message = $rs->getMessage();
        if ($code != 0) {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => -2,
                'message' => array(
                    'message' => $message
                )
            )));
        } else {
            /** @var $userEntity User */
            $userEntity = $this->getUserTable()->getUserById($identified[SessionInfoKey::ID]);
            $username = $userEntity->getUsername();
            $userId = $userEntity->getID();

            /** @var $userTable \Authenticate\Model\UserTable */
            $userTable = $this->getServiceLocator()->get('Authenticate\Model\UserTable');

            $updateValue = array();
            $updateValue['Nickname'] = $nickName;
            $updateValue['Phone'] = $phone;
            $updateValue['Email'] = $email;
            $result = $userTable->update($updateValue, array('id' => $userId));
            if ($result != 1) {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => -3,
                    'message' => array(
                        'message' => '信息没有更新'
                    )
                )));
                return $response;
            }

            $response->setContent(Json::encode(array(
                'state' => true,
                'message' => array(
                    'message' => '保存信息成功'
                )
            )));
        }


        return $response;
    }

    public function updatePasswordAction()
    {

        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $post = $request->getPost();
        $oripwd = $post->get('oripwd');
        $newpwd = $post->get('newpwd');



        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');
        $identified = $authSvr->getIdentity();
        if ($identified == null) {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => -2,
                'message' => array(
                    'message' => '请先登录再操作'
                )
            )));
            return $response;
        }
        /** @var $userEntity User */
        $userEntity = $this->getUserTable()->getUserById($identified[SessionInfoKey::ID]);
        if ($oripwd == null || $newpwd !== null) {
            if($userEntity->getPassword()===""){
                $updateValue = array();
                $updateValue['Password'] = (new PasswordEncrypt())->getPasswordMd5($userEntity->getUsername(), $newpwd);
                $result = $this->getUserTable()->update($updateValue, array('id' => $identified[SessionInfoKey::ID]));
                if ($result != 1) {
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => -6,
                        'message' => array(
                            'message' => '系统错误'
                        )
                    )));
                    return $response;
                }
                $response->setContent(Json::encode(array(
                    'state' => true,
                    'message' => array(
                        'message' => '密码修改成功'
                    )
                )));
                return $response;
            }else{
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => -1,
                    'message' => array(
                        'message' => '缺少参数'
                    )
                )));
                return $response;
            }
        }
        if ($oripwd == null || $newpwd == null) {
        $response->setContent(Json::encode(array(
            'state' => false,
            'code' => -1,
            'message' => array(
                'message' => '缺少参数'
            )
        )));
        return $response;
    }


        if (!(new PasswordEncrypt())->verifyPassword($userEntity->getUsername(),$oripwd,$userEntity->getPassword())) {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => -3,
                'message' => array(
                    'message' => '原密码错误'
                )
            )));
            return $response;
        }
        if ($oripwd == $newpwd) {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => -4,
                'message' => array(
                    'message' => '新密码不能和原密码相同'
                )
            )));
            return $response;
        }


        $reg = new Regex();
        $reg->checkPassword($newpwd);
        $code = $reg->getCode();
        if ($code != 0) {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => -5,
                'message' => array(
                    'message' => '新密码不符合格式'
                )
            )));
        } else {

            /** @var $userEntity User */
            $userEntity = $this->getUserTable()->getUserById($identified[SessionInfoKey::ID]);
            $userId = $userEntity->getID();
            $username = $userEntity->getUsername();
            /** @var $userTable \Authenticate\Model\UserTable */
            $userTable = $this->getServiceLocator()->get('Authenticate\Model\UserTable');
            $updateValue = array();
            $updateValue['Password'] = (new PasswordEncrypt())->getPasswordMd5($username, $newpwd);
            $result = $userTable->update($updateValue, array('id' => $userId));
            if ($result != 1) {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => -6,
                    'message' => array(
                        'message' => '系统错误'
                    )
                )));
                return $response;
            }
            //TODO update the identify
            $password = $newpwd;
            $userType = $identified[SessionInfoKey::role];
            //更新用户登陆信息
            $authAdapter = new CommonAuthenticateAdapter($username, $password, $userType, $this->getServiceLocator());
            $authSvr->authenticate($authAdapter);
            $response->setContent(Json::encode(array(
                'state' => true,
                'message' => array(
                    'message' => '密码修改成功'
                )
            )));
        }


        return $response;
    }

    public function getDeliveryAddressAction()
    {
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $post = $request->getPost();
        /** @var $deliveryAddressTable DeliveryAddressTable */
        $deliveryAddressTable = $this->getServiceLocator()->get('User\Model\DeliveryAddressTable');

        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');

        if (!$authSvr->hasIdentity()) {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => 10001,
                'message' => array(
                    'message' => '请先登录再操作'
                )
            )));
            return $response;
        }
        $identified = $authSvr->getIdentity();
        /** @var $userEntity User */
        $userId = $identified[SessionInfoKey::ID];

        $id = $post->get('id');
        if ($post->count()) { //带参数
            $rows = $deliveryAddressTable->getDeliveryAddressById($id, $userId);
        } else { //不带参数
            $rows = $deliveryAddressTable->getAddressByUserId($userId);
        }

        if (!$rows) {
            $response->setContent(Json::encode(array(
                'state' => true,
                'message' => array(
                    'message' => '列表为空',
                    'list' => array()
                )
            )));
            return $response;
        } else {
            $response->setContent(Json::encode(array(
                'state' => true,
                'message' => array(
                    'message' => '获取收获地址数据成功',
                    'list' => $rows
                )
            )));
            return $response;
        }
    }

    public function addDeliveryAddressAction()
    {

        try {

            /** @var $request Request */
            $request = $this->getRequest();
            $response = $this->getResponse();
            $post = $request->getPost();

            /** @var $authSvr AuthenticationService */
            $authSvr = $this->getServiceLocator()->get('Session');
            $identified = $authSvr->getIdentity();
            if ($identified == null) {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => 10001,
                    'message' => array(
                        'message' => '请先登录再操作'
                    )
                )));
                return $response;
            }

            /** @var $userEntity User */
            $userId = $identified[SessionInfoKey::ID];
            $domain = $post->get('Domain');
            $domain2 = $post->get('Domain2');
            $domain3 = $post->get('Domain3');
            $address = $post->get('Address');
            $phone = $post->get('Phone');
            $userName = $post->get('Name');

            if ($domain == null || trim($domain) == '') {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => 10011,
                    'message' => array(
                        'message' => '地域不能为空'
                    )
                )));
                return $response;
            }
            if ($address == null || trim($address) == '') {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => 10012,
                    'message' => array(
                        'message' => '地址不能为空'
                    )
                )));
                return $response;
            }
            $reg = new Regex();
            $reg->checkLocationAddress($address);
            if ($reg->getCode() != 0) {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => 10019,
                    'message' => array(
                        'message' => '所在地址格式错误'
                    )
                )));
                return $response;
            }
            $reg->flush();
            $reg->checkLocationAddress($domain2);
            if($reg->getCode()!=0){
                $response->setContent(Json::encode(array(
                    'state'=>false,
                    'code'=>10020,
                    'message'=>array(
                        'message'=>'区域地址格式错误'
                    )
                )));
                return $response;
            }
            if ($phone == null || trim($phone) == '') {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => 10013,
                    'message' => array(
                        'message' => '电话不能为空'
                    )
                )));
                return $response;
            }
            $reg->flush();
            $reg->checkPhone($phone);
            if ($reg->getCode() != 0) {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => 10014,
                    'message' => array(
                        'message' => '电话格式错误'
                    )
                )));
                return $response;
            }

            if ($userName == null || trim($userName) == '') {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => 10015,
                    'message' => array(
                        'message' => '收货人不能为空'
                    )
                )));
                return $response;
            }


            $reg->flush();
            $reg->checkRealName($userName);
            if ($reg->getCode() != 0) {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => 10016,
                    'message' => array(
                        'message' => '收货人名称只能是中文、字母和下划线'
                    )
                )));
                return $response;
            }
            //校验完毕

            $domainList = new DomainList();
            $number = $domainList->has($domain,$domain2,$domain3);
            if (!$number) {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => 10017,
                    'message' => array(
                        'message' => '所选地域不存在'
                    )
                )));
                return $response;
            }
            switch($number){
                case 0:
                    $domain = "";
                case 1:
                    $domain2 = "";
                case 2:
                    $domain3 = "";
                default:
                    break;
            }

            /** @var $deliveryAddressTable DeliveryAddressTable */
            $deliveryAddressTable = $this->getServiceLocator()->get('User\Model\DeliveryAddressTable');
            $value = new DeliveryAddress();
            $value->setUserID($userId)
                ->setName($userName)
                ->setDomain($domain)
                ->setDomain2($domain2)
                ->setDomain3($domain3)
                ->setAddress($address)
                ->setPhone($phone);
            $result = $deliveryAddressTable->create($value);
            if (!$result) {
                $response->setContent(Json::encode(array(
                    'state' => false,
                    'code' => 10018,
                    'message' => array(
                        'message' => '添加失败'
                    )
                )));
                return $response;
            } else {
                $response->setContent(Json::encode(array(
                    'state' => true,
                    'id' => $deliveryAddressTable->getLastInsertValue(),
                    'message' => array(
                        'message' => '保存成功'
                    )
                )));
                return $response;
            }


        } catch (\Exception $e) {
            $this->logger->info($e->getCode(), 'UserInfoController' . $e->getMessage());

        }

    }

    public function updateDeliveryAddressAction(){
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $post = $request->getPost();
        $code = 0;
        $message = "";
        try {
            do{
                /** @var $authSvr AuthenticationService */
                $authSvr = $this->getServiceLocator()->get('Session');
                $identified = $authSvr->getIdentity();
                if ($identified == null) {
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 10001,
                        'message' => array(
                            'message' => '请先登录再操作'
                        )
                    )));
                    return $response;
                }

                /** @var $userEntity User */
                $userId = $identified[SessionInfoKey::ID];
                $addrID = $post->get('ID');
                $domain = $post->get('Domain');
                $domain2 = $post->get('Domain2');
                $domain3 = $post->get('Domain3');
                $address = $post->get('Address');
                $phone = $post->get('Phone');
                $userName = $post->get('Name');

                if($addrID == null || $domain == null || $domain2==null ||
                    $domain3 == null || $address == null || $phone == null || $userName == null){
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 20001,
                        'message' => array(
                            'message' => '参数不全'
                        )
                    )));
                    return $response;
                }
                $reg = new Regex();
                $reg->checkId($addrID);
                if ($reg->getCode() != 0) {
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 20002,
                        'message' => array(
                            'message' => 'id错误'
                        )
                    )));
                    return $response;
                }
                if ($domain == null || trim($domain) == '') {
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 10011,
                        'message' => array(
                            'message' => '地域不能为空'
                        )
                    )));
                    return $response;
                }
                if ($address == null || trim($address) == '') {
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 10012,
                        'message' => array(
                            'message' => '地址不能为空'
                        )
                    )));
                    return $response;
                }
                $reg->flush()->checkLocationAddress($address);
                if ($reg->getCode() != 0) {
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 10019,
                        'message' => array(
                            'message' => '所在地址格式错误'
                        )
                    )));
                    return $response;
                }
                $reg->flush();
                $reg->checkLocationAddress($domain2);
                if($reg->getCode()!=0){
                    $response->setContent(Json::encode(array(
                        'state'=>false,
                        'code'=>10020,
                        'message'=>array(
                            'message'=>'区域地址格式错误'
                        )
                    )));
                    return $response;
                }
                if ($phone == null || trim($phone) == '') {
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 10013,
                        'message' => array(
                            'message' => '电话不能为空'
                        )
                    )));
                    return $response;
                }
                $reg->flush();
                $reg->checkPhone($phone);
                if ($reg->getCode() != 0) {
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 10014,
                        'message' => array(
                            'message' => '电话格式错误'
                        )
                    )));
                    return $response;
                }

                if ($userName == null || trim($userName) == '') {
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 10015,
                        'message' => array(
                            'message' => '收货人不能为空'
                        )
                    )));
                    return $response;
                }

                $reg->flush();
                $reg->checkRealName($userName);
                if ($reg->getCode() != 0) {
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 10016,
                        'message' => array(
                            'message' => '收货人名称只能是中文、字母和下划线'
                        )
                    )));
                    return $response;
                }
                //校验完毕
                $domainList = new DomainList();
                $number = $domainList->has($domain,$domain2,$domain3);
                if (!$number) {
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 10017,
                        'message' => array(
                            'message' => '所选地域不存在'
                        )
                    )));
                    return $response;
                }

                /** @var $deliveryAddressTable DeliveryAddressTable */
                $deliveryAddressTable = $this->getServiceLocator()->get('User\Model\DeliveryAddressTable');
                $legal = $deliveryAddressTable->select(array(
                    "userid" => $userId,
                    "id" => $addrID
                ))->current();
                if(!$legal){
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 10018,
                        'message' => array(
                            'message' => '非法操作'
                        )
                    )));
                    return $response;
                }
                //end check

                $value = new DeliveryAddress();
                $value->setID($addrID)
                    ->setUserID($userId)
                    ->setName($userName)
                    ->setDomain($domain)
                    ->setDomain2($domain2)
                    ->setDomain3($domain3)
                    ->setAddress($address)
                    ->setPhone($phone);
                $rsl = $deliveryAddressTable->edit($value);
                if(!$rsl){
                    $response->setContent(Json::encode(array(
                        'state' => false,
                        'code' => 10019,
                        'message' => array(
                            'message' => '数据没有更新'
                        )
                    )));
                    return $response;
                }else{
                    $response->setContent(Json::encode(array(
                        'state' => true,
                        'message' => array(
                            'message' => '保存成功'
                        )
                    )));
                    return $response;
                }

            }while(false);
        }catch (\Exception $e){
            $this->logger->info($e->getCode(), 'UserInfoController' . $e->getMessage());
        }
        return $response;
    }

    public function getDeliveryLocationAction()
    {

        $response = $this->getResponse();


        $list = (new DomainList())->get();

        $response->setContent(Json::encode(array(
            'state' => true,
            'message' => array(
                'message' => '获取地域列表成功',
                'list' => $list
            )
        )));
        return $response;
    }

    public function getDomain2ListAction(){
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();

        $post = $request->getPost();
        $domain = $post->get('domain');
        $list = (new DomainList())->getDomain2List($domain);

        $response->setContent(Json::encode(array(
            'state' => true,
            'message' => array(
                'message' => '获取地域列表成功',
                'list' => $list
            )
        )));
        return $response;
    }

    public function getDomain3ListAction(){
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();

        $post = $request->getPost();
        $domain = $post->get('domain');
        $domain2 = $post->get('domain2');
        $list = (new DomainList())->getDomain3List($domain,$domain2);

        $response->setContent(Json::encode(array(
            'state' => true,
            'message' => array(
                'message' => '获取地域列表成功',
                'list' => $list
            )
        )));
        return $response;
    }

    public function removeAddressAction()
    {
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $post = $request->getPost();
        /** @var $deliveryAddressTable DeliveryAddressTable */
        $deliveryAddressTable = $this->getServiceLocator()->get('User\Model\DeliveryAddressTable');

        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');

        if (!$authSvr->hasIdentity()) {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => 10001,
                'message' => array(
                    'message' => '请先登录再操作'
                )
            )));
            return $response;
        }
        $identified = $authSvr->getIdentity();
        $userId = $identified[SessionInfoKey::ID];

        $id = $post->get('id');
        if ($id != null) { //带参数
            $rows = $deliveryAddressTable->delete(array('id' => $id, 'userid' => $userId));
        } else {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => 10002,
                'message' => array(
                    'message' => '参数缺失'
                )
            )));
            return $response;
        }

        if ($rows == 0) {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => 10003,
                'message' => array(
                    'message' => '删除失败'
                )
            )));
            return $response;
        } else {
            $response->setContent(Json::encode(array(
                'state' => true,
                'message' => array(
                    'message' => '删除成功',
                )
            )));
            return $response;
        }
    }

    private function initBase()
    {
        /** @var $renderer PhpRenderer */
        $baseUrl = $this->getRequest()->getBaseUrl();
        $renderer = $this->getServiceLocator()->get( 'Zend\View\Renderer\PhpRenderer');
        $renderer->headTitle('我的快递鼠:');
        $renderer->headMeta()->appendName('viewport', 'width=device-width, initial-scale=1.0');
        $renderer->headMeta()->setCharset('utf-8');
        $renderer->headLink(array('rel' => 'shortcut icon', 'type' => 'image/vnd.microsoft.icon', 'href' => $baseUrl . '/images/favicon.gif'));
        $renderer->headLink()->prependStylesheet($baseUrl .'/css/bootstrap.min.css',null,null,null);
        $renderer->headLink()->prependStylesheet($baseUrl .'/css/bootstrap-responsive.min.css',null,null,null);
        $renderer->headLink()->prependStylesheet($baseUrl . '/css/home.css',null,null,null);
        $renderer->headLink()->prependStylesheet($baseUrl . '/css/topMenu.css',null,null,null);
        // Only add the script in the "production" environment.
        $renderer->headScript()->prependFile($baseUrl . '/js/html5.js', 'text/javascript', array('conditional' => 'lt IE 9',))
            ->prependFile($baseUrl . '/js/bootstrap.min.js')
            ->prependFile($baseUrl .'/js/jquery.validate.min.js')
            ->prependFile($baseUrl .'/js/paramsCheck.js')
            ->prependFile($baseUrl .'/js/md5.js')
            ->prependFile($baseUrl . '/js/jquery.min.js');



        $view = new ViewModel();
        $topNav = $this->forward()->dispatch('Application\Controller\Plugin', array(
            "action" => "topnavbar",
        ));
        $view->setVariable("topNavbar", $topNav);
        $topHeader = $this->forward()->dispatch('Application\Controller\Plugin', array(
            "action" => "topheader",
        ));
        $topNavigation = $this->forward()->dispatch('Application\Controller\Plugin', array(
            "action" => "topnavigation",
        ));
        $localNav = $this->localNavView(
            array('/public/user/userinfo'=>'个人信息'));
        $view->addChild($localNav,'localNav');
        $footer = $this->forward()->dispatch('User\Controller\UserInfo',array(
            'action'=>'footer'
        ));
        $view->setVariable('topheader',$topHeader);
        $view->setVariable('topnavigation',$topNavigation);
//        $view->setVariable('localNav',$localNav);
        $view->setVariable('footer',$footer);
        $view->setTemplate('UserMainPageTemplate');
        $view->setTerminal(true);

        return $view;
    }

    public function userInfoPageContentAction()
    {
        $baseUrl = $this->getRequest()->getBaseUrl();
        /** @var $renderer PhpRenderer */
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
        $renderer->headTitle('个人信息');
        $renderer->inlineScript()->prependFile($baseUrl . '/js/user/userInfoContent.js'); // Disable CDATA comments
        //main view
        $contentView = new ViewModel();
        $contentView->setTemplate('UserContent');

        //child views

        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');
        $identified = $authSvr->getIdentity();
        /** @var $userEntity User */
        $userEntity = $this->getUserTable()->getUserById($identified[SessionInfoKey::ID]);
        $username = $userEntity->getUsername();
        $email = $userEntity->getEmail();
        $phone = $userEntity->getPhone();
        $nickname = $userEntity->getNickname();
        $userinfo = array(
            'username' => $username,
            'nickname' => $nickname,
            'email' => $email,
            'phone' => $phone
        );
        $menuList = (new UserCommon())->getUserInfoMenu();

        $content = new ViewModel();
        $content->setTemplate('UserInfoContent');
        $content->setVariable('userinfo', $userinfo);


        $menu = new ViewModel(array(
            'menu'=> $menuList,
            'selected'=>'/user/userinfo'
        ));
        $menu->setTemplate('UserMenu');


        $contentView->addChild($content, 'content')
                    ->addChild($menu, 'menu');

        return $contentView;
    }

    private function userPwdPageContentAction()
    {
        $baseUrl = $this->getRequest()->getBaseUrl();
        /** @var $renderer PhpRenderer */
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
        $renderer->headTitle('修改密码');
        $renderer->inlineScript()->prependFile($baseUrl . '/js/user/modifyPwd.js'); // Disable CDATA comments
        $contentView = new ViewModel();
        $contentView->setTemplate('UserContent');

        $menuList = (new UserCommon())->getUserInfoMenu();

        $content = new ViewModel();
        //检查账号密码是否正确
        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');
        $identitied = $authSvr->getIdentity();
        $userId = $identitied[SessionInfoKey::ID];
        /** @var $userTable UserTable */
        $userTable = $this->getServiceLocator()->get('Authenticate\Model\UserTable');
        $userEntity = $userTable->getUserById($userId);
        if($userEntity->getPassword()===""){
            $content->setVariable('hasPwd',false);
        }else{
            $content->setVariable('hasPwd',true);
        }
        $content->setTemplate('UserModifyPasswordTemplate');


        $menu = new ViewModel(array(
            'menu'=> $menuList,
            'selected'=>'/user/modifypassword'
        ));
        $menu->setTemplate('UserMenu');


        $contentView->addChild($content, 'content')
                    ->addChild($menu, 'menu');


        return $contentView;
    }

    private function userDeliveryAddressPageContentAction()
    {
        $baseUrl = $this->getRequest()->getBaseUrl();
        /** @var $renderer PhpRenderer */
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
        $renderer->headTitle('收货地址');
        $renderer->inlineScript()->prependFile($baseUrl . '/js/user/deliveryAddress.js'); // Disable CDATA comments
        $contentView = new ViewModel();
        $contentView->setTemplate('UserContent');

        $menuList = (new UserCommon())->getUserInfoMenu();

        $content = new ViewModel();
        $content->setTemplate('UserDeliveryAddressTemplate');


        $menu = new ViewModel(array(
            'menu'=> $menuList,
            'selected'=>'/user/deliveryAddress'
        ));
        $menu->setTemplate('UserMenu');


        $contentView->addChild($content, 'content')
            ->addChild($menu, 'menu');


        return $contentView;
    }

    /**
     * @return UserTable
     */
    private function getUserTable()
    {
        return $this->getServiceLocator()->get('Authenticate\Model\UserTable');
    }


}