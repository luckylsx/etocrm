<?php
/**
 * Created by PhpStorm.
 * User: lucky.li
 * Date: 2017/10/31
 * Time: 10:21
 */
namespace App\Classes;
use Cache;

/**
 * 微信接口使用类
 * 微信工具类
 */
class Wechat{
    //企业ID
    protected $corpid;
    //应用的凭证密钥
    protected $corpsecret;
    public function __construct()
    {
        $data = config('wechat.wechat');
        $this->corpid = array_get($data,'corpid');
        $this->corpsecret = array_get($data,'corpsecret');
    }

    /**
     * 获取access_token
     * @return mixed
     */
    public function getAccessToken(){
        $key = md5($this->corpid . $this->corpsecret);
        //判断access_token是否存在
        if ($access_token = Cache::get($key)){
            return $access_token;
        }
        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$this->corpid."&corpsecret=".$this->corpsecret;
        $resData = file_get_contents($url);
        $res = json_decode($resData,true);
        $access_token = array_get($res,'access_token');
        Cache::put($key,$access_token,7000);
        return $access_token;
    }

}