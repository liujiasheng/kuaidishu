<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-10
 * Time: 下午4:58
 */

namespace Goods\Model;

use Application\Entity\GoodsMainClass;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;

class MainClassTable extends AbstractTableGateway{

    protected $table = "t_goods_mainclass";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }


    public function fetchAll(){
        $select = new Select($this->table);
        $resultSet = $this->selectWith($select);
        $entities = $this->pushResultSet($resultSet);
        return $entities;
    }

    public function fetchAllWithClass(){
        $select = new Select($this->table);
        $select->join(array("c" => "t_goods_class"),
            "t_goods_mainclass.id = c.mainclassid",
            array(
                "ClassID" => "id" ,
                "ClassName" => "name",
            ),
            Select::JOIN_LEFT)
            ->join(array("s" => "t_goods_superclass"),
            "t_goods_mainclass.superclassid = s.id",
            array(
                "SuperClassName" => "name"
            ),
            Select::JOIN_LEFT);
        $rowsArr = $this->selectWith($select)->toArray();
        $arr = array();
        foreach($rowsArr as $row){
            $arr[$row["SuperClassID"]]["Name"] = $row["SuperClassName"];
            $arr[$row["SuperClassID"]]["MainClasses"][$row["ID"]]["Name"] = $row["Name"];
            if($row["ClassID"]){
                $arr[$row["SuperClassID"]]["MainClasses"][$row["ID"]]["Classes"][$row["ClassID"]] = $row;
            }
            else{
                $arr[$row["SuperClassID"]]["MainClasses"][$row["ID"]]["Classes"] = array();
            }
        }
        return $arr;
    }

    //搜索商品页 展示 主类和子类
    public function fetchAllWithClassNoSuper(){
        $select = new Select($this->table);
        $select->join(array("c" => "t_goods_class"),
            "t_goods_mainclass.id = c.mainclassid",
            array(
                "ClassID" => "id" ,
                "ClassName" => "name",
            ),
            Select::JOIN_LEFT);

        $rowsArr = $this->selectWith($select)->toArray();
        $arr = array();
        foreach($rowsArr as $row){
            $arr[$row["ID"]]["ID"] = $row["ID"];
            $arr[$row["ID"]]["Name"] = $row["Name"];
            if($row["ClassID"]){
                $arr[$row["ID"]]["Classes"][$row["ClassID"]] = $row;
            }else{
                $arr[$row["ID"]]["Classes"] = array();
            }
        }
        return $arr;
    }

    //全部商品页 展示 超类和主类
    public function fetchAllSuperWithMainClass(){
        $select = new Select($this->table);
        $select->join(array("s" => "t_goods_superclass"),
                "t_goods_mainclass.superclassid = s.id",
                array(
                    "SuperClassID" => "id",
                    "SuperClassName" => "name"
                ),
                Select::JOIN_LEFT);
        $mcIdSelect = new Select('t_goods');
        $mcIdSelect->columns(array(new Expression('distinct mainClassId')));
        $sIdSelect = new Select('t_seller');
        $sIdSelect->columns(array('Id'))->where( array('state'=>'1'));
        $mcIdSelect->where->in('sellerId', $sIdSelect);
        $select->where->in('t_goods_mainclass.id', $mcIdSelect);
        $rowsArr = $this->selectWith($select)->toArray();
        $arr = array();
        foreach($rowsArr as $row){
            $arr[$row["SuperClassID"]]["ID"] = $row["SuperClassID"];
            $arr[$row["SuperClassID"]]["Name"] = $row["SuperClassName"];
            if($row["ID"]){
                $arr[$row["SuperClassID"]]["Classes"][$row["ID"]] = $row;
            }else{
                $arr[$row["SuperClassID"]]["Classes"] = array();
            }
        }
        return $arr;
    }

    public function getMainClass($id){
        $row = $this->select(array("id" => $id))->current();
        if(!$row) return false;
        $mainClass = new GoodsMainClass();
        $mainClass->setID($row->ID)
            ->setName($row->Name);
        return $mainClass;
    }

    public function getMainClassByName($superClassID, $name){
        return $this->select(array(
            "SuperClassID" => $superClassID,
            "Name" => $name
        ))->current();
    }

    public function getChildClass($id){
        $classTable = new ClassTable($this->adapter);
        return $classTable->fetchByMainClassID($id);
    }

    /**
     * @param $mainClass \Application\Entity\GoodsMainClass
     */
    public function saveMainClass($mainClass){
        $id = $mainClass->getID();
        if($id==0){ //insert
            $res = $this->insert(array(
                "superClassId" => $mainClass->getSuperClassID(),
                "name" => $mainClass->getName()
            ));
            if(!$res) return false;
            return $this->getLastInsertValue();
        }else{ //update
            $res = $this->update(array(
                "name" => $mainClass->getName(),
            ),array(
                "id" => $mainClass->getID(),
            ));
            if(!$res) return false;
            return $res;
        }
    }


    public function pushResultSet($resultSet){
        $entities = array();
        foreach ($resultSet as $row){
            $entity = new GoodsMainClass();
            $entity->setID($row->ID)
                ->setName($row->Name);
            $entities[] = $entity;
        }
        return $entities;
    }
}