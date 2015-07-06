<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-7-4
 * Time: 下午10:26
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model;

class RegexClass
{
    public static function getRegexStringArray(){
        return array(
            "username" => "/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{6,16}$/u",
            "nickname" => "/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{2,16}$/u",
            "password" => "/^[a-zA-Z0-9_]{32}$/",
            "email"    => "/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/",
            "phone"    => "/^[0-9]{6,16}$/",
            "searchText"=> "/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{0,32}$/u",
            "workerUsername" => "/^[a-zA-Z0-9]{6,16}$/",
            "realName" => "/^[\x{4e00}-\x{9fa5}]{2,8}$/u",
            "certNumber" => "/^[0-9]{6,18}$/",
            "address" => "/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{6,32}$/u",
            "sellerName" => "/^[a-zA-Z0-9_\x{0000}-\x{ffff}]{2,32}$/u",
            "sellerComment" => "/^[a-zA-Z0-9_\x{4e00}-\x{9fa5}\x{0000}-\x{ffff}]{1,120}$/u",
            "className" => "/^[a-zA-Z0-9\/\x{4e00}-\x{9fa5}]{1,12}$/u",                   //1-12 字母、数字、中文  /
            "goodsName" => "/^[a-zA-Z0-9()_\x{0000}-\x{ffff}]{1,32}$/u",
            "goodsUnit" => "/^[\x{4e00}-\x{9fa5}]{1,4}$/u",                               //1-4 汉字
            "locationAddress"=>"/^[a-zA-Z0-9- _\x{4e00}-\x{9fa5}]{2,20}$/u",
            "standardName" => "/^[a-zA-Z0-9\x{0000}-\x{ffff}]{1,12}$/u",                //1-12 字母、数字、中文
            "orderRemark" => "/^[a-zA-Z0-9 ()!,.~;:\x{4e00}-\x{9fa5}\x{0000}-\x{ffff}]{0,1000}$/u",
        );
    }
}
