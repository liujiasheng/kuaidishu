<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Home for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Home\Controller;

use Application\Model\Logger;
use Application\Model\Regex;
use Authenticate\AssistantClass\UserType;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Exception;
use Zend\View\Model\ViewModel;

class HomeController extends AbstractActionController
{
    protected $_homeSellerTable;
    protected $_goodsTable;
    protected $_goodsStandardTable;
    protected $_mainClassTable;
    protected $_superClassTable;

    public function indexAction()
    {
        $detect = new \Mobile_Detect();
        if( $detect->isMobile() ){
            return $this->redirect()->toUrl('/mobile');
        }

        $vm = null;
        try{
            $homeSeller = $this->getHomeSellerTable()->fetchAll();
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
                $goods = $this->getGoodsTable()->getRandomGoodsBySuperClassID($key, 10);
                $this->getGoodsStandardTable()->fillStandards($goods);
                $goodsGroups[$key] = array(
                    "id" => $superClass["ID"],
                    "name" => $superClass["Name"],
                    "goods" => $goods,
                );
            }
            $arr = array(
                "homeSeller" => $homeSeller,
                "goodsGroups" => $goodsGroups,
                "classArr" => $this->getMainClassTable()->fetchAllSuperWithMainClass(),
            );

            $fw = $this->forward();
            $this->layout()->setVariable("topNavbar",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "topnavbar",
            )));
            $this->layout()->setVariable("topHeader",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "topheader",
            )));
            $this->layout()->setVariable("topNavigation",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "topnavigation",
            )));
            $this->layout()->setVariable("topMenu",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "topmenu",
            )));
            $this->layout()->setVariable("footer",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "footer",
            )));
            $this->layout()->setVariable("advertisement","true");

            $vm = new ViewModel($arr);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    public function getCartInfoAction(){
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $items = $data["items"];
                    if($items==null || count($items) < 1){
                        $code = 101;
                        break;
                    }
                    $regex = new Regex();
                    $idArr = array();
                    $countArr = array();
                    foreach($items as $item){
                        $regex->checkId($item["id"])->checkId($item["count"]);
                        $idArr[] = $item["id"];
                        $countArr[$item["id"]] = $item["count"];
                    }
                    if($regex->getCode()!=0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    if(count($idArr) < 1){
                        $code = 102;
                        break;
                    }
                    //end check

                    $cartGoods = $this->getGoodsStandardTable()->getGoodsByStandardIDs($idArr);
                    $cartItems = array();
                    foreach($cartGoods as $cg){
                        $standard = $cg->getGoodsStandards()[0];
                        $cartItems[] = array(
                            "id" => $standard["ID"],
                            "name" => $cg->getName(),
                            "price" => $standard["Price"],
                            "image" => $cg->getImage(),
                            "mainClassName" => $cg->getMainClassName(),
                            "className" => $cg->getClassName(),
                            "sellerName" => $cg->getSellerName(),
                            "count" => $countArr[$standard["ID"]],
                            "standard" => $standard["Standard"],
                        );
                    }

//                    $where = new Where();
//                    $where->in("t_goods.id",$idArr);
//                    $cartGoods = $this->getGoodsTable()->fetchSlice(1,100,$where);
//                    $cartItems = array();
//                    foreach($cartGoods as $cg){
//                        $cartItems[] = array(
//                            "id" => $cg->getID(),
//                            "name" => $cg->getName(),
//                            "price" => $cg->getPrice(),
//                            "image" => $cg->getImage(),
//                            "mainClassName" => $cg->getMainClassName(),
//                            "className" => $cg->getClassName(),
//                            "sellerName" => $cg->getSellerName(),
//                            "count" => $countArr[$cg->getID()],
//                        );
//                    }

                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "items" => $cartItems,
                    )));
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message
            )));
        }
        return $response;
    }


    /**
     * @return \Home\Model\HomeSellerTable
     */
    public function getHomeSellerTable(){
        if(!$this->_homeSellerTable){
            $t = $this->getServiceLocator();
            $this->_homeSellerTable = $t->get('Home\Model\HomeSellerTable');
        }
        return $this->_homeSellerTable;
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
     * @return \Goods\Model\MainClassTable
     */
    public function getSuperClassTable(){
        if(!$this->_superClassTable){
            $t = $this->getServiceLocator();
            $this->_superClassTable = $t->get('Goods\Model\SuperClassTable');
        }
        return $this->_superClassTable;
    }

}
