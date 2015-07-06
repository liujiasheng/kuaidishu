<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-19
 * Time: 下午3:12
 */

namespace Home\Controller;


use Application\Model\Logger;
use Application\Model\Regex;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Exception;
use Zend\View\Model\ViewModel;

class SearchController extends AbstractActionController{

    protected $_sellerTable;
    protected $_superClassTable;
    protected $_mainClassTable;
    protected $_classTable;
    protected $_goodsTable;
    protected $_goodsStandardTable;

    public function indexAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $code = 0;
        $message = "";
        $arr = array();
        $vm = null;
        try{
            do{
                //搜索类型  s：商家   g：商品
                $type = $request->getQuery('t');$type = $type?$type:"";
                if($type=="")$type = $this->Params('query')['t'];$type = $type?$type:"";
                //商家关键字
                $skey = $request->getQuery('s');$skey = $skey?$skey:"";
                //商品关键字
                $gkey = $request->getQuery('g');$gkey = $gkey?$gkey:"";
                //子类id
                $cs = $request->getQuery('cs'); $cs = $cs?$cs:"";
                //主类id
                $mc = $request->getQuery('mc'); $mc = $mc?$mc:"";
                //超类id
                $sc = $request->getQuery('sc'); $sc = $sc?$sc:"";

                $regex = new Regex();

                switch($type){
//                    搜索商家
                    case "s":
                        $where = new Where();
                        $where->equalTo("state", 1, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
                        if($skey != ""){
                            $regex->checkSearchText($skey);
                            if($regex->getCode()!=0){
                                $code = $regex->getCode();
                                $message = $regex->getMessage();
                                break;
                            }
                            $where->like("name", "%$skey%");
                        }
                        $excludeSID = array('100218');
                        $where->notIn('id', $excludeSID);
                        $sellers = $this->getSellerTable()->fetchSlice(1, 12, $where);
                        if( count($sellers) == 1){
                            return $this->redirect()->toUrl("home/seller/".$sellers[0]->getName());
                        }
                        $arr["sellers"] = $sellers;
                        break;

//                    搜索商品
                    case "g":
                        $where = new Where();
                        $where->notEqualTo("t_goods.state", -1, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
                        $sellerIdSelect = new Select('t_seller');
                        $sellerIdSelect->columns(array('id'));
                        $sellerIdSelect->where(array('state' => 9));
                        $where->notIn('t_goods.sellerId', $sellerIdSelect);
                        if($cs != ""){
                            $regex->checkId($mc);
                            if($regex->getCode() != 0){
                                $code = $regex->getCode();
                                $message = $regex->getMessage();
                                break;
                            }
                            $where->equalTo("t_goods.classid", intval($cs), Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
                        }
                        if($mc != ""){
                            $regex->checkId($mc);
                            if($regex->getCode() != 0){
                                $code = $regex->getCode();
                                $message = $regex->getMessage();
                                break;
                            }
                            $where->equalTo("t_goods.mainclassid", intval($mc), Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
                        }
                        if($sc != ""){
                            $regex->checkId($sc);
                            if($regex->getCode() != 0){
                                $code = $regex->getCode();
                                $message = $regex->getMessage();
                                break;
                            }
                            $select = new Select("t_goods_mainclass");
                            $select->columns(array("ID"))
                                ->where(array("superclassid" => $sc));
                            $where->in("t_goods.mainclassid", $select);
                        }
                        if($gkey != ""){
                            $regex->checkSearchText($gkey);
                            $where->like("t_goods.name", "%$gkey%");
                            if($skey != ""){
                                $regex->checkSellerName($skey);
                                $where->like("s.name", "%$skey%");
                            }
                            if($regex->getCode()!=0){
                                $code = $regex->getCode();
                                $message = $regex->getMessage();
                                break;
                            }
                        }
                        //$mainClassArr = $this->getMainClassTable()->fetchAllWithClassNoSuper();
                        $superClassArr = $this->getMainClassTable()->fetchAllSuperWithMainClass();
                        $goods = $this->getGoodsTable()->fetchSlice(1, 12, $where);
                        $this->getGoodsStandardTable()->fillStandards($goods);

                        $arr["classArr"] = $superClassArr;
                        $arr["goodsArr"] = $goods;
                        break;

//                    默认
                    default:
                }

                $arr["all"] = ($type=="s" && $skey == "") || ($type == "g" && $gkey == "");
                $arr["key"] = $type == 's'?$skey:$gkey;
                $arr["state"] = true;
                $arr["type"] = $type;
                $arr["csID"] = $cs?$cs:"";
                $arr["cs"] = $cs?$this->getClassTable()->getClass($cs)->getName():"";
                $arr["mc"] = $mc;
                $arr["sc"] = $sc;
                $vm = new ViewModel($arr);
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code != 0){
            $vm = new ViewModel(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            ));
        }
        $this->setRenderer();
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
        return $vm;
    }

    public function allgoodsAction(){
        $excludeSC = array(
            7,  //特俗商品
        );
        $where = new Where();
        $where->notIn("id", $excludeSC);
        $scRows = $this->getSuperClassTable()->select($where);
        $superClasses = array();
        if($scRows){
            $scArr = $scRows->toArray();
            foreach( $scArr as $row){
                $superClasses[$row["ID"]] = $row;
            }
        }
        $goodsGroups = array();
        foreach($superClasses as $key => $superClass){
            $goods = $this->getGoodsTable()->getRandomGoodsBySuperClassID($key, 8);
            $this->getGoodsStandardTable()->fillStandards($goods);
            $goodsGroups[$key] = array(
                "id" => $superClass["ID"],
                "name" => $superClass["Name"],
                "goods" => $goods,
            );
        }

        $this->setRenderer();
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
        return new ViewModel(array(
            "goodsGroups" => $goodsGroups,
            "classArr" => $this->getMainClassTable()->fetchAllSuperWithMainClass(),
        ));
    }

    private function setRenderer(){
        $baseUrl = $this->getRequest()->getBaseUrl();
        $renderer = $this->getServiceLocator()->get( 'Zend\View\Renderer\PhpRenderer');
        $renderer->headScript()->prependFile($baseUrl . '/js/home/search.js');
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
                    $type = $data["type"];
                    $key = $data["key"];
                    $page = $data["page"];
                    $count = $data["count"];
                    $sc = $data["sc"];
                    $mc = $data["mc"];
                    $cs = $data["cs"];
                    if($type === null || $key === null || $page === null ||
                        $count === null || $sc === null || $mc === null || $cs === null){
                        $code = 20005;
                        $message = "参数不全";
                        break;
                    }

                    $html = "";
                    if($type == "g"){
                        $html = $this->getSearchGoodsHtml($type, $key, $sc, $mc, $cs, intval($page), intval($count));
                    }elseif($type == "s"){
                        $html = $this->getSearchSellersHtml($type, $key, $sc, $mc, $cs, intval($page), intval($count));
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

    protected function getSearchGoodsHtml($type, $key, $sc, $mc, $cs, $page, $count){
        $regex = new Regex();
        $where = new Where();
        $where->notEqualTo("t_goods.state", -1, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        if($cs != ""){
            $regex->checkId($mc);
            if($regex->getCode() != 0){

            }else{
                $where->equalTo("t_goods.classid", intval($cs), Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
            }
        }
        if($mc != ""){
            $regex->checkId($mc);
            if($regex->getCode() != 0){

            }else{
                $where->equalTo("t_goods.mainclassid", intval($mc), Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
            }
        }
        if($sc != ""){
            $regex->checkId($sc);
            if($regex->getCode() != 0){

            }else{
                $select = new Select("t_goods_mainclass");
                $select->columns(array("ID"))
                    ->where(array("superclassid" => $sc));
                $where->in("t_goods.mainclassid", $select);
            }
        }
        if($key != ""){
            $regex->checkSearchText($key);
            if($regex->getCode() != 0){

            }else{
                $where->like("t_goods.name", "%$key%");
            }
        }
        $sellerIdSelect = new Select('t_seller');
        $sellerIdSelect->columns(array('id'));
        $sellerIdSelect->where(array('state' => 9));
        $where->notIn('t_goods.sellerId', $sellerIdSelect);
        $goods = $this->getGoodsTable()->fetchSlice($page, $count, $where);
        $this->getGoodsStandardTable()->fillStandards($goods);

        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $vm = new ViewModel(array(
            "goodsArr" => $goods,
        ));
        $vm->setTemplate("home/search/searchGoods");
        $html = $viewRender->render($vm);
        return $html;
    }

    protected function getSearchSellersHtml($type, $key, $sc, $mc, $cs, $page, $count){
        $regex = new Regex();
        $where = new Where();
        $where->equalTo("state", 1, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        if($key != ""){
            $regex->checkSearchText($key);
            if($regex->getCode()!=0){

            }else{
                $where->like("name", "%$key%");
            }
        }
        $sellers = $this->getSellerTable()->fetchSlice($page, $count, $where);

        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $vm = new ViewModel(array(
            "sellers" => $sellers,
        ));
        $vm->setTemplate("home/search/searchSellers");
        $html = $viewRender->render($vm);
        return $html;
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
     * @return \Goods\Model\MainClassTable
     */
    public function getSuperClassTable(){
        if(!$this->_superClassTable){
            $t = $this->getServiceLocator();
            $this->_superClassTable = $t->get('Goods\Model\SuperClassTable');
        }
        return $this->_superClassTable;
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

    /**
     * @return \Goods\Model\ClassTable
     */
    public function getClassTable(){
        if(!$this->_classTable){
            $t = $this->getServiceLocator();
            $this->_classTable = $t->get('Goods\Model\ClassTable');
        }
        return $this->_classTable;
    }

} 