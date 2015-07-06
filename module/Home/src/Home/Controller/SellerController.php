<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-14
 * Time: 下午7:57
 */

namespace Home\Controller;


use Application\Controller\IndexController;
use Application\Model\Logger;
use Application\Model\Regex;
use Goods\Controller\GoodsController;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Exception;

class SellerController extends AbstractActionController{

    protected $_goodsTable;
    protected $_sellerTable;
    protected $_goodsStandardTable;
    protected $_sellerClassRelationTable;

    protected $_countPerPage = 12;

    public function indexAction(){
        /** @var $request \Zend\Http\Request */
        $vm = new ViewModel();
        $request = $this->getRequest();
        try{
            do{
                $sellerName = $this->params('name');
                if($sellerName == null){
                    return $this->redirect()->toRoute("home");
                }
                $regex = new Regex();
                $regex->checkSellerName($sellerName);
                if(!$sellerName || $regex->getCode()!=0){
                    return $this->redirect()->toRoute("home");
                }
                //get seller
                $seller = $this->getSellerTable()->getSellerByName($sellerName);
                if(!$seller || $seller->getState() != "1"){
                    return $this->redirect()->toRoute("home");
                }
                //get search key
                $key = $request->getQuery('k');
                $key = $key?$key:"";
                $regex->flush()->checkSearchText($key);
                if( $regex->getCode()!=0){
                    return $this->redirect()->toRoute("home");
                }

                if($key == ""){
                    $arr = $this->fullSellerGoods($seller);
                }else{
                    $arr = $this->searchSellerGoods($seller, $key);
                }


                $fw = $this->forward();
                $this->layout()->setVariable("topNavbar",$fw->dispatch('Application\Controller\Plugin',array(
                    "action" => "topnavbar",
                )));
                $this->layout()->setVariable("topHeader",$fw->dispatch('Application\Controller\Plugin',array(
                    "action" => "topheader"
                )));
                $this->layout()->setVariable("topNavigation",$fw->dispatch('Application\Controller\Plugin',array(
                    "action" => "topnavigation"
                )));
                $this->layout()->setVariable("footer",$fw->dispatch('Application\Controller\Plugin',array(
                    "action" => "footer",
                )));

                $vm = new ViewModel($arr);
                $this->setRenderer();
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    protected function fullSellerGoods($seller){
        //get main class
        $mainClassArr = $this->getSellerClassRelationTable()->getMainClassesBySellerID($seller->getID());

        //get choice goods html
        $choiceGoodsHtml = $this->getSellerChoiceGoodsHtml($seller->getID());
        //get all goods html
        $allGoodsHtml = $this->getSellerGoodsHtml($seller->getID(), "", "", 1, $this->_countPerPage);
        //get each main Class Goods html
        $mainClassGoodsHtmlArr = array();
        foreach($mainClassArr as $mainClassGroup){
            $mainClassGoodsHtmlArr[$mainClassGroup["ID"]] = $this->getSellerGoodsHtml($seller->getID(), $mainClassGroup["ID"], "", 1, $this->_countPerPage );
        }

        $arr = array(
            "isSearch" => false,
            "hasGoods" => !($allGoodsHtml==""),
            "seller" => $seller,
            "mainClassArr" => $mainClassArr,
            "choiceGoodsHtml" => $choiceGoodsHtml,
            "allGoodsHtml" => $allGoodsHtml,
            "mainClassGoodsHtmlArr" => $mainClassGoodsHtmlArr,
        );
        return $arr;
    }
    protected function searchSellerGoods($seller, $key){
        $html = $this->getSellerGoodsHtml($seller->getID(), "", $key, 1, $this->_countPerPage);
        $arr = array(
            "isSearch" => true,
            "key" => $key,
            "hasGoods" => !($html==""),
            "seller" => $seller,
            "searchGoodsHtml" => $html
        );
        return $arr;
    }

    public function searchAction(){
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
                        $html = $this->getSellerGoodsHtml($sellerId, "", $key, intval($page), intval($count));
                    }


                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "html" => $html,
                    )));
                }
            }while(false);
        }catch (Exception $e){
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

    private function setRenderer(){
        $baseUrl = $this->getRequest()->getBaseUrl();
        $renderer = $this->getServiceLocator()->get( 'Zend\View\Renderer\PhpRenderer');
        $renderer->headScript()->prependFile($baseUrl . '/js/home/seller.js');
    }

    protected function getSellerGoodsHtml($sellerId, $mc, $key, $page, $count){
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
        $vm->setTemplate("home/seller/sellerGoods");
        $html = $viewRender->render($vm);
        return $html;
    }

    protected function getSellerChoiceGoodsHtml($sellerId){
        $goods = $this->getSellerChoiceGoods($sellerId);
        $this->getGoodsStandardTable()->fillStandards($goods);
        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $vm = new ViewModel(array(
            "goodsArr" => $goods,
        ));
        $vm->setTemplate("home/seller/sellerGoods");
        $html = $viewRender->render($vm);
        return $html;
    }
    protected function getSellerChoiceGoods($sellerId){
        $goods = $this->getGoodsTable()->getRandomGoodsBySellerID($sellerId, 4);
        return $goods;
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
     * @return \Seller\Model\SellerTable
     */
    public function getSellerTable(){
        if(!$this->_sellerTable){
            $t = $this->getServiceLocator();
            $this->_sellerTable = $t->get('Seller\Model\SellerTable');
        }
        return $this->_sellerTable;
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