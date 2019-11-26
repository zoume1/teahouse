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
            unset($excel_array[0]);
            halt($excel_array);//到这剩下的就交给你自己了

            //插入的操作最好放在循环外面
            // $result = db('sys_ceshi')->insertAll($data);
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
