<?php
namespace max_api\contracts;

use max_api\contracts\sms\smsAbstract;

class smsApi extends smsAbstract
{

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $this->config = config::get('sms');
    }

    /**
     * @param $code
     * @param $phone
     * @return mixed
     */
    public function sendRegister($code, $phone)
    {
        $data = [
            "submission" => [
                "api_key" => $this->config['api_key'],
                "api_secret" => $this->config['api_secret'],
                "sms" => [
                    [
                        "id" => uuid::v4(),
                        "brandname" => $this->config['brand_name'],
                        "text" => sprintf($this->config['text']['register'], $code),
                        "to" => $phone
                    ]
                ]
            ],
        ];

        return $this->send($data);
    }

    /**
     * @param $phone
     * @param $password
     * @return mixed
     */
    public function sendPassword($phone,$password)
    {
        $data = [
            "submission" => [
                "api_key" => $this->config['api_key'],
                "api_secret" => $this->config['api_secret'],
                "sms" => [
                    [
                        "id" => uuid::v4(),
                        "brandname" => $this->config['brand_name'],
                        "text" => sprintf($this->config['text']['re_pass'], $password),
                        "to" => $phone
                    ]
                ]
            ],
        ];

        return $this->send($data);
    }

    /**
     * @param $data
     * @return mixed // {"submission":{"sms":[{"id":"09e0332c-d52b-49cd-bfed-cc26a70f2b56","status":0,"error_message":""}]}}
     */
    public function send($data)
    {
        $dataString = $this->json($data);

        $ch = curl_init($this->config['end_point']);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @param $data
     * @return string
     */
    public function json($data)
    {
        if (is_array($data)) {
            return json_encode($data);
        }

        return $data;
    }
}