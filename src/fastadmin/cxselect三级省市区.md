# add edit
```html
<div class="form-group">
    <label class="control-label col-xs-12 col-sm-2">地区:</label>
    <div class="col-xs-12 col-sm-8">
        <div class="form-inline"
             data-toggle="cxselect"
             data-selects="province,city,area"
        >
            <select class="province form-control" name="row[province_id]"
                    data-url="ajax/area"
            ></select>
            <select class="city form-control" name="row[city_id]"
                    data-url="ajax/area"
                    data-query-name="province"
            ></select>
            <select class="area form-control" name="row[area_id]"
                    data-url="ajax/area"
                    data-query-name="city"
            ></select>
        </div>
    </div>
</div>
<div class="form-group">
    <label class="control-label col-xs-12 col-sm-2">地区:</label>
    <div class="col-xs-12 col-sm-8">
        <div class="form-inline"
             data-toggle="cxselect"
             data-selects="province,city,area">
            <select class="province form-control" name="row[province_id]"
                    data-url="ajax/area"
                    data-value="{$row.province_id}"
            ></select>
            <select class="city form-control" name="row[city_id]"
                    data-url="ajax/area"
                    data-query-name="province"
                    data-value="{$row.city_id}"
            ></select>
            <select class="area form-control" name="row[area_id]"
                    data-url="ajax/area"
                    data-query-name="city"
                    data-value="{$row.area_id}">
            </select>
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
