<?php

return [
    'sftp' => [
        'host' => 'xxxxxxxxx',
        'port' => 'xxxxxxxxx',
        'username' => 'xxxxxxxxx',
        'password' => 'xxxxxxxxx'
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

        'relate_type' => [
            'content', 'comment', 'status'
        ],

        'media_type' => [
            'image','video','stream'
        ],

        'dir_base' => '/home/khanhdq/nghienapi/nghienbongda/media/uploads/%s/%s/',

        'dir' => '/home/khanhdq/nghienapi/nghienbongda/media/uploads/%s/%s/%s.%s',

        'url' => 'http://gbd.maxsoft.vn/media/uploads/%s/%s/%s.%s'
    ],
];
