<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-7-5
 * Time: 下午8:30
 * To change this template use File | Settings | File Templates.
 */

namespace Worker\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select;

class WorkerTable extends AbstractTableGateway
{
    protected $table = "t_worker";

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

    /**
     * @param null $where
     * @return int
     */
    public function fetchCount($where = null){
        $select = new Select($this->table);
        $select->where(" state != -1");
        if($where!=null && count($where)) $select->where($where);
        $count = $this->selectWith($select)->count();
        return $count;
    }

    /**
     * @param $id
     * @return \Application\Entity\Worker|bool
     */
    public function getWorker($id){
        $row = $this->select(array("id" => $id))->current();
        if(!$row) return false;
        $worker = new \Application\Entity\Worker();
        $worker->setID($row->ID)
            ->setUsername($row->Username)
            ->setPassword($row->Password)
            ->setName($row->Name)
            ->setCertNumber($row->CertNumber)
            ->setSex($row->Sex)
            ->setSelfPhone($row->SelfPhone)
            ->setPhone($row->Phone)
            ->setLoginIP($row->LoginIP)
            ->setLoginTime($row->LoginTime)
            ->setState($row->State);
        return $worker;
    }


    /**
     * @param $worker \Application\Entity\Worker
     * @return bool|int
     */
    public function saveWorker($worker){
        $id = $worker->getID();
        if($id == 0){ //insert
            $encrypt = new \Authenticate\AssistantClass\PasswordEncrypt();
            $enPW = $encrypt->getPasswordMd5($worker->getUsername(),$worker->getPassword());
            $res = $this->insert(array(
                "Username" => $worker->getUsername(),
                "Password" => $enPW,
                "Name" => $worker->getName(),
                "Sex" => $worker->getSex(),
                "CertNumber" => $worker->getCertNumber(),
                "Phone" => $worker->getPhone(),
                "SelfPhone" => $worker->getSelfPhone(),
                "State" => 1
            ));
            if(!$res) return false;
            return $this->getLastInsertValue();
        }else if($id){ //update
            $setter = array(
                "name" => $worker->getName(),
                "sex" => $worker->getSex(),
                "certNumber" => $worker->getCertNumber(),
                "phone" => $worker->getPhone(),
                "selfPhone" => $worker->getSelfPhone(),
                "state" => $worker->getState()
            );
            if($worker->getPassword()!=""){
                $encrypt = new \Authenticate\AssistantClass\PasswordEncrypt();
                $enPw = $encrypt->getPasswordMd5($worker->getUsername(), $worker->getPassword());
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

    /**
     * @param $resultSet
     * @return array
     */
    protected function pushResultSet($resultSet){
        $entities = array();
        foreach ($resultSet as $row){
            $entity = new \Application\Entity\Worker();
            $entity->setID($row->ID)
                ->setUsername($row->Username)
                ->setPassword($row->Password)
                ->setName($row->Name)
                ->setCertNumber($row->CertNumber)
                ->setSex($row->Sex)
                ->setSelfPhone($row->SelfPhone)
                ->setPhone($row->Phone)
                ->setLoginIP($row->LoginIP)
                ->setLoginTime($row->LoginTime)
                ->setState($row->State);
            $entities[] = $entity;
        }
        return $entities;
    }

}
