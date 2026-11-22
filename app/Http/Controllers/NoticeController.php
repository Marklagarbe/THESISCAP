<?php

namespace App\Http\Controllers;

use App\Models\Notice;

class NoticeController extends Controller
{
    public function index()
    {
        return view('notifications.index', ['notices' => $this->user()->notices()->latest()->paginate(20)]);
    }

    public function open(Notice $notice)
    {
        abort_unless($notice->user_id === $this->user()->id, 403);
        $notice->update(['read_at' => $notice->read_at ?? now()]);

        return redirect($notice->url ?: route('notices.index'));
    }

    public function readAll()
    {
        $this->user()->notices()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
