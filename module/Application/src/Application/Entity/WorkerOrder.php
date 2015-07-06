<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-12
 * Time: 下午10:42
 */

namespace Application\Entity;


use Exception;

class WorkerOrder {
    protected $_AppointTime;
    protected $_ID;
    protected $_OrderID;
    protected $_WorkerID;

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

    /**
     * @param mixed $WorkerID
     * @return $this
     */
    public function setWorkerID($WorkerID)
    {
        $this->_WorkerID = $WorkerID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWorkerID()
    {
        return $this->_WorkerID;
    }

    /**
     * @param mixed $AppointTime
     * @return $this
     */
    public function setAppointTime($AppointTime)
    {
        $this->_AppointTime = $AppointTime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppointTime()
    {
        return $this->_AppointTime;
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
     * @param mixed $OrderID
     * @return $this
     */
    public function setOrderID($OrderID)
    {
        $this->_OrderID = $OrderID;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderID()
    {
        return $this->_OrderID;
    }


} 