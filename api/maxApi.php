<?php
namespace max_api\api;

use max_api\contracts\api;
use max_api\contracts\config;
use max_api\database\database;
use max_api\database\query;

class maxApi extends api
{

    public function __construct()
    {
        parent::__construct();
    }


    /*
     * Public method for access api.
     * This method dynmically call the method based on the query string
     *
     */
    public function processApi()
    {
        $func = strtolower(trim(str_replace("/", "", $_REQUEST['action'])));

        if ((int)method_exists($this, $func) > 0)
            $this->$func();
        else
            $this->response('', 404);                // If the method not exist with in this class, response would be "Page not found".
    }

    /**
     *
     */
    public function get_token()
    {
        $vendor = $this->_request['vendor'];
        $hash = $this->_request['hash'];

        if (!$vendor || !$hash) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }

        $query = new query();

        $token = $query->apiCheck($vendor, $hash);

        if(!$token){
            $return = array(
                "success" => false,
                "errorCode" => "max02",
            );

            return $this->response($this->json($return), 400);
        }

        $return = array(
            "success" => true,
            "_token" => $token
        );

        echo $this->response($this->json($return), 200);
    }

    /*
    *	Encode array into JSON
	*/
    private function json($data)
    {
        if (is_array($data)) {
            return json_encode($data);
        }
        return $data;
    }

}
