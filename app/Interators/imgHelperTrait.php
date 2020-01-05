<?php
namespace App\Interactors;

use App\Image;

trait imgHelperTrait
{
	public function getImgFromStorage(Request $request)
	{

    }
    
    /**
     * @param $image 
     * @return Intervention\Image\Image
     */
    protected function isPermissibleSize($file,int $width = 1000,int $height = 1000)
    {
        $image = \Image::make($file);
        if (($image->width() <= $width) && ($image->height() <= $height)) {
            return $file;
        } else {
            $image->resize($width, $height);
            $img = (string) $image->encode('jpg', 90);
            return $img;
        }
    }
    
    /**
     * @param $file 
     * @param $width
     * @param $height
     * @return boolean
     */
    protected function createThumbnail($file, string $folder ,string $name, int $width = 200, int $height = 200)
    {
        $path = '/confirmations/'.'thumb_'.$name.'.jpg';
        if ($image = \Image::make($file)) {
            $image->resize($width,$height);
            $img = (string) $image->encode('jpg',90);
            \Storage::put('public/' . $path,$img);
        }
    }

        /**
     * @param $id
     * @param $image_content
     * @return Image
     */
    protected function saveImageTransaction(string $folder, string $name, $imageContent, int $id = 0, array $args = [])
    {
        $name = $id . str_random(40);
        $fileName = $folder . $name . '.jpg';
        $thumbName = $folder.'thumb_'.$name.'.jpg';
        $image_content=$this->isPermissibleSize($imageContent);
        $this->createThumbnail($imageContent, $folder, $name);
        \Storage::put(
            'public/' . $fileName,
            $image_content
        );
        $image = new Image();
        $image->name = 'storage/' . $fileName;
        $image->thumbnail = 'storage/' . $thumbName;
        $image->transaction_id = $id;
        $image->save();
        return $image;
    }
}