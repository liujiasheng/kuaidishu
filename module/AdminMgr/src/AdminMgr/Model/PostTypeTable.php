<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-23
 * Time: 下午9:33
 */

namespace AdminMgr\Model;


use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class PostTypeTable extends AbstractTableGateway{

    protected $table = "t_post_type";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getTypeOption(){
        $rs = $this->select();
        if(!$rs){
            return false;
        }
        return $rs->toArray();
    }
} 