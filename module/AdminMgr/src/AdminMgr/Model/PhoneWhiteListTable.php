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

class PhoneWhiteListTable extends AbstractTableGateway{

    protected $table = "t_phone_whitelist";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
} 