<?php
/**
 * Created by PhpStorm.
 * User: GY
 * Date: 2019/2/20
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\paginator\driver\Bootstrap;
use think\Session;
use think\View;
use app\api\model\VideoFrequency;


class  Material extends  Controller{


    /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播
     **************************************
     * @return \think\response\View
     */
    public function direct_seeding(){
        $store_id = Session::get("store_id");
        $data = Db::name("video_frequency")->where("store_id",$store_id)->paginate(20 ,false, [
            'query' => request()->param(),
        ]);
        $direct = Db::name("direct_seeding")->where("store_id",$store_id)->select();  //分类
        return view("direct_seeding",["data"=>$data,'direct'=>$direct]);
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播添加编辑设备
     **************************************
     */
    public  function  direct_seeding_add(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id");
            $data = $request->param();
            $show_images = $request->file("icon_image");
            if ($show_images) {
                $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["icon_image"] = str_replace("\\", "/", $show_images->getSaveName());
            }
            $data['store_id'] = $store_id;
            if(empty($data['store_name'])){
                $data['live'] = 2;
                $data['store_name'] = $data['store_one'];
                unset($data['store_one']);
            } else {
                $data['live'] = 1;
                unset($data['store_one']);
            }
            if(empty($data['store_name'])){
                $this->error("请选择直播分类或者输入直播名称", url("admin/Material/direct_seeding"));
           }
            $bool = Db::name("video_frequency")->insert($data);
            if ($bool) {
                $this->success("添加成功", url("admin/Material/direct_seeding"));
            } else {
                $this->error("添加失败", url("admin/Material/direct_seeding"));
            }
        }
        $store_id = Session::get("store_id");
        $store_name = Db::name("store_house")->where("store_id",$store_id)->select(); //仓库
        $direct = Db::name("direct_seeding")->where("store_id",$store_id)->where("status",1)->select();  //分类
        return  view("direct_seeding_add",["store_name"=>$store_name,"direct"=>$direct]);
    }


        /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播编辑设备
     **************************************
     */
    public  function  direct_seeding_edit($id){
        $data = Db::name("video_frequency")->where("id",$id)->select();
        $store_id = Session::get("store_id");
        $store_name = Db::name("store_house")->where("store_id",$store_id)->select(); //仓库
        $direct = Db::name("direct_seeding")->where("store_id",$store_id)->select();  //分类
        return  view("direct_seeding_edit",["store_name"=>$store_name,"direct"=>$direct,"data"=>$data]);
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播更新设备
     **************************************
     */
    public  function  direct_seeding_update(Request $request){
        if($request->isPost()){
        $data = $request->param();
        $show_images = $request->file("icon_image");
        if ($show_images) {
            $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
            $data["icon_image"] = str_replace("\\", "/", $show_images->getSaveName());
        }
        if(empty($data['store_name'])){
            $data['live'] = 2;
            $data['store_name'] = $data['store_one'];
            unset($data['store_one']);
        } else {
            $data['live'] = 1;
            unset($data['store_one']);
        }
       if(empty($data['store_name'])){
            $this->error("请选择直播分类或者输入直播名称", url("admin/Material/direct_seeding"));
       }

        $bool = Db::name("video_frequency")->where("id",$data['id'])->update($data);
        if ($bool) {
            $this->success("更新成功", url("admin/Material/direct_seeding"));
        } else {
            $this->error("更新失败", url("admin/Material/direct_seeding"));
        }
    }
        
    }

        /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播更新设备
     **************************************
     */
    public  function  direct_seeding_delete($id){
        $bool = Db::name("video_frequency")->where("id",$id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/Material/direct_seeding"));
        } else {
            $this->error("删除失败", url("admin/Material/direct_seeding"));
        }      
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播更新设备
     **************************************
     */
    public  function  direct_seeding_status(Request $request){       
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("video_frequency")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Material/direct_seeding"));
                } else {
                    $this->error("修改失败", url("admin/Material/direct_seeding"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("video_frequency")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Material/direct_seeding"));
                } else {
                    $this->error("修改失败", url("admin/Material/direct_seeding"));
                }
            }
        }
    }

<<<<<<< HEAD
    //直播token
    public function video_token()
    {

        $data = db('tb_config')->find();
=======
        /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播搜索
     **************************************
     */
    public  function  direct_seeding_search(){    
        $status = input('status')?input('status'):null;
        $open_status = input('open_status')?input('open_status'):null;
        $classify_name = input('classify_name')?input('classify_name'):null;
        $store_id = Session::get("store_id");

        if(!empty($status) && !empty($open_status) && !empty($classify_name)){
            $data = Db::name("video_frequency")
            ->where("store_id",$store_id)
            ->where("open_status",$open_status)
            ->where("status",$status)
            ->where("classify_name",$classify_name)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } elseif(!empty($status) && empty($open_status) && empty($classify_name)){
            $data = Db::name("video_frequency")
            ->where("store_id",$store_id)
            ->where("status",$status)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } elseif(empty($status) && !empty($open_status) && empty($classify_name)){
            $data = Db::name("video_frequency")
            ->where("store_id",$store_id)
            ->where("open_status",$open_status)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } elseif(empty($status) && empty($open_status) && !empty($classify_name)){
            $data = Db::name("video_frequency")
            ->where("store_id",$store_id)
            ->where("classify_name",$classify_name)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } elseif(!empty($status) && !empty($open_status) && empty($classify_name)){
            $data = Db::name("video_frequency")
            ->where("store_id",$store_id)
            ->where("open_status",$open_status)
            ->where("status",$status)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } elseif(!empty($status) && empty($open_status) && !empty($classify_name)){
            $data = Db::name("video_frequency")
            ->where("store_id",$store_id)
            ->where("status",$status)
            ->where("classify_name",$classify_name)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } elseif(empty($status) && !empty($open_status) && !empty($classify_name)){
            $data = Db::name("video_frequency")
            ->where("store_id",$store_id)
            ->where("open_status",$open_status)
            ->where("classify_name",$classify_name)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        } else {
            $data = Db::name("video_frequency")
            ->where("store_id",$store_id)
            ->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        }
        $direct = Db::name("direct_seeding")->where("store_id",$store_id)->select();  //分类
        return view("direct_seeding",["data"=>$data,'direct'=>$direct]);
    }
>>>>>>> 3bf58001c45cc5924cf9ed2c29f38a6869456546

        $res = $data ?['code'=>1,'msg'=>'获取成功','data'=>$data] : ['code'=>0,'msg'=>'获取失败'];

        return json($res);exit;
    }

    //直播更新token
    public function edit_video_token()
    {
        $request      = Request::instance();
        $param        = $request->param();//获取所有参数，最全
        $validate     = new Validate([
            ['accesstoken', 'require'],
            ['expiretime', 'require'],
        ]);
        //验证部分数据合法性
        if (!$validate->check($param)) {
            echo json_encode(['code' => 0,'msg' => $validate->getError()]);
        }
        $data = db('config')->find();
        if($data){
            $result = db('config')->where('id',$data['id'])
                ->update(
                    [   'accesstoken' => $param['accesstoken'],
                        'expiretime' => $param['expiretime'],
                        'upated_time' => date("Y-m-d H:i:s", time())
                    ]);

            $res = $result ? ['code' => 1, 'msg' => '成功'] : ['code' => 0, 'msg' => '失败'];
            echo json_encode($res);
        }else{

            echo json_encode(['code' => 0, 'msg' => '数据有误']);
        }

    }
    /**
     **************GY*******************
     * @param Request $request
     * Notes:直播分类
     **************************************
     */
    public function direct_seeding_classification(){
        $store_id = Session::get("store_id");
        $direct_data = Db::name("direct_seeding") 
                ->where("store_id",$store_id)
                ->select();
        $url = 'admin/Material/direct_seeding_classification';
        $pag_number = 20;
        $data = paging_data($direct_data,$url,$pag_number);
        return view("direct_seeding_classification",["data"=>$data]);
    }

    /**
     **************GY******************* 
     * @param Request $request
     * Notes:直播分类添加保存
     **************************************
     * @return \think\response\View
     */
    public function direct_seeding_classification_add(Request $request){
        if($request->isPost()){
            $store_id = Session::get("store_id");
            $data = input();
            $data['status'] = isset($data['status'])?$data['status']:0;
            $data['store_id'] = $store_id;
            $show_images = $request->file("icon_image");
            if ($show_images) {
                $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["icon_image"] = str_replace("\\", "/", $show_images->getSaveName());
            }
            if(empty($data['title']) || empty($data['icon_image'])){
                $this->error("请仔细填写", url("admin/Material/direct_seeding_classification"));
            }
            
            $bool = Db::name("direct_seeding")->insert($data);
            if ($bool) {
                $this->success("添加成功", url("admin/Material/direct_seeding_classification"));
            } else {
                $this->error("添加失败", url("admin/Material/direct_seeding_classification"));
            }
        }
        return view("direct_seeding_classification_add");
        
    }


    /**
     **************GY******************* 
     * @param Request $request
     * Notes:直播分类编辑
     **************************************
     * @return \think\response\View
     */
    public function direct_seeding_classification_edit($id){
        $store_id = Session::get("store_id");
        $bool = Db::name("direct_seeding")->where("id",$id)->select();
        return view("direct_seeding_classification_edit",['bool'=>$bool]);
    }

    /**
     **************GY******************* 
     * @param Request $request
     * Notes:直播分类删除
     **************************************
     * @return \think\response\View
     */
    public function direct_seeding_classification_delete($id){

        $store_id = Session::get("store_id");
        $bool = Db::name("direct_seeding")->where("id",$id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/Material/direct_seeding_classification"));
        } else {
            $this->error("删除失败", url("admin/Material/direct_seeding_classification"));
        }
    }

    /**
     **************GY******************* 
     * @param Request $request
     * Notes:直播分类更新
     **************************************
     * @return \think\response\View
     */
    public function direct_seeding_classification_update(Request $request){
        if($request->isPost()){
            $data = input();
            $id = $request->only(["id"])["id"];
            $data['status'] = isset($data['status'])?$data['status']:0;
            $show_images = $request->file("icon_image");
            if ($show_images) {
                $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["icon_image"] = str_replace("\\", "/", $show_images->getSaveName());
            }
            $bool = Db::name("direct_seeding")->where("id",$id)->update($data);
            if ($bool) {
                $this->success("更新成功", url("admin/Material/direct_seeding_classification"));
            } else {
                $this->error("未更改数据", url("admin/Material/direct_seeding_classification"));
            }
        }
        
    }
       
    /**
     * [图片删除]
     * 郭杨
     */
    public function direct_seeding_classification_delete_image(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(['id'])['id'];
            $image_url = db("direct_seeding")->where("id", $id)->field("icon_image")->find();
            if ($image_url['icon_image'] != null) {
                unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $image_url['icon_image']);
            }
            $bool = db("direct_seeding")->where("id", $id)->field("icon_image")->update(["icon_image" => null]);
            if ($bool) {
                return ajax_success("删除成功");
            } else {
                return ajax_error("删除失败");
            }
        }
    }


        /**
     * [图片删除]
     * 郭杨
     */
    public function direct_seeding_delete_image(Request $request)
    {
        if ($request->isPost()) {
            $id = $request->only(['id'])['id'];
            $image_url = db("video_frequency")->where("id", $id)->field("icon_image")->find();
            if ($image_url['icon_image'] != null) {
                unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $image_url['icon_image']);
            }
            $bool = db("video_frequency")->where("id", $id)->field("icon_image")->update(["icon_image" => null]);
            if ($bool) {
                return ajax_success("删除成功");
            } else {
                return ajax_error("删除失败");
            }
        }
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:视频直播更新设备
     **************************************
     */
    public  function  direct_seeding_classification_status(Request $request){       
        if ($request->isPost()) {
            $status = $request->only(["status"])["status"];
            if ($status == 0) {
                $id = $request->only(["id"])["id"];
                $bool = db("direct_seeding")->where("id", $id)->update(["status" => 0]);
                if ($bool) {
                    $this->redirect(url("admin/Material/direct_seeding_classification"));
                } else {
                    $this->error("修改失败", url("admin/Material/direct_seeding_classification"));
                }
            }
            if ($status == 1) {
                $id = $request->only(["id"])["id"];
                $bool = db("direct_seeding")->where("id", $id)->update(["status" => 1]);
                if ($bool) {
                    $this->redirect(url("admin/Material/direct_seeding_classification"));
                } else {
                    $this->error("修改失败", url("admin/Material/direct_seeding_classification"));
                }
            }
        }
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:防伪溯源
     **************************************
     * @return \think\response\View
     */
    public function anti_fake(){
        return view("anti_fake");
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:温湿传感
     **************************************
     * @return \think\response\View
     */
    public function interaction_index(){
        //获取仪器列表
        $store_id=Session::get('store_id');
        $list=db('instrument')->where('store_id',$store_id)->select();
        if($list){
            $pp=1;
        }else{
           $pp=0;
        }
        return view("interaction_index",['pp'=>$pp,'data'=>$list]);
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:温湿传感添加编辑
     **************************************
     * @return \think\response\View
     */
    public function interaction_add(){
        
        return view("interaction_add");
    }
    /**
     **************GY*******************
     * @param Request $request
     * Notes:温湿传感添加编辑
     **************************************
     * @return \think\response\View
     */
    public function interaction_add_do(){
       //获取数据
       $input=input();
       $input['store_id']=Session::get('store_id');
       $re=db('instrument')->where('instrument_number',$input['instrument_number'])->find();
       if(!$re){
           db('instrument')->insert($input);
       }else{
           db('instrument')->where('instrument_number',$input['instrument_number'])->update($input);
       }

        // return view("interaction_index");/
        $this->success('操作成功',url('Material/interaction_index'));
    }
    /**
     * lilu
     * 温湿度查询
     */
    public function wenshidu(){
        //获取店铺id
        $store_id=Session::get('store_id');
        $ret=db('instrument')->where(['instrument_number'=>'8606S86YL8295C5Y','store_id'=>$store_id])->find();
        $ret['update_time']=date('Y-m-d H:i:s' , time());
        if($ret){
             return ajax_success('登录成功',$ret);
        }else{
            return ajax_success('登录失败',$ret);
        }
    }
     /*
        * 发起POST网络提交
        * @params string $url : 网络地址
        * @params json $data ： 发送的json格式数据
        */
        public function https_post($url,$data)
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            if (!empty($data)){
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        }

    //详情
    public function video_comment(){
        return view("video_comment");
    }
 }