<html lang="zh">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>test</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/2.1.1/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/bootstrap.css">
    <link href="/css/style.css" rel="stylesheet">


    <script src="https://cdn.bootcdn.net/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="/js/jquery-sinaEmotion-2.1.0.min.js"></script>
    <link href="/css/jquery-sinaEmotion-2.1.0.min.css" rel="stylesheet">
    <script>
        var ws, name, client_list = {}, room_id, client_id;
        room_id = getQueryString('room_id') ? getQueryString('room_id') : 1;

        function getQueryString(name) {
            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
            var r = window.location.search.substr(1).match(reg);
            if (r != null) return unescape(r[2]);
            return null;
        }

        function connect() {
            ws = new WebSocket("ws://" + document.domain + ":7272");
            ws.onclose = function () {
                connect()
            }
            ws.onopen = function onopen() {
                if (!name) {
                    name = prompt('输入你的名字：', '');
                    if (!name || name == 'null') {
                        name = '游客';
                    }
                }
                ws.send('{"type":"login","client_name":"' + name.replace(/"/g, '\\"') + '","room_id":' + room_id + '}');
            }
            ws.onmessage = function onmessage(e) {
                console.log(e.data);
                const data = JSON.parse(e.data);
                switch (data['type']) {
                    case 'ping':
                        ws.send('{"type":"pong"}')
                        break
                    case 'logout':
                        //{"type":"logout","client_id":xxx,"time":"xxx"}
                        say(data['from_client_id'], data['from_client_name'], data['from_client_name'] + ' 退出了', data['time'])
                        delete client_list[data['from_client_id']]
                        flush_client_list()
                        break
                    case 'login':
                        var client_name = data['client_name'];
                        if (data['client_list']) {
                            client_id = data['client_id'];
                            client_name = '你';
                            client_list = data['client_list'];
                        } else {
                            client_list[data['client_id']] = data['client_name'];
                        }
                        say(data['client_id'], data['client_name'], client_name + ' 加入了聊天室', data['time']);
                        flush_client_list();
                        console.log(data['client_name'] + "登录成功");
                        break;
                    case 'say':
                        //{"type":"say","from_client_id":xxx,"to_client_id":"all/client_id","content":"xxx","time":"xxx"}
                        say(data['from_client_id'], data['from_client_name'], data['content'], data['time']);
                        break;
                }
            }
        }

        function onSubmit() {
            var input = document.getElementById("textarea");
            var to_client_id = $("#client_list option:selected").attr("value");
            var to_client_name = $("#client_list option:selected").text();
            ws.send('{"type":"say","to_client_id":"' + to_client_id + '","to_client_name":"' + to_client_name + '","content":"' + input.value.replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r') + '"}');
            input.value = "";
            input.focus();
        }

        function flush_client_list() {
            var userlist_window = $("#userlist");
            var client_list_slelect = $("#client_list");
            userlist_window.empty();
            client_list_slelect.empty();
            userlist_window.append('<h4>在线用户</h4><ul>');
            client_list_slelect.append('<option value="all" id="cli_all">所有人</option>');
            for (var p in client_list) {
                userlist_window.append('<li id="' + p + '">' + client_list[p] + '</li>');
                if (p != client_id) {
                    client_list_slelect.append('<option value="' + p + '">' + client_list[p] + '</option>');
                }
            }
            $("#client_list").val(select_client_id);
            userlist_window.append('</ul>');
        }

        function say(from_client_id, from_client_name, content, time) {
            //解析新浪微博图片
            content = content.replace(/(http|https):\/\/[\w]+.sinaimg.cn[\S]+(jpg|png|gif)/gi, function (img) {
                    return "<a target='_blank' href='" + img + "'>" + "<img src='" + img + "'>" + "</a>";
                }
            );
            //解析url
            content = content.replace(/(http|https):\/\/[\S]+/gi, function (url) {
                    if (url.indexOf(".sinaimg.cn/") < 0)
                        return "<a target='_blank' href='" + url + "'>" + url + "</a>";
                    else
                        return url;
                }
            );
            $("#dialog").append('<div class="speech_item"><img src="http://lorempixel.com/38/38/?' + from_client_id + '" class="user_icon" /> ' + from_client_name + ' <br> ' + time + '<div style="clear:both;"></div><p class="triangle-isosceles top">' + content + '</p> </div>').parseEmotion();
        }

        $(function () {
            select_client_id = 'all';
            $("#client_list").change(function () {
                select_client_id = $("#client_list option:selected").attr("value");
            });
            $('.face').click(function (event) {
                $(this).sinaEmotion();
                event.stopPropagation();
            });
        });
    </script>
</head>
<body onload="connect();">
<div class="container">
    <div class="row clearfix">
        <div class="col-md-1 column">
        </div>
        <div class="col-md-6 column">
            <div class="thumbnail">
                <div class="caption" id="dialog"></div>
            </div>
            <form onsubmit="onSubmit(); return false;">
                <select style="margin-bottom:8px" id="client_list">
                    <option value="all">所有人</option>
                </select>
                <textarea class="textarea thumbnail" id="textarea"></textarea>
                <div class="say-btn">
                    <input type="button" class="btn btn-default face pull-left" value="表情"/>
                    <input type="submit" class="btn btn-default" value="发表"/>
                </div>
            </form>
            <div>
                &nbsp;&nbsp;&nbsp;&nbsp;<b>房间列表:</b>（当前在&nbsp;房间
                <script>document.write(room_id)</script>
                ）<br>
                &nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=1">房间1</a>&nbsp;&nbsp;&nbsp;&nbsp;<a
                        href="/?room_id=2">房间2</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="/?room_id=3">房间3</a>&nbsp;&nbsp;&nbsp;&nbsp;<a
                        href="/?room_id=4">房间4</a>
                <br><br>
            </div>
        </div>
        <div class="col-md-3 column">
            <div class="thumbnail">
                <div class="caption" id="userlist"></div>
            </div>
        </div>
    </div>
</div>
<script>
    var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
    document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F7b1919221e89d2aa5711e4deb935debd' type='text/javascript'%3E%3C/script%3E"))
    // 动态自适应屏幕
    document.write('<meta name="viewport" content="width=device-width,initial-scale=1">');
    $("textarea").on("keydown", function (e) {
        // 按enter键自动提交
        if (e.keyCode === 13 && !e.ctrlKey) {
            e.preventDefault();
            $('form').submit();
            return false;
        }
        // 按ctrl+enter组合键换行
        if (e.keyCode === 13 && e.ctrlKey) {
            $(this).val(function (i, val) {
                return val + "\n";
            });
        }
    });
</script>
</body>
</html>
