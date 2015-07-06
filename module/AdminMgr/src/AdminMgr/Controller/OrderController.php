<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-23
 * Time: 下午3:42
 */

namespace AdminMgr\Controller;


use AdminBase\Model\AdminMenu;
use Application\Model\DomainList;
use Application\Model\FetionSender;
use Application\Model\Logger;
use Application\Model\Regex;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\Parameters;
use Zend\View\Model\ViewModel;
use Exception;

class OrderController extends AbstractActionController{

    protected $_orderTable;
    protected $_orderDetailTable;
    protected $_workerTable;
    protected $_workerOrderTable;
    protected $_buyerSellerTable;

    public function indexAction(){
        $vm = null;
        try{
            $basePath = $this->getRequest()->getBasePath();
            $cf = new AdminMenu();
            $menu = $cf->getMenu('订单管理', $basePath);
            $orders = $this->getOrderTable()->fetchSlice(1, 10, null);
            $orderDetails = $this->getOrderDetailTable()->getOrderDetailByOrderIDs(array_keys($orders));
            $count = $this->getOrderTable()->fetchCount(null);
            $domainList = new DomainList();
            $workers = $this->getWorkerTable()->fetchSlice(1,100,array("state"=>"1"));
            $arr = array(
                "menu" => $menu,
                "orderTable" => $orders,
                "orderDetailTable" => $orderDetails,
                "count" => $count,
                "domainList" => $domainList->get(),
                "workers" => $workers,
            );

            $fw = $this->forward();
            $this->layout()->setVariable("topNavbar",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "topnavbar",
            )));
            $this->layout()->setVariable("adminHeader",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "adminHeader",
            )));
            $this->layout()->setVariable("footer",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "footer",
            )));
            $vm =  new ViewModel($arr);
            $this->setRenderer();
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    private function setRenderer(){
        $baseUrl = $this->getRequest()->getBaseUrl();
        $renderer = $this->getServiceLocator()->get( 'Zend\View\Renderer\PhpRenderer');
        $renderer->headScript()->prependFile($baseUrl . '/js/admin/adminOrder.js');
    }

    public function searchAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try {
            do {
                if ($request->isPost()) {
                    $data = $request->getPost();
                    $state = $data["state"];
                    $text = $data["searchText"];
                    $curPage = $data["curPage"];
                    $pageCount = $data["pageCount"];
                    $domain = $data["domain"];
                    $domain2 = $data["domain2"];
                    $domain3 = $data["domain3"];
                    $address = $data["address"];
                    if($state===null||$text===null||$curPage===null||$pageCount===null||$domain===null||
                        $domain2===null||$domain3===null||$address===null){
                        $code = 10001;
                        $message = "参数不全";
                        break;
                    }
                    //check regex
                    $regex = new Regex();
                    $regex->checkState($state)
                        ->checkSearchText($text);
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //end check
                    $where = array();
                    if($state != "0")
                        $where[] = "t_order.state = ".$state;

                    $orders = $this->getOrderTable()->fetchSlice( intval($curPage), intval($pageCount), $where);
                    $count = $this->getOrderTable()->fetchCount($where);
                    $orderDetails = $this->getOrderDetailTable()->getOrderDetailByOrderIDs(array_keys($orders));
                    $orderArr = $this->getOrderTable()->entitiesToArray($orders);
                    $response->setContent(\Zend\Json\Json::encode(array(
                        "state" => true,
                        "count" => $count,
                        "orders" => $orderArr,
                        "orderDetails" => $orderDetails,
                    )),true);
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
            $code = -1;
        }
        if ($code != 0) {
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function viewAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try {
            do {
                if ($request->isPost()) {
                    $data = $request->getPost();
                    $id = $data["id"];
                    if($id == null){
                        $code = 10001;
                        $message = "参数不全";
                        break;
                    }
                    //check regex
                    $regex = new Regex();
                    $regex->checkId($id);
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //end check
                    $order = $this->getOrderTable()->getOrder(array(
                        "id" => $id
                    ));
                    if(!$order){
                        $code = 10002;
                        $message = "该定订单不存在";
                        break;
                    }
                    $orderMsg = $this->getOrderMsg($order);

                    $response->setContent(\Zend\Json\Json::encode(array(
                        "state" => true,
                        "order" => $this->getOrderTable()->entitiesToArray([$order])[0],
                        "orderMsg" => $orderMsg,
                    )),true);
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
            $code = -1;
        }
        if ($code != 0) {
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function rejectOrdersAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try {
            do {
                if ($request->isPost()) {
                    $data = $request->getPost();
                    $text = $data["text"];
                    $ids = $data["ids"];
                    $regex = new Regex();
                    $regex->checkRejectReason($text);
                    if(!is_array($ids)){
                        $code = 13001;
                        $message = "参数错误";
                        break;
                    }
                    foreach($ids as $id) $regex->checkId($id);
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //end check

                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    /** @var $connection \Zend\Db\Adapter\Driver\Pdo\Connection */
                    $connection = $adapter->getDriver()->getConnection();
                    $connection->beginTransaction();
                    $totalChanged = 0;
                    $changedArr = array();
                    $mark = true;
                    foreach($ids as $id){
                        $legal = $this->getOrderTable()->select(array(
                            "id = ". $id,
                            "(state = 1 or state = 2)",
                        ))->current();
                        if($legal){
                            $res = $this->getOrderTable()->update(array(
                                "comment" => $text,
                                "state" => 3,
                            ),array(
                                "id" => $id
                            ));
                            if($res < 1){
                                $mark = false;
                                break;
                            }
                            $totalChanged++;
                            array_push($changedArr, $id);
                        }
                    }
                    if(!$mark){
                        $connection->rollback();
                        $code = 13002;
                        $message = "更新数据失败";
                        break;
                    }

                    $connection->commit();

                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "total" => $totalChanged,
                        "changedArr" => $changedArr,
                    )));
                }
            } while (false);
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
            $code = -1;
        }
        if ($code != 0) {
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function confirmOrdersAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try {
            do {
                if ($request->isPost()) {
                    $data = $request->getPost();
                    $ids = $data["ids"];
                    $workerId = $data["workerId"];
                    $regex = new Regex();
                    $regex->checkId($workerId);
                    if(!is_array($ids)){
                        $code = 13001;
                        $message = "参数错误";
                        break;
                    }
                    foreach($ids as $id) $regex->checkId($id);
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    if(!$wk = $this->getWorkerTable()->select(array(
                        "id" => $workerId,
                        "state" => "1",
                    ))->current()){
                        $code = 13002;
                        $message = "不存在此在职员工";
                        break;
                    }
                    //end check

                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    /** @var $connection \Zend\Db\Adapter\Driver\Pdo\Connection */
                    $connection = $adapter->getDriver()->getConnection();
                    $connection->beginTransaction();
                    $totalChanged = 0;
                    $changedArr = array();
                    $sendErrArr = array();
                    $sendBuyerErrArr = array();
                    $mark = true;
                    foreach($ids as $id){
                        $order = $this->getOrderTable()->select(array(
                            "id" => $id,
                            "state" => "1",
                        ))->current();
                        if($order){
                            //insert workerOrder
                            $res = $this->getWorkerOrderTable()->insert(array(
                                "WorkerID" => $workerId,
                                "OrderID" => $id,
                                "AppointTime" => (new \DateTime())->format("Y-m-d H:i:s"),
                            ));
                            if($res < 1){
                                $mark = false;
                                break;
                            }
                            //update order
                            $res = $this->getOrderTable()->update(array(
                                "state" => 2
                            ),array(
                                "id" => $id,
                            ));
                            if($res < 1){
                                $mark = false;
                                break;
                            }
                            $totalChanged++;
                            array_push($changedArr, $id);

                            //send order to Worker
                            $p = new Parameters();
                            $p->set("workerid", $workerId)
                                ->set("orderid", $id);
                            $this->getRequest()->setMethod('GET');
                            $this->getRequest()->setQuery($p);
                            $rsl = $this->forward()->dispatch('AdminMgr\Controller\Order',array(
                                "action" => "sendOrderToWorker",
                            ));
                            $msgArr = Json::decode($rsl->getContent());
                            if(!$msgArr->state){
                                $sendErrArr[] = array(
                                    "orderid" => $id,
                                    "message" => $msgArr->message,
                                );
                            }
//                            //send order to buyer
//                            $buyerseller = $this->getBuyerSellerTable()->select(array(
//                                "SellerID" => $order->SellerID,
//                            ))->current();
//                            if($buyerseller){
//                                if($buyerseller->WorkerID != $workerId){
//                                    $p = new Parameters();
//                                    $p->set("workerid", $buyerseller->WorkerID)
//                                        ->set("orderid", $id);
//                                    $this->getRequest()->setMethod('GET');
//                                    $this->getRequest()->setQuery($p);
//                                    $rsl = $this->forward()->dispatch('AdminMgr\Controller\Order',array(
//                                        "action" => "sendOrderToWorker",
//                                    ));
//                                    $msgArr = Json::decode($rsl->getContent());
//                                    if(!$msgArr->state){
//                                        $sendBuyerErrArr[] = array(
//                                            "orderid" => $id,
//                                            "message" => $msgArr->message,
//                                        );
//                                    }
//                                }
//                            }
                        }
                    }
                    if(!$mark){
                        $connection->rollback();
                        $code = 13003;
                        $message = "插入数据失败";
                        break;
                    }
                    //success
                    $connection->commit();

                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "total" => $totalChanged,
                        "changedArr" => $changedArr,
                        "sendErrArr" => $sendErrArr,
                        "sendBuyerErrArr" => $sendBuyerErrArr,
                    )));
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
            $code = -1;
        }
        if ($code != 0) {
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function sendOrderToWorkerAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try {
            do {
                $data = $request->getQuery();
                $orderid = $data["orderid"];
                $workerid = $data["workerid"];
                if($orderid == null || $workerid == null){
                    $code = 20001;
                    $message = "参数不全";
                    break;
                }
                $regex = new Regex();
                $regex->flush()->checkId($orderid)->checkId($workerid);
                if($regex->getCode() != 0 ){
                    $code = $regex->getCode();
                    $message = $regex->getMessage();
                    break;
                }
//                $legel = $this->getWorkerOrderTable()->select(array(
//                    "workerid" => $workerid,
//                    "orderid" => $orderid,
//                ))->current();
//                if(!$legel){
//                    $code = 20002;
//                    $message = "此操作非法";
//                    break;
//                }
                //check order state is 2(已确认)
                $legel = $this->getOrderTable()->select(array(
                    "id = ".$orderid,
                    "(state = 1 or state = 2)",
                ))->current();
                if(!$legel){
                    $code = 20003;
                    $message = "此操作非法，订单已经完成或被退回。";
                    break;
                }
                //end check

                $worker = $this->getWorkerTable()->getWorker($workerid);
                if(!$worker){
                    $code = 20004;
                    $message = "获取数据失败";
                    break;
                }

                //get order content
                $order = $this->getOrderTable()->getOrder(array(
                    "id" => $orderid,
                ));
//                $orderDetails = $this->getOrderDetailTable()->getOrderDetailByOrderIDs([$orderid]);
//                $orderDetails = array_pop(array_pop($orderDetails));
//
//                $sellerName = reset($orderDetails)["SellerName"];
//                $orderContent = "[".$sellerName."]\n";
//                foreach($orderDetails as $detail){
//                    $orderContent .= " ".$detail["Name"]."*".$detail["Count"].";\n";
//                }
//
//                $content = sprintf('订单号:%s
//订单内容:%s
//备注:%s
//总额:%s元
//收货人:%s(%s)
//地址:%s %s %s %s。',
//                    $order->getID(),
//                    $orderContent,
//                    $order->getRemark()==""?"空":$order->getRemark(),
//                    $order->getTotal(),
//                    $order->getName(),
//                    $order->getPhone(),
//                    $order->getDomain(),
//                    $order->getDomain2(),
//                    $order->getDomain3(),
//                    $order->getAddress());
                $content = $this->getOrderMsg($order);

                $msgArr = FetionSender::sendMessage($worker->getPhone(), $content);
                if( !$msgArr["state"] ){
                    $code = 20005;
                    $message = $msgArr["message"];
                    break;
                }

                //push message to worker
                $this->pushToWorker($workerid, $orderid);

                $response->setContent(Json::encode(array(
                    "state" => true,
                )));
            }while(false);
        }catch (Exception $ex){

        }
        if($code != 0 ){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function sendOrdersToWorkerAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try {
            do {
                if ($request->isPost()) {
                    $data = $request->getPost();
                    $workerId = $data["workerid"];
                    $orderIds = $data["ids"];
                    if($workerId === null || $orderIds === null || !is_array($orderIds)){
                        $code = 20001;
                        $message = "参数不全";
                        break;
                    }
                    $regex = new Regex();
                    $regex->checkId($workerId);
                    foreach($orderIds as $id) $regex->checkId($id);
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    if(!$wk = $this->getWorkerTable()->select(array(
                        "id" => $workerId,
                        "state" => "1",
                    ))->current()){
                        $code = 13002;
                        $message = "不存在此在职员工";
                        break;
                    }
                    //end check
                    $msg = "";
                    foreach($orderIds as $id){
                        //send order to Worker
                        $p = new Parameters();
                        $p->set("workerid", $workerId)
                            ->set("orderid", $id);
                        $this->getRequest()->setMethod('GET');
                        $this->getRequest()->setQuery($p);
                        $rsl = $this->forward()->dispatch('AdminMgr\Controller\Order',array(
                            "action" => "sendOrderToWorker",
                        ));
                        $msgArr = Json::decode($rsl->getContent());
                        if(!$msgArr->state){
                            $msg .= $id.":".$msgArr->message."\n";
                        }
                    }
                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "message" => $msg,
                    )));
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
            $code = -1;
        }
        if ($code != 0) {
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function finishOrdersAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try {
            do {
                if ($request->isPost()) {
                    $data = $request->getPost();
                    $ids = $data["ids"];
                    if($ids==null || count($ids)<1 ){
                        $code = 13001;
                        $message = "参数不全";
                        break;
                    }
                    $regex = new Regex();
                    foreach($ids as $id) $regex->checkId($id);
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //end check

                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    /** @var $connection \Zend\Db\Adapter\Driver\Pdo\Connection */
                    $connection = $adapter->getDriver()->getConnection();
                    $connection->beginTransaction();
                    $totalChanged = 0;
                    $changedArr = array();
                    $mark = true;
                    foreach($ids as $id){
                        $legal = $this->getOrderTable()->select(array(
                            "id" => $id,
                            "state" => 2,
                        ))->current();
                        if($legal){
                            $res = $this->getOrderTable()->update(array(
                                "EndTime" => (new \DateTime())->format("Y-m-d H:i:s"),
                                "state" => "9",
                            ),array(
                                "id" => $id,
                            ));
                            if($res < 1){
                                $mark = false;
                                break;
                            }
                            $totalChanged++;
                            array_push($changedArr, $id);
                        }
                    }
                    if(!$mark){
                        $connection->rollback();
                        $code = 13002;
                        $message = "更新数据失败";
                        break;
                    }

                    //success
                    $connection->commit();

                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "total" => $totalChanged,
                        "changedArr" => $changedArr,
                    )));
                }
            } while (false);
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
            $code = -1;
        }
        if ($code != 0) {
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }


    protected function getOrderMsg($order){
        $orderDetails = $this->getOrderDetailTable()->getOrderDetailByOrderIDs([$order->getID()]);
        $orderDetails = array_pop(array_pop($orderDetails));

        $sellerName = reset($orderDetails)["SellerName"];
        $orderContent = "[".$sellerName."]\n";
        foreach($orderDetails as $detail){
            $orderContent .= ""."(".$detail["Price"].")".$detail["Name"]."*".$detail["Count"].";\n";
        }
        $content = sprintf('订单号:%s
下单时间:%s
订单内容:%s
备注:%s
总额:%s元
收货人:%s(%s)
地址:%s %s %s %s。',
            $order->getID(),
            split(" ",$order->getOrderTime())[1],
            $orderContent,
            $order->getRemark()==""?"空":$order->getRemark(),
            $order->getTotal(),
            $order->getName(),
            $order->getPhone(),
            $order->getDomain(),
            $order->getDomain2(),
            $order->getDomain3(),
            $order->getAddress());
        return $content;
    }
    /**
     * @return \Home\Model\OrderTable
     */
    public function getOrderTable()
    {
        if (!$this->_orderTable) {
            $t = $this->getServiceLocator();
            $this->_orderTable = $t->get('Home\Model\OrderTable');
        }
        return $this->_orderTable;
    }

    /**
     * @return \Home\Model\OrderDetailTable
     */
    public function getOrderDetailTable()
    {
        if (!$this->_orderDetailTable) {
            $t = $this->getServiceLocator();
            $this->_orderDetailTable = $t->get('Home\Model\OrderDetailTable');
        }
        return $this->_orderDetailTable;
    }

    /**
     * @return \Worker\Model\WorkerTable
     */
    public function  getWorkerTable(){
        if(!$this->_workerTable){
            $t = $this->getServiceLocator();
            $this->_workerTable = $t->get('Worker\Model\WorkerTable');
        }
        return $this->_workerTable;
    }

    /**
     * @return \AdminMgr\Model\WorkerOrderTable
     */
    public function  getWorkerOrderTable(){
        if(!$this->_workerOrderTable){
            $t = $this->getServiceLocator();
            $this->_workerOrderTable = $t->get('AdminMgr\Model\WorkerOrderTable');
        }
        return $this->_workerOrderTable;
    }

    /**
     * @return \AdminMgr\Model\BuyerSellerTable
     */
    public function  getBuyerSellerTable(){
        if(!$this->_buyerSellerTable){
            $t = $this->getServiceLocator();
            $this->_buyerSellerTable = $t->get('AdminMgr\Model\BuyerSellerTable');
        }
        return $this->_buyerSellerTable;
    }

    public function pushToWorker($workerId, $orderId){
        // This is our new stuff
        $context = new \ZMQContext();
        /** @var $socket \ZMQSocket */
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://127.0.0.1:5555");

        $socket->send(json_encode(array(
            "type" => 2,
            "message" => array(
                "workerId" => $workerId,
                "data" => array(
                    "orderId" => $orderId,
                ),
            ),
        )));
    }
} 