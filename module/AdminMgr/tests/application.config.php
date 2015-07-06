<?php
return array(
    'modules' => array(
        'Application',
        'User',
        'AdminBase',
        'Seller',
        'Administrator',
        'Worker',
        'Authenticate',
        'Goods',
        'Home',
        'AdminMgr',
    ),
    'module_listener_options' => array(
        'config_glob_paths'    => array(
            './../../../config/autoload/{,*.}{global,local}.php',
        ),
        'module_paths' => array(
            './module',
            './vendor',
        ),
    ),
);
