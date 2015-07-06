<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-8
 * Time: 上午10:07
 */

namespace Seller\Model;

use Application\Entity\Seller;
use Authenticate\AssistantClass\PasswordEncrypt;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select;

class SellerTable extends AbstractTableGateway{

    protected $table = "t_seller";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    /**
     * @param $page int
     * @param $count int
     * @param null $where array
     * @return array
     */
    public function fetchSlice($page, $count,$where = null){
        $select = new Select($this->table);
        $select->order('id desc')
            ->limit($count)
            ->offset(($page-1)*$count)
            ->where('state != -1');
        if($where!=null && count($where)) $select->where($where);
        $resultSet = $this->selectWith($select);
        $entities = $this->pushResultSet($resultSet);
        return $entities;
    }

    public function fetchAll(){
        $select = new Select($this->table);
        $select->where("state != -1");
        $resultSet = $this->selectWith($select);
        return $this->pushResultSet($resultSet);
    }

    public function fetchAllByRecommendOrder(){
        $select = new Select($this->table);
        $select->join(array("r" => "t_home_seller"),
            "r.sellerid = t_seller.id",
            array("order" => "order"),
            Select::JOIN_LEFT);
        $select->order(array(
            new \Zend\Db\Sql\Expression("r.order is null, r.order asc"),
        ));
        $select->where('state != -1');
        $resultSet = $this->selectWith($select);
        return $this->pushResultSet($resultSet);
    }

    /**
     * @param null $where
     * @return int
     */
    public function fetchCount($where = null){
        $select = new Select($this->table);
        $select->where("state != -1");
        if($where!=null && count($where)) $select->where($where);
        $count = $this->selectWith($select)->count();
        return $count;
    }

    /**
     * @param $Seller \Application\Entity\Seller
     * @return bool|int
     */
    public function saveSeller($Seller){
        $id = $Seller->getID();
        if($id == 0){ //insert
            $encrypt = new \Authenticate\AssistantClass\PasswordEncrypt();
            $enPW = $encrypt->getPasswordMd5($Seller->getUsername(),$Seller->getPassword());
            $res = $this->insert(array(
                "Username" => $Seller->getUsername(),
                "Password" => $enPW,
                "Name" => $Seller->getName(),
                "Comment" => $Seller->getComment(),
                "Address" => $Seller->getAddress(),
                "Email" => $Seller->getEmail(),
                "Phone" => $Seller->getPhone(),
                "ContactPhone" => $Seller->getContactPhone(),
                "Logo" => $Seller->getLogo(),
                "State" => 1
            ));
            if(!$res) return false;
            return $this->getLastInsertValue();
        }else if($id){ //update
            $setter = array(
                "Name" => $Seller->getName(),
                "Comment" => $Seller->getComment(),
                "Address" => $Seller->getAddress(),
                "Email" => $Seller->getEmail(),
                "Phone" => $Seller->getPhone(),
                "ContactPhone" => $Seller->getContactPhone(),
                "State" =>$Seller->getState(),
            );
            if($Seller->getPassword()!=""){
                $encrypt = new PasswordEncrypt();
                $enPW = $encrypt->getPasswordMd5($Seller->getUsername(),$Seller->getPassword());
                $setter["Password"] = $enPW;
            }
            if($Seller->getLogo()!=null) $setter["Logo"] = $Seller->getLogo();
            $res = $this->update($setter, array("id" => $Seller->getID()));
            if(!$res) return false;
            return $id;
        }else
            return false;
    }

    /**
     * @param $id
     * @return \Application\Entity\Seller|bool
     */
    public function getSeller($id){
        $row = $this->select(array("id" => $id))->current();
        if(!$row) return false;
        $seller = $this->fillSeller($row);
        return $seller;
    }

    public function getSellerByName($name){
        $row = $this->select(array("name" => $name))->current();
        if(!$row) return false;
        $seller = $this->fillSeller($row);
        return $seller;
    }

    protected function fillSeller($row){
        $seller = new Seller();
        $seller->setID($row->ID)
            ->setUsername($row->Username)
            ->setPassword($row->Password)
            ->setName($row->Name)
            ->setEmail($row->Email)
            ->setAddress($row->Address)
            ->setPhone($row->Phone)
            ->setContactPhone($row->ContactPhone)
            ->setLoginIP($row->LoginIP)
            ->setLoginTime($row->LoginTime)
            ->setLogo($row->Logo)
            ->setComment($row->Comment)
            ->setState($row->State);
        return $seller;
    }

    /**
     * @param $resultSet
     * @return array
     */
    protected function pushResultSet($resultSet){
        $entities = array();
        foreach ($resultSet as $row){
            $entity = $this->fillSeller($row);
            $entities[] = $entity;
        }
        return $entities;
    }

} 