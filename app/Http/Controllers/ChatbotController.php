<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    public function __construct(protected ChatbotService $chatbotService) {}

    public function respond(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $reply = $this->chatbotService->reply($request->input('message'));

        $sessionId = $request->session()->getId();
        $conversation = ChatbotConversation::firstOrNew([
            'session_id' => $sessionId,
            'user_id' => Auth::id(),
        ]);

        $messages = $conversation->messages ?? [];
        $messages[] = ['from' => 'user', 'text' => $request->input('message'), 'at' => now()->toDateTimeString()];
        $messages[] = ['from' => 'bot', 'text' => $reply, 'at' => now()->toDateTimeString()];
        $conversation->messages = $messages;
        $conversation->save();

        return response()->json(['reply' => $reply]);
    }
}
