<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $players = User::members()->active()->where('id', '!=', $request->user()->id)->orderBy('name')->get(['id', 'name']);

        return view('member.chat', compact('players'));
    }

    public function messages(Request $request): JsonResponse
    {
        $request->validate(['with' => ['required', 'string'], 'after' => ['nullable', 'integer', 'min:0']]);

        $me = $request->user()->id;
        $after = (int) $request->query('after', 0);
        $query = Message::with('sender:id,name');

        if ($request->query('with') === 'lobby') {
            $query->whereNull('recipient_id');
        } else {
            $other = User::members()->active()->findOrFail((int) $request->query('with'));

            $query->where(function ($q) use ($me, $other) {
                $q->where(fn ($x) => $x->where('sender_id', $me)->where('recipient_id', $other->id))
                    ->orWhere(fn ($x) => $x->where('sender_id', $other->id)->where('recipient_id', $me));
            });
        }

        $messages = $after === 0
            ? $query->orderByDesc('id')->limit(50)->get()->reverse()->values()
            : $query->where('id', '>', $after)->orderBy('id')->limit(100)->get();

        return response()->json($messages->map(fn (Message $m) => [
            'id' => $m->id,
            'mine' => $m->sender_id === $me,
            'name' => $m->sender->name,
            'body' => $m->body,
            'time' => $m->created_at->format('g:i A'),
        ]));
    }

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'with' => ['required', 'string'],
            'body' => ['required', 'string', 'max:500'],
        ]);

        $recipientId = null;

        if ($data['with'] !== 'lobby') {
            $recipientId = User::members()->active()->where('id', '!=', $request->user()->id)->findOrFail((int) $data['with'])->id;
        }

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $recipientId,
            'body' => trim($data['body']),
        ]);

        return response()->json(['id' => $message->id], 201);
    }
}
