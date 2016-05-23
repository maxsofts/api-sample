#Hướng dẫn sử dụng

Cài đặt csdl vào file ```config/config.php``` sửa thông tin kết nối database.

API giờ xây dựng riêng cho Ghiền nên database sử dụng có thể liên hệ ```khanh.dao@maxxus.vn``` để lấy

Chi tiết hoạt động API vui lòng xem code :|

#Các hàm đã xây dựng

1. Tạo token
2. Reset token
3. Đăng ký
4. Đăng nhập


#Hướng dẫn sử dụng với Ajax Jquery

# Tạo và lấy token //reset token
```php
$.ajax({
    url:'http://<Đường dẫn của bạn đến thư mục>/',
    dataType:'json',
    data:{
        'action':'get_token',               // thay action là reset_token để lấy mã token mới
        'vendor':'<vendor được cung cấp>',
        'hash':'<hash được cung cấp>'
    },
    success:function(results){
        console.log(results); //Dữ liệu trả về
    }
});
```
hoặc truy cập trực tiếp vào đường dẫn
```php
http://<Đường dẫn của bạn đến thư mục>/?action=get_token&vendor=vendorcungcap&hash=hashcungcap // thay get_token = reset_token để lấy mã token mới
```

Dữ liệu trả về
```php
{"success":true,"_token":"matoken"}
```

Lưu trữ mã token để thay bằng việc auth trực tiếp qua hệ thống

# Đăng ký

Dữ liệu truyền lên với kiểu đăng ký là số điện thoại

```php
$.ajax({
    url:'http://<Đường dẫn của bạn đến thư mục>/',
    dataType:'json',
    data:{
        'action':'register',
        'token' : '<mã token được cung cấp>',   // mã token đã lấy ở trên
        'type': 'phone',
        'name': '<Tên hiển thị>',
        'username':'<Số điện thoại>',
        'password':'<Mật khẩu>'
    },
    success:function(results){
        console.log(results); //Dữ liệu trả về
    }
});
```

Dữ liệu truyền lên với kiểu đăng ký là facebook
```php
$.ajax({
    url:'http://<Đường dẫn của bạn đến thư mục>/',
    dataType:'json',
    data:{
        'action':'register',
        'token' : '<mã token được cung cấp>',   // mã token đã lấy ở trên
        'type': 'facebook',
        'name': '<Tên hiển thị>',
        'data':'<Số điện thoại>',
    },
    success:function(results){
        console.log(results); //Dữ liệu trả về
    }
});
```
Thông tin dữ liệu trả về

```php
{
    "success":true,
    "data":{
        "id":"2",
        }
}
```

#Đăng nhập Bằng Phone
```php
$.ajax({
    url:'http://<Đường dẫn của bạn đến thư mục>/',
    dataType:'json',
    data:{
        'action':'login',
        'token' : '<mã token được cung cấp>',   // mã token đã lấy ở trên
        'type': 'phone',
        'username':'<Số điện thoại>',
        'password':'<Mật khẩu>'
    },
    success:function(results){
        console.log(results); //Dữ liệu trả về
    }
});
```
#Đăng nhập bằng Facebook

Sau khi xác nhận tài khoản từ API Facebook truyền lên ID của facebook
```php
$.ajax({
    url:'http://<Đường dẫn của bạn đến thư mục>/',
    dataType:'json',
    data:{
        'action':'login',
        'token' : '<mã token được cung cấp>',   // mã token đã lấy ở trên
        'type': 'facebook',
        'fb_id':'<facebook_id>',
    },
    success:function(results){
        console.log(results); //Dữ liệu trả về
    }
});
```