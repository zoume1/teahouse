<?php
/**
 * Created by PhpStorm.
 * User: FYK
 * Date: 2019/8/27 0028
 * Time: 16:57
 */
namespace app\api\controller;
use app\api\model\Member;
use think\Controller;
use think\Validate;
use think\Request;
use app\api\model\DirectSeeding; 
use app\api\model\VideoFrequency;
use app\api\model\Give;
use app\api\model\VideoComment;

class Live extends Controller{

    //查询直播分类
    public function classification($store_id){

        $classif = new DirectSeeding();
        $data = $classif->detail($store_id);
        
        foreach($data as $k=>$v){
            $list = new VideoFrequency();
            $list1 = $list->live_broadcast($store_id,$v['id']);
            $data[$k]['list'] = $list1;
           // print_r($list1);die;
        }
       
        $res = $data ?['code'=>1,'msg'=>'获取成功','data'=>$data] : ['code'=>0,'msg'=>'获取失败'];

        return json($res);exit;

    }


    //直播列表
    public function video_list($store_id,$class_id){

        $list = new VideoFrequency();
        $data = $list->live_broadcast($store_id,$class_id);

        $res = $data ?['code'=>1,'msg'=>'获取成功','data'=>$data] : ['code'=>0,'msg'=>'获取失败'];

        return json($res);exit;
    }

    //详情
    public function details($store_id,$vid){
        $list = new VideoFrequency();
       
        $browsing = $list->live_browsing($store_id,$vid);

        $data = $list->live_details($store_id,$vid);
        $res = $data ?['code'=>1,'msg'=>'获取成功','data'=>$data] : ['code'=>0,'msg'=>'获取失败'];

        return json($res);exit;
    }

    //视频点赞
    public  function  video_give(){
        $request      = Request::instance();
        $param        = $request->param();//获取所有参数，最全
        $validate     = new Validate([
            ['user_id', 'require'],
            ['store_id', 'require'],
            ['vid', 'require', '主题id不能为空'],
        ]);
        //验证部分数据合法性
        if (!$validate->check($param)) {
            return json(['code' => 0,'msg' => $validate->getError()]);
        }
       $give = [
            'give_uid'=>$param['user_id'],
            'store_id' =>$param['store_id'],
            'article_id' =>$param['vid'],
       ];
        $list = new Give();

        $list->give($give);

    }

    //视频评论新增
    public  function  video_comment(){
        $request      = Request::instance();
        $param        = $request->param();//获取所有参数，最全

        $validate = new Validate([
            ['store_id', 'require', '店铺id不能为空'],
            ['vid', 'require', '视频id不能为空'],
            ['user_id', 'require', '用户id不能为空'],
            ['content', 'require', '内容不能为空'],
        ]);
        //验证部分数据合法性
        if (!$validate->check($param)) {
            return json(['code' => 0,'msg' => $validate->getError()]);
        }
        $add_comment = new VideoComment();
        $add_comment->data([
            'store_id'    => $param['store_id'],
            'topic_id'    => $param['vid'],
            'from_uid'    => $param['user_id'],
            'content'     => $param['content'],
            'create_time' => time()
        ]);
        $result = $add_comment->save();

        $res = $result ? ['code' => 1, 'msg' => '评论成功'] : ['code' => 0, 'msg' => '评论失败'];
        return json($res);exit();
    }

    /**
     * 视频回复评论
     * @author fyk
     * @time   2019/08/27
     */
    public function video_reply()
    {
        $request = Request::instance();
        $param   = $request->param();//获取所有参数，最全
        $validate     = new Validate([
            ['sid', 'require', '店铺id不能为空'],
            ['cid', 'require', '评论id不能为空'],
            ['uid', 'require', '回复用户id不能为空'],
            ['aid', 'require', '视频id不能为空'],
            ['con', 'require', '内容不能为空'],
        ]);
        //验证部分数据合法性
        if (!$validate->check($param)) {
            return json(['code' => 0,'msg' => $validate->getError()]);
        }
        $add_comment = new VideoComment();
        $add_comment->data([
            'store_id'    => $param['sid'],
            'topic_id'    => $param['aid'],
            'from_uid'    => $param['uid'],
            'to_uid'      => $param['cid'],
            'content'     => $param['con'],
            'create_time' => time()
        ]);

        $result = $add_comment->save();

        $res = $result ? ['code' => 1, 'msg' => '回复成功'] : ['code' => 0, 'msg' => '回复失败'];

        return json($res);exit();
    }

    //视频评论主页
    public function video_index()
    {
        $request = Request::instance();
        $param   = $request->param();//获取所有参数，最全

        $validate = new Validate([
            ['store_id', 'require', '店铺id不能为空'],
            ['vid', 'require', '视频id不能为空'],
        ]);
        //验证部分数据合法性
        if (!$validate->check($param)) {
            return json(['code' => 0,'msg' => $validate->getError()]);
        }

        $store_id = $param['store_id'];
        $vid = $param['vid'];
        //实例化model
        $list = new VideoComment();
        $data = $list->video($store_id,$vid);
        foreach ($data as $k =>$v){
            $user = new Member();//个人信息
            $user_name = $user -> user($v['from_uid']);
            $data[$k]['user_name'] = $user_name['member_name'];
            $data[$k]['user_img'] = $user_name['member_head_img'];
            //是否有二级评论
            $comment = $list ->level($v['store_id'],$v['topic_id'],$v['id']);
            foreach ($comment as $k1 => $v1){
                $user_name = $user -> user($v1['from_uid']);
                $comment[$k1]['user_name'] =  $user_name['member_name'];
            }
            $data[$k]['list'] = $comment;

        }
        $res = $data ?['code'=>1,'msg'=>'获取成功','data'=>$data] : ['code'=>0,'msg'=>'获取失败'];

        return json($res);exit;


    }


}