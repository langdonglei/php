# 在新窗口中使用以下代码可以给父窗口(从哪个窗口点击addclass_dialog),可以给窗口发送参数,父窗口在callback:function(str){接收参数}

```javascript
parent.window.$(".layui-layer-iframe").find(".layui-layer-close").on('click', function () {
    Fast.api.close("参数可空")
})
parent.window.document.onkeyup = function (e) {
    if (e.code === 'Escape') {
        Fast.api.close()
    }
}
```
```javascript
{
    text     : '处理退款',
    classname: 'btn btn-xs btn-primary btn-dialog',
    url      : 'refund/index?ding_id={id}', // {id}是当前row的字段 默认传参 ids=id
    disable  : function (row) {
        return row.status !== 'refund' || row.refund_wait_is === 0
    },
    callback : function () {
        $(".btn-refresh").trigger("click")
    }
},
```

