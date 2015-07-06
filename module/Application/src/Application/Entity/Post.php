<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-23
 * Time: 下午9:35
 */

namespace Application\Entity;


use Exception;

class Post {
    protected $_Author;
    protected $_Content;
    protected $_Date;
    protected $_ID;
    protected $_Remark;
    protected $_Source;
    protected $_Summary;
    protected $_SurfacePlot;
    protected $_Title;
    protected $_Type;
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
     * @param mixed $Content
     * @return $this
     */
    public function setContent($Content)
    {
        $this->_Content = $Content;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->_Content;
    }

    /**
     * @param mixed $Date
     * @return $this
     */
    public function setDate($Date)
    {
        $this->_Date = $Date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->_Date;
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
     * @param mixed $Remark
     * @return $this
     */
    public function setRemark($Remark)
    {
        $this->_Remark = $Remark;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRemark()
    {
        return $this->_Remark;
    }

    /**
     * @param mixed $Source
     * @return $this
     */
    public function setSource($Source)
    {
        $this->_Source = $Source;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->_Source;
    }

    /**
     * @param mixed $Summary
     * @return $this
     */
    public function setSummary($Summary)
    {
        $this->_Summary = $Summary;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSummary()
    {
        return $this->_Summary;
    }

    /**
     * @param mixed $SurfacePlot
     * @return $this
     */
    public function setSurfacePlot($SurfacePlot)
    {
        $this->_SurfacePlot = $SurfacePlot;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSurfacePlot()
    {
        return $this->_SurfacePlot;
    }

    /**
     * @param mixed $Title
     * @return $this
     */
    public function setTitle($Title)
    {
        $this->_Title = $Title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->_Title;
    }

    /**
     * @param mixed $Type
     * @return $this
     */
    public function setType($Type)
    {
        $this->_Type = $Type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->_Type;
    }

    /**
     * @param mixed $Author
     * @return $this
     */
    public function setAuthor($Author)
    {
        $this->_Author = $Author;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->_Author;
    }



} 