define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'cookie'], function ($, undefined, Backend, Table, Form, Template, undefined) {
    return {
        index: _ => {
            Table.api.init({
                extend: {
                    index_url: 'v/test/index' + location.search,
                    add_url  : 'v/test/add',
                    edit_url : 'v/test/edit',
                    del_url  : 'v/test/del',
                    table    : 'user'
                }
            })
            let table = $("#table")
            table.bootstrapTable({
                url    : $.fn.bootstrapTable.defaults.extend.index_url,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: '编号'},
                        {
                            field    : 'operate',
                            title    : '操作',
                            table    : table,
                            events   : Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            })
            Table.api.bindevent(table)
        },
        add  : _ => {
            Form.api.bindevent($('form'))
        },
        edit : _ => {
            $(document).on("fa.event.appendfieldlist", ".btn-append", function (event, dom) {
                Form.events.datetimepicker(dom)
                Form.events.faupload(dom)
                Form.events.selectpage(dom)
                Form.events.cxselect(dom)
            })
            Form.api.bindevent($('form'))
        }
    }
})
