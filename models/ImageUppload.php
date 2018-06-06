<?php
/**
 * Created by PhpStorm.
 * User: Rust
 * Date: 05.06.2018
 * Time: 11:55
 */

namespace app\models;


use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class ImageUppload extends Model
{
    public $image;

    public function rules()
    {
        return [
          [['image'], 'required'],
          [['image'], 'file', 'extensions' => 'jpg, png']
        ];
    }

    public function uploadFile(UploadedFile $file, $currentImage)
    {
        $this->image = $file;

        if ($this->validate()){
            $this->deleteCurrentImage($currentImage);
            return $this->saveImage();
        }
    }

    public function deleteCurrentImage($currentImage)
    {
        //die("Удаление старого файла");
        if ($this->fileExist($currentImage))
        {
            unlink($this->getFolder() . $currentImage);
        }
    }


    public function saveImage()
    {
        $filename = $this->generateFilename();
        $this->image->saveAs($this->getFolder().$filename);
        return $filename;
    }


    private function generateFilename()
    {
        return strtolower(md5(uniqid($this->image->baseName)) . '.' . $this ->image-> extension);
    }


    private function getFolder()
    {
        return Yii::getAlias('@web') . 'uploads/';
    }

    /**
     * @param $currentImage
     * @return string
     */
    private function fileExist($currentImage)
    {
        //die($currentImage);
        if (!empty($currentImage) && $currentImage != null){
            return file_exists($this->getFolder()) . $currentImage;
        }
    }
}