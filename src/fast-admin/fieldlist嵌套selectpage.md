```javascript
define(['jquery', 'bootstrap', 'backend', 'table', 'form' ,'vue'], function ($, undefined, Backend, Table, Form, Vue) {
    return {
        add  : function () {
            Form.api.bindevent($("form[role=form]"))
            // $(document).on("dp.change", ".fieldlist", function () {
            //     $(this).parent().prev().find("input").trigger("change");
            // });
            // $(document).on("fa.event.appendfieldlist", ".fieldlist .btn-append", function (e, obj) {
            //     Form.events.cxselect('.fieldlist dd.form-inline');
            //     // Form.events.cxselect($(.item_parent:last))
            // })
            // $(document).on('fa.event.appendfieldlist','.btn-append',_=>{
            //     Form.events.selectpage('#ttt')
            // })
            $(document).on("fa.event.appendfieldlist","#second-table .btn-append",function(e,obj){
                var employeescate_id=''
                $('.employeescate').on('change',function(e){
                    var key = $(this).attr('data-key')
                    $('#employeespost-'+key).selectPageClear()
                    var value = $('#employeescate-'+key).val()
                })
                $(document).on("focus",".employeespost",function(){
                    var key = $(this).attr('data-key');
                    employeescate_id = $('#employeescate'+key).val()
                })
                $('.employeespost').data("params",function(e){
                    return {custom:{employeescate_id:employeescate_id}}
                })
                Form.events.selectpage(obj)
                Form.events.datetimepicker(obj)
            })
        }
    }
})

```