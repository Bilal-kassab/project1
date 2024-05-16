<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class ImageProcess
{
    public static function storeImage($imagefile,$path)
    {
        $image_name=time() . '.' . $imagefile->getClientOriginalExtension();
        $imagefile->move($path,$image_name);
        return "$path/$image_name";
    }

    public static function updateImage($oldimagepath,$imagefile,$path)
    {
        if(File::exists($oldimagepath))
        {
            File::delete($oldimagepath);
        }

        $image_name=time() . '.' . $imagefile->getClientOriginalExtension();
        $imagefile->move($path,$image_name);
        return "$path/$image_name";
    }

    public static function deleteImage($imagefile,$path)
    {

    }

}
