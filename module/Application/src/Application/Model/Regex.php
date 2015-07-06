<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-7-5
 * Time: 上午9:46
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model;

class Regex
{
    protected $_regexArray;
    protected $_code;
    protected $_message;

    public function __construct(){
        $this->_code = 0;
        $this->_message = "";
        $this->_regexArray = (new RegexClass())->getRegexStringArray();
    }

    public function flush(){
        $this->_code = 0;
        $this->_message = "";
        return $this;
    }

    public function checkUsername($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["username"], $value)){
                $this->_code = 1;
                $this->_message = "用户名格式错误";
            }
        }
        return $this;
    }

    public function checkPassword($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["password"], $value)){
                $this->_code = 2;
                $this->_message = "密码格式错误";
            }
        }
        return $this;
    }

    public function checkEmail($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["email"], $value)){
                $this->_code = 3;
                $this->_message = "Email格式错误";
            }
        }
        return $this;
    }

    public function checkPhone($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["phone"], $value)){
                $this->_code = 4;
                $this->_message = "联系电话格式错误";
            }
        }
        return $this;
    }

    public function checkSearchText($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["searchText"], $value)){
                $this->_code = 5;
                $this->_message = "搜索信息格式错误";
            }
        }
        return $this;
    }

    public function checkNickname($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["nickname"], $value)){
                $this->_code = 6;
                $this->_message = "昵称格式错误";
            }
        }
        return $this;
    }

    public function checkWorkerUsername($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["workerUsername"], $value)){
                $this->_code = 7;
                $this->_message = "工号格式错误";
            }
        }
        return $this;
    }

    public function checkRealName($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["realName"], $value)){
                $this->_code = 8;
                $this->_message = "姓名格式错误";
            }
        }
        return $this;
    }

    public function checkCertNumber($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["certNumber"], $value)){
                $this->_code = 9;
                $this->_message = "证件号格式错误";
            }
        }
        return $this;
    }

    public function checkSex($value){
        if($this->_code == 0){
            if( $value!="男" && $value!="女" ){
                $this->_code = 10;
                $this->_message = "性别格式错误";
            }
        }
        return $this;
    }

    public function checkState($value){
        if($this->_code == 0){
            if( !is_numeric($value) ){
                $this->_code = 11;
                $this->_message = "状态格式错误";
            }
        }
        return $this;
    }

    public function checkAddress($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["address"], $value)){
                $this->_code = 12;
                $this->_message = "地址格式错误";
            }
        }
        return $this;
    }

    public function checkSellerName($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["sellerName"], $value)){
                $this->_code = 13;
                $this->_message = "商家名称格式错误";
            }
        }
        return $this;
    }

    public function checkClassName($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["className"], $value)){
                $this->_code = 14;
                $this->_message = "分类名格式错误";
            }
        }
        return $this;
    }

    public function checkId($value){
        if($this->_code == 0){
            if(!is_numeric($value)){
                $this->_code = 15;
                $this->_message = "ID格式错误";
            }
        }
        return $this;
    }

    public function checkGoodsName($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["goodsName"], $value)){
                $this->_code = 16;
                $this->_message = "商品名格式错误";
            }
        }
        return $this;
    }

    public function checkPrice($value){
        if($this->_code == 0){
            if(!is_float($value) && !is_numeric($value)){
                $this->_code = 17;
                $this->_message = "商品价格格式错误";
            }
        }
        return $this;
    }

    public function checkGoodsUnit($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["goodsUnit"], $value)){
                $this->_code = 18;
                $this->_message = "单位格式错误";
            }
        }
        return $this;
    }

    public function checkSellerComment($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["sellerComment"], $value)){
                $this->_code = 19;
                $this->_message = "简介格式错误";
            }
        }
        return $this;
    }

    public function checkLocationAddress($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["locationAddress"], $value)){
                $this->_code = 20;
                $this->_message = "所在地址错误";
            }
        }
        return $this;
    }

    public function checkRejectReason($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["sellerComment"], $value)){
                $this->_code = 21;
                $this->_message = "退回原因格式错误";
            }
        }
        return $this;
    }

    public function checkStandard($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["standardName"], $value)){
                $this->_code = 22;
                $this->_message = "规格名格式错误";
            }
        }
        return $this;
    }

    public function checkOrderRemark($value){
        if($this->_code == 0){
            if(!preg_match($this->_regexArray["orderRemark"], $value)){
                $this->_code = 23;
                $this->_message = "订单备注格式错误";
            }
        }
        return $this;
    }

    public function getMessage(){
        return $this->_message;
    }

    public function getCode(){
        return $this->_code;
    }

}
