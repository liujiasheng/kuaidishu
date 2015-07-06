<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Worker for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Worker\Controller;

use Application\Model\Logger;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Exception;

class WorkerController extends AbstractActionController
{
    protected $_workerTable;

    public function indexAction()
    {
        $vm = null;
        try{
            $basePath = $this->getRequest()->getBasePath();
            $cf = new \AdminBase\Model\AdminMenu();
            $menu = $cf->getMenu('员工管理', $basePath);
            $arr = array(
                "workerTable" => $this->getWorkerTable()->fetchSlice(1,10,null),
                "count" => $this->getWorkerTable()->fetchCount(),
                "menu" => $menu,
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
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    public function searchAction(){
        $request = $this->getRequest();
        $response = $this->getResponse();
        try{
            if($request->isPost()){
                $data = $request->getPost();
                $state = $data["state"];
                $text = $data["searchText"];
                $where = array();
                if($state != "0")
                    $where[] = "state = ".$state;
                if($text!="")
                    $where[] = "(username like '%".$text."%' or name like '%".$text."%')";
                //check text
                if($text!=""){
                    $regex = new \Application\Model\Regex();
                    $regex->flush()->checkSearchText($text);
                    if($regex->getCode() != 0){
                        $response->setContent(\Zend\Json\Json::encode(array("state" => false, "code" => $regex->getCode(), "message"=>$regex->getMessage())));
                        return $response;
                    }
                }
                $slice = $this->getWorkerTable()->fetchSlice((int)$data["curPage"], (int)$data["pageCount"], $where);
                $all = $this->getWorkerTable()->fetchCount($where);
                $cnt = count($slice);
                $workers = array();
                for($k=0; $k<$cnt; $k++){
                    $workers[$k]["id"] = (int)($slice[$k]->getID());
                    $workers[$k]["username"] = $slice[$k]->getUsername();
                    $workers[$k]["name"] = $slice[$k]->getName();
                    $workers[$k]["sex"] = $slice[$k]->getSex();
                    $workers[$k]["certNumber"] = $slice[$k]->getCertNumber();
                    $workers[$k]["phone"] = $slice[$k]->getPhone();
                    $workers[$k]["state"] = (int)($slice[$k]->getState());
                }
                if($cnt == 0)
                    $response->setContent(\Zend\Json\Json::encode(array("state" => true, "count" => 0)));
                else
                    $response->setContent(\Zend\Json\Json::encode(array("state" => true, "count" => $all, "workerTable" => $workers), true));
            }
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $response;
    }

    public function addAction(){
        $request = $this->getRequest();
        $response = $this->getResponse();
        $message = "";
        $code = 0;
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $worker = new \Application\Entity\Worker();
                    $worker->setOptions(array(
                        "ID" => 0,
                        "Username" => $data["username"],
                        "Password" => $data["password"],
                        "Name" => $data["name"],
                        "Sex" => $data["sex"],
                        "CertNumber" => $data["certNumber"],
                        "Phone" => $data["phone"],
                        "SelfPhone" => $data["selfPhone"]
                    ));
                    //check worker
                    $regex = new \Application\Model\Regex();
                    $regex->flush()
                        ->checkWorkerUsername($worker->getUsername())
                        ->checkPassword($worker->getPassword())
                        ->checkRealName($worker->getName())
                        ->checkSex($worker->getSex())
                        ->checkCertNumber($worker->getCertNumber())
                        ->checkPhone($worker->getPhone());
                    if($worker->getSelfPhone()!="") $regex->checkPhone($worker->getSelfPhone());
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //check username exist
                    $exist = $this->getWorkerTable()->select(array("username"=>$worker->getUsername()))->current();
                    if($exist){
                        $code = 101;
                        $message = "工号已存在";
                        break;
                    }
                    //insert
                    $workerId = $this->getWorkerTable()->saveWorker($worker);
                    if(!$workerId){
                        $code = 100;
                        $message = "添加员工失败";
                        break;
                    }else{
                        $response->setContent(\Zend\Json\Json::encode(array("state" => true, "id" => $workerId)));
                    }
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(\Zend\Json\Json::encode(array("state" => false, "code" => $code, "message" => $message)));
        }
        return $response;
    }

    public function viewAction(){
        $request = $this->getRequest();
        $response = $this->getResponse();
        try{
            if($request->isPost()){
                $data = $request->getPost();
                $wid = $data["id"];
                if(!$worker = $this->getWorkerTable()->getWorker($wid)){
                    $response->setContent(\Zend\Json\Json::encode(array("state" => false)));
                }else{
                    $response->setContent(\Zend\Json\Json::encode(array(
                        "state" => true,
                        "worker" => array(
                            "id" => $worker->getID(),
                            "username" => $worker->getUsername(),
                            "name" => $worker->getName(),
                            "certNumber" => $worker->getCertNumber(),
                            "sex" => $worker->getSex(),
                            "phone" => $worker->getPhone(),
                            "selfPhone" => $worker->getSelfPhone(),
                            "loginIP" => $worker->getLoginIP(),
                            "loginTime" => $worker->getLoginTime(),
                            "state" => $worker->getState()
                        ),
                    )));
                }
            }
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $response;
    }

    public function editAction(){
        $request = $this->getRequest();
        $response = $this->getResponse();
        $message = "";
        $code = 0;
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $worker = new \Application\Entity\Worker();
                    $worker->setOptions(array(
                        "ID" => $data["id"],
                        "Username" => $data["username"],
                        "Password" => $data["password"],
                        "Name" => $data["name"],
                        "Sex" => $data["sex"],
                        "CertNumber" => $data["certNumber"],
                        "Phone" => $data["phone"],
                        "SelfPhone" => $data["selfPhone"],
                        "State" => $data["state"]
                    ));
                    //check worker
                    $regex = new \Application\Model\Regex();
                    $regex->flush()
                        ->checkRealName($worker->getName())
                        ->checkSex($worker->getSex())
                        ->checkCertNumber($worker->getCertNumber())
                        ->checkPhone($worker->getPhone())
                        ->checkState($worker->getState());
                    if($worker->getPassword()!="") $regex->checkPassword($worker->getPassword());
                    if($worker->getSelfPhone()!="") $regex->checkPhone($worker->getSelfPhone());
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //update
                    if(!$wid = $this->getWorkerTable()->saveWorker($worker)){
                        $code = 101;
                        $message = "数据没有更新";
                        break;
                    }
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(\Zend\Json\Json::encode(array("state"=>false, "code"=>$code, "message"=>$message)));
        }else{
            $response->setContent(\Zend\Json\Json::encode(array("state"=>true)));
        }
        return $response;
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
    public function fooAction()
    {
        // This shows the :controller and :action parameters in default route
        // are working when you browse to /worker/worker/foo
        return array();
    }
}
