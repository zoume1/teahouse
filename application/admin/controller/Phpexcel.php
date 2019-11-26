<?php
namespace app\admin\controller;


use think\Controller;
use think\Db;
use think\Session;
// include('../vendor/PHPExcel/Classes/PHPExcel.php');
// include('../vendor/PHPExcel/Classes/PHPExcel/IOFactory.php');
// include('../vendor/PHPExcel/Classes/PHPExcel/Reader/Excel5.php');
// include('../vendor/PHPExcel/Classes/PHPExcel/Reader/Excel2007.php');

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
     * 导入
     */
    public function import_excel()
    {
         if (!empty ($_FILES ['file_stu'] ['name'])) {
            $tmp_file = $_FILES ['file_stu'] ['tmp_name'];
            $file_types = explode(".", $_FILES ['file_stu'] ['name']);
            $file_type = $file_types [count($file_types) - 1];
            /*判别是不是.xls文件，判别是不是excel文件*/
            if (strtolower($file_type) != "xls") {
                $this->error('不是Excel文件，重新上传');
            }
            /*设置上传路径*/
            /*百度有些文章写的上传路径经过编译之后斜杠不对。不对的时候用大写的DS代替，然后用连接符链接就可以拼凑路径了。*/
            $savePath = ROOT_PATH . 'public' . DS . 'upload' . DS;

            /*以时间来命名上传的文件*/
            $str = date('Ymdhis');
            $file_name = $str . "." . $file_type;
            /*是否上传成功*/
            if (!copy($tmp_file, $savePath . $file_name)) {
                $this->error('上传失败');
            }
            /*
            *对上传的Excel数据进行处理生成编程数据,这个函数会在下面第三步的ExcelToArray类中
            *注意：这里调用执行了第三步类里面的read函数，把Excel转化为数组并返回给$res,再进行数据库写入
            */
            // require THINK_PATH.'Library/Org1/Util/ExcelToArrary.class.php';//导入excelToArray类
          //引入这个类试了百度出来的好几个方法都不行。最后简单粗暴的使用了require方式。这个类想放在哪里放在哪里。只要路径对就行。
            // $ExcelToArrary=new \ExcelToArrary();//实例化

            $res=$this->read($savePath.$file_name,"UTF-8",$file_type);//传参,判断office2007还是office2003

            /*对生成的数组进行数据库的写入*/
            foreach ($res as $k => $v) {
                if ($k > 1) {
                    $data[$k]['username'] = $v[1];
                    $data[$k]['phone'] = $v[2];
//                    $data ['password'] = sha1('111111');
                }
            }
            //插入的操作最好放在循环外面
            $result = db('sys_ceshi')->insertAll($data);
            //var_dump($result);
        }
    }

    public function read($filename,$encode,$file_type){
        if(strtolower ( $file_type )=='xls')//判断excel表类型为2003还是2007
        {

            $PHPExcel_IOFactory= new \PHPExcel_IOFactory();
            // $objReader = PHPExcel_IOFactory::createReader('Excel5');
            $objReader = $PHPExcel_IOFactory->createReader('Excel5');

        }elseif(strtolower ( $file_type )=='xlsx')
        {
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
        }
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $PHPExcel_Cell= new \PHPExcel_Cell();
        $highestColumnIndex = $PHPExcel_Cell->columnIndexFromString($highestColumn);
        // $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                }
        }
        halt($excelData);
        return $excelData;
}


    
}
