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
            'avatar' => '524288', //Dung lượng tối đa 500mb
            'post' => '',
        ],

        'dir_base' => '/home/khanhdq/nghienapi/nghienbongda/media/%s/%s/',

        'dir' => '/home/khanhdq/nghienapi/nghienbongda/media/%s/%s/%s.%s',

        'url' => 'http://gbd.maxsoft.vn/media/%s/%s/%s.%s'
    ],
];
