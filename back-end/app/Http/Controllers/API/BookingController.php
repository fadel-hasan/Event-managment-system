<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\PaymentMethod;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function booking(Request $request)
    {
        Validator::extend('booking_within_week', function ($attribute, $value, $parameters, $validator) {
            $bookingDate = \Carbon\Carbon::parse($value);
            $weekFromNow = now()->addWeek();
            return $bookingDate->between(now(), $weekFromNow);
        });
        // write validation for data
        $validatedData = Validator::make($request->all(),[
            'service_id' => 'required|exists:services,id',
            'bookingDate' => 'required|date|booking_within_week',
            'amount' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        if($validatedData->fails()){
            return response()->json([
                'message' => $validatedData->errors()
            ], 422);
        }
        $service = Service::where('id', $request->service_id)->where('status','1')->firstOrFail();
        if(!$service){
            return response()->json([
                'message' => 'Service not found'
            ], 404);
        }
        else{
            // fetch available day in service where store in available day as json like ['sunday','tusday']
            $availableDay = $service->available_day;
            $bookingDayOfWeek = Carbon::parse($request->bookingDate)->format('l');

            $bookingDayOfWeekLower = strtolower($bookingDayOfWeek);
            $availableDayLower = array_map('strtolower', $availableDay);

            if (in_array($bookingDayOfWeekLower, $availableDayLower))
            {
                $booking = Booking::create([
                    'user_id' => Auth::user()->id,
                    'service_id' => $request->service_id,
                    'bookingDate' => $request->bookingDate,
                ]);

                $booking->payment()->create([
                    'amount' => $request->amount,
                    'payment_method_id' => $request->payment_method_id,
                    'paymentData' =>Carbon::now()->toDateTimeString(),
                ]);

                return response()->json([
                    'message'=>'booking successfully',
                ]) ;
            }
            else {
                return response()->json([
                    'message'=>'this service is not available in this date'
                ],400);
            }
        }
    }

    public function booking_provider()
    {
        $services = Service::with('bookings.payment')->where('user_id', Auth::user()->id)->get();
        return response()->json([
           'message'=>'booking info',
           'data'=>$services
        ]);
    }

    public function booking_user()
    {
        $booking = Booking::where('user_id', Auth::user()->id)->get();
        return response()->json($booking);
    }
    public function paymentMethod()
    {
        $payment = PaymentMethod::all();
        return response()->json($payment);
    }

    public function accept($id)
    {
        $booking = Booking::where('id', $id)->first();
        if($booking)
        {
            $service = Service::where('id', $booking->service_id)->first();
            if($service)
            {
                $booking->status = 'confirmed';
                $booking->save();
                return response()->json([
                    'message' => 'booking is accepted'
                ]);
            }
            else
            {
                return response()->json([
                    'message' => 'service not found'
                ]);
            }
        }
        else
        {
            return response()->json([
                'message' => 'booking not found'
            ]);
        }
    }

    public function reject($id)
    {
        $booking = Booking::where('id', $id)->first();
        if($booking)
        {
            $service = Service::where('id', $booking->service_id)->first();
            if($service)
            {
                $booking->status = 'pending';
                $booking->save();
                return response()->json([
                    'message' => 'booking is rejected'
                ]);
            }
            else
            {
                return response()->json([
                    'message' => 'service not found'
                ]);
            }
        }
        else
        {
            return response()->json([
                'message' => 'booking not found'
            ]);
        }
    }
}
