<?php
/**
 * Created by JetBrains PhpStorm.
 * User: james
 * Date: 14-7-1
 * Time: 下午5:52
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Entity;


class Admin {

protected $_ID;
protected $_Username;
protected $_Password;
protected $_Nickname;
protected $_Email;
protected $_LoginIP;
protected $_LoginTime;
protected $_State;
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
     * @param mixed $Email
     */
    public function setEmail($Email)
    {
        $this->_Email = $Email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->_Email;
    }

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
     * @param mixed $LoginIP
     */
    public function setLoginIP($LoginIP)
    {
        $this->_LoginIP = $LoginIP;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLoginIP()
    {
        return $this->_LoginIP;
    }

    /**
     * @param mixed $LoginTime
     */
    public function setLoginTime($LoginTime)
    {
        $this->_LoginTime = $LoginTime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLoginTime()
    {
        return $this->_LoginTime;
    }

    /**
     * @param mixed $Nickname
     */
    public function setNickname($Nickname)
    {
        $this->_Nickname = $Nickname;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNickname()
    {
        return $this->_Nickname;
    }

    /**
     * @param mixed $Password
     */
    public function setPassword($Password)
    {
        $this->_Password = $Password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->_Password;
    }

    /**
     * @param mixed $State
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
     * @param mixed $Username
     */
    public function setUsername($Username)
    {
        $this->_Username = $Username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->_Username;
    }




}