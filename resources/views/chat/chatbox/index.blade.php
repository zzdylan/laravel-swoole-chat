<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0" />
        <meta name="format-detection" content="telephone=no,email=no,date=no,address=no">
        <title>聊天室</title>
        <link href="{{asset('Hui/css/Hui.css')}}" rel="stylesheet" type="text/css" />
        <style type="text/css">
        </style>
    </head>
    <body>
        <div class="H-chatbox H-padding-vertical-bottom-10">
            <div class="H-padding-vertical-top-10 H-text-align-center H-font-size-12 H-theme-font-color-999">{{$time}}</div>
            <div class="H-chatbox-receiver H-flexbox-horizontal H-padding-horizontal-both-10 H-box-sizing-border-box H-margin-vertical-top-10">
                <div class="H-chatbox-img H-position-relative"><img src="/image/logo.png" class="H-display-block H-border-radius-circle" alt="" title="" /></div>
                <div class="H-chatbox-main H-flex-item H-flexbox-horizontal H-position-relative H-margin-horizontal-left-12">
                    <div class="H-chatbox-content">
                        <div class="H-position-relative">
                            <div class="H-chatbox-content-text H-font-size-16 H-padding-10 H-theme-background-color-black H-theme-font-color-white H-border-radius-12">嗨，我是Hui。</div>
                            <div class="H-chatbox-bugle H-theme-border-color-black H-position-absolute H-z-index-100 H-bugle-left"></div>
                        </div>
                    </div>
                    <div class="H-chatbox-status H-flex-item H-padding-horizontal-both-10 H-box-sizing-border-box H-text-align-left H-padding-vertical-top-12"></div>
                </div>
            </div>
            <div class="H-chatbox-sender H-flexbox-horizontal H-padding-horizontal-both-10 H-box-sizing-border-box H-margin-vertical-top-10">
                <div class="H-chatbox-main H-flex-item H-flexbox-horizontal H-position-relative H-margin-horizontal-right-12">
                    <div class="H-chatbox-status H-flex-item H-padding-horizontal-both-10 H-box-sizing-border-box H-text-align-right H-padding-vertical-top-12"></div>
                    <div class="H-chatbox-content">
                        <div class="H-font-size-12 H-theme-font-color-444 H-padding-2">{{$user['nickname']}}</div>
                        <div class="H-position-relative">
                            <div class="H-chatbox-content-text H-font-size-16 H-padding-10 H-theme-background-color1 H-theme-font-color-white H-border-radius-12">嗨，我是阿震。</div>
                            <div class="H-chatbox-bugle H-theme-border-color1 H-position-absolute H-z-index-100 H-bugle-right"></div>
                        </div>
                    </div>
                </div>
                <div class="H-chatbox-img H-position-relative"><img src="{{$user['avatar']}}" class="H-display-block H-border-radius-circle" alt="" title="" /></div>
            </div>
        </div>
        <div id="msg"></div>
        <input type="text" id="text">
        <input type="submit" value="发送数据" onclick="song()">
        <script src="{{asset('Hui/js/H.js')}}" type="text/javascript"></script>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
        <script type="text/javascript">
        /* 处理 Android 4.4 以下版本兼容性问题 */
        function resizeWidth() {
            H.cssText(".H-chatbox-content", "max-width:" + (document.body.clientWidth - 60 * 2) + "px");
        }
        resizeWidth();
        window.onresize = function () {
            resizeWidth();
        }

        var msg = document.getElementById("msg");
        var wsServer = 'ws://97.64.38.12:9502';
        //调用websocket对象建立连接：
        //参数：ws/wss(加密)：//ip:port （字符串）
        var websocket = new WebSocket(wsServer);
        //onopen监听连接打开
        websocket.onopen = function (evt) {
            //websocket.readyState 属性：
            /*
             CONNECTING  0   The connection is not yet open.
             OPEN    1   The connection is open and ready to communicate.
             CLOSING 2   The connection is in the process of closing.
             CLOSED  3   The connection is closed or couldn't be opened.
             */
            if (websocket.readyState == 1) {
                msg.innerHTML = '进入聊天室<br>';
            }

        };

        function song() {
            var text = document.getElementById('text').value;
            document.getElementById('text').value = '';
            //向服务器发送数据
            websocket.send(text);
        }
        //监听连接关闭
        //    websocket.onclose = function (evt) {
        //        console.log("Disconnected");
        //    };

        //onmessage 监听服务器数据推送
        websocket.onmessage = function (evt) {
            msg.innerHTML += evt.data + '<br>';
            console.log('Retrieved data from server: ' + evt.data);
        };
        //监听连接错误信息
        //    websocket.onerror = function (evt, e) {
        //        console.log('Error occured: ' + evt.data);
        //    };

        </script>
    </body>
</html>