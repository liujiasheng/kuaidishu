<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-7-4
 * Time: 下午4:29
 * To change this template use File | Settings | File Templates.
 */

namespace Authenticate\AssistantClass;

class PasswordEncrypt
{
    protected $auth = "_kuaidishu";
    public function getPasswordMd5($username, $password){
        $cr = new \Zend\Crypt\Password\Apache();
        $cr->setFormat('md5')->setAuthName($this->auth)->setUserName($username);
        return $cr->create($password);
    }

    public function verifyPassword($username,$password,$encryptPassword){
        $cr = new \Zend\Crypt\Password\Apache();
        $cr->setFormat('md5')->setAuthName($this->auth)->setUserName($username);
        return $cr->verify($password,$encryptPassword);
    }
}
