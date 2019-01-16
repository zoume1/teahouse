<?php
namespace app\index\controller;

use think\Controller;
use think\Cache;

class Index extends Controller
{
    public function index()
    {
        return view("index");
    }


    public function text(){
        return view("text");
    }
}
