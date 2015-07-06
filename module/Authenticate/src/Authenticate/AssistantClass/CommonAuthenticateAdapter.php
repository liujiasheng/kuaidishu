<?php
/**
 * Created by JetBrains PhpStorm.
 * User: james
 * Date: 14-7-1
 * Time: 上午10:44
 * To change this template use File | Settings | File Templates.
 */

namespace Authenticate\AssistantClass;

use Application\Model\AdminState;
use Application\Model\Identity;
use Application\Model\Logger;
use Application\Model\SellerState;
use Application\Model\UserState;
use Application\Model\WorkerState;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;



class CommonAuthenticateAdapter implements AdapterInterface{

    protected  $_table;
    protected $username;
    protected $password;
    protected $userType;
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $_serviceLocator;

    protected $logger;
    /**
     * Sets username and password for authentication
     *
     * @param $username
     * @param $password
     * @param $userType
     * @param $serviceLocator \Zend\ServiceManager\ServiceLocatorInterface
     * @return \Authenticate\AssistantClass\CommonAuthenticateAdapter
     */
    public function __construct($username, $password,$userType,$serviceLocator)
    {
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setUserType($userType);
        $this->setServiceLocator($serviceLocator);
    }
    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
       try{
           //TODO check the argument {username,password,userType}
           switch($this->getUserType()){
               case UserType::Admin:
                   $admin = $this->getAdminTable()->getAdminByName(
                       $this->getUsername()
                   );
                   if(!$admin){
                       $this->getLogger()->notice(0,"admin login fail");
                       return new Result(Result::FAILURE_IDENTITY_NOT_FOUND,null,array("message"=>"用户不存在"));
                   }
                   //TODO check user information
                    switch($admin->getState()){
                        case AdminState::Active:
//                            $md5 = (new PasswordEncrypt())->getPasswordMd5($this->getUsername(),$this->getPassword());
                            if(!(new PasswordEncrypt())->verifyPassword($this->getUsername(),$this->getPassword(),$admin->__get("Password"))){
                                $this->getLogger()->notice(0,"admin passwd wrong");
                                return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户密码错误"));
                            }
                            break;
                        case AdminState::Forbidden:
                            return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户被禁用"));
                            break;
                        default:
                            return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户状态错误"));
                            break;
                    }


//                    $md5 = (new PasswordEncrypt())->getPasswordMd5($this->getUsername(),$this->getPassword());

                   //TODO To store information into session after admin login

                   $this->getAdminTable()->update(array("LoginIP"=>$this->GetIP(),"LoginTime"=>(new \DateTime())->format("Y-m-d H:i:s")),array("id" => $admin->getId()));
                   $this->getLogger()->info(0,"admin login success,ID:".$admin->getId());
                   return new Result(Result::SUCCESS,array(
                       SessionInfoKey::role=>UserType::Admin,
                       SessionInfoKey::ID => $admin->getID()
                   ),array("message"=>"登陆成功"));
                   break;
               case UserType::User:
                   $user = $this->getUserTable()->getUserByUsername($this->getUsername());

                   //TODO check user information

                   if(!$user){
                       $this->getLogger()->notice(0," user login fail");
                       return new Result(Result::FAILURE_IDENTITY_NOT_FOUND,null,array("message"=>"用户不存在"));
                   }

                   switch($user->getState()){
                       case UserState::Active:
//                           $md5 = (new PasswordEncrypt())->getPasswordMd5($this->getUsername(),$this->getPassword());
                           if(!(new PasswordEncrypt())->verifyPassword($this->getUsername(),$this->getPassword(),$user->__get("Password"))){
                               $this->getLogger()->notice(0,"user passwd wrong");
                               return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户密码错误"));
                           }
                           break;
                       case UserState::Inactive:
                           return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户未激活"));
                           break;
                       case UserState::Forbidden:
                           return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户被禁用"));
                           break;
                       default:
                           return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户状态错误"));
                           break;
                   }


                   //TODO To store information into session after user login

                   //TODO to get the client ip
                   $this->getUserTable()->update(array("LoginIP"=>$this->GetIP(),"LoginTime"=>(new \DateTime())->format("Y-m-d H:i:s")),array("id" => $user->getID()));
                   $this->getLogger()->info(0,"user login success,ID:".$user->getID());
                   return new Result(Result::SUCCESS,array(
                       SessionInfoKey::role=>UserType::User,
                       SessionInfoKey::ID => $user->getID()
                   ),array("message"=>"登陆成功"));
                   break;
               case UserType::Worker:
                   $worker = $this->getWorkerTable()->getWorkerByUsername($this->getUsername());

                   //TODO check user information

                   if(!$worker){
                       $this->getLogger()->notice(0," worker login fail");
                       return new Result(Result::FAILURE_IDENTITY_NOT_FOUND,null,array("message"=>"用户不存在"));
                   }

                   switch($worker->getState()){
                       case WorkerState::Active:
//                           $md5 = (new PasswordEncrypt())->getPasswordMd5($this->getUsername(),$this->getPassword());

                           if(!(new PasswordEncrypt())->verifyPassword($this->getUsername(),$this->getPassword(),$worker->__get("Password"))){
                               $this->getLogger()->notice(0,"worker passwd wrong");
                               return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户密码错误"));
                           }
                           break;

                       case WorkerState::Dismission:
                           return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户已解约"));
                           break;
                       case WorkerState::Forbidden:
                           return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户被禁用"));
                           break;
                       default:
                           return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户状态错误"));
                           break;
                   }

                   //TODO To store information into session after worker login

                   //TODO to get the client ip
                   $this->getUserTable()->update(array("LoginIP"=>$this->GetIP(),"LoginTime"=>(new \DateTime())->format("Y-m-d H:i:s")),array("id" => $worker->getID()));
                   $this->getLogger()->info(0,"worker login success,ID:".$worker->getID());
                   return new Result(Result::SUCCESS,array(
                       SessionInfoKey::role=>UserType::Worker,
                       SessionInfoKey::ID => $worker->getID()
                   ),array("message"=>"登陆成功"));
                   break;
               case UserType::Seller:
                   $seller = $this->getSellerTable()->getSellerByUsername($this->getUsername());

                   //TODO check user information



                   if(!$seller){
                       $this->getLogger()->notice(0," seller login fail");
                       return new Result(Result::FAILURE_IDENTITY_NOT_FOUND,null,array("message"=>"用户不存在"));
                   }

                   switch($seller->getState()){
                       case SellerState::Active:
//                       $md5 = (new PasswordEncrypt())->getPasswordMd5($this->getUsername(),$this->getPassword());
                           if(!(new PasswordEncrypt())->verifyPassword($this->getUsername(),$this->getPassword(),$seller->__get("Password"))){
                               $this->getLogger()->notice(0," seller passwd wrong");
                               return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户密码错误"));
                           }
                           break;

                       case SellerState::Dismission:
                           return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户已解约"));
                           break;
                       case SellerState::Forbidden:
                           return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户被禁用"));
                           break;
                       default:
                           return new Result(Result::FAILURE_CREDENTIAL_INVALID,null,array("message"=>"用户状态错误"));
                           break;
                   }


                   //TODO To store information into session after user login

                   //TODO to get the client ip
                   $this->getUserTable()->update(array("LoginIP"=>$this->GetIP(),"LoginTime"=>(new \DateTime())->format("Y-m-d H:i:s")),array("id" => $seller->getId()));
                   $this->getLogger()->info(0,"seller login success,ID:".$seller->getID());
                   return new Result(Result::SUCCESS,array(
                       SessionInfoKey::role=>UserType::Seller,
                       SessionInfoKey::ID => $seller->getID()
                   ),array("message"=>"登陆成功"));
                   break;
               default:
                   return new Result(Result::FAILURE,null,array("message"=>"用户类型错误"));

           }


       }catch (\Exception $e){
        $this->getLogger()->err($e->getCode(),$e->getMessage());
    }

    }

    /**
     * @param mixed $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $userType
     * @return $this
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @param mixed $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return \Authenticate\Model\AdminTable
     */
    private function getAdminTable()
    {
        if (!$this->_table) {
            $sm = $this->_serviceLocator;
            $this->_table = $sm->get('Authenticate\Model\AdminTable');
        }
        return $this->_table;
    }

    /**
     * @return \Authenticate\Model\UserTable
     */
    private function getUserTable()
    {
        if (!$this->_table) {
            $sm = $this->_serviceLocator;
            $this->_table = $sm->get('Authenticate\Model\UserTable');
        }
        return $this->_table;
    }
    /**
     * @return \Authenticate\Model\WorkerTable
     */
    private function getWorkerTable()
    {
        if (!$this->_table) {
            $sm = $this->_serviceLocator;
            $this->_table = $sm->get('Authenticate\Model\WorkerTable');
        }
        return $this->_table;
    }
    /**
     * @return \Authenticate\Model\SellerTable
     */
    private function getSellerTable()
    {
        if (!$this->_table) {
            $sm = $this->_serviceLocator;
            $this->_table = $sm->get('Authenticate\Model\SellerTable');
        }
        return $this->_table;
    }

    /**
     * @param $serviceLocator
     * @return $this
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->_serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getServiceLocator()
    {
        return $this->_serviceLocator;
    }

    function GetIP(){
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return($ip);
    }

    private function getLogger()
    {
        if($this->logger==null){
            $this->logger = new Logger();
        }
        return $this->logger;
    }

}