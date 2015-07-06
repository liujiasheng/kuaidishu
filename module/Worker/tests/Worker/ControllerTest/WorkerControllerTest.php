<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-9-13
 * Time: 下午11:22
 */

namespace WorkerTest\ControllerTest;



use Zend\Authentication\Result;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class WorkerControllerTest extends AbstractHttpControllerTestCase {

    protected $traceError = true;
    public function setUp()
    {


        $this->setApplicationConfig(
            include 'application.config.php'
        );


        parent::setUp();
    }
    public function testGetLastTenOrder(){
        $post = array(
            'loginUserName' => $username = '111021',
            "loginPWD" => md5(123123),
            "userType" => 3);
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);

        $post = array(
            'code' => 1,
            'state'=>9
        );
        $this->dispatch('/worker/WorkerOrder/getOrderList','POST',$post);
        $result = $this->getResponse()->getContent();
        $list = Json::decode($result);
        if($list){
            $orderList = $list->list;
            $size = count($orderList);
            if($size==0){
                echo "无数据";
            }else{
                echo "载入了".$size."条信息";
            }
        }
    }


    public function testGetNewOrder(){
        $post = array(
            'loginUserName' => $username = '111021',
            "loginPWD" => md5(123123),
            "userType" => 3);
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);

        $post = array(
            'code' => 2,
            'id'=>399,
            'state'=>9
        );
        $this->dispatch('/worker/WorkerOrder/getOrderList','POST',$post);
        $result = $this->getResponse()->getContent();
        $list = Json::decode($result);
        if($list){
            $orderList = $list->list;
            $size = count($orderList);
            if($size==0){
                echo "无数据";
            }else{
                echo "载入了".$size."条信息";
            }
        }
    }

    public function testGetOldOrder(){
        $post = array(
            'loginUserName' => $username = '111021',
            "loginPWD" => md5(123123),
            "userType" => 3);
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);

        $post = array(
            'code' => 3,
            'id'=>46,
            'state'=>9
        );
        $this->dispatch('/worker/WorkerOrder/getOrderList','POST',$post);
        $result = $this->getResponse()->getContent();
        $list = Json::decode($result);
        if($list){
            $orderList = $list->list;
            $size = count($orderList);
            if($size==0){
                echo "无数据";
            }else{
                echo "载入了".$size."条信息";
            }
        }
    }

    public function testGetAllOrder(){
        $post = array(
            'loginUserName' => $username = '111021',
            "loginPWD" => md5(123123),
            "userType" => 3);
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);

        $post = array(
            'code' => 4,
            'state'=>2
        );
        $this->dispatch('/worker/WorkerOrder/getOrderList','POST',$post);
        $result = $this->getResponse()->getContent();
        $list = Json::decode($result);
        if($list){
            $orderList = $list->list;
            $size = count($orderList);
            if($size==0){
                echo "无数据";
            }else{
                echo "载入了".$size."条信息";
            }
        }
    }

    public function testGetOrderDetail(){
        $post = array(
            'loginUserName' => $username = '111021',
            "loginPWD" => md5(123123),
            "userType" => 3);
        $this->dispatch('/authenticate/Authenticate/login', 'POST', $post);

        $post = array(
            'id' => 1000002228
        );
        $this->dispatch('/worker/WorkerOrder/getOrderDetail','POST',$post);
        $result = $this->getResponse()->getContent();
        $list = Json::decode($result);

        if($list){

            $orderList = $list->list;
            var_dump($orderList);
            $size = count($orderList);
            if($size==0){
                echo "无数据";
            }else{
                echo "载入了".$size."条信息";
            }
        }
    }
}
 