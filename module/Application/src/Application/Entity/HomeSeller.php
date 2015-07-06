<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-14
 * Time: 下午4:31
 */

namespace Application\Entity;

class HomeSeller {

    protected $_ID;
    protected $_SellerID;
    protected $_Order;

    protected $_SellerName;
    protected $_Logo;

    /**
     * @param mixed $ID
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
     * @param mixed $Order
     */
    public function setOrder($Order)
    {
        $this->_Order = $Order;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->_Order;
    }

    /**
     * @param mixed $SellerID
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
     * @param mixed $Logo
     */
    public function setLogo($Logo)
    {
        $this->_Logo = $Logo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLogo()
    {
        return $this->_Logo;
    }

    /**
     * @param mixed $SellerName
     */
    public function setSellerName($SellerName)
    {
        $this->_SellerName = $SellerName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSellerName()
    {
        return $this->_SellerName;
    }


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

}