<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewVoteController extends Controller
{
    public function store(Request $request, Review $review)
    {
        $data = $request->validate([
            'vote' => 'required|in:up,down',
        ]);

        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        DB::transaction(function () use ($review, $data, $userId, $sessionId) {
            $existing = $userId
                ? ReviewVote::where('review_id', $review->id)->where('user_id', $userId)->first()
                : ReviewVote::where('review_id', $review->id)->where('session_id', $sessionId)->first();

            if ($existing) {
                if ($existing->vote !== $data['vote']) {
                    $existing->update(['vote' => $data['vote']]);

                    if ($data['vote'] === 'up') {
                        $review->increment('helpful_count');
                        $review->decrement('unhelpful_count');
                    } else {
                        $review->increment('unhelpful_count');
                        $review->decrement('helpful_count');
                    }
                }

                return;
            }

            ReviewVote::create([
                'review_id' => $review->id,
                'user_id' => $userId,
                'session_id' => $userId ? null : $sessionId,
                'vote' => $data['vote'],
            ]);

            if ($data['vote'] === 'up') {
                $review->increment('helpful_count');
            } else {
                $review->increment('unhelpful_count');
            }
        });

        $review->refresh();

        return response()->json([
            'success' => true,
            'helpful_count' => $review->helpful_count,
            'unhelpful_count' => $review->unhelpful_count,
        ]);
    }
}
