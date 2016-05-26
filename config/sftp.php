<?php

return [
    'sftp' => [
        'host' => 'xxxxx',
        'port' => 'xxxxx',
        'username' => 'xxxxx',
        'password' => 'xxxxxx'
    ],


    'upload' => [
        'type' => [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
        ],
        'size' => [
            'avatar' => '524288', //Dung lượng tối đa 500kb
            'post' => '524288',
        ],

        'type_media' => [
            'content', 'comment', 'status'
        ],

        'dir_base' => '/home/khanhdq/nghienapi/nghienbongda/media/uploads/%s/%s/',

        'dir' => '/home/khanhdq/nghienapi/nghienbongda/media/uploads/%s/%s/%s.%s',

        'url' => 'http://gbd.maxsoft.vn/media/uploads/%s/%s/%s.%s'
    ],
];
