```php
protected $multiFields = 'audit_switch';
```
```javascript
{
    field: 'audit_switch',
    title: '审核',
    table: table,
    formatter: Table.api.formatter.toggle,
    operate: Config.operate,
    searchList: {"1":__('Yes'),"0":__('No')},
    visible: Config.is_super_admin,
},
```
```text
auth/rule multi 权限
```
