<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-9-22
 * Time: 下午7:30
 */

namespace Application\Model;


class ExcelGenerator {


    /**
     * @param $headers array
     * @param $contents array
     * @param $fileName string
     */
    public function generateExcel($headers, $contents, $fileName){

        $phpExcel = new \PHPExcel();

        $headerMap = array();
        $col = 0;
        $row = 1;
        foreach($headers as $header){
            $phpExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $header);
            $headerMap[$header] = $col;
            $col++;
        }

        $row = 2;
        foreach($contents as $content){
            $col = 0;
            foreach($content as $key => $value){
                if(isset($headerMap[$key]) && $headerMap[$key]){
                    $phpExcel->getActiveSheet()->setCellValueByColumnAndRow( $headerMap[$key], $row, $value);
                }else{
                    $phpExcel->getActiveSheet()->setCellValueByColumnAndRow( $col, $row, $value);
                }
                $col++;
            }
            $row++;
        }




        $outputFileName = $fileName . '.xls';
        $xlsWriter = new \PHPExcel_Writer_Excel5($phpExcel);
//ob_start(); ob_flush();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outputFileName.'"');
        header("Content-Transfer-Encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $xlsWriter->save( "php://output" );

    }

} 