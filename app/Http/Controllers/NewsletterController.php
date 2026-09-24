<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $data = $request->validate(['email' => 'required|email']);

        NewsletterSubscriber::updateOrCreate(
            ['email' => $data['email']],
            ['is_active' => true, 'subscribed_at' => now()]
        );

        return back()->with('success', 'Thanks for subscribing!');
    }
}
