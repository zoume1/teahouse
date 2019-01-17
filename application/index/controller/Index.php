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
    public function home(){
        return view("home");
    }

    public function text(){
        return view("text");
    }

}
