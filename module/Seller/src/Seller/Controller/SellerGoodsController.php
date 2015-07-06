<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-19
 * Time: 上午9:42
 */

namespace Seller\Controller;


use Application\Entity\Seller;
use Application\Model\Logger;
use Authenticate\AssistantClass\SessionInfoKey;
use Seller\Model\GoodsTable;
use Seller\Model\SellerCommon;
use Seller\Model\SellerTable;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class SellerGoodsController extends AbstractActionController
{


    protected $_sellerTable;
    protected $_goodsTable;
    protected $_goodsStandardTable;
    protected $_mainClassTable;

    /**
     * @var Logger
     */
    protected $_logger;

    public function __construct()
    {
        $this->_logger = new Logger();
    }

    public function goodsMgrAction()
    {
        $view = $this->initBase();
        $content = $this->goodsMgrContent();
        $view->addChild($content, 'content');
        return $view;
    }

    public function viewAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        if($request->isPost()){
            $goodsId = $request->getPost('id');
            if(!$goodsId || !is_numeric($goodsId)){
                return $response;
            }
            //check goods is belong to this seller
            $authSvr = $this->getServiceLocator()->get('Session');
            if ($authSvr->hasIdentity() === null) {
                return $response;
            }
            $identified = $authSvr->getIdentity();
            $sellerID = $identified[SessionInfoKey::ID];
            $legal = $this->getGoodsTable()->select(array(
                "id" => $goodsId,
                "sellerid" => $sellerID
            ))->current();
            if(!$legal){
                return $response;
            }
            //end check
            $response = $this->forward()->dispatch('Goods\Controller\Goods', array(
                'action' => 'view',
                'innerCall' => true,
            ));
        }
        return $response;
    }

    public function editAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        if($request->isPost()){
            $goodsId = $request->getPost('id');
            if(!$goodsId || !is_numeric($goodsId)){
                return $response;
            }
            //check goods is belong to this seller
            $authSvr = $this->getServiceLocator()->get('Session');
            if ($authSvr->hasIdentity() === null) {
                return $response;
            }
            $identified = $authSvr->getIdentity();
            $sellerID = $identified[SessionInfoKey::ID];
            $legal = $this->getGoodsTable()->select(array(
                "id" => $goodsId,
                "sellerid" => $sellerID
            ))->current();
            if(!$legal){
                return $response;
            }
            //end check
            $response = $this->forward()->dispatch('Goods\Controller\Goods', array(
                'action' => 'editGoods',
                'innerCall' => true,
            ));
        }
        return $response;
    }

    public function getGoodsStandardAction(){
        $response = $this->forward()->dispatch('Goods\Controller\Goods', array(
            'action' => 'getGoodsStandard',
            'innerCall' => true,
        ));
        return $response;
    }

    public function editGoodsStandardAction(){
        $response = $this->forward()->dispatch('Goods\Controller\Goods', array(
            'action' => 'editGoodsStandard',
            'innerCall' => true,
        ));
        return $response;
    }

    private function initBase()
    {
        /** @var $renderer PhpRenderer */
        $baseUrl = $this->getRequest()->getBaseUrl();
        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
        $renderer->headTitle('我的快递鼠:');
        $renderer->headMeta()->appendName('viewport', 'width=device-width, initial-scale=1.0');
        $renderer->headMeta()->setCharset('utf-8');
        $renderer->headLink(array('rel' => 'shortcut icon', 'type' => 'image/vnd.microsoft.icon', 'href' => $baseUrl . '/images/favicon.gif'));
        $renderer->headLink()->prependStylesheet($baseUrl . '/css/bootstrap.min.css', null, null, null);
        $renderer->headLink()->prependStylesheet($baseUrl . '/css/bootstrap-responsive.min.css', null, null, null);
        $renderer->headLink()->prependStylesheet($baseUrl . '/css/home.css', null, null, null);
        $renderer->headLink()->prependStylesheet($baseUrl . '/css/topMenu.css', null, null, null);
        // Only add the script in the "production" environment.
        $renderer->headScript()->prependFile($baseUrl . '/js/html5.js', 'text/javascript', array('conditional' => 'lt IE 9',))
            ->prependFile($baseUrl . '/js/bootstrap.min.js')
            ->prependFile($baseUrl . '/js/jquery.validate.min.js')
            ->prependFile($baseUrl . '/js/paramsCheck.js')
            ->prependFile($baseUrl . '/js/md5.js')
            ->prependFile($baseUrl . '/js/jquery.min.js')
            ->prependFile($baseUrl . '/js/adminCommon.js')
            ->prependFile($baseUrl . '/js/seller/goods.js');


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
            array('/public/user/userinfo' => '个人信息'));
        $view->addChild($localNav, 'localNav');
        $footer = $this->forward()->dispatch('User\Controller\UserInfo', array(
            'action' => 'footer'
        ));
        $view->setVariable('topheader', $topHeader);
        $view->setVariable('topnavigation', $topNavigation);
//        $view->setVariable('localNav',$localNav);
        $view->setVariable('footer', $footer);
        $view->setTemplate('UserMainPageTemplate');
        $view->setTerminal(true);

        return $view;
    }

    private function localNavView($links)
    {


        $view = new ViewModel();
        $view->setVariable('links', $links);
        $view->setTemplate('localNavTemplate');
        return $view;

    }

    private function goodsMgrContent()
    {
        try {
            /** @var $authSvr AuthenticationService */
            $authSvr = $this->getServiceLocator()->get('Session');
            if ($authSvr->hasIdentity() === null) {
                return $this->redirect()->toRoute('home');
            }
            $baseUrl = $this->getRequest()->getBaseUrl();
            /** @var $renderer PhpRenderer */
            $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
            $renderer->headTitle('商品管理');
//        $renderer->inlineScript()->prependFile($baseUrl . '/js/user/userInfoContent.js'); // Disable CDATA comments
            //main view
            $contentView = new ViewModel();
            $contentView->setTemplate('SellerContent');

            //child views


            $identified = $authSvr->getIdentity();
//        /** @var $sellerEntity Seller */
//        $sellerEntity = $this->getSellerTable()->getUserById($identified[SessionInfoKey::ID]);
            $sellerID = $identified[SessionInfoKey::ID];

//            $start = 0;
//            $limit = 10;
            $queryPage = $this->getRequest()->getQuery('page');
            $page = $queryPage? intval($queryPage):1;
            $count = 10;
            $where = array("t_goods.sellerid" => $sellerID);
            $goodsList = $this->getGoodsTable()->fetchSlice($page, $count, $where);
            $this->getGoodsStandardTable()->fillStandards($goodsList);
            $all = $this->getGoodsTable()->fetchCount($where);
            $content = new ViewModel();
            $content->setTemplate('GoodsContent');
            $content->setVariable('goodsList', $goodsList);
            $content->setVariable('pager', array(
                "page" => $page,
                "count" => $count,
                "all" => $all
            ));
            $mainClassTable = $this->getMainClassTable()->fetchAll();
            $content->setVariable('mainClassTable', $mainClassTable);

            $menuList = (new SellerCommon())->getSellerInfoMenu();
            $menu = new ViewModel(array(
                'menu' => $menuList,
                'selected' => '/seller/goodsMgr'
            ));
            $menu->setTemplate('SellerMenu');


            $contentView->addChild($content, 'content')
                ->addChild($menu, 'menu');
            return $contentView;
        } catch (\Exception $e) {
            $this->_logger->err($e->getCode(), $e->getMessage());
        }
        return "";
    }

    /**
     * @return SellerTable
     */
    private function getSellerTable()
    {
        if (!$this->_sellerTable) {
            $t = $this->getServiceLocator();
            $this->_sellerTable = $t->get('Seller\Model\SellerTable');
        }
        return $this->_sellerTable;
    }

    /**
     * @return \Goods\Model\GoodsTable
     */
    private function getGoodsTable()
    {
        if (!$this->_goodsTable) {
            $t = $this->getServiceLocator();
            $this->_goodsTable = $t->get('Goods\Model\GoodsTable');
        }
        return $this->_goodsTable;
    }

    /**
     * @return \Goods\Model\GoodsStandardTable
     */
    private function getGoodsStandardTable()
    {
        if (!$this->_goodsStandardTable) {
            $t = $this->getServiceLocator();
            $this->_goodsStandardTable = $t->get('Goods\Model\GoodsStandardTable');
        }
        return $this->_goodsStandardTable;
    }

    /**
     * @return \Goods\Model\MainClassTable
     */
    public function getMainClassTable(){
        if(!$this->_mainClassTable){
            $t = $this->getServiceLocator();
            $this->_mainClassTable = $t->get('Goods\Model\MainClassTable');
        }
        return $this->_mainClassTable;
    }
} 