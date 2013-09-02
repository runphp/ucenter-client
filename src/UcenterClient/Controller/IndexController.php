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
        $options = $this->getServiceLocator()->get('ucenter_client_module_options');
        $_DCACHE = $get = $post = array();
        $query = $this->getRequest()->getQuery();
        $code = $query->get('code');

        parse_str(Services\Plugin\Utils::ucAuthcode($code, 'DECODE', $options->getUcKey()), $get);
        defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
        if (MAGIC_QUOTES_GPC) {
            $get = $this->stripslashes($get);
        }
        if (empty($get)) {
            exit('Invalid Request');
        }
        $timestamp = time();
        if ($timestamp - $get['time'] > 3600) {
            exit('Authracation has expiried');
        }
        $post = SerializerXml::unserialize(file_get_contents('php://input'));
        if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {
            exit($this->ucNote()->$get['action']($get, $post));
        }else {
            exit(UcenterInterface::API_RETURN_FAILED);
        }
    }

    private function stripslashes($string)
    {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = stripslashes($val);
            }
        } else {
            $string = stripslashes($string);
        }
        return $string;
    }
}