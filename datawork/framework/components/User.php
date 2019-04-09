<?php

class User  {

    const ADMIN = 1;
    const PRODUCER = 2;//开发者
    const CORE = 3;//核心
    const AUDIT = 4;
    const SUPER = 5;//超级管理员

    public $group;

    function init()
    {
        //下面的Url不用登录检测，直接通过
        $whiteUrl = new AuthManager();
        foreach($whiteUrl->whiteUrl['notAuth'] as $url){
            if(strpos(strtolower($_SERVER['REQUEST_URI']),$url) ===0)
                return true;
        }

        $useInnerLogin = env('INNER_LOGIN_INTERFACE', false);
        if($useInnerLogin) {
            $user_cookie = $_COOKIE['username_token'];
            if(empty($user_cookie)){
                return;
            }
            $tmp = explode('#', $user_cookie);
            $user_id = $tmp[1];
            $username = Yii::app()->cache->get($user_id.':username');
            //判断缓存是否有该用户，屏蔽删除用户
            if($username){
                $this->username = $tmp[0];
                $this->id = $tmp[1];
                $this->name = $tmp[2];
                $this->role = Yii::app()->cache->get($tmp[1].':role');
                $this->getUserGroup($tmp[0]);
                return Yii::app()->cache->get($tmp[1].':name');
            }
        } else {
            //从session中获取数据(禁用cookie后直接退出到登陆页面)
            $jsonUser = Yii::app()->session['data_analysis_login_user'];
            $userInfo = json_decode($jsonUser, true);
            if(empty($userInfo)){
                return '';
            }

            //判断缓存是否有该用户，屏蔽删除用户
            if($userInfo['id']){
                $this->username = $userInfo['user_name'];
                $this->id       = $userInfo['id'];
                $this->name     = $userInfo['realname'];
                $this->getUserGroup($userInfo['user_name']);
                return $userInfo['realname'];
            }
        }
    }

    public function getUsername()
    {
        $prefix = Yii::app()->user->id;
        return Yii::app()->cache->get($prefix.':username');
    }
    
    public function getName()
    {
        $prefix = Yii::app()->user->id;
        return Yii::app()->cache->get($prefix.':name');
    }
    
    public function getRole()
    {
        $prefix = Yii::app()->user->id;
        return Yii::app()->cache->get($prefix.':role');
    }

    public function isAdmin()
    {
        $res = $this->selectUserGroup('', true);
        if (in_array(self::ADMIN, $res)) {
            return true;
        }
        return false;

    }

    public function isAudit()
    {
        $res = $this->selectUserGroup('', true);
        if (in_array(self::AUDIT, $res)) {
            return true;
        }
        return false;

    }

    public function isProducer()
    {
        /*
         * 如果group为2 则为分析师
         */
        $res = $this->selectUserGroup('', true);
        if (in_array(self::PRODUCER, $res) || $this->isSuper()) {
            return true;
        }
        return false;
    }

    public function isCore()
    {
        /*
         * 如果group为3 则为core
         */
        $res = $this->selectUserGroup('', true);
        if (in_array(self::CORE, $res)) {
            return true;
        }
        return false;
    }

    public function isSuper()
    {
        /*
         * 如果group为3 则为core
         */
        $res = $this->selectUserGroup('', true);
        if (in_array(self::SUPER, $res)) {
            return true;
        }
        return false;
    }

    public function selectUserGroup($name = '', $retuArr = false)
    {
        $result = $this->group;

        if ($retuArr == true) {
            return explode(',', $result);
        }
        return $result;
    }

    public function getUserGroup($name = '')
    {
        if (empty($name)) {
            $this->group = '';
        } else {
            $result = Yii::app()->sdb_metric_meta->createCommand()
                ->select('group')->from('t_visual_user')
                ->where('user_name=:user_name', array(':user_name' => $name))
                ->queryRow();

            $this->group = $result['group'];
        }
    }
}
