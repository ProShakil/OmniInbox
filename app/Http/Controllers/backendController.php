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

    public function storeVoice(Request $request)
    {
        $request->validate([
            'conversation_id' => ['required', 'exists:chat_db.conversations,id'],
            'audio' => ['required', 'file']
        ]);
        DB::connection('chat_db')->transaction(function () use ($request) {
            $message = Message::create([
                'conversation_id' => $request->conversation_id,
                'sender_type'    => 'admin',
                'platform'       => 'website',
                'message'        => null,
            ]);

            $file = $request->file('audio');

            $fileName = time() . '_' . $file->getClientOriginalName();

            $file->move(
                public_path('chat/voice'),
                $fileName
            );

            MessageAttachment::create([
                'message_id' => $message->id,
                'file_name'  => $file->getClientOriginalName(),
                'file_path'  => 'chat/voice/' . $fileName,
                'file_type'  => $file->getMimeType(),
                'file_size'  => $file->getSize(),
            ]);

        });

        return response()->json([
            'success' => true
        ]);
    }

    public function storeReply(Request $request)
    {
        $request->validate([
            'conversation_id' => ['required', 'exists:chat_db.conversations,id'],
            'message'         => ['nullable', 'string'],
            'attachments.*'   => ['nullable', 'file']
        ]);

        DB::transaction(function () use ($request) {

            $message = Message::create([
                'conversation_id' => $request->conversation_id,
                'sender_type'    => 'admin',
                'platform'       => 'website',
                'message'        => $request->message,
            ]);

            if ($request->hasFile('attachments')) {

                foreach ($request->file('attachments') as $file) {

                    $fileName =
                        time() . '_' . uniqid() . '_' .
                        $file->getClientOriginalName();

                    $file->move(
                        public_path('chat'),
                        $fileName
                    );

                    MessageAttachment::create([
                        'message_id' => $message->id,
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => 'chat/' . $fileName,
                        'file_type'  => $file->getMimeType(),
                        'file_size'  => $file->getSize(),
                    ]);
                }
            }

            \App\Models\Conversation::where(
                'id',
                $request->conversation_id
            )->update([
                'last_message_at' => now()
            ]);
        });

        return response()->json([
            'success' => true
        ]);
    }

}
