<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-20
 * Time: 上午10:41
 */

namespace Seller\Controller;


use Application\Model\Logger;
use Authenticate\AssistantClass\SessionInfoKey;
use Seller\Model\GoodsTable;
use Seller\Model\SellerCommon;
use Seller\Model\SellerOrderDetailTable;
use Seller\Model\SellerOrderTable;
use Seller\Model\SellerTable;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Sql\Where;
use Zend\Http\Request;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class SellerOrderMgrController extends AbstractActionController{

    protected $_sellerTable;
    protected $_goodsTable;
    protected $_logger;
    protected $_orderTable;
    protected $_orderDetailTable;
    protected $_rOrderTable;

    function __construct()
    {
        $this->_logger = new Logger();
    }

    public function orderMgrAction(){

        $view = $this->initBase();
        $content = $this->orderMgrContent();
        $view->addChild($content,'content');
        return $view;
    }

    public function getNewerOrderAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $orderId = $data['id'];
                    if($orderId == null || !is_numeric($orderId)){
                        $code = 10001;
                        $message = "参数不全";
                        break;
                    }
                    $authSvr = $this->getServiceLocator()->get('Session');
                    $identified = $authSvr->getIdentity();
                    $sellerID = $identified[SessionInfoKey::ID];
                    $count = 0;
                    $ids = array();
                    $where = new Where();
                    $where->equalTo('sellerId', $sellerID, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
                    $where->greaterThan('id', $orderId, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
                    $arr = $this->getOrderTable()->getOrders($where);
                    if($arr){
                        $count = count($arr);
                        foreach( $arr as $order){
                            $ids[] = $order->getID();
                        }
                    }
                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "data" => array(
                            "count" => $count,
                            "ids" => $ids,
                        ),
                    )));
                }
            }while(false);
        }catch (\Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
            $code = -1;
        }
        if($code != 0 ){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
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
            ->prependFile($baseUrl . '/js/jquery.min.js')
            ->appendFile($baseUrl . '/js/admin/autobahn.js')
            ->appendFile($baseUrl . '/js/seller/sellerOrder.js');



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

    private function localNavView($links){


        $view = new ViewModel();
        $view->setVariable('links',$links);
        $view->setTemplate('localNavTemplate');
        return $view;

    }

    private function orderMgrContent()
    {
        try{
            /** @var $authSvr AuthenticationService */
            $authSvr = $this->getServiceLocator()->get('Session');
            if($authSvr->hasIdentity()===null){
                return $this->redirect()->toRoute('home');
            }
            $baseUrl = $this->getRequest()->getBaseUrl();
            /** @var $renderer PhpRenderer */
            $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
            $renderer->headTitle('商品管理');
            $renderer->headScript()->appendFile($baseUrl . '/js/lightbox.min.js'); // Disable CDATA comments
            $renderer->inlineScript()->prependFile($baseUrl . '/js/easyzoom.js'); // Disable CDATA comments
            $renderer->headLink()->appendStylesheet('/css/lightbox.css',null,null,null);
//            $renderer->headLink()->prependStylesheet($baseUrl . '/css/easyzoom.css',null,null,null);
            //main view
            $contentView = new ViewModel();
            $contentView->setTemplate('SellerContent');

            //child views


            $identified = $authSvr->getIdentity();
            $sellerID = $identified[SessionInfoKey::ID];

            $content = new ViewModel();
            $content->setTemplate('SellerOrderContent');

            /** @var $request Request */
            $request = $this->getRequest();
            $response = $this->getResponse();
            $page = $request->getQuery('page');
            if(!is_numeric($page)){
                $page = 1;
            }
            $page = $page<1?1:$page;

            $limit = 10;
            $start = ($page-1)*$limit;

            //get order list by sellerID and page limit
            $orderTable = $this->getSellerOrderTable();
            $where = new Where();
            $where->in('SellerID',array($sellerID));
            $rs = $orderTable->getOrderIDList($where,$start,$limit);
            $content->setVariable('orderList',$rs['orderList']);
            $content->setVariable('orderIdList',$rs['orderIdList']);
            $pages = ceil($orderTable->getOrderCount($where,$start,$limit)/$limit);
            $content->setVariable('pager',array(
                "page"=>$page,
                "pages"=>$pages
            ));
            //get order detail by orderID list
            $orderDetailTable = $this->getSellerOrderDetailTable();
            $detailList = $orderDetailTable->getDetailListByOrderID($rs['orderIdList']);

            $content->setVariable('detailList', $detailList);

            $content->setVariable('sellerId', $sellerID);

            $menuList = (new SellerCommon())->getSellerInfoMenu();
            $menu = new ViewModel(array(
                'menu'=> $menuList,
                'selected'=>'/seller/orderMgr'
            ));
            $menu->setTemplate('SellerMenu');


            $contentView->addChild($content, 'content')
                ->addChild($menu, 'menu');
            return $contentView;
        }catch (\Exception $e){
            $this->_logger->err($e->getCode(),$e->getMessage());
        }
    }

    /**
     * @return SellerTable
     */
    private function getSellerTable()
    {
        if(!$this->_sellerTable){
            $t = $this->getServiceLocator();
            $this->_sellerTable = $t->get('Seller\Model\SellerTable');
        }
        return $this->_sellerTable;
    }

    /**
     * @return GoodsTable
     */
    private function getGoodsTable()
    {
        if(!$this->_goodsTable){
            $t = $this->getServiceLocator();
            $this->_goodsTable = $t->get('Seller\Model\GoodsTable');
        }
        return $this->_goodsTable;
    }
    /**
     * @return SellerOrderTable
     */
    private function getSellerOrderTable()
    {
        if(!$this->_orderTable){
            $t = $this->getServiceLocator();
            $this->_orderTable = $t->get('Seller\Model\SellerOrderTable');
        }
        return $this->_orderTable;
    }

    /**
     * @return SellerOrderDetailTable
     */
    private function getSellerOrderDetailTable()
    {
        if(!$this->_orderDetailTable){
            $t = $this->getServiceLocator();
            $this->_orderDetailTable = $t->get('Seller\Model\SellerOrderDetailTable');
        }
        return $this->_orderDetailTable;
    }

    /**
     * @return \Home\Model\OrderTable
     */
    public function getOrderTable()
    {
        if (!$this->_rOrderTable) {
            $t = $this->getServiceLocator();
            $this->_rOrderTable = $t->get('Home\Model\OrderTable');
        }
        return $this->_rOrderTable;
    }

}