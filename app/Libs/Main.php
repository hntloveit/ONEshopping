<?php
namespace App\Libs;
class Main {
    public static function money_format($money){
        $money = number_format($money,"0",",",".");
        return  $money . ' <small class="font-green-sharp">VND</small>';
    }
	public static function phone_format($phone){				$dau = substr($phone, 0, 4);		$duoi = substr($phone, -3, 3);        $phone = $dau.'***'.$duoi;        return  $phone;    }
    public static function is_image($path)
    {
        $a = getimagesize($path);
        $image_type = $a[2];

        if(in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)))
        {
            return true;
        }
        return false;
    }
}