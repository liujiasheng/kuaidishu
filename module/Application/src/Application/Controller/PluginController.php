<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-23
 * Time: 下午7:52
 */

namespace Application\Controller;


use Application\Model\WeChatCallback;
use Authenticate\AssistantClass\SessionInfoKey;
use Authenticate\AssistantClass\UserType;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class PluginController extends AbstractActionController{

    private function getTopNavbarHtml(){
        $authSvr = $this->getServiceLocator()->get('Session');
        $name = "guest";
        $role = -1;
        if($authSvr->hasIdentity()){
            $identity = $authSvr->getIdentity();
            $role = $identity[SessionInfoKey::role];
            $entity = null;
            switch($role){
                case UserType::Admin:
                case UserType::User:
                $entity = $this->getServiceLocator()->get('Authenticate\Model\UserTable')->getUserById($identity[SessionInfoKey::ID]);
                    /** @var $name \Application\Entity\User */
                    if($entity) $name = $entity->getNickname();
                    break;
                case UserType::Seller:
                    $entity = $this->getServiceLocator()->get('Authenticate\Model\SellerTable')->getSellerById($identity[SessionInfoKey::ID]);
                    if($entity) $name = $entity->getName();
                    break;
                case UserType::Worker:
                    $entity = $this->getServiceLocator()->get('Authenticate\Model\WorkerTable')->getWorkerById($identity[SessionInfoKey::ID]);
                    if($entity) $name = $entity->getName();
                    break;
            }
            if(!$entity){
                $rsp = $this->forward()->dispatch('Authenticate\Controller\Authenticate', array(
                    'action' => 'logout',
                    'innerCall' => true,
                ));
            }
        }

        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $topVm = new ViewModel(array(
            "isLogin" => $authSvr->hasIdentity(),
            "role" => $role,
            "name" => $name,
        ));
        $topVm->setTemplate("layout/topNavbar");
        $topHtml = $viewRender->render($topVm);
        return $topHtml;
    }

    public function topnavbarAction(){
        return $this->getTopNavbarHtml();
    }

    private function getTopHeaderHtml(){
        $authSvr = $this->getServiceLocator()->get('Session');
        $role = -1;
        if($authSvr->hasIdentity()){
            $identity = $authSvr->getIdentity();
            $role = $identity[SessionInfoKey::role];
        }

        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $topVm = new ViewModel(array(
            "isLogin" => $authSvr->hasIdentity(),
            "role" => $role,
        ));
        $topVm->setTemplate("layout/topHeader");
        $topHtml = $viewRender->render($topVm);
        return $topHtml;
    }

    public function topheaderAction(){
        return $this->getTopHeaderHtml();
    }

    private function getTopNavigationHtml(){
        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        //check if is home page
        $requestUri = $this->getRequest()->getRequestUri();
        $baseUrl = $this->getRequest()->getBaseUrl()."/";
        $arr = ( ($requestUri === $baseUrl) || (strlen($requestUri)>1 && $requestUri[1]=='?') )?
            array(
                "isHome" => true,
            ):array(
                "isHome" => false,
                "classMenu" => $this->getClassMenuHtml(false),
            );
        //end check
        $topVm = new ViewModel($arr);
        $topVm->setTemplate("layout/topNavigation");
        $topHtml = $viewRender->render($topVm);
        return $topHtml;
    }

    public function classmenuAction(){
       return $this->getClassMenuHtml();
    }

    private function getClassMenuHtml($isHome = false){
        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $vm = new ViewModel(array(
            "isHome" => $isHome
        ));
        $vm->setTemplate("layout/classMenu");
        $classMenu = $viewRender->render($vm);
        return $classMenu;
    }

    public function topnavigationAction(){
        return $this->getTopNavigationHtml();
    }

    private function getTopMenuHtml(){
        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $topVm = new ViewModel(array(
            "classMenu" => $this->getClassMenuHtml(true),
        ));
        $topVm->setTemplate("layout/topMenu");
        $topHtml = $viewRender->render($topVm);
        return $topHtml;
    }

    public function topmenuAction(){
        return $this->getTopMenuHtml();
    }

    public function footerAction(){
        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $footer = new ViewModel();
        $footer->setTemplate("plugin/footer");
        $footerHtml = $viewRender->render($footer);
        return $footerHtml;
    }

    public function adminHeaderAction(){
        $viewRender = $this->getServiceLocator()->get("ViewRenderer");
        $adminHeader = new ViewModel();
        $adminHeader->setTemplate("plugin/adminHeader");
        $footerHtml = $viewRender->render($adminHeader);
        return $footerHtml;
    }


} 