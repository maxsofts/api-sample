<?php

namespace max_api\contracts;

class sftp extends \Net_SFTP
{
    protected $config;


    public function __construct()
    {
        $config = config::get('sftp.sftp');

        $this->Net_SFTP($config['host'], $config['port']);

        if (!$this->login($config['username'], $config['password'])) {
            return false;
        };

    }
}