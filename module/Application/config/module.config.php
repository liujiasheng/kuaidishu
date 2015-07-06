<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Home\Controller\Home',
                        'action'     => 'index',
                    ),
                ),
            ),
            //sitemap
            'sitemap' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/sitemap.xml',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'sitemap',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
            'Application\Cache' =>'Zend\Cache\Service\StorageCacheFactory',
            'Navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        ),
    ),
    'navigation' => array(
        'default' => array(
            array(
                "label" => "Home",
                "route" => "home",
                "lastmod" => "2014-08-20",
                "changefreq" => "weekly",
                "priority" => "1.0",
            ),
            array(
                "label" => "Login",
                "route" => "userlogin",
                "lastmod" => "2014-08-20",
                "changefreq" => "weekly",
                "priority" => "0.8",
            ),
            array(
                "label" => "Register",
                "route" => "register",
                "lastmod" => "2014-08-20",
                "changefreq" => "weekly",
                "priority" => "0.8",
            ),
            array(
                "label" => "AllSeller",
                "route" => "homeAllSeller",
                "lastmod" => "2014-08-20",
                "changefreq" => "weekly",
                "priority" => "0.8",
            ),
            array(
                "label" => "AllGoods",
                "route" => "homeAllGoods",
                "lastmod" => "2014-08-20",
                "changefreq" => "weekly",
                "priority" => "0.8",
            ),

//            array(
//                "label" => "",
//                "route" => "",
//                "lastmod" => "2014-08-20",
//                "changefreq" => "",
//                "priority" => "",
//            ),
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Plugin' => 'Application\Controller\PluginController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'default'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'plugin/footer'           => __DIR__ .'/../view/layout/footer.phtml',
            'plugin/adminHeader'           => __DIR__ .'/../view/layout/adminHeader.phtml',
            'sitemap/sitemapxml'     => __DIR__ .'/../view/layout/sitemapLayout.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
