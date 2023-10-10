```html
    <div class="form-group">
    <label class="control-label col-xs-12 col-sm-2">标签</label>
    <div class="col-xs-12 col-sm-8">
        <select class="form-control selectpicker" multiple="" name="row[label][]">
            {foreach $tagList??[] as $tag}
            <option value="{$key}" {in name="key" value="" }selected{/in}>{$vo}</option>
            {/foreach}
        </select>
    </div>
</div>
```
