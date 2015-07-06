<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-14
 * Time: 下午8:39
 */

namespace Application\Model;


class CommonFunctions {

    static function getPricesFromStandards($standards){
        $max = 0;
        $min = 0;
        if( $standards && is_array($standards) && count($standards) > 0 ){
            $max = $min = $standards[0]["Price"];
            foreach($standards as $standard){
                $tmpPrice = $standard["Price"];
                if($tmpPrice > $max){
                    $max = $tmpPrice;
                }
                if($tmpPrice < $min){
                    $min = $tmpPrice;
                }
            }
        }else{

        }
        return array(
            "max" => $max,
            "min" => $min
        );
    }

    static $_startWorkTime = "21:00:00";
    static $_endWorkTime = "23:00:00";
    static function getExpectedDeliveredTime($orderTime){
        $times = array(
            "12:30:00",
            "17:45:00",
            "21:30:00",
            "22:30:00",
        );
        $orderTimePrev = explode(" ", $orderTime)[0];
        $orderTimeAfter = explode(" ", $orderTime)[1];
        $orderTimeStampToday = CommonFunctions::getTodaySeconds($orderTimeAfter);
        $loc = -1;
        for($i=0;$i<count($times);$i++){
            if( $orderTimeStampToday < CommonFunctions::getTodaySeconds($times[$i])){
                $loc = $i;
                break;
            }
        }
        $begTime = $date = $orderTimePrev . " 00:00:00";
        $begTimeStamp = strtotime($begTime);
        if($loc == -1){
            $begTimeStamp += 24*60*60;
            $loc = 0;
        }
        $expectedStamp = $begTimeStamp + CommonFunctions::getTodaySeconds($times[$loc]) + 45*60;
        $expectedTime = date("Y-m-d H:i:00", $expectedStamp);
        return $expectedTime;

//        $orderTimePrev = explode(" ", $orderTime)[0];
//        $orderTimeAfter = explode(" ", $orderTime)[1];
//        $startTimeStampToday = CommonFunctions::getTodaySeconds(CommonFunctions::$_startWorkTime);
//        $endTimeStampToday = CommonFunctions::getTodaySeconds(CommonFunctions::$_endWorkTime);
//        $orderTimeStampToday = CommonFunctions::getTodaySeconds($orderTimeAfter);
//
//        $orderTimeStamp = $expectedStamp = strtotime($orderTime);
//        //在工作时间内
//        if($orderTimeStampToday >= $startTimeStampToday && $orderTimeStampToday <= $endTimeStampToday ){
//            $expectedStamp = intval($orderTimeStamp) + 45*60;
//        }
//        //在工作时间开始前
//        else if($orderTimeStampToday < $startTimeStampToday){
//            $begTime = $date = $orderTimePrev . " ".CommonFunctions::$_startWorkTime;
//            $begTimeStamp = strtotime($begTime);
//            $expectedStamp = $begTimeStamp + 45*60;
//        }
//        //在工作时间后
//        else{
//            $begTime = $date = $orderTimePrev . " ".CommonFunctions::$_startWorkTime;
//            $begTimeStamp = strtotime($begTime);
//            $expectedStamp = $begTimeStamp + 24*60*60 + 45*60;
//        }
//
//        $expectedTime = date("Y-m-d H:i:00", $expectedStamp);
//        return $expectedTime;
    }

    static function getTodaySeconds($hmsStr){
        $timeArr = explode(":", $hmsStr);
        $seconds = intval($timeArr[0])*60*60 + intval($timeArr[1])*60 + intval($timeArr[2]);
        return $seconds;
    }

} 