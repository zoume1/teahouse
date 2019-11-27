<?php
namespace app\admin\controller;


use think\Controller;
use think\Db;
use think\Session;


/**
 * lilu
 * excel 导入导出
 */

class Phpexcel extends Controller{
    public function __construct() {
        //这些文件需要下载phpexcel，然后放在vendor文件里面。具体参考上一篇数据导出。
        include('../vendor/PHPExcel/Classes/PHPExcel.php');
        include('../vendor/PHPExcel/Classes/PHPExcel/IOFactory.php');
        include('../vendor/PHPExcel/Classes/PHPExcel/Reader/Excel5.php');
        include('../vendor/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php');
        include('../vendor/PHPExcel/Classes/PHPExcel/Cell.php');
    }

    /**
     * lilu
     * 
     */
    public function import_excel()
    {
         if (!empty ($_FILES ['file_stu'] ['name'])) {
            $tmp_file = $_FILES ['file_stu'] ['tmp_name'];
            $file_types = explode(".", $_FILES ['file_stu'] ['name']);
            $obj_PHPExcel = new \PHPExcel();
            $objReader =\PHPExcel_IOFactory::createReaderForFile($_FILES["file_stu"]['tmp_name']);
            $objReader->setReadDataOnly(true);//这段其实也可以不要
            $obj_PHPExcel =\PHPExcel_IOFactory::load($_FILES["file_stu"]['tmp_name']);
            //这里是加载文件,千万要注意你导入文件的格式不是文件后缀是文件格式,笔者在这个坑上待了很久,
            //$fileType=PHPExcel_IOFactory::identify($filename);上面load()500了可以用着个来校验上传的文件格式如果是html格式你就要注意了.
            $excel_array=$obj_PHPExcel->getsheet(0)->toArray();   //转换为数组格式
            unset($excel_array[0]);    //去除Excel的表头
            //获取
            if(!$excel_array){
                $this->error('检测到上传文件为空，请重新上传');
            }
            $count=count($excel_array[1]);
            $store_id=Session::get('store_id');
            if($count==15){  //
                foreach($excel_array as $k =>$v){
                    $data['id']=$v[0];
                    $data['goods_number']=$v[1];
                    $data['goods_name']=$v[2];
                    $data['goods_brand']=$v[3];
                    $data['produce']=$v[4];
                    $data['category']=$v[5];
                    $data['goods_type']=$v[6];
                    $data['unit']=$v[7];
                    $data['pick_size']=$v[8];
                    $data['goods_repertory']=$v[9];
                    $data['stock_date']=$v[10];
                    $data['frement_date']=$v[11];
                    $data['date']=$v[12];
                    $data['origin']=$v[13];
                    $data['store_id']=$store_id;
                    $data['produceUid']=$v[14];
                    $data['create_time']=time();
                    $data['is_create_good']='0';
                    $re=db('anti_goods')->insert($data);
                }
            }else{
                    foreach($excel_array as $k =>$v){
                        $data['id']=$v[0];
                        $data['goods_name']=$v[1];
                        $data['parent_code']=$v[2];
                        $data['child_code']=$v[3];
                        $data['nfc_num']=$v[4];
                        $data['qr_num']=$v[5];
                        $data['pid']=$v[6];
                        $data['store_id']=$store_id;
                        $data['create_time']=time();
                        $data['produceUid']=$v[7];
                        $re=db('anti_goods')->insert($data);
                    }
            }
            $this->success("导入成功","admin/Material/anti_fake");
        }else{
             $this->error('未检测到上传文件，请重新上传');
        }
    }

    /**
     * lilu
     * 导出
     */

        public function output_excel(){
            //phpexcel
            vendor("PHPExcel.PHPExcel"); //获取PHPExcel类
            $lists = Db::name('orders')->select();
            $objectPHPExcel = new \PHPExcel();
            $objectPHPExcel->setActiveSheetIndex(0);
            $current_page = 0;
            $n = 0;
            foreach ( $lists as $k=>$v )
            {
                $current_page = $current_page +1;
                //表格头的输出
                $objectPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
                $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('A1','订单编号');
                $objectPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('B1','商品名称');
                $objectPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
                $objectPHPExcel->setActiveSheetIndex(0)->setCellValue('C1','商品型号');
                
                $objectPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objectPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objectPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            
                $objectPHPExcel->getActiveSheet()->setCellValue('A'.($n+2) ,$v['order_sn']);
                $objectPHPExcel->getActiveSheet()->setCellValue('B'.($n+2) ,$v['goods_name']);
                $objectPHPExcel->getActiveSheet()->setCellValue('C'.($n+2) ,$v['goods_name_en']);
                //设置边框
                $n = $n +1;
            }
            //设置分页显示
            //$objectPHPExcel->getActiveSheet()->setBreak( 'I55' , PHPExcel_Worksheet::BREAK_ROW );
            //$objectPHPExcel->getActiveSheet()->setBreak( 'I10' , PHPExcel_Worksheet::BREAK_COLUMN );
            $objectPHPExcel->getActiveSheet()->getPageSetup()->setHorizontalCentered(true);
            $objectPHPExcel->getActiveSheet()->getPageSetup()->setVerticalCentered(false);
            ob_end_clean();
            ob_start();
            $file_name = date('Y-m-d_His').'.xls';
            header('Content-Disposition: attachment;filename='.$file_name);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $objWriter= \PHPExcel_IOFactory::createWriter($objectPHPExcel,'Excel2007');
            $objWriter->save('php://output');
            exit;
        }




    
}
