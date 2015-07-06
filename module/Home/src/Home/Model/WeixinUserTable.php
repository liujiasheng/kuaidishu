<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-21
 * Time: 下午10:05
 */

namespace Home\Model;


use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class WeixinUserTable extends AbstractTableGateway{

    protected $table = "t_weixin_user";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function isOpenIDExist($openid, $type){
        $exist = $this->select(array(
            "openid" => $openid,
            "type" => $type
        ))->current();
        return $exist? true : false ;
    }

    public function getUserIDByOpenID($openid){
        $res = $this->select(array(
            "openid" => $openid,
            "type" => 1,
        ))->current();
        if(!$res){
            return false;
        }else{
            $id = $res->UserID;
            return $id?$id:false;
        }
    }

}
