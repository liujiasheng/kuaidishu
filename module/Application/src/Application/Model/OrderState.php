<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-12
 * Time: 下午10:03
 */

namespace Application\Model;


class OrderState {
//1:Wait Confirm
//2:Confirm and Appoint Worker
//3:Reject and Set Comment
//9:End Mission

    const WAITING_CONFIRM = '1';
    const CONFIRM_AND_APPOINT_TO_WORKER = '2';
    const REJECT_AND_SET_COMMENT = '3';
    const END_MISSION = '9';


}