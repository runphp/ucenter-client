<?php
namespace UcenterClient\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use UcenterClient\Services\UcenterInterface;

class UcNote extends AbstractPlugin
{

    function test($get, $post)
    {
        return UcenterInterface::API_RETURN_SUCCEED;
    }
}