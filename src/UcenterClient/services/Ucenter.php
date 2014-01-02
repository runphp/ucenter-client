<?php
namespace UcenterClient\Services;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use UcenterClient\Options\UcenterOptionsInterface;

class Ucenter implements ServiceManagerAwareInterface
{

    protected $options;

    public function getUser($ucCookie){
        return explode("\t", Plugin\Utils::ucAuthcode($ucCookie, 'DECODE'));
    }

    public function userLogin($username, $password, $isuid = 0, $checkques = 0, $questionid = '', $answer = '')
    {
        $options = $this->getOptions();
        $apiFunc = $options->getUcConnect() == 'mysql' ? 'ucApimysql' : 'ucApipost';
        $return = call_user_func(array(
            $this,
            $apiFunc
        ), 'user', 'login', array(
            'username' => $username,
            'password' => $password,
            'isuid' => $isuid,
            'checkques' => $checkques,
            'questionid' => $questionid,
            'answer' => $answer
        ));
        return $options->getUcConnect() == 'mysql' ? $return : Plugin\SerializerXml::unserialize($return);
    }

    public function userSynlogin($uid)
    {
        $uid = intval($uid);
        $config = new \Zend\Config\Config(include getcwd() . '/data/cache/ucenter_apps.php');
        if (count($config) > 1) {
            $return = $this->ucApipost('user', 'synlogin', array(
                'uid' => $uid
            ));
        } else {
            $return = '';
        }
        return $return;
    }

    public function userSynlogout()
    {
        $config = new \Zend\Config\Config(include getcwd() . '/data/cache/ucenter_apps.php');
        if (count($config) > 1) {
            $return = $this->ucApipost('user', 'synlogout', array());
        } else {
            $return = '';
        }
        return $return;
    }

    private function ucStripslashes($string)
    {
        defined('MAGIC_QUOTES_GPC') || define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
        if (MAGIC_QUOTES_GPC) {
            return stripslashes($string);
        } else {
            return $string;
        }
    }

    private function ucApipost($module, $action, $args = array())
    {
        $options = $this->getOptions();
        $s = $sep = '';
        foreach ($args as $k => $v) {
            $k = urlencode($k);
            if (is_array($v)) {
                $s2 = $sep2 = '';
                foreach ($v as $k2 => $v2) {
                    $k2 = urlencode($k2);
                    $s2 .= "$sep2{$k}[$k2]=" . urlencode($this->ucStripslashes($v2));
                    $sep2 = '&';
                }
                $s .= $sep . $s2;
            } else {
                $s .= "$sep$k=" . urlencode($this->ucStripslashes($v));
            }
            $sep = '&';
        }
        $postdata = $this->ucApiRequestdata($module, $action, $s);
        return Plugin\Utils::ucFopen2($options->getUcApi() . '/index.php', 500000, $postdata, '', TRUE, $options->getUcIp(), 20);
    }

    private function ucApimysql($model, $action, $args = array())
    {}

    private function ucApiRequestdata($module, $action, $arg = '', $extra = '')
    {
        $options = $this->getOptions();
        $input = $this->ucApiInput($arg);
        $post = "m=$module&a=$action&inajax=2&release=" . self::UC_CLIENT_RELEASE . "&input=$input&appid=" . $options->getUcAppId() . $extra;
        return $post;
    }

    private function ucApiInput($data)
    {
        $options = $this->getOptions();
        $s = urlencode(Plugin\Utils::ucAuthcode($data . '&agent=' . md5($_SERVER['HTTP_USER_AGENT']) . "&time=" . time(), 'ENCODE', $options->getUcKey()));
        return $s;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * get service options
     *
     * @return UserServiceOptionsInterface
     */
    public function getOptions()
    {
        if (! $this->options instanceof UcenterOptionsInterface) {
            $this->setOptions($this->getServiceManager()
                ->get('zfcuser_module_options'));
        }
        return $this->options;
    }

    /**
     * set service options
     *
     * @param UserServiceOptionsInterface $options
     */
    public function setOptions(UcenterOptionsInterface $options)
    {
        $this->options = $options;
    }
}