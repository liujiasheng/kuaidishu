<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-31
 * Time: 下午2:33
 */

namespace AdminMgr\Controller;

use Zend\Json\Json;
use Zend\Log\Logger;
use Application\Model\Regex;
use Zend\Mvc\Controller\AbstractActionController;

class PhoneListController extends AbstractActionController{

    protected $_phoneBlackListTable;
    protected $_phoneWhiteListTable;

    public function indexAction(){
        return $this->getResponse();
    }

    public function getPhoneStateAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        $phoneState = PhoneState::Unknown;
        $phoneMsg = PhoneState::UnknownStr;
        try {
            do {
                if ($request->isPost()) {
                    $phone = $request->getPost("phone");
                    if($phone == null){
                        $code = 10001;
                        $message = "参数不全";
                        break;
                    }

                    $regex = new Regex();
                    $regex->checkPhone($phone);
                    if($regex->getCode() != 0 ){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //end check

                    $inWhite = $this->getPhoneWhiteListTable()->select(array(
                        "phone" => $phone
                    ))->current();
                    if($inWhite){
                        //if in white list
                        $phoneState = PhoneState::Whited;
                        $phoneMsg = PhoneState::WhitedStr;
                        break;
                    }

                    $inBlack = $this->getPhoneBlackListTable()->select(array(
                        "phone" => $phone
                    ))->current();
                    if($inBlack){
                        //if in black list
                        $phoneState = PhoneState::Blacked;
                        $phoneMsg = PhoneState::BlackedStr;
                        break;
                    }

                }
            }while(false);
        }catch (\Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), "PhoneListController ".$e->getMessage());
            $code = -1;
        }
        if ($code != 0) {
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }else{
            $response->setContent(Json::encode(array(
                "state" => true,
                "phoneState" => $phoneState,
                "phoneStr" => $phoneMsg,
            )));
        }
        return $response;
    }

    /**
     * @return \AdminMgr\Model\PhoneBlackListTable
     */
    public function  getPhoneBlackListTable(){
        if(!$this->_phoneBlackListTable){
            $t = $this->getServiceLocator();
            $this->_phoneBlackListTable = $t->get('AdminMgr\Model\PhoneBlackListTable');
        }
        return $this->_phoneBlackListTable;
    }

    /**
     * @return \AdminMgr\Model\PhoneWhiteListTable
     */
    public function  getPhoneWhiteListTable(){
        if(!$this->_phoneWhiteListTable){
            $t = $this->getServiceLocator();
            $this->_phoneWhiteListTable = $t->get('AdminMgr\Model\PhoneWhiteListTable');
        }
        return $this->_phoneWhiteListTable;
    }

}

class PhoneState{
    const Unknown = 0;
    const Whited = 1;
    const Blacked = -1;

    const UnknownStr = "未知号码";
    const WhitedStr = "白名单号码";
    const BlackedStr = "黑名单号码";
}