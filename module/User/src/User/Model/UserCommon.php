<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-7-13
 * Time: 下午12:53
 */

namespace User\Model;


class UserCommon {

    public function getUserInfoMenu()
    {
        return array(
            '/user/userinfo'=>"个人信息",
            '/user/modifypassword'=>'修改密码',
            '/user/deliveryAddress'=>"收货地址",
            '/user/orderManage'=>'订单管理'
        );
    }
}