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
        $resData = httpGet($url);
        $res = json_decode($resData,true);
        //access_token获取失败
        if (array_get($res,'errcode')!=0){
            return false;
        }
        //获取成功
        $access_token = array_get($res,'access_token');
        Cache::put($key,$access_token,7000);
        return $access_token;
    }

    /**
     * 获取jsapi_ticket
     * @return bool|mixed
     */
    public function getJsApiTicket()
    {
        $js_key = md5($this->corpid . $this->corpsecret . "js_api_ticket");
        //如果jsapiticket存在，直接返回
        if ($jsApiTicket = Cache::get($js_key)){
            return $jsApiTicket;
        }
        //获取jsApiTicket
        $access_token = $this->getAccessToken();
        if (!$access_token){
            return false;
        }
        $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token={$access_token}";
        $resData = httpGet($url);
        $res = json_decode($resData,true);
        //获取失败有错误
        if (array_get($res,'errcode')!=0){
            return false;
        }
        //获取成功，缓存jsApiTicket
        $jsApiTicket = array_get($res,'ticket');
        Cache::put($js_key,$jsApiTicket,7000);
        return $jsApiTicket;

    }
    /**
     * 创建随机字符串
     * @param int $length
     * @return string
     */
    public function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    public function jssdk($url)
    {
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        $jsapi_ticket = $this->getJsApiTicket();
        $string = "jsapi_ticket=" . $jsapi_ticket . "&noncestr=".$nonceStr . "&timestamp=".$timestamp."&url=".$url;
        $signPackage = array(
            "debug"	  =>true,
            "appId"     => $this->corpid,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "signature" => sha1($string),
            "string" => $string,
            "jsApiList" =>array(
                'onMenuShareAppMessage',
                'onMenuShareWechat',
                'startRecord',
                'stopRecord',
                'onVoiceRecordEnd',
                'playVoice',
                'pauseVoice',
                'stopVoice',
                'onVoicePlayEnd',
                'uploadVoice',
                'downloadVoice',
                'chooseImage',
                'previewImage',
                'uploadImage',
                'downloadImage',
                'previewFile',
                'getNetworkType',
                'openLocation',
                'getLocation',
                'onHistoryBack',
                'hideOptionMenu',
                'showOptionMenu',
                'hideMenuItems',
                'showMenuItems',
                'hideAllNonBaseMenuItem',
                'showAllNonBaseMenuItem',
                'closeWindow',
                'scanQRCode',
                'selectEnterpriseContact',
                'openEnterpriseChat',
                'chooseInvoice'
            )
        );
        return $signPackage;
    }


}