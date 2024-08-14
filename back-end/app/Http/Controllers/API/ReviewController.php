<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function review(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|numeric|between:1,5',
            'service_id' => 'required|exists:services,id',
        ]);

        $service = Service::where('id', $validated['service_id'])
            ->where('status', '1')
            ->firstOrFail();

        $verifiedBooking = $this->hasVerifiedBooking($validated['service_id']);
        if (!$verifiedBooking) {
            throw new HttpResponseException(response()->json([
                'message' => 'You cannot rate this service, you must book it before',
            ], 400));
        }

        $this->saveReview($validated['service_id'], $validated['rating'], $request->input('comment'));

        return response()->json([
            'message' => 'Rating saved successfully',
        ]);
    }

    private function hasVerifiedBooking($serviceId)
    {
        return Booking::where('user_id', Auth::id())
            ->where('service_id', $serviceId)
            ->where('status', 'done')
            ->exists();
    }

    private function saveReview($serviceId, $rating, $comment = null)
    {
        $existingReview = Review::where('user_id', Auth::id())
            ->where('service_id', $serviceId)
            ->first();

        if ($existingReview) {
            $existingReview->rating = $rating;
            $existingReview->update();
        } else {
            Review::create([
                'user_id' => Auth::id(),
                'service_id' => $serviceId,
                'rating' => $rating,
                'comment' => $comment,
            ]);
        }
    }
}
