<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-31
 * Time: 下午2:40
 */

namespace AdminMgr\Model;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class PhoneBlackListTable extends AbstractTableGateway{

    protected $table = "t_phone_blacklist";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
} 