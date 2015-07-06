<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-22
 * Time: 下午4:58
 */

namespace Application\Model;


class WeChatCallbackMsgMaker {

    public static function getTextMsg($postObj, $contentStr){
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $time = time();
        $msgType = "text";
        $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
        return $resultStr;
    }

    public static function getArticleItem($title, $description, $picUrl, $url){
        $textTpl = '<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
            </item>';
        $resStr = sprintf($textTpl, $title, $description, $picUrl, $url);
        return $resStr;
    }

    public static function getArticlesMsg($postObj, $itemArr){
        $textTpl = '<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>%d</ArticleCount>
            <Articles>
            %s
            </Articles>
            </xml> ';
        $itemsStr = "";
        foreach($itemArr as $item){
            $itemsStr .= WeChatCallbackMsgMaker::getArticleItem($item["title"], $item["description"], $item["picUrl"], $item["url"]);
        }
        $resStr = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), count($itemArr), $itemsStr);

        return $resStr;
    }

} 