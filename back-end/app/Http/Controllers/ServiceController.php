<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $service = Service::create($request->except(['image','capacity','address']));
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

    public function get_type_service()
    {
        $data = ServiceType::all();

        return response()->json($data);
    }

    public function store_detail($id)
    {
        $service = Service::find($id);
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
    public function get_category()
    {
        $data = DB::table('food_category')->get();

        return response()->json($data);
    }
}
