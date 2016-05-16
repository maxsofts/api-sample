#Cách sử dụng

với localhost là : api.max


```php
$.ajax({
url:'http://api.max/max-api',
dataType:'json',
data:{
'action':'get_token',
'username':'maxapi',
'password':'3md3pk0c4ns0nph4n'
},
success:function(results){
console.log(results);
}
});

```
sửa config database trong file database

Bảng mặc định

max_api_user

các trường
```
id
username
password
token
```


