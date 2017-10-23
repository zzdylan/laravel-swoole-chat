<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsersCopy;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ChatController extends Controller {

    public function enter(Request $request) {
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;
        $weekArr = ['星期天', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'];
        $time = $now->toDateTimeString() . ' ' . $weekArr[$dayOfWeek];
        $ip = $request->ip();
        $user = Cache::rememberForever('users.' . $ip, function() {
                    $randomUser = UsersCopy::
                            inRandomOrder()
                            ->first();
                    //dd($randomUser);
                    $avatar = file_get_contents($randomUser->avatar);
                    $fileName = md5_file($randomUser->avatar);
                    file_put_contents(public_path('avatar/') . $fileName, $avatar);
                    return ['nickname' => $randomUser->nickname, 'avatar' => url('avatar/' . $fileName)];
                });
        return view('chat.chatbox.index', ['time' => $time,'user'=>$user]);
    }

    public function chatRoom() {
        return view('chat.chatbox.index');
    }

}
