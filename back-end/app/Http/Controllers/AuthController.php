<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request as r;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        try {
            //Validated

            $ValidateRules = [
                'name' => 'required|min:2|max:100',
                'username' => 'required|min:6|max:20|unique:users,username|regex:/^[A-Za-z0-9]{6,}$/i',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|max:255',
            ];
            $ValidatorErrors = [
                'name.required' => 'الاسم مطلوب',
                'name.min' => 'الاسم يجب ان لا يقل عن حرفين',
                'name.max' => 'الاسم يجب ان لايزيد عن 100 حرف',
                'username.required' => 'اسم المستخدم مطلوب',
                'username.min' => 'اسم المستخدم يجب ان لا يقل عن 6 احرف',
                'username.max' => 'اسم المستخدم يجب ان لا يزيد عن 20 حرف',
                'username.regex' => 'عذراً يجب اسم المستخدم ان يحتوي على الأقل على 6 حروف ولا يحوي رموز غريبة',
                'username.unique' => 'اسم المستخدم مستخدم من قبل',
                'email.required' => 'رجاء أدخل الايميل',
                'email.email' => 'ايميل غير صحيح',
                'email.unique' => 'هذا الايميل مستخدم',
                'password.required' => 'كلمة السر مطلوبة',
                'password.min' => 'كلمة السر قصيرة جدا',
                'password.max' => 'كلمة السر طويلة جدا',
            ];
            $validatedData = Validator::make($request->all(), $ValidateRules, $ValidatorErrors);

            if ($validatedData->fails()) {
                // dd($validatedData->failed());
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validatedData->errors()
                ], 401);
            }

            $userExists = User::where('email', $request->email)->exists();
            if ($userExists) {
                return response()->json([
                    'status' => false,
                    'message' => 'البريد موجود مسبقا',
                    'errors' => 'البريد موجود مسبقا'
                ], 401);}
            $HashedPassword = Hash::make($request->password);
            $date['name'] = $request->name;
            $date['email'] = $request->email;
            $date['password'] = $HashedPassword;
            $date['username'] = $request->username;
            $user = User::create($date);

            $user->assignRole('user');

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN",["*"], now()->addDay())->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $loginField = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $loginValue = $request->input('login');
        try {
            $validateUser = Validator::make($request->all(),
                [
                    'login' => 'required|'.($loginField=="email"?'exists:users,email':'exists:users,username'),
                    'password' => 'required'
                ],[
                    'login.required' => 'هذا الحقل مطلوب رجاءا',
                    'login.exists' =>   'معلومات تسجيل دخول خاطئة حاول مرة اخر',
                    'password.required' => 'كلمة السر مطلوبة'
                ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::where($loginField, $loginValue)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'معلومات تسجيل دخول خاطئة حاول مرة اخر',
                ], 404);
            }
//            if ($user->is_blocked) {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'الحساب محظور',
//                    'errors' => 'الحساب محظور'
//                ], 401);
//            }



            if (auth()->attempt([$loginField => $loginValue, 'password' => $request->password])) {
                return response()->json([
                    'status' => true,
                    'message' => 'User Logged In Successfully',
                    'token' => $user->createToken("API TOKEN", ["*"], now()->addMonth())->plainTextToken
                ], 200);

            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'معلومات تسجيل دخول خاطئة حاول مرة اخر',
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }

    public function forgetPassword(Request $request)
    {
        $loginField = filter_var($request->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $loginValue = $request->input('login');

        $validateUser = Validator::make($request->all(),
            [
                'login' => 'required|'.($loginField=="email"?'exists:users,email':'exists:users,username'),
            ],[
                'login.required' => 'هذا الحقل مطلوب رجاءا',
                'login.exists' => 'المستخدم غير موجود',
            ]);

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        $token = Str::random(64);
        if($loginField == 'username')
        {
            $email = DB::table('users')->where('username','=',$loginValue)->first()->email;
        }
        else
        {
            $email = $loginValue;
        }
        $updatePassword = DB::table('password_reset_tokens')->where([
            'email' => $email,
        ])->first();
        if($updatePassword)
        {
            if (now()->diffInMinutes($updatePassword->created_at) >= 30) {
                DB::table("password_reset_tokens")->where(["email" => $email])->delete();
            }
        }
        try {
            $d= DB::table('password_reset_tokens')->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
        } catch (\Exception $exception) {
            return response()->json(['message' => 'many request'], 400);
        }
        Mail::to($email)->send(new PasswordResetMail($token));
        return response()->json(['message' => 'We have emailed your password reset link!'], 200);
    }

    public function resetPassword(Request $request)
    {
        $validateUser = Validator::make($request->all(),
            [
                'token' => 'required',
                'password' => 'required|confirmed',
            ],[
                'token.required' => 'هناك غلط ما اعد محاولة تعيين كلمة المرور',
                'password.required' => 'هذا الحقل مطلوب رجاءا',
                'password.confirmed' => 'كلمة المرور غير متطابقة',
            ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        $updatePassword = DB::table('password_reset_tokens')->where([
            'token' => $request->token
        ])->first();

        if (!$updatePassword) {
            return response()->json(['message' => 'This password reset token is invalid.'], 404);
        }

        if (Carbon::parse($updatePassword->created_at)->addMinutes(30)->isPast()) {
            DB::table('password_reset_tokens')->where(['token' => $request->token])->delete();
            return response()->json(['message' => 'This password reset token has expired.'], 422);
        }

        User::where('email', $updatePassword->email)->update(['password' => Hash::make($request->password)]);
        DB::table('password_reset_tokens')->where(['email' => $updatePassword->email])->delete();

        return response()->json(['message' => 'Your password has been reset!'], 200);
    }
}
