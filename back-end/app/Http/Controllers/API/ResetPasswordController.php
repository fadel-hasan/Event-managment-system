<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ResetCodePassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|exists:reset_code_passwords',
            'password' => 'required|string|min:6|confirmed',
        ],[
            'code.required' => 'رمز إعادة تعيين كلمة المرور مطلوب',
            'code.string' => 'رمز إعادة تعيين كلمة المرور يجب أن يكون نص',
            'code.exists' => 'رمز إعادة تعيين كلمة المرور غير صالح',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.string' => 'كلمة المرور يجب أن تكون نص',
            'password.min' => 'كلمة المرور يجب أن تكون على الأقل 6 أحرف',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->toArray()
            ], 422,[], JSON_UNESCAPED_UNICODE);
        }
        // find the code
        $passwordReset = ResetCodePassword::firstWhere('code', $request->code);

        // check if it does not expired: the time is one hour
        if ($passwordReset->created_at > now()->addHour()) {
            $passwordReset->delete();
            return response(['message' => trans('passwords.code_is_expire')], 422);
        }

        // find user's email
        $user = User::firstWhere('email', $passwordReset->email);

        // update user password
        $user->update($request->only('password'));

        // delete current code
        $passwordReset->delete();

        return response(['message' =>'password has been successfully reset'], 200);
    }

    public function change_password(Request $request)
    {
        $validate=validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|confirmed|min:8|string'
        ]);
        $auth = Auth::user();

        if (!Hash::check($request->get('current_password'), $auth->password))
        {
            return response()->json([
                'error'=>"Current Password is Invalid"
            ],400);
        }

        if (strcmp($request->get('current_password'), $request->new_password) == 0)
        {
            return response()->json([
                'error'=>"New Password cannot be same as your current password."
            ],400);
        }

        $user =  User::find($auth->id);
        $user->password =  Hash::make($request->new_password);
        $user->save();
        return response()->json([
           'message'=>"Password Changed Successfully"
        ]);
    }
}
