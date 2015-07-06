<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-9-17
 * Time: 下午8:14
 */

namespace Mobile\Controller;

use Application\Entity\User;
use Application\Model\Logger;
use Application\Model\Regex;
use Authenticate\AssistantClass\PasswordEncrypt;
use Authenticate\AssistantClass\UserType;
use Authenticate\AssistantClass\SessionInfoKey;
use Home\Controller\CartController;
use Zend\Db\Sql\Where;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Json\Json;

class OrderController extends AbstractActionController{

    protected $_goodsTable;
    protected $_goodsStandardTable;
    protected $_deliveryAddressTable;
    protected $_userTable;
    protected $_orderTable;

    public function indexAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $arr = array();
        try{
            do{
                $goodsArr = $this->getGoodsFromCart($request);
                if(count($goodsArr) < 1){
                    return $this->redirect()->toUrl('/mobile');
                }


                //check if is user login
                $userId = null;
                $sl = $this->getServiceLocator();
                $authSvr = $sl->get('Session');
                if ($authSvr->hasIdentity()) {
                    $identity = $authSvr->getIdentity();
                    $role = $identity[SessionInfoKey::role];
                    if ($role == UserType::User){
                        $userId = $identity[SessionInfoKey::ID];
                    }
                }
                if($userId){
                    $deliveries = $this->getDeliveryAddressTable()->getAddressByUserId($userId);
                    if($deliveries && count($deliveries) > 0){
                        $delivery = $deliveries[0];
                        $arr["delivery"] = $delivery;
                    }
                }

                $cartCtrl = new CartController();
                $warningArr = $cartCtrl->constructWarningMsgArray($goodsArr);

                $arr["goods"] = $goodsArr;
                $arr["warningArr"] = $warningArr;

            }while(false);
        }catch (\Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), 'Mobile/Controller/Order indexAction:'.$e->getMessage());
        }
        $vm = new ViewModel($arr);
        return $vm;
    }

    public function orderedAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
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
        if(!$orderNumStr) return $this->redirect()->toUrl("/mobile");
        $orderNumArr = explode(",",$orderNumStr);
        foreach($orderNumArr as $orderNum){
            if(!is_numeric($orderNum)){
                return $this->redirect()->toUrl("/mobile");
            }
        }
        $userId = $identity[SessionInfoKey::ID];
        $where = new Where();
        $where->equalTo("UserID", $userId, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $where->in("ID", $orderNumArr);
        $orders = $this->getOrderTable()->getOrders($where);
        if(!$orders){
            return $this->redirect()->toUrl("/mobile");
        }
        $arr = array(
            "orders" => $orders,
        );
        $vm = new ViewModel($arr);
        return $vm;
    }

    public function submitOrderAction(){
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
                    $info = $data["info"];
                    $remark = $data["remark"];

                    $name = $info["name"];
                    $domain = $info["domain"];
                    $domain2 = $info["domain2"];
                    $domain3 = $info["domain3"];
                    $address = $info["address"];
                    $phone = $info["phone"];

                    //generate user account
                    $account = $this->getRandomAccount();
                    $encrypt = new PasswordEncrypt();
                    $user = new User(array(
                        "username" => $account["username"],
                        "password" => md5($account["password"]),
                        "nickname" => $account["username"],
                        "email"    => $account["username"].'@kuaidishu.com',
                        "registerTime" => (new \DateTime())->format("Y-m-d H:i:s"),
                        "state"    => 1
                    ));
//                    $user = new User(array(
//                        "username" => "mb_1411054199",
//                        "password" => "36685b8b33704d49ab51d9b964eb7dd9",
//                        "nickname" => "mb_1411054199",
//                        "email"    => "mb_1411054199".'@kuaidishu.com',
//                        "registerTime" => (new \DateTime())->format("Y-m-d H:i:s"),
//                        "state"    => 1
//                    ));
                    $userId = $this->getUserTable()->saveUser($user);
//                    $userId = "100599";
                    if(!$userId){
                        $code = 10002;
                        $message = "添加帐号失败";
                        break;
                    }
                    $user->setID($userId);

                    //login user account
                    $request->getPost()->set("loginUserName", $user->getUsername());
                    $request->getPost()->set("loginPWD", $user->getPassword());
                    $request->getPost()->set("userType", UserType::User);

                    $rsp = $this->forward()->dispatch('Authenticate\Controller\Authenticate', array(
                        'action' => 'login',
                        'innerCall' => true,
                    ));
                    $rspArr = Json::decode($rsp->getContent());
                    if(!$rspArr->state){
                        $code = 10003;
                        $message = "模拟登录失败";
                        $this->getUserTable()->delete(array('id' => $userId));
                        break;
                    }

                    //add address to user account
                    $request->getPost()->set("Domain", $domain);
                    $request->getPost()->set("Domain2", $domain2);
                    $request->getPost()->set("Domain3", $domain3);
                    $request->getPost()->set("Address", $address);
                    $request->getPost()->set("Phone", $phone);
                    $request->getPost()->set("Name", $name);

                    $rsp = $this->forward()->dispatch('User\Controller\UserInfo', array(
                        "action" => 'addDeliveryAddress',
                        "innerCall" => true,
                    ));
                    $rspArr = Json::decode($rsp->getContent());
                    if(!$rspArr->state){
                        $code = 10004;
                        $message = $rspArr->message->message;
                        $this->getUserTable()->delete(array('id' => $userId));
                        break;
                    }
                    $addrId = $rspArr->id;

                    //generate an order
                    $request->getPost()->set('items', $items);
                    $request->getPost()->set('addrId', $addrId);
                    $request->getPost()->set('remark', $remark);

                    $rsp = $this->forward()->dispatch('Home\Controller\Cart', array(
                        "action" => 'submitOrder',
                        "innerCall" => true,
                    ));
                    $rspArr = Json::decode($rsp->getContent());
                    if(!$rspArr->state){
                        $code = 10005;
                        $message = "新增订单失败";
                        $this->getDeliveryAddressTable()->delete(array('id' => $addrId));
                        $this->getUserTable()->delete(array('id' => $userId));
                        break;
                    }
                    $ids = $rspArr->ids;

                    //return account and orderIdArr
                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "account" => array(
                            "username" => $user->getUsername(),
//                            "password" => "",
                            "password" => $user->getPassword(),
                        ),
                        "ids" => $ids,
                    )));
                }
            }while(false);
        }catch (\Exception $e){
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
        }
        return $goodsArr;
    }

    protected function getRandomAccount(){
        $ts = time();
        $username = "mb_" . $ts;
        $password = substr(md5($ts + rand(1,1000)), 0, 16);
        return array(
            "username" => $username,
            "password" => $password
        );
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
     * @return \User\Model\UserTable
     */
    protected function getUserTable() {
        if(!$this->_userTable) {
            $t = $this->getServiceLocator();
            $this->_userTable = $t->get('User\Model\UserTable');
        }
        return $this->_userTable;
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
}
