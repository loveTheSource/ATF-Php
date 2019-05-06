<?php

namespace ATFApp\Helper;

use ATFApp\Exceptions;
use ATFApp\BasicFunctions;
use ATFApp\Exceptions\ExceptionHandler;

/**
 * image handler class
 * used to 
 * - get image infos
 * - do resize operations (requires imagemagick)
 */
class Image {

    private $jpgQuality = 85;
    private $pngQuality = 5;

    public function getImageWidth($imgFile) {
        return $this->getImageDimensions($imgFile)['width'];
    }
    
    public function getImageHeight($imgFile) {
        return $this->getImageDimensions($imgFile)['height'];
    }
    
    public function getImageDimensions($imgFile) {
        $imgInfo = getimagesize($imgFile);
        if (!is_array($imgInfo)) {
            throw new Exceptions\Custom('unable to get image size information (getimagesize) for file: ' . $imgFile);
        }
        return [
            'width' => $imgInfo[0],
            'height' => $imgInfo[1]
        ];
    }

    /**
     * check if file is an image
     * 
     * tries to get data from getimagesize()
     * tries to dtermine type with exif_imagetype()
     * tries to create image from source using GD imagecreatefrom* 
     * 
     * @param string $imgFile
     * @param string $extension
     * @return boolean
     */
    public function isImage($imgFile, $extension) {
        try {
            if (getimagesize($imgFile)) {
                if (exif_imagetype($imgFile) !== false) {
                    $img = false;
    
                    switch ($extension) {
                        case "jpg":
                        $img = imagecreatefromjpeg($imgFile);
                        break;
            
                        case "gif":
                        $img = imagecreatefromgif($imgFile);
                        break;
            
                        case "png":
                        $img = imagecreatefrompng($imgFile);
                        break;
            
                        case "bmp":
                        $img = imagecreatefrombmp($imgFile);
                        break;
                    }
                    
                    if ($img !== false) {
                        unset($img);
                        return true;
                    }    
                }
            }
    
            return false;
        } catch (\Throwable $e) {
            if (!BasicFunctions::isProduction()) {
                ExceptionHandler::handle($e);
            }
            return false;
        }
    }

    public function recreateImage(\ATFApp\Helper\File $file) {
        try {
            $sourceImgPath = QUARANTINE_PATH . $file->getFilename();
            $sourceDimensions = $this->getImageDimensions($sourceImgPath);
            $newImg = imagecreatetruecolor($sourceDimensions['width'], $sourceDimensions['height']);

            switch ($file->getExtension()) {
                case "jpg":
                $sourceImg = imagecreatefromjpeg($sourceImgPath);
                break;
    
                case "gif":
                $sourceImg = imagecreatefromgif($sourceImgPath);
                break;
    
                case "png":
                $sourceImg = imagecreatefrompng($sourceImgPath);
                imagealphablending($newImg, FALSE);
                imagesavealpha($newImg, TRUE);        
                break;
    
                case "bmp":
                $sourceImg = imagecreatefrombmp($sourceImgPath);
                break;
            }

            // recreate
            imagecopyresized($newImg, $sourceImg, 0, 0, 0, 0, $sourceDimensions['width'], $sourceDimensions['height'], $sourceDimensions['width'], $sourceDimensions['height']);
            // delete source img obj
            unset($sourceImg);
            unlink($sourceImgPath);

            // save file
            switch ($file->getExtension()) {
                case "jpg":
                $result = imagejpeg($newImg, $sourceImgPath, $this->jpgQuality);
                break;
    
                case "gif":
                $background = imagecolorallocate($newImg, 0, 0, 0); 
                imagecolortransparent($newImg, $background);
                $result = imagegif($newImg, $sourceImgPath);
                break;
    
                case "png":
                $result = imagepng($newImg, $sourceImgPath, $this->pngQuality);
                break;
    
                case "bmp":
                $result = imagebmp($newImg, $sourceImgPath);
                break;
            }
            
            return $result;
        } catch (\Throwable $e) {
            var_dump($e);die();
            ExceptionHandler::handle($e);
            return false;
        }
    }


    public function resizeImage() {
        /*
		$img = 'image.jpg';
		header('Content-type: image/jpeg');
        $image = new \Imagick($img);

        // If 0 is provided as a width or height parameter,
        // aspect ratio is maintained
        $image->thumbnailImage(100, 0);

        echo $image;
        die();
        */
    }
}