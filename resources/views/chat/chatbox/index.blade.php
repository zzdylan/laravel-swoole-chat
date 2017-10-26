<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0" />
        <meta name="format-detection" content="telephone=no,email=no,date=no,address=no">
        <title>聊天室</title>
        <link href="{{asset('Hui/css/Hui.css')}}" rel="stylesheet" type="text/css" />
        <style type="text/css">
            html,body{
                height:100%
            }
        </style>
    </head>
    <body class="H-flexbox-vertical">
        <header class="H-chatbox H-padding-vertical-bottom-10">
            <div onclick="clearStorage()" class="H-padding-vertical-top-10 H-text-align-center H-font-size-12 H-theme-font-color-999">{{$time}}</div>

        </header>
        <main id="main" class="H-flex-item H-overflow-y-scroll H-padding-vertical-bottom-10">
            <div id="msg"></div>
        </main>
        <footer class="H-flexbox-horizontal">
            <input oninput="checkValueEmpty(this)" onclick="scrollToBottom()" id="text" type="text" class="H-textbox H-vertical-align-middle H-vertical-middle H-font-size-14 H-flex-item H-box-sizing-border-box H-border-none H-border-vertical-top-after H-padding-12">
            <button onclick="song()" id="send" disabled class="H-button H-font-size-14 H-border-none H-padding-vertical-both-12">发送</button>
        </footer>
        <script src="{{asset('Hui/js/H.js')}}" type="text/javascript"></script>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<!--        <script src="https://cdn.bootcss.com/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>-->
        <script type="text/javascript">
                /* 处理 Android 4.4 以下版本兼容性问题 */
                function resizeWidth() {
                    H.cssText(".H-chatbox-content", "max-width:" + (document.body.clientWidth - 60 * 2) + "px");
                }
                resizeWidth();
                window.onresize = function () {
                    resizeWidth();
                }
                document.onkeydown = function () {                //网页内按下回车触发
                    if (event.keyCode == 13)
                    {
                        document.getElementById("send").click();
                        return false;
                    }
                }
                var msg = document.getElementById("msg");
                var wsServer = 'ws://97.64.38.12:9502';
                //调用websocket对象建立连接：
                //参数：ws/wss(加密)：//ip:port （字符串）
                var websocket = new WebSocket(wsServer);
                //onopen监听连接打开
                websocket.onopen = onopen;

                function onopen(evt) {
                    //websocket.readyState 属性：
                    /*
                     CONNECTING  0   The connection is not yet open.
                     OPEN    1   The connection is open and ready to communicate.
                     CLOSING 2   The connection is in the process of closing.
                     CLOSED  3   The connection is closed or couldn't be opened.
                     */
                    if (websocket.readyState == 1) {
                        msg.innerHTML += '<div style="text-align:center">连接成功...正在进入聊天室...</div>';
                    }else{
                        msg.innerHTML += '<div style="text-align:center">连接失败</div>';
                    }
                }

                function onmessage(evt) {
                    var jsonData = JSON.parse(evt.data);
                    if (jsonData.type == 'inform') {
                        msg.innerHTML += '<div style="text-align:center">' + jsonData.content + '</div>';
                    } else if (jsonData.type == 'message') {
                        msg.innerHTML += msgHtml(jsonData.msg, jsonData.nickname, jsonData.avatar, jsonData.is_own);
                        scrollToBottom();
                        console.log('Retrieved data from server: ' + evt.data);
                    } else if (jsonData.type == 'refresh_token') {
                        localStorage.setItem('token', jsonData.token);
                    } else if (jsonData.type == 'get_token') {
                        var data = {type: 'send_token', token: localStorage.getItem('token')};
                        console.log(data);
                        websocket.send(JSON.stringify(data));
                    }

                }
                ;

                function clearStorage() {
                    localStorage.clear();
                    alert('清除缓存成功');
                    location.reload();
                }

                //断线重连
                function reconnect() {
                    msg.innerHTML += '<div style="text-align:center" onclick="clearStorage()">连接已经断开</div>';
                    console.log('连接已经断开');
                    websocket = new WebSocket(wsServer);
                    websocket.onmessage = onmessage;
                    websocket.onclose = disConnect;
                    websocket.onerror = function (evt, e) {
                        console.log('Error occured: ' + evt.data);
                    };
                    websocket.onopen = onopen;
                }

                var disConnect = function () {
                    msg.innerHTML += '<div style="text-align:center" onclick="clearStorage()">重新连接中...</div>';
                    console.log('重新连接中...');
                    setTimeout(function () {
                        reconnect();
                    }, 5000);
                }

                //检查用户是否输入，启用“发送”按钮
                function checkValueEmpty(ele) {
                    if (ele.value) {
                        $('#send').removeAttr('disabled').addClass('H-theme-background-color1');
                    } else {
                        $('#send').attr('disabled', true).removeClass('H-theme-background-color1');
                    }
                }

                //滚动到底部
                function scrollToBottom() {
                    var main = document.getElementById('main');
                    main.scrollTop = main.scrollHeight + main.offsetHeight;
                }
                //发送
                function song() {
                    var text = $('#text').val();
                    $('#text').val('').focus();
                    $('#send').attr('disabled', true).removeClass('H-theme-background-color1');
                    //向服务器发送数据
                    var data = {type: 'message', token: localStorage.getItem('token'), content: text};
                    console.log(data);
                    websocket.send(JSON.stringify(data));
                }
                //监听连接关闭
                websocket.onclose = disConnect;

                //onmessage 监听服务器数据推送
                websocket.onmessage = onmessage;
                //监听连接错误信息
                websocket.onerror = function (evt, e) {
                    console.log('Error occured: ' + evt.data);
                };

                function msgHtml(msg, nickname, avatar, is_own) {
                    if (is_own) {
                        return ['<div class="H-chatbox-sender H-flexbox-horizontal H-padding-horizontal-both-10 H-box-sizing-border-box H-margin-vertical-top-10">',
                            '            <div class="H-chatbox-main H-flex-item H-flexbox-horizontal H-position-relative H-margin-horizontal-right-12">',
                            '                <div class="H-chatbox-status H-flex-item H-padding-horizontal-both-10 H-box-sizing-border-box H-text-align-right H-padding-vertical-top-12"></div>',
                            '                <div class="H-chatbox-content">',
                            '                    <div class="H-font-size-12 H-theme-font-color-444 H-padding-2 H-text-align-right">',
                            nickname,
                            '                   </div>',
                            '                    <div class="H-position-relative">',
                            '                        <div class="H-chatbox-content-text H-font-size-16 H-padding-10 H-theme-background-color1 H-theme-font-color-white H-border-radius-12">',
                            msg,
                            '                         </div>',
                            '                        <div class="H-chatbox-bugle H-theme-border-color1 H-position-absolute H-z-index-100 H-bugle-right"></div>',
                            '                    </div>',
                            '                </div>',
                            '            </div>',
                            '            <div class="H-chatbox-img H-position-relative"><img src="',
                            avatar,
                            '" class="H-display-block H-border-radius-circle" alt="" title="" /></div>',
                            '        </div>'].join("");
                    } else {
                        return ['<div class="H-chatbox-receiver H-flexbox-horizontal H-padding-horizontal-both-10 H-box-sizing-border-box H-margin-vertical-top-10">',
                            '            <div class="H-chatbox-img H-position-relative"><img src="',
                            avatar,
                            '" class="H-display-block H-border-radius-circle" alt="" title="" /></div>',
                            '            <div class="H-chatbox-main H-flex-item H-flexbox-horizontal H-position-relative H-margin-horizontal-left-12">',
                            '                <div class="H-chatbox-content">',
                            '                    <div class="H-font-size-12 H-theme-font-color-444 H-padding-2">',
                            nickname,
                            '</div>',
                            '                    <div class="H-position-relative">',
                            '                        <div class="H-chatbox-content-text H-font-size-16 H-padding-10 H-theme-background-color-black H-theme-font-color-white H-border-radius-12">',
                            msg,
                            '</div>',
                            '                        <div class="H-chatbox-bugle H-theme-border-color-black H-position-absolute H-z-index-100 H-bugle-left"></div>',
                            '                    </div>',
                            '                </div>',
                            '                <div class="H-chatbox-status H-flex-item H-padding-horizontal-both-10 H-box-sizing-border-box H-text-align-left H-padding-vertical-top-12"></div>',
                            '            </div>',
                            '        </div>'].join("");
                    }
                }
        </script>
    </body>
</html>