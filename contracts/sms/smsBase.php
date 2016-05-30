<?php
namespace max_api\contracts\sms;

interface smsBase
{
    /**
     * Khởi tạo các thông tin
     *
     * @return mixed
     *
     */
    public function init();

    public function sendRegister($code,$phone);

    public function sendPassword($password);

    public function send($dataString);

    public function json($data);
}