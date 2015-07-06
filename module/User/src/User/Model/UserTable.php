<?php

namespace User\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select;

class UserTable extends AbstractTableGateway {
	
	protected $table = "t_user";
	
	public function __construct(Adapter $adapter) {
		$this->adapter = $adapter;
	}
	
	public function fetchAll() {
		$resultSet = $this->select(function (Select $select) {
			$select->order('id desc');
		});
		$entities = $this->pushResultSet($resultSet);
		return $entities;
	}

    //page 3 count 10 means limit 20,10
    public function fetchSlice($page,$count,$where = null ){
        $select = new Select();
        $select->from($this->table)
            ->order("id desc")
            ->limit($count)
            ->offset(($page-1)*$count)
            ->where('state != -1');
        if($where!=null && count($where)) $select->where($where);
        $resultSet = $this->selectWith($select);
        $entities = $this->pushResultSet($resultSet);
        return $entities;
    }

    public function fetchCount($where = null){
        $select = new Select($this->table);
        $select->where('state != -1');
        if($where!=null && count($where)) $select->where($where);
        $count = $this->selectWith($select)->count();
        return $count;
    }

    public function getUser($id) {
        $row = $this->select(array("id"=>$id))->current();
        if(!$row)
            return false;
        $user = new \Application\Entity\User();
        $user->setId($row->ID)
            ->setUsername($row->Username)
            ->setPassword($row->Password)
            ->setNickname($row->Nickname)
            ->setEmail($row->Email)
            ->setPhone($row->Phone)
            ->setRegisterTime($row->RegisterTime)
            ->setLoginIP($row->LoginIP)
            ->setLoginTime($row->LoginTime)
            ->setState($row->State);
        return $user;
    }

    public function getUserByUsername($username) {
        $row = $this->select(array("username"=>$username))->current();
        if(!$row)
            return false;
        $user = new \Application\Entity\User();
        $user->setId($row->ID)
            ->setUsername($row->Username)
            ->setPassword($row->Password)
            ->setNickname($row->Nickname)
            ->setEmail($row->Email)
            ->setPhone($row->Phone)
            ->setRegisterTime($row->RegisterTime)
            ->setLoginIP($row->LoginIP)
            ->setLoginTime($row->LoginTime)
            ->setState($row->State);
        return $user;
    }

    /**
     * @param $user \Application\Entity\User
     * @return bool|int
     */
    public function saveUser($user){

        $id = $user->getId();
        if ( $id == 0 ){ //insert
            $encrypt = new \Authenticate\AssistantClass\PasswordEncrypt();
            $enPw = $encrypt->getPasswordMd5($user->getUsername(),$user->getPassword());
            $res = $this->insert(array(
                "username" => $user->getUsername(),
                "password" => $enPw,
                "nickname" => $user->getUsername(),
                "email"    => $user->getEmail(),
                "registerTime" => (new \DateTime())->format("Y-m-d H:i:s"),
                "state"    => 1,
            ));
            if(!$res) return false;
            return $this->getLastInsertValue();
        }elseif($id){ //update
            $setter = array(
                "nickname" => $user->getNickname(),
                "email" => $user->getEmail(),
                "phone" => $user->getPhone(),
                "state" => $user->getState(),
            );
            if($user->getPassword() != ""){
                $encrypt = new \Authenticate\AssistantClass\PasswordEncrypt();
                $enPw = $encrypt->getPasswordMd5($user->getUsername(), $user->getPassword());
                $setter["password"] = $enPw;
            }
            $res = $this->update($setter,array(
                "id" => $id
            ));
            if(!$res) return false;
            return $id;
        }else
            return false;
    }

    protected function pushResultSet($resultSet)
    {
        $entities = array();
        foreach ($resultSet as $row) {
            $entity = new \Application\Entity\User();
            $entity->setId($row->ID)
                ->setUsername($row->Username)
                ->setPassword($row->Password)
                ->setNickname($row->Nickname)
                ->setEmail($row->Email)
                ->setPhone($row->Phone)
                ->setRegisterTime($row->RegisterTime)
                ->setLoginIP($row->LoginIP)
                ->setLoginTime($row->LoginTime)
                ->setState($row->State);
            $entities[] = $entity;
        }
        return $entities;
    }


}