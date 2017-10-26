<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ChatController extends Controller {

    public function enter(Request $request) {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;
        $weekArr = ['星期天', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
        $time = $now->toDateTimeString() . ' ' . $weekArr[$dayOfWeek];
        return view('chat.chatbox.index', ['time' => $time]);
    }

    public function chatRoom() {
        return view('chat.chatbox.index');
    }

    public function getCache(){
        dd(Cache::get('users.101.87.74.202'));
    }
}
