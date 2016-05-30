<?php
/**
 * Class là controller chính giúp quản lý các request truyền lên và xử lý
 */
namespace max_api\api;

use max_api\contracts\api;
use max_api\contracts\config;
use max_api\contracts\guid;
use max_api\contracts\sftp;
use max_api\contracts\sms;
use max_api\database\query;
use max_api\model\categories;
use max_api\model\comments;
use max_api\model\contents;
use max_api\model\likes;
use max_api\model\media;
use max_api\model\shares;
use max_api\model\status;
use max_api\model\users;
use RuntimeException;


/**
 * Class maxApi
 * @package max_api\api
 */
class maxApi extends api
{
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
     * TODO: Lấy token hoặc tạo mới token nếu chưa có
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
     * TODO: Tạo mới token hoặc thay đổi token ;
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
     * TODO: Login app
     */
    public function login()
    {
        $users = new users();

        $typeBase = config::get("register");

        $token = $this->_request['token'];

        $username = $this->_request['username'];

        $password = $this->_request['password'];

        $name = $this->_request['name'];

        $data = json_decode($this->_request['data']);

        $type = $this->_request['type'];


        if (!$type || !in_array($type, $typeBase)) {
            $return = array(
                "success" => false,
                "errorCode" => "max08",
            );

            return $this->response($this->json($return), 400);
        }

        $check = $users->checkToken($token);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        }


        switch ($type) {
            case "facebook":
                if (!$data || !$name):
                    $return = array(
                        "success" => false,
                        "errorCode" => "max01",
                    );

                    return $this->response($this->json($return), 400);
                endif;

                //Kiểm tra id facebook đã được đăng ký chưa
                $user_id = $users->getUserIdByFB($data->id);

                if (!$user_id):
                    //đăng ký tài khoản facebook nếu chưa có
                    $user_id = $users->registerFaceBook($name, $data);

                    if (!$user_id):
                        $return = [
                            "success" => false,
                            "errorCode" => "max07",
                            "error_list" => $users->__get("_query")->error_list
                        ];

                        return $this->response($this->json($return), 400);

                    endif;
                endif;

                $profile = $users->getInfoUser($user_id);

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
                $auth = $users->auth($username, $password);

                if (!$auth) :
                    $return = array(
                        "success" => false,
                        "errorCode" => "max05",
                    );

                    return $this->response($this->json($return), 400);
                endif;

                $profile = $users->getInfoUser($auth);

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
     * TODO: Đăng ký
     *
     * @return bool|void
     */
    public function register()
    {
        $users = new users();

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

        $check = $users->checkToken($token);

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

                $register = $users->registerFaceBook($name, $data);

                if (!$register):
                    $return = array(
                        "success" => false,
                        "errorCode" => "max07",
                        "error_list" => $users->__get("_query")->error_list
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

                $register = $users->register($data);

                if (!$register):
                    $return = array(
                        "success" => false,
                        "errorCode" => "max07",
                        "error_list" => $users->__get("_query")->error_list
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
     * TODO: Thay đổi mật khẩu
     */
    public function change_pass()
    {
        $users = new users();
        $token = $this->_request['token'];
        $userId = $this->_request['user_id'];
        $passOld = $this->_request['pass_old'];
        $passNew = $this->_request['pass_new'];

        $check = $users->checkToken($token);

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

        if (!$users->checkPassById($userId, $passOld)):
            $return = array(
                "success" => false,
                "errorCode" => "max10",
            );

            return $this->response($this->json($return), 400);
        endif;

        if (!$users->changePass($userId, $passOld)):
            $return = array(
                "success" => false,
                "errorCode" => "max07",
                "error_list" => $users->__get("_query")->error_list
            );

            return $this->response($this->json($return), 400);
        endif;

        $return = [
            "success" => true,
            "messages" => "Change pass success"
        ];

        return $this->response($this->json($return));
    }

    /**
     * TODO: Sửa thông tin cá nhân
     */
    public function update_profile()
    {
        $users = new users();

        $token = $this->_request['token'];

        $check = $users->checkToken($token);

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

        if (!$users->updateProfile($user_id, $data)):
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
     * TODO: Upload image avatar
     */
    public function upload_avatar()
    {

        $users = new users();

        $user_id = $this->_request['user_id'];

        $token = $this->_request['token'];

        $check = $users->checkToken($token);

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


            $sftp = new sftp();
            if (!$sftp) {
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


            if (!$users->updateAvatar($user_id, $url)) {
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
     * TODO: Lấy danh sách user
     */
    public function get_users()
    {

        $users = new users();

        $token = $this->_request['token'];

        $check = $users->checkToken($token);

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

        $listUser = $users->getUsers($limit, $offset, $order);

        if (!$listUser) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $users->__get("_query")->error_list
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
     * TODO: Lấy dữ liệu menu
     */
    public function get_menus()
    {
        $categories = new categories();

        $token = $this->_request['token'];

        $check = $categories->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $menu = $categories->getMenusMobile();

        if (!$menu) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $categories->__get("_query")->error_list
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
     * TODO: Lấy toàn bộ các danh mục
     */
    public function get_categories()
    {
        $categories = new categories();

        $token = $this->_request['token'];

        $check = $categories->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $category = $categories->getCategories();

        if (!$category) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $categories->__get("_query")->error_list
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
     * TODO: Lấy danh sách bài viết theo danh mục
     */
    public function get_contents_by_category()
    {
        $contents = new contents();

        $token = $this->_request['token'];

        $check = $contents->checkToken($token);

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

        $content = $contents->getContentsByCategory($cat_id, $limit, $offset);

        if (!$content) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $contents->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => $content
        ];

        return $this->response($this->json($return));
    }


    /**
     * TODO: Lấy danh sách bài viết theo user
     */
    public function get_contents_by_user()
    {
        $contents = new contents();

        $token = $this->_request['token'];

        $check = $contents->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $user_id = $this->_request['user_id'];

        $limit = $this->_request['limit'] ? $this->_request['limit'] : 10;

        $offset = $this->_request['offset'] ? $this->_request['offset'] : 0;


        if (!$user_id):
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        endif;

        $content = $contents->getContentsByUser($user_id, $limit, $offset);

        if (!$content) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $contents->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => $content
        ];

        return $this->response($this->json($return));
    }

    /**
     *
     * TODO: Lấy các comment theo bài viết
     */
    public function get_comments_by_content()
    {
        $comments = new comments();

        $token = $this->_request['token'];

        $check = $comments->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }


        $id = $this->_request['id'];

        $limit = $this->_request['limit'] ? $this->_request['limit'] : 10;

        $offset = $this->_request['offset'] ? $this->_request['offset'] : 0;


        if (!$id) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }

        $list_comment_content = $comments->getCommentsByRelateType($id, $limit, $offset, 'content');

        $total = $comments->getCountComment($id, 'content');

        if (!$list_comment_content) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $comments->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        //get comment by comment
        foreach ($list_comment_content as $comment) {
            $comment->comment_reply = $comments->getCommentsByRelateType($comment->id, $limit, $offset, 'comment');
            $comment->total_reply = $comments->getCountComment($comment->id, 'comment');
        }

        $return = [
            "success" => true,
            "data" => [
                "content" => [
                    "total" => $total,
                    "list_comment_content" => $list_comment_content
                ],
            ]
        ];

        return $this->response($this->json($return));
    }

    /**
     * TODO: Comment by comment
     */
    public function get_comments_by_comment()
    {
        $comments = new comments();

        $token = $this->_request['token'];

        $check = $comments->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }


        $id = $this->_request['id'];

        $limit = $this->_request['limit'] ? $this->_request['limit'] : 10;

        $offset = $this->_request['offset'] ? $this->_request['offset'] : 0;

        if (!$id) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }

        $list_comment_reply = $comments->getCommentsByRelateType($id, $limit, $offset, 'comment');

        $total = $comments->getCountComment($id, 'comment');

        if (!$list_comment_reply) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $comments->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => [
                "total" => $total,
                "list_comment_reply" => $list_comment_reply
            ]
        ];

        return $this->response($this->json($return));
    }

    /**
     * TODO: lay comment theo status
     */
    public function get_comments_by_status()
    {
        $comments = new comments();

        $token = $this->_request['token'];

        $check = $comments->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }


        $id = $this->_request['id'];

        $limit = $this->_request['limit'] ? $this->_request['limit'] : 10;

        $offset = $this->_request['offset'] ? $this->_request['offset'] : 0;


        if (!$id) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }

        $list_comment_content = $comments->getCommentsByRelateType($id, $limit, $offset, 'status');

        $total = $comments->getCountComment($id, 'status');

        if (!$list_comment_content) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $comments->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        //get comment by comment
        foreach ($list_comment_content as $comment) {
            $comment->comment_reply = $comments->getCommentsByRelateType($comment->id, $limit, $offset, 'status');
            $comment->total_reply = $comments->getCountComment($comment->id, 'status');
        }

        $return = [
            "success" => true,
            "data" => [
                "content" => [
                    "total" => $total,
                    "list_comment_content" => $list_comment_content
                ],
            ]
        ];

        return $this->response($this->json($return));
    }

    /**
     * TODO: Thêm bình luận mới
     */
    public function set_comment()
    {
        $comments = new comments();

        $token = $this->_request['token'];

        $check = $comments->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $id = $this->_request['id'];

        $user_id = $this->_request['user_id'];

        $relate_type = $this->_request['relate_type'];

        $comment_text = $this->_request['comment'];

        $media_id = $this->_request['media_id'];

        if (!$id || !$user_id || !$comment_text || !in_array($relate_type, config::get('sftp.upload.relate_type'))) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }

        $setComment = $comments->setComment($id, $user_id, $comment_text, $relate_type);


        if (!$setComment) {
            $return = [
                "success" => false,
                "errorCode" => "max07",
                "error_list" => $comments->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        if ($media_id) {
            $media = new media();

            $media->updateParent($media_id, $setComment);
        }

        $return = [
            "success" => true,
            "data" => [
                "id" => $setComment
            ]
        ];

        if ($comments->__get("_query")->error_list) {
            $return["message"] = [
                "update count not success on content",
                "error" => $comments->__get("_query")->error_list
            ];
        }

        return $this->response($this->json($return));
    }

    /**
     * TODO: Sửa lại
     */
    public function update_comment()
    {

        $comments = new comments();

        $token = $this->_request['token'];

        $check = $comments->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $id = $this->_request['id'];

        $comment_text = $this->_request['comment'];

        if (!$id || !$comment_text) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }


        if (!$comments->updateComment($id, $comment_text)) {
            $return = [
                "success" => false,
                "errorCode" => "max07",
                "error_list" => $comments->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "message" => "Update success"
        ];


        return $this->response($this->json($return));
    }

    /**
     * TODO: Xoá comment
     */
    public function delete_comment()
    {
        $comments = new comments();

        $token = $this->_request['token'];

        $check = $comments->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $id = $this->_request['id'];

        if (!$id) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }


        if (!$comments->deleteComment($id)) {
            $return = [
                "success" => false,
                "errorCode" => "max20",
                "error_list" => $comments->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "message" => "Delete success"
        ];

        return $this->response($this->json($return));
    }


    /**
     * TODO: Tao moi status
     */
    public function set_status()
    {
        $status = new status();

        $token = $this->_request['token'];

        $check = $status->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $user_id = $this->_request['user_id'];

        $text = $this->_request['text'];

        $media_id = $this->_request['media_id'];

        if (!$user_id || !$text) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }

        $last_id = $status->setStatus($user_id, $text);

        if (!$last_id) {
            $return = [
                "success" => false,
                "errorCode" => "max07",
                "error_list" => $status->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }


        if ($media_id) {
            if ($media_id) {
                $media = new media();

                $media->updateParent($media_id, $last_id);
            }
        }
        $return = [
            "success" => true,
            "data" => [
                "id" => $last_id
            ]
        ];

        if ($status->__get("_query")->error_list) {
            $return["message"] = [
                "update count not success on content",
                "error" => $status->__get("_query")->error_list
            ];
        }

        return $this->response($this->json($return));

    }

    /**
     * TODO: lay status theo nguoi dung
     */
    public function get_status_by_user()
    {
        $status = new status();

        $token = $this->_request['token'];

        $check = $status->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $user_id = $this->_request['user_id'];

        $limit = $this->_request['limit'] ? $this->_request['limit'] : 10;

        $offset = $this->_request['offset'] ? $this->_request['offset'] : 0;

        if (!$user_id) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }

        $list = $status->getStatusByUser($user_id, $limit, $offset);

        if (!$list) {
            $return = [
                "success" => false,
                "errorCode" => "max19",
                "error_list" => $status->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => $list
        ];

        return $this->response($this->json($return));

    }

    /**
     * TODO: Xoá trạng thái
     */
    public function delete_status()
    {
        $status = new status();

        $token = $this->_request['token'];

        $check = $status->checkToken($token);

        if (!$check) {
            $return = [
                "success" => false,
                "errorCode" => "max04",
            ];

            return $this->response($this->json($return), 400);
        }

        $id = $this->_request['id'];

        if (!$id) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }


        if (!$status->deleteComment($id)) {
            $return = [
                "success" => false,
                "errorCode" => "max20",
                "error_list" => $status->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "message" => "Delete success"
        ];

        return $this->response($this->json($return));
    }

    /**
     * TODO: new share
     */
    public function set_share()
    {
        $shares = new shares();

        $token = $this->_request['token'];

        $check = $shares->checkToken($token);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        }

        $id = $this->_request['id'];

        $relate_type = $this->_request['relate_type'];

        $user_id = $this->_request['user_id'];

        if (!$id || !$relate_type || !$user_id) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }

        $last_id = $shares->setShare($user_id, $id, $relate_type);

        if (!$last_id) {
            $return = [
                "success" => false,
                "errorCode" => "max07",
                "error_list" => $shares->__get("_query")->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => [
                "id" => $last_id
            ]
        ];

        return $this->response($this->json($return));
    }

    /**
     * TODO: Upload image by comment
     */
    public function upload_image()
    {
        $media = new media();

        $token = $this->_request['token'];

        $check = $media->checkToken($token);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        }

        $config = config::get('sftp');

        $user_id = $this->_request['user_id'];

        //   $parent_id = $this->_request['parent_id'];

        $relate_type = $this->_request['relate_type'];


        if (!in_array($relate_type, $config['upload']['relate_type']) || !$user_id) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }


        try {

            if (!isset($_FILES['image']['error']) || is_array($_FILES['image']['error'])) {
                $return = [
                    "success" => false,
                    "errorCode" => "max11"
                ];

                return $this->response($this->json($return), 400);
            }

            // Kiểm tra lỗi
            switch ($_FILES['image']['error']) {
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

            // kiểm tra file síze
            if ($_FILES['image']['size'] > (float)$config['upload']['size']['post']) {
                $return = [
                    "success" => false,
                    "errorCode" => "max12"
                ];

                return $this->response($this->json($return), 400);
            }

            // kiểm tra định dạng ảnh

            if (false === $ext = array_search(
                    $_FILES['image']['type'],
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

            $nameImage = guid::get();

            $upload = sprintf($config['upload']['dir'], $relate_type, $user_id, $nameImage, $ext);
            $url = sprintf($config['upload']['url'], $relate_type, $user_id, $nameImage, $ext);
            $dirUp = sprintf($config['upload']['dir_base'], $relate_type, $user_id);

            if (!$id_media = $media->setMedia($user_id, $url, 0, $relate_type)) {
                $return = [
                    "success" => false,
                    "errorCode" => "max07",
                    "error_list" => $media->__get('_query')->error_list
                ];

                return $this->response($this->json($return), 400);
            }

            $sftp = new sftp();
            if (!$sftp) {
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
            if (!$sftp->put($upload, $_FILES['image']['tmp_name'], NET_SFTP_LOCAL_FILE)) {
                $return = [
                    "success" => false,
                    "errorCode" => "max17"
                ];

                return $this->response($this->json($return), 400);
            };


            $return = [
                "success" => true,
                "data" => [
                    "url" => $url,
                    "id" => $id_media
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
     *
     */
    public function delete_media()
    {
        $media = new media();

        $token = $this->_request['token'];

        $check = $media->checkToken($token);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        }

        $id_media = $this->_request['id'];

        if (!$media->deleteMediaById($id_media)) {
            $return = [
                "success" => false,
                "errorCode" => "max07",
                "error_list" => $media->__get('_query')->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "message" => "Delete success"
        ];

        return $this->response($this->json($return));
    }

    /*
     * TODO: Like share
     */

    /**
     * Like
     *
     * request: token, parent_id, relate_type, user_id
     */
    public function like_comment()
    {
        $likes = new likes();

        $token = $this->_request['token'];

        $check = $likes->checkToken($token);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        }

        $id = $this->_request['id'];

        $user_id = $this->_request['user_id'];

        if (!$id || !$user_id) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }


        $like = $likes->checkLike($id, $user_id, 'comment');

        if ($like) {
            $setLike = $likes->like($id, $user_id, 'comment');
        } else {
            $setLike = $likes->unLike($id, $user_id, 'comment');
        }

        $total = $likes->getCountLike($id, 'comment');

        $comments = new comments();

        $comments->updateCountLike($id, $total);

        if (!$setLike) {
            $return = [
                "success" => false,
                "errorCode" => "max07",
                "error_list" => $likes->__get('_query')->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => [
                "like" => $like,
                "total" => $total
            ]
        ];

        return $this->response($this->json($return));
    }


    /**
     * TODO: Like Content
     */
    public function like_content()
    {
        $likes = new likes();

        $token = $this->_request['token'];

        $check = $likes->checkToken($token);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        }

        $id = $this->_request['id'];

        $user_id = $this->_request['user_id'];

        if (!$id || !$user_id) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }


        $like = $likes->checkLike($id, $user_id, 'content');

        if ($like) {
            $setLike = $likes->like($id, $user_id, 'content');
        } else {
            $setLike = $likes->unLike($id, $user_id, 'content');
        }

        $total = $likes->getCountLike($id, 'content');

        $contents = new contents();

        $contents->updateCountLike($id, $total);

        if (!$setLike) {
            $return = [
                "success" => false,
                "errorCode" => "max07",
                "error_list" => $likes->__get('_query')->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => [
                "like" => $like,
                "total" => $total
            ]
        ];

        return $this->response($this->json($return));
    }

    /**
     * Update like status
     */
    public function like_status()
    {
        $likes = new likes();

        $token = $this->_request['token'];

        $check = $likes->checkToken($token);

        if (!$check) {
            $return = array(
                "success" => false,
                "errorCode" => "max04",
            );

            return $this->response($this->json($return), 400);
        }

        $id = $this->_request['id'];

        $user_id = $this->_request['user_id'];

        if (!$id || !$user_id) {
            $return = array(
                "success" => false,
                "errorCode" => "max01",
            );

            return $this->response($this->json($return), 400);
        }


        $like = $likes->checkLike($id, $user_id, 'status');

        if ($like) {
            $setLike = $likes->like($id, $user_id, 'status');
        } else {
            $setLike = $likes->unLike($id, $user_id, 'status');
        }

        $total = $likes->getCountLike($id, 'status');

        $status = new status();

        $status->updateCountLike($id, $total);

        if (!$setLike) {
            $return = [
                "success" => false,
                "errorCode" => "max07",
                "error_list" => $likes->__get('_query')->error_list
            ];

            return $this->response($this->json($return), 400);
        }

        $return = [
            "success" => true,
            "data" => [
                "like" => $like,
                "total" => $total
            ]
        ];

        return $this->response($this->json($return));
    }


    public function test()
    {
        $code = 902234;

        $sms = new sms();

        $test = $sms->sendRegister($code, "01693493926");

        echo "<pre>";
        print_r($test);
    }
}
