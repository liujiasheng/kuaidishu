<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-10
 * Time: 下午4:58
 */

namespace Goods\Model;

use Application\Entity\GoodsClass;
use Application\Entity\GoodsMainClass;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;

class ClassTable extends AbstractTableGateway{

    protected $table = "t_goods_class";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function fetchByMainClassID($id){
        $select = new Select($this->table);
        $select->where(array(
            "mainClassID" => $id
        ));
        $resultSet = $this->selectWith($select);
        $entities = $this->pushResultSet($resultSet);
        return $entities;
    }

    public function getClass($id){
        if(!$row = $this->select(array("id" => $id))->current()){
            return false;
        }else{
            $class = new GoodsClass();
            $class->setID($row->ID)
                ->setMainClassID($row->MainClassID)
                ->setName($row->Name);
            return $class;
        }
    }

    public function getClassesByName($name){
        $rows = $this->select(array("name" => $name));
        if(!$rows){
            return null;
        }else{
            return $this->pushResultSet($rows);
        }
    }

    /**
     * @param $class \Application\Entity\GoodsClass
     */
    public function saveClass($class){
        $id = $class->getID();
        if($id==0){ //insert
            $res = $this->insert(array(
                "mainClassId" => $class->getMainClassID(),
                "name" => $class->getName(),
            ));
            if(!$res) return false;
            return $this->getLastInsertValue();
        }else{ //update
            $res = $this->update(array(
                "name" => $class->getName(),
            ),array(
                "id" => $class->getID(),
            ));
            if(!$res) return false;
            return $res;
        }
    }

    public function getClassByName($mainClassId, $name){
        $row = $this->select(array(
            "mainClassId" => $mainClassId,
            "name" => $name))->current();
        if(!$row) return false;
        $class = new GoodsClass();
        $class->setID($row->ID)
            ->setMainClassID($row->MainClassID)
            ->setName($row->Name);
        return $class;
    }

    public function pushResultSet($resultSet){
        $entities = array();
        foreach($resultSet as $row){
            $entity = new GoodsClass();
            $entity->setID($row->ID)
                ->setName($row->Name)
                ->setMainClassID($row->MainClassID);
            $entities[] = $entity;
        }
        return $entities;
    }


}