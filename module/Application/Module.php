<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\Model\Intercepter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        $e->getApplication()->getServiceManager()->get('translator');
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $sm = $e->getApplication()->getServiceManager();
        $routes = array('UserInfo-test', 'foo/baz');
        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractController', 'dispatch', function($e) {


            $controller      = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleName = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $controllerName = substr(mb_strrchr($controllerClass,'\\'), 1);
            $config          = $e->getApplication()->getServiceManager()->get('config');
            if (isset($config['module_layouts'][$controllerName])) {
                $controller->layout($config['module_layouts'][$controllerName]);
            }elseif(isset($config['module_layouts'][$moduleName])){
                $controller->layout($config['module_layouts'][$moduleName]);
            }


            $isInnerCall = $controller->Params('innerCall');
            if( $isInnerCall ){

            }else{
                //Intercepter
                $intercerpt = new Intercepter();
                $flag = $intercerpt->Intercerpt($controller);
                if(!$flag){
                    $e->getApplication()->getEventManager()->trigger(MvcEvent::EVENT_FINISH, $e);
                    exit();
                }
            }

        }, 100);

//        $eventManager->attach(MvcEvent::EVENT_ROUTE, function($e) use ($sm) {
////            $route = $e->getRouteMatch()->getMatchedRouteName();
//            /** @var $cache \Zend\Cache\Storage\StorageInterface */
//            $cache = $sm->get('Application\Cache');
//            $key   = 'route-cache';
////            $cache->removeItem($key);
//            if ($cache->hasItem($key)) {
//                // Handle response
//                $content  = $cache->getItem($key);
//
//                $response = $e->getResponse();
//                $response->setContent($content);
//
//                return $response;
//            }
//        }, -1000); // Low, then routing has happened
//
//
//
//
//
//        $eventManager->attach(MvcEvent::EVENT_FINISH, function($e) use ($sm) {
////            $route = $e->getRouteMatch()->getMatchedRouteName();
//            $cache = $sm->get('Application\Cache');
////            if (!in_array($route, $routes)) {
////                return;
////            }
//            $key   = 'route-cache';
//            if ($cache->hasItem($key)) {
//                return;
//            }
//            $response = $e->getResponse();
//            $content  = $response->getContent();
//            if(!$content){
//                return;
//            }
//
//
//            $cache->setItem($key, $content);
//        }, -1000); // Late, then rendering has happened

        //when dispatch error
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this,'onDispatchError'), 100);

    }


    public function onDispatchError(MvcEvent $e) {
        $vm = $e->getViewModel();
        $vm->setTemplate('layout/Home');
    }


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__.'/src/autoload_classmap.php',
            ),
        );
    }
    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Session' => function($sm) {
                        $auSvr = new AuthenticationService();
                        $session = new Session("Authenticate");
                        //Session name Authenticate
                        $auSvr->setStorage($session);
                        return $auSvr;
                    },


            ),
        );
    }



}
