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
use think\Validate;
use app\api\model\VideoFrequency;
use app\admin\controller\Qiniu;


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
        $rest = new VideoFrequency;
        $data = $rest->getList($store_id);
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
             //测试七牛上传图片
           $qiniu=new Qiniu();
           //获取店铺七牛云的配置项
           $peizhi=Db::table('applet')->where('store_id',$store_id)->find();
           $images='icon_image';
           $rr=$qiniu->uploadimg($peizhi['accesskey'],$peizhi['secretkey'],$peizhi['bucket'],$peizhi['domain'],$images);
            // $show_images = $request->file("icon_image");
            if ($rr) {
                // $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["icon_image"] =$rr[0];
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
        $store_id=Session::get('store_id');
         //测试七牛上传图片
         $qiniu=new Qiniu();
         //获取店铺七牛云的配置项
         $peizhi=Db::table('applet')->where('store_id',$store_id)->find();
         $images='icon_image';
         $rr=$qiniu->uploadimg($peizhi['accesskey'],$peizhi['secretkey'],$peizhi['bucket'],$peizhi['domain'],$images);
        // $show_images = $request->file("icon_image");
        if ($rr) {
            // $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
            $data["icon_image"] = $rr[0];
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
            // $show_images = $request->file("icon_image");
             //测试七牛上传图片
            $qiniu=new Qiniu();
            //获取店铺七牛云的配置项
            $peizhi=Db::table('applet')->where('store_id',$store_id)->find();
            $images='icon_image';
            $rr=$qiniu->uploadimg($peizhi['accesskey'],$peizhi['secretkey'],$peizhi['bucket'],$peizhi['domain'],$images);
            if ($rr) {
                // $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["icon_image"] = $rr[0];
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
            $store_id=Session::get('store_id');
            // $show_images = $request->file("icon_image");
                //测试七牛上传图片
            $qiniu=new Qiniu();
            //获取店铺七牛云的配置项
            $peizhi=Db::table('applet')->where('store_id',$store_id)->find();
            $images='icon_image';
            $rr=$qiniu->uploadimg($peizhi['accesskey'],$peizhi['secretkey'],$peizhi['bucket'],$peizhi['domain'],$images);
            if ($rr) {
                // $show_images = $request->file("icon_image")->move(ROOT_PATH . 'public' . DS . 'uploads');
                $data["icon_image"] =$rr[0];
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
     **************lilu*******************
     * @param Request $request
     * Notes:防伪溯源---判断是否需要同步数据
     **************************************
     * @return \think\response\View
     */
    public function anti_fake_judge(){
        $store_id=Session::get('store_id');
        // $sql='SELECT v_test.* FROM  v_test where v_test.id = 49';
        $con=mysqli_connect("39.97.124.73:50306","root","Lingtian2118",'lingtian_wms_xm');
        if($con)
        {
            //1.统计子标表里的总数
            $sql2='SELECT v_trace_subscript.* FROM  v_trace_subscript GROUP BY child_code';
            $res2= mysqli_query($con,$sql2);
            //2.统计茶仓里子标的总数
            $count=db('anti_parent_code')->where('store_id',$store_id)->count();
            if($count==$res2->num_rows)
            {
                //无需更新数据，已同步
                return ajax_success('数据已同步');
            }else{
                //同步数据
                return ajax_error('需同步数据');
            }
            //1.获取商品列表，导入自己的数据库
            // $sql='SELECT v_trace_commodity.* FROM  v_trace_commodity where produceUid = 47 limit 1 ';
            // $res= mysqli_query($con,$sql);
            // $rr=$res->fetch_all(MYSQLI_ASSOC);
            // halt($res);
            // foreach($rr as $k =>$v){
            //     $v['create_time']=time();
            //     $v['store_id']=$store_id;
            //     $v['produceUid']='50';
            //     db('anti_goods')->insert($v);
            // }
            // //2.获取目标列表，导入自己的数据库
            // $sql2='SELECT v_trace_subscript.* FROM  v_trace_subscript  ';
            // $res2= mysqli_query($con,$sql2);
            // $rr2=$res2->fetch_all(MYSQLI_ASSOC);
            // foreach($rr2 as $k2 =>$v2){
            //     $v2['create_time']=time();
            //     $v2['store_id']=$store_id;
            //     $v2['produceUid']='50';
            //     db('anti_parent_code')->insert($v2);
            // }
            // halt($rr);
        }
    }

    /**
     * lilu
     * 防伪溯源同步数据
     */
    public function anti_fake_dts()
    {
        $store_id=Session::get('store_id');
        // $sql='SELECT v_test.* FROM  v_test where v_test.id = 49';
        $con=mysqli_connect("39.97.124.73:50306","root","Lingtian2118",'lingtian_wms_xm');
        if($con)
        {
            //1.获取商品列表，导入自己的数据库
            $sql='SELECT v_trace_commodity.* FROM  v_trace_commodity where produceUid = 47 GROUP by id  ';
            $res= mysqli_query($con,$sql);
            $rr=$res->fetch_all(MYSQLI_ASSOC);
            foreach($rr as $k =>$v){
                $ids=db('anti_goods')->where('store_id',$store_id)->column('id');
                if(in_array($v['id'],$ids)){
                    continue;
                }
                $v['create_time']=time();
                $v['store_id']=$store_id;
                $v['produceUid']='50';
                $v['goods_number']=get_random();
                db('anti_goods')->insert($v);
            }
            //2.获取母标，子标记录列表
            $sql2='SELECT v_trace_subscript.* FROM  v_trace_subscript  ';
            $res2= mysqli_query($con,$sql2);
            $rr2=$res2->fetch_all(MYSQLI_ASSOC);
            foreach($rr2 as $k2 =>$v2){
                //获取子母标的id序列
                $ids2=db('anti_parent_code')->where('store_id',$store_id)->column('child_code');
                if(in_array($v2['child_code'],$ids2)){
                    continue;
                }
                $v2['create_time']=time();
                $v2['store_id']=$store_id;
                $v2['produceUid']='50';
                db('anti_parent_code')->insert($v2);
            }
            return ajax_success('同步成功');
        }

    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:防伪溯源
     **************************************
     * @return \think\response\View
     */
    public function anti_fake()
    {
        // //获取商品的list
        // $store_id = Session::get('store_id');
        // $rr=db('anti_goods')->where('store_id',$store_id)->select();
        // //获取会员范围
        // $scope = db("member_grade")->where("store_id","EQ",$store_id)->field("member_grade_name")->select();
        // return view("anti_fake",['data'=>$rr,'scope'=>$scope]);
        $userName="18510393696";
            $password="zhcc63268696";
            $url_login='https://api.dtuip.com/qy/user/login.html';
            $data_login= '{
                "userName":"18510393696",
                "password":"zhcc63268696",
            }';
            $login=$thsi->https_post($url_login,$data_login);
            halt($login);
            $userApiKey='';   //      zhcc63268696
            $deviceNo='8606S86YL8295C5Y';

            // $data = '{
            //     // "action":"add",
            //     // "requestdomain":"https://'.$domain.'",
            //     // "wsrequestdomain":"wss://'.$domain.'",
            //     // "uploaddomain":"https://'.$domain.'",
            //     // "downloaddomain":"https://'.$domain.'"
            // }';
            // $url = "https://api.dtuip.com/qy/device/queryDevMoniData.html";
            // $res = https_post($url,$data);
            // var_dump($res);
           
    }
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
    /**
     **************GY*******************
     * @param Request $request
     * Notes:温湿传感
     **************************************
     * @return \think\response\View
     */
    public function interaction_index(){
        $name = input('name');
        $store_id = Session::get('store_id');
        if(!empty($name)){
            $list = Db::name('instrument')
                ->where('store_id',$store_id)
                ->where('instrument_number|store_name', 'like', '%' . trim($name) . '%')
                ->order(['create_time' => 'desc'])
                ->paginate(20, false, [
                'query' => \request()->request()
            ]);
        } else {
            $list = Db::name('instrument')
            ->where('store_id',$store_id)
            ->order(['create_time' => 'desc'])
            ->paginate(20, false, [
            'query' => \request()->request()
            ]);
        }
        return view("interaction_index",['data'=>$list]);
    }

    /**
     **************GY*******************
     * @param Request $request
     * Notes:温湿传感添加编辑
     **************************************
     * @return \think\response\View
     */
    public function interaction_add(Request $request){
        if($request->isPost()){
            $store_id = Session::get('store_id');
            $data = Request::instance()->param();
            $data['create_time'] = time();
            $data['store_id'] = $store_id;
            $restul = db('instrument')->insertGetId($data);
            if($restul < 0){
                $this->error('新增失败',url('admin/Material/interaction_index'));
            }
            $this->success('新增成功',url('admin/Material/interaction_index'));
        }
        $store_id = Session :: get("store_id");
        $store_name = Db::name('store_house')
                    ->where('store_id','=',$store_id)
                    ->select();
        return view("interaction_add",['store_name'=>$store_name]);
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
       $input = input();
       $input['store_id']=Session::get('store_id');
       $re=db('instrument')->where('instrument_number',$input['instrument_number'])->find();
       if(!$re){
            db('instrument')->insert($input);
       }else{
            db('instrument')->where('instrument_number',$input['instrument_number'])->update($input);
       }
        $this->success('操作成功',url('Material/interaction_index'));
    }

    /**gy
     * 温湿度删除
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function interaction_delete($id)
    {
        $bool = Db::name('instrument')
                ->where('id','=',$id)
                ->delete();
        if(!$bool){
            $this->error('删除失败',url('Material/interaction_index'));
        }
        $this->success('删除成功',url('Material/interaction_index'));
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
    //  /*
    //     * 发起POST网络提交
    //     * @params string $url : 网络地址
    //     * @params json $data ： 发送的json格式数据
    //     */
    //     public function https_post($url,$data)
    //     {
    //         $curl = curl_init();
    //         curl_setopt($curl, CURLOPT_URL, $url);
    //         if (!empty($data)){
    //             curl_setopt($curl, CURLOPT_POST, 1);
    //             curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    //         }
    //         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //         $output = curl_exec($curl);
    //         curl_close($curl);
    //         return $output;
    //     }

    //详情
    public function video_comment(){
        return view("video_comment");
    }

    //直播token
    public function video_token()
    {

        $data = db('config')->find();

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
     * lilu
     * 物联--防伪溯源--芯片详情
     */
    public function fake_chip()
    {
        $input=input();
        $store_id=Session::get('store_id');
        if(!empty($input)){
            //检索--获取id下的母标的列表
            $rr=db('anti_parent_code')->where(['store_id'=>$store_id,'pid'=>$input['id']])->group('parent_code')->select();
            return view("fake_chip",['data'=>$rr]);
        }else{
            $this->error('获取参数失败');
        }
    }
    /**
     * lilu
     * 物联--防伪溯源--芯片详情
     */
    public function chip_details()
    {
        $input=input();
        $store_id=Session::get('store_id');
        if(!empty($input)){
            //检索--获取id下的母标的列表
            $rr=db('anti_parent_code')->where(['store_id'=>$store_id,'parent_code'=>$input['parent_code']])->select();
            return view("chip_details",['data'=>$rr]);
        }else{
            $this->error('获取参数失败');
        }
    }

     /**
     * lilu
     * 防伪溯源-生成商品
     * id    防伪溯源商品id
     */
    public function create_good()
    {
        $input=input();
        $store_id=Session::get('store_id');
        //测试七牛上传图片
        $qiniu=new Qiniu();
        //获取店铺七牛云的配置项
        $peizhi=Db::table('applet')->where('store_id',$store_id)->find();
        $images='goods_show_images';
        $rr=$qiniu->uploadimg($peizhi['accesskey'],$peizhi['secretkey'],$peizhi['bucket'],$peizhi['domain'],$images);
        if(empty($rr)){
          
        }else{
         $data["goods_show_image"] =  $rr[0];
         $data["goods_show_images"] = implode(',', $rr);
        }
        //获取防伪溯源商品的信息
        $anti_info=db('anti_goods')->where('id',$input['id'])->find();
        $data['goods_name']=$anti_info['goods_name'];
        $data['produce']=$anti_info['produce'];
        $data['brand']=$anti_info['goods_brand'];
        $data['goods_number']=$anti_info['goods_number'];
        $data['date']=$anti_info['frement_date'];
        $data['goods_selling']=$input['goods_selling'];
        $data['goods_standard']=0;
        $data['goods_new_money']=$input['goods_new_money'];
        $data['goods_bottom_money']=$input['goods_bottom_money'];
        $data['goods_cost']=$input['goods_cost'];
        $data['goods_repertory']=$anti_info['goods_repertory'];
        $data['goods_franking']=0;
        $data['label']=0;
        $data['status']=0;
        // //判断商品的分类是否存在
        // $data['pid']=$anti_info['category'];
        if(!empty($input["scope"])){
            $data["scope"] = implode(',', $input["scope"]);
        } else {
            $data["scope"] = "";
        }
        $data['store_id']=Session::get('store_id');
        if($data)
        {
            //生成新的商品
            $re=db('goods')->insert($data);
            if($re){
                //修改防伪溯源商品的生成状态
                $res=db('anti_goods')->where('id',$input['id'])->update(['is_create_good'=>1]);
                $this->success("生成成功", url("admin/Material/anti_fake"));
            }else{
                $this->error('生成失败');
            }
        }else{
            $this->error('获取参数失败');
        }

    }

    
 }
