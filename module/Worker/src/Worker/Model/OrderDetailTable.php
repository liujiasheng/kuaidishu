<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-9-18
 * Time: 下午6:42
 */

namespace Worker\Model;


use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Stdlib\ArrayObject;

class OrderDetailTable extends AbstractTableGateway{
    protected $table = "t_order_detail";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getDetailsByOrderId($orderId)
    {
        $select = new Select($this->table);
        $select->where(array("OrderID"=>$orderId))->order($this->table.".ID desc")
            ->join(array("s"=>"t_seller"),
                $this->table.".SellerID = s.ID",
                array('SellerName'=>'Name'),
                Select::JOIN_LEFT
            );
         $rs = $this->selectWith($select);;
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