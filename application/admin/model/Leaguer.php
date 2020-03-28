<?php
namespace app\admin\model;
use think\Model;
const ONE = 1;
class Leaguer extends Model
{
    protected $name = 'leaguer';
    /**
     * 获取成员分销设置
     * @param $member_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getMemberScale($member_id)
    {
        $scale_data = $this->where('member_id',"=",$member_id)->where("status","=",ONE)->find();
        if($scale_data){
            $grade = explode(",",$scale_data['grade']);
            $award = explode(",",$scale_data['award']);
            $scale = explode(",",$scale_data['scale']);
            $integral = explode(",",$scale_data['integral']);
            $data = [
                'grade' => $grade,
                'award' => $award,
                'scale' => $scale,
                'integral' => $integral
            ];
            return $data;
        }
        return $scale_data;   
    }
 
 
}
