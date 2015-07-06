<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-11
 * Time: 下午10:05
 */

namespace Goods\Model;


use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;

class SuperClassTable extends AbstractTableGateway{

    protected $table = "t_goods_superclass";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function fillEmptySuperClass($superClassArr){
        $rowsArr = $this->select(null)->toArray();
        foreach($rowsArr as $row){
            if(!in_array($row["ID"], array_keys($superClassArr))){
                $superClassArr[$row["ID"]] = array(
                    "Name" => $row["Name"],
                    "MainClasses" => array(),
                );
            }
        }
        return $superClassArr;
    }

    public function fetchAllWithClass(){

        return array();
    }

    public function getSuperClassByID($superClassId){
        $rows = $this->select(array(
            "id" => $superClassId,
        ));
        if($rows->count() < 1){
            return null;
        }else{
            return $rows->current();
        }
    }

} 