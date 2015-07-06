<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-11
 * Time: 下午1:18
 */

namespace AdminMgr\Controller;


use AdminBase\Model\AdminMenu;
use Application\Entity\GoodsClass;
use Application\Entity\GoodsMainClass;
use Application\Model\Logger;
use Application\Model\Regex;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Exception;

class GoodsClassController extends AbstractActionController{

    protected $_superClassTable;
    protected $_mainClassTable;
    protected $_classTable;

    public function indexAction(){
        $vm = null;
        try{
            $basePath = $this->getRequest()->getBasePath();
            $cf = new AdminMenu();
            $menu = $cf->getMenu('商品分类管理', $basePath);

            $classTable = $this->getMainClassTable()->fetchAllWithClass();
            $classTable = $this->getSuperClassTable()->fillEmptySuperClass($classTable);

            $arr = array(
                "menu" => $menu,
                "classTable" => $classTable,
            );

            $fw = $this->forward();
            $this->layout()->setVariable("topNavbar",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "topnavbar",
            )));
            $this->layout()->setVariable("adminHeader",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "adminHeader",
            )));
            $this->layout()->setVariable("footer",$fw->dispatch('Application\Controller\Plugin',array(
                "action" => "footer",
            )));
            $vm =  new ViewModel($arr);
            $this->setRenderer();
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    private function setRenderer(){
        $baseUrl = $this->getRequest()->getBaseUrl();
        $renderer = $this->getServiceLocator()->get( 'Zend\View\Renderer\PhpRenderer');
        $renderer->headScript()->prependFile($baseUrl . '/js/admin/adminGoodsClass.js');
    }

    public function getMainClassAction(){
        $response = $this->getResponse();
        try{
            do{
                $mClasses = $this->getMainClassTable()->fetchAll();
                if(count($mClasses) > 0){
                    $mainClassTable = array();
                    for($i=0; $i<count($mClasses); $i++){
                        $mainClassTable[$i]["id"] = (int)($mClasses[$i]->getID());
                        $mainClassTable[$i]["name"] = ($mClasses[$i]->getName());
                    }
                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "mainClassTable" => $mainClassTable,
                    ),true));
                }else{
                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "mainClassTable" => array(),
                    )));
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $response;
    }

    public function getClassAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isGet()){
                    $data = $request->getQuery();
                    $id = $data["id"];
                    if(!is_numeric($id)){
                        $code = 1;
                        $message = "参数错误";
                        break;
                    }
                    $classes = $this->getClassTable()->fetchByMainClassID($id);
                    if(count($classes) > 0){
                        $classTable = array();
                        for($i=0; $i<count($classes); $i++){
                            $classTable[$i]["id"] = (int)($classes[$i]->getID());
                            $classTable[$i]["name"] = ($classes[$i]->getName());
                            $classTable[$i]["mainClassId"] = (int)($classes[$i]->getMainClassID());
                        }
                        $response->setContent(Json::encode(array(
                            "state" => true,
                            "classTable" => $classTable,
                        ),true));
                    }else{
                        $response->setContent(Json::encode(array(
                            "state" => true,
                            "classTable" => array(),
                        )));
                    }
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function addMainClassAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $name = $data["name"];
                    $superClassID = $data["superClassID"];
                    if( $name == null || $superClassID == null){
                        $code = 100;
                        $message = "参数不全";
                        break;
                    }
                    $regex = new Regex();
                    $regex->flush()->checkClassName($name)
                        ->checkId($superClassID);
                    if($regex->getCode()!=0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    if(!$this->getSuperClassTable()->getSuperClassByID($superClassID)){
                        $code = 102;
                        $message = "此超分类不存在";
                        break;
                    }
                    if($this->getMainClassTable()->getMainClassByName($superClassID, $name)){
                        $code = 101;
                        $message = "此分类已存在";
                        break;
                    }
                    $mainClass = new GoodsMainClass();
                    $mainClass->setName($name)
                        ->setSuperClassID($superClassID);
                    $id = $this->getMainClassTable()->saveMainClass($mainClass);
                    if(!$id){
                        $code = 100;
                        $message = "添加分类失败";
                        break;
                    }else{
                        $response->setContent(Json::encode(array(
                            "state" => true,
                            "id" => $id
                        )));
                    }
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function addClassAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $mainClassId = $data["mainClassId"];
                    $name = $data["name"];
                    if($mainClassId == null || $name == null){
                        $code = 100;
                        $message = "参数不全";
                        break;
                    }
                    $regex = new Regex();
                    $regex->flush()
                        ->checkId($mainClassId)
                        ->checkClassName($name);
                    if($regex->getCode()!=0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //check mainClassId exist
                    if(false == $mc = $this->getMainClassTable()->getMainClass($mainClassId)){
                        $code = 101;
                        $message = "不存在此上级分类";
                        break;
                    }
                    //check name exist
                    if($exist = $this->getClassTable()->getClassByName($mainClassId, $name)){
                        $code = 102;
                        $message = "此分类已存在";
                        break;
                    }
                    //insert
                    $class = new GoodsClass();
                    $class->setID(0)
                        ->setMainClassID($mainClassId)
                        ->setName($name);
                    $res = $this->getClassTable()->saveClass($class);
                    if(!$res){
                        $code = 100;
                        $message = "添加分类失败";
                        break;
                    }else{
                        $response->setContent(Json::encode(array(
                            "state" => true,
                            "id" => $res
                        )));
                    }
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function editMainClassAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $superClassId = $data["superClassId"];
                    $id = $data["id"];
                    $name = $data["name"];
                    if($superClassId == null || $id == null || $name == null ){
                        $code = 10001;
                        $message = "参数不全";
                        break;
                    }
                    $regex = new Regex();
                    $regex->flush()
                        ->checkId($superClassId)
                        ->checkId($id)
                        ->checkClassName($name);
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //end check

                    $mainClass = new GoodsMainClass();
                    $mainClass->setID($id)
                        ->setName($name);

                    if($this->getMainClassTable()->select(array(
                        "superClassId" => $superClassId,
                        "name" => $name
                    ))->count() > 0){
                        $code = 10004;
                        $message = "该主分类名已存在";
                        break;
                    }


                    // update
                    if(!$this->getMainClassTable()->saveMainClass($mainClass)){
                        $code = 10005;
                        $message = "数据没有更新";
                        break;
                    }

                    $response->setContent(Json::encode(array(
                        "state" => true,
                    )));
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function editClassAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $id = $data["id"];
                    $name = $data["name"];
                    if($id == null || $name == null ){
                        $code = 10001;
                        $message = "参数不全";
                        break;
                    }
                    $regex = new Regex();
                    $regex->flush()->checkId($id)
                        ->checkClassName($name);
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //end check

                    $goodsClass = new GoodsClass();
                    $goodsClass->setID($id)
                        ->setName($name);

                    if(!$this->getClassTable()->saveClass($goodsClass)){
                        $code = 10005;
                        $message = "数据没有更新";
                        break;
                    }

                    $response->setContent(Json::encode(array(
                        "state" => true,
                    )));
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function viewMainClassAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $id = $data["id"];
                    if($id == null){
                        $code = 10001;
                        $message = "参数不全";
                        break;
                    }
                    $regex = new Regex();
                    $regex->flush()->checkId($id);
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }

                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "name" => "fakeName",
                    )));
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }

    public function viewClassAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $code = 0;
        $message = "";
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $id = $data["id"];
                    if($id == null){
                        $code = 10001;
                        $message = "参数不全";
                        break;
                    }
                    $regex = new Regex();
                    $regex->flush()->checkId($id);
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }

                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "name" => "fakeName",
                    )));
                }
            }while(false);
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(Json::encode(array(
                "state" => false,
                "code" => $code,
                "message" => $message,
            )));
        }
        return $response;
    }






    /**
     * @return \Goods\Model\SuperClassTable
     */
    public function getSuperClassTable(){
        if(!$this->_superClassTable){
            $t = $this->getServiceLocator();
            $this->_superClassTable = $t->get('Goods\Model\SuperClassTable');
        }
        return $this->_superClassTable;
    }

    /**
     * @return \Goods\Model\MainClassTable
     */
    public function getMainClassTable(){
        if(!$this->_mainClassTable){
            $t = $this->getServiceLocator();
            $this->_mainClassTable = $t->get('Goods\Model\MainClassTable');
        }
        return $this->_mainClassTable;
    }

    /**
     * @return \Goods\Model\ClassTable
     */
    public function getClassTable(){
        if(!$this->_classTable){
            $t = $this->getServiceLocator();
            $this->_classTable = $t->get('Goods\Model\ClassTable');
        }
        return $this->_classTable;
    }
} 