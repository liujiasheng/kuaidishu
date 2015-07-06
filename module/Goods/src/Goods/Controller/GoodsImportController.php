<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-17
 * Time: 下午1:18
 */

namespace Goods\Controller;


use Application\Entity\Goods;
use Application\Model\AliOssConfig;
use Application\Model\AliOssOperator;
use Application\Model\ImageCompresser;
use Application\Model\Regex;
use Goods\Model\GoodsStandardTable;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class GoodsImportController extends AbstractActionController{

    protected $_sellerTable;
    protected $_goodsTable;
    protected $_mainClassTable;
    protected $_classTable;
    protected $_goodsStandardTable;
    protected $_sellerClassRelationTable;

    public function indexAction(){
        $this->setRenderer();
        return new ViewModel(array(
            "sellers" => $this->getSellerTable()->fetchAll(),
        ));
    }

    public function importAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $sellerId = $data["sellerId"];
                    $dir = $data["dir"];
                    $xls = $request->getFiles('xls');

                    if($sellerId == null || $xls == null || $dir == null){
                        $code = 20005;
                        $message = "参数不全";
                        break;
                    }
                    //end check

                    $table = $this->getTableFromXls($xls);
                    $validArr = $this->validateTable($table, $dir);
                    if(!$validArr["state"]){
                        $code = 20006;
                        $message = $validArr["message"];
                        break;
                    }
                    //return $response;
                    //begin to add goods
                    $rsl = $this->addGoodsFromTable($sellerId, $dir, $table);

                    if(!$rsl){
                        $code = 20008;
                        $message = "导入失败";
                        break;
                    }

                    $response->setContent(Json::encode(array(
                        "state" => true,
                    )));
                }
            }while(false);
        }catch (\Exception $ex){

        }
        if( $code != 0 ){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    protected function getTableFromXls($file){
        $phpExcel = new \PHPExcel();
        $objReader = \PHPExcel_IOFactory::createReader('Excel5');
        $objExcel = $objReader->load($file["tmp_name"]);
        $sheet = $objExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumn = \PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn()) ;

        $colLabels = array(
            0 => "image",
            1 => "name",
            2 => "price",
            3 => "unit",
            4 => "standards",
            5 => "class"
        );
        $table = array();
        for($rowIndex = 2; $rowIndex <= $highestRow; $rowIndex++){
            for($colIndex = 0; $colIndex < 6; $colIndex++){
                $table[$rowIndex][$colLabels[$colIndex]] =  $sheet->getCellByColumnAndRow($colIndex, $rowIndex)->getValue();
            }
        }

        return $this->trimTable($table);
    }

    protected function validateTable($table, $dir){
        $message = "";
        $flag = true;

        do{
            //check null
            if(!$table || !is_array($table) || count($table)<1){
                $message = "xls表table为空";
                $flag = false;
                break;
            }
            foreach($table as $key => $row){
                if($row["image"] == null || $row["name"] == null || $row["price"] == null ||
                    $row["unit"] == null || $row["class"] == null ){
                    $message = "row ".$key." 参数不全";
                    $flag = false;
                    break;
                }
            }
            if(!$flag) break;

            //check classes exist
            $classTmpArr = array();
            foreach($table as $row){
                $classTmpArr[] = $row["class"];
            }
            $classTmpArr = array_unique($classTmpArr);
            $select = new Select("t_goods_class");
            $select->columns(array(new Expression("distinct(name) as name")))
                ->where->in("name",$classTmpArr);
            $rows = $this->getClassTable()->selectWith($select)->toArray();
            $classDbArr = array();
            foreach($rows as $row){
                $classDbArr[] = $row["name"];
            }
            foreach($classTmpArr as $class){
                if(!in_array($class, $classDbArr)){
                    $flag = false;
                    $message = $class." 不存在数据库内，请先添加分类";
                    break;
                }
            }
            if(!$flag) break;

            //check files exist
            foreach($table as $row){
                if( $row["image"] != "empty" && !file_exists($dir."/".iconv("UTF-8","gb2312",$row["image"]) )){
                    $message .= $dir."/".$row["image"]." doesn't exist\n";
                    $flag = false;
//                    break;
                }
            }
            if(!$flag) break;

            //check regex
            foreach($table as $key => $row){
                $goods = new Goods(array(
                    "Name" => $row["name"],
                    "Price" => $row["price"],
                    "Unit" => $row["unit"],
                ));
                $regex = new Regex();
                $regex->flush()
                    ->checkGoodsName($goods->getName())
                    ->checkPrice($goods->getPrice())
                    ->checkGoodsUnit($goods->getUnit());
                if($regex->getCode() != 0){
                    $flag = false;
                    $message = "row ".$key. " ".$goods->getName()." ".$goods->getPrice(). " " .$goods->getUnit()." is invalid\n";
                    $message .= $regex->getMessage();
                    break;
                }
            }
            if(!$flag) break;




        }while(false);



        return array(
            "state" => $flag,
            "message" => $message,
        );
    }

    protected function trimTable($table){
        $trimTable = array();
        foreach($table as $key => $row){
            if($row["image"]){
                $trimTable[$key] = $row;
            }
        }
        return $trimTable;
    }

    protected function addGoodsFromTable($sellerId, $imgDir, $tableSrc){
        $flag = true;
        $code = 0;
        $message = "";
        try{
            do{
                $chunkSize = 100;
                $tableChunks = array_chunk($tableSrc, $chunkSize);

                foreach($tableChunks as $table){

                    //begin transation
                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    /** @var $connection \Zend\Db\Adapter\Driver\Pdo\Connection */
                    $connection = $adapter->getDriver()->getConnection();
                    $connection->beginTransaction();


                    foreach($table as $rowNum => $row){
//                    begin foreach
                        $class = null;
                        $classes = $this->getClassTable()->getClassesByName($row["class"]);
                        if( $classes!=null && count($classes) > 0){
                            $class = $classes[0];
                        }
                        $goods = new Goods(array(
                            "ID" => 0,
                            "MainClassID" => $class->getMainClassID(),
                            "ClassID" => $class->getID(),
                            "SellerID" => $sellerId,
                            "Name" => $row["name"],
                            "Price" => $row["price"],
                            "Unit" => $row["unit"],
                            "Comment" => "",
                            "Barcode" => "",
                            "State" => 1,
                            "Remain" => 100,
                        ));
//                        $regex = new Regex();
//                        $regex->flush()
//                            ->checkId($goods->getMainClassID())
//                            ->checkId($goods->getClassID())
//                            ->checkId($goods->getSellerID())
//                            ->checkGoodsName($goods->getName())
//                            ->checkPrice($goods->getPrice())
//                            ->checkGoodsUnit($goods->getUnit())
//                            ->checkState($goods->getState());
//                        if( $regex->getCode() != 0){
//                            $code = $regex->getCode();
//                            $message = $regex->getMessage();
//                            break;
//                        }
                        //end check

                        //add seller_class Relation
                        $this->getSellerClassRelationTable()->addSellerClassRelation($goods);
                        //save file
                        if($row["image"] == "empty"){
                            $goods->setImage("goods.jpg");
                        }
                        else if(!$this->saveGoodsImage($row["image"], $imgDir."/".iconv("UTF-8","gb2312",$row["image"]), $goods)){
                            $message = "上传图片失败";
                            $code = 20003;
                            break;
                        }
                        //save goods
                        $gid = $this->getGoodsTable()->saveGoods($goods);
                        //save standards
                        //add goods default standard
                        $cnt = $this->getGoodsStandardTable()->insert(array(
                            "goodsID" => $gid,
                            "standard" => "默认",
                            "price" => $goods->getPrice(),
                            "state" => $goods->getState(),
                        ));
                        if(!$cnt){
                            $code = 20004;
                            $message = "添加规格失败";
                            break;
                        }
//                    endforeach
                    }

                    if($code != 0){
                        $connection->rollback();
                        throw new \Exception($message, $code);
                    }

                    $connection->commit();


                }

                if($code != 0){
                    break;
                }

            }while(false);
        }catch (\Exception $e){
            throw $e;
        }
        return $flag;
    }

    private function saveGoodsImage($filename, $fileDir, $goods){
        $newName = $this->getGoodsNewName($filename, $goods);

        //压缩文件，生成4个图片(等比压缩)
        //tiny  maxHeight 50px,
        //small maxHeight 100px,
        //medium maxHeight 200px,
        //large maxHeight 300px
        $tinyImg = ImageCompresser::compressImageImport($fileDir, 100, 50 );
        $smallImg = ImageCompresser::compressImageImport($fileDir, 100, 100);
        $mediumImg = ImageCompresser::compressImageImport($fileDir, 100, 200);
        $largeImg = ImageCompresser::compressImageImport($fileDir, 100, 768);
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
            return true;
        }else{
            $goods->setImage("goods.jpg");
            return false;
        }
    }
    private function getGoodsNewName($oldName, $goods){
        $oldType = substr($oldName,strrpos($oldName,"."));
//        $newName =  $goods->getSellerID()."-".$goods->getMainClassID()."-".$goods->getClassID()."-".$goods->getName()."-".time();
        $newName =  $goods->getSellerID()."-".$goods->getMainClassID()."-".$goods->getClassID()."-".md5($goods->getName())."-".time();
        return $newName.$oldType;
    }

    public function longCallAction(){

        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $t = $request->getQuery("time");

        if($t){
            sleep(intval($t));
        }

        return $this->getResponse()->setContent(Json::encode(array(
            "state" => true,
            "time" => $t? $t:"0",
        )));
    }

    public function fooAction(){
        $response = $this->getResponse();

        return $response;

        $flag = true;
        $message = "";

        $rows = $this->getGoodsTable()->fetchSlice(1,1000,array(
            "sellerid" => 100208,//哈茶
        ));
        $goodsIdArr = array();
        foreach($rows as $row){
            $goodsIdArr[] = $row->getID();
        }
        $rows = $this->getGoodsStandardTable()->fetchAllByGoodsIDS($goodsIdArr);

        //begin transation
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        /** @var $connection \Zend\Db\Adapter\Driver\Pdo\Connection */
        $connection = $adapter->getDriver()->getConnection();
        $connection->beginTransaction();

        foreach($rows as $rowT){
            $row = $rowT[0];
            $res = $this->getGoodsStandardTable()->insert(array(
                "GoodsID" => $row["GoodsID"],
                "Standard" => "500cc",
                "Price" => $row["Price"],
                "State" => 1,
            ));
            if(!$res){
                $flag = false;
                break;
            }
            $res = $this->getGoodsStandardTable()->insert(array(
                "GoodsID" => $row["GoodsID"],
                "Standard" => "700cc",
                "Price" => floatval($row["Price"]) + 2,
                "State" => 1,
            ));
            if(!$res){
                $flag = false;
                break;
            }
        }
        if(!$flag){
            $connection->rollback();
        }else{
            $connection->commit();
        }

        $response->setContent(Json::encode(array(
            "state" => $flag,
            "message" => $message,
        )));
        return $response;
    }







    private function setRenderer(){
        $baseUrl = $this->getRequest()->getBaseUrl();
        $renderer = $this->getServiceLocator()->get( 'Zend\View\Renderer\PhpRenderer');
        $renderer->headScript()->prependFile($baseUrl . '/js/import/goods.js');
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
} 