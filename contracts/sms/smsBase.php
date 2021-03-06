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

    public function sendPassword($phone,$password);

    public function sendNewPhone($phone, $password);

    public function send($dataString);

    public function json($data);
}