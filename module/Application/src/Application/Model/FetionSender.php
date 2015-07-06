<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-21
 * Time: 下午2:55
 */

namespace Application\Model;


use Zend\Json\Json;

class FetionSender {

    protected static $FetionUsername = "15913139608";
    protected static $FetionPassword = "Pyn3UQBY";

    protected static function getRequestUrl($phone, $content){
        $str = sprintf("http://quanapi.sinaapp.com/fetion.php?u=%s&p=%s&to=%s&m=%s",
            FetionSender::$FetionUsername, FetionSender::$FetionPassword, $phone, urlencode($content) );
        return $str;
    }

    public static function sendMessage($phone, $content){
        $flag = true;
        $message = "";

//        $url = FetionSender::getRequestUrl($phone, $content);
//        $ret = file_get_contents($url);
//        $retArr = Json::decode($ret);
//        if($retArr->result != 0){
//            $flag = false;
//            $message = $retArr->message;
//        }

        return array(
            "state" => $flag,
            "message" => $message
        );
    }



} 