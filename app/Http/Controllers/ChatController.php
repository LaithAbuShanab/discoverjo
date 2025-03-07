<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(){
        $chats = Message::all();
        return view('admin.chat.index', compact('chats'));
    }

    public function destroy($id)
    {
        // Find the message by ID
        $message = Message::findOrFail($id);

        // Delete the associated media (if any)
        $message->clearMediaCollection(); // This will remove all media files associated with this model

        // Delete the message record from the database
        $message->delete();

        // Redirect back with a success message
        return redirect()->route('admin.chat.index')->with('success', 'Message and associated media deleted successfully.');
    }

}
