<?php
/**
 * Created by PhpStorm.
 * User: haris
 * Date: 5.6.17
 * Time: 15:39
 */

namespace Ibuntu\Services;


use Ibuntu\Libraries\ImageManipulationLibrary;

class ImageStorageService
{

    private $domain;
    private $protocol;
    private $basePath;
    /** @var  ImageManipulationLibrary $imageManipulationLibrary */
    private $imageManipulationLibrary;

    function __construct($basePath, $protocol, $domain, $imageManipulationLibrary)
    {
        $this->domain = $domain;
        $this->protocol = $protocol;
        $this->basePath = $basePath;
        $this->imageManipulationLibrary = $imageManipulationLibrary;
    }

    public function storeImage($image, $folderName, $width, $height){
        // Compose image name
        // until I come up with algorithm that generates unique names
        $imageName = str_replace("_", " ", $image->getClientOriginalName());

        // Resize
        $image = $this->imageManipulationLibrary->resizeImage(file_get_contents($image->getRealPath()), $width, $height);

        // Store to disk
        $path = $this->storeImageContents('/uploads'.'/'.$folderName.'/'.$imageName, $image);

        return $path;

    }

    private function storeImageContents($path, $content){
        file_put_contents($this->basePath.$path, $content);
        chmod($this->basePath.$path, 0777);
        return $path;
    }

    public function moveAndRenameImage($folderName, $url, $id){
        $digits = 5;
        $random = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
        $newUrl = "/uploads"."/".$folderName.'/'.$id."_".$random.".jpg";
        rename($this->basePath.$url, $this->basePath.$newUrl );
        return $newUrl;
    }
}