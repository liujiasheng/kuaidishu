<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Authenticate for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Authenticate;

use Authenticate\AssistantClass\SessionInfoKey;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\View\Helper\Placeholder\Container;

class Module implements AutoloaderProviderInterface
{
    protected $logger;

    public function __construct()
    {
        $this->logger = new \Application\Model\Logger();
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    // if we're in a namespace deeper than one level we need to fix the \ in the path
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__),
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

//    public function onBootstrap(MvcEvent $e)
//    {
//        // You may not need to do this if you're doing it elsewhere in your
//        // application
//        $eventManager        = $e->getApplication()->getEventManager();
//        $moduleRouteListener = new ModuleRouteListener();
//        $moduleRouteListener->attach($eventManager);
//
//    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Authenticate\Model\AdminTable' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $table = new Model\AdminTable($dbAdapter);
                        return $table;
                    },
                'Authenticate\Model\UserTable' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $table = new Model\UserTable($dbAdapter);
                        return $table;
                    },
                'Authenticate\Model\WorkerTable' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $table = new Model\WorkerTable($dbAdapter);
                        return $table;
                    },
                'Authenticate\Model\SellerTable' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $table = new Model\SellerTable($dbAdapter);
                        return $table;
                    },
                'Authenticate\Model\CopLoginTable' => function ($sm) {
                        $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                        $table = new Model\CopLoginTable($dbAdapter);
                        return $table;
                    },
                'Authenticate\getCaptcha' => function ($sm) {

                        try {
                            $session = new \Zend\Session\Container("Captcha");

                            // Originating request:
                            $captcha = new \Zend\Captcha\Image(array(
                                'name' => 'kuaidishu',
                                'wordLen' => 4,
                                'timeout' => 10,
                            ));
                            $fontPath = __DIR__ . '/src/Authenticate/Controller/arial.ttf';
                            $captcha->setFont($fontPath);

                            $captcha->setFontSize(40);
                            $captcha->setHeight(100);
                            $captcha->setWidth(200);
                            $captcha->setTimeout(60);
                            $captcha->setExpiration(60);
                            //                    $captcha->setSession(new \Zend\Session\Container("captcha"));
                            if (isset($_SERVER['Captcha_Path'])) {
                                $captcha->setImgDir($_SERVER['Captcha_Path'] . '/' . $captcha->getImgDir());
                            }
                            $captcha->setImgDir('public/captcha/');
                            $captcha->setImgUrl('/captcha/');
                            $captcha->generate();
//                        $captcha->setImgDir($projectPath . '/' . $captcha->getImgDir());
                            $session->offsetSet(SessionInfoKey::captchaId, $captcha->getId());
                            $session->offsetSet('captcha', $captcha->getWord());
                            return $captcha;
                        } catch (\Exception $e) {
                            $this->logger->info($e->getCode(), $e->getMessage());

                        }
                    }
            ),
        );
    }
}
