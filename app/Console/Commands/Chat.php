<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use swoole_websocket_server;
use Illuminate\Support\Facades\Cache;
use App\Models\UsersCopy;
use Image;

class Chat extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'start chat server';
    protected $fd = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        //创建websocket服务器对象，监听0.0.0.0:9502端口
        $ws = new swoole_websocket_server("0.0.0.0", 9502);

        //监听WebSocket连接打开事件
        $ws->on('open', function ($ws, $request) {
            $pushData = ['type' => 'get_token'];
            $ws->push($request->fd, json_encode($pushData));
            $this->info("client-{$request->fd} is connected\n");
        });

        //监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
//            if($frame->data == ''){
//                return false;
//            }
            $data = json_decode($frame->data, true);
            if(!$data){
                file_put_contents('test.jpg', file_get_contents($frame->data));
            }
            //echo "data\n";
            //var_dump($data);
            if ($data['type'] == 'message') {
                $userInfo = Cache::get($data['token']);
                $content = htmlentities($data['content']);
                echo '(fd:' . $frame->fd . ')' . $userInfo['nickname'] . ':' . $frame->data . "\n";
                $pushData = ['type' => 'message', 'nickname' => $userInfo['nickname'], 'avatar' => $userInfo['avatar'], 'msg' => $content];
                foreach ($ws->connections as $i) {
                    if ($i == $frame->fd) {
                        $pushData['is_own'] = 1;
                    } else {
                        $pushData['is_own'] = 0;
                    }
                    $ws->push($i, json_encode($pushData));
                }
            } else if ($data['type'] == 'send_token') {
                if (!$data['token']) {
                    $token = uniqid('user_');
                    $pushData ['type'] = 'refresh_token';
                    $pushData ['token'] = $token;
                    $userInfo = Cache::rememberForever($token, function() {
                                $randomUser = UsersCopy::
                                        inRandomOrder()
                                        ->first();
                                $pathinfo = pathinfo($randomUser->avatar);
                                $extension = isset($pathinfo['extension']) ? $pathinfo['extension'] : 'png';
                                $fileName = md5_file($randomUser->avatar) . '.' . $extension;
                                //file_put_contents(public_path('avatar/') . $fileName, $avatar);
                                $img = Image::make($randomUser->avatar)->resize(50, 50);
                                $img->save(public_path('avatar/' . $fileName));
                                $randomUser->avatar = env('APP_URL') . 'avatar/' . $fileName;
                                return ['nickname' => $randomUser->nickname, 'avatar' => $randomUser->avatar];
                            });
                    Cache::forever("fd$frame->fd", $userInfo);
                    echo "生成token并且保存:$token" . "\n" . '用户资料:';
                    var_dump($userInfo);
                    $ws->push($frame->fd, json_encode($pushData));
                } else {
                    $userInfo = Cache::get($data['token']);
                    if (!$userInfo) {
                        $userInfo = Cache::rememberForever($data['token'], function() {
                                    $randomUser = UsersCopy::
                                            inRandomOrder()
                                            ->first();
                                    //dd($randomUser);
                                    //$avatar = file_get_contents($randomUser->avatar);
                                    $pathinfo = pathinfo($randomUser->avatar);
                                    $extension = isset($pathinfo['extension']) ? $pathinfo['extension'] : 'png';
                                    $fileName = md5_file($randomUser->avatar) . '.' . $extension;
                                    //file_put_contents(public_path('avatar/') . $fileName, $avatar);
                                    $img = Image::make($randomUser->avatar)->resize(50, 50);
                                    $img->save(public_path('avatar/' . $fileName));
                                    $randomUser->avatar = env('APP_URL') . 'avatar/' . $fileName;
                                    return ['nickname' => $randomUser->nickname, 'avatar' => $randomUser->avatar];
                                });
                        Cache::forever("fd$frame->fd", $userInfo);
                    }
                    echo "userInfo\n";
                    var_dump($userInfo);
                }
                //if ($userInfo) {
                $pushData = ['type' => 'inform', 'content' => "欢迎{$userInfo['nickname']}进入聊天室"];
                foreach ($ws->connections as $i) {
                    $ws->push($i, json_encode($pushData));
                }
                //}
            } else if ($data['type'] == 'image') {
//                $disk = \Storage::disk('qiniu');
//                $this->uploadFile($filePath, $savePath);
                echo "文件\n";
                var_dump($data);
            }

            // $ws->push($frame->fd, "server: {$frame->data}");
            // $ws->push($frame->fd, "server: {$frame->data}");
        });

        //监听WebSocket连接关闭事件
        $ws->on('close', function ($ws, $fd) {
            $this->info("client-{$fd} is closed\n");
            $userInfo = Cache::get("fd$fd");
            var_dump($userInfo);
            if ($userInfo) {
                $pushData = ['type' => 'inform', 'content' => "{$userInfo['nickname']}退出了聊天室"];
                foreach ($ws->connections as $i) {
                    $ws->push($i, json_encode($pushData));
                }
            }
        });

        $ws->on('shutdown', function($ws, $fd) {
            echo 'onShutdown';
        });

        $this->info("server starting at 9502 port...");
        $ws->start();
    }

    private function uploadFile($filePath, $savePath) {
        $disk = \Storage::disk('qiniu');
        $pathinfo = pathinfo($filePath);
        $extension = isset($pathinfo['extension']) ? $pathinfo['extension'] : 'png';
        $fileName = md5_file($filePath) . '.' . $extension;
        //file_put_contents(public_path('avatar/') . $fileName, $avatar);
        $img = Image::make($filePath)->resize(50, 50);
        $savePath = $savePath . md5($img);
        $disk->put($savePath, $img); //上传文件
        return $disk->getDriver()->downloadUrl($savePath);
    }

}
