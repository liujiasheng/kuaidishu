<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-9-8
 * Time: 下午10:01
 */

namespace Seller\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class SellerWorkTimeTable extends AbstractTableGateway{
    protected $table = "t_seller_worktime";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getSellerWorkTime($id){
        $rsl = $this->select(array(
            "SellerID" => $id,
        ))->current();
        if(!$rsl) return false;
        else{
            return array(
                "SellerID" => $rsl->SellerID,
                "Day" => $rsl->Day,
                "OpenTime" => $rsl->OpenTime,
                "CloseTime" => $rsl->CloseTime,
            );
        }
    }

} 