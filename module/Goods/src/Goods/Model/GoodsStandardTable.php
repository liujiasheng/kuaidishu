<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-7
 * Time: 下午7:11
 */

namespace Goods\Model;


use Application\Entity\Goods;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;

class GoodsStandardTable extends AbstractTableGateway{

    protected $table = "t_goods_standard";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function fetchAllByGoodsID($goodsID){
        $resultSet = $this->select(array(
            "goodsID" => $goodsID
        ));
        $arr = $resultSet->toArray();

        return $arr;
    }

    public function fetchAllByGoodsIDS($IdArr){
        if(count($IdArr) < 1) return array();
        $where = new Where();
        $where->in("goodsId",$IdArr);
        $resultSet = $this->select($where);
        $arr = array();
        foreach($resultSet as $row){
            $arr[$row->GoodsID][] = array(
                "ID" => $row->ID,
                "GoodsID" => $row->GoodsID,
                "Standard" => $row->Standard,
                "Price" => $row->Price,
                "State" => $row->State,
            );
        }
        return $arr;
    }

    public function getPriceRange($standards){
        $max = $min = 0;
        if($standards && is_array($standards) && count($standards)>0){
            $max = $min = floatval($standards[0]["Price"]);
            foreach($standards as $standard){
                if(floatval($standard["Price"]) > $max) $max = floatval($standard["Price"]);
                if(floatval($standard["Price"]) < $min) $min = floatval($standard["Price"]);
            }
        }
        return array(
            "max" => $max,
            "min" => $min
        );
    }

    public function getGoodsByStandardID($standardID){
        $standards = $this->getGoodsByStandardIDs(array($standardID));
        if(count($standards)<1) return null;
        return $standards[0];
    }

    public function getGoodsByStandardIDs($standardIdArr){
        $select = new Select($this->table);
        $select->columns(array(
                "StandardID" => "ID",
                "Standard" => "Standard",
                "StandardPrice" => "Price",
                "StandardState" => "State"
            ))
            ->join(array("g"=>"t_goods"),
                "t_goods_standard.goodsId = g.id",
                array(
                    "ID"=> "ID",
                    "MainClassID"=> "MainClassID",
                    "ClassID"=> "ClassID",
                    "SellerID"=> "SellerID",
                    "Name"=> "Name",
//                    "Price"=> "Price",
                    "Unit" => "Unit",
                    "Image"=> "Image",
                    "Comment"=>"Comment",
                    "Barcode"=> "Barcode",
                    "State"=> "State",
                    "Remain"=> "Remain"),
                Select::JOIN_LEFT)

            ->join(array("s"=>"t_seller"),
                "g.sellerId = s.id",
                array("SellerName" => "name"),
                Select::JOIN_LEFT)

            ->join(array("c"=>"t_goods_class"),
                "g.classId = c.id",
                array("ClassName" => "name"),
                Select::JOIN_LEFT)

            ->join(array("m"=>"t_goods_mainclass"),
                "g.mainClassId = m.id",
                array("MainClassName" => "name"),
                Select::JOIN_LEFT);

        $where = new Where();
        $where->in("t_goods_standard.id", $standardIdArr);
        $select->where($where);

        $resultSet = $this->selectWith($select);
        $goodsArr = array();
        foreach($resultSet as $row){
            $goods = new Goods(array(
                "ID" => $row->ID,
                "MainClassID" => $row->MainClassID,
                "ClassID" => $row->ClassID,
                "SellerID" => $row->SellerID,
                "Name" => $row->Name,
//                "Price" => $row->Price,
                "Unit" => $row->Unit,
                "Image" => $row->Image,
                "Comment" => $row->Comment,
                "Barcode" => $row->Barcode,
                "State" => $row->State,
                "Remain" => $row->Remain,
                "SellerName" => $row->SellerName,
                "MainClassName" => $row->MainClassName,
                "ClassName" => $row->ClassName,
                "GoodsStandards" => [array(
                    "ID" => $row->StandardID,
                    "Standard" => $row->Standard,
                    "Price" => $row->StandardPrice,
                    "State" => $row->StandardState,
                )]
            ));
            $goodsArr[] = $goods;
        }
        return $goodsArr;
    }

    /**
     * @param $goodsArr array
     */
    public function fillStandards($goodsArr){
        $idArr = array();
        foreach($goodsArr as $goods) $idArr[] = $goods->getID();
        $standards = $this->fetchAllByGoodsIDS($idArr);
        foreach($goodsArr as $goods){
            /** @var $goods \Application\Entity\Goods */
            $goods->setGoodsStandards($standards[$goods->getID()]);
        }
    }

    public function fillStandardsWithGroup($goodsArr){
        $idArr = array();
        foreach($goodsArr as $group){
            foreach($group["Goods"] as $goods){
                $idArr[] = $goods->getID();
            }
        }
        $standards = $this->fetchAllByGoodsIDS($idArr);
        foreach($goodsArr as $group){
            foreach($group["Goods"] as $goods){
                /** @var $goods \Application\Entity\Goods */
                $goods->setGoodsStandards($standards[$goods->getID()]);
            }
        }
    }

} 