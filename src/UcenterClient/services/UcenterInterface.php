<?php
namespace UcenterClient\Services;

interface UcenterInterface
{

    const UC_CLIENT_VERSION = '1.6.0'; // note UCenter 版本标识
    const UC_CLIENT_RELEASE = '20110501';

    const API_DELETEUSER = 1; // note 用户删除 API 接口开关
    const API_RENAMEUSER = 1; // note 用户改名 API 接口开关
    const API_GETTAG = 1; // note 获取标签 API 接口开关
    const API_SYNLOGIN = 1; // note 同步登录 API 接口开关
    const API_SYNLOGOUT = 1; // note 同步登出 API 接口开关
    const API_UPDATEPW = 1; // note 更改用户密码 开关
    const API_UPDATEBADWORDS = 1; // note 更新关键字列表 开关
    const API_UPDATEHOSTS = 1; // note 更新域名解析缓存 开关
    const API_UPDATEAPPS = 1; // note 更新应用列表 开关
    const API_UPDATECLIENT = 1; // note 更新客户端缓存 开关
    const API_UPDATECREDIT = 1; // note 更新用户积分 开关
    const API_GETCREDITSETTINGS = 1; // note 向 UCenter 提供积分设置 开关
    const API_GETCREDIT = 1; // note 获取用户的某项积分 开关
    const API_UPDATECREDITSETTINGS = 1; // note 更新应用积分设置 开关
    const API_RETURN_SUCCEED = '1';

    const API_RETURN_FAILED = '-1';

    const API_RETURN_FORBIDDEN = '-2';

    public function userLogin($username, $password, $isuid, $checkques, $questionid, $answer);
}