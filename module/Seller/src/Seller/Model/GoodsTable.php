<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-19
 * Time: 上午11:24
 */

namespace Seller\Model;


use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;

class GoodsTable extends AbstractTableGateway{

    protected $table = "t_goods";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getGoodsBySellerID($sellerID,$begin,$limit)
    {
        $se = new Select($this->table);
        $se->offset($begin)->limit($limit)->where(array('SellerID'=>$sellerID))->order('ID desc');
        $rs = $this->selectWith($se);
        $rsArr = array();
        foreach($rs as $row){
            array_push($rsArr,array(
                'ID'=>$row->ID,
                'MainClassID'=>$row->MainClassID,
                'ClassID'=>$row->ClassID,
                'SellerID'=>$row->SellerID,
                'Name'=>$row->Name,
                'Price'=>$row->Price,
                'Unit'=>$row->Unit,
                'Image'=>$row->Image,
                'Comment'=>$row->Comment,
                'Barcode'=>$row->Barcode,
                'State'=>$row->State,
                'Remain'=>$row->Remain,
            ));
        }
        if(!count($rsArr)){
            return false;
        }
        return $rsArr;
    }
} 