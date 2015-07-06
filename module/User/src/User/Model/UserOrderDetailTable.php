<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-7-31
 * Time: 下午4:46
 */

namespace User\Model;


use Application\Entity\OrderDetail;
use ArrayObject;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;

class UserOrderDetailTable extends AbstractTableGateway{

    protected $table = "t_order_detail";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function searchOrderDetail($detailIdList){

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

    public function getDetailById($id)
    {
        $row = $this->select(array('OrderID'=>$id))->current();
        if(!$row){
            return false;
        }
        $detail = new OrderDetail(
            array(
                'ID'=>$row->ID,
                'OrderID'=>$row->OrderID,
                'SellerID'=>$row->SellerID,
                'GoodsID'=>$row->GoodsID,
                'Name'=>$row->Name,
                'Price'=>$row->Price,
                'Unit'=>$row->Unit,
                'Count'=>$row->Count,
                'Total'=>$row->Total,
                'Image'=>$row->Image,
                'Comment'=>$row->Comment,
                'Barcode'=>$row->Barcode,
            )
            );
        return $detail;
    }

} 