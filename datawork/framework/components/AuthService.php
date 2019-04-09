<?php

class AuthService
{
    //权限管理示例
    private static $_instance = null;

    //需要缓存的数据
    var $userId   = null; //当前登录用户id
    var $userInfo = null; //当前登录用户信息
    var $menu     = null; //当前用户可看到的菜单

    //获取单例对象
    public static function getInstance()
    {
        if (self::$_instance != null) return self::$_instance;
        self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * 是否定登录
     * @return bool
     */
    public static function isLogin()
    {
        //本地session检验
        $userInfo = self::getLoginUser();
        //SSO-Token检验
        $checkTokenResult = self::checkToken();

        if ($userInfo != false or $checkTokenResult) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检验Token
     * @return bool
     */
    public static function checkToken()
    {
        $jsonUser = Yii::app()->session['data_analysis_login_user'];
        if (empty($jsonUser)) return false;
        $userInfo  = json_decode($jsonUser, true);
        $token  = $userInfo['token'];

        $checkToken = SSO_CHECK_TOKEN . '?token=' . $token;
        $content = json_decode(file_get_contents($checkToken), true);
        Yii::log($content['code']);
        switch ($content['code']) {
            //成功登陆
            case '0':
                return true;
                break;
            //其他状况
            default:
                return false;
        }
    }

    /**
     * 获取当前登录用户信息
     * @return bool|mixed|null
     */
    public static function getLoginUser()
    {
        //内存中有的话先从内存中获取
        $instance = self::getInstance();
        if (!empty($instance->userInfo)) {
            $userInfo  = $instance->userInfo;
            $adminInfo = AuthManager::getAdminInfo($userInfo['mobile']);

            $newUserInfo = [
                'user_name' => $userInfo['email'],
                'realname'  => $userInfo['name'],
                'iphone'    => $userInfo['mobile'],
            ];

            if (empty($adminInfo)) {
                AuthManager::addAdminInfo($newUserInfo);
            }

            return $userInfo;
        }

        $jsonUser = Yii::app()->session['data_analysis_login_user'];

        if (empty($jsonUser)) return false;

        $userInfo = json_decode($jsonUser, true);
        if (empty($userInfo)) return false;

        //设置成员数据
        $instance->userId   = $userInfo['id'];
        $instance->userInfo = $userInfo;

        //返回用户信息
        return $userInfo;
    }


    /**
     *  本地session退出登录
     */
    public static function logout()
    {
        self::$_instance = null;

        Yii::app()->session->clear();
    }

    /**
     * 请求SSO退出
     */
    public static function SsoLogout()
    {
        $url = SSO_LOGOUT;
        $tarurl = SSO_LOGIN_URL . '?app_key=' . PROJECT_KEY . '&tarurl=' . 'http://' . $_SERVER['HTTP_HOST'];//. $_SERVER['REQUEST_URI'];

        $jsonUser = Yii::app()->session['data_analysis_login_user'];
        if (empty($jsonUser)) return false;
        $userInfo  = json_decode($jsonUser, true);
        $token  = $userInfo['token'];
        if (empty($token)) return false;

        $url .= '?' . http_build_query(['tarurl'=>$tarurl,'app_key'=>PROJECT_KEY,'token'=>$token]);
        if (empty($url)) return false;
        return $url;
    }

    /**
     * 进行登录
     * @param $loginUserInfo
     * @return bool
     */
    public static function login($loginUserInfo)
    {
        if (!isset($loginUserInfo['id']) || !isset($loginUserInfo['user_name'])) return false;
        //$prefix  = $loginUserInfo['id'];
        Yii::app()->session['sso_user_info'] = $loginUserInfo;
        //用户信息写入内存
        $instance           = self::getInstance();
        $instance->userId   = $loginUserInfo['account'];
        $instance->userInfo = $loginUserInfo;
        $jsonUser           = json_encode($loginUserInfo);
        //设置session
        Yii::app()->session['data_analysis_login_user'] = $jsonUser;

        $sessionId = Yii::app()->session->sessionID;
        $expires_time = time() + 60 * 60 * 24 * 7; //过期时间24小时
        setcookie('visual', $sessionId, $expires_time, '/');

        return true;
    }
}