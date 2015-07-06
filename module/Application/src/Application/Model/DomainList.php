<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-7-16
 * Time: 下午3:21
 */

namespace Application\Model;


class DomainList
{

    protected $_list;

    public function __construct()
    {

        $this->_list = array(
            "广州大学" => array(
                '梅苑' => array(
                    'B1', 'B2','B3', 'B4','B5','B6','B7','B8','B9','B10',
                ),
                '竹苑' => array(
                    'B11','B12','B13','B14','B15',
                ),
                '菊苑' => array(
                    'B21','B22','B23','B24','B25',
                ),
                '兰苑' => array(
                    'B16','B17','B18','B19','B20',
                ),
                '商中',
                '行政东楼',
                '行政西楼'),
//            '中山大学'=>array(
//                '宿舍1',
//                '宿舍2',
//                '教学楼'
//            ),
//            '广东工业大学'=>array(
//                '饭堂',
//                '教学楼'
//            ),
        );
    }

    public function has($domain, $domain2, $domain3)
    {
        if (array_key_exists($domain, $this->_list)) {
            $domain2List = $this->_list[$domain];
            if (array_key_exists($domain2, $domain2List)) {
                $domain3List = $domain2List[$domain2];
                if (array_key_exists($domain3, $domain3List)) {
                    return 3;
                } elseif (in_array($domain3, $domain3List)) {
                    return 3;
                }
                return 2;
            } elseif (in_array($domain2, $domain2List)) {
                return 2;
            }
            return 1;
        } elseif (in_array($domain, $this->_list)) {
            return 1;
        }
        return 0;
    }

    public function get()
    {
        $list = array();
        foreach ($this->_list as $key => $domain) {
            array_push($list, $key);
        }
        return $list;
    }

    public function getDomain2List($value)
    {
        if (array_key_exists($value, $this->_list)) {
            $domain2 = $this->_list[$value];
            $rs = array();
            foreach ($domain2 as $key => $val) {
                if (is_array($val)) {
                    array_push($rs, $key);
                } else {
                    array_push($rs, $val);
                }
            }
            return $rs;
        }
        return array();
    }

    public function getDomain3List($domain, $domain2)
    {
        if (array_key_exists($domain, $this->_list)) {
            $domain2List = $this->_list[$domain];
            if (array_key_exists($domain2, $domain2List)) {
                $domain3List = $domain2List[$domain2];
                $rs = array();
                foreach ($domain3List as $key => $val) {
                    if (is_array($val)) {
                        array_push($rs, $key);
                    } else {
                        array_push($rs, $val);
                    }
                }
                return $rs;
            }
        }

        return array();
    }

    public function getFullDomainList(){
        return $this->_list;
    }
}