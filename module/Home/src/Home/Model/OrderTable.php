<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-21
 * Time: 下午8:53
 */

namespace Home\Model;


use Application\Entity\Order;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;

class OrderTable extends AbstractTableGateway{

    protected $table = "t_order";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function fetchSlice($page, $count, $where = null){
        $select = $this->getRawSelect();
        $select->offset(($page-1)*$count)
            ->limit($count)
            ->order('t_order.id desc');
        if($where!=null) $select->where($where);
        $resultSet = $this->selectWith($select);
        $entities = $this->pushResultSet($resultSet);
        return $entities;
    }

    public function fetchCount($where = null){
        $select = $this->getRawSelect();
        if($where!=null && count($where)) $select->where($where);
        $count = $this->selectWith($select)->count();
        return $count;
    }

    /**
     * @param $order \Application\Entity\Order
     */
    public function saveOrder($order){
        if($order->getID() == 0){ //insert
            $res = $this->insert(array(
                "UserID" => $order->getUserID(),
                "OrderTime" => $order->getOrderTime(),
                "EndTime" => $order->getEndTime(),
                "Total" => $order->getTotal(),
                "State" => $order->getState(),
                "Comment" => $order->getComment(),
                "Remark" => $order->getRemark(),
                "Name" => $order->getName(),
                "Phone" => $order->getPhone(),
                "Domain" => $order->getDomain(),
                "Domain2" => $order->getDomain2(),
                "Domain3" => $order->getDomain3(),
                "Address" => $order->getAddress(),
                "SellerID" => $order->getSellerID(),
            ));
            if(!$res) return false;
            return $this->getLastInsertValue();
        }else{ //update
            $res = $this->update(array(
                "UserID" => $order->getUserID(),
                "OrderTime" => $order->getOrderTime(),
                "EndTime" => $order->getEndTime(),
                "Total" => $order->getTotal(),
                "State" => $order->getState(),
                "Comment" => $order->getComment(),
                "Remark" => $order->getRemark(),
                "Name" => $order->getName(),
                "Phone" => $order->getPhone(),
                "Domain" => $order->getDomain(),
                "Domain2" => $order->getDomain2(),
                "Domain3" => $order->getDomain3(),
                "Address" => $order->getAddress(),
            ),array("ID" => $order->getID()));
            if(!$res) return false;
            else return $res;
        }
    }

    /**
     * @param $where array
     * @return Order|bool
     */
    public function getOrder($where){
        $row = $this->select($where)->current();
        if(!$row) return false;
        $order = $this->fillOrder($row);
        return $order;
    }

    public function getOrders($where){
        $rows = $this->select($where);
        if(!$rows) return false;
        $orders = array();
        foreach($rows as $row){
            $orders[] = $this->fillOrder($row);
        }
        return $orders;
    }

    protected  function fillOrder($row){
        $order = new Order();
        $order->setOptions(array(
            "ID" => $row->ID,
            "UserID" => $row->UserID,
            "OrderTime" => $row->OrderTime,
            "EndTime" => $row->EndTime,
            "Total" => $row->Total,
            "State" => $row->State,
            "Remark" => $row->Remark,
            "Comment" => $row->Comment,
            "Name" => $row->Name,
            "Phone" => $row->Phone,
            "Domain" => $row->Domain,
            "Domain2" => $row->Domain2,
            "Domain3" => $row->Domain3,
            "Address" => $row->Address,
            "SellerID" => $row->SellerID,
        ));
        return $order;
    }

    public function pushResultSet($resultSet){
        $entities = array();
        foreach($resultSet as $row){
            $entity = $this->fillOrder($row);
            $entity->setUserName($row->UserName);
            $entities[$row->ID] = $entity;
        }
        return $entities;
    }

    public function entitiesToArray($entities){
        $arr = array();
        foreach($entities as $key => $entity){
            $arr[] = array(
                "ID" => $entity->getID(),
                "UserID" => $entity->getUserID(),
                "OrderTime" => $entity->getOrderTime(),
                "EndTime" => $entity->getEndTime(),
                "Total" => $entity->getTotal(),
                "State" => $entity->getState(),
                "Remark" => $entity->getRemark(),
                "Comment" => $entity->getComment(),
                "Name" => $entity->getName(),
                "Phone" => $entity->getPhone(),
                "Domain" => $entity->getDomain(),
                "Domain2" => $entity->getDomain2(),
                "Domain3" => $entity->getDomain3(),
                "Address" => $entity->getAddress(),
                "UserName" => $entity->getUserName(),
            );
        }
        return $arr;
    }

    protected function getRawSelect(){
        $select = new Select($this->table);
        $select->join(array("u" => "t_user"),
            "t_order.userId = u.id",
            array("UserName" => "username"),
            Select::JOIN_LEFT);
        return $select;
    }

} 