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
//ID
//Username
//Password
//Nickname
//Email
//LoginIP
//LoginTime
//State
class AdminTable extends AbstractTableGateway {

    protected $table = 't_admin';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getAdmin($id){
        $row = $this->select(array(
            'ID' => (int) $id
        ))->current();

        if(!$row){
            return false;
        }

        return $this->packageDate($row);
    }

    public function getAdminByName($arg_username){
        $row = $this->select(array(
            'Username' => (string) $arg_username,
        ))->current();
        if(!$row){
            return false;
        }

        return $this->packageDate($row);
    }

    public function getAdminByUserNameAndPwd($arg_username,$arg_password){
        $row = $this->select(array(
            'Username' => (string) $arg_username,
            'Password'=>(string)$arg_password ,
        ))->current();

        if(!$row){
            return false;
        }
        return $this->packageDate($row);
    }

    /**
     * @param $row array|\ArrayObject|null
     * @return \Application\Entity\Admin|bool
     */
    public function packageDate($row){
        if(!$row){
            return false;
        }
        $user = new \Application\Entity\Admin(array(
            'ID'=>$row->ID,
            'Username'=>$row->Username,
            'Password'=>$row->Password,
            'Nickname'=>$row->Nickname,
            'Email'=>$row->Email,
            'LoginIP'=>$row->LoginIP,
            'LoginTime'=>$row->LoginTime,
            'State'=>$row->State,
        ));

        return $user;
    }


}