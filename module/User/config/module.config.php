<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'User\Controller\User' => 'User\Controller\UserController',
            'User\Controller\UserInfo' => 'User\Controller\UserInfoController',
            'User\Controller\UserOrderMGR' => 'User\Controller\UserOrderMGRController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'user' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/admin/user',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'User\Controller',
                        'controller'    => 'User',
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
            'userinfo' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/user/userinfo',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'User\Controller',
                        'controller'    => 'UserInfo',
                        'action'        => 'userinfopage',
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
            'modifyPassword' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/user/modifypassword',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'User\Controller',
                        'controller'    => 'UserInfo',
                        'action'        => 'userPwdPage',
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
            'deliveryAddress' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/user/deliveryAddress',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'User\Controller',
                        'controller'    => 'UserInfo',
                        'action'        => 'userDeliveryAddressPage',
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
            'userOrderManagement' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/user/orderManage',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'User\Controller',
                        'controller'    => 'UserOrderMGR',
                        'action'        => 'userOrderManagePage2',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // This route is a sane default when developing a module;
                    // as you solidify the routes for your module, however,
                    // you may want to remove it and replace it with more
                    // specific routes.
                    'detail'=>array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/detail/:id',
                            'constraints' => array(
//                                'id' => '[0-9]{10,15}',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller'    => 'UserOrderMGR',
                                'action'=>'userOrderManageDetailPage'
                            ),
                        )
                    ),
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:action]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'User\Controller',
                                'controller'    => 'UserOrderMGR',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'layout/Admin/User'           => __DIR__ . '/../view/layout/layout.phtml',
            'UserInfoController'           => __DIR__ . '/../view/layout/userInfoLayout.phtml',
            'localNavTemplate'           => __DIR__ . '/../view/layout/localNav.phtml',
            'UserModuleFooterTemplate' => __DIR__ . '/../view/layout/footer.phtml',
            'UserContent'           => __DIR__ . '/../view/layout/userContent.phtml',
            'UserMenu'           => __DIR__ . '/../view/layout/userMenu.phtml',
            'UserMainPageTemplate'           => __DIR__ . '/../view/layout/userMainPageTemplate.phtml',
            'UserDeliveryAddressTemplate'           => __DIR__ . '/../view/user/user-info/delivery.phtml',
            'UserModifyPasswordTemplate'           => __DIR__ . '/../view/user/user-info/modifyPassword.phtml',
            'UserInfoContent'           => __DIR__ . '/../view/user/user-info/userInfoContent.phtml',
            'UserInfoDeliveryAddressTable'           => __DIR__ . '/../view/user/user-info/deliverAddressTable.phtml',
            'UserInfoDeliveryAddressBox'           => __DIR__ . '/../view/user/user-info/deliveryBox.phtml',
            'UserInfoOrderManagementContent'    => __DIR__ . '/../view/user/user-info/orderManagement.phtml',
            'UserInfoOrderManagementContent2'    => __DIR__ . '/../view/user/user-info/orderManagement2.phtml',
            'UserInfoOrderManagementDetailContent'=> __DIR__ . '/../view/user/user-info/orderMGRdetail.phtml',

        ),
        'template_path_stack' => array(
            'User' => __DIR__ . '/../view',
        ),
    ),
    'module_layouts' => array(
//        'User' =>  'default',
        'User'         =>  'layout/Admin/User',
        'UserInfoController'    =>  'UserInfoController',
    ),
);
