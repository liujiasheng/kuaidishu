<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-9-5
 * Time: 下午1:04
 */

namespace Worker\Model;


use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Stdlib\ArrayObject;

class WorkerOrderTable  extends AbstractTableGateway{

    protected $table = "t_worker_order";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getLastOrderListByWorkerId($workerId,$state, $lastLimit)
    {
        $select = new Select($this->table);
        $select->where(array('WorkerID'=>$workerId,"o.State"=>$state))->limit($lastLimit)
            ->join(array("o"=>"t_order"),
                "o.ID = OrderID",
                array(
                    'OrderID'=>'ID',
//                    'UserID'=>'UserID',
                    'OrderTime'=>'OrderTime',
                    'EndTime'=>'EndTime',
                    'Total'=>'Total',
                    'State'=>'State',
                    'Comment'=>'Comment',
                    'Remark'=>'Remark',
                    'Name'=>'Name',
                    'Phone'=>'Phone',
                    'Domain'=>'Domain',
                    'Domain2'=>'Domain2',
                    'Domain3'=>'Domain3',
                    'Address'=>'Address',
//                    'SellerID'=>'SellerID',
                    'Version'=>'Version',
                ),
                Select::JOIN_LEFT
            )->join(array("s"=>"t_seller"),
                "o.SellerID = s.ID",
                array('SellerName'=>'Name',),
                Select::JOIN_LEFT
            )
            ->order($this->table.".ID desc");
        $rs = $this->selectWith($select);
        if($rs->count()==0){
            return null;
        }
        $rs1 = array();
        /** @var $row ArrayObject */
        foreach($rs as $row){
            array_push($rs1,$row->getArrayCopy());
        }
        return $rs1;
    }

    public function getOrderListGreaterThanID($workerId,$id,$state,$limit)
    {
        $select = new Select($this->table);
        $where = new Where();
        $where->greaterThan($this->table.'.ID',$id)->AND->equalTo('WorkerID',$workerId)->equalTo("o.State",$state);
        $select->where($where)
            ->join(array("o"=>"t_order"),
                "o.ID = OrderID",
                array(
                    'OrderID'=>'ID',
//                    'UserID'=>'UserID',
                    'OrderTime'=>'OrderTime',
                    'EndTime'=>'EndTime',
                    'Total'=>'Total',
                    'State'=>'State',
                    'Comment'=>'Comment',
                    'Remark'=>'Remark',
                    'Name'=>'Name',
                    'Phone'=>'Phone',
                    'Domain'=>'Domain',
                    'Domain2'=>'Domain2',
                    'Domain3'=>'Domain3',
                    'Address'=>'Address',
//                    'SellerID'=>'SellerID',
                    'Version'=>'Version',
                ),
                Select::JOIN_LEFT
            )
            ->join(array("s"=>"t_seller"),
                "o.SellerID = s.ID",
                array('SellerName'=>'Name',),
                Select::JOIN_LEFT
            )
            ->order($this->table.".ID desc");
        $rs = $this->selectWith($select);
        if($rs->count()==0){
            return null;
        }
        $rs1 = array();
        /** @var $row ArrayObject */
        foreach($rs as $row){
            array_push($rs1,$row->getArrayCopy());
        }
        return $rs1;
    }

    public function getOrderListLessThanID($workerId, $id,$state,$limit)
    {
        $select = new Select($this->table);
        $where = new Where();
        $where->lessThan($this->table.'.ID',$id)->AND->equalTo('WorkerID',$workerId)->equalTo("o.State",$state);
        $select->where($where)
            ->join(array("o"=>"t_order"),
                "o.ID = OrderID",
                array(
                    'OrderID'=>'ID',
//                    'UserID'=>'UserID',
                    'OrderTime'=>'OrderTime',
                    'EndTime'=>'EndTime',
                    'Total'=>'Total',
                    'State'=>'State',
                    'Comment'=>'Comment',
                    'Remark'=>'Remark',
                    'Name'=>'Name',
                    'Phone'=>'Phone',
                    'Domain'=>'Domain',
                    'Domain2'=>'Domain2',
                    'Domain3'=>'Domain3',
                    'Address'=>'Address',
//                    'SellerID'=>'SellerID',
                    'Version'=>'Version',
                ),
                Select::JOIN_LEFT
            )->join(array("s"=>"t_seller"),
                "o.SellerID = s.ID",
                array('SellerName'=>'Name',),
                Select::JOIN_LEFT
            )
            ->order($this->table.".ID desc")
            ->limit($limit);
        $rs = $this->selectWith($select);
        if($rs->count()==0){
            return null;
        }
        $rs1 = array();
        /** @var $row ArrayObject */
        foreach($rs as $row){
            array_push($rs1,$row->getArrayCopy());
        }
        return $rs1;
    }

    public function getAllOrderListByID($workerId,$state)
    {
        $select = new Select($this->table);
        $where = new Where();
        $where->equalTo('WorkerID',$workerId)->equalTo("o.State",$state);
        $select->where($where)
            ->join(array("o"=>"t_order"),
                "o.ID = OrderID",
                array(
                    'OrderID'=>'ID',
//                    'UserID'=>'UserID',
                    'OrderTime'=>'OrderTime',
                    'EndTime'=>'EndTime',
                    'Total'=>'Total',
                    'State'=>'State',
                    'Comment'=>'Comment',
                    'Remark'=>'Remark',
                    'Name'=>'Name',
                    'Phone'=>'Phone',
                    'Domain'=>'Domain',
                    'Domain2'=>'Domain2',
                    'Domain3'=>'Domain3',
                    'Address'=>'Address',
//                    'SellerID'=>'SellerID',
                    'Version'=>'Version',
                ),
                Select::JOIN_LEFT
            )->join(array("s"=>"t_seller"),
                "o.SellerID = s.ID",
                array('SellerName'=>'Name',),
                Select::JOIN_LEFT
            )
            ->order($this->table.".ID desc");
        $rs = $this->selectWith($select);
        if($rs->count()==0){
            return null;
        }
        $rs1 = array();
        /** @var $row ArrayObject */
        foreach($rs as $row){
            array_push($rs1,$row->getArrayCopy());
        }
        return $rs1;
    }
} 