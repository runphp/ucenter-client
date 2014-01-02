<?php
namespace UcenterClient\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use UcenterClient\Services\UcenterInterface as Ui;
use UcenterClient\Services;
use Zend\Http\Header\SetCookie;
use Zend\Stdlib\ResponseInterface as Response;

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
        $cookie = new SetCookie('ucenter_auth', Services\Plugin\Utils::ucAuthcode($uid . "\t" . $username, 'ENCODE'), time() + 3600, '/', null, false, true);
        $this->getController()
            ->getResponse()
            ->getHeaders()
            ->addHeader($cookie)
            ->addHeaderLine('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        // 登录
        /* @var $options \UcenterClient\Options\ModuleOptions */
        $options = $this->getServiceLocator()->get('ucenter_client_module_options');
        $ucAuthAdapter = $this->getServiceLocator()->get($options->getUcAuthAdapter());
        $ucAuthService = $this->getServiceLocator()->get($options->getUcAuthService());
        // clear adapters
        $ucAuthAdapter->resetAdapters();
        $ucAuthService->clearIdentity();
        $result = $ucAuthAdapter->prepareForAuthentication($this->getController()->getRequest());

        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            return $result;
        }

        // authenticate
        $auth = $ucAuthService->authenticate($ucAuthAdapter);
        if (! $auth->isValid()) {
            return Ui::API_RETURN_FAILED;
        }
        return Ui::API_RETURN_SUCCEED;
    }

    public function synlogout($get, $post)
    {
        if (! Ui::API_SYNLOGOUT) {
            return Ui::API_RETURN_FORBIDDEN;
        }

        // note 同步登出 API 接口
        $cookie = new SetCookie('ucenter_auth', '', 0, '/', null, false, true);
        $this->getController()
            ->getResponse()
            ->getHeaders($cookie)
            ->addHeader($cookie)
            ->addHeaderLine('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
        return Ui::API_RETURN_SUCCEED;
    }

    public function getServiceLocator()
    {
        return $this->getController()->getServiceLocator();
    }
}
