<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-22
 * Time: 下午12:52
 */

namespace AdminMgr\Controller;


use AdminBase\Model\AdminMenu;
use AdminMgr\Model\PostTable;
use AdminMgr\Model\PostTypeTable;
use Application\Model\Logger;
use Exception;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Sql\Where;
use Zend\Http\Request;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\XmlRpc\Value\DateTime;

class PostController extends AbstractActionController
{

    public function adminPostAction()
    {
        $vm = null;
        try {
            /** @var $phpRenderer phpRenderer */
            $phpRenderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
            $phpRenderer->headLink()->appendStylesheet('/css/bootstrap-combined.no-icons.min.css',null,null,null);
            /** @var $authSvr AuthenticationService */
            $authSvr = $this->getServiceLocator()->get('Session');
            if(!$authSvr->hasIdentity()){
                return $this->redirect()->toRoute('home');
            }
            /** @var $request Request */
            $request = $this->getRequest();
            $basePath = $this->getRequest()->getBasePath();
            $cf = new AdminMenu();
            $menu = $cf->getMenu('文章管理', $basePath);

            $page = trim($request->getQuery('page'));
            $type = trim($request->getQuery('type'));
            $source = trim($request->getQuery('source'));
            $searchText = trim($request->getQuery('kw'));
            if(!is_numeric($page)){
                $page = 1;
            }
            $page = $page<1?1:$page;

            $limit = 10;


            /** @var $postTable PostTable */
            $postTable = $this->getServiceLocator()->get('AdminMgr\Model\PostTable');
            $where = new Where();
            if($type!==null&&$type!==""){
                $where->equalTo("Type",$type);
            }
            if($source!==null&&$source!==""){
                $where->equalTo("Source",$source);
            }
            if($searchText!==null&&$searchText!==""){
                $where->OR->like('Title',"%".$searchText."%")
                    ->OR->like("Summary", "%".$searchText."%")
                    ->OR->like("Remark", "%".$searchText."%");
            }
            $pages = ceil($postTable->getPostListCount($where)/$limit);
            $pages = $pages==0?1:$pages;
            $page = $page>$pages?$pages:$page;
            $start = ($page-1)*$limit;
            $list = $postTable->getPostList($where,$start,$limit);
            $typeList = $postTable->getTypeList();
            $sourceList = $postTable->getSourceList();
            $arr = array(
                "menu" => $menu,
                "list" => $list,
                'page'=>$page,
                'pages'=>$pages,
                'typeList'=>$typeList,
                'sourceList'=>$sourceList,
                'searchInfo'=>array(
                    'type'=>$type,
                    'source'=>$source,
                    'searchText'=>$searchText
                )
            );
            $fw = $this->forward();
            $this->layout()->setVariable("topNavbar", $fw->dispatch('Application\Controller\Plugin', array(
                "action" => "topnavbar",
            )));
            $this->layout()->setVariable("adminHeader", $fw->dispatch('Application\Controller\Plugin', array(
                "action" => "adminHeader",
            )));
            $this->layout()->setVariable("footer", $fw->dispatch('Application\Controller\Plugin', array(
                "action" => "footer",
            )));
            $vm = new ViewModel($arr);
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    public function editPostAction()
    {
        $vm = null;
        try {
            /** @var $authSvr AuthenticationService */
            $authSvr = $this->getServiceLocator()->get('Session');
            if(!$authSvr->hasIdentity()){
                return $this->redirect()->toRoute('home');
            }
            $baseUrl = $this->getRequest()->getBaseUrl();
            $basePath = $this->getRequest()->getBasePath();
            $cf = new AdminMenu();
            $menu = $cf->getMenu('文章管理', $basePath);
            $postId = $this->params('postId');
            /** @var $postTable PostTable */
            $postTable = $this->getServiceLocator()->get('AdminMgr\Model\PostTable');
            $content = null;
            if($postId!==null){
                $content = $postTable->getPostContentById($postId);
            }
            $typeList = $postTable->getTypeList();
            $sourceList = $postTable->getSourceList();
            $arr = array(
                "menu" => $menu,
                "typeList" => $typeList,
                "sourceList" => $sourceList,
                "post"=>$content
            );
            /** @var $phpRenderer phpRenderer */
            $phpRenderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
            $phpRenderer->headScript()->appendFile($baseUrl . '/js/jquery.validate.min.js');
            $phpRenderer->headScript()->appendFile($baseUrl . '/js/ckeditor/ckeditor.js');
            $phpRenderer->headScript()->appendFile($baseUrl . '/js/ckeditor/adapters/jquery.js');
            $fw = $this->forward();
            $this->layout()->setVariable("topNavbar", $fw->dispatch('Application\Controller\Plugin', array(
                "action" => "topnavbar",
            )));
            $this->layout()->setVariable("adminHeader", $fw->dispatch('Application\Controller\Plugin', array(
                "action" => "adminHeader",
            )));
            $this->layout()->setVariable("footer", $fw->dispatch('Application\Controller\Plugin', array(
                "action" => "footer",
            )));
            $vm = new ViewModel($arr);
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;
    }

    public function addPostAction()
    {

        try {

            /** @var $authSvr AuthenticationService */
            $authSvr = $this->getServiceLocator()->get('Session');
            if(!$authSvr->hasIdentity()){
                return $this->redirect()->toRoute('home');
            }
            /** @var $request Request */
            $request = $this->getRequest();
            $response = $this->getResponse();
            $post = $request->getPost();
            $postid = $post->get('post_id');
            $title = $post->get('post_title');
            $author = $post->get('post_author');
            $type = $post->get('post_type');
            $source = $post->get('post_source');
            $summary = $post->get('post_summary');
            $remark = $post->get('post_remark');
            $content = $post->get('content');
            /** @var $postTable PostTable */
            $postTable = $this->getServiceLocator()->get('AdminMgr\Model\PostTable');
            if($postid!==""&&$postid!==null){
                $rs = $postTable->update(array(
                    'Title' => $title,
                    'Author'=>$author,
                    'Type' => $type,
                    'Date' => (new \DateTime())->format("Y-m-d H:i:s"),
                    'Source' => $source,
                    'Summary' => $summary,
                    'Remark' => $remark,
                    'SurfacePlot' => "",
                    'Content' => $content,
                ),array('ID'=>$postid));
            }else{
                $rs = $postTable->insert(array(
                    'Title' => $title,
                    'Author'=>$author,
                    'Type' => $type,
                    'Date' => (new \DateTime())->format("Y-m-d H:i:s"),
                    'Source' => $source,
                    'Summary' => $summary,
                    'Remark' => $remark,
                    'SurfacePlot' => "",
                    'Content' => $content,
                ));
                if($rs!=false){
                    $postid = $postTable->getLastInsertValue();
                }
            }
            if ($rs==1) {
                $response->setContent(Json::encode(array(
                    'state'=>true,
                    'message'=>array(
                        'message'=>'保存成功',
                        'postId'=>$postid
                    )
                )));
                return $response;
            }else{
                $response->setContent(Json::encode(array(
                    'state'=>false,
                    'code'=>'100010',
                    'message'=>array(
                        'message'=>'保存失败'
                    )
                )));
                return $response;
            }
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return array();

    }

    public function readPostAction()
    {

        $vm = null;
        try {
            $baseUrl = $this->getRequest()->getBaseUrl();
            $basePath = $this->getRequest()->getBasePath();
            $cf = new AdminMenu();
            $menu = $cf->getMenu('文章管理', $basePath);
            /** @var $postTable PostTable */
            $postTable = $this->getServiceLocator()->get('AdminMgr\Model\PostTable');
            $postId = $this->params('postId');
            $postContent = $postTable->getPostById($postId);
            if(!$postContent){
                return $this->redirect()->toRoute('home');
            }
            $arr = array(
                "menu" => $menu,
                'post' => $postContent
            );
            /** @var $phpRenderer phpRenderer */
            $phpRenderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
            $phpRenderer->headScript()->appendFile($baseUrl . '/js/jquery.validate.min.js');
            $fw = $this->forward();
            $this->layout()->setVariable("topNavbar", $fw->dispatch('Application\Controller\Plugin', array(
                "action" => "topnavbar",
            )));

            $this->layout()->setVariable("footer", $fw->dispatch('Application\Controller\Plugin', array(
                "action" => "footer",
            )));
            $vm = new ViewModel($arr);
        } catch (Exception $e) {
            $logger = new Logger();
            $logger->err($e->getCode(), $e->getMessage());
        }
        return $vm;


    }
}