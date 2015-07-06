<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-19
 * Time: 上午10:24
 */

namespace Seller\Model;


class SellerCommon {

    public function getSellerInfoMenu(){
        return array(
            '/seller/goodsMgr'=>"商品管理",
            '/seller/orderMgr'=>'订单管理'
        );
    }
} 