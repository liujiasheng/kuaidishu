<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Seller\Controller\Seller' => 'Seller\Controller\SellerController',
            'Seller\Controller\SellerGoods' => 'Seller\Controller\SellerGoodsController',
            'Seller\Controller\SellerOrderMgr' => 'Seller\Controller\SellerOrderMgrController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'seller' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/admin/seller',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Seller\Controller',
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
            'sellerGoodsMgr' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/seller/goodsMgr',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Seller\Controller',
                        'controller'    => 'SellerGoods',
                        'action'        => 'goodsMgr',
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
            'sellerOrderMgr' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/seller/orderMgr',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Seller\Controller',
                        'controller'    => 'SellerOrderMgr',
                        'action'        => 'orderMgr',
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
            'layout/Admin/Seller'           => __DIR__ . '/../view/layout/layout.phtml',
            'SellerContent'=>__DIR__ . '/../view/layout/sellerContent.phtml',
            'GoodsContent'=>__DIR__ . '/../view/seller/seller/goodsContent.phtml',
            'SellerMenu'=>__DIR__ . '/../view/layout/sellerMenu.phtml',
            'SellerOrderContent'=>__DIR__ . '/../view/seller/seller/sellerOrderContent.phtml',
        ),
        'template_path_stack' => array(
            'Seller' => __DIR__ . '/../view',
        ),
    ),
    'module_layouts' => array(
        'Seller' =>  'layout/Admin/Seller',
    ),
);
