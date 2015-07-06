<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-27
 * Time: 下午3:41
 */

namespace AdminMgr\Model;


use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Stdlib\ArrayObject;

class WorkerOrderTable extends AbstractTableGateway{

    protected $table = "t_worker_order";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function searchOrderList($where, $start, $limit)
    {
        $select = new Select($this->table);
        $select->join(array(
                "t" => "t_order"
            ), "t.ID = " . $this->table . ".OrderID",
            Select::SQL_STAR,
            Select::JOIN_LEFT)
        ///get worker name
        ->join(array(
                "s"=>"t_worker"
            ),"s.ID = ".$this->table.".WorkerID",
                array("WorkerName"=>"Name","WorkerAccount"=>"Username"),
                Select::JOIN_LEFT)
        //get seller name
            ->join(array(
                "v"=>"t_seller"
            ),"v.ID = t.SellerID",
            array("SellerName"=>"Name"),
            Select::JOIN_LEFT)
        ;
        $select->where($where)->offset($start)->limit($limit)->order($this->table.".ID desc");
        $rs = $this->selectWith($select);
        if(!$rs){
            return false;
        }
        $rs1 = array();
        /** @var $row ArrayObject */
        foreach ($rs as $row) {
            array_push($rs1,$row->getArrayCopy());
        }

        return $rs1;
    }

    public function searchOrderListCount($where)
    {
        $select = new Select($this->table);
        $select->join(array(
                "t" => "t_order"
            ), "t.ID = " . $this->table . ".OrderID",
            Select::SQL_STAR,
            Select::JOIN_LEFT)
            ///get worker name
            ->join(array(
                    "s"=>"t_worker"
                ),"s.ID = ".$this->table.".WorkerID",
                array("WorkerName"=>"Name","WorkerAccount"=>"Username"),
                Select::JOIN_LEFT)
            //get seller name
            ->join(array(
                    "v"=>"t_seller"
                ),"v.ID = t.SellerID",
                array("SellerName"=>"Name"),
                Select::JOIN_LEFT);
        $select->where($where);
        $rs = $this->selectWith($select)->count();
        return $rs;
    }

} 