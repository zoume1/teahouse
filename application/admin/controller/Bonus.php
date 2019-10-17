<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/3 0003
 * Time: 18:21
 */

namespace app\admin\controller;

use think\Session;
use think\console\Input;
use think\Controller;
use think\Db;
use think\Request;
use think\paginator\driver\Bootstrap;
use app\admin\controller\Qiniu;

class Bonus extends Controller
{

    /**
     * [积分商城显示]
     * GY
     */
    public function bonus_index()
    {
        $store_id = Session::get("store_id");
        $bonus = db("bonus_mall")->where("store_id","EQ",$store_id)->order("sort_number desc")->paginate(20 ,false, [
            'query' => request()->param(),
        ]);

        return view('bonus_index', ["bonus" => $bonus]);
    }



    /**
     * [积分商城添加商品]
     * GY
     */
    public function bonus_add()
    {
        return view('bonus_add');
    }


    /**
     * [积分商城保存商品]
     * GY
     */
    public function bonus_save(Request $request)
    {
        if ($request->isPost()) {
            $store_id = Session::get("store_id");
            $goods_data = $request->param();
            if(empty($goods_data['goods_delivery'])){
                return $this->error('缺少必填参数');
            }
            $goods_data['goods_delivery']=json_encode($goods_data['goods_delivery']);
            // $list = [];
            // $show_images = $request->file("goods_show_images");
            //测试七牛上传图片
            $qiniu=new Qiniu();
            //获取店铺七牛云的配置项
            $peizhi=Db::table('applet')->where('store_id',$store_id)->find();
            $images='goods_show_images';
            $rr=$qiniu->uploadimg($peizhi['accesskey'],$peizhi['secretkey'],$peizhi['bucket'],$peizhi['domain'],$images);

            if (!empty($rr)) {
                $goods_data["goods_show_image"] = $rr[0];
                $goods_data["goods_show_images"] = implode(',', $rr);
                $goods_data["store_id"] = $store_id;
            }
            $bool = db("bonus_mall")->insert($goods_data);
            if ($bool) {
                $this->success("添加成功", url("admin/Bonus/bonus_index"));
            } else {
                $this->success("添加失败", url('admin/Bonus/bonus_index'));
            }
        }

    }

    /**
     * [积分商城编辑商品]
     * GY
     */
    public function bonus_edit($id)
    {
        $mall = db("bonus_mall")->where("id", $id)->select();
        foreach ($mall as $key => $value) {
            if (!empty($mall[$key]["goods_show_images"])) {
                $mall[$key]["goods_show_images"] = explode(",", $mall[$key]["goods_show_images"]);
            }
            $mall[$key]['goods_delivery']=json_decode($value['goods_delivery'],true);
        }
        return view('bonus_edit', ["mall" => $mall]);
    }


    /**
     * [积分商城更新商品]
     * GY
     */
    public function bonus_update(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $goods_data = $request->param();
            if(empty($goods_data['goods_delivery'])){
                return $this->error('缺少必填参数');
            }
            $goods_data['goods_delivery']=json_encode($goods_data['goods_delivery']);
            $store_id=Session::get('store_id');
            // $show_images = $request->file("goods_show_images");
            // $list = [];
            //测试七牛上传图片
            $qiniu=new Qiniu();
            //获取店铺七牛云的配置项
            $peizhi=Db::table('applet')->where('store_id',$store_id)->find();
            $images='goods_show_images';
            $rr=$qiniu->uploadimg($peizhi['accesskey'],$peizhi['secretkey'],$peizhi['bucket'],$peizhi['domain'],$images);
            if (!empty($rr)) {
                $liste = implode(',', $rr);
                $image = db("bonus_mall")->where("id", $id)->field("goods_show_images")->find();
                if (!empty($image["goods_show_images"])) {
                    $exper = $image["goods_show_images"];
                    $montage = $exper . "," . $liste;
                    $goods_data["goods_show_images"] = $montage;
                } else {
                    $montage = $liste;
                    $goods_data["goods_show_image"] = $rr[0];
                    $goods_data["goods_show_images"] = $montage;
                }
            } else {
                $image = db("bonus_mall")->where("id", $id)->field("goods_show_images")->find();
                if (!empty($image["goods_show_images"])) {
                    $goods_data["goods_show_images"] = $image["goods_show_images"];
                } else {
                    $goods_data["goods_show_images"] = null;
                    $goods_data["goods_show_image"] = null;
                }
            }

            $bool = db("bonus_mall")->where("id", $id)->update($goods_data);
            if ($bool) {
                $this->success("更新成功", url("admin/Bonus/bonus_index"));
            } else {
                $this->success("更新失败", url('admin/Bonus/bonus_index'));
            }

        }
    }


    /**
     * [积分商城删除商品]
     * GY
     */
    public function bonus_delete(Request $request)
    {
        $id = $request->only(["id"])["id"];
        $bool = db("bonus_mall")->where("id", $id)->delete();

        if ($bool) {
            $this->success("删除成功", url("admin/Bonus/bonus_index"));
        } else {
            $this->success("删除失败", url('admin/Bonus/bonus_index'));
        }
    }


    /**
     * [积分商城商品图片删除]
     * GY
     */
    public function bonus_images(Request $request)
    {
        if ($request->isPost()) {
            $tid = $request->param();
            $id = $tid["id"];
            $image = db("bonus_mall")->where("id", $tid['pid'])->field("goods_show_images")->find();
            if (!empty($image["goods_show_images"])) {
                $se = explode(",", $image["goods_show_images"]);
                foreach ($se as $key => $value) {
                    if ($value == $id) {
                        // unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $value);
                        unset($se[$key]);
                    } else {
                        $new_image[] = $value;
                    }
                }
            }
            if (!empty($new_image)) {
                $new_imgs_url = implode(',', $new_image);
                $res = Db::name('bonus_mall')->where("id", $tid['pid'])->update(['goods_show_images' => $new_imgs_url]);
            } else {
                $res = Db::name('bonus_mall')->where("id", $tid['pid'])->update(['goods_show_images' => null, 'goods_show_image' => null]);
            }
            if ($res) {
                return ajax_success('删除成功');
            } else {
                return ajax_success('删除失败');
            }
        }
    }

    /**
     * [积分商品搜索]
     * 郭杨
     */
    public function bonus_search()
    {
        $store_id = Session::get("store_id");
        $ppd = input('goods');          //积分商品编号或名称
        if (!empty($ppd)) {
            $bonus = db("bonus_mall")->where("store_id","EQ",$store_id)->where("goods_number", "like", "%" . $ppd . "%")->whereOr("goods_name", "like", "%" . $ppd . "%")->paginate(20 ,false, [
                'query' => request()->param(),
            ]);

        } else {
            $bonus = db("bonus_mall")->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        }
        return view('bonus_index', ["bonus" => $bonus]);
    }

 
    /**
     * [积分商品列表组批量删除]
     * GY
     */
    public function bonus_dels(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            if (is_array($id)) {
                $where = 'id in(' . implode(',', $id) . ')';
            } else {
                $where = 'id=' . $id;
            }
            $list = Db::name('bonus_mall')->where($where)->delete();
            if (empty($list)) {
                return ajax_success('成功删除!', ['status' => 1]);
            } else {
                return ajax_error('删除失败', ['status' => 0]);
            }
        }
    }


    /**
     * [优惠券显示]
     * GY
     */
    public function coupon_index()
    {
        $store_id = Session::get("store_id");
        $coupon = db("coupon")->where("store_id","EQ",$store_id)->paginate(20 ,false, [
            'query' => request()->param(),
        ]);
        return view('coupon_index', ["coupon" => $coupon]);
    }


    /**
     * [优惠券添加]
     * GY
     */
    public function coupon_add()
    {
        $store_id = Session::get("store_id");
        $scope = db("member_grade")->field("member_grade_name")->where("store_id","EQ",$store_id)->select();
        return view('coupon_add', ["scope" => $scope]);
    }



    /**
     * [优惠券保存入库]
     * GY
     */
    public function coupon_save(Request $request)
    {
        if ($request->isPost()) {
            $store_id = Session::get("store_id");
            $data = $request->param();
            $data['store_id'] = $store_id;
            if(isset($data["scope"])){     
                $data["scope"] = implode(",", $data["scope"]);
            }
            if(isset($data["suit_price"])){     
                $data["suit_price"] = implode(",", $data["suit_price"]);
            }

            unset($data["goods_number"]);
            if(!empty($data["goods_id"])){
                foreach($data["goods_id"] as $k => $v){
                    $str[$k] = substr($data["goods_id"][$k], 0, strrpos($data["goods_id"][$k],"_" ));  //商品id
                    $sts[$k] = substr($data["goods_id"][$k], -1, strrpos($data["goods_id"][$k],"_" ));//商品类型 1=>普通 2=>众筹
                }               
                if (!empty($str)) {
                    foreach ($sts as $key => $value) {
                        if($sts[$key] == 1){
                        $goods[$key] = db("goods")->where("id", $str[$key])->field("id,goods_number,goods_show_image,goods_name,goods_standard,goods_repertory,label,coupon_type")->find();
                    } else {
                        $goods[$key] = db("crowd_goods")->where("id", $str[$key])->find();
                    }
                }
                unset($data["goods_id"]);
            }
        }
        $coupon_id = db("coupon")->insertGetId($data);

        if (!empty($goods)) {
            foreach ($goods as $key => $value) {
                if($goods[$key]["coupon_type"] == 1){
                    $new_goods[$key]["goods_id"] = $goods[$key]["id"];
                    $new_goods[$key]["label"] = $goods[$key]["label"];
                    $new_goods[$key]["coupon_type"] = $goods[$key]["coupon_type"];
                    $new_goods[$key]["coupon_id"] = $coupon_id;
                    $new_goods[$key]["goods_show_images"] = $goods[$key]["goods_show_image"];
                    $new_goods[$key]["goods_name"] = $goods[$key]["goods_name"];
                    $new_goods[$key]["goods_number"] = $goods[$key]["goods_number"];
                    $new_goods[$key]["goods_standard"] = $goods[$key]["goods_standard"];
                    if ($goods[$key]["goods_standard"] == 1) {
                        $new_goods[$key]["goods_repertory"] = db("special")->where("goods_id", $goods[$key]["id"])->sum("stock");
                    } else {
                        $new_goods[$key]["goods_repertory"] = $goods[$key]["goods_repertory"];
                    }  
                } 
                if($goods[$key]["coupon_type"] == 2) {
                    $new_goods[$key]["goods_id"] = $goods[$key]["id"];
                    $new_goods[$key]["label"] = $goods[$key]["label"];
                    $new_goods[$key]["coupon_type"] = $goods[$key]["coupon_type"];
                    $new_goods[$key]["coupon_id"] = $coupon_id;
                    $new_goods[$key]["goods_show_images"] = $goods[$key]["goods_show_image"];
                    $new_goods[$key]["goods_number"] = $goods[$key]["id"];
                    $new_goods[$key]["goods_name"] = $goods[$key]["project_name"];
                    $new_goods[$key]["goods_standard"] = 1;
                    $new_goods[$key]["goods_repertory"] = db("crowd_special")->where("goods_id", $goods[$key]["id"])->sum("stock");
                }
            }

            foreach ($new_goods as $k => $v) {
                    $rest = db("join")->insert($v);
            }
        }

            if ($coupon_id || $rest) {
                $this->success("添加成功", url("admin/Bonus/coupon_index"));
            } else {
                $this->error("添加失败", url("admin/Bonus/coupon_add"));
            }
        }
    }


    /**
     * [优惠券编辑]
     * GY
     */
    public function coupon_edit($id)
    {
        $store_id = Session::get("store_id");
        $coupons = db("coupon")->where("id", $id)->select();
        foreach ($coupons as $k => $v) {
            $coupons[$k]["scope"] = explode(",", $coupons[$k]["scope"]);
            $coupons[$k]["suit_price"] = explode(",", $coupons[$k]["suit_price"]);
        }
        $scope = db("member_grade")->where("store_id","EQ",$store_id)->field("member_grade_name")->select();
        return view('coupon_edit', ["coupons" => $coupons, "scope" => $scope]);


    }


    /**
     * [优惠券编辑]
     * GY
     */
    public function coupon_weave(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(["id"])["id"];
            $join = db("join")->where("coupon_id", $id)->select();
            if (!empty($join) && !empty($id)) {
                return ajax_success("获取成功", $join);
            } else {
                return ajax_error("获取失败优惠券失败");
            }

        }

    }



    /**
     * [优惠券更新]
     * GY
     */
    public function coupon_update()
    {
        $request = Request::instance();

        if ($request->isPost()) {
            
            $data = $request->param();
            
            if(isset($data["scope"])){
                $data["scope"] = implode(",", $data["scope"]);
            }
            if(isset($data["suit_price"])){
                $data["suit_price"] = implode(",", $data["suit_price"]);
            }
            
            if (!empty($data["goods_id"])) {
                foreach ($data["goods_id"] as $key => $value) {
                    $goodes[$key] = db("goods")->where("id", $data["goods_id"][$key])->field("id,goods_number,goods_show_images,goods_name,goods_standard,goods_repertory")->find();
                }
                unset($data["goods_id"]);
            }
            unset($data["goods_number"]);
            if (!empty($goodes)){
                foreach ($goodes as $key => $value) {
                    $goodes[$key]["goods_id"] = $goodes[$key]["id"];
                    $goodes[$key]["coupon_id"] = $request->only(["id"])["id"];

                    if ($goodes[$key]["goods_standard"] == 1) {
                        $goodes[$key]["goods_repertory"] = db("special")->where("goods_id", $goodes[$key]["id"])->sum("stock");
                        $goodes[$key]["goods_show_images"] = explode(",", $goodes[$key]["goods_show_images"])[0];
                    } else {
                        $goodes[$key]["goods_show_images"] = explode(",", $goodes[$key]["goods_show_images"])[0];
                    }
                    unset($goodes[$key]["id"]);
                }             
                foreach ($goodes as $k => $v) {
                    $rest = db("join")->insert($v);
                }
            }
            $bool = db("coupon")->where('id', $request->only(["id"])["id"])->update($data);

            if ($bool || $rest) {
                $this->success("编辑成功", url("admin/Bonus/coupon_index"));
            } else {
                $this->error("编辑失败", url("admin/Bonus/coupon_index"));
            }
        }
    }



    /**
     * [优惠券删除]
     * GY
     */
    public function coupon_del($id)
    {
        $bool = db("coupon")->where("id", $id)->delete();
        $boole = db("join")->where("coupon_id", $id)->delete();
        if ($bool || $boole) {
            $this->success("删除成功", url("admin/Bonus/coupon_index"));
        } else {
            $this->error("删除失败", url("admin/Bonus/coupon_index"));
        }
    }



    /**
     * [优惠券搜索商品]
     * GY
     */
    public function coupon_search(Request $request)
    {
        $goods_number = input("goods_number");   //商品编码
        $coupon_type = input("coupon_type");     //商品类型 
        $store_id = Session::get("store_id"); 
        /**
         * 鲁文兵改过
         */
        //普通商品
        if($coupon_type == 1){
             $goods = db("goods")
             ->where('goods_number|goods_name','like','%'.$goods_number.'%')
                ->where("coupon_type",$coupon_type)
                ->where("store_id","EQ",$store_id)
                ->field("id,goods_number,goods_show_images,goods_name,goods_standard,goods_repertory,coupon_type")
                ->select();
                if(!empty($goods)){
                    foreach ($goods as $key => $value) {
                        if ($goods[$key]["goods_standard"] == 1) {
                            $goods[$key]["goods_repertory"] = db("special")->where("goods_id", $goods[$key]["id"])->sum("stock");
                            $goods[$key]["goods_show_images"] = explode(",", $goods[$key]["goods_show_images"])[0];
                        } else {
                            $goods[$key]["goods_show_images"] = explode(",", $goods[$key]["goods_show_images"])[0];
                        }
                    }
                    return ajax_success("获取成功", $goods);
                } else {
                    return ajax_error("未找到该商品");
                }
            } else {
                    //众筹商品
                    $id = $goods_number - 1000000;
                    $key = 0;
                    $goods = db("crowd_goods")->where("id", $id)->field("id,goods_show_image,project_name,coupon_type")->find();
                    if(!empty($goods)){
                    $goods['goods_repertory'] = db("crowd_special")->where("goods_id",$goods['id'])->sum("stock");
                    $goodes[$key] = array(
                        'id'=> $goods['id'],
                        'goods_number'=> $goods['id'] +1000000,
                        'goods_name'=> $goods['project_name'],
                        'goods_standard'=> 1,
                        'goods_show_images'=> $goods['goods_show_image'],
                        'goods_repertory'=> $goods['goods_repertory'],
                        'coupon_type'=> $goods['coupon_type']
                    );
                    return ajax_success("获取成功", $goodes);
                } else {
                    return ajax_error("未找到该商品");
                }
            }
        
    }


    /**
     * [优惠券搜索]
     * GY
     */
    public function coupon_seek(Request $request)
    {
        $seek = input('seek');         //优惠券名称

        if (!empty($seek)) {
            $activ = db("coupon")->where("label", "like", "%" . $seek . "%")->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } else {
            $activ = db("coupon")->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        }
        return view('coupon_index', ["coupon" => $activ]);
    }




}