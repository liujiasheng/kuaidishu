<?php

/**
 * Description of StickyNotesTable
 *
 * @author Arian Khosravi <arian@bigemployee.com>, <@ArianKhosravi>
 */
// module/Authenticate/src/Authenticate/Model/AdminTable.php

namespace Authenticate\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Select;
//key name as follow

class WorkerTable extends AbstractTableGateway {

    protected $table = 't_worker';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getWorkerByUsername($username)
    {
        $row = $this->select(array("Username"=>$username))->current();
        if(!$row){
            return false;
        }
        return $worker = new \Application\Entity\Worker(array(
            'ID'=>$row->ID,
            'Username'=>$row->Username,
            'Password'=>$row->Password,
            'Name'=>$row->Name,
            'CertNumber'=>$row->CertNumber,
            'Sex'=>$row->Sex,
            'SelfPhone'=>$row->SelfPhone,
            'Phone'=>$row->Phone,
            'LoginIP'=>$row->LoginIP,
            'LoginTime'=>$row->LoginTime,
            'State'=>$row->State,
        ));
    }

    /**
     * @param $id
     * @return \Application\Entity\Seller|bool
     */
    public function getWorkerById($id)
    {
        $row = $this->select(array("ID"=>$id))->current();
        if(!$row)
            return false;
        $entity = new \Application\Entity\Worker(array(
            'ID'=>$row->ID,
            'Username'=>$row->Username,
            'Password'=>$row->Password,
            'Name'=>$row->Name,
            'CertNumber'=>$row->CertNumber,
            'Sex'=>$row->Sex,
            'SelfPhone'=>$row->SelfPhone,
            'Phone'=>$row->Phone,
            'LoginTime'=>$row->LoginTime,
            'LoginIP'=>$row->LoginIP,
            'State'=>$row->State,
        ));
        return $entity;
    }
}