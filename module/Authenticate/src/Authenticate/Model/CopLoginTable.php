<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-5
 * Time: 下午10:03
 */

namespace Authenticate\Model;


use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class CopLoginTable  extends AbstractTableGateway{

    protected $table = 't_cop_login';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function checkOpenId($openid){
        $rs = $this->select(array('OpenId'=>$openid))->current();
        if($rs!=false){
            return $rs->getArrayCopy();
        }
        return null;
    }

    public function saveCopUser($username, $openid, $type)
    {
        $res = $this->insert(array(
            'UserName'=>$username,
            'OpenId'=>$openid,
            'Type'=>$type,
            'BindTime'=>(new \DateTime())->format("Y-m-d H:i:s"),
        ));
        if(!$res) return false;
        return $this->getLastInsertValue();
    }

    public function checkUsername($username)
    {
        $rs = $this->select(array('userName'=>$username))->current();
        return $rs;
    }
} 