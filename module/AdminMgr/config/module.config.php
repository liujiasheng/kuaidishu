<?php
return array(
    'controllers' => array(
        'invokables' => array(
//            'AdminMgr\Controller\Skeleton' => 'AdminMgr\Controller\SkeletonController',
            'AdminMgr\Controller\Order' => 'AdminMgr\Controller\OrderController',
            'AdminMgr\Controller\GoodsClass' => 'AdminMgr\Controller\GoodsClassController',
            'AdminMgr\Controller\Post' => 'AdminMgr\Controller\PostController',
            'AdminMgr\Controller\OrderAssign' => 'AdminMgr\Controller\OrderAssignController',
            'AdminMgr\Controller\PhoneList' => 'AdminMgr\Controller\PhoneListController',
            'AdminMgr\Controller\Statistics' => 'AdminMgr\Controller\StatisticsController',
        ),
    ),
    'router' => array(
        'routes' => array(
//            'admin-mgr' => array(
//                'type'    => 'Literal',
//                'options' => array(
//                    // Change this to something specific to your module
//                    'route'    => '/skeleton',
//                    'defaults' => array(
//                        // Change this value to reflect the namespace in which
//                        // the controllers for your module are found
//                        '__NAMESPACE__' => 'AdminMgr\Controller',
//                        'controller'    => 'Skeleton',
//                        'action'        => 'index',
//                    ),
//                ),
//                'may_terminate' => true,
//                'child_routes' => array(
//                    // This route is a sane default when developing a module;
//                    // as you solidify the routes for your module, however,
//                    // you may want to remove it and replace it with more
//                    // specific routes.
//                    'default' => array(
//                        'type'    => 'Segment',
//                        'options' => array(
//                            'route'    => '/[:controller[/:action]]',
//                            'constraints' => array(
//                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
//                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
//                            ),
//                            'defaults' => array(
//                            ),
//                        ),
//                    ),
//                ),
//            ),
            'adminOrder' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/admin/order',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'AdminMgr\Controller',
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
            'adminGoodsClass' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/admin/goodsClass',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'AdminMgr\Controller',
                        'controller'    => 'GoodsClass',
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
            'adminPost' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/admin/post',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'AdminMgr\Controller',
                        'controller'    => 'Post',
                        'action'        => 'adminPost',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'add' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/add',
                            'constraints' => array(
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'AdminMgr\Controller',
                                'controller'    => 'Post',
                                'action'        => 'addPost',
                            ),
                        ),
                    ),
                    'edit' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/edit[/:postId]',
                            'constraints' => array(
                                'postId' => '[0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'AdminMgr\Controller',
                                'controller'    => 'Post',
                                'action'        => 'editPost',
                            ),
                        ),
                    ),
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:postId]',
                            'constraints' => array(
                                'postId' => '[0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'AdminMgr\Controller',
                                'controller'    => 'Post',
                                'action'        => 'readPost',
                            ),
                        ),
                    ),
                ),
            ),
            'readpost' => array(
                'type'    => 'Segment',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/post/[:postId]',
                    'constraints' => array(
                        'postId' => '[0-9_-]*',
                    ),
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'AdminMgr\Controller',
                        'controller'    => 'Post',
                        'action'        => 'readPost',
                    ),
                ),
                'may_terminate' => true,
            ),
            'adminPhoneList' => array(
                'type'      => 'Segment',
                'options'   => array(
                    'route'    => '/admin/phoneList',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'AdminMgr\Controller',
                        'controller'    => 'PhoneList',
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
            'workerAssign'=>array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/admin/orderAssign',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'AdminMgr\Controller',
                        'controller'    => 'OrderAssign',
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
            'adminStatistics' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/admin/statistics',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'AdminMgr\Controller',
                        'controller'    => 'Statistics',
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
            'layout/adminMgr'           => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            'AdminMgr' => __DIR__ . '/../view',
        ),
    ),
    'module_layouts' => array(
        'AdminMgr' =>  'layout/adminMgr',
    ),
);
