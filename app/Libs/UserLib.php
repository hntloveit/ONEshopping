<?php
namespace App\Libs;
use App\User;
class UserLib {
    public static function getUserIdCard($userId){
        if($userId == 0) return '';
        $user = User::find($userId);
        return $user->id_card;
    }

    public static function getName($user_id) {
        if($user_id == 0) return '';

        $user = User::find($user_id);
        return $user->name;
    }

    public static function getById($user_id){
        if($user_id == 0) return '';
        $user = User::find($user_id);
        return $user;
    }
}