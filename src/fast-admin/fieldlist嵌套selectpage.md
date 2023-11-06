```html
<form id="add-form" class="form-horizontal" role="form" data-toggle="validator" method="POST" action="">

    <div class="form-group">
        <label class="control-label col-xs-2">名称</label>
        <div class="col-xs-8">
            {:Form::text('row[name]',$row.name??'')}
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-2">标题</label>
        <div class="col-xs-8">
            {:Form::text('row[title]',$row.title??'')}
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-2">图片</label>
        <div class="col-xs-8">
            {:Form::image('row[image]',$row.image??'')}
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-2">介绍</label>
        <div class="col-xs-8">
            {:Form::editor('row[content]',$row.content??'')}
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-2">类型</label>
        <div class="col-xs-8">
            {:Form::radios('row[type]',['1'=>'按天','2'=>'按次'],$row.type??1)}
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-2">截至日期</label>
        <div class="col-xs-8">
            {:Form::datetimepicker('row[expire_at]','',[])}
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-2">期限(天)</label>
        <div class="col-xs-8">
            {:Form::input('number','row[expire]',$row.expire??100)}
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-xs-2">事项</label>
        <div class="col-xs-8">
            {:Form::textarea('row[item]','',['class'=>'hide'])}
            <table class="table fieldlist" data-template="template" data-name="row[item]">
                <tr>
                    <td style="width:200px">类型</td>
                    <td style="width:200px">多选</td>
                    <td style="width:100px">每个目标写多少字完成</td>
                </tr>
                <tr>
                    <td>
                        <span class="btn btn-success btn-append">添加</span>
                    </td>
                </tr>
            </table>
            <script type="text/html" id="template">
                <tr class="form-inline">
                    <td><input size="20" class="form-control type type-<%=index%> selectpage" name="<%=name%>[<%=index%>][type]" data-source="vv/plan/type" data-key="<%=index%>"></td>
                    <td><input size="20" class="form-control ids ids-<%=index%> selectpage" name="<%=name%>[<%=index%>][ids]" data-source="vv/plan/item" data-key="<%=index%>" data-multiple="true"></td>
                    <td><input size="20" class="form-control" name="<%=name%>[<%=index%>][count]" type="number" value="10"></td>
                    <td><span class="btn btn-danger btn-remove"><i class="fa fa-times"></i></span></td>
                </tr>
            </script>
        </div>
    </div>

    <div class="form-group layer-footer">
        <label class="control-label col-2"></label>
        <div class="col-xs-8">
            <button type="submit" class="btn btn-primary btn-embossed disabled">确定</button>
            <button type="reset" class="btn btn-default btn-embossed">重置</button>
        </div>
    </div>

</form>
```

```javascript
    add  : function () {
        Form.api.bindevent($("form[role=form]"))
        $(document).on("dp.change", ".datetimepicker", function () {
            $(this).parent().prev().find("input").trigger("change")
        })
        $(document).on("fa.event.appendfieldlist", ".btn-append", function (e, o) {
            var type
            $('.type').on('change', function (e) {
                var key = $(this).attr('data-key')
                type = $("input[name='row[item]["+key+"][type]']").val()
                $('.ids-'+key).selectPageClear()
            })
            $(document).on("focus", ".ids", function(){
                var key = $(this).attr('data-key');
                type = $("input[name='row[item]["+key+"][type]']").val();
                // type = $('.type-'+key).val()
            });
            $('.ids').data('params', function(){
                return {custom: {type:type}};
            })
            Form.events.cxselect(o)
            Form.events.selectpage(o)
            Form.events.datetimepicker(o)
            Form.events.faupload(o)
        })
    }
```