<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-29
 * Time: 下午2:15
 */

namespace AdminMgr\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class BuyerSellerTable extends AbstractTableGateway{

    protected $table = "t_buyer_seller";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

} 