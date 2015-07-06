<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-7-16
 * Time: 下午4:30
 */

namespace Application\Model;


use Authenticate\AssistantClass\SessionInfoKey;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;

class Intercepter{


    /**
     * @param $controller
     * @param $roles array
     * @return bool
     */
    public function hasAuthority($controller,$roles){
        $sl =  $controller->getServiceLocator();
        $authSvr =$sl->get('Session');
        if(!$authSvr->hasIdentity()){
            return false;
        }
        $entity = $authSvr->getIdentity();
        $role = $entity[SessionInfoKey::role];
        $flag = in_array($role, $roles);
        return $flag;
    }

    public function Intercerpt($controller){
        $flag = false;
            $controllerClass = get_class($controller);
            $actionName = $controller->params('action');


            $array = (new IntercepterArray())->getIntercerpterArray();
            if(array_key_exists($controllerClass, $array) &&
                array_key_exists( strtolower($actionName), $array[$controllerClass])){
                $flag = $this->hasAuthority($controller, $array[$controllerClass][strtolower($actionName)]);
            }else{
                return true;
            }
            if(!$flag){
                $dirs = (new IntercepterArray())->getRedirectUrlArray();
                if(array_key_exists($controllerClass, $dirs) &&
                    array_key_exists( strtolower($actionName), $dirs[$controllerClass])){
                    $url = $dirs[$controllerClass][strtolower($actionName)];
                    $controller->redirect()->toUrl($url);
                }else{
//                    $controller->redirect()->toUrl('/');
                    $controller->getResponse()->setContent(Json::encode(array(
                        "state" => false,
                        "code" => -10000,
                        "message" => ""
                    )));
                }
            }
        return $flag;
    }

} 