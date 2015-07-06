<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-14
 * Time: 下午4:19
 */

namespace Home\Model;


use Application\Entity\HomeSeller;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;

class HomeSellerTable extends AbstractTableGateway {

    protected $table = "t_home_seller";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function fetchAll(){
        $select = $this->getRawSelect();
        $select->order($this->table.".order asc");
        $resultSet = $this->selectWith($select);
        $entities = $this->pushResultSet($resultSet);
        return $entities;
    }

    protected function pushResultSet($resultSet){
        $entities = array();
        foreach ($resultSet as $row){
            $hs = new HomeSeller();
            $hs->setID($row->ID)
                ->setSellerID($row->SellerID)
                ->setOrder($row->Order)
                ->setSellerName($row->SellerName)
                ->setLogo($row->Logo);
            $entities[] = $hs;
        }
        return $entities;
    }

    protected function getRawSelect(){
        $select = new Select($this->table);
        $select->join(array("s" => "t_seller"),
            $this->table.".sellerId = s.id",
            array(
                "SellerName" => "name",
                "Logo" => "logo"),
            Select::JOIN_LEFT);
        return $select;
    }

} 