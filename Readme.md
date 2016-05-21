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

1. Tạo và lấy token
```php
$.ajax({
    url:'http://<Đường dẫn của bạn đến thư mục>/',
    dataType:'json',
    data:{
        'action':'get_token',
        'vendor':'<vendor được cung cấp>',
        'hash':'<hash được cung cấp>'
    },
    success:function(results){
        console.log(results); //Dữ liệu trả về
    }
});
```
hoặc truy cập trực tiếp vào đường dẫn
```php http://<Đường dẫn của bạn đến thư mục>/?action=get_token&vendor=vendorcungcap&hash=hashcungcap```

Dữ liệu trả về
```php
{"success":true,"_token":"matoken"}
```

Lưu trữ mã token để thay bằng việc auth trực tiếp qua hệ thống