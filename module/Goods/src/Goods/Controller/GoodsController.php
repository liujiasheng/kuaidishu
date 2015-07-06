<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Goods for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Goods\Controller;

use AdminBase\Model\AdminMenu;
use Application\Entity\Goods;
use Application\Entity\GoodsClass;
use Application\Entity\GoodsMainClass;
use Application\Entity\SellerClassRelation;
use Application\Model\AliOssConfig;
use Application\Model\AliOssOperator;
use Application\Model\ImageCompresser;
use Application\Model\Logger;
use Application\Model\Regex;
use Application\Model\StaticInfo;
use Composer\Package\AliasPackage;
use Goods\Model\MainClassTable;
use Zend\Db\Sql\Where;
use Zend\File\Transfer\Adapter\Http;
use Zend\Form\Element\DateTime;
use Zend\Json\Json;
use Zend\Ldap\Node\RootDse\eDirectory;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Exception;

class GoodsController extends AbstractActionController
{
    protected $_mainClassTable;
    protected $_classTable;
    protected $_goodsTable;
    protected $_sellerTable;
    protected $_sellerClassRelationTable;
    protected $_goodsStandardTable;

    public function indexAction()
    {
        $vm = null;
        try{
            $basePath = $this->getRequest()->getBasePath();
            $cf = new AdminMenu();
            $menu = $cf->getMenu('商品管理', $basePath);
            $mainClassTable = $this->getMainClassTable()->fetchAll();
            $goodsTable = $this->getGoodsTable()->fetchSlice(1,10,null);
            $sellerTable = $this->getSellerTable()->fetchSlice(1,1000);
            $arr = array(
                "mainClassTable" => $mainClassTable,
                "goodsTable" => $goodsTable,
                "sellerTable" => $sellerTable,
                "count" => $this->getGoodsTable()->fetchCount(null),
                "menu" => $menu,
            );
            $fw = $this->forward();
            $this->layout()->setVariable("topNavbar",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "topnavbar",
            )));
            $this->layout()->setVariable("adminHeader",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "adminHeader",
            )));
            $this->layout()->setVariable("footer",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "footer",
            )));
            $vm =  new ViewModel($arr);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    public function searchAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        try{
            if($request->isPost()){
                $data = $request->getPost();
                $state = $data["state"];
                $text = $data["searchText"];
                $curPage = $data["curPage"];
                $pageCount = $data["pageCount"];
                $sellerId = $data["sellerId"];
                $mainClassId = $data["mainClassId"];
                $classId = $data["classId"];
                $where = array();
                if($sellerId!="0")
                    $where[] = "t_goods.sellerId = ".$sellerId;
                if($mainClassId!="0")
                    $where[] = "t_goods.mainClassId = ".$mainClassId;
                if($classId!="0")
                    $where[] = "t_goods.classId = ".$classId;
                if($state != "0")
                    $where[] = "t_goods.state = ".$state;
                if($text!="")
                    $where[] = "t_goods.name like '%".$text."%'";
                //check text
                if($text!=""){
                    $regex = new Regex();
                    $regex->flush()->checkSearchText($text);
                    if($regex->getCode() != 0){
                        $response->setContent(Json::encode(array(
                            "state" => false,
                            "code" => $regex->getCode(),
                            "message"=>$regex->getMessage())));
                        return $response;
                    }
                }
                $slice = $this->getGoodsTable()->fetchSlice((int)$curPage, (int)$pageCount, $where);
                $all = $this->getGoodsTable()->fetchCount($where);
                $cnt = count($slice);
                $goods = array();
                for($k=0; $k<$cnt; $k++){
                    $goods[$k]["id"] = (int)$slice[$k]->getID();
                    $goods[$k]["name"] = $slice[$k]->getName();
                    $goods[$k]["price"] = (float)$slice[$k]->getPrice();
                    $goods[$k]["mainClassName"] = $slice[$k]->getMainClassName();
                    $goods[$k]["className"] = $slice[$k]->getClassName();
                    $goods[$k]["sellerName"] = $slice[$k]->getSellerName();
                    $goods[$k]["state"] = $slice[$k]->getState();
                }
                if($cnt == 0)
                    $response->setContent(\Zend\Json\Json::encode(array("state" => true, "count" => 0)));
                else
                    $response->setContent(\Zend\Json\Json::encode(array("state" => true, "count" => $all,
                        "goodsTable" => $goods), true));
            }
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $response;
    }

    public function addGoodsAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    if($data["standard"] == null){
                        $code = 10001;
                        $message = "参数不全";
                        break;
                    }
                    //end check

                    $goods = new Goods();
                    $goods->setOptions(array(
                        "ID" => 0,
                        "MainClassID" => $data["mainClassId"],
                        "ClassID" => $data["classId"],
                        "SellerID" => $data["sellerId"],
                        "Name" => $data["name"],
                        "Price" => $data["price"],
                        "Unit" => $data["unit"],
//                        "Image" => $data[""],
                        "Comment" => $data["comment"],
                        "Barcode" => $data["barcode"],
                        "State" => $data["state"],
                        "Remain" => $data["remain"],
                    ));
                    $regex = new Regex();
                    $regex->flush()
                        ->checkId($goods->getMainClassID())
                        ->checkId($goods->getClassID())
                        ->checkId($goods->getSellerID())
                        ->checkGoodsName($goods->getName())
                        ->checkPrice($goods->getPrice())
                        ->checkGoodsUnit($goods->getUnit())
                        ->checkState($goods->getState())
                        ->checkStandard($data["standard"]);
                    if($regex->getCode()!=0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //end regex

                    //check is class exist
                    $class = $this->getClassTable()->getClass($goods->getClassID());
                    if(!$class){
                        $code = 101;
                        $message = "分类ID错误";
                        break;
                    }
                    if($class->getMainClassID() != $goods->getMainClassID()){
                        $code = 102;
                        $message = "主分类ID错误";
                        break;
                    }
                    $seller = $this->getSellerTable()->getSeller($goods->getSellerID());
                    if(!$seller){
                        $code = 103;
                        $message = "不存在此商家";
                        break;
                    }
                    //check exist
                    if($exist = $this->getGoodsTable()->select(array(
                        "name" => $goods->getName(),
                        "sellerId" => $goods->getSellerID(),
                        "mainClassId" => $goods->getMainClassID(),
                        "classId" => $goods->getClassID()))->current()){
                        $code = 104;
                        $message = "商品已存在";
                        break;
                    }
                    //check file
                    $files = $request->getFiles();
                    if($files->count() > 0){
                        $file = $files->get("image");
                        if($file["error"] != 0){
                            $code = 1041;
                            $message = "图片上传失败";
                            break;
                        }
                        if($file["size"]>1<<23){
                            $code = 105;
                            $message = "图片最大为8M";
                            break;
                        }
                        $this->saveGoodsImage($file, $goods);
                    }else{
                        $code = 106;
                        $message = "图片不能为空";
                        break;
                    }
                    //end check

                    //add seller_class Relation
                    $this->addSellerClassRelation($goods);

                    //begin transation
                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    /** @var $connection \Zend\Db\Adapter\Driver\Pdo\Connection */
                    $connection = $adapter->getDriver()->getConnection();
                    $connection->beginTransaction();

                    //insert goods
                    if(!$nid = $this->getGoodsTable()->saveGoods($goods)){
                        $code = 100;
                        $message = "添加商品失败";
                        $connection->rollback();
                        break;
                    }

                    //add goods default standard
                    $cnt = $this->getGoodsStandardTable()->insert(array(
                        "goodsID" => $nid,
                        "standard" => $data["standard"],
                        "price" => $goods->getPrice(),
                        "state" => $goods->getState(),
                    ));
                    if(!$cnt){
                        $code = 107;
                        $message = "添加规格失败";
                        $connection->rollback();
                        break;
                    }

                    //success
                    $connection->commit();
                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "id" => $nid,
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
                "message" => $message,
            )));
        }
        return $response;
    }

    public function editGoodsAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $goods = new Goods();
                    $goods->setOptions(array(
                        "ID" => $data["id"],
                        "SellerID" => $data["sellerId"],
                        "MainClassID" => $data["mainClassId"],
                        "ClassID" => $data["classId"],
                        "Name" => $data["name"],
//                        "Price" => $data["price"],
                        "Unit" => $data["unit"],
                        "Image" => "",
                        "Comment" => $data["comment"],
                        "Barcode" => $data["barcode"],
                        "State" => $data["state"],
                        "Remain" => $data["remain"],
                    ));
                    $regex = new Regex();
                    $regex->flush()
                        ->checkId($goods->getID())
                        ->checkId($goods->getMainClassID())
                        ->checkId($goods->getClassID())
                        ->checkGoodsName($goods->getName())
//                        ->checkPrice($goods->getPrice())
                        ->checkGoodsUnit($goods->getUnit())
                        ->checkState($goods->getState());
                    if($regex->getCode()!=0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //check image
                    $files = $request->getFiles();
                    if($files->count() > 0){
                        $file = $files->image;
                        if($file["error"] != 0){
                            $code = 1011;
                            $message = "图片上传失败";
                            break;
                        }
                        if($file["size"]>1<<23){
                            $code = 102;
                            $message = "图片最大为8M";
                            break;
                        }
                        $this->saveGoodsImage($file, $goods);
                    }

                    //add seller_class Relation
                    $this->addSellerClassRelation($goods);

                    //update
                    $res = $this->getGoodsTable()->saveGoods($goods);
                    if(!$res && $goods->getImage()==""){
                        $code = 101;
                        $message = "数据没有更新";
                        break;
                    }
                    $response->setContent(Json::encode(array(
                        "state" => true,
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
                "message" => $message,
            )));
        }
        return $response;
    }

    public function viewAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $id = $data["id"];
                    $regex = new Regex();
                    $regex->flush()->checkId($id);
                    if($regex->getCode()!=0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //get goods
                    if(!$goods = $this->getGoodsTable()->getGoods($id)){
                        $code = 100;
                        $message = "无法获取商品信息";
                        break;
                    }else{
                        $standards = $this->getGoodsStandardTable()->fetchAllByGoodsID($goods->getID());
                        $range = $this->getGoodsStandardTable()->getPriceRange($standards);
                        $response->setContent(Json::encode(array(
                            "state" => true,
                            "goods" => array(
                                "Name" => $goods->getName(),
                                "Price" => ($range["max"]==$range["min"]?$range["max"]:$range["min"]."-".$range["max"]),
                                "Unit" => $goods->getUnit(),
                                "Image" => $goods->getImage(),
                                "Comment" => $goods->getComment(),
                                "Barcode" => $goods->getBarcode(),
                                "State" => intval($goods->getState()),
                                "Remain" => intval($goods->getRemain()),
                                "SellerName" => $goods->getSellerName(),
                                "MainClassName" => $goods->getMainClassName(),
                                "ClassName" => $goods->getClassName(),
                                "SellerID" => $goods->getSellerID(),
                                "MainClassID" => $goods->getMainClassID(),
                                "ClassID" => $goods->getClassID(),
                            ),
                        )));
                    }
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
                "message" => $message,
            )));
        }
        return $response;
    }

    public function getGoodsStandardAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $goodsID = $data['id'];
                    if($goodsID == null){
                        $code = 400001;
                        $message = "参数不全";
                        break;
                    }
                    //Regex the params
                    $regex = new Regex();
                    $regex->checkId($goodsID);
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //end check

                    $arr = $this->getGoodsStandardTable()->fetchAllByGoodsID($goodsID);
                    $goods = $this->getGoodsTable()->getGoods($goodsID);

                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "standards" => $arr,
                        "goods" => array(
                            "name" => $goods->getName(),
                        ),
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
                "message" => $message,
            )));
        }
        return $response;
    }

    //编辑商品的规格
    public function editGoodsStandardAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $goodsID = $data['goodsId'];
                    $standards = $data['standards'];
                    if($goodsID==null || $standards==null ||
                        !is_array($standards) || count($standards)< 1){
                        $code = 40001;
                        $message = "参数不全";
                        break;
                    }
                    //regex
                    $regex = new Regex();
                    $regex->checkId($goodsID);
                    foreach($standards as $standard){
                        if($standard["standard"]==null || $standard["price"]==null
                            || $standard["state"]==null){
                            $code = 40002;
                            $message = "参数错误";
                            break;
                        }
                        $regex->checkStandard($standard["standard"])
                            ->checkPrice($standard["price"])
                            ->checkState($standard["state"]);
                    }
                    if($code != 0 ) break;
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //check same standard name
                    $standardNames = array();
                    foreach($standards as $standard) $standardNames[] = $standard["standard"];
                    $standardNameCntArr = array_count_values($standardNames);
                    foreach($standardNameCntArr as $cnt){
                        if($cnt > 1){
                            $code = 40005;
                            $message = "规格名相同";
                            break;
                        }
                    }
                    if($code != 0 )break;
                    // end check

                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    /** @var $connection \Zend\Db\Adapter\Driver\Pdo\Connection */
                    $connection = $adapter->getDriver()->getConnection();
                    $connection->beginTransaction();
                    $mark = true;
                    $delCnt = $this->getGoodsStandardTable()->delete(array(
                        "GoodsID" => $goodsID
                    ));
                    if($delCnt < 1){
                        $code = 40003;
                        $message = "更新失败";
                        $connection->rollback();
                        break;
                    }
                    foreach($standards as $standard){
                        $res = $this->getGoodsStandardTable()->insert(array(
                            "goodsId" => $goodsID,
                            "standard" => $standard["standard"],
                            "price" => $standard["price"],
                            "state" => $standard["state"]
                        ));
                        if($res < 1){
                            $mark = false;
                            break;
                        }
                    }
                    if(!$mark){
                        $code = 40004;
                        $message = "更新失败";
                        $connection->rollback();
                        break;
                    }

                    $connection->commit();

                    $response->setContent(Json::encode(array(
                        "state" => true,
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
                "message" => $message,
            )));
        }
        return $response;
    }

    public function fooTestAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    //check file
                    $files = $request->getFiles();
                    if($files->count() > 0){
                        $file = $files->get("image");
                        if($file["error"] != 0){
                            $code = 1041;
                            $message = "图片上传失败";
                            break;
                        }
                        if($file["size"]>1<<23){
                            $code = 105;
                            $message = "图片最大为8M";
                            break;
                        }
                        //$this->saveGoodsImage($file, $goods);
                        //save image

                        $rsl = $this->saveToOSS($file);
                        if(!$rsl){
                            $code = -1;
                            $message = "保存图片失败";
                            break;
                        }


                    }else{
                        $code = 106;
                        $message = "图片不能为空";
                        break;
                    }
                    //end check


                    $response->setContent(Json::encode(array(
                        "state" => true,
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
                "message" => $message,
            )));
        }
        return $response;
    }
    public function fooAction()
    {
        return array();
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

    private function addSellerClassRelation($goods){
        $scr = new SellerClassRelation();
        $scr->setSellerID($goods->getSellerID())
            ->setMainClassID($goods->getMainClassID())
            ->setClassID($goods->getClassID());
        if(!$exist = $this->getSellerClassRelationTable()->isExist($scr)){
            $this->getSellerClassRelationTable()->saveRelation($scr);
        }
    }

    /**
     * @param $oldName
     * @param $goods \Application\Entity\Goods
     */
    private function getGoodsNewName($oldName, $goods){
        $oldType = substr($oldName,strrpos($oldName,"."));
        //$this->type = $oldType;
        //$newName = time().floor(microtime() * 10000).rand(10, 99);
        $newName =  $goods->getSellerID()."-".$goods->getMainClassID()."-".$goods->getClassID()."-".md5($goods->getName())."-".time();
        return $newName.$oldType;
    }

    /**
     * @param $file
     * @param $goods \Application\Entity\Goods
     */
    private  function saveGoodsImage($file, $goods){
        $newName = $this->getGoodsNewName($file['name'], $goods);

        //压缩文件，生成4个图片(等比压缩)
        //tiny  maxHeight 50px,
        //small maxHeight 100px,
        //medium maxHeight 200px,
        //large maxHeight 300px
        $tinyImg = ImageCompresser::compressImage($file, 100, 50 );
        $smallImg = ImageCompresser::compressImage($file, 100, 100);
        $mediumImg = ImageCompresser::compressImage($file, 100, 200);
        $largeImg = ImageCompresser::compressImage($file, 100, 768);
        $tinyMeta = stream_get_meta_data($tinyImg);
        $smallMeta = stream_get_meta_data($smallImg);
        $mediumMeta = stream_get_meta_data($mediumImg);
        $largeMeta = stream_get_meta_data($largeImg);

        //存储文件
        if( AliOssOperator::uploadFile($tinyMeta['uri'], $newName, AliOssConfig::getGoodsTinyImageDir()) &&
            AliOssOperator::uploadFile($smallMeta['uri'], $newName, AliOssConfig::getGoodsSmallImageDir()) &&
            AliOssOperator::uploadFile($mediumMeta['uri'], $newName, AliOssConfig::getGoodsMediumImageDir()) &&
            AliOssOperator::uploadFile($largeMeta['uri'], $newName, AliOssConfig::getGoodsLargeImageDir()) ){
            $goods->setImage($newName);
        }else{
            $goods->setImage("goods.jpg");
        }
//        if( copy($tinyMeta['uri'], StaticInfo::getGoodsTinyImageDir().$newName) &&
//            copy($smallMeta['uri'], StaticInfo::getGoodsSmallImageDir().$newName) &&
//            copy($mediumMeta['uri'], StaticInfo::getGoodsMediumImageDir().$newName) &&
//            copy($largeMeta['uri'], StaticInfo::getGoodsLargeImageDir().$newName) ){
//            $goods->setImage($newName);
//        }else{
//            $goods->setImage("goods.jpg");
//        }

    }
//    private  function saveGoodsImage($file, $goods){
//        $fileAdapter = new Http();
//        //重新生成文件名
//        $newName = $this->getGoodsNewName($file['name'], $goods);
//        //储存文件
//        $fileAdapter->addFilter('File\Rename',
//            array('target'    => StaticInfo::getGoodsImageDir().iconv("UTF-8","gb2312", $newName),
//                'overwrite' => true,
//                'source'    => $file['tmp_name'],
//
//            ));
//        if ($fileAdapter->receive($file['name'])){
//            $resFile = $fileAdapter->getFilter('File\Rename')->getFile();
//            $goods->setImage($newName);
//        }else{
//            $goods->setImage("goods.jpg");
//        }
//    }
    private function saveToOSS($file){
        $rsl = AliOssOperator::uploadFile($file["tmp_name"], "happytest.jpg" , "test/");
        return $rsl;
    }

}
