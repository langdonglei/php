```html
<div class="form-group">
    <label class="control-label col-xs-12 col-sm-2">核销人员:</label>
    <div class="col-xs-12 col-sm-8">
        <input class="form-control selectpage" name="row[user_ids]" value="{$row.user_ids}"
               data-source="user/user/index"
               data-params='{"custom[group_id]":0}'
               data-primary-key="id"
               data-field="nickname"
               data-pagination="true"
               data-page-size="10"
               data-multiple="true"
               data-max-select-limit="5"
        >
    </div>
</div>
```
