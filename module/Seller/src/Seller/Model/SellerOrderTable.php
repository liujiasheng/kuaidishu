<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-20
 * Time: 上午10:58
 */

namespace Seller\Model;


use ArrayObject;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;

class SellerOrderTable extends AbstractTableGateway{

    protected $table = "t_order";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    /**
     * @param $where Where
     * @param $start
     * @param $limit
     * @return array|bool
     */
    public function getOrderIDList($where,$start,$limit)
    {
        $select = new Select($this->table);
        $select->offset($start)->limit($limit)->where($where)->order("id desc");
        $rs = $this->selectWith($select);
        if($rs->count()==0){
            return false;
        }
        $rs1 = array();
        $rs2 = array();
        /** @var $row ArrayObject */
        foreach($rs as $row){
            array_push($rs1,$row->ID);
            $rs2[$row->ID] = $row->getArrayCopy();
        }
        return array('orderIdList'=>$rs1,
        'orderList'=>$rs2);

    }

    public function getOrderCount($where, $start, $limit)
    {
        $select = new Select($this->table);
        $select->where($where);
        $rs = $this->selectWith($select)->count();
        return $rs;
    }


}
