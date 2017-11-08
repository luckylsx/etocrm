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
    private static $app_name = 'ikea';
    //企业ID
    protected $corpid;
    //应用的凭证密钥
    protected $corpsecret;
    protected $AgentId;
    public function __construct()
    {
        /*$data = config('wechat.wechat');
        $this->corpid = array_get($data,'corpid');
        $this->corpsecret = array_get($data,'corpsecret');*/
        //测试url
        $url = "http://wbatest.showgrid.cn/api/getCoreWeChatRole";
        //get请求
        $resData = httpGet($url);

        $data = json_decode($resData,'true');
        $res = json_decode(self::bcrypt($data['data']),true);
        if (array_get($data,'code')!='10000'){
            return false;
        }else{
            //企业ID
            $this->corpid = array_get($res,'AppId');
            //应用的凭证密钥
            $this->corpsecret = array_get($res,'Secret');
            //应用ID
            $this->AgentId = array_get($res,'AgentId');
        }
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
        $url = "http://wbatest.showgrid.cn/api/getAccessToken";
        $resData = httpGet($url);
        $res = json_decode($resData,true);
        //access_token获取失败
        if (array_get($res,'code')!='10000'){
            return false;
        }
        $access_token=self::bcrypt($res['data']);
        Cache::put($key,$access_token,115); //有效期为分钟 线上
        //Cache::put($key,$access_token,7000); //有效期为分钟 本地
        return $access_token;
    }

    /**
     * 获取jsapi_ticket
     * @return bool|mixed
     */
    /*public function getJsApiTicket()
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
        $url = "http://wbatest.showgrid.cn/api/getAccessToken";
        $resData = httpGet($url);
        $res = json_decode($resData,true);
        //获取失败有错误
        if (array_get($res,'errcode')!=0){
            return false;
        }
        //获取成功，缓存jsApiTicket
        $jsApiTicket = array_get($res,'ticket');
        Cache::put($js_key,$jsApiTicket,115);   //有效期为分钟
        return $jsApiTicket;

    }*/
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
        //获取jssdk参数
        $jssdk = $this->getJssdk();
        if (!$jssdk || !is_array($jssdk)){
            return false;
        }
        $timestamp = array_get($jssdk,'timestamp');
        $nonceStr = array_get($jssdk,'nonceStr');
        $jsapi_ticket = array_get($jssdk,'rawString');
        $jsapi_ticket = strstr(substr(strstr($jsapi_ticket,'='),1,-1),'=',true);
        $string = "jsapi_ticket={$jsapi_ticket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        $signPackage = array(
            "debug"	=>true,
            "appId"     => $this->corpid,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => sha1($string),
            "rawString" => $string,
            "jsApiList" =>array(
                "scanQRCode"
            )
        );
        return $signPackage;
    }

    /**
     * 获取jssdk参数
     * @return bool|mixed
     */
    public function getJssdk()
    {
        $url = "http://wbatest.showgrid.cn/api/getSignPackage";
        $resData = httpGet($url);
        $res = json_decode($resData,true);
        //是否获取成功
        if (array_get($res,'code')!='10000'){
            return false;
        }
        $jssdk = self::bcrypt($res['data']);
        return $jssdk;
    }

    /**
     * 获取应用套件凭证
     * @return bool|mixed
     */
    /*public function getSuitAccessToken()
    {
        $key = "suite_access_token . {$this->corpid} . {$this->corpsecret}";
        if ($suit_access_token = Cache::get($key)){
            return $suit_access_token;
        }
        $url = "ttps://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token";
        $data = [
            "suite_id"=>"id_value" ,
            "suite_secret"=>"secret_value",
            "suite_ticket"=>"ticket_value"
        ];
        $resData = http_post($url,json_encode($data));
        $res = json_decode($resData,true);
        if (array_get($res,'errcode')!=0){
            return false;
        }
        $suit_access_token = array_get($resData,'suite_access_token');
        return $suit_access_token;
    }*/

    /**
     *
     */
   /* public function getPreAuthCode()
    {
        $suit_access_token = $this->getSuitAccessToken();
        $url = "https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code?suite_access_token={$suit_access_token}";
        $data = [
          "suite_id" =>
        ];
    }*/
    public function getAccessData()
    {
        //测试url
        $url = "http://wbatest.showgrid.cn/api/getCoreWeChatRole";
        //get请求
        $resData = httpGet($url);
        $data = json_decode($resData,'true');
        //var_dump($data);
        $d = self::bcrypt($data['data']);
        $da = json_decode($d,true);
        var_dump($da);

   }

    /**
     * 加密
     * @param $data
     * @return bool|string
     */
    private static function encrypt($data)
    {
        if(!$data){
            return false;
        }
        $encrypt = [];
        $key = strtoupper(md5(base64_encode(json_encode(self::$app_name))));
        $encrypt['data'] = json_encode($data);
        $encrypt['iv']  = (substr(md5(base64_encode(json_encode(self::$app_name))),0,16));
        $encrypt['value'] = (openssl_encrypt($encrypt['data'],'AES-128-CBC',$key,0,$encrypt['iv']));
        return base64_encode(json_encode($encrypt));
    }

    /**
     * 解密
     * @param $data
     * @return bool|mixed
     */
    private static function bcrypt($data)
    {
        if(!$data){
            return false;
        }
        $key = strtoupper(md5(base64_encode(json_encode(self::$app_name))));
        $encrypt = json_decode(base64_decode($data),true);
        $encrypt['iv']  = (substr(md5(base64_encode(json_encode(self::$app_name))),0,16));
        $encrypt['data'] = openssl_decrypt($encrypt['value'],'AES-128-CBC',$key,0,$encrypt['iv']);
        $data = json_decode($encrypt['data'],true);
        return $data;
    }


}
