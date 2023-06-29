!function ws() {
    const socket = new WebSocket("ws://" + document.domain + ":4110")
    socket.onclose = function () {
        ws()
    }
    socket.onmessage = function (e) {
        const {type, data} = JSON.parse(e.data)
        switch (type) {
            case 'ping':
                socket.send(JSON.stringify({type: 'pong'}))
                break;
        }
    }
}()
