<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-12
 * Time: 下午10:08
 */

namespace Application\Model;


class OrderStateMsg {
//1:Wait Confirm
//2:Confirm and Appoint Worker
//3:Reject and Set Comment
//9:End Mission

    const WAITING_CONFIRM = '等待确认订单';
    const CONFIRM_AND_APPOINT_TO_WORKER = '已确认订单';
    const REJECT_AND_SET_COMMENT = '已拒绝订单';
    const END_MISSION = '已完成订单';

    const OTHER = '错误状态';

} 