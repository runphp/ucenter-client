<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'UcenterClientIndex' => 'UcenterClient\Controller\IndexController'
        )
    ),
    'router' => array(
        'routes' => array(
            'ucenterclient' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/ucenter/api/uc.php',
                    'defaults' => array(
                        'controller' => 'UcenterClientIndex',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true
            )
        )
    )
);
