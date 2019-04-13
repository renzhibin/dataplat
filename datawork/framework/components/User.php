<?php

class User  {

    function init()
    {
        //下面的Url不用登录检测，直接通过
        $whiteUrl = array(
            '/site/index',
            '/site/logout',
            '/site/login',
            '/site/PwdPage',
            '/site/ResetPwd',
            '/service/getmenu',
            '/visual/getcontrast',
            '/visual/gettable',
            '/visual/getdata',
            '/visual/getchart',
            '/chart/showchart',
            '/timemail/urllibmail',
            '/realtime/fetchygorder',
            '/realtime/fetchorder',
            '/heatmap/showmap',
            '/heatmap/MarketLayout',
            '/heatmap/GYzoneRate',
            '/salesvisit',
            '/wap/speed',
            // '/tool/fileup',
            '/tool/getFileUp',
            '/tool/CreateHiveData',
            '/tool/BehaviorLog'
        );
        foreach($whiteUrl as $url){
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
    
}
