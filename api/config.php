<?php
namespace max_api\api;

use max_api\contracts\api;
use max_api\database\database;

class config extends api
{
    private $_db;

    public function __construct()
    {
        $this->_db = new database();
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
        $username = $this->_request['username'];
        $password = $this->_request['password'];


        if (!$username || !$password) {
            $return = array(
                "success" => false,
                "message" => "Invalid Username or Password"
            );


            return $this->response($this->json($return), 400);
        }

        $auth = $this->_db->auth($username, $password);

        if (!$auth->field_count) {
            $return = array(
                "success" => false,
                "message" => "Invalid Username or Password"
            );

            return $this->response($this->json($return), 400);
        }

        $authObject = $auth->fetch_object();

        $token = $this->_db->set_token($authObject->id);


        if (!$token) {
            $return = array(
                "success" => false,
                "message" => "set token false please contact admin"
            );

            return $this->response($this->json($return), 501);
        }


        $results = array(
            "success" => true,
            "token" => $token
        );

        echo $this->response($this->json($results), 200);
    }

    /*
    *	Encode array into JSON
	*/
    private function json($data)
    {
        if (is_array($data)) {
            return json_encode($data);
        }
    }
}
