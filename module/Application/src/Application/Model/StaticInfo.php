<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-8
 * Time: 下午8:06
 */

namespace Application\Model;


class StaticInfo {

    static function getSellerLogoDir(){
//        return "E:/WebRoot/kuaidishu/public/storage/sellerlogo/";
        return "/var/www/kuaidishu/public/storage/sellerlogo/";
    }

    static function getGoodsImageDir(){
//        return "E:/WebRoot/kuaidishu/public/storage/goodsimg/";
        return "/var/www/kuaidishu/public/storage/goodsimg/";
    }

    static function getGoodsSmallImageDir(){
        return StaticInfo::getGoodsImageDir().'small/';
    }

    static function getGoodsMediumImageDir(){
        return StaticInfo::getGoodsImageDir().'medium/';
    }

    static function getGoodsLargeImageDir(){
        return StaticInfo::getGoodsImageDir().'large/';
    }

    static function getGoodsTinyImageDir(){
        return StaticInfo::getGoodsImageDir().'tiny/';
    }

    static function getImageAddress(){
        return "http://static.kuaidishu.com/";
    }
    static function getSellerLogoAddress(){
        return StaticInfo::getImageAddress()."sellerlogo/";
    }
    static function getGoodsLargeAddress(){
        return StaticInfo::getImageAddress()."goodsimg/large/";
    }
    static function getGoodsMediumAddress(){
        return StaticInfo::getImageAddress()."goodsimg/medium/";
    }
    static function getGoodsSmallAddress(){
        return StaticInfo::getImageAddress()."goodsimg/small/";
    }
    static function getGoodsTinyAddress(){
        return StaticInfo::getImageAddress()."goodsimg/tiny/";
    }


} 