<?php

namespace App\Http\Controllers;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;

use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class backendController extends Controller
{
    public function conversations()
    {
        $conversations = Conversation::with([
            'contact:id,name,platform',
            'latestMessage'
        ])
        ->select(
            'id',
            'contact_id',
            'last_message_at'
        )
        ->latest('last_message_at')
        ->paginate(10);
        // dd($conversations);
        return response()->json($conversations);
    }

    public function conversationMessages(Conversation $conversation)
    {
        $conversation->load([
            'contact:id,name,platform'
        ]);

        $messages = Message::with('attachments')
            ->where(
                'conversation_id',
                $conversation->id
            )
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }
}
