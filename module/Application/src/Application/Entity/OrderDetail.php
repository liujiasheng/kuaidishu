<?php

namespace Application\Entity;

use Exception;

class OrderDetail {
    protected $_ID;
    protected $_OrderID;
    protected $_SellerID;
    protected $_GoodsID;
    protected $_Name;
    protected $_Price;
    protected $_Unit;
    protected $_Count;
    protected $_Total;
    protected $_Image;
    protected $_Comment;
    protected $_Barcode;

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


    public function setOrderID($OrderID)
    {
        $this->_OrderID = $OrderID;
        return $this;
    }


    public function getOrderID()
    {
        return $this->_OrderID;
    }


    public function setSellerID($SellerID)
    {
        $this->_SellerID = $SellerID;
        return $this;
    }


    public function getSellerID()
    {
        return $this->_SellerID;
    }


    public function setGoodsID($GoodsID)
    {
        $this->_GoodsID = $GoodsID;
        return $this;
    }


    public function getGoodsID()
    {
        return $this->_GoodsID;
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


    public function setPrice($Price)
    {
        $this->_Price = $Price;
        return $this;
    }


    public function getPrice()
    {
        return $this->_Price;
    }


    public function setUnit($Unit)
    {
        $this->_Unit = $Unit;
        return $this;
    }


    public function getUnit()
    {
        return $this->_Unit;
    }


    public function setCount($Count)
    {
        $this->_Count = $Count;
        return $this;
    }


    public function getCount()
    {
        return $this->_Count;
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


    public function setImage($Image)
    {
        $this->_Image = $Image;
        return $this;
    }


    public function getImage()
    {
        return $this->_Image;
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


    public function setBarcode($Barcode)
    {
        $this->_Barcode = $Barcode;
        return $this;
    }


    public function getBarcode()
    {
        return $this->_Barcode;
    }


}
