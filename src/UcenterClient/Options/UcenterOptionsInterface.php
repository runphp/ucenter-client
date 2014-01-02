<?php
namespace UcenterClient\Options;

interface UcenterOptionsInterface
{

    public function getUcConnect();

    public function setUcConnect($ucConnect);

    public function getUcKey();

    public function setUcKey($ucKey);

    public function getUcApi();

    public function setUcApi($ucApi);

    public function getUcCharset();

    public function setUcCharset($ucCharset);

    public function getUcIp();

    public function setUcIp($ucIp);

    public function getUcAppId();

    public function setUcAppId($ucAppId);

    public function getUcPpp();

    public function setUcPpp($ucPpp);

    public function getUcAuthService();

    public function SetUcAuthService($ucAuthService);

    public function getUcAuthAdapter();

    public function SetUcAuthAdapter($ucAuthAdapter);
}