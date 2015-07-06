<?php
/**
 * Created by JetBrains PhpStorm.
 * User: james
 * Date: 14-7-5
 * Time: 下午8:32
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model;

//1:Active 9:Forbidden
class AdminState {
    //账号的状态
    const Active = "1";
    const Inactive = "2";
    const Forbidden = "9";
}