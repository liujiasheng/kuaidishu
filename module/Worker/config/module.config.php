<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Worker\Controller\Worker' => 'Worker\Controller\WorkerController',
            'Worker\Controller\WorkerOrder' => 'Worker\Controller\WorkerOrderController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'worker' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/admin/worker',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Worker\Controller',
                        'controller'    => 'Worker',
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
            'workerOrder' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/worker',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'Worker\Controller',
                        'controller'    => 'WorkerOrder',
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
                    'getOrderList' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/getOrderList',
                            'defaults' => array(
                                '__NAMESPACE__' => 'Worker\Controller',
                                'controller'    => 'WorkerOrder',
                                'action'        => 'getOrderList',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'layout/Admin/Worker'           => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            'Worker' => __DIR__ . '/../view',
        ),
    ),
    'module_layouts' => array(
        'Worker' =>  'layout/Admin/Worker',
    ),
);
