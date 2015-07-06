<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-16
 * Time: 下午5:14
 */

namespace Application\Model;


use Authenticate\AssistantClass\UserType;

class IntercepterArray {

    //配置需要拦截的Action,不填写默认为无需权限。
    public function getIntercerpterArray(){
        $arr = array(
            //系统管理后台
            'User\Controller\UserController' => array(
                'index' => array( UserType::Admin ),
                'search' => array( UserType::Admin ),
                'add' =>  array( UserType::Admin ),
                'edit' =>  array( UserType::Admin ),
                'view' =>  array( UserType::Admin ),
            ),
            'Seller\Controller\SellerController' => array(
                'index' => array( UserType::Admin ),
                'search' => array( UserType::Admin ),
                'add' =>  array( UserType::Admin ),
                'edit' => array( UserType::Admin ),
                'view' => array( UserType::Admin ),
            ),
            'Worker\Controller\WorkerController' => array(
                'index' => array( UserType::Admin ),
                'search' => array( UserType::Admin ),
                'add' => array( UserType::Admin ),
                'edit' => array( UserType::Admin ),
                'view' => array( UserType::Admin ),
            ),
            'Goods\Controller\GoodsController' => array(
                'index' => array( UserType::Admin ),
                'search' => array( UserType::Admin ),
                'addGoods' => array( UserType::Admin ),
                'editGoods' => array( UserType::Admin ),
                'view' => array( UserType::Admin ),
                'getGoodsStandard' => array( UserType::Admin ),
                'editGoodsStandard' => array( UserType::Admin ),
            ),
            'AdminMgr\Controller\OrderController' => array(
                'index' =>array( UserType::Admin ),
                'search' => array( UserType::Admin ),
                'rejectOrders' => array( UserType::Admin ),
                'confirmOrders' => array( UserType::Admin ),
                'sendOrderToWorker' => array( UserType::Admin ),
                'sendOrdersToWorker' => array( UserType::Admin ),
                'finishOrders' => array( UserType::Admin ),
            ),
            'AdminMgr\Controller\GoodsClassController' => array(
                'index' => array( UserType::Admin ),
                'getMainClass' => array( UserType::Admin , UserType::Seller),
                'getClass' => array( UserType::Admin , UserType::Seller),
                'addMainClass' => array( UserType::Admin ),
                'addClass' => array( UserType::Admin ),
                'editMainClass' => array( UserType::Admin ),
                'editClass' => array( UserType::Admin ),
                'viewMainClass' => array( UserType::Admin ),
                'viewClass' => array( UserType::Admin ),
            ),
            'AdminMgr\Controller\PostController' => array(
                'adminPost'=> array( UserType::Admin ),
                'editPost'=> array( UserType::Admin ),
                'addPost'=> array( UserType::Admin ),
            ),
            'AdminMgr\Controller\OrderAssignController' => array(
                'index'=> array( UserType::Admin ),
            ),
            'AdminMgr\Controller\PhoneListController' => array(
                'index'=> array( UserType::Admin ),
                'getPhoneState'=> array( UserType::Admin ),
            ),

            //数据导入
            'Goods\Controller\GoodsImportController' => array(
                'index' => array( UserType::Admin ),
                'import' => array( UserType::Admin ),
            ),

            //数据统计
            'AdminMgr\Controller\StatisticsController' => array(
                'index'=> array( UserType::Admin ),
                'exportSellerReportForm'=> array( UserType::Admin ),
                'updateAnalysisChart' => array( UserType::Admin ),
            ),

            //购物车、订单页面
            'Home\Controller\CartController' => array(
                'index' => array( UserType::User ),
                'order' => array( UserType::User ),
                'ordered' => array( UserType::User ),
                'submitOrder' => array( UserType::User ),
            ),

            //用户后台
            'User\Controller\UserInfoController' => array(
                'index' => array( UserType::User ),
                'userinfo' => array( UserType::User ),
                'modifypwd' => array( UserType::User ),
                'delivery' => array( UserType::User ),
                'updateuserinfo' => array( UserType::User ),
                'updatePassword' => array( UserType::User ),
                'getDeliveryAddress' => array( UserType::User ),
                'addDeliveryAddress' => array( UserType::User ),
                'updateDeliveryAddress' => array( UserType::User ),
                'userInfoPage' => array( UserType::User),
                'userPwdPage' => array( UserType::User ),
                'userDeliveryAddressPage' => array( UserType::User),

            ),
            'User\Controller\UserOrderMGRController' => array(
                'userOrderManagePage2'=>array( UserType::User ),
                'userOrderManagePage'=>array( UserType::User ),
                'userOrderManageDetailPage'=>array( UserType::User ),
            ),

            //商家后台
            'Seller\Controller\SellerOrderMgrController' => array(
                'orderMgr' => array( UserType::Seller),
            ),
            'Seller\Controller\SellerGoodsController' => array(
                'goodsMgr' => array( UserType::Seller),
                'view' => array( UserType::Seller),
                'getGoodsStandard' => array( UserType::Seller),
                'editGoodsStandard' => array( UserType::Seller),
            ),

            //员工后台
            'Worker\Controller\WorkerOrderController' => array(
                'getOrderList' => array( UserType::Worker ),
                'getOrderMessage' => array( UserType::Worker ),
            ),


//            这些入口/接口被禁用
            'Authenticate\Controller\AuthenticateController' => array(
                'workerLoginPage' => array(),
            ),

        );
        $copy = $this->makeCopy($arr);
        return $copy;
    }

    //配置被拦截后跳转到的Url，默认为主页。
    public function getRedirectUrlArray(){
        return array(
            //系统管理后台
            'User\Controller\UserController' => array(
                'index' => "/",
            ),
            'Seller\Controller\SellerController' => array(
                'index' => "/",
            ),
            'Worker\Controller\WorkerController' => array(
                'index' => "/",
            ),
            'Goods\Controller\GoodsController' => array(
                'index' => "/",
            ),
            'AdminMgr\Controller\OrderController' => array(
                'index' => "/",
            ),
            'AdminMgr\Controller\GoodsClassController' => array(
                'index' => "/",
            ),
            'AdminMgr\Controller\PostController' => array(
                'adminPost'=> "/",
            ),
            'AdminMgr\Controller\OrderAssignController' => array(
                'index' => "/",
            ),
            'AdminMgr\Controller\PhoneListController' => array(
                'index' => "/",
            ),
            //购物车、订单页面
            'Home\Controller\CartController' => array(
                'index' => '/userlogin?redirect='.urlencode('/home/cart'),
                'order' => '/userlogin',
                'ordered' => '/userlogin',
            ),

            //用户后台
            'User\Controller\UserInfoController' => array(
//                'index' => array( UserType::User ),
//                'userinfo' => array( UserType::User ),
//                'modifypwd' => array( UserType::User ),
//                'delivery' => '/userlogin',
                'userinfopage' => '/userlogin',
                'userpwdpage' => '/userlogin',
                'userdeliveryaddresspage' => '/userlogin',
            ),
            'User\Controller\UserOrderMGRController' => array(
                'userordermanagepage2'=>'/userlogin',
                'userordermanagepage'=>'/userlogin',
                'userordermanagedetailpage'=>'/userlogin',
            ),

        );
    }

    protected function makeCopy($arr){
        $newArr = array();
        $keyArr = array_keys($arr);
        while($ctrlName = array_pop($keyArr)){
            $newArr[$ctrlName] = array_change_key_case($arr[$ctrlName], CASE_LOWER);
        }
        return $newArr;
    }

} 