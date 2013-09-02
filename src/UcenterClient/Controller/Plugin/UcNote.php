<?php
namespace UcenterClient\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use UcenterClient\Services\UcenterInterface as Ui;
use UcenterClient\Services;

class UcNote extends AbstractPlugin
{

    public function test($get, $post)
    {
        return Ui::API_RETURN_SUCCEED;
    }

    public function updateapps($get, $post)
    {
        if (! Ui::API_UPDATEAPPS) {
            return Ui::API_RETURN_FORBIDDEN;
        }

        // note 写 app 缓存文件
        $cachefile = getcwd() . '/data/cache/ucenter_apps.php';
        $config = new \Zend\Config\Config($post, true);
        $writer = new \Zend\Config\Writer\PhpArray();
        $writer->toFile($cachefile, $config);

        // TODO note 写配置文件

        return Ui::API_RETURN_SUCCEED;
    }

    public function updatepw($get, $post)
    {
        if (! Ui::API_UPDATEPW) {
            return Ui::API_RETURN_FORBIDDEN;
        }
        $username = $get['username'];
        $password = $get['password'];
        return Ui::API_RETURN_SUCCEED;
    }

    function deleteuser($get, $post)
    {
        $uids = $get['ids'];
        if (! Ui::API_DELETEUSER) {
            exit(Ui::API_RETURN_FORBIDDEN);
        }
        return Ui::API_RETURN_SUCCEED;
    }

    public function synlogin($get, $post)
    {
        $uid = $get['uid'];
        $username = $get['username'];
        if (! Ui::API_SYNLOGIN) {
            return Ui::API_RETURN_FORBIDDEN;
        }

        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        $this->setcookie('ucenter_auth', Services\Plugin\Utils::ucAuthcode($uid . "\t" . $username, 'ENCODE'));
    }

    public function synlogout($get, $post)
    {
        if (! Ui::API_SYNLOGOUT) {
            return Ui::API_RETURN_FORBIDDEN;
        }

        // note 同步登出 API 接口
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        $this->setcookie('ucenter_auth', '', - 86400 * 365);
    }

    private function setcookie($var, $value, $life = 0, $prefix = 1)
    {
        global $cookiepre, $cookiedomain, $cookiepath, $timestamp, $_SERVER;
        setcookie(($prefix ? $cookiepre : '') . $var, $value, $life ? $timestamp + $life : 0, $cookiepath, $cookiedomain, $_SERVER['SERVER_PORT'] == 443 ? 1 : 0);
    }
}