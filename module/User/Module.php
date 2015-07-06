<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/User for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace User;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements AutoloaderProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
		    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig() {
    	return array(
    			'factories' => array(
    					'User\Model\UserTable' => function($sm) {
    						$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    						$table = new Model\UserTable($dbAdapter);
    						return $table;
    					},
                    'User\Model\DeliveryAddressTable'=>function($sm){
                            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                            $table = new Model\DeliveryAddressTable($dbAdapter);
                            return $table;
                        },
                    'User\Model\UserOrderTable'=>function($sm){
                            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                            $table = new Model\UserOrderTable($dbAdapter);
                            return $table;
                        },
                    'User\Model\UserOrderDetailTable'=>function($sm){
                            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                            $table = new Model\UserOrderDetailTable($dbAdapter);
                            return $table;
                        },
                    'User\Model\WorkerOrderTable'=>function($sm){
                            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                            $table = new Model\WorkerOrderTable($dbAdapter);
                            return $table;
                        },
    			),
    	);
    }
    
    public function onBootstrap(MvcEvent $e)
    {
        // You may not need to do this if you're doing it elsewhere in your
        // application
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }


}
