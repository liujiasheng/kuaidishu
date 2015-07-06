<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-7-23
 * Time: 下午2:29
 */

namespace User\Model;


use Application\Entity\Order;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;

class UserOrderTable extends AbstractTableGateway {

    protected $table = "t_order";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getOrderListByUserIdAndPage($userId, $begin, $limit)
    {
        $se = new Select($this->table);
        $se->offset($begin)->limit($limit)->where(array('UserId'=>$userId))->order('id desc');
        $rs = $this->selectWith($se);
        $rsArr = array();
        foreach($rs as $row){
            $date = date_create($row->OrderTime);
            $date->add(new \DateInterval('PT45M'));
            array_push($rsArr,array(
                'ID'=>$row->ID,
                'UserID'=>$row->UserID,
                'OrderTime'=>$row->OrderTime,
                'ExpectTime'=>$date->format('Y-m-d H:i:s'),
                'EndTime'=>$row->EndTime,
                'Total'=>$row->Total,
                'State'=>$row->State,
                'Comment'=>$row->Comment,
                'Remark'=>$row->Remark,
                'Name'=>$row->Name,
                'Phone'=>$row->Phone,
                'Domain'=>$row->Domain,
                'Domain2'=>$row->Domain2,
                'Domain3'=>$row->Domain3,
                'Address'=>$row->Address,
            ));
        }
        if(!count($rsArr)){
            return false;
        }
        return $rsArr;
    }

    public function getOrderListCountByUserIdAndPage($userId, $begin, $limit)
    {
        $se = new Select($this->table);
        $se->where(array('UserId'=>$userId));
        $rs = $this->selectWith($se)->count();
        return $rs;
    }

    public function searchOrderList($userId, $where, $begin, $limit)
    {

        $se = new Select($this->table);
        $se->where($where)->where(array('UserId'=>$userId));
        $rowCount = $this->selectWith($se)->count();
        $se->order('id desc')->offset($begin)->limit($limit);
        $rs = $this->selectWith($se);
        $rsArr = array();
        foreach($rs as $row){
            $date = date_create($row->OrderTime);
            $date->add(new \DateInterval('PT45M'));
            array_push($rsArr,array(
                'ID'=>$row->ID,
                'UserID'=>$row->UserID,
                'OrderTime'=>$row->OrderTime,
                'ExpectTime'=>$date->format('Y-m-d H:i:s'),
                'EndTime'=>$row->EndTime,
                'Total'=>$row->Total,
                'State'=>$row->State,
                'Comment'=>$row->Comment,
                'Remark'=>$row->Remark,
                'Name'=>$row->Name,
                'Phone'=>$row->Phone,
                'Domain'=>$row->Domain,
                'Domain2'=>$row->Domain2,
                'Domain3'=>$row->Domain3,
                'Address'=>$row->Address,
            ));
        }
        return array(
            'rowCount'=>$rowCount,
            'data'=>$rsArr
        );
    }

    public function getOrderByID($id){
        $row = $this->select(array("ID"=>$id))->current();
        if(!$row){
            return false;
        }

        return new Order(array(
            'ID'=>$row->ID,
            'UserID'=>$row->UserID,
            'OrderTime'=>$row->OrderTime,
            'EndTime'=>$row->EndTime,
            'Total'=>$row->Total,
            'State'=>$row->State,
            'Comment'=>$row->Comment,
            'Remark'=>$row->Remark,
            'Name'=>$row->Name,
            'Phone'=>$row->Phone,
            'Domain'=>$row->Domain,
            'Domain2'=>$row->Domain2,
            'Domain3'=>$row->Domain3,
            'Address'=>$row->Address,
        ));


    }
} 