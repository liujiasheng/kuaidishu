<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-16
 * Time: 下午12:54
 */

namespace Application\Model;


class AliOssConfig {

    // oss bucket
    static public function getBucket(){
        return "kuaidishu-oss";
    }

    static public function getSellerLogoDir(){
        return 'sellerlogo/';
    }

    static public function getGoodsImageDir(){
        return 'goodsimg/';
    }

    static public function getGoodsSmallImageDir(){
        return AliOssConfig::getGoodsImageDir().'small/';
    }

    static public function getGoodsMediumImageDir(){
        return AliOssConfig::getGoodsImageDir().'medium/';
    }

    static public function getGoodsLargeImageDir(){
        return AliOssConfig::getGoodsImageDir().'large/';
    }

    static public function getGoodsTinyImageDir(){
        return AliOssConfig::getGoodsImageDir().'tiny/';
    }

} 