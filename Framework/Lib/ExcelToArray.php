<?php

namespace Core\Lib;

use Core\Lib\PHPExcel;

class ExcelToArray {

    public function __construct() {
        /* 导入phpExcel核心类    注意 ：你的路径跟我不一样就不能直接复制 */
        require_once(FRAMEWORK_ROOT . 'Lib/PHPExcel/PHPExcel.php');
    }

    /**
     * 读取excel $filename 路径文件名 $encode 返回数据的编码 默认为utf8 
     * 以下基本都不要修改 
     */
    public function read($filename, $encode = 'utf-8') {
        require_once(FRAMEWORK_ROOT . 'Lib/PHPExcel/PHPExcel/IOFactory.php');
        require_once(FRAMEWORK_ROOT . 'Lib/PHPExcel/PHPExcel/Cell.php');
        $file_obj  = new \SplFileInfo($filename);
        $ext       = $file_obj->getExtension();

        if($ext == "xls"){
            $excel_version = "Excel5";
        }elseif($ext == "xlsx"){
            $excel_version = "Excel2007";
        }else{
            return false;
        }

        $objReader = \Core\Lib\PHPExcel\PHPExcel_IOFactory::createReader($excel_version);
        
        $objReader->setReadDataOnly(true);
        $objPHPExcel = $objReader->load($filename);
        $objWorksheet = $objPHPExcel->getActiveSheet();

        if(!$objWorksheet){
            return false;
        }
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] = (string) $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;
    }

}

?>