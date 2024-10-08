<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = validator::make($request->all(),[
            'photograph' => 'boolean',
            'food' => 'boolean',
            'music' => 'boolean',
            'price' => 'required|numeric|min:0',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
            'available_day' => 'required|array',
            'available_day.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'type_id' => 'required|exists:service_type,id',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'capacity' => 'required|numeric|min:1',
            'address' => 'required|string',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message'=>$validatedData->errors()
            ],422);
        }
        $data= $request->all();
        $data['user_id'] = Auth::user()->id;
        $service = Service::create($data);
        $image = $request->file('image');
        $imageName = time().'.'.$image->extension();
        $image->storeAs('public/images', $imageName);
        $data['capacity'] = $request->capacity;
        $data['address'] = $request->address;
        $data['image']='/storage/images/' . $imageName;
        $service->venue()->create([
            'image' => $data['image'],
            'capacity' => $data['capacity'],
            'address' => $data['address'],
        ]);


        return response()->json($service, 201);
    }


    public function store_detail($id)
    {
        $service = Service::where('user_id',Auth::user()->id)->find($id);
        if(!$service)
        {
            return response()->json([
                'message'=>'there is wrong try again'
            ],400);
        }
        $validate = $this->validate_data($service->food, $service->music);
        if ($validate[0]->fails()) {
            return response()->json([
                'message' => $validate[0]->errors()
            ], 422);
        }

        switch ($validate[1]) {
            case 1:
                foreach (\request('food') as $food) {
                    $image = $food['image'];
                    $imageName = time() . '.' . $image->extension();
                    $image->storeAs('public/images', $imageName);
                    DB::table('food')->insert([
                        'image' => '/storage/images/' . $imageName,
                        'desc' => $food['desc'],
                        'category_id' => $food['category_id'],
                        'service_id' => $id
                    ]);
                }
                DB::table('music')->insert([
                    'desc' => \request('music')
                ]);
                break;
            case 2:
                foreach (\request('food') as $key => $food) {
                    $image = $food['image'];
                    $imageName = time() . '.' . $image->extension();
                    $image->storeAs('public/images', $imageName);
                    DB::table('food')->insert([
                        'image' => '/storage/images/' . $imageName,
                        'desc' => $food['desc'],
                        'category_id' => $food['category_id'],
                        'service_id' => $id
                    ]);
                }
                break;
            case 3:
                DB::table('music')->insert([
                    'desc' => \request('music')
                ]);
                break;
        }
        return response()->json([
           'message'=>"success"
        ]);
    }

    public function validate_data($food,$music){
        if($food && $music)
        {
            $validate[0] =validator::make(\request()->all(),[
                'food' => 'required|array',
                'food.*.category_id' => 'required|integer',
                'food.*.image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'food.*.desc' => 'required|string',
                'music' => 'required|string',
            ]);
            $validate[1] = 1;
            return $validate;
        }
        else if($food && !$music)
        {
            $validate[0] =validator::make(\request()->all(),[
                'food' => 'required|array',
                'food.*.category_id' => 'required|integer',
                'food.*.image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'food.*.desc' => 'required|string',
            ]);
            $validate[1] = 2;
            return $validate;
        }
        else if(!$food && $music)
        {
            $validate[0] =validator::make(\request()->all(),[
                'music' => 'required|string'
            ]);
            $validate[1] = 3;
            return $validate;
        }
        return;
    }


    public function get_type_service()
    {
        $data = ServiceType::all();

        return response()->json($data);
    }

    public function get_category()
    {
        $data = DB::table('food_category')->get();

        return response()->json($data);
    }


    public function favorite($id)
    {
        $service = Service::find($id);
        if(!$service)
        {
           return response()->json([
               'error'=>'there is error'
           ],400);
        }
        if($service->favorited())
        {
            return response()->json([
                'error'=>'this product in your favorite list you can\'t added it again'
            ],400);
        }
        auth()->user()->favoriteServices()->attach($service->id);
        return response()->json([
            'message'=>'product is added to your favorite list'
        ]);
    }

    public function unFavorite($id)
    {
        $service = Service::find($id);
        if(!$service)
        {
            return response()->json([
                'error'=>'there is error'
            ],400);
        }
        Auth::user()->favoriteServices()->detach($service->id);

        return response()->json([
            'message'=>'product is removed from your favorite list'
        ]);
    }

    public function myFavorites()
    {
        $service = Auth::user()->favoriteServices()->with('venue')->get();

        return response()->json([
            'message'=>'favorite service',
            'data'=>$service
        ]);
    }


    public function service_by_type($id)
    {
        $services = Service::whereHas('type', function ($query) use ($id) {
            $query->where('id', $id);
        })->whereNotIn('status',['2','0'])->with(['venue','reviews' => function ($query) {
        $query->selectRaw('service_id, AVG(rating) as avg_rating');
        $query->groupBy('service_id');
        }])->get();

        return response()->json([
            'services' => $services
        ]);
    }

    public function service_detail($id)
    {
//        $service=Service::with(['venue','foods'=>['foodCategory'],'music'])->find($id);
        $service = Service::with(['venue','musics', 'foods.foodCategory','reviews' => function ($query) {
        $query->selectRaw('service_id , AVG(rating) as avg_rating');
        $query->groupBy('service_id');
    }])->whereNotIn('status',['2','0'])->find($id);

        return response()->json([
            'service'=>$service
        ]);

    }


    public function delete_service($id)
    {
        $service=Service::where('user_id',Auth::user()->id)->find($id);
        $service->status = '0';
        $service->save();
        return response()->json([
           'message' =>'service is deleted'
        ]);
    }

    public function update(Request $request)
    {
        $service = Service::where('user_id',Auth::user()->id)->find($request->id);
        $validatedData = validator::make($request->all(),[
            'photograph' => 'boolean',
            'food' => 'boolean',
            'music' => 'boolean',
            'price' => 'required|numeric|min:0',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
            'available_day' => 'required|array',
            'available_day.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'type_id' => 'required|exists:service_type,id',
            'image' => 'image|mimes:jpeg,png,jpg|max:2048',
            'capacity' => 'required|numeric|min:1',
            'address' => 'required|string',
        ]);

        if ($validatedData->fails()){
            return response()->json([
                'message'=>$validatedData->errors()
            ],422);
        }
        else{
            if($request->food == $service->food && $request->music == $service->music)
            {
                $this->update_data($request,$service);
            }
            else if($request->food != $service->food && $request->food == 0)
            {
                $service->foods()->delete();
                $this->update_data($request,$service);
            }
            else if($request->music != $service->music && $request->music==0)
            {
                $service->musics()->delete();
                $this->update_data($request,$service);
            }
            return response()->json([
             'message'=>'updated successfully'
            ]);
        }
    }
    public function validate_update_data($food,$music){
        if($food && $music)
        {
            $validate[0] =validator::make(\request()->all(),[
                'food' => 'required|array',
                'food.*.category_id' => 'required|integer',
                'food.*.image' => 'image|mimes:jpeg,png,jpg|max:2048',
                'food.*.desc' => 'required|string',
                'music' => 'required|string',
            ]);
            $validate[1] = 1;
            return $validate;
        }
        else if($food && !$music)
        {
            $validate[0] =validator::make(\request()->all(),[
                'food' => 'required|array',
                'food.*.category_id' => 'required|integer',
                'food.*.image' => 'image|mimes:jpeg,png,jpg|max:2048',
                'food.*.desc' => 'required|string',
            ]);
            $validate[1] = 2;
            return $validate;
        }
        else if(!$food && $music)
        {
            $validate[0] =validator::make(\request()->all(),[
                'music' => 'required|string'
            ]);
            $validate[1] = 3;
            return $validate;
        }
        else{
            $validate[0] ='';
            $validate[1]=4;
            return $validate;
        }
        return;
    }

    public function update_data(Request $request,$service)
    {
        if($request->hasFile('image')) {
            $destination = $request->image;
            if (File::exists($destination)) {
                File::delete($destination);
            }
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('storage/images/', $filename);
        }
        $service->update([
            'photograph' => $request->photograph,
            'food' => $request->food,
            'music' => $request->music,
            'price' => $request->price,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'available_day' => $request->available_day,
            'type_id' => $request->type_id,
        ]);
        $service->venue()->update([
            'image' => $filename??$service->venue->image,
            'capacity' => $request->capacity,
            'address' => $request->address,
        ]);

    }

    public function search(Request $request)
    {
        $address = $request->input('address');
        $services = Service::whereHas('venue', function ($venueQuery) use ($address) {
            $venueQuery->where('address', 'like', '%' . $address . '%');
        })->get();;

        return response()->json($services);
    }

}
