<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-9-4
 * Time: 上午11:40
 */

namespace Worker\Controller;


use Application\Model\Logger;
use Application\Model\Regex;
use Authenticate\AssistantClass\SessionInfoKey;
use Authenticate\AssistantClass\UserType;
use Worker\Model\OrderDetailTable;
use Worker\Model\OrderTable;
use Worker\Model\WorkerOrderTable;
use Worker\Model\WorkerTable;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Sql\Where;
use Zend\Http\Request;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class WorkerOrderController extends AbstractActionController{

    private $logger;
    protected $_workerOrderTable;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public function getWorkerIdAction(){
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');
        if(!$authSvr->hasIdentity()&&$authSvr->getIdentity()==null){
            $response->setContent(Json::encode(array(
                "state"=>false,
                "code"=>-1,
                "message"=>array(
                    "message"=>"没登录，不能操作"
                )
            )));
            return $response;
        }
        $identitied = $authSvr->getIdentity();
        $response->setContent(Json::encode(array(
            "state"=>true,
            "code"=>0,
            "id"=>$identitied[SessionInfoKey::ID]

        )));
        return $response;
    }

    public function getOrderDetailAction(){
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');
        if(!$authSvr->hasIdentity()&&$authSvr->getIdentity()==null){
            $response->setContent(Json::encode(array(
                "state"=>false,
                "code"=>-1,
                "message"=>array(
                    "message"=>"没登录，不能操作"
                )
            )));
            return $response;
        }
        $post = $request->getPost();
        $orderId = $post->get("id");
        /** @var $orderDetailTable OrderDetailTable */
        $orderDetailTable = $this->getServiceLocator()->get('Worker\Model\OrderDetailTable');
        $list = $orderDetailTable->getDetailsByOrderId($orderId);
        $response->setContent(Json::encode(array(
            "state"=>true,
            "code"=>0,
            "list"=>$list
        )));
        return $response;
    }

    public function getOrderListAction(){

        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        /** @var $authSvr AuthenticationService */
        $authSvr = $this->getServiceLocator()->get('Session');
        if(!$authSvr->hasIdentity()&&$authSvr->getIdentity()==null){
            $response->setContent(Json::encode(array(
                "state"=>false,
                "code"=>-1,
                "message"=>array(
                   "message"=>"没登录，不能操作"
                )
            )));
            return $response;
        }
        $identified = $authSvr->getIdentity();
        $role = $identified[SessionInfoKey::role];
        if($role!=UserType::Worker){
            $response->setContent(Json::encode(array(
                "state"=>false,
                "code"=>-2,
                "message"=>array(
                    "message"=>"非员工不能操作"
                )
            )));
            return $response;
        }
        $workerId = $identified[SessionInfoKey::ID];
        $post = $request->getPost();
        /*
         * code 1,2,3,4 first(last 10 new);second(new,old,all)
         */
        $code = $post->get("code");

        $where = new Where();

//        $this->logger->info(0,"worker getOrderListAction post argument count:".$request->getPost()->count());
        /** @var $workerOrderTable WorkerOrderTable */
        $workerOrderTable = $this->getServiceLocator()->get('Worker\Model\WorkerOrderTable');
        /** @var $orderTable OrderTable */
        $orderTable = $this->getServiceLocator()->get('Worker\Model\OrderTable');
        $where = new Where();
        $listData = array();
        switch($code){
            case 1://first
                $lastLimit = 10;
                $state = $post->get("state");
                $listData = $workerOrderTable->getLastOrderListByWorkerId($workerId,$state,$lastLimit);
//                $where->in('t_order.ID',$listData);
//                $orderList = $orderTable->getOrderListByOrderIdList($where);
                break;
            case 2://new : get last order
                $id = $post->get("id");
                $state = $post->get("state");
                if($id==null)
                    break;
                $limit = 10;
                $listData = $workerOrderTable->getOrderListGreaterThanID($workerId,$id,$state,$limit);
//                $where->in('t_order.ID',$listData);
//                $orderList = $orderTable->getOrderListByOrderIdList($where);
                break;
            case 3://old
                $id = $post->get("id");
                $state = $post->get("state");
                if($id==null)
                    break;
                $limit = 10;
                $listData = $workerOrderTable->getOrderListLessThanID($workerId,$id,$state,$limit);
//                $where->in('t_order.ID',$listData);
//                $orderList = $orderTable->getOrderListByOrderIdList($where);
                break;
            case 4://all
                $state = $post->get("state");
                $listData = $workerOrderTable->getAllOrderListByID($workerId,$state);
//                $where->in('t_order.ID',$listData);
//                $orderList = $orderTable->getOrderListByOrderIdList($where);
                break;
            default:
                //error
                break;
        }
        $response->setContent(Json::encode(array(
            "state"=>true,
            "code"=>0,
            "list"=>$listData

        )));
        return $response;
    }

    public function getOrderMessageAction(){
        /** @var $request Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        $rspData = array();
        do{
            if($request->isPost()){
                $data = $request->getPost();
                $orderIds = $data["orderIds"];
                $orderIds = [$orderIds];
                if($orderIds == null || !is_array($orderIds) || count($orderIds) < 1 ){
                    $code = 10001;
                    $message = "参数错误";
                    break;
                }
                $regex = new Regex();
                foreach($orderIds as $id){
                    $regex->checkId($id);
                }
                if($regex->getCode() != 0){
                    $code = $regex->getCode();
                    $message = $regex->getMessage();
                    break;
                }

                /** @var $authSvr AuthenticationService */
                $authSvr = $this->getServiceLocator()->get('Session');
                $identity = $authSvr->getIdentity();
                $workerId = $identity[SessionInfoKey::ID];

                foreach($orderIds as $id){
                    $legal = $this->getWorkerOrderTable()->select(array(
                        "workerid" => $workerId,
                        "orderid" => $id
                    ))->current();
                    if($legal){
                        $request->getPost()->set("id", $id);
                        $rsp = $this->forward()->dispatch('AdminMgr\Controller\Order', array(
                            'action' => 'view',
                            'innerCall' => true,
                        ));
                        $rspArr = Json::decode($rsp->getContent());
                        if($rspArr->state){
                            $rspData[$rspArr->order->ID] = $rspArr->orderMsg;
                        }
                    }
                }

            }
        }while(false);

        $response->setContent(Json::encode(array(
            "code" => $code,
            "message" => $message,
            "data" => $rspData
        )));

        return $response;
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

}