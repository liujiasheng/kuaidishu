<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-22
 * Time: 下午3:26
 */

namespace Home\Model;


use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;

class OrderDetailTable extends AbstractTableGateway{

    protected $table = "t_order_detail";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    /**
     * @param $orderDetail \Application\Entity\OrderDetail
     * @return bool|int
     */
    public function saveOrderDetail($orderDetail){
        if($orderDetail->getID()==0){
            $res = $this->insert(array(
                "OrderID" => $orderDetail->getOrderID(),
                "SellerID" => $orderDetail->getSellerID(),
                "GoodsID" => $orderDetail->getGoodsID(),
                "Name" => $orderDetail->getName(),
                "Price" => $orderDetail->getPrice(),
                "Unit" => $orderDetail->getUnit(),
                "Count" => $orderDetail->getCount(),
                "Total" => $orderDetail->getTotal(),
                "Image" => $orderDetail->getImage(),
                "Comment" => $orderDetail->getComment(),
                "Barcode" => $orderDetail->getBarcode(),
            ));
            if(!$res) return false;
            else return $this->getLastInsertValue();
        }else{
            return false;
        }
    }

    public function getOrderDetailByOrderIDs($idArr){
        if(!$idArr || !is_array($idArr) || count($idArr)<1){
            return array();
        }
        $select = new Select($this->table);
        $select->join(array( "s" => "t_seller"),
            "s.id = t_order_detail.sellerid",
            array("SellerName" => "name"),
            Select::JOIN_LEFT);
        $where = new Where();
        $where->in("OrderID", $idArr);
        $select->where($where);
        $rows = $this->selectWith($select);
        $orderDetails = array();
        foreach($rows as $row){
            $orderDetails[$row->OrderID][$row->SellerID][] = array(
                "SellerName" => $row->SellerName,
                "Name" => $row->Name,
                "Price" => $row->Price,
                "Unit" => $row->Unit,
                "Count" => $row->Count,
                "Total" => $row->Total,
            );
        }
        return $orderDetails;
    }

    public function getOrderDetailWithOrderInfo($sellerId, $startTime, $endTime){
        $select = new Select($this->table);
        $select->join(array("o" => "t_order"),
            "o.id = t_order_detail.orderid",
            array(
                "OrderId" => "ID",
                "OrderTime" => "OrderTime",
                "State" => "State",
                "AName" => "Name",
                "UserID" => "UserID",
                "Domain" => "Domain",
                "Domain2" => "Domain2",
                "Domain3" => "Domain3",
                "Address" => "Address",
//                "Remark" => "Remark",
            ),
            Select::JOIN_LEFT)

            ->join(array('g' => 't_goods'),
            "g.id = t_order_detail.goodsId",
            array(),
            Select::JOIN_LEFT)

            ->join(array('mc' => 't_goods_mainclass'),
            "mc.id = g.mainClassId",
            array(
                "MainClass" => "Name"
            ),
            Select::JOIN_LEFT)

            ->join(array('c' => 't_goods_class'),
            "c.id = g.classId",
            array(
                "Class" => "Name"
            ),
            Select::JOIN_LEFT)

            ->join(array('s' => 't_seller'),
            "s.id = t_order_detail.sellerId",
            array(
                "SellerName" => "Name",
            ),
            Select::JOIN_LEFT);

        $where = new Where();
        $where->equalTo('o.State', "9", Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        if($sellerId != '0') $where->equalTo('o.SellerID', $sellerId, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $where->greaterThan('o.OrderTime', $startTime, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $where->lessThan('o.OrderTime', $endTime, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $select->where($where);
        $rows = $this->selectWith($select);
        $orderDetails = array();
        foreach($rows as $row){
            $orderDetails[] = array(
                "OrderID" => $row->OrderID,
                "UserID" => $row->UserID,
                "OrderTime" => $row->OrderTime,
                "SellerName" => $row->SellerName,
                "Name" => $row->Name,
                "MainClass" => $row->MainClass,
                "Class" => $row->Class,
                "Price" => $row->Price,
                "Unit" => $row->Unit,
                "Count" => $row->Count,
                "Total" => $row->Total,
                "AName" => $row->AName,
                "Domain" => $row->Domain,
                "Domain2" => $row->Domain2,
                "Domain3" => $row->Domain3,
                "Address" => $row->Address,
//                "Remark" => $row->Remark,
            );
        }
        return $orderDetails;
    }

}





