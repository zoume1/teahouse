<?php
/**
 * Created by PhpStorm.
 * User: FYK
 * Date: 2019/8/23 0028
 * Time: 16:57
 */
namespace app\api\controller;
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
<<<<<<< HEAD

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

    //视频评论
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
            $this->error('提交失败：' . $validate->getError());
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


=======
    
    
>>>>>>> 3bf58001c45cc5924cf9ed2c29f38a6869456546
}