<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\Wechat;
class Homecontroller extends Controller
{
    public function test()
    {
       //echo "laravel测试！";
        $wechat = new Wechat();
        $wechat->test();
        return view("test");
    }
}
