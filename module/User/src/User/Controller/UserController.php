<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/User for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace User\Controller;

use AdminBase\Model\AdminMenu;
use Application\Model\Intercepter;
use Application\Model\Logger;
use Authenticate\AssistantClass\UserType;
use Zend\Http\PhpEnvironment\RemoteAddress;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Exception;

class UserController extends AbstractActionController
{
	protected  $_userTable;

    public function indexAction()
    {
        $vm = null;
        try{
            $basePath = $this->getRequest()->getBasePath();
            $cf = new AdminMenu();
            $menu = $cf->getMenu('用户管理', $basePath);
            $arr = array(
                "userTable" => $this->getUserTable()->fetchSlice(1,10,null),
                "count" => $this->getUserTable()->fetchCount(),
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
                    $where[] = "username like '%".$text."%'";
                //check text
                if($text!=""){
                    $regex = new \Application\Model\Regex();
                    $regex->flush()->checkSearchText($text);
                    if($regex->getCode() != 0){
                        $response->setContent(\Zend\Json\Json::encode(array("state" => false, "code" => $regex->getCode(), "message"=>$regex->getMessage())));
                        return $response;
                    }
                }
                $slice = $this->getUserTable()->fetchSlice((int)$data["curPage"], (int)$data["pageCount"], $where);
                $all = $this->getUserTable()->fetchCount($where);
                $cnt = count($slice);
                $users = array();
                for($k=0; $k<$cnt; $k++){
                    $users[$k]["id"] = (int)($slice[$k]->getId());
                    $users[$k]["username"] = $slice[$k]->getUsername();
                    $users[$k]["nickname"] = $slice[$k]->getNickname();
                    $users[$k]["email"] = $slice[$k]->getEmail();
                    $users[$k]["registerTime"] = $slice[$k]->getRegisterTime();
                    $users[$k]["state"] = (int)($slice[$k]->getState());
                }
                if($cnt == 0)
                    $response->setContent(\Zend\Json\Json::encode(array("state" => true, "count" => 0)));
                else
                    $response->setContent(\Zend\Json\Json::encode(array("state" => true, "count" => $all, "userTable" => $users), true));
            }
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $response;
    }

    public function addAction() {
        $request = $this->getRequest();
        $response = $this->getResponse();
        $message = "";
        $code = 0;
        try{
            do{
                 if($request->isPost()){
                      $data = $request->getPost();
                     $user = new \Application\Entity\User();
                     $user->setId(0)
                         ->setUsername($data["username"])
                         ->setPassword($data["password"])
                         ->setEmail($data["email"]);
                     //check user
                     $regex = new \Application\Model\Regex();
                     $regex->flush()
                         ->checkUsername($user->getUsername())
                         ->checkPassword($user->getPassword())
                         ->checkEmail($user->getEmail());
                     if($regex->getCode()!=0){
                         $code = $regex->getCode();
                         $message = $regex->getMessage();
                         break;
                     }
                     //check username exist
                     $exist = $this->getUserTable()->select(array("username" => $user->getUsername()))->current();
                     if($exist){
                         $code = 101;
                         $message = "用户名已存在";
                         break;
                     }
                     //insert
                     $userId = $this->getUserTable()->saveUser($user);
                     if(!$userId){
                         $response->setContent(\Zend\Json\Json::encode(array("state" => false, "code" => 0 , "message" => "")));
                     }else{
                         $response->setContent(\Zend\Json\Json::encode(array("state" => true, "id" => $userId,
                             "regTime" => $this->getUserTable()->getUser($userId)->getRegisterTime())));
                     }
                 }
            }while(false);
            if($code!=0){
                $response->setContent(\Zend\Json\Json::encode(array("state" => false, "code" => $code, "message" => $message)));
            }
        }catch(Exception $e){
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
                    $user = new \Application\Entity\User();
                    $user->setId($data["id"])
                        ->setNickname($data["nickname"])
                        ->setPassword($data["password"])
                        ->setEmail($data["email"])
                        ->setPhone($data["phone"])
                        ->setState($data["state"]);
                    //check user
                    $regex = new \Application\Model\Regex();
                    $regex->flush()
                        ->checkNickname($user->getNickname())
                        ->checkEmail($user->getEmail())
                        ->checkPhone($user->getPhone())
                        ->checkState($user->getState());
                    if($user->getPassword()!="") $regex->checkPassword($user->getPassword());
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //update
                    if(!$uid = $this->getUserTable()->saveUser($user)){
                        $response->setContent(\Zend\Json\Json::encode(array("state" => false, "code"=>5, "message" => "数据没有更新")));
                    }else{
                        $response->setContent(\Zend\Json\Json::encode(array("state" => true)));
                    }
                }
            }while(false);
            if($code!=0){
                $response->setContent(\Zend\Json\Json::encode(array("state" => false, "code"=>$code, "message" => $message)));
            }
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $response;
    }

    public function  viewAction() {
        $request = $this->getRequest();
        $response = $this->getResponse();
        try{
            if($request->isPost()){
                $data = $request->getPost();
                $userId = $data["id"];
                if(!$user = $this->getUserTable()->getUser($userId)){
                    $response->setContent(\Zend\Json\Json::encode(array("state" => false)));
                }else{
                    $response->setContent(\Zend\Json\Json::encode(array(
                        "state" => true,
                        "user" => array(
                            "id" => $user->getId(),
                            "username" => $user->getUsername(),
                            "nickname" => $user->getNickname(),
                            "email" => $user->getEmail(),
                            "phone" => $user->getPhone(),
                            "registerTime" => $user->getRegisterTime(),
                            "loginTime" => $user->getLoginTime(),
                            "loginIP" => $user->getLoginIP(),
                            "state" => $user->getState(),
                        )
                    )));
                }
            }
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $response;
    }

    
    protected function getUserTable() {
    	if(!$this->_userTable) {
    		$t = $this->getServiceLocator();
    		$this->_userTable = $t->get('User\Model\UserTable');
    	}
    	return $this->_userTable;
    }

}
