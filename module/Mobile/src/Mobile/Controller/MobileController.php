<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Mobile for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Mobile\Controller;

use Application\Model\Logger;
use Application\Model\Regex;
use Zend\Db\Sql\Where;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class MobileController extends AbstractActionController
{
    protected $_sellerTable;
    protected $_goodsTable;
    protected $_goodsStandardTable;
    protected $_sellerClassRelationTable;

    protected $_countPerPage = 20;

    public function indexAction()
    {

        $sellerHtml = $this->getSellerHtml();

        return new ViewModel(array(
            "sellerHtml" => $sellerHtml
        ));
    }

    public function sellerAction(){
        $sellerName = $this->params('name');
        if($sellerName == null){
            return $this->redirect()->toRoute("mobile");
        }
        $regex = new Regex();
        $regex->checkSellerName($sellerName);
        if(!$sellerName || $regex->getCode()!=0){
            return $this->redirect()->toRoute("mobile");
        }
        //get seller
        $seller = $this->getSellerTable()->getSellerByName($sellerName);
        if(!$seller){
            return $this->redirect()->toRoute("mobile");
        }
        //end check seller
        $request = $this->getRequest();
        $data = $request->getQuery();
        $mc = $data["mc"]?$data["mc"]:"";
        $key = $data["k"]?$data["k"]:"";
        if($key){
            $regex->flush()->checkSearchText($key);
            if($regex->getCode() != 0){
                $key = "";
            }
        }

        $arr = $this->searchSellerGoods($seller, $mc, $key, 1, $this->_countPerPage);
        $vm = new ViewModel($arr);
        return $vm;
    }

    public function searchGoodsAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try {
            do {
                if ($request->isPost()) {
                    $data = $request->getPost();
                    $sellerId = $data["sellerId"];
                    $key = $data["key"];
                    $page = $data["page"];
                    $count = $data["count"];
                    $mc = $data["mc"];
                    if($sellerId === null || $page === null ||
                        $count === null || $mc === null || $key===null ){
                        $code = 20005;
                        $message = "参数不全";
                        break;
                    }

                    //end check

                    $html = "";
                    if($key == ""){
                        $html = $this->getSellerGoodsHtml($sellerId, $mc=="0"?"":$mc, $key, intval($page), intval($count));
                    }else{
                       // $html = $this->getSellerGoodsHtml($sellerId, "", $key, intval($page), intval($count));
                    }


                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "html" => $html,
                    )));
                }
            }while(false);
        }catch (\Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
            $code = -1;
        }
        if ($code != 0) {
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function fooAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /mobile/mobile/foo
        return array();
    }

    protected function searchSellerGoods($seller, $mc, $key, $page, $count){
        $menuHtml = $this->getSellerMenuHtml($seller->getID(), $mc);
        $goodsHtml = $this->getSellerGoodsHtml($seller->getID(), $mc, $key, $page, $this->_countPerPage);
        return array(
            "goodsHtml" => $goodsHtml,
            "menuHtml" => $menuHtml,
            "seller" => $seller,
        );
    }

    protected function getSellerMenuHtml($sellerId, $mc){
        $mainClasses = $this->getSellerClassRelationTable()->getMainClassesBySellerID($sellerId);

        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $arr = array(
            "activeClass" => $mc,
            "mainClasses" => $mainClasses,
            "sellerId" => $sellerId
        );
        $vm = new ViewModel($arr);
        $vm->setTemplate("mobile/seller/sellerMenu");
        $html = $viewRender->render($vm);
        return $html;
    }

    protected function getSellerGoodsHtml($sellerId, $mc, $key, $page, $count ){
        $where = new Where();
        $where->equalTo("s.id", $sellerId, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        if($mc != ""){
            $where->equalTo("t_goods.mainclassid", $mc, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        }
        if($key != ""){
            $where->like("t_goods.name", "%$key%");
        }
        $goods = $this->getGoodsTable()->fetchSlice($page, $count, $where);
        $this->getGoodsStandardTable()->fillStandards($goods);
        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $arr = array(
            "goodsArr" => $goods,
        );
        if(count($goods) == $count){
            $arr["hasMore"] = true;
            $arr["mainClassId"] = $mc;
        }
        $vm = new ViewModel($arr);
        $vm->setTemplate("mobile/seller/sellerGoods");
        $html = $viewRender->render($vm);
        return $html;
    }

    protected function getSellerHtml(){
//        $sellers = $this->getSellerTable()->fetchAllByRecommendOrder();
        $sellers = $this->getSellerTable()->fetchSlice(1,100,array('state'=>'1'));
        $viewRender = $this->getServiceLocator()->get("ViewRenderer");

        $arr = array(
            "sellers" => $sellers
        );
        $vm = new ViewModel($arr);
        $vm->setTemplate("mobile/home/seller");
        $html = $viewRender->render($vm);
        return $html;
    }

    /**
     * @return \Seller\Model\SellerTable
     */
    public function  getSellerTable(){
        if(!$this->_sellerTable){
            $t = $this->getServiceLocator();
            $this->_sellerTable = $t->get('Seller\Model\SellerTable');
        }
        return $this->_sellerTable;
    }

    /**
     * @return \Goods\Model\GoodsTable
     */
    public function getGoodsTable(){
        if(!$this->_goodsTable){
            $t = $this->getServiceLocator();
            $this->_goodsTable = $t->get('Goods\Model\GoodsTable');
        }
        return $this->_goodsTable;
    }

    /**
     * @return \Goods\Model\GoodsStandardTable
     */
    public function getGoodsStandardTable(){
        if(!$this->_goodsStandardTable){
            $t = $this->getServiceLocator();
            $this->_goodsStandardTable = $t->get('Goods\Model\GoodsStandardTable');
        }
        return $this->_goodsStandardTable;
    }

    /**
     * @return \Seller\Model\SellerClassRelationTable
     */
    public function getSellerClassRelationTable(){
        if(!$this->_sellerClassRelationTable){
            $t = $this->getServiceLocator();
            $this->_sellerClassRelationTable = $t->get('Seller\Model\SellerClassRelationTable');
        }
        return $this->_sellerClassRelationTable;
    }

}
