<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-5
 * Time: 上午11:01
 */

namespace Application\Model;


use Zend\Db\Sql\Where;

class WeChatCallbackConfig{
    //weixin Token, used to check if the msg sent from wechat server
    static function getToken(){
        return 'b1e9ce5e3a97b02da706ccb739d0e2a7';
    }

    //welcome msg, shown to new subscriber, when subscribed
    static function getWelcomeMsg($postObj){
        $contentStr = "哈喽，欢迎来到快递鼠。我是鼠小萌。在这里你可以选购大学周边商家的任何商品哦，快递鼠会在45分钟内送到贵府上,而且是免费的哦嘘。目前我们开通了晚上9点到11点的配送，9点前订购的商品我们会在9点统一送出，9点到11点订购的商品，45分钟内送到。之后会开展全天配送以及外卖45分钟免费送上宿舍服务。希望大家支持鼠小萌哦。http://www.kuaidishu.com/";
        return WeChatCallbackMsgMaker::getTextMsg($postObj, $contentStr);
    }

    //菜单->优惠活动 返回图文
    static function getYouhuiNews($postObj){
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $textTpl = "
            <xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[%s]]></MsgType>
            <ArticleCount>1</ArticleCount>
            <Articles>
            <item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
            </item>
            </Articles>
            </xml> ";
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, "news",
            "一大波优惠活动",
            "亚马逊不仅关注您买了多少",
            "http://mmbiz.qpic.cn/mmbiz/yLxiafnF6cSQBedv4FqRwHnHQgPDYQ9vf9Hd7D1QvZs8GibswJl4MU3z3DdNuzsHmAyNniaNWiaprSY70XuPso0CXg/0",
            "http://mp.weixin.qq.com/s?__biz=MzA5Mzg4NTkyNw==&mid=200318414&idx=1&sn=da2c6fee7e654efd88e509d2afc80ff8#rd");
        return $resultStr;
    }

    //员工绑定
    static function getWorkerBindingMsg($postObj){
        $fromUsername = $postObj->FromUserName;
        $contentStr = "我知道了你是员工，还没开发好嘛!http://www.kuaidishu.com/?oi=". $fromUsername;
        return WeChatCallbackMsgMaker::getTextMsg($postObj, $contentStr);
    }

    //用户绑定
    /**
     * @param $ctrl \Home\Controller\WeixinController
     */
    static function getUserBindingMsg($ctrl, $postObj){
        $fromUsername = $postObj->FromUserName;
        $url = "http://www.kuaidishu.com/weixin/weixin/userbinding?oi=". $fromUsername;
        //check has binded
        if($ctrl->getWeixinUserTable()->isOpenIDExist($fromUsername, 1)){
            $contentStr = "此微信号已经绑定了一个快递鼠帐号!";
        }else{
            $contentStr = "微信号与快递鼠帐号绑定后，可以在微信追踪订单。".'<a href="'.$url.'">点击绑定</a>';
        }
        return WeChatCallbackMsgMaker::getTextMsg($postObj, $contentStr);
    }

    //用户解除绑定
    /**
     * @param $ctrl \Home\Controller\WeixinController
     */
    static function getUserUnBindingMsg($ctrl, $postObj){
        $fromUsername = $postObj->FromUserName;
        $url = "http://www.kuaidishu.com/weixin/weixin/userunbinding?oi=". $fromUsername;
        //check has binded
        if($ctrl->getWeixinUserTable()->isOpenIDExist($fromUsername, 1)){
            $contentStr = '<a href="'.$url.'">点击解除绑定</a>';
        }else{
            $contentStr = "此微信号还没绑定快递鼠帐号!";
        }
        return WeChatCallbackMsgMaker::getTextMsg($postObj, $contentStr);
    }

    //用户查看订单
    /**
     * @param $ctrl \Home\Controller\WeixinController
     */
    static function getCheckOrdersMsg($ctrl, $postObj){
        $fromUsername = $postObj->FromUserName;
        $contentStr = "";
        if($ctrl->getWeixinUserTable()->isOpenIDExist($fromUsername, 1)){
//            $strArr = WeChatCallbackConfig::getOrdersStrArr($ctrl, $fromUsername);
            $strArr = WeChatCallbackConfig::getOrdersIDsArr($ctrl, $fromUsername);
            if(!$strArr || !is_array($strArr) || count($strArr)< 1 ){
                return "";
            }else{
                $contentStr = "您最近7天的订单如下，您可以回复订单号查询订单详情。\n";
                foreach($strArr as $str){
//                    $contentStr .= $str."\n--------------\n";
                    $contentStr .= $str ."\n";
                }
            }
        }else{
            $url = "http://www.kuaidishu.com/weixin/weixin/userbinding?oi=". $fromUsername;
            $contentStr = "您还没有绑定快递鼠帐号，请先绑定：".'<a href="'.$url.'">点击绑定</a>';
        }

        return WeChatCallbackMsgMaker::getTextMsg($postObj, $contentStr);
    }

    /**
     * @param $ctrl \Home\Controller\WeixinController
     */
    static function getOrdersIDsArr($ctrl, $openid){
        $id = $ctrl->getWeixinUserTable()->getUserIDByOpenID($openid);
        $where = new Where();
        $where->equalTo("t_order.userid", $id, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $beforeTime = new \DateTime();
        date_timestamp_set($beforeTime, time() - 7*24*60*60);
        $where->greaterThan("t_order.ordertime", $beforeTime->format("Y-m-d H:i:s"), Where::TYPE_IDENTIFIER, Where::TYPE_VALUE );
        $orders = $ctrl->getOrderTable()->fetchSlice(1, 100, $where);
        if( !$orders || !is_array($orders) || count($orders)<1 ){
            return false;
        }
        $idsArr = array();
        foreach($orders as $order){
            $idsArr[] = $order->getID();
        }
        return $idsArr;
    }

    /**
     * @param $ctrl \Home\Controller\WeixinController
     */
    static function getOrdersStrArr($ctrl, $openid){
        $id = $ctrl->getWeixinUserTable()->getUserIDByOpenID($openid);
        $where = new Where();
        $where->equalTo("t_order.userid", $id, Where::TYPE_IDENTIFIER, Where::TYPE_VALUE);
        $beforeTime = new \DateTime();
        date_timestamp_set($beforeTime, time() - 7*24*60*60);
        $where->greaterThan("t_order.ordertime", $beforeTime->format("Y-m-d H:i:s"), Where::TYPE_IDENTIFIER, Where::TYPE_VALUE );
        $orders = $ctrl->getOrderTable()->fetchSlice(1, 10, $where);
        if( !$orders || !is_array($orders) || count($orders)<1 ){
            return false;
        }
        $orderDetails = $ctrl->getOrderDetailTable()->getOrderDetailByOrderIDs(array_keys($orders));
        $pattern = '订单号:%s
订单状态:%s
下单时间:%s
总额:%s元

订单内容:%s
备注:%s
收货人:%s
地址:%s %s %s %s。';
        $strArr = array();
        foreach($orders as $orderKey => $order){
            $details = array_pop($orderDetails[$orderKey]);
            $orderContent = '['.reset($details)["SellerName"].']';
            foreach($details as $detail){
                $orderContent .= " ".$detail["Name"]."*".$detail["Count"].";";
            }
            $strArr[] = sprintf($pattern, $id, WeChatCallbackConfig::getOrderStateStr($order), $order->getOrderTime(),
                $order->getTotal(), $orderContent, $order->getRemark(), $order->getName(),
                $order->getDomain(), $order->getDomain2(), $order->getDomain3(), $order->getAddress());
        }
        return $strArr;
    }

    /**
     * @param $ctrl \Home\Controller\WeixinController
     */
    static function getOrderStr($ctrl, $postObj, $orderid){
        $id = $ctrl->getWeixinUserTable()->getUserIDByOpenID($postObj->FromUserName);
        $order = $ctrl->getOrderTable()->getOrder(array(
            "userid" => $id,
            "id" => $orderid,
        ));
        if(!$order){
            return "";
        }
        $detail = $ctrl->getOrderDetailTable()->getOrderDetailByOrderIDs([$orderid]);
        $details = array_pop(array_pop($detail));
        $orderContent = "[".reset($details)["SellerName"]."]";
        foreach($details as $detail){
            $orderContent .= " ".$detail["Name"]."*".$detail["Count"].";";
        }
        $pattern = '订单号:%s
订单状态:%s
下单时间:%s
总额:%s元

订单内容:%s
备注:%s
收货人:%s
地址:%s %s %s %s。

*查询数据仅供参考，详细内容以快递鼠网站的内容为准。';
        $str = sprintf($pattern, $orderid, WeChatCallbackConfig::getOrderStateStr($order), $order->getOrderTime(),
            $order->getTotal(), $orderContent, $order->getRemark(), $order->getName(),
            $order->getDomain(), $order->getDomain2(), $order->getDomain3(), $order->getAddress());
        $resStr = WeChatCallbackMsgMaker::getArticlesMsg($postObj, array(array(
            "title" => "订单查询结果",
            "description" => $str,
            "picUrl" => "",
            "url" => "",
        )));
        return $resStr;
    }

    static function getOrderStateStr($order){
        $stateStr = "";
        switch($order->getState()){
            case "1": $stateStr = "待确认";break;
            case "2": $stateStr = "已确认";break;
            case "3": $stateStr = "已退回\n退回原因:".$order->getComment();break;
            case "9": $stateStr = "已完成";break;
        }
        return $stateStr;
    }

} 