<?php
namespace UcenterClient;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    public function getConfig($env = null)
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'ucenter_client_module_options' => function ($sm)
                {
                    $config = $sm->get('Config');
                    return new Options\ModuleOptions(isset($config['ucenter']) ? $config['ucenter'] : array());
                },
                'ucenter_service' => function ($sm)
                {
                    $service = new Services\Ucenter();
                    $service->setOptions($sm->get('ucenter_client_module_options'));
                    return $service;
                }
            )
        );
    }

    public function getControllerPluginConfig()
    {
        return array(
            'invokables' => array(
                'ucNote' => 'UcenterClient\Controller\Plugin\UcNote'
            )
        );
    }
}
