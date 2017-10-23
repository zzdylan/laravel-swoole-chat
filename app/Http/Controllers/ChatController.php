<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsersCopy;
use Illuminate\Support\Facades\Cache;

class ChatController extends Controller
{
    public function enter(Request $request){
        $ip = $request->ip();
    }
}
