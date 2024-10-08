<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{


    public function show()
    {
        $user = Auth::user();
        return response()->json([
            'message'=>'معلومات الحساب',
            'data'=>$user
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validatedData = Validator::make($request->all(),[
            'name' => 'required|string|max:100|min:3',
            'email' => ['required', 'email', 'max:255'],
            'number' => ['numeric', 'required'],
            'username'=>['required','min:6','max:20','regex:/^[A-Za-z0-9]{6,}$/i'],
            'address'=>['required']
        ]);

        if($validatedData->fails())
        {
            return response()->json([
                'message'=>$validatedData->errors()
            ],422);
        }
        if($user->email != $request->email)
        {
            $validate=Validator::make($request->all(),['email'=>Rule::unique('users')]);
            if($validate->fails())
            {
                return response()->json([
                'message'=>$validate->errors()
            ],422);
            }
        }
        if($user->username != $request->username)
        {
            $validate=Validator::make($request->all(),['username'=>Rule::unique('users')]);
            if($validate->fails())
            {
                return response()->json([
                    'message'=>$validate->errors()
                ],422);
            }
        }
        // Check if the fields have actually changed
        $fieldsToUpdate = collect($request->all())
            ->filter(function ($value, $key) use ($user) {
                return $user->{$key} != $value;
            })
            ->toArray();

        $user->update($fieldsToUpdate);
        return response()->json([
            'message'=>'updated profile successfully'
        ]);
    }
    public function delete_account(){
        try {
            DB::beginTransaction();

            $user = User::findOrFail(Auth::user()->id);
            foreach ($user->bookings as $booking) {
                $booking->payment()->delete();
            }
            $user->bookings()->delete();
            $user->favoriteServices()->delete();
            foreach ($user->services as $service) {
                $service->reviews()->delete();
                $service->venue()->delete();
                $service->foods()->delete();
                $service->musics()->delete();
            }
            $user->services()->delete();

            $user->delete();

            DB::commit();

            return response()->json([
                'message' => 'User deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function services()
    {
        $services = Service::where('user_id',Auth::user()->id)->whereNotIn('status',['0','2'])->with(['venue','reviews' => function ($query) {
            $query->selectRaw('service_id, AVG(rating) as avg_rating');
            $query->groupBy('service_id');
            }])->get();
        return response()->json([
            'message'=>'success',
            'data'=>$services
        ]);
    }
}
