<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
            $fd[] = $request->fd;
            $GLOBALS['fd'][] = $fd;
            //$ws->push($request->fd, "hello, welcome\n");
        });

//监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
            $msg = 'from' . $frame->fd . ":{$frame->data}\n";
//var_dump($GLOBALS['fd']);
//exit;
            foreach ($GLOBALS['fd'] as $aa) {
                foreach ($aa as $i) {
                    $ws->push($i, $msg);
                }
            }
            // $ws->push($frame->fd, "server: {$frame->data}");
            // $ws->push($frame->fd, "server: {$frame->data}");
        });

//监听WebSocket连接关闭事件
        $ws->on('close', function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
        });
        $this->info('server starting at 9502 port...');

        $ws->start();   
    }

}
