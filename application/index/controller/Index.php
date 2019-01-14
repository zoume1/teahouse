<?php
namespace app\index\controller;

use think\Controller;
use think\Cache;

class Index extends Controller
{
    public function index()
    {
        Cache::store('redis')->set('name','value',10*60*1000);
        return view("index");
    }


    public function text(){
        $bool = Cache::store('redis')->get('name');
        return view("text");
    }
}
