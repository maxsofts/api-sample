<?php
/**
 * Class là controller chính giúp quản lý các request truyền lên và xử lý
 */
namespace max_api\api;

use max_api\contracts\api;
use max_api\contracts\config;
use max_api\contracts\guid;
use max_api\database\query;


/**
 * Class maxApi
 * @package max_api\api
 */
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
     * Lấy token hoặc tạo mới token nếu chưa có
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

        $check = $query->checkExitsVendor($vendor, $hash);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max03",
            );

            return $this->response($this->json($return), 400);
        }

        $token = $query->apiCheck($vendor, $hash);

        if (!$token) {
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

        return $this->response($this->json($return), 200);
    }

    /**
     * Tạo mới token hoặc thay đổi token ;
     */
    public function reset_token()
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

        $check = $query->checkExitsVendor($vendor, $hash);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max03",
            );

            return $this->response($this->json($return), 400);
        }

        $token = $query->setToken($vendor, $hash);

        if (!$token) {
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

        return $this->response($this->json($return), 200);
    }


    /**
     * Login app
     */
    public function login()
    {
        $query = new query();

        $typeBase = config::get("register");

        $token = $this->_request['token'];

        $username = $this->_request['username'];

        $password = $this->_request['password'];

        $fb_id = $this->_request['fb_id'];

        $type = $this->_request['type'];


        if (!$type || !in_array($type, $typeBase)) {
            $return = array(
                "success" => false,
                "errorCode" => "max08",
            );

            return $this->response($this->json($return), 400);
        }

        $check = $query->checkToken($token);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        }


        switch ($type) {
            case "facebook":
                if (!$fb_id):
                    $return = array(
                        "success" => false,
                        "errorCode" => "max01",
                    );

                    return $this->response($this->json($return), 400);
                endif;

                $user_id = $query->getUserIdByFB($fb_id);

                if (!$user_id):
                    $return = array(
                        "success" => false,
                        "errorCode" => "max09",
                    );

                    return $this->response($this->json($return), 400);
                endif;

                $profile = $query->getInfoUser($user_id);

                if (!$profile) :
                    $return = array(
                        "success" => false,
                        "errorCode" => "max06",
                    );

                    return $this->response($this->json($return), 400);
                endif;

                $return = array(
                    "success" => true,
                    "data" => $profile,
                );

                return $this->response($this->json($return));

                break;

            // login with phone or username
            case "phone":
                if (!$username || !$password):
                    $return = array(
                        "success" => false,
                        "errorCode" => "max01",
                    );

                    return $this->response($this->json($return), 400);
                endif;
                $auth = $query->auth($username, $password);

                if (!$auth) :
                    $return = array(
                        "success" => false,
                        "errorCode" => "max05",
                    );

                    return $this->response($this->json($return), 400);
                endif;

                $profile = $query->getInfoUser($auth);

                if (!$profile) :
                    $return = array(
                        "success" => false,
                        "errorCode" => "max06",
                    );

                    return $this->response($this->json($return), 400);
                endif;

                $return = array(
                    "success" => true,
                    "data" => $profile,
                );

                return $this->response($this->json($return));

                break;

            default:
                break;
        }

    }


    /**
     * Đăng ký
     *
     * @return bool|void
     */
    public function register()
    {
        $query = new query();

        $typeBase = config::get("register");

        $token = $this->_request['token'];

        $name = $this->_request['name'];

        $username = $this->_request['username'];

        $password = $this->_request['password'];

        $data = json_decode($this->_request['data']);

        $type = $this->_request['type'];

        /*
         * Kiểm tra dạng có nằm trong dạng cho phép
         */

        if (!$type || !in_array($type, $typeBase)) {
            $return = array(
                "success" => false,
                "errorCode" => "max08",
            );

            return $this->response($this->json($return), 400);
        }

        $check = $query->checkToken($token);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        }
        /*
         * Type facebook
         */
        switch ($type) {
            case "facebook":

                if (!$data || !$name):
                    $return = array(
                        "success" => false,
                        "errorCode" => "max01",
                    );

                    return $this->response($this->json($return), 400);
                endif;

                $register = $query->registerFaceBook($name, $data);

                if (!$register):
                    $return = array(
                        "success" => false,
                        "errorCode" => "max07",
                        "error_list" => $query->__get("_query")->error_list
                    );

                    return $this->response($this->json($return), 400);

                endif;

                $return = array(
                    "success" => true,
                    "data" => array(
                        "id" => $register
                    ),
                );

                return $this->response($this->json($return));

                break;
            /*
             * Type Phone
             */
            case "phone":
                if (!$name || !$username || !$password) :
                    $return = array(
                        "success" => false,
                        "errorCode" => "max01",
                    );

                    return $this->response($this->json($return), 400);
                endif;

                $data = [
                    "first_name" => $name,
                    "username" => $username,
                    "password" => $password
                ];

                $register = $query->register($data);

                if (!$register):
                    $return = array(
                        "success" => false,
                        "errorCode" => "max07",
                        "error_list" => $query->__get("_query")->error_list
                    );

                    return $this->response($this->json($return), 400);

                endif;

                $return = array(
                    "success" => true,
                    "data" => array(
                        "id" => $register
                    ),
                );

                return $this->response($this->json($return));
                break;

            default:
                return false;
                break;
        }

    }

    /**
     * Thay đổi mật khẩu
     */
    public function change_pass()
    {
        $query = new query();
        $token = $this->_request['token'];
        $userId = $this->_request['user_id'];
        $passOld = $this->_request['pass_old'];
        $passNew = $this->_request['pass_new'];

        $check = $query->checkToken($token);

        if (!$check):
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        endif; //end check

        if (!$passNew || !$passOld || !$userId):
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        endif; //end passnew pass old

        if (!$query->checkPassById($userId, $passOld)):
            $return = array(
                "success" => false,
                "errorCode" => "max10",
            );

            return $this->response($this->json($return), 400);
        endif;

        if (!$query->changePass($userId, $passOld)):
            $return = array(
                "success" => false,
                "errorCode" => "max07",
                "error_list" => $query->__get("_query")->error_list
            );

            return $this->response($this->json($return), 400);
        endif;

        $return = [
            "success" => true,
            "messages" => "Change pass success"
        ];

        /** @var TYPE_NAME $this */
        return $this->response($this->json($return));
    }

    /**
     * Sửa thông tin cá nhân
     */
    public function update_profile()
    {
        $query = new query();

        $token = $this->_request['token'];

        $check = $query->checkToken($token);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        }

        /*
         * Get Data Form
         */
        $user_id = $this->_request['user_id'];

        $name = $this->_request['name'];

        $email = $this->_request['email'];

        $phone = $this->_request['phone'];


        if (!$user_id || !$name || !$email || !$phone):
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        endif;

        $data = [
            "first_name" => $name,
            "email" => $email,
            "phone" => $phone,
        ];

        if (!$query->updateProfile($user_id, $data)):
            $return = array(
                "success" => false,
                "errorCode" => "max07",
            );

            return $this->response($this->json($return), 400);
        endif;

        $return = array(
            "success" => true,

            "data" => [
                "id" => $user_id,
                "message" => "Update success"
            ],
        );

        return $this->response($this->json($return), 400);
    }

    /**
     * Upload image avatar
     */
    public function upload_avatar()
    {

        $query = new query();

        $user_id = $this->_request['user_id'];

        $token = $this->_request['token'];

        $check = $query->checkToken($token);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        }


        $config = config::get('sftp');

        try {

            if (!isset($_FILES['avatar']['error']) || is_array($_FILES['avatar']['error'])) {
                $return = [
                    "success" => false,
                    "errorCode" => "max11"
                ];

                return $this->response($this->json($return), 400);
            }

            // Check $_FILES['upfile']['error'] value.
            switch ($_FILES['avatar']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $return = [
                        "success" => false,
                        "errorCode" => "max11"
                    ];

                    return $this->response($this->json($return), 400);
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $return = [
                        "success" => false,
                        "errorCode" => "max12"
                    ];

                    return $this->response($this->json($return), 400);
                default:
                    $return = [
                        "success" => false,
                        "errorCode" => "max13"
                    ];

                    return $this->response($this->json($return), 400);
            }

            // You should also check filesize here.
            if ($_FILES['avatar']['size'] > (float)$config['upload']['size']['avatar']) {
                $return = [
                    "success" => false,
                    "errorCode" => "max12"
                ];

                return $this->response($this->json($return), 400);
            }

            // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
            // Check MIME Type by yourself.

            if (false === $ext = array_search(
                    $_FILES['avatar']['type'],
                    $config['upload']['type'],
                    true
                )
            ) {
                $return = [
                    "success" => false,
                    "errorCode" => "max14"
                ];

                return $this->response($this->json($return), 400);
            }

            // You should name it uniquely.
            // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.

            $nameImage = guid::get();

            $upload = sprintf($config['upload']['dir'], "avatar", $user_id, $nameImage, $ext);
            $url = sprintf($config['upload']['url'], "avatar", $user_id, $nameImage, $ext);
            $dirUp = sprintf($config['upload']['dir_base'], "avatar", $user_id);


            $sftp = new \Net_SFTP($config['sftp']['host']);
            if (!$sftp->login($config['sftp']['username'], $config['sftp']['password'])) {
                $return = [
                    "success" => false,
                    "errorCode" => "max16"
                ];

                return $this->response($this->json($return), 400);
            }

            if (!$sftp->is_dir($dirUp)) {
                if (!$sftp->mkdir($dirUp)) {
                    $return = [
                        "success" => false,
                        "errorCode" => "max15"
                    ];

                    return $this->response($this->json($return), 400);
                }
            }
            // puts an x-byte file named filename.remote on the SFTP server,
            if (!$sftp->put($upload, $_FILES['avatar']['tmp_name'], NET_SFTP_LOCAL_FILE)) {
                $return = [
                    "success" => false,
                    "errorCode" => "max17"
                ];

                return $this->response($this->json($return), 400);
            };


            if (!$query->updateAvatar($user_id, $url)) {
                $return = [
                    "success" => false,
                    "errorCode" => "max18",
                    "url" => $url
                ];

                return $this->response($this->json($return), 400);
            }

            $return = [
                "success" => true,
                "data" => [
                    "url" => $url
                ]
            ];

            return $this->response($this->json($return));


        } catch (RuntimeException $e) {
            $return = [
                "success" => false,
                "error" => "max13"
            ];

            return $this->response($this->json($return), 400);

        }


    }


    /**
     * Lấy danh sách user
     */
    public function get_users()
    {

        $query = new query();

        $token = $this->_request['token'];

        $check = $query->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $limit = $this->_request['limit'] ? $this->_request['limit'] : 20;

        $offset = $this->_request['offset'] ? $this->_request['offset'] : 0;

        $order = $this->_request['order'] ? $this->_request['order'] : "point DESC";

        $listUser = $query->getUsers($limit, $offset, $order);

        if (!$listUser) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $query->__get("_query")->error_list
            ];
            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => $listUser
        ];

        return $this->response($this->json($return), 400);
    }

    /**
     * Lấy dữ liệu menu
     */
    public function get_menus()
    {
        $query = new query();

        $token = $this->_request['token'];

        $check = $query->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $menu = $query->getMenusMobile();

        if (!$menu) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $query->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => $menu
        ];

        return $this->response($this->json($return), 400);
    }


    /**
     * Lấy toàn bộ các danh mục
     */
    public function get_categories()
    {
        $query = new query();

        $token = $this->_request['token'];

        $check = $query->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $category = $query->getCategories();

        if (!$category) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $query->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => $category
        ];

        return $this->response($this->json($return));
    }


    /**
     * Lấy danh sách bài viết
     */
    public function get_contents_by_category(){
        $query = new query();

        $token = $this->_request['token'];

        $check = $query->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $cat_id = $this->_request['cat_id'];

        $limit = $this->_request['limit'] ? $this->_request['limit'] : 10;

        $offset = $this->_request['offset'] ? $this->_request['offset'] : 0;


        if (!$cat_id):
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        endif;

        $content = $query->getContentsByCategory($cat_id,$limit,$offset);

        if (!$content) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $query->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => $content
        ];

        return $this->response($this->json($return));
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
