<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Seller for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Seller\Controller;

use AdminBase\Model\AdminMenu;
use Application\Entity\Seller;
use Application\Model\AliOssConfig;
use Application\Model\AliOssOperator;
use Application\Model\ImageCompresser;
use Application\Model\Regex;
use Application\Model\StaticInfo;
use Zend\File\Transfer\Adapter\Http;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Json\Json;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Uri\File;
use Zend\View\Model\ViewModel;
use Exception;

class SellerController extends AbstractActionController
{
    protected $_sellerTable;

    public function indexAction()
    {
        $vm = null;
        try{
            $basePath = $this->getRequest()->getBasePath();
            $cf = new AdminMenu();
            $menu = $cf->getMenu('商家管理', $basePath);
            $arr = array(
                "sellerTable" => $this->getSellerTable()->fetchSlice(1,10,null),
                "count" => $this->getSellerTable()->fetchCount(null),
                "menu" => $menu,
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
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    public function searchAction(){
        $request = $this->getRequest();
        $response = $this->getResponse();
        try{
            if($request->isPost()){
                $data = $request->getPost();
                $state = $data["state"];
                $text = $data["searchText"];
                $where = array();
                if($state != "0")
                    $where[] = "state = ".$state;
                if($text!="")
                    $where[] = "(username like '%".$text."%' or name like '%".$text."%')";
                //check text
                if($text!=""){
                    $regex = new Regex();
                    $regex->flush()->checkSearchText($text);
                    if($regex->getCode() != 0){
                        $response->setContent(\Zend\Json\Json::encode(array("state" => false, "code" => $regex->getCode(), "message"=>$regex->getMessage())));
                        return $response;
                    }
                }
                $slice = $this->getSellerTable()->fetchSlice((int)$data["curPage"], (int)$data["pageCount"], $where);
                $all = $this->getSellerTable()->fetchCount($where);
                $cnt = count($slice);
                $sellers = array();
                for($k=0; $k<$cnt; $k++){
                    $sellers[$k]["id"] = (int)($slice[$k]->getID());
                    $sellers[$k]["username"] = $slice[$k]->getUsername();
                    $sellers[$k]["name"] = $slice[$k]->getName();
                    $sellers[$k]["phone"] = $slice[$k]->getPhone();
                    $sellers[$k]["contactPhone"] = $slice[$k]->getContactPhone();
                    $sellers[$k]["state"] = (int)($slice[$k]->getState());
                }
                if($cnt == 0)
                    $response->setContent(\Zend\Json\Json::encode(array("state" => true, "count" => 0)));
                else
                    $response->setContent(\Zend\Json\Json::encode(array("state" => true, "count" => $all, "sellerTable" => $sellers), true));
            }
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $response;
    }

    public function viewAction(){
        $request = $this->getRequest();
        $response = $this->getResponse();
        try{
            if($request->isPost()){
                $data = $request->getPost();
                $sid = $data["id"];
                if(!$seller = $this->getSellerTable()->getSeller($sid)){
                    $response->setContent(Json::encode(array("state" => false)));
                }else{
                    $response->setContent(Json::encode(array(
                        "state" => true,
                        "seller" => array(
                            "id" => $seller->getID(),
                            "username" => $seller->getUsername(),
                            "address" => $seller->getAddress(),
                            "name" => $seller->getName(),
                            "email" => $seller->getEmail(),
                            "phone" => $seller->getPhone(),
                            "contactPhone" => $seller->getContactPhone(),
                            "comment" => $seller->getComment(),
                            "loginIP" => $seller->getLoginIP(),
                            "loginTime" => $seller->getLoginTime(),
                            "logo" => $seller->getLogo(),
                            "state" => $seller->getState()
                        ),
                    )));
                }
            }
        }catch (Exception $e){
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $response;
    }

    public function addAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $response = $this->getResponse();
        $message = "";
        $code = 0;
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $seller = new Seller();
                    $seller->setOptions(array(
                        "ID" => 0,
                        "Username" => $data["username"],
                        "Password" => $data["password"],
                        "Name" => $data["name"],
                        "Address" => $data["address"],
                        "Email" => $data["email"],
                        "Phone" => $data["phone"],
                        "ContactPhone" => $data["contactPhone"],
                        "Comment" => $data["comment"],
                    ));
                    //check worker
                    $regex = new \Application\Model\Regex();
                    $regex->flush()
                        ->checkUsername($seller->getUsername())
                        ->checkPassword($seller->getPassword())
                        ->checkSellerName($seller->getName())
                        ->checkSellerComment($seller->getComment())
                        ->checkAddress($seller->getAddress())
                        ->checkEmail($seller->getEmail())
                        ->checkPhone($seller->getPhone())
                        ->checkPhone($seller->getContactPhone());
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    //check username exist
                    $exist = $this->getSellerTable()->select(array("username"=>$seller->getUsername()))->current();
                    if($exist){
                        $code = 101;
                        $message = "用户名已存在";
                        break;
                    }
                    if($sameName = $this->getSellerTable()->select(array("name"=>$seller->getName()))->current()){
                        $code = 103;
                        $message = "商家名已存在";
                        break;
                    }
                    //save the logo
                    $files = $request->getFiles();
                    if($files->count() > 0){
                        $file = $files->logo;
                        //check file
                        if($file["error"] != 0){
                            $code = 1031;
                            $message = "图片上传失败";
                            break;
                        }
                        if($file["size"]>1<<23){
                            $code = 102;
                            $message = "图片最大为8M";
                            break;
                        }
                        //save logo
                        $this->saveLogo($file, $seller);
                    }
                    //insert
                    $sellerId = $this->getSellerTable()->saveSeller($seller);
                    if(!$sellerId){
                        $code = 100;
                        $message = "添加商家失败";
                        break;
                    }else{
                        $response->setContent(Json::encode(array("state" => true, "id" => $sellerId)));
                    }
                }
            }while(false);
        }catch (Exception $e){
            //log
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(\Zend\Json\Json::encode(array("state" => false, "code" => $code, "message" => $message)));
        }
        return $response;
    }

    public function editAction(){
        /** @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        /** @var $response \Zend\Http\Response */
        $response = $this->getResponse();
        $message = "";
        $code = 0;
        try{
            do{
                if($request->isPost()){
                    $data = $request->getPost();
                    $seller = new Seller();
                    $seller->setOptions(array(
                        "ID" => $data["id"],
                        "Password" => $data["password"],
                        "Name" => $data["name"],
                        "Comment" => $data["comment"],
                        "Address" => $data["address"],
                        "Email" => $data["email"],
                        "Phone" => $data["phone"],
                        "ContactPhone" => $data["contactPhone"],
                        "State" => $data["state"],
                    ));
                    //get username by id
                    $tmpSeller = $this->getSellerTable()->getSeller($seller->getID());
                    if($tmpSeller){
                        $seller->setUsername($tmpSeller->getUsername());
                    }else{
                        $code = 201;
                        $message = "id有误";
                        break;
                    }
                    $regex = new Regex();
                    $regex->flush()
                        ->checkSellerName($seller->getName())
                        ->checkSellerComment($seller->getComment())
                        ->checkAddress($seller->getAddress())
                        ->checkEmail($seller->getEmail())
                        ->checkPhone($seller->getPhone())
                        ->checkPhone($seller->getContactPhone())
                        ->checkState($seller->getState());
                    if($seller->getPassword()!="") $regex->checkPassword($seller->getPassword());
                    if($regex->getCode() != 0){
                        $code = $regex->getCode();
                        $message = $regex->getMessage();
                        break;
                    }
                    if($sameName = $this->getSellerTable()->select("name='".$seller->getName()."' and id!=".$seller->getID())->current()){
                        $code = 103;
                        $message = "商家名已存在";
                        break;
                    }
                    //check logo
                    $files = $request->getFiles();
                    if($files->count() > 0){
                        $file = $files->logo;
                        if($file["error"] != 0){
                            $code = 1031;
                            $message = "图片上传失败";
                            break;
                        }
                        if($file["size"]>1<<23){
                            $code = 102;
                            $message = "图片最大为8M";
                            break;
                        }
                        $this->saveLogo($file, $seller);
                    }

                    //update
                    if((!$sid = $this->getSellerTable()->saveSeller($seller)) && $seller->getLogo()==null){
                        $code = 101;
                        $message = "数据没有更新";
                        break;
                    }
                }
            }while(false);
        }catch (Exception $e){
            //log
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        if($code!=0){
            $response->setContent(\Zend\Json\Json::encode(array("state"=>false, "code"=>$code, "message"=>$message)));
        }else{
            $response->setContent(\Zend\Json\Json::encode(array("state"=>true)));
        }
        return $response;
    }

    /**
     * @return \Seller\Model\SellerTable
     */
    public function  getSellerTable(){
        if(!$this->_sellerTable){
            $t = $this->getServiceLocator();
            $this->_sellerTable = $t->get('Seller\Model\SellerTable');
        }
        return $this->_sellerTable;
    }

    public function fooAction()
    {
//        $logger = new \Application\Model\Logger();
//        $logger->info(123123,"123123")
//            ->debug(123123,"321321");


        return array();
    }


    /**
     * @param $oldName
     * @param $seller Seller
     * @return string
     */
    private  function getLogoNewName($oldName,$seller)
    {
        $oldType = substr($oldName,strrpos($oldName,"."));
        //$this->type = $oldType;
        //$newName = time().floor(microtime() * 10000).rand(10, 99);
        $newName =  $seller->getUsername()."-".time();
        return $newName.$oldType;
    }

    /**
     * @param $file
     * @param $seller Seller
     */
    private function saveLogo($file,$seller){
//        $fileAdapter = new Http();
        //重新生成文件名
        $newName = $this->getLogoNewName($file['name'],$seller);

        //压缩文件
        //$newFile = tmpfile();
        $newFile = ImageCompresser::compressImage($file, 100, 200 );
        $metaData = stream_get_meta_data($newFile);
        //$this->compress_image($file, $newFile, 100);


        //储存文件
        if ( AliOssOperator::uploadFile($metaData['uri'], $newName, AliOssConfig::getSellerLogoDir()) ){
//            $resFile = $fileAdapter->getFilter('File\Rename')->getFile();
            $seller->setLogo($newName);
        }else{
            $seller->setLogo("logo.jpg");
        }

//        if (copy($metaData['uri'], StaticInfo::getSellerLogoDir().$newName)){
////            $resFile = $fileAdapter->getFilter('File\Rename')->getFile();
//            $seller->setLogo($newName);
//        }else{
//            $seller->setLogo("logo.jpg");
//        }
    }

//    function compress_image($source, $destination, $quality) {
//        $info = getimagesize($source['tmp_name']);
//        $image = null;
//        if ($info['mime'] == 'image/jpeg') $image =  imagecreatefromjpeg($source['tmp_name']);
//        elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($source['tmp_name']);
//        elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($source['tmp_name']);
//
//        $metaData = stream_get_meta_data($destination);
//        $abc = $this->compressImage($source);
//
//        //save file
////        imagecopyresized($destination_url, $source, 0,0,0,0,150,150,1920,1080);
//        $a = imagejpeg($abc, $metaData['uri'], $quality);
//
//        //return destination file
//        return $destination;
//    }
//
//    private function compressImage($file){
//        //压缩图片
//        $srcImg = $file['tmp_name'];
//        $maxHeight = 200;
//        // Get new sizes
//        list($width, $height) = getimagesize($srcImg);
//        if($height > $maxHeight){
//            $newWidth = $width/$height * $maxHeight;
//            $newHeight = $maxHeight;
//        }else{
//            $newWidth = $width;
//            $newHeight = $height;
//        }
//        // Load
//        $thumb = imagecreatetruecolor($newWidth, $newHeight);
//        $source = imagecreatefromjpeg($srcImg);
//        // Resize
//        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
//        return $thumb;
//    }

}
