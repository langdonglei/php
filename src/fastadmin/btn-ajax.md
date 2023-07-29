```javascript
{
    text: '退款',
    classname: 'btn btn-xs btn-danger btn-ajax',
    url: 'ding/refund',
    visible: function (item) {
        return item['status'] === 'refunding'
    },
    confirm: '确定吗?',
    refresh: true
}
```
