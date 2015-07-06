<?php
namespace Application\Entity;

class Goods {
    protected $_ID;
    protected $_MainClassID;
    protected $_ClassID;
    protected $_SellerID;
    protected $_Name;
    protected $_Price;
    protected $_Unit;
    protected $_Image;
    protected $_Comment;
    protected $_Barcode;
    protected $_State;
    protected $_Remain;

    protected $_SellerName;
    protected $_MainClassName;
    protected $_ClassName;

    protected $_GoodsStandards;


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

    /**
     * @param array $options
     * @return $this
     */
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

    /**
     * @param $ID
     * @return $this
     */
    public function setID($ID)
    {
        $this->_ID = $ID;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getID()
    {
        return $this->_ID;
    }


    /**
     * @param $MainClassID
     * @return $this
     */
    public function setMainClassID($MainClassID)
    {
        $this->_MainClassID = $MainClassID;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getMainClassID()
    {
        return $this->_MainClassID;
    }


    /**
     * @param $ClassID
     * @return $this
     */
    public function setClassID($ClassID)
    {
        $this->_ClassID = $ClassID;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getClassID()
    {
        return $this->_ClassID;
    }


    /**
     * @param $SellerID
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
     * @param $Name
     * @return $this
     */
    public function setName($Name)
    {
        $this->_Name = $Name;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_Name;
    }


    /**
     * @param $Price
     * @return $this
     */
    public function setPrice($Price)
    {
        $this->_Price = $Price;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->_Price;
    }

    /**
     * @param mixed $Unit
     */
    public function setUnit($Unit)
    {
        $this->_Unit = $Unit;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->_Unit;
    }


    /**
     * @param $Image
     * @return $this
     */
    public function setImage($Image)
    {
        $this->_Image = $Image;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->_Image;
    }


    /**
     * @param $Comment
     * @return $this
     */
    public function setComment($Comment)
    {
        $this->_Comment = $Comment;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->_Comment;
    }


    /**
     * @param $Barcode
     * @return $this
     */
    public function setBarcode($Barcode)
    {
        $this->_Barcode = $Barcode;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getBarcode()
    {
        return $this->_Barcode;
    }


    /**
     * @param $State
     * @return $this
     */
    public function setState($State)
    {
        $this->_State = $State;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->_State;
    }


    /**
     * @param $Remain
     * @return $this
     */
    public function setRemain($Remain)
    {
        $this->_Remain = $Remain;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getRemain()
    {
        return $this->_Remain;
    }


    /**
     * @param mixed $ClassName
     */
    public function setClassName($ClassName)
    {
        $this->_ClassName = $ClassName;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->_ClassName;
    }

    /**
     * @param mixed $MainClassName
     */
    public function setMainClassName($MainClassName)
    {
        $this->_MainClassName = $MainClassName;
    }

    /**
     * @return mixed
     */
    public function getMainClassName()
    {
        return $this->_MainClassName;
    }

    /**
     * @param mixed $SellerName
     */
    public function setSellerName($SellerName)
    {
        $this->_SellerName = $SellerName;
    }

    /**
     * @return mixed
     */
    public function getSellerName()
    {
        return $this->_SellerName;
    }


    /**
     * @param mixed $GoodsStandards
     */
    public function setGoodsStandards($GoodsStandards)
    {
        $this->_GoodsStandards = $GoodsStandards;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGoodsStandards()
    {
        return $this->_GoodsStandards;
    }

}
