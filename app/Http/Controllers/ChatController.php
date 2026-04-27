<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(): Response
    {
        $messages = Message::with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return Inertia::render('Chat', [
            'messages' => $messages,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $message = $request->user()->messages()->create([
            'body' => $request->body,
        ]);

        broadcast(new MessageSent($message));

        return back();
    }
}
