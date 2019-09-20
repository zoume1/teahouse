<?php
/**
 * Created by PhpStorm.
 * User: PHP
 * Date: 2019/9/20
 * Time: 10:17
 */
namespace app\rec\controller;
use think\Controller;

class Share extends Controller
{
    public function qr_code()
    {

//        $a = 'https://vip.fykcy.vip/20190422.doc';
//        //$b = 1111;
//        //$c = 2222;
//        $list = 'http://qr.topscan.com/api.php?text=' . $a;//.$b.$c;
//
//        echo "<img src='" . $list . "'>";


        $data = $this->code();

        return $data;

    }




    function code(){
        $a = 'https://vip.fykcy.vip/20190422.doc';
        //$b = 1111;
        //$c = 2222;
        $list = 'http://qr.topscan.com/api.php?text=' . $a;//.$b.$c;

        echo "<img src='" . $list . "'>";
    }

}