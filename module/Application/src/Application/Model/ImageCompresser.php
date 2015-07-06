<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 14-8-5
 * Time: 下午10:05
 */

namespace Application\Model;


use Zend\Captcha\Image;

class ImageCompresser {

    static function compressImage($sourceFile, $quality, $maxHeight){
        $newFile = tmpfile();
        //$metaData = stream_get_meta_data($newFile);
        $newImg = ImageCompresser::compress_image($sourceFile["tmp_name"], $newFile, $quality, $maxHeight);
        return $newImg;
    }

    static function compressImageImport($sourceLoc, $quality, $maxHeight){
        $newFile = tmpfile();
        //$metaData = stream_get_meta_data($newFile);
        $newImg = ImageCompresser::compress_image($sourceLoc, $newFile, $quality, $maxHeight);
        return $newImg;
    }


    private static function compress_image($source, $destination, $quality, $maxHeight) {
        $info = getimagesize($source);
        //$image = null;
        //if ($info['mime'] == 'image/jpeg') $image =  imagecreatefromjpeg($source['tmp_name']);
        //elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($source['tmp_name']);
        //elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($source['tmp_name']);

        $metaData = stream_get_meta_data($destination);
        //$abc = $this->compressImage($source);
        $srcImg = $source;
        //$maxHeight = 200;
        // Get new sizes
        list($width, $height) = getimagesize($srcImg);
        if($height > $maxHeight){
            $newWidth = $width/$height * $maxHeight;
            $newHeight = $maxHeight;
        }else{
            $newWidth = $width;
            $newHeight = $height;
        }
        // Load
        $thumb = imagecreatetruecolor($newWidth, $newHeight);
        $source = imagecreatefromjpeg($srcImg);
        // Resize
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        //save file
//        imagecopyresized($destination_url, $source, 0,0,0,0,150,150,1920,1080);
        $a = imagejpeg($thumb, $metaData['uri'], $quality);

        //return destination file
        return $destination;
    }

} 