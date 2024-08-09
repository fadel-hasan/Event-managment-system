<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function delete_account(){
        $user_id = auth()->user()->id;

        $user = User::findOrFail($user_id);

        $user->status = 'disable';
        $user->save();
        auth()->user()->tokens()->delete();
        return response(['message'=>"تم حذف الحساب"]);
    }
}
