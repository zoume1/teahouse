<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/27 0027
 * Time: 15:20
 */

namespace  app\index\controller;

use think\Controller;
use think\Request;

class  Order extends  Controller{
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:立即购买过去购物清单数据返回
     **************************************
     */
    public function order_return(Request $request){
        if($request->isPost()){
            //goods_id
            $goods_id =$request->only("goods_id")["goods_id"];

        }
    }
}