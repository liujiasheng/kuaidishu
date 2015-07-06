<?php
/**
 * Created by PhpStorm.
 * User: james
 * Date: 14-8-30
 * Time: 下午5:21
 */

namespace AdminMgr\Controller;


use AdminBase\Model\AdminMenu;
use AdminMgr\Model\WorkerOrderTable;
use Application\Model\Logger;
use Exception;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Sql\Where;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class OrderAssignController extends AbstractActionController{

    public function indexAction(){
        $vm = null;
        try {
            /** @var $phpRenderer phpRenderer */
            $phpRenderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
            $phpRenderer->headLink()->appendStylesheet('/css/bootstrap-combined.no-icons.min.css',null,null,null);
            /** @var $authSvr AuthenticationService */
//            $authSvr = $this->getServiceLocator()->get('Session');
//            if(!$authSvr->hasIdentity()){
//                return $this->redirect()->toRoute('home');
//            }
            /** @var $request Request */
            $request = $this->getRequest();
            $basePath = $this->getRequest()->getBasePath();
            $cf = new AdminMenu();
            $menu = $cf->getMenu('订单指派管理', $basePath);

            $page = trim($request->getQuery('page'));
            $workInfo = trim($request->getQuery('w'));
            $sellerName = trim($request->getQuery('s'));
            $orderInfo = trim($request->getQuery('o'));
            $orderState = trim($request->getQuery('os'));
            if(!is_numeric($page)){
                $page = 1;
            }
            $page = $page<1?1:$page;

            $limit = 10;
            $start = ($page-1)*$limit;

            /** @var $orderWorkerTable WorkerOrderTable */
            $orderWorkerTable = $this->getServiceLocator()->get('AdminMgr\Model\WorkerOrderTable');
            $where = new Where();
            if($workInfo!==null&&$workInfo!==""){
                $where->like("s.Name","%".$workInfo."%")
                    ->OR->like("s.Username","%".$workInfo."%");
            }

            if($sellerName!==null&&$sellerName!==""){
                $where->like("v.Name","%".$sellerName."%");
            }

            if($orderInfo!==null&&$orderInfo!==""){
                $where->AND->like("t.ID","%".$orderInfo."%");
            }

            if($orderState!==null&&$orderState!==""){
                if($orderState!=="0")
                $where->AND->equalTo("t.State",$orderState);
            }
//            $where->AND->equalTo("t.State",$orderState)
//                ->like("v.Name","%".$sellerName."%")
//                ->AND->like("s.Name","%".$workInfo."%")
//                ->OR->like("s.ID","%".$workInfo."%")
//                ->AND->like("t.ID","%".$orderInfo."%")
            ;
            $list = $orderWorkerTable->searchOrderList($where,$start,$limit);

//            /** @var $postTable PostTable */
//            $postTable = $this->getServiceLocator()->get('AdminMgr\Model\PostTable');
//            if($type!==null&&$type!==""){
//                $where->equalTo("Type",$type);
//            }
//            if($source!==null&&$source!==""){
//                $where->equalTo("Source",$source);
//            }
//            if($searchText!==null&&$searchText!==""){
//                $where->OR->like('Title',"%".$searchText."%")
//                    ->OR->like("Summary", "%".$searchText."%")
//                    ->OR->like("Remark", "%".$searchText."%");
//            }
            $pages = ceil($orderWorkerTable->searchOrderListCount($where)/$limit);
            $pages = $pages==0?1:$pages;
            $page = $page>$pages?$pages:$page;

            $arr = array(
                "menu" => $menu,
                "list" => $list,
                'page'=>$page,
                'pages'=>$pages,
                'searchInfo'=>array(
                    'w'=>$workInfo,
                    's'=>$sellerName,
                    'o'=>$orderInfo,
                    'os'=>$orderState,
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
} 