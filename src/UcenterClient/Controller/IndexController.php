<?php
namespace UcenterClient\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use UcenterClient\Services\Plugin\SerializerXml;
use UcenterClient\Services\UcenterInterface;
use UcenterClient\Services;

class IndexController extends AbstractActionController
{

    /**
     * 客户端接口
     *
     * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
     */
    public function indexAction()
    {
        $response = $this->getResponse();
        $options = $this->getServiceLocator()->get('ucenter_client_module_options');
        $_DCACHE = $get = $post = array();
        $query = $this->getRequest()->getQuery();
        $code = $query->get('code');

        parse_str(Services\Plugin\Utils::ucAuthcode($code, 'DECODE', $options->getUcKey()), $get);
        defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
        if (MAGIC_QUOTES_GPC) {
            $get = $this->_stripslashes($get);
        }
        if (empty($get)) {
            return $response->setContent('Invalid Request');
        }
        $timestamp = time();
        if (isset($get['time']) && $timestamp - $get['time'] > 3600) {
            return $response->setContent('Authracation has expiried');
        }
        $post = SerializerXml::unserialize(file_get_contents('php://input'));
        if (isset($get['action']) && in_array($get['action'], array(
            'test',
            'deleteuser',
            'renameuser',
            'gettag',
            'synlogin',
            'synlogout',
            'updatepw',
            'updatebadwords',
            'updatehosts',
            'updateapps',
            'updateclient',
            'updatecredit',
            'getcreditsettings',
            'updatecreditsettings'
        ))) {
            return $response->setContent($this->ucNote()
                ->$get['action']($get, $post));
        } else {
            return $response->setContent(UcenterInterface::API_RETURN_FAILED);
        }
    }

    private function _stripslashes($string)
    {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = $this->_stripslashes($val);
            }
        } else {
            $string = stripslashes($string);
        }
        return $string;
    }
}