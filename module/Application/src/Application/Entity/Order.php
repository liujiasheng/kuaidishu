<?php

namespace Application\Entity;

use Exception;

class Order {
    protected $_Address;
    protected $_Comment;
    protected $_Domain2;
    protected $_Domain3;
    protected $_Domain;
    protected $_EndTime;
    protected $_ID;
    protected $_Name;
    protected $_OrderTime;
    protected $_Phone;
    protected $_Remark;
    protected $_SellerID;
    protected $_State;
    protected $_Total;
    protected $_UserID;
    protected $_Version;



    protected $_UserName;

    public function __construct(array $options = null) {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value) {
        $method = 'set' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid Method');
        }
        $this->$method($value);
    }

    public function __get($name) {
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Exception('Invalid Method');
        }
        return $this->$method();
    }

    public function setOptions(array $options) {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setID($ID)
    {
        $this->_ID = $ID;
        return $this;
    }


    public function getID()
    {
        return $this->_ID;
    }


    public function setUserID($UserID)
    {
        $this->_UserID = $UserID;
        return $this;
    }


    public function getUserID()
    {
        return $this->_UserID;
    }

    public function setOrderTime($OrderTime)
    {
        $this->_OrderTime = $OrderTime;
        return $this;
    }


    public function getOrderTime()
    {
        return $this->_OrderTime;
    }


    public function setEndTime($EndTime)
    {
        $this->_EndTime = $EndTime;
        return $this;
    }


    public function getEndTime()
    {
        return $this->_EndTime;
    }


    public function setTotal($Total)
    {
        $this->_Total = $Total;
        return $this;
    }


    public function getTotal()
    {
        return $this->_Total;
    }


    public function setState($State)
    {
        $this->_State = $State;
        return $this;
    }


    public function getState()
    {
        return $this->_State;
    }


    public function setComment($Comment)
    {
        $this->_Comment = $Comment;
        return $this;
    }


    public function getComment()
    {
        return $this->_Comment;
    }


    public function setRemark($Remark)
    {
        $this->_Remark = $Remark;
        return $this;
    }


    public function getRemark()
    {
        return $this->_Remark;
    }


    public function setName($Name)
    {
        $this->_Name = $Name;
        return $this;
    }


    public function getName()
    {
        return $this->_Name;
    }


    public function setPhone($Phone)
    {
        $this->_Phone = $Phone;
        return $this;
    }


    public function getPhone()
    {
        return $this->_Phone;
    }


    public function setDomain($Domain)
    {
        $this->_Domain = $Domain;
        return $this;
    }


    public function getDomain()
    {
        return $this->_Domain;
    }


    public function setDomain2($Domain2)
    {
        $this->_Domain2 = $Domain2;
        return $this;
    }


    public function getDomain2()
    {
        return $this->_Domain2;
    }


    public function setDomain3($Domain3)
    {
        $this->_Domain3 = $Domain3;
        return $this;
    }


    public function getDomain3()
    {
        return $this->_Domain3;
    }


    public function setAddress($Address)
    {
        $this->_Address = $Address;
        return $this;
    }


    public function getAddress()
    {
        return $this->_Address;
    }

    /**
     * @param mixed $UserName
     * @return $this
     */
    public function setUserName($UserName)
    {
        $this->_UserName = $UserName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->_UserName;
    }

    /**
     * @param mixed $SellerID
     * @return $this
     */
    public function setSellerID($SellerID)
    {
        $this->_SellerID = $SellerID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSellerID()
    {
        return $this->_SellerID;
    }

    /**
     * @param mixed $Version
     * @return $this
     */
    public function setVersion($Version)
    {
        $this->_Version = $Version;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->_Version;
    }


}
