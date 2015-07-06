<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-7-16
 * Time: 下午4:45
 */

namespace User\Model;


use Application\Entity\DeliveryAddress;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class DeliveryAddressTable extends AbstractTableGateway
{

    protected $table = "t_delivery_address";

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function create(DeliveryAddress $v)
    {
        $res = $this->insert(array(
            'UserID' => $v->getUserID(),
            'Address' => $v->getAddress(),
            'Phone' => $v->getPhone(),
            'Name' => $v->getName(),
            'Domain' => $v->getDomain(),
            'Domain2'=>$v->getDomain2(),
            'Domain3'=>$v->getDomain3(),
        ));
        if (!$res) return false;
        return $this->getLastInsertValue();

    }

    public function edit(DeliveryAddress $v){
        $res = $this->update(array(
            'Address' => $v->getAddress(),
            'Phone' => $v->getPhone(),
            'Name' => $v->getName(),
            'Domain' => $v->getDomain(),
            'Domain2'=>$v->getDomain2(),
            'Domain3'=>$v->getDomain3(),
        ),array(
            "id" => $v->getID()
        ));
        if(!$res) return false;
        return true;
    }

    public function getDeliveryAddressById($id, $userId)
    {
        $rs = $this->select(array('id'=>$id,'UserId'=>$userId))->toArray();
        if(count($rs) < 1){
           return false;
        }
        return $rs;
    }

    public function getAddressByUserId($userId)
    {
        $rows = $this->select(array('UserId'=>$userId));
        if(!$rows)
            return false;
        $list = array();
        foreach($rows as $row){
            $arr = array(
                'ID'=>$row->ID,
                'Address'=>$row->Address,
                'Phone'=>$row->Phone,
                'Name'=>$row->Name,
                'Domain'=>$row->Domain,
                'Domain2'=>$row->Domain2,
                'Domain3'=>$row->Domain3,
            );
            array_push($list,$arr);
        }
        if(count($list)==0){
            return false;
        }
        return $list;
    }


}