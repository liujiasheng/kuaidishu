<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'AdminBase\Controller\AdminBase' => 'AdminBase\Controller\AdminBaseController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'admin-base' => array(
                'type'    => 'Literal',
                'options' => array(
                    // Change this to something specific to your module
                    'route'    => '/adminBase',
                    'defaults' => array(
                        // Change this value to reflect the namespace in which
                        // the controllers for your module are found
                        '__NAMESPACE__' => 'AdminBase\Controller',
                        'controller'    => 'AdminBase',
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
            'layout/adminBase' => __DIR__ . '/../../AdminBase/view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            'AdminBase' => __DIR__ . '/../view',
        ),
    ),
    'module_layouts' => array(
        'AdminBase' =>  'layout/adminBase',
    ),
);
