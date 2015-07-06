<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-12
 * Time: 下午10:46
 */

namespace User\Model;


use Application\Entity\WorkerOrder;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;

class WorkerOrderTable extends AbstractTableGateway{

    protected $table = "t_worker_order";

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function getEntityByOrderID($orderId)
    {
        $row = $this->select(array('OrderID'=>$orderId))->current();
        if(!$row){
            return false;
        }
        return new WorkerOrder(array(
            'ID'=>$row->ID,
            'WorkerID'=>$row->WorkerID,
            'OrderID'=>$row->OrderID,
            'AppointTime'=>$row->AppointTime,
        ));
    }


} 