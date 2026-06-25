<?php

namespace App\Http\Controllers;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;

use Inertia\Inertia;
use Inertia\Response;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class testController extends Controller
{
    public function test()
    {
        return Inertia::render('Test/TestMessage');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'nullable|string|max:255',
            'phone'      => 'required|string|max:30',
            'email'      => 'nullable|email|max:255',
            'platform'   => 'required|in:whatsapp,messenger,website',
            'message'    => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        DB::connection('chat_db')->transaction(function () use ($request) {

            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            $contact = Contact::where('platform', $request->platform)
                ->where('phone', $request->phone)
                ->first();

            if (!$contact) {

                $contact = Contact::create([
                    'name'        => $request->name,
                    'phone'       => $request->phone,
                    'email'       => $request->email,
                    'platform'    => $request->platform,
                    'platform_id' => null,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Conversation
            |--------------------------------------------------------------------------
            */

            $conversation = Conversation::firstOrCreate(
                [
                    'contact_id' => $contact->id,
                ],
                [
                    'status'          => 'open',
                    'last_message_at' => now(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Message
            |--------------------------------------------------------------------------
            */

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_type'     => 'customer',
                'platform'        => $request->platform,
                'message'         => $request->message,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Attachment
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('attachment')) {

                $file = $request->file('attachment');

                $originalName = $file->getClientOriginalName();
                $fileType = $file->getClientOriginalExtension();
                $fileSize = $file->getSize();
                $uploadPath = public_path('chat');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                $filename = time() . '_' . uniqid() . '.' . $fileType;
                $file->move($uploadPath, $filename);

                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_name'  => $originalName,
                    'file_path'  => 'chat/' . $filename,
                    'file_type'  => $fileType,
                    'file_size'  => $fileSize,
                ]);
            }
        
            /*
            |--------------------------------------------------------------------------
            | Update Conversation
            |--------------------------------------------------------------------------
            */

            $conversation->update([
                'last_message_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            'Message sent successfully.'
        );
    }
    public function conversations()
    {
        return Conversation::with([
            'contact:id,name,platform'
        ])
        ->select(
            'id',
            'contact_id',
            'last_message_at'
        )
        ->latest('last_message_at')
        ->paginate(20);
    }

    public function conversation($id)
    {
        $conversation = Conversation::with([
            'contact',
            'messages'
        ])->findOrFail($id);

        return response()->json([
            'conversation' => $conversation
        ]);
    }


}
