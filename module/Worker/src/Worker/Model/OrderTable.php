<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-9-13
 * Time: 下午9:16
 */

namespace Worker\Model;


use Application\Entity\Order;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Stdlib\ArrayObject;

class OrderTable extends AbstractTableGateway{
    protected $table = "t_order";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getOrderListByOrderIdList($where)
    {
        $select = new Select($this->table);
        $select->where($where)->join(
            array("s"=>"t_seller"),
            "s.ID = SellerID",
            array("SellerName"=>"Name"),
            Select::JOIN_LEFT
        );
        $rs = $this->selectWith($select);
        $rs1 = array();
        /** @var $row ArrayObject */
        foreach($rs as $row){
           array_push($rs1,$row->getArrayCopy());
        }
        return $rs1;
    }

} 