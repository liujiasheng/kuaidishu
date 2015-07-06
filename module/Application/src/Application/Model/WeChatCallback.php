<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-5
 * Time: 上午10:25
 */

namespace Application\Model;


class WeChatCallback {
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            return $echoStr;
        }
        return "";
    }

    public function responseMsg($ctrl)
    {
        //if globals not defined return ""
        if(!isset($GLOBALS["HTTP_RAW_POST_DATA"]))
            return "";

        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $type = $postObj->MsgType;
            $keyword = trim($postObj->Content);

            //订阅事件
            if( $type == "event" && $postObj->Event == "subscribe"){
                $rsl = WeChatCallbackConfig::getWelcomeMsg($postObj);
                return $rsl;
            }
            //点击菜单“click”事件
            if( $type == 'event' && $postObj->Event == "CLICK"){
                $eventKey = $postObj->EventKey;
                $rsl = "";
                switch($eventKey){
                    //优惠活动
                    case "YOUHUI": $rsl = WeChatCallbackConfig::getYouhuiNews($postObj); break;
                    //帐号绑定
                    case "BINDING": $rsl = WeChatCallbackConfig::getUserBindingMsg($ctrl, $postObj); break;
                    //帐号解除绑定
                    case "UNBINDING": $rsl = WeChatCallbackConfig::getUserUnBindingMsg($ctrl, $postObj); break;
                    //用户查看订单
                    case "CHECKORDERS" : $rsl = WeChatCallbackConfig::getCheckOrdersMsg($ctrl, $postObj); break;
                    //其他
                    default: $rsl = "";
                }
                return $rsl;
            }
            //回复文本事件
            if( $type == 'text' && !empty( $keyword ))
            {
                $rsl = "";
                switch($keyword){
                    case "绑定":
                    case "账号绑定":
                    case "用户绑定": $rsl = WeChatCallbackConfig::getUserBindingMsg($ctrl, $postObj); break;
                    case "员工绑定": $rsl = WeChatCallbackConfig::getWorkerBindingMsg($postObj); break;
                    default: $rsl = "";
                }
                if(preg_match("/^[0-9]{10}$/",$keyword)){
                    $rsl = WeChatCallbackConfig::getOrderStr($ctrl, $postObj, $keyword);
                }
                return $rsl;
            }

            //其他情况
            return "";

        }else {
            return "";
        }
    }

    public function checkSignature()
    {
        if( !isset($_GET["signature"]) || !isset($_GET["timestamp"]) ||
            !isset($_GET["nonce"]) ){
            return false;
        }
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = WeChatCallbackConfig::getToken();
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
} 