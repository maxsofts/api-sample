#Cách sử dụng

với đường dẫn localhost là : api.max

(với các máy ko thay đổi đường dẫn thì truy cập mặc định đến file max-api.php)


Cách test ở một server bất kỳ
```php
$.ajax({
    url:'http://<Đường dẫn của bạn đến thư mục>/max-api',
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
Trong đó username và password đã được tạo trong database với tài khoản và mật khẩu như trên

Sửa config database trong file database

Bảng mặc định

max_api_user
```php

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `max_api_user`
--

-- --------------------------------------------------------

--
-- Table structure for table `max_api_user`
--

CREATE TABLE `max_api_user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `token` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `max_api_user`
--

INSERT INTO `max_api_user` (`id`, `username`, `password`, `token`) VALUES
(1, 'maxapi', '3md3pk0c4ns0nph4n', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `max_api_user`
--
ALTER TABLE `max_api_user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `max_api_user`
--
ALTER TABLE `max_api_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
```


