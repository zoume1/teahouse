<?php
namespace app\api\model;

use think\Model;

class Give extends Model
{
    protected $table = "tb_give";
    protected $resultSetType = 'collection';

    /**
     * 点赞
     * @author fyk
     * @time   2019/08/26
     */
    public function give($give)
    {
        //查询是否有这条记录
        $user = Give::where(['store_id'=>$give['store_id'],'article_id'=>$give['article_id'],'give_uid'=>$give['give_uid']]) ->find();

        if ($user) {
            $result = Give::destroy(['id' => $user['id']]);

            $res = $result ? ['code' => 1, 'msg' => '取消点赞成功'] : ['code' => 0, 'msg' => '失败'];
            echo json_encode($res);
        } else {
            $inser  = [
                'store_id' => $give['store_id'],
                'article_id'  => $give['article_id'],
                'give_uid'    => $give['give_uid'],
                'type'        => 0,
                'create_time' => time(),
            ];
            $result = Give::create($inser);

            $res = $result ? ['code' => 1, 'msg' => '点赞成功'] : ['code' => 0, 'msg' => '失败'];
            echo json_encode($res);
        }
    }

    /**
     * 点赞总数
     * @author fyk
     * @time   2019/08/26
     */
    public function give_count($sid,$vid)
    {
        $data = Give::where(['store_id'=>$sid,'article_id'=>$vid])-> count();
        return $data;
    }

}