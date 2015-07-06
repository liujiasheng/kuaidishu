<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-7-16
 * Time: 下午1:44
 */

namespace Application\Entity;


use Exception;

class DeliveryAddress {


    protected $_Address;
    protected $_Domain2;
    protected $_Domain3;
    protected $_Domain;
    protected $_ID;
    protected $_Name;
    protected $_Phone;
    protected $_UserID;

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
     * @param mixed $Address
     * @return $this
     */
    public function setAddress($Address)
    {
        $this->_Address = $Address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->_Address;
    }

    /**
     * @param mixed $Domain
     * @return $this
     */
    public function setDomain($Domain)
    {
        $this->_Domain = $Domain;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->_Domain;
    }

    /**
     * @param mixed $ID
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
     * @param mixed $Name
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
     * @param mixed $Phone
     * @return $this
     */
    public function setPhone($Phone)
    {
        $this->_Phone = $Phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->_Phone;
    }

    /**
     * @param mixed $UserID
     * @return $this
     */
    public function setUserID($UserID)
    {
        $this->_UserID = $UserID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserID()
    {
        return $this->_UserID;
    }

    /**
     * @param mixed $Domain2
     * @return $this
     */
    public function setDomain2($Domain2)
    {
        $this->_Domain2 = $Domain2;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDomain2()
    {
        return $this->_Domain2;
    }

    /**
     * @param mixed $Domain3
     * @return $this
     */
    public function setDomain3($Domain3)
    {
        $this->_Domain3 = $Domain3;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDomain3()
    {
        return $this->_Domain3;
    }


} 