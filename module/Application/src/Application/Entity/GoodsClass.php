<?php

namespace Application\Entity;

class GoodsClass {

    protected $_ID;
    protected $_Name;
    protected $_MainClassID;

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


    public function setName($Name)
    {
        $this->_Name = $Name;
        return $this;
    }


    public function getName()
    {
        return $this->_Name;
    }


    public function setMainClassID($MainClassID)
    {
        $this->_MainClassID = $MainClassID;
        return $this;
    }


    public function getMainClassID()
    {
        return $this->_MainClassID;
    }


}
