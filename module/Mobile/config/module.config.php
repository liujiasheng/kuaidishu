<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Mobile\Controller\Mobile' => 'Mobile\Controller\MobileController',
            'Mobile\Controller\Order' => 'Mobile\Controller\OrderController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'mobile' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/mobile',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Mobile\Controller',
                        'controller'    => 'Mobile',
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
            'mobileSeller' => array(
                'type'    => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/m/s/:name',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Mobile\Controller',
                        'controller'    => 'Mobile',
                        'action'        => 'seller',
                    ),
                ),
            ),
            'mobileOrder' => array(
                'type'    => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/m/order',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Mobile\Controller',
                        'controller'    => 'Order',
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
            'mobileOrdered' => array(
                'type'    => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/m/ordered',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Mobile\Controller',
                        'controller'    => 'Order',
                        'action'        => 'ordered',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'mobile/home/seller'           => __DIR__ . '/../view/layout/seller.phtml',
            'mobile/seller/sellerGoods'   => __DIR__ . '/../view/layout/sellerGoods.phtml',
            'mobile/seller/sellerMenu'   => __DIR__ . '/../view/layout/sellerMenu.phtml',
        ),
        'template_path_stack' => array(
            'Mobile' => __DIR__ . '/../view',
        ),
    ),
);
