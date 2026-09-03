<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.contact_messages.index', compact('messages'));
    }

    public function show(ContactMessage $contactMessage)
    {
        if (!$contactMessage->is_read) {
            $contactMessage->update(['is_read' => true]);
        }
        return view('admin.contact_messages.show', compact('contactMessage'));
    }

    public function reply(Request $request, ContactMessage $contactMessage)
    {
        $request->validate([
            'reply_message' => 'required|string'
        ]);

        if ($contactMessage->is_replied) {
            return redirect()->back()->with('error', 'Pesan ini sudah dibalas sebelumnya.');
        }

        try {
            \Illuminate\Support\Facades\Mail::to($contactMessage->email)->send(new \App\Mail\ContactReplyMail($contactMessage, $request->reply_message));
            
            $contactMessage->update([
                'is_replied' => true,
                'replied_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Balasan berhasil dikirim ke ' . $contactMessage->email);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }
}
