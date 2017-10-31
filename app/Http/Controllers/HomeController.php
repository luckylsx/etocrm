<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\Wechat;
class Homecontroller extends Controller
{

    public function __construct()
    {
        $data = config('wechat.wechat');
        $this->corpid = array_get($data,'corpid');
        $this->corpsecret = array_get($data,'corpsecret');
    }

    public function test()
    {
        $a = http_get("https://www.baidu.com");
        echo $a;
        die;
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
        echo "<a href='{$redirect}'>点击链接授权</a>";
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
        $url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?access_token={$access_token}&code={$code}";
        $resData = httpGet($url);
        $res = json_decode($resData,true);
        echo "该用户的userId是".array_get($res,'UserId') . "，user_ticket是：".$res['user_ticket'];
    }
}
