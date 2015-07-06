<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-18
 * Time: 下午3:48
 */

namespace Home\Controller;


use Application\Entity\Order;
use Application\Entity\OrderDetail;
use Application\Model\Logger;
use Application\Model\Regex;
use Authenticate\AssistantClass\SessionInfoKey;
use Authenticate\AssistantClass\UserType;
use Zend\Authentication\Storage\Session;
use Zend\Db\Adapter\Driver\Pdo\Connection;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use \Application\Model\CommonFunctions;
use Exception;

class CartController extends AbstractActionController
{

    protected $_goodsTable;
    protected $_deliveryAddressTable;
    protected $_orderTable;
    protected $_orderDetailTable;
    protected $_goodsStandardTable;
    protected $_sellerWorkTimeTable;

    // home/cart
    public function indexAction()
    {
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $vm = null;
        try {
            do {
                $goodsArr = $this->getGoodsFromCart($request);

                if(count($goodsArr) < 1){
                    return $this->redirect()->toUrl("/");
                }

                $fw = $this->forward();
                $this->layout()->setVariable("topNavbar", $fw->dispatch('Application\Controller\Plugin', array(
                    "action" => "topnavbar",
                )));
                $this->layout()->setVariable("topHeader", $fw->dispatch('Application\Controller\Plugin', array(
                    "action" => "topheader"
                )));
                $this->layout()->setVariable("footer",$fw->dispatch('Application\Controller\Plugin',array(
                    "action" => "footer",
                )));
                $arr = array(
                    "goods" => $goodsArr,
                );
                $vm = new ViewModel($arr);
            } while (false);
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    // home/order
    public function orderAction()
    {
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $vm = null;
        try {
            do {
                //check if is user login
                $sl = $this->getServiceLocator();
                $authSvr = $sl->get('Session');
                if (!$authSvr->hasIdentity()) {
                    return $this->redirect()->toUrl($request->getBaseUrl()."/userlogin");
                }
                $identity = $authSvr->getIdentity();
                $role = $identity[SessionInfoKey::role];
                if ($role != UserType::User) {
                    return $this->redirect()->toUrl($request->getBaseUrl());
                }
                //end check

                //trim invalid cookies
                //$this->trimInvalidCartCookies($request);

                //get goods array
                $goodsArr = $this->getGoodsFromCart($request);

                if(count($goodsArr) < 1){
                    return $this->redirect()->toUrl("/");
                }

                $user = $this->getServiceLocator()->get('Authenticate\Model\UserTable')->getUserById($identity[SessionInfoKey::ID]);
                $userId = $user->getID();
                $addresses = $this->getDeliveryAddressTable()->getAddressByUserId($userId);

                //construct warning array
                $warningArr = $this->constructWarningMsgArray($goodsArr);

                $fw = $this->forward();
                $this->layout()->setVariable("topNavbar", $fw->dispatch('Application\Controller\Plugin', array(
                    "action" => "topnavbar",
                )));
                $this->layout()->setVariable("topHeader", $fw->dispatch('Application\Controller\Plugin', array(
                    "action" => "topheader"
                )));
                $this->layout()->setVariable("footer",$fw->dispatch('Application\Controller\Plugin',array(
                    "action" => "footer",
                )));
                $arr = array(
                    "goods" => $goodsArr,
                    "addresses" => $addresses?$addresses:array(),
                    "warnings" => $warningArr,
                );
                $vm = new ViewModel($arr);
            } while (false);
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    // home/ordered
    public function orderedAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $vm = null;
        try {
            do {
                //check if is user login
                $sl = $this->getServiceLocator();
                $authSvr = $sl->get('Session');
                if (!$authSvr->hasIdentity()) {
                    return $this->redirect()->toUrl($request->getBaseUrl()."/userlogin");
                }
                $identity = $authSvr->getIdentity();
                $role = $identity[SessionInfoKey::role];
                if ($role != UserType::User) {
                    return $this->redirect()->toUrl($request->getBaseUrl()."/userlogin");
                }
                //end check
                $orderNumStr = $request->getQuery("order");
                if(!$orderNumStr) return $this->redirect()->toUrl("/");
                $orderNumArr = explode(",",$orderNumStr);
                foreach($orderNumArr as $orderNum){
                    if(!is_numeric($orderNum)){
                        return $this->redirect()->toUrl("/");
                    }
                }
                $userId = $identity[SessionInfoKey::ID];
                $where = new Where();
                $where->equalTo("UserID", $userId, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
                $where->in("ID", $orderNumArr);
                $orders = $this->getOrderTable()->getOrders($where);
                if(!$orders){
                    return $this->redirect()->toUrl("/");
                }
//                $order = $this->getOrderTable()->getOrder(array(
//                    "ID" => $orderNum,
//                    "UserID" => $userId,
//                ));
//                if(!$order){
//                    return $this->redirect()->toUrl("/");
//                }


                $fw = $this->forward();
                $this->layout()->setVariable("topNavbar", $fw->dispatch('Application\Controller\Plugin', array(
                    "action" => "topnavbar",
                )));
                $this->layout()->setVariable("topHeader", $fw->dispatch('Application\Controller\Plugin', array(
                    "action" => "topheader"
                )));
                $this->layout()->setVariable("footer",$fw->dispatch('Application\Controller\Plugin',array(
                    "action" => "footer",
                )));
                $arr = array(
                    "orders" => $orders,
                );
                $vm = new ViewModel($arr);
            } while (false);
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    public function submitOrderAction()
    {
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try {
            do {
                if ($request->isPost()) {
                    $data = $request->getPost();
                    $items = $data["items"];
                    $addrId = $data["addrId"];
                    $remark = $data["remark"];
                    if($items == null || $addrId == null || $remark === null){
                        $code = 9999;
                        $message = "参数不全";
                        break;
                    }
                    //check items count
                    if (count($items) < 1) {
                        $code = 10000;
                        $message = "购物车为空";
                        break;
                    }
                    //check regex
                    $regex = new Regex();
                    $regex->checkId($addrId);
                    foreach ($items as $item) $regex->checkId($item["id"])->checkId($item["count"]);
                    $regex->checkOrderRemark($remark);
                    if ($regex->getCode() != 0) {
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //check address id is under the user
                    $authSvr = $this->getServiceLocator()->get('Session');
                    $identity = $authSvr->getIdentity();
                    if ($identity == null || $identity[SessionInfoKey::role] != UserType::User) {
                        $code = 10001;
                        $message = "未登录或非用户";
                        break;
                    }
                    /** @var $user \Application\Entity\User */
                    $user = $this->getServiceLocator()->get('Authenticate\Model\UserTable')->getUserById($identity[SessionInfoKey::ID]);
                    $userAddress = $this->getDeliveryAddressTable()->getDeliveryAddressById($addrId, $user->getID());
                    if (!$userAddress) {
                        $code = 10002;
                        $message = "收货地址id有误";
                        break;
                    }
                    $userAddress = $userAddress[0];
                    //end check

                    //get
                    $standardIDs = array();
                    $countInItem = array();
                    foreach($items as $item){
                        $standardIDs[] = $item["id"];
                        $countInItem[$item["id"]] = $item["count"];
                    }
                    $itemGoodsArr = $this->getGoodsStandardTable()->getGoodsByStandardIDs($standardIDs);
                    $itemGoodsArrBySeller = array();
                    foreach($itemGoodsArr as $itemGoods){
                        $itemGoodsArrBySeller[$itemGoods->getSellerID()][] = array(
                            "goods" => $itemGoods,
                            "count" => $countInItem[$itemGoods->getGoodsStandards()[0]["ID"]],
                        );
                    }

                    $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
                    /** @var $connection \Zend\Db\Adapter\Driver\Pdo\Connection */
                    $connection = $adapter->getDriver()->getConnection();
                    $connection->beginTransaction();
                    $orderIdArr = array();
                    foreach($itemGoodsArrBySeller as $sellerId => $itemGoods){
                        $order = new Order();
                        $order->setOptions(array(
                            "ID" => 0,
                            "UserID" => $user->getID(),
                            "OrderTime" => (new \DateTime())->format("Y-m-d H:i:s"),
                            "State" => 1,
                            "Remark" => $remark,
                            "Name" => $userAddress["Name"],
                            "Phone" => $userAddress["Phone"],
                            "Domain" => $userAddress["Domain"],
                            "Domain2" => $userAddress["Domain2"],
                            "Domain3" => $userAddress["Domain3"],
                            "Address" => $userAddress["Address"],
                            "SellerID" => $sellerId,
                        ));
                        //generate order
                        $orderId = $this->getOrderTable()->saveOrder($order);
                        if (!$orderId) {
                            $code = 11001;
                            $message = "插入订单表失败";
                            break;
                        }
                        $order->setID($orderId);
                        $orderIdArr[] = $orderId;
                        $orderTotal = 0;
                        $mark = true;
                        //generate order detail
                        foreach ($itemGoods as $item) {
                            $count = $item["count"];
                            $goods = $item["goods"];
                            $standard = $goods->getGoodsStandards()[0];
                            $orderDetail = new OrderDetail();
                            $total = floatval($standard["Price"]) * intval($count);
                            $orderTotal += $total;
                            $orderDetail->setOptions(array(
                                "ID" => 0,
                                "OrderID" => $orderId,
                                "SellerID" => $goods->getSellerID(),
                                "GoodsID" => $goods->getID(),
                                "Name" => $goods->getName().($standard["Standard"]=="默认"?"":"(".$standard["Standard"].")"),
                                "Price" => $standard["Price"],
                                "Unit" => $goods->getUnit(),
                                "Count" => $count,
                                "Total" => $total,
                                "Image" => $goods->getImage(),
                                "Comment" => $goods->getComment(),
                                "Barcode" => $goods->getBarcode(),
                            ));
                            $res = $this->getOrderDetailTable()->saveOrderDetail($orderDetail);
                            if(!$res){
                                $mark = false;
                                break;
                            }
                        }
                        if(!$mark){
                            $code = 11002;
                            $message = "插入订单详细表失败";
                            break;
                        }
                        //update order
                        $order->setID($orderId)
                            ->setTotal($orderTotal);
                        $res = $this->getOrderTable()->saveOrder($order);
                        if(!$res){
                            $code = 11003;
                            $message = "更新订单表失败";
                            break;
                        }
                        //push message broadcast
                        $this->pushToSeller($sellerId, $orderId);

                    }
                    if($code != 0){
                        $connection->rollback();
                        break;
                    }

//                    $order = new Order();
//                    $order->setOptions(array(
//                        "ID" => 0,
//                        "UserID" => $user->getID(),
//                        "OrderTime" => (new \DateTime())->format("Y-m-d H:i:s"),
//                        "State" => 1,
//                        "Remark" => $remark,
//                        "Name" => $userAddress["Name"],
//                        "Phone" => $userAddress["Phone"],
//                        "Domain" => $userAddress["Domain"],
//                        "Domain2" => $userAddress["Domain2"],
//                        "Domain3" => $userAddress["Domain3"],
//                        "Address" => $userAddress["Address"],
//                    ));
//                    //generate order
//                    $orderId = $this->getOrderTable()->saveOrder($order);
//                    $order->setID($orderId);
//                    if (!$orderId) {
//                        $code = 11001;
//                        $message = "插入订单表失败";
//                        $connection->rollback();
//                        break;
//                    }
//                    $orderTotal = 0;
//                    $mark = true;
//                    //generate order detail
//                    foreach ($items as $item) {
//                        $count = $item["count"];
////                        $goods = $this->getGoodsTable()->getGoods($item["id"]);
//                        $goods = $this->getGoodsStandardTable()->getGoodsByStandardID($item["id"]);
//                        if(!$goods){
////                            $mark = false;
////                            break;
//                            continue;
//                        }
//                        $standard = $goods->getGoodsStandards()[0];
//                        $orderDetail = new OrderDetail();
//                        $total = floatval($standard["Price"]) * intval($count);
//                        $orderTotal += $total;
//                        $orderDetail->setOptions(array(
//                            "ID" => 0,
//                            "OrderID" => $orderId,
//                            "SellerID" => $goods->getSellerID(),
//                            "GoodsID" => $goods->getID(),
//                            "Name" => $goods->getName().($standard["Standard"]=="默认"?"":"(".$standard["Standard"].")"),
//                            "Price" => $standard["Price"],
//                            "Unit" => $goods->getUnit(),
//                            "Count" => $count,
//                            "Total" => $total,
//                            "Image" => $goods->getImage(),
//                            "Comment" => $goods->getComment(),
//                            "Barcode" => $goods->getBarcode(),
//                        ));
//                        $res = $this->getOrderDetailTable()->saveOrderDetail($orderDetail);
//                        if(!$res){
//                            $mark = false;
//                            break;
//                        }
//                    }
//                    if(!$mark){
//                        $code = 11002;
//                        $message = "插入订单详细表失败";
//                        $connection->rollback();
//                        break;
//                    }
//                    //update order
//                    $order->setID($orderId)
//                        ->setTotal($orderTotal);
//                    $res = $this->getOrderTable()->saveOrder($order);
//                    if(!$res){
//                        $code = 11003;
//                        $message = "更新订单表失败";
//                        $connection->rollback();
//                        break;
//                    }

                    //success , commit transaction!!
                    $connection->commit();

                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "ids" => $orderIdArr,
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

    protected function getGoodsFromCart($request)
    {
        /** @var $request \Zend\Http\Request */
        $cookie = $request->getCookie();
        $goodsArr = array();
        $cart = isset($cookie->cart)? $cookie->cart : null;
        if ($cart) {
            $arr = explode(",", $cart);
            $items = array();
            $ids = array();
            $regex = new Regex();
            for ($i = 0, $j = 0; $i < count($arr); $i++) {
                if(!isset($arr[$i]) || !isset($arr[$i+1])) break;
                $ids[$j++] = $arr[$i];
                $regex->checkId($arr[$i]);
                $items[$arr[$i]] = $arr[++$i];
                $regex->checkId($arr[$i]);
            }
            if ($regex->getCode() != 0) {
                return array();
            }

            $goods = $this->getGoodsStandardTable()->getGoodsByStandardIDs($ids);

            foreach ($goods as $good) {
                $standard = $good->getGoodsStandards()[0];
                $goodsArr[$good->getSellerID()][$standard["ID"]] = array(
                    "id" => $standard["ID"],
                    "name" => $good->getName(),
                    "price" => $standard["Price"],
                    "image" => $good->getImage(),
                    "mainClassName" => $good->getMainClassName(),
                    "className" => $good->getClassName(),
                    "sellerId" => $good->getSellerID(),
                    "sellerName" => $good->getSellerName(),
                    "count" => $items[$standard["ID"]],
                    "standard" => $standard["Standard"],
                );
            }

//            $where = new Where();
//            $where->in('t_goods.id', $ids);
//            $goods = $this->getGoodsTable()->fetchSlice(1, 1000, $where);
//            $i = 0;
//            foreach ($goods as $good) {
//                $goodsArr[$good->getSellerID()][$good->getID()] = array(
//                    "id" => $good->getID(),
//                    "name" => $good->getName(),
//                    "price" => $good->getPrice(),
//                    "image" => $good->getImage(),
//                    "mainClassName" => $good->getMainClassName(),
//                    "className" => $good->getClassName(),
//                    "sellerId" => $good->getSellerID(),
//                    "sellerName" => $good->getSellerName(),
//                    "count" => $items[$good->getID()],
//                );
//            }
        }
        return $goodsArr;
    }

    protected function trimInvalidCartCookies($request){
        /** @var $request \Zend\Http\Request */
        $cookie = $request->getCookie();
        $cart = isset($cookie->cart)? $cookie->cart : null;
        if($cart){
            $arr = explode(",", $cart);
            $items = array();
            $ids = array();
            $regex = new Regex();
            for ($i = 0, $j = 0; $i < count($arr); $i++) {
                if(!isset($arr[$i]) || !isset($arr[$i+1])) break;
                $ids[$j++] = $arr[$i];
                $regex->checkId($arr[$i]);
                $items[$arr[$i]] = $arr[++$i];
                $regex->checkId($arr[$i]);
            }

            $validArr = $this->getGoodsStandardTable()->getGoodsByStandardIDs($ids);
            if(count($validArr) < count($ids)){
                //there are some invalid items
            }
        }
    }

    public function constructWarningMsgArray($goodsArr){
        $arr = array();
        $now = date('Y-m-d H:i:s');
        $timePrev = explode(" ", $now)[0];
        $timeAfter = explode(" ", $now)[1];
        $nowToday = CommonFunctions::getTodaySeconds($timeAfter);
        $startTimeStampToday = CommonFunctions::getTodaySeconds(CommonFunctions::$_startWorkTime);
        $endTimeStampToday = CommonFunctions::getTodaySeconds(CommonFunctions::$_endWorkTime);

        //$arr[] = '各位小伙伴们，快递鼠准备升级啦，近期快递鼠和十大厂家深度合作。百货商品将有快递鼠自己把关，自己销售，并进行搜索优化。饮料，零食，日化，烟酒，既便宜又方便。给我们一周时间，全新的快递鼠服务您。';
        $timePoints = array(
            '12:30:00',
            '17:45:00',
            '21:30:00',
            '22:30:00'
        );
        $arr[] = '快递鼠配送时间为每天的12:30，17:45，21:30，22:30。我们会统一在这些时间点配送，下单后会在下一个配送时间开始配送。';


//        if($nowToday<$startTimeStampToday){
//            $arr[] = "订单将会在晚上9点开始送货。";
//        }
//        if($nowToday>($endTimeStampToday)){
//            $arr[] = "订单将会在第二天的晚上9点开始发货。";
//        }
//        if($nowToday>($endTimeStampToday-15*60) &&$nowToday<$endTimeStampToday){
//            $arr[] = "由于我们送货时间在晚上9点到11点，请您下次尽量在10点45分前下单，谢谢。";
//        }
        if(count($goodsArr)>1){
            $arr[] = "由于您采购了不同商家的商品，系统将会为您拆成 ".count($goodsArr)." 单。";
        }

        return $arr;
    }

    protected function checkSellerWorkerTime($sellerId){
        $workTime = $this->getSellerWorkTimeTable()->getSellerWorkTime($sellerId);

        return $workTime;
    }

    public function generateOrderNumber(){
        $time = $this->getTimeStamp();
        $suffix = $this->getSuffix();
        $num = $time . $suffix;
        return $num;
    }
    protected function getTimeStamp(){
        $time = explode(" ", microtime());
        $stamp = $time[1] . $time[0] * 1000000;
        return $stamp;
    }
    protected function getSuffix(){
        $r = mt_rand(1000,9999);
        return $r;
    }


    /**
     * @return \Goods\Model\GoodsTable
     */
    public function getGoodsTable()
    {
        if (!$this->_goodsTable) {
            $t = $this->getServiceLocator();
            $this->_goodsTable = $t->get('Goods\Model\GoodsTable');
        }
        return $this->_goodsTable;
    }

    /**
     * @return \User\Model\DeliveryAddressTable
     */
    public function getDeliveryAddressTable()
    {
        if (!$this->_deliveryAddressTable) {
            $t = $this->getServiceLocator();
            $this->_deliveryAddressTable = $t->get('User\Model\DeliveryAddressTable');
        }
        return $this->_deliveryAddressTable;
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
     * @return \Goods\Model\GoodsStandardTable
     */
    public function getGoodsStandardTable(){
        if(!$this->_goodsStandardTable){
            $t = $this->getServiceLocator();
            $this->_goodsStandardTable = $t->get('Goods\Model\GoodsStandardTable');
        }
        return $this->_goodsStandardTable;
    }

    /**
     * @return \Seller\Model\SellerWorkTimeTable
     */
    public function getSellerWorkTimeTable(){
        if(!$this->_sellerWorkTimeTable){
            $t = $this->getServiceLocator();
            $this->_sellerWorkTimeTable = $t->get('Seller\Model\SellerWorkTimeTable');
        }
        return $this->_sellerWorkTimeTable;
    }

    public function pushToSeller($sellerId, $orderId){
        // This is our new stuff
        $context = new \ZMQContext();
        /** @var $socket \ZMQSocket */
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'my pusher');
        $socket->connect("tcp://127.0.0.1:5555");

        $socket->send(json_encode(array(
            "type" => 1,
            "message" => array(
                "sellerId" => $sellerId,
                "data" => array(
                    "type" => 3,
                    "orderIds" => array(
                        $orderId,
                    )
                ),
            ),
        )));
    }

} 