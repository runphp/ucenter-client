<?php
namespace UcenterClient\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use UcenterClient\Services\Plugin\SerializerXml;
use UcenterClient\Services\UcenterInterface;

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

        parse_str($this->_authcode($code, 'DECODE', $options->getUcKey()), $get);
        defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
        if (MAGIC_QUOTES_GPC) {
            $get = $this->stripslashes($get);
        }
        $timestamp = time();
        if ($timestamp - $get['time'] > 3600) {
            exit('Authracation has expiried');
        }
        if (empty($get)) {
            exit('Invalid Request');
        }
        $action = $get['action'];
        $post = SerializerXml::unserialize(file_get_contents('php://input'));
        if(in_array($get['action'], array('test', 'deleteuser', 'renameuser', 'gettag', 'synlogin', 'synlogout', 'updatepw', 'updatebadwords', 'updatehosts', 'updateapps', 'updateclient', 'updatecredit', 'getcreditsettings', 'updatecreditsettings'))) {
            exit($this->ucNote()->$get['action']($get, $post));
        }else {
            exit(UcenterInterface::API_RETURN_FAILED);
        }
    }

    private function _authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;

        $key = md5($key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), - $ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i ++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i ++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i ++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    private function stripslashes($string)
    {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = _stripslashes($val);
            }
        } else {
            $string = stripslashes($string);
        }
        return $string;
    }
}