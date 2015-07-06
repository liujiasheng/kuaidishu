<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-7-22
 * Time: 下午9:34
 */

namespace User\Controller;


use Application\Entity\User;
use Authenticate\AssistantClass\SessionInfoKey;
use Authenticate\AssistantClass\UserType;
use Authenticate\Model\UserTable;
use User\Model\UserCommon;
use User\Model\UserOrderDetailTable;
use User\Model\UserOrderTable;
use User\Model\WorkerOrderTable;
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
use Zend\View\View;

class UserOrderMGRController extends AbstractActionController{

    protected $logger;
    protected $userEntity;

    public function __construct()
    {
        $this->logger = new \Application\Model\Logger();
    }

    /**
     * @return User
     */
    private function getUserEntity(){

        if($this->userEntity==null){
            /** @var $authSvr AuthenticationService */
            $authSvr = $this->getServiceLocator()->get('Session');
            $identified = $authSvr->getIdentity();

            if (!isset($identified[SessionInfoKey::role]) && $identified[SessionInfoKey::role] !== UserType::User) {

            }
            /** @var $userEntity User */
            $this->userEntity = $this->getUserTable()->getUserById($identified[SessionInfoKey::ID]);
            return $this->userEntity;
        }
        return $this->userEntity;
    }

    private function localNavView($links){


        $view = new ViewModel();
        $view->setVariable('links',$links);
        $view->setTemplate('localNavTemplate');
        return $view;

    }

    private  function footer(){

        /** @var $viewRender phpRenderer */
        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $view = new ViewModel();
        $view->setTemplate('UserModuleFooterTemplate');
//        $view->setTerminal(true);
        return $view;
    }

    public function userOrderManagePageAction(){
        $view = $this->initBase();
        $localNav = $this->localNavView(
            array('/public/user/userinfo'=>'个人信息'));
        $view->addChild($localNav,'localNav');
        $content = $this->userOrderManagePageContent();
        $view->addChild($content,'content');
        return $view;
    }

    public function userOrderManagePage2Action(){

        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');
        $identified = $authSvr->getIdentity();
        if (!isset($identified[SessionInfoKey::role]) && $identified[SessionInfoKey::role] !== UserType::User) {
            return $this->redirect()->toRoute('home');
        }
        $view = $this->initBase();
        $localNav = $this->localNavView(
            array('/public/user/userinfo'=>'个人信息'));
        $view->addChild($localNav,'localNav');
        $content = $this->userOrderManagePageContent2();
        $view->addChild($content,'content');
        return $view;
    }

    public function userOrderManageDetailPageAction(){
        $view = $this->initBase();
        $localNav = $this->localNavView(
            array('/public/user/userinfo'=>'个人信息'));
        $view->addChild($localNav,'localNav');
        $content = $this->userOrderManageDetailPageContent();
        $view->addChild($content,'content');
        return $view;
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

        $footer = $this->footer();
        $view->addChild($footer,'footer');
        $view->setVariable('topheader',$topHeader);
        $view->setVariable('topnavigation',$topNavigation);
//        $view->setVariable('localNav',$localNav);
//        $view->setVariable('footer',$footer);
        $view->setTemplate('UserMainPageTemplate');
        $view->setTerminal(true);

        return $view;
    }

    public  function userOrderManagePageContent()
    {

        $baseUrl = $this->getRequest()->getBaseUrl();
        /** @var $renderer PhpRenderer */
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
        $renderer->headTitle("订单管理");
        $renderer->inlineScript()->prependFile($baseUrl . '/js/user/orderManage.js'); // Disable CDATA comments
        $contentView = new ViewModel();
        $contentView->setTemplate('UserContent');

        $menuList = (new UserCommon())->getUserInfoMenu();

        $content = new ViewModel();
        $content->setTemplate('UserInfoOrderManagementContent');


        $menu = new ViewModel(array(
            'menu'=> $menuList,
            'selected'=>'/user/orderManage'
        ));
        $menu->setTemplate('UserMenu');


        $contentView->addChild($content, 'content')
            ->addChild($menu, 'menu');


        return $contentView;
    }

    private function userOrderManageDetailPageContent()
    {
        $baseUrl = $this->getRequest()->getBaseUrl();
        /** @var $renderer PhpRenderer */
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
        $renderer->headTitle("订单管理");
        $renderer->inlineScript()->prependFile($baseUrl . '/js/user/orderDetail.js'); // Disable CDATA comments
        $contentView = new ViewModel();
        $contentView->setTemplate('UserContent');

        $menuList = (new UserCommon())->getUserInfoMenu();

        $content = new ViewModel();
        $id = $this->params('id');
        if($id!==null){

            //TODO find info about order which id is x
            /** @var $table UserOrderTable */
            $order = $this->getServiceLocator()->get('User\Model\UserOrderTable');
            $orderEntity = $order->getOrderByID($id);
            if(!$orderEntity){
                $this->redirect()->toRoute('home');
            }
            /** @var $authSvr AuthenticationService */
            $authSvr = $this->getServiceLocator()->get("Session");
            $identitied = $authSvr->getIdentity();
            $userId = $identitied[SessionInfoKey::ID];
            if($orderEntity->UserID!==$userId){
                $this->redirect()->toRoute('home');
            }
            /** @var $orderDetailTable UserOrderDetailTable */
            $orderDetailTable = $this->getServiceLocator()->get('User\Model\UserOrderDetailTable');
            $detailEntity = $orderDetailTable->searchOrderDetail(array($id));
            /** @var $workerOrderTable WorkerOrderTable */
            $workerOrderTable = $this->getServiceLocator()->get('User\Model\WorkerOrderTable');
            $workerOrderEntity = $workerOrderTable->getEntityByOrderID($id);


            $detail = array(
                'Order'=>$orderEntity,
                'detail'=>$detailEntity,
                'workerOrder'=>$workerOrderEntity
            );
            $content->setVariable('data',$detail);
        }else{
            //TODO You can give user a blank page
        }
        $content->setTemplate('UserInfoOrderManagementDetailContent');


        $menu = new ViewModel(array(
            'menu'=> $menuList,
            'selected'=>'/user/orderManage'
        ));
        $menu->setTemplate('UserMenu');


        $contentView->addChild($content, 'content')
            ->addChild($menu, 'menu');


        return $contentView;
    }

    public function getUserOrderListAction(){
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $page = $request->getQuery('page');
        if(!is_numeric($page)){
           $page = 1;
        }
        $page = $page<1?1:$page;

        $limit = 10;
        $begin = ($page-1)*$limit;

        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');
        $identified = $authSvr->getIdentity();

        if (!isset($identified[SessionInfoKey::role]) && $identified[SessionInfoKey::role] !== UserType::User) {
            $response->setContent(Json::encode(array(
                'state' => false,
                'code' => 10100,
                'message' => array(
                    'message' => "该接口仅供用户修改个人信息使用"
                )
            )));
            return $response;
        }
        /** @var $userEntity User */
        $userEntity = $this->getUserTable()->getUserById($identified[SessionInfoKey::ID]);
//        $username = $userEntity->getUsername();
        $userId = $userEntity->getID();
        /** @var $table UserOrderTable */
        $table = $this->getServiceLocator()->get('User\Model\UserOrderTable');
        $rs = $table->getOrderListByUserIdAndPage($userId,$begin,$limit);
        $pages = ceil($table->getOrderListCountByUserIdAndPage($userId,$begin,$limit)/$limit);
        if(!$rs){
            $response->setContent(Json::encode(array(
                'state'=>false,
                'code'=>10101,
                'message'=>array(
                    'message'=>'success',
                    'list'=>$rs
                )
            )));
            return $response;
        }

        $response->setContent(Json::encode(array(
            'state'=>true,
            'message'=>array(
                'message'=>'success',
                'list'=>$rs,
                'page'=>$page,
                'pageCount'=>$pages
            )
        )));


        return $response;
    }





    public function fooAction(){
        return array();
    }

    private function userOrderManagePageContent2()
    {
        $baseUrl = $this->getRequest()->getBaseUrl();
        /** @var $renderer PhpRenderer */
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
        $renderer->headTitle("订单管理");
        $contentView = new ViewModel();
        $contentView->setTemplate('UserContent');

        $menuList = (new UserCommon())->getUserInfoMenu();

        $list = $this->getUserOrderList();

        $content = new ViewModel();
        $content->setTemplate('UserInfoOrderManagementContent2');
        $content->setVariable('list',$list);


        $menu = new ViewModel(array(
            'menu'=> $menuList,
            'selected'=>'/user/orderManage'
        ));
        $menu->setTemplate('UserMenu');


        $contentView->addChild($content, 'content')
            ->addChild($menu, 'menu');


        return $contentView;
    }

    private function getUserOrderList()
    {
        /** @var $request Request */
        $request = $this->getRequest();
        $page = $request->getQuery('page');
        if(!is_numeric($page)){
            $page = 1;
        }
        $page = (int)($page<1?1:$page);

        $limit = 10;
        $begin = ($page-1)*$limit;

        $userId = $this->getUserEntity()->getID();
        /** @var $table UserOrderTable */
        $order = $this->getServiceLocator()->get('User\Model\UserOrderTable');
        /** @var $table UserOrderDetailTable */
        $detail = $this->getServiceLocator()->get('User\Model\UserOrderDetailTable');
        $where = new Where();

        $orderRs = $order->searchOrderList($userId,$where,$begin,$limit);
        $orderRs['page'] = $page;
        $orderRs['total'] = ceil($orderRs['rowCount']==0?1:($orderRs['rowCount']/$limit));
        $orderIdList = array();
        foreach($orderRs['data'] as $row){
                array_push($orderIdList,$row['ID']);
        }
        $detailRs = $detail->searchOrderDetail($orderIdList);

        return array('order'=>$orderRs,'detail'=>$detailRs);
    }

    /**
     * @return UserTable
     */
    private function getUserTable()
    {
        return $this->getServiceLocator()->get('Authenticate\Model\UserTable');
    }
}

