<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-20
 * Time: 上午11:56
 */

namespace Seller\Model;


use ArrayObject;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;

class SellerOrderDetailTable extends AbstractTableGateway{

    protected $table = "t_order_detail";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getDetailListByOrderID($detailIdList)
    {
        if(empty($detailIdList)){
            return array();
        }
        $select = new Select($this->table);
        $select->where->in('OrderId',$detailIdList);
        $rs = $this->selectWith($select);
        $rs1 = array();

        /** @var $row ArrayObject */
        foreach($rs as $row){
            if(!isset($rs1[$row->OrderID])){
                $rs1[$row->OrderID] = array();
            }
//            array_push($rs1[$row->OrderID],$row->getArrayCopy());
            $rs1[$row->OrderID][$row->ID] = $row->getArrayCopy();
        }
        return $rs1;
    }
} 