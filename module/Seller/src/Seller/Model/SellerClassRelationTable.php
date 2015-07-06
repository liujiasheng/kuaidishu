<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-12
 * Time: 下午1:56
 */

namespace Seller\Model;


use Application\Entity\SellerClassRelation;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;

class SellerClassRelationTable extends AbstractTableGateway{

    protected $table = "t_seller_class_relation";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    /**
     * @param $scr \Application\Entity\SellerClassRelation
     * @return array|\ArrayObject|null
     */
    public function isExist($scr){
        $exist = $this->select(array(
            "sellerId" => $scr->getSellerID(),
            "mainClassId" => $scr->getMainClassID(),
            "classId" => $scr->getClassID(),
        ))->current();
        return $exist;
    }

    /**
     * @param $scr \Application\Entity\SellerClassRelation
     * @return bool|int
     */
    public function saveRelation($scr){
        $res = $this->insert(array(
            "sellerId" => $scr->getSellerID(),
            "mainClassId" => $scr->getMainClassID(),
            "classId" => $scr->getClassID(),
        ));
        if(!$res) return false;
        return $this->getLastInsertValue();
    }

    public function getMainClassesBySellerID($sellerId){
        $select = new Select($this->table);
        $select->join(array("m" => "t_goods_mainclass"),
            "m.id = ".$this->table.".mainclassid",
            array(
                "MainClassName" => "name"
            ),Select::JOIN_LEFT)
            ->where(array(
                $this->table.".sellerId" => $sellerId
            ));
        $rows = $this->selectWith($select)->toArray();
        $mainClassArr = array();
        foreach($rows as $row){
            $mainClassArr[$row["MainClassID"]]["ID"] = $row["MainClassID"];
            $mainClassArr[$row["MainClassID"]]["Name"] = $row["MainClassName"];
            $mainClassArr[$row["MainClassID"]][$row["ClassID"]] = $row;
        }
        return $mainClassArr;
    }


    public function addSellerClassRelation($goods){
        $scr = new SellerClassRelation();
        $scr->setSellerID($goods->getSellerID())
            ->setMainClassID($goods->getMainClassID())
            ->setClassID($goods->getClassID());
        if(!$exist = $this->isExist($scr)){
            $this->saveRelation($scr);
        }
    }


} 