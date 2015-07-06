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

class UserTable extends AbstractTableGateway {

    protected $table = 't_user';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getUserByUsername($username){
        $row = $this->select(array("Username"=>$username))->current();
        if(!$row)
            return false;
        $user = new \Application\Entity\User(array(
            'ID'=>$row->ID,
            'Username'=>$row->Username,
            'Password'=>$row->Password,
            'Nickname'=>$row->Nickname,
            'Email'=>$row->Email,
            'Phone'=>$row->Phone,
            'RegisterTime'=>$row->RegisterTime,
            'LoginIP'=>$row->LoginIP,
            'LoginTime'=>$row->LoginTime,
            'State'=>$row->State,
        ));
        return $user;
    }

    public function saveUser($regUsername, $regPwd, $regEmail)
    {
        $encrypt = new \Authenticate\AssistantClass\PasswordEncrypt();
        $enPw = $encrypt->getPasswordMd5($regUsername,$regPwd);
        $res = $this->insert(array(
        'Username' => $regUsername,
        'Password' => $enPw,
        'Nickname' => $regUsername,
        'Email' => $regEmail,
        "registerTime" => (new \DateTime())->format("Y-m-d H:i:s"),
        'State' => 1,
    ));
        if(!$res) return false;
        return $this->getLastInsertValue();
    }

    /**
     * @param $id
     * @return \Application\Entity\User|bool
     */
    public function getUserById($id)
    {
        $row = $this->select(array("ID"=>$id))->current();
        if(!$row)
            return false;
        $user = new \Application\Entity\User(array(
            'ID'=>$row->ID,
            'Username'=>$row->Username,
            'Password'=>$row->Password,
            'Nickname'=>$row->Nickname,
            'Email'=>$row->Email,
            'Phone'=>$row->Phone,
            'RegisterTime'=>$row->RegisterTime,
            'LoginIP'=>$row->LoginIP,
            'LoginTime'=>$row->LoginTime,
            'State'=>$row->State,
        ));
        return $user;
    }

    public function saveCopUser($regUsername, $regPwd, $regEmail,$nickName)
    {
        $res = $this->insert(array(
            'Username' => $regUsername,
            'Password' => "",
            'Nickname' => $nickName,
            'Email' => $regEmail,
            "registerTime" => (new \DateTime())->format("Y-m-d H:i:s"),
            'State' => 1,
        ));
        if(!$res) return false;
        return $this->getLastInsertValue();
    }


}