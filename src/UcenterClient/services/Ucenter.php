<?php
namespace UcenterClient\Services;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use UcenterClient\Options\UcenterOptionsInterface;

class Ucenter extends EventProvider implements ServiceManagerAwareInterface
{
    protected $options;

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
        return $this->ucFopen2($options->getUcApi() . '/index.php', 500000, $postdata, '', TRUE, $options->getUcIp(), 20);
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
        $s = urlencode($this->ucAauthcode($data . '&agent=' . md5($_SERVER['HTTP_USER_AGENT']) . "&time=" . time(), 'ENCODE', $options->getUcKey()));
        return $s;
    }

    private function ucAauthcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        $ckey_length = 4;

        $key = md5($key ? $key : UC_KEY);
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

    function ucFopen2($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE)
    {
        $__times__ = isset($_GET['__times__']) ? intval($_GET['__times__']) + 1 : 1;
        if ($__times__ > 2) {
            return '';
        }
        $url .= (strpos($url, '?') === FALSE ? '?' : '&') . "__times__=$__times__";
        return $this->ucFopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block);
    }

    function ucFopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE)
    {
        $return = '';
        $matches = parse_url($url);
        ! isset($matches['host']) && $matches['host'] = '';
        ! isset($matches['path']) && $matches['path'] = '';
        ! isset($matches['query']) && $matches['query'] = '';
        ! isset($matches['port']) && $matches['port'] = '';
        $host = $matches['host'];
        $path = $matches['path'] ? $matches['path'] . ($matches['query'] ? '?' . $matches['query'] : '') : '/';
        $port = ! empty($matches['port']) ? $matches['port'] : 80;
        if ($post) {
            $out = "POST $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            // $out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= 'Content-Length: ' . strlen($post) . "\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cache-Control: no-cache\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
            $out .= $post;
        } else {
            $out = "GET $path HTTP/1.0\r\n";
            $out .= "Accept: */*\r\n";
            // $out .= "Referer: $boardurl\r\n";
            $out .= "Accept-Language: zh-cn\r\n";
            $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out .= "Host: $host\r\n";
            $out .= "Connection: Close\r\n";
            $out .= "Cookie: $cookie\r\n\r\n";
        }

        if (function_exists('fsockopen')) {
            $fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
        } elseif (function_exists('pfsockopen')) {
            $fp = @pfsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
        } else {
            $fp = false;
        }

        if (! $fp) {
            return '';
        } else {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            @fwrite($fp, $out);
            $status = stream_get_meta_data($fp);
            if (! $status['timed_out']) {
                while (! feof($fp)) {
                    if (($header = @fgets($fp)) && ($header == "\r\n" || $header == "\n")) {
                        break;
                    }
                }

                $stop = false;
                while (! feof($fp) && ! $stop) {
                    $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                    $return .= $data;
                    if ($limit) {
                        $limit -= strlen($data);
                        $stop = $limit <= 0;
                    }
                }
            }
            @fclose($fp);
            return $return;
        }
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