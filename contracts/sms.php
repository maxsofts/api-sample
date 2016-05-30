<?php
namespace max_api\contracts;

use max_api\contracts\sms\smsAbstract;

class sms extends smsAbstract
{

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $this->config = config::get('sms');
    }

    public function sendRegister($code, $phone)
    {
        $data = [
            "submission" => [
                "api_key" => $this->config['api_key'],
                "api_secret" => $this->config['api_secret'],
                "sms" => [
                    [
                        "id" => $this->config['UUID'],
                        "brandname" => $this->config['brandname'],
                        "text" => sprintf($this->config['registed'], $code),
                        "to" => $phone
                    ]
                ]
            ],
        ];

        return $this->send($data);
    }

    /**
     * @param $password
     */
    public function sendPassword($password)
    {

    }

    /**
     * @param $data
     * @return mixed
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