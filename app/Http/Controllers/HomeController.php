<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\Wechat;
use Cache;
class Homecontroller extends Controller
{
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

    public function test(Wechat $wechat)
    {
	    //$ac = $wechat->getAccessToken();
	    $wechat->jssdk($url = '');
	    //var_dump($ac);
        die;
    }
    public function index(){
        return view("index");
    }

    /**
     * 取得授权，获取code
     */
    public function getAuthorize()
    {
        //获取微信配置信息
        $we = config("wechat.wechat");
        //获得appid
        $appid = array_get($we,'corpid');
        //获得agentid
        $agentid = array_get($we,'agentid');
        //获得重定向的url地址
        $u =  'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $re_u = strstr($u,"api",true);
        $redirect_url = $re_u . "api/getCode";
        //授权链接
        $auth_url = "https://open.weixin.qq.com/connect/oauth2/authorize";
        //$auth_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=CORPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&agentid=AGENTID&state=STATE#wechat_redirect";
        $redirect = $auth_url . "?appid=" . $appid . "&redirect_uri=".urlencode($redirect_url) . "&response_type=code"."&scope=snsapi_privateinfo&agentid=".$agentid."#wechat_redirect";
        return view("shouquan",['url'=>$redirect]);
        //echo "<a href='{$redirect}'>点击链接授权</a>";
    }

    /**
     * 获取code
     */
    public function getCode(Request $request,Wechat $wechat){
        $input = $request->input();
        //获取code信息
        $code = array_get($input,'code');
        //获取用户信息
        $access_token = $wechat->getAccessToken();
        if (!$access_token){
            return "授权失败！";
        }
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token={$access_token}&code={$code}";
        $resData = httpGet($url);
        $res = json_decode($resData,true);
        if(array_get($res,'errcode')!=0){
            p($res);
            echo "获取用户信息失败";
            die;
        }
        echo "该用户的userId是".array_get($res,'UserId') . "，user_ticket是：".array_get($res,'user_ticket');
    }

    /**
     * 获取jssdk
     * @param Request $request
     * @param Wechat $wechat
     */
    public function getSDK(Request $request,Wechat $wechat)
    {
        $input = $request->input();
        $url = array_get($input,'url');
        #$url= 'http://etocrm.lylucky.com/api/index';
        if(empty($url)){
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }else{
            $url = urldecode($url);
        }
        $signPackage = $wechat->jssdk($url);
        //$a = json_encode($signPackage);
        echo json_encode($signPackage);die;
    }


}
