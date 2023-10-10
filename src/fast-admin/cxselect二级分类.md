```html
<div class="form-group">
    <label class="control-label col-xs-12 col-sm-2">分类</label>
    <div class="col-xs-12 col-sm-8">
        <div class="form-inline"
             data-toggle="cxselect"
             data-selects="flagA,flagB"
        >
            <select class="form-control flagA" name="row[category_pid]"
                    data-value="{$row.category_pid??''}"
                    data-url="ajax/category?type=good&pid=0"
            ></select>
            <select class="form-control flagB" name="row[category_id]"
                    data-value="{$row.category_id??''}"
                    data-url="ajax/category"
                    data-query-name="pid"
            ></select>
            <!--data-query-name="pid" request ajax/category?pid=上个select的value-->
        </div>
    </div>
</div>
```
# index 搜索
```javascript
{
    field     : 'not_exist_1',
    title     : '地区',
    searchList: function () {
        return Template('area_tpl', {})
    },
    visible   : false,
},
$('#area-select').parent('div').parent('div').css('z-index', '9999')
```
```html
<script id="area_tpl" type="text/html">
    <div class="row" id="area-select">
        <div class="col-xs-12">
            <div class="form-inline" data-toggle="cxselect" data-selects="province,city,area">
                <select class="province form-control" name="province_id" data-url="ajax/area" ></select>
                <input type="hidden" class="operate" data-name="province_id" value="=" />
                <select class="city form-control" name="city_id" data-url="ajax/area" data-query-name="province"></select>
                <input type="hidden" class="operate" data-name="city_id" value="=" />
                <select class="area form-control" name="area_id" data-url="ajax/area" data-query-name="city"></select>
                <input type="hidden" class="operate" data-name="area_id" value="=" />
            </div>
        </div>
    </div>
</script>
```
