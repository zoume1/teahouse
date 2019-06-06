<?php
/**
 * Created by PhpStorm.
 * User: 李火生
 * Date: 2018/10/25
 * Time: 11:22
 */
namespace  app\admin\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\paginator\driver\Bootstrap;
use think\Session;

class User extends Controller{
    /**
     **************李火生*******************
     * @return \think\response\View
     * 会员首页
     **************************************
     */
    public function index(){
        $store_id = Session::get("store_id");
        $user_data =Db::name('member')->where("store_id",'EQ',$store_id)->order("member_id","desc")->paginate(20 ,false, [
            'query' => request()->param(),
        ]);
        $grade_data =Db::name("member_grade")->where("store_id",'EQ',$store_id)->field("member_grade_id,member_grade_name")->select();
        return view('index',['user_data'=>$user_data,"grade_data"=>$grade_data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:订单搜索
     **************************************
     */
    public function user_search(){
        $store_id = Session::get("store_id");
        $search_a =input("search_a") ? input("search_a"):null;
        $grade_type =input("grade_type") ? input("grade_type"):null;
        $time_min  =input("date_min") ? input("date_min"):null;
        $date_max  =input('date_max') ? input('date_max'):null;
        if(!empty($search_a)){
            $condition =" `member_id` like '%{$search_a}%' or `member_name` like '%{$search_a}%' or `member_phone_num` like '%{$search_a}%' or `member_real_name` like '%{$search_a}%' or `ID_card` like '%{$search_a}%'";
            $user_data = Db::name('member')->where("store_id","EQ",$store_id)->where($condition)->order("member_id","desc")->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        }else if (!empty($grade_type)){
            $user_data =Db::name('member')->where("store_id","EQ",$store_id)->where("member_grade_id",$grade_type)->order("member_id","desc")->paginate(20 ,false, [
                'query' => request()->param(),
            ]);
        }else{
            if(!empty($time_min)){
                $timemin =strtotime($time_min);
            }
            if(!empty($date_max)){
                /*添加一天（23：59：59）*/
                $t=date('Y-m-d H:i:s',strtotime($date_max)+1*24*60*60);
                $timemax  =strtotime($t);

            }
            if(!empty($time_min) && empty($date_max)){
                $time_condition  = "member_create_time>{$timemin}";
                //开始时间
                $user_data =Db::name('member')->where("store_id","EQ",$store_id)->where($time_condition)->order("member_id","desc")->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);
            }else if (empty($time_min) && (!empty($date_max))){
                $time_condition  = "member_create_time< {$timemax}";
                //结束时间
                $user_data =Db::name('member')->where("store_id","EQ",$store_id)->where($time_condition)->order("member_id","desc")->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);
            }else if((!empty($timemin)) && (!empty($date_max))){
                $time_condition  = "member_create_time>{$timemin} and member_create_time< {$timemax}";
                //既有开始又有结束
                $user_data =Db::name('member')->where("store_id","EQ",$store_id)->where($time_condition)->order("member_id","desc")->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);
            }else{
                $user_data =Db::name('member')->where("store_id","EQ",$store_id)->order("member_id","desc")->paginate(20 ,false, [
                    'query' => request()->param(),
                ]);

            }
        }
        $grade_data =Db::name("member_grade")->where("store_id","EQ",$store_id)->field("member_grade_id,member_grade_name")->select();
        return view('index',['user_data'=>$user_data,"grade_data"=>$grade_data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:客户账户启用状态编辑
     **************************************
     */
    public function  status(Request $request){
        if($request->isPost()){
            $data =$_POST;
            $store_id = Session::get("store_id");
            if(!empty($data)){
                $bool =Db::name('member')->where("store_id","EQ",$store_id)->where('member_id',$data['id'])->update(['member_status'=>$data['status']]);
                if($bool){
                    return ajax_success('修改成功',['status'=>1]);
                }else{
                    return ajax_error('修改失败',['status'=>0]);
                }
            }
        }
    }

    /**
     **************李火生*******************
     * @return \think\response\View
     * 会员编辑
     **************************************
     */
    public function edit($id){
        $store_id = Session::get("store_id");
        $member_data = Db::name('member')->where('member_id',$id)->find();
        $term_data = Db::name('member_grade')->where("store_id","EQ",$store_id)->select();
        return view('edit',['member_data'=>$member_data,'term_data'=>$term_data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员更新
     **************************************
     * @param $id
     */
    public function update($id){
        if($this->request->isPost()){
            $data = $data =$this->request->post();
            $grade_name =Db::name('member_grade')->field('member_grade_name')->where('member_grade_id',$data['member_grade_id'])->find();
            $data['member_grade_name'] = $grade_name['member_grade_name'];
            if(!empty($id)){
                $bool =Db::name('member')->where('member_id',$id)->update($data);
                if($bool){
                    $this->success('编辑成功','admin/User/index');
                }else{
                    $this->error('编辑失败');
                }
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员删除
     **************************************
     * @param $id
     */
    public function del($id){
        $bool = db("member")->where("member_id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/User/index"));
        } else {
            $this->error("删除失败", url("admin/User/index"));
        }
    }



    /**
     **************李火生*******************
     * @return \think\response\View
     * 会员等级
     **************************************
     */
    public function  grade(){
       $store_id = Session::get("store_id");
       $grade_data = Db::name('member_grade')->where("store_id","EQ",$store_id)->paginate(20 ,false, [
           'query' => request()->param(),
       ]);
        return view('grade',['grade_data'=>$grade_data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员等级编辑(添加编辑都在这)
     **************************************
     * @param null $id
     * @return \think\response\View
     */
    public function grade_edit($id =null){
        $store_id = Session::get("store_id");
        $term_data = Db::name('term')->select();
        if($id > 0){
            $info =Db::name('member_grade')->where("store_id","EQ",$store_id)->where("member_grade_id",$id)->find();
            $this->assign('info',$info);
        }
        if($this->request->isPost()){
            $data =$this->request->post();
            $data['create_time'] =time();
            $file =$this->request->file("member_grade_img");
            if($file){
                $datas = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                $images_url = str_replace("\\","/",$datas->getSaveName());
                $data['member_grade_img'] =$images_url;
            }
            if($id > 0){
                $res =Db::name('member_grade')->where('member_grade_id',$id)->update($data);
            }else{
                $res =Db::name('member_grade')->insertGetId($data);
            }
            if($res>0){
                $this->success('编辑成功','admin/User/grade');
            }else{
                $this->error('编辑失败');
            }
        }
        return view('grade_edit',['term_data'=>$term_data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员等级添加（写在编辑里面）
     **************************************
     * @return \think\response\View
     */
	public function  grade_add(){
		return view('grade_add');
	}

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员等级图片删除
     **************************************
     * @param Request $request
     */
	public function  grade_start_image_del(Request $request){
        if ($request->isPost()) {
            $id = $request->only(['id'])['id'];
            $image_url = Db::name('member_grade')->where("member_grade_id", $id)->field("member_grade_img")->find();
            if ($image_url['member_grade_img'] != null) {
                unlink(ROOT_PATH . 'public' . DS . 'uploads/' . $image_url['member_grade_img']);
            }
            $bool = Db::name('member_grade')->where("member_grade_id", $id)->field("member_grade_img")->update(["member_grade_img" => null]);
            if ($bool) {
                return ajax_success("删除成功");
            } else {
                return ajax_error("删除失败");
            }
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员等级列表删除（注意没有把图片删除，其他地方有用到）
     **************************************
     * @param $id
     */
    public function grade_del($id){
        $bool = db("member_grade")->where("member_grade_id", $id)->delete();
        if ($bool) {
            $this->success("删除成功", url("admin/User/grade"));
        } else {
            $this->error("删除失败", url("admin/User/grade"));
        }
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:会员等级列表状态值修改
     **************************************
     * @param Request $request
     */
    public function  grade_status(Request $request){
        if($request->isPost()){
            $data =$_POST;
            if(!empty($data)){
                $bool =Db::name('member_grade')->where('member_grade_id',$data['id'])->update(['introduction_display'=>$data['status']]);
                if($bool){
                    return ajax_success('修改成功',['status'=>1]);
                }else{
                    return ajax_error('修改失败',['status'=>0]);
                }
            }
        }
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:微信提现
     **************************************
     * @return \think\response\View
     */
    public function recharge_application(){
        $data =Db::table("tb_recharge_reflect")
            ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_phone_num,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
            ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
            ->where("operation_type",-1)
            ->where("pay_type_content","微信")
            ->select();
        $all_idents =$data ;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页3行记录
        $showdata = array_slice($all_idents, ($curPage - 1)*$listRow, $listRow,true);// 数组中根据条件取出一段值，并返回
        $data = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path'     => url('admin/User/withdrawal_application'),//这里根据需要修改url
            'query'    =>  [],
            'fragment' => '',
        ]);
        $data->appends($_GET);
        $this->assign('listpage', $data->render());
        return view("recharge_application",["data"=>$data]);
    }


    /**
     **************李火生*******************
     * @param Request $request
     * Notes:微信提现搜索
     **************************************
     * @return \think\response\View
     */
    public function recharge_application_search(){
        $search_a =input("search_a") ? input("search_a"):null;
        $time_min  =input("date_min") ? input("date_min"):null;
        $date_max  =input('date_max') ? input('date_max'):null;
        $arr_condition ="`operation_type` = '-1' and `pay_type_content` = '微信' ";
        if(!empty($search_a)){
            $where ="`member_name` = '{$search_a}' or `member_phone_num` = '{$search_a}' ";
            $data =Db::table("tb_recharge_reflect")
                ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_phone_num,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
                ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
                ->where($arr_condition)
                ->where($where)
                ->select();
        }else{
            if(!empty($time_min)){
                $timemin =strtotime($time_min);
            }
            if(!empty($date_max)){
                /*添加一天（23：59：59）*/
                $t=date('Y-m-d H:i:s',strtotime($date_max)+1*24*60*60);
                $timemax  =strtotime($t);
            }
            if(!empty($time_min) && empty($date_max)){
                $time_condition  = "operation_linux_time>{$timemin}";
                //开始时间
                $data =Db::table("tb_recharge_reflect")
                    ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
                    ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
                    ->where($arr_condition)
                    ->where($time_condition)
                    ->select();
            }else if (empty($time_min) && (!empty($date_max))){
                $time_condition  = "operation_linux_time< {$timemax}";
                //结束时间
                $data =Db::table("tb_recharge_reflect")
                    ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
                    ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
                    ->where($arr_condition)
                    ->where($time_condition)
                    ->select();
            }else if((!empty($timemin)) && (!empty($date_max))){
                $time_condition  = "operation_linux_time>{$timemin} and operation_linux_time< {$timemax}";
                //既有开始又有结束
                $data =Db::table("tb_recharge_reflect")
                    ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
                    ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
                    ->where($arr_condition)
                    ->where($time_condition)
                    ->select();
            }else{
                $data =Db::table("tb_recharge_reflect")
                    ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
                    ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
                    ->where($arr_condition)
                    ->select();

            }
        }

        $all_idents =$data ;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页3行记录
        $showdata = array_slice($all_idents, ($curPage - 1)*$listRow, $listRow,true);// 数组中根据条件取出一段值，并返回
        $data = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path'     => url('admin/User/withdrawal_application'),//这里根据需要修改url
            'query'    =>  [],
            'fragment' => '',
        ]);
        $data->appends($_GET);
        $this->assign('listpage', $data->render());
        return view("recharge_application",["data"=>$data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:银行卡提现
     **************************************
     * @return \think\response\View
     */
    public function withdrawal_application(){
        $data =Db::table("tb_recharge_reflect")
            ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_phone_num,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
            ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
            ->where("operation_type",-1)
            ->where("pay_type_content","银行卡")
            ->select();
        $all_idents =$data ;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页3行记录
        $showdata = array_slice($all_idents, ($curPage - 1)*$listRow, $listRow,true);// 数组中根据条件取出一段值，并返回
        $data = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path'     => url('admin/User/withdrawal_application'),//这里根据需要修改url
            'query'    =>  [],
            'fragment' => '',
        ]);
        $data->appends($_GET);
        $this->assign('listpage', $data->render());
        return view("withdrawal_application",["data"=>$data]);
    }
    /**
     **************李火生*******************
     * @param Request $request
     * Notes:银行卡提现
     **************************************
     * @return \think\response\View
     */
    public function withdrawal_application_search(){
        $search_a =input("search_a") ? input("search_a"):null;
        $time_min  =input("date_min") ? input("date_min"):null;
        $date_max  =input('date_max') ? input('date_max'):null;
        $arr_condition ="`operation_type` = '-1' and `pay_type_content` = '银行卡' ";
        if(!empty($search_a)){
            $where ="`member_name` = '{$search_a}' or `member_phone_num` = '{$search_a}' ";
            $data =Db::table("tb_recharge_reflect")
                ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_phone_num,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
                ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
                ->where($arr_condition)
                ->where($where)
                ->select();
        }else{
            if(!empty($time_min)){
                $timemin =strtotime($time_min);
            }
            if(!empty($date_max)){
                /*添加一天（23：59：59）*/
                $t=date('Y-m-d H:i:s',strtotime($date_max)+1*24*60*60);
                $timemax  =strtotime($t);
            }
            if(!empty($time_min) && empty($date_max)){
                $time_condition  = "operation_linux_time>{$timemin}";
                //开始时间
                $data =Db::table("tb_recharge_reflect")
                    ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
                    ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
                    ->where($arr_condition)
                    ->where($time_condition)
                    ->select();
            }else if (empty($time_min) && (!empty($date_max))){
                $time_condition  = "operation_linux_time< {$timemax}";
                //结束时间
                $data =Db::table("tb_recharge_reflect")
                    ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
                    ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
                    ->where($arr_condition)
                    ->where($time_condition)
                    ->select();
            }else if((!empty($timemin)) && (!empty($date_max))){
                $time_condition  = "operation_linux_time>{$timemin} and operation_linux_time< {$timemax}";
                //既有开始又有结束
                $data =Db::table("tb_recharge_reflect")
                    ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
                    ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
                    ->where($arr_condition)
                    ->where($time_condition)
                    ->select();
            }else{
                $data =Db::table("tb_recharge_reflect")
                    ->field("tb_recharge_reflect.*,tb_member.member_name,tb_member.member_real_name,tb_member.member_wallet,tb_member.member_recharge_money")
                    ->join("tb_member","tb_recharge_reflect.user_id=tb_member.member_id",'left')
                    ->where($arr_condition)
                    ->select();

            }
        }

        $all_idents =$data ;//这里是需要分页的数据
        $curPage = input('get.page') ? input('get.page') : 1;//接收前段分页传值
        $listRow = 20;//每页3行记录
        $showdata = array_slice($all_idents, ($curPage - 1)*$listRow, $listRow,true);// 数组中根据条件取出一段值，并返回
        $data = Bootstrap::make($showdata, $listRow, $curPage, count($all_idents), false, [
            'var_page' => 'page',
            'path'     => url('admin/User/withdrawal_application'),//这里根据需要修改url
            'query'    =>  [],
            'fragment' => '',
        ]);
        $data->appends($_GET);
        $this->assign('listpage', $data->render());
        return view("withdrawal_application",["data"=>$data]);
    }





    /**
     **************李火生*******************
     * @param Request $request
     * Notes:提现设置
     **************************************
     * @return \think\response\View
     */
    public function withdrawal_setting(){
        $data =Db::name("withdrawal")->where("id",1)->select();
        return view("withdrawal_setting",["data"=>$data]);
    }

    /**
     **************李火生*******************
     * @param Request $request
     * Notes:提现设置更新数据
     **************************************
     * @param Request $request
     */
    public function  withdrawal_save(Request $request){
        if($request->isPost()){
            $min_money =$request->only(["min_money"])["min_money"];
            $day_max_money =$request->only(["day_max_money"])["day_max_money"];
            $day_frequency =$request->only(["day_frequency"])["day_frequency"];
            $service_charge =$request->only(["service_charge"])["service_charge"];

            if(empty($min_money) || empty($day_max_money) || empty($day_frequency) || empty($service_charge)){
                $this->error("所传参数不能为空");
            }
            $data =[
                "min_money"=>$min_money,
                "day_max_money"=>$day_max_money,
                "day_frequency"=>$day_frequency,
                "service_charge"=>$service_charge
            ];
            $bool =Db::name("withdrawal")->where("id",1)->update($data);
            if($bool){
                $this->success("修改成功");
            }else{
                $this->error("数据未改动");
            }
        }
    }


}