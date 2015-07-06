<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Home\Controller\Home' => 'Home\Controller\HomeController',
            'Home\Controller\Seller' => 'Home\Controller\SellerController',
            'Home\Controller\Cart' => 'Home\Controller\CartController',
            'Home\Controller\Search' => 'Home\Controller\SearchController',
            'Home\Controller\Weixin' => 'Home\Controller\WeixinController'
        ),
    ),
    'router' => array(
        'routes' => array(
            'homeSrc' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/home',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Home',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
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
            'homeSeller' => array(
                'type'      => 'Segment',
                'options'   => array(
                    'route'    => '/home/seller/:name',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Seller',
                        'action'        => 'index',
                    ),
                ),
            ),
            'homeSellerChild' => array(
                'type'      => 'Segment',
                'options'   => array(
                    'route'    => '/home/sellerCtrl',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Seller',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
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
            'homeCart' => array(
                'type'      => 'Segment',
                'options'   => array(
                    'route'    => '/home/cart',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Cart',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
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
            'homeOrder' => array(
                'type'      => 'Segment',
                'options'   => array(
                    'route'    => '/home/order',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Cart',
                        'action'        => 'order',
                    ),
                ),
            ),
            'homeOrdered' => array(
                'type'      => 'Segment',
                'options'   => array(
                    'route'    => '/home/ordered',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Cart',
                        'action'        => 'ordered',
                    ),
                ),
            ),
            'search' => array(
                'type'      => 'Segment',
                'options'   => array(
                    'route'    => '/search',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Search',
                        'action'        => 'index',
                    ),
                ),
            ),
            'homeAllSeller' => array(
                'type'      => 'Segment',
                'options'   => array(
                    'route'    => '/allseller',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Search',
                        'action'        => 'index',
                        'query' => array(
                            't' => 's',
                        ),
                    ),

                ),
            ),
            'homeAllGoods' => array(
                'type'      => 'Segment',
                'options'   => array(
                    'route'    => '/allgoods',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Search',
                        'action'        => 'allgoods',
                        'query' => array(
                            't' => 'g',
                        ),
                    ),

                ),
            ),
            'homeWeixin' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/weixin',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Weixin',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
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
    'view_manager' => array(
        'template_map' => array(
            'layout/Home'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/WeixinLayout'           => __DIR__ . '/../view/layout/weixinLayout.phtml',
            'layout/topNavbar'     => __DIR__ . '/../view/layout/topNavbar.phtml',
            'layout/topHeader'     => __DIR__ . '/../view/layout/topHeader.phtml',
            'layout/topNavigation'=> __DIR__ . '/../view/layout/topNavigation.phtml',
            'layout/topMenu'       => __DIR__ . '/../view/layout/topMenu.phtml',
            'layout/classMenu'    => __DIR__ . '/../view/layout/classMenu.phtml',
            'home/search/searchGoods' => __DIR__ .'/../view/layout/searchGoods.phtml',
            'home/search/searchSellers' => __DIR__ .'/../view/layout/searchSellers.phtml',
            'home/seller/sellerGoods' => __DIR__ .'/../view/layout/sellerGoods.phtml',
         ),
        'template_path_stack' => array(
            'Home' => __DIR__ . '/../view',
        ),
    ),
    'module_layouts' => array(
        'Home' => 'layout/Home'
    ),
);
