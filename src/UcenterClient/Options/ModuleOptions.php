<?php
namespace UcenterClient\Options;

use Zend\Stdlib\AbstractOptions;

class ModuleOptions extends AbstractOptions implements UcenterOptionsInterface
{

    protected $ucConnect = null;

    protected $ucKey = '';

    protected $ucApi = 'http://bbs.runphp.net/uc_server';

    protected $ucCharset = 'utf-8';

    protected $ucIp = '';

    protected $ucAppId = 0;

    protected $ucPpp = 0;

    public function getUcConnect()
    {
        return $this->ucConnect;
    }

    public function setUcConnect($ucConnect)
    {
        $this->ucConnect = $ucConnect;
        return $this;
    }

    public function getUcKey()
    {
        return $this->ucKey;
    }

    public function setUcKey($ucKey)
    {
        $this->ucKey = $ucKey;
        return $this;
    }

    public function getUcApi()
    {
        return $this->ucApi;
    }

    public function setUcApi($ucApi)
    {
        $this->ucApi = $ucApi;
        return $this;
    }

    public function getUcCharset()
    {
        return $this->ucCharset;
    }

    public function setUcCharset($ucCharset)
    {
        $this->ucCharset = $ucCharset;
        return $this;
    }

    public function getUcIp()
    {
        return $this->ucIp;
    }

    public function setUcIp($ucIp)
    {
        $this->ucIp = $ucIp;
        return $this;
    }

    public function getUcAppId()
    {
        return $this->ucAppId;
    }

    public function setUcAppId($ucAppId)
    {
        $this->ucAppId = $ucAppId;
        return $this;
    }

    public function getUcPpp()
    {
        return $this->ucPpp;
    }

    public function setUcPpp($ucPpp)
    {
        $this->ucPpp = $ucPpp;
        return $this;
    }
}