<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-10
 * Time: 下午4:58
 */


namespace Goods\Model;

use Application\Entity\Goods;
use Application\Entity\GoodsMainClass;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;

class GoodsTable extends AbstractTableGateway{

    protected $table = "t_goods";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }


    public function fetchSlice($page, $count, $where=null){
        $select = $this->getRawSelect();
        $select->order("t_goods.state asc, t_goods.id desc")
            ->limit($count)
            ->offset(($page-1)*$count)
            ->where('t_goods.state != -1');
        if($where!=null && count($where)) $select->where($where);
        $resultSet = $this->selectWith($select);
        $entities = $this->pushResultSet($resultSet);
        return $entities;
    }
    public function fetchSliceWithGroup($page, $count, $where=null){
        $select = $this->getRawSelect();
        $select->order("t_goods.id desc")
            ->limit($count)
            ->offset(($page-1)*$count)
            ->where('t_goods.state != -1');
        if($where!=null && count($where)) $select->where($where);
        $resultSet = $this->selectWith($select);
        $entities = $this->pushResultSetWithGroup($resultSet);
        return $entities;
    }

    /**
     * @param null $where
     * @return int
     */
    public function fetchCount($where = null){
        $select = new Select($this->table);
        $select->where(" state != -1");
        if($where!=null && count($where)) $select->where($where);
        $count = $this->selectWith($select)->count();
        return $count;
    }

    public function fetchSliceByClassID($page, $count, $id){
        return $this->fetchSlice($page, $count, new Where(array(
            "ClassID" => $id,
        )));
    }

    public function getGoods($id){
        $select = $this->getRawSelect();
        $select->where(array(
            "t_goods.id" => $id
        ));
        $row = $this->selectWith($select)->current();
        if(!$row) return false;
        $goods = $this->fillGoods($row);
        return $goods;
    }

    /**
     * @param $goods \Application\Entity\Goods
     */
    public function saveGoods($goods){
        $id = $goods->getID();
        if($id==0){ //insert
            $res = $this->insert(array(
                "MainClassID" => $goods->getMainClassID(),
                "ClassID" => $goods->getClassID(),
                "SellerID" => $goods->getSellerID(),
                "Name" => $goods->getName(),
//                "Price" => $goods->getPrice(),
                "Unit" => $goods->getUnit(),
                "Image" => $goods->getImage(),
                "Comment" => $goods->getComment(),
                "Barcode" => $goods->getBarcode(),
                "State" => $goods->getState(),
                "Remain" => $goods->getRemain(),
            ));
            if(!$res) return false;
            return $this->getLastInsertValue();
        }else{ //update
            $setter = array(
                "MainClassID" => $goods->getMainClassID(),
                "ClassID" => $goods->getClassID(),
                "Name" => $goods->getName(),
//                "Price" => $goods->getPrice(),
                "Unit" => $goods->getUnit(),
//                "Image" =>
                "Comment" => $goods->getComment(),
                "Barcode" => $goods->getBarcode(),
                "State" => $goods->getState(),
                "Remain" => $goods->getRemain(),
            );
            if($goods->getImage()!=""){
                $setter["Image"] = $goods->getImage();
            }
            $res = $this->update($setter, array(
                "id" => $goods->getID()
            ));
            if(!$res) return false;
            return $id;
        }
    }

    public function getRandomGoodsBySellerID($sellerId, $count){
        $select = $this->getRawSelect();
        $where = new Where();
        $where->equalTo('t_goods.sellerId', $sellerId, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $where->equalTo('t_goods.state', 1, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $rand = new Expression("rand()");
        $select->limit($count)
            ->where($where)
            ->order($rand);
        $rows = $this->selectWith($select);
        $goods = $this->pushResultSet($rows);
        return $goods;
    }

    public function getRandomGoodsByWhere($where, $count){
        $select = $this->getRawSelect();
        $rand = new Expression("rand()");
        $select->limit($count)
            ->where($where)
            ->order($rand);
        $rows = $this->selectWith($select);
        $goods = $this->pushResultSet($rows);
        return $goods;
    }

    public function getRandomGoodsBySuperClassID($superclassId, $count){
        $select = new Select("t_goods_mainclass");
        $select->columns(array("ID"))
            ->where(array("superclassid" => $superclassId));
        $where = new Where();
        $where->in("t_goods.mainclassid", $select);
        $sellerIdSelect = new Select('t_seller');
        $sellerIdSelect->columns(array('id'));
        $sellerIdSelect->where(array('state' => 9));
        $where->notIn('t_goods.sellerId', $sellerIdSelect);
        $where->equalTo('t_goods.state', 1, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        return $this->getRandomGoodsByWhere($where, $count);
    }

    protected function pushResultSet($resultSet){
        $entities = array();
        foreach ($resultSet as $row){
            $goods = $this->fillGoods($row);
            $entities[] = $goods;
        }
        return $entities;
    }

    protected function pushResultSetWithGroup($resultSet){
        $entities = array();
        foreach ($resultSet as $row){
            $goods = $this->fillGoods($row);
            $entities[$goods->getMainClassID()]["MainClassName"] = $goods->getMainClassName();
            $entities[$goods->getMainClassID()]["MainClassID"] = $goods->getMainClassID();
            $entities[$goods->getMainClassID()]["Goods"][] = $goods;
        }
        return $entities;
    }

    protected function fillGoods($row){
        $goods = new Goods();
        $goods->setOptions(array(
            "ID"=>$row->ID,
            "MainClassID"=>$row->MainClassID,
            "ClassID"=>$row->ClassID,
            "SellerID"=>$row->SellerID,
            "Name"=>$row->Name,
            "Price"=>$row->Price,
            "Unit" =>$row->Unit,
            "Image"=>$row->Image,
            "Comment"=>$row->Comment,
            "Barcode"=>$row->Barcode,
            "State"=>$row->State,
            "Remain"=>$row->Remain,
            "SellerName"=>$row->SellerName,
            "MainClassName"=>$row->MainClassName,
            "ClassName"=>$row->ClassName,
        ));
        return $goods;
    }

    protected function getRawSelect(){
        $select = new Select($this->table);
        $select->join(array("s"=>"t_seller"),
            "t_goods.sellerId = s.id",
            array("SellerName" => "name"),
            Select::JOIN_LEFT)

            ->join(array("c"=>"t_goods_class"),
                "t_goods.classId = c.id",
                array("ClassName" => "name"),
                Select::JOIN_LEFT)

            ->join(array("m"=>"t_goods_mainclass"),
                "t_goods.mainClassId = m.id",
                array("MainClassName" => "name"),
                Select::JOIN_LEFT);

//            ->join(array("gs"=>"t_goods_standard"),
//                "t_goods.id = gs.goodsId",
//                array(
//                    "StandardID" => "id",
//                    "Standard" => "standard",
//                    "StandardPrice" => "price",
//                    "StandardState" => "state",),
//                Select::JOIN_RIGHT);
        return $select;
    }

}