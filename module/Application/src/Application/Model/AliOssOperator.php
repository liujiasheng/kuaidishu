<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-16
 * Time: 上午10:59
 */

namespace Application\Model;

/**
 * 加载sdk包以及错误代码包
 */
//require_once '../../../../../API/oss_php_sdk/sdk.class.php';

class AliOssOperator {

    static public function uploadFile($file, $name, $dir){
        $flag = true;

            $oss_sdk_service = new \ALIOSS();
            $bucket = AliOssConfig::getBucket();
            $content = '';
            $length = 0;
            $fp = fopen( $file ,'r');
            if($fp)
            {
                $f = fstat($fp);
                $length = $f['size'];
                while(!feof($fp))
                {
                    $content .= fgets($fp,8192);
                }
            }
            $upload_file_options = array('content' => $content, 'length' => $length);
            $upload_file_by_content = $oss_sdk_service->upload_file_by_content($bucket , $dir.$name, $upload_file_options);

            if($upload_file_by_content->status != 200 ){
                //throw new \Exception($upload_file_by_content->body, $upload_file_by_content->status);
                $flag = false;
                $logger = new Logger();
                $message = "AliOssOperator Error: file: ".$file." ".$name." dir:".$dir." message:".$upload_file_by_content->body;
                $logger->err($upload_file_by_content->status,$message);
            }


        return $flag;
    }

} 