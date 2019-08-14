<?php
namespace app\admin\model;
use think\Model;
const ONE = 1;
class Leaguer extends Model
{
    protected $name = 'member';
    /**
     * 获取会员级别人数
     * @param $member_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getMemberRank($member_id)
    {
  
    }
 
 
}