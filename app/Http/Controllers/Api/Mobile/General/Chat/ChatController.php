<?php

namespace App\Http\Controllers\Api\Mobile\General\Chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Events\Chat\MessageEvent;
use App\Models\User;
use App\Models\Room;
use App\Models\RoomMessage as Message;
use Pusher\Pusher;
use \Auth;

class ChatController extends Controller
{
    public function getRoom(Request $request) {
        $rooms = Message::
            join('rooms', 'rooms.id', '=', 'room_messages.room_id')
            ->join(DB::raw('users as sender'), 'sender.id', '=', 'rooms.sender_id')
            ->join(DB::raw('users as receiver'), 'receiver.id', '=', 'rooms.receiver_id')
            ->join(DB::raw('(SELECT room_id, MAX(created_at) AS latest_created_at FROM room_messages GROUP BY room_id) AS latest'), function ($join) {
                $join->on('room_messages.room_id', '=', 'latest.room_id')
                    ->on('room_messages.created_at', '=', 'latest.latest_created_at');
            })
            ->where(function($query) {
                $query->where('rooms.sender_id', Auth::id())
                    ->orWhere('rooms.receiver_id', Auth::id());
            })
            ->select(
                'room_messages.room_id',
                'rooms.receiver_id',
                DB::raw('receiver.name as receiver_name'),
                DB::raw('receiver.picture as receiver_picture_url'),
                'rooms.sender_id',
                DB::raw('sender.name as sender_name'),
                DB::raw('sender.picture as sender_picture_url'),
                DB::raw('room_messages.message AS message'),
                DB::raw('room_messages.created_at AS timestamp'),
            )
            ->orderBy('timestamp', 'DESC')
            ->get();
        $response = [
            'rooms' => $rooms
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }
    
    public function getChat(Request $request) {
        $validator = Validator::make($request->all(), [
	        'opponent_id' => 'required|numeric',
	        'last_id' => 'nullable|numeric'
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $opponent = $valid['opponent_id'];
        $room = Room::
            join(DB::raw('users as sender'), 'sender.id', '=', 'rooms.sender_id')
            ->join(DB::raw('users as receiver'), 'receiver.id', '=', 'rooms.receiver_id')
            ->join('room_messages', 'room_messages.room_id', '=', 'rooms.id')
            ->where(function($query) {
                $query->where('rooms.sender_id', Auth::id())
                    ->orWhere('rooms.receiver_id', Auth::id());
            })
            ->where(function($query) use ($opponent) {
                $query->where('rooms.sender_id', $opponent)
                    ->orWhere('rooms.receiver_id', $opponent);
            })
            ->select(
                'rooms.id',
                'rooms.receiver_id',
                DB::raw('receiver.name as receiver_name'),
                DB::raw('receiver.picture as receiver_picture_url'),
                'rooms.sender_id',
                DB::raw('sender.name as sender_name'),
                DB::raw('sender.picture as sender_picture_url'),
            )
            ->first();
        if($room) {
            $message = Message::
                join('users', 'users.id', '=', 'room_messages.user_id')
                ->where('room_id', $room->id);
            if(isset($valid['last_id'])) {
                $message = $message->whereRaw('room_messages.id < '.$valid['last_id']);
            }
            $message = $message->select(
                    'room_messages.id',
                    'room_messages.room_id',
                    'room_messages.user_id',
                    DB::raw('users.name as user_name'),
                    'room_messages.type',
                    'room_messages.message',
                    DB::raw('room_messages.created_at as timestamp'),
                )
                ->orderBy('room_messages.created_at', 'desc')
                ->limit(15)
                ->get();
            if(count($message) > 1) {
                $newLastId = $message[count($message) - 1]['id'];
            } else if(count($message) == 1) {
                $newLastId = $message[0]['id'];
            } else {
                $newLastId = '';
            }
            $more = false;
            if($newLastId !== '') {
                $countPrev = Message::
                    join('users', 'users.id', '=', 'room_messages.user_id')
                    ->where('room_id', $room->id);
                $countPrev = $countPrev->select(
                    'room_messages.id',
                    'room_messages.room_id',
                    'room_messages.user_id',
                    DB::raw('users.name as user_name'),
                    'room_messages.type',
                    'room_messages.message',
                    DB::raw('room_messages.created_at as timestamp'),
                )
                ->whereRaw('room_messages.id < '.$newLastId)
                ->orderBy('room_messages.created_at', 'desc')
                ->count();
                $more = $countPrev >= 15;
            }
            $chat = [
                'messages' => $message,
                'last_id' => $newLastId,
                'more' => $more,
            ];
        } else {
            $newRoom = new Room;
            $newRoom->sender_id = Auth::id();
            $newRoom->receiver_id = $opponent;
            $newRoom->save();
            
            $room = Room::
                join(DB::raw('users as sender'), 'sender.id', '=', 'rooms.sender_id')
                ->join(DB::raw('users as receiver'), 'receiver.id', '=', 'rooms.receiver_id')
                ->join('room_messages', 'room_messages.room_id', '=', 'rooms.id')
                ->where('id', $newRoom->id)
                ->select(
                    'rooms.id',
                    'rooms.receiver_id',
                    DB::raw('receiver.name as receiver_name'),
                    DB::raw('receiver.picture as receiver_picture_url'),
                    'rooms.sender_id',
                    DB::raw('sender.name as sender_name'),
                    DB::raw('sender.picture as sender_picture_url'),
                )
                ->first();
            $chat = [
                'messages' => [],
                'last_id' => '',
                'more' => false
            ];
        }
        $response = [
            'room' => $room,
            'chat' => $chat
        ];
        return response()->json(['success' => true, 'data' => $response]);
    }
    
    public function sendChat(Request $request) {
        $validator = Validator::make($request->all(), [
	        'room_id' => 'required|numeric',
	        'pesan' => 'required'
	    ]);
        if ($validator->fails()) {
	        return response()->json([
	            'success' => false,
	            'message' => $validator->errors()->all()[0],
	        ], 422);
	    }
	    $valid = $validator->validated();
        $message = new Message;
        $message->room_id = $valid['room_id'];
        $message->message = $valid['pesan'];
        $message->user_id = Auth::id();
        $message->save();

        $user = Auth::user();

        broadcast(new MessageEvent(
            $message->id,
            $message->room_id,
            $message->user_id,
            $user->name,
            $message->type,
            $message->message,
            $message->created_at
        ));
        
        $data = [
            'id' => $message->id,
            'room_id' => $message->room_id,
            'user_id' => $message->user_id,
            'user_name' => $user->name,
            'type' => $message->type,
            'message' => $message->message,
            'created_at' => $message->created_at
        ];
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );
        $response = $pusher->trigger('room.'.$message->room_id, 'message-event', $data);
        if($response){
            return response()->json(['success' => true, 'message' => 'Berhasil mengirim pesan']);
        }
    }
}
