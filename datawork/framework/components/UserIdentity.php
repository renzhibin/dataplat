<?php

class UserIdentity extends CUserIdentity
{
    private $_id;
    private $_realname;
    public $errMsg;
    public $changePwd;

    public function authenticate()
    {

        $this->errorMessage = 'success';
        // step1: get the user information
        $user = $this->getUserInfo(trim($this->username), trim($this->password));
        if ($user === false) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
            $this->errorMessage = '获取用户信息错误';

            return !self::ERROR_NONE;
        }

        $uid = $user['id'];
        $this->_id = $uid;
        $this->changePwd = $user['change_pwd'];
        $this->_realname = !empty(trim($user['realname'])) ? trim($user['realname']) : '';
        
        // step2: get the user roles
        if(($role = $this->getUserGroupRoles($uid)) === false) {
            $this->errorCode=self::ERROR_USERNAME_INVALID;
            return !self::ERROR_NONE;
        }

        // step3: setState
        $prefix = $uid;
        Yii::app()->cache->set($prefix . ':username', $this->username);
        Yii::app()->cache->set($prefix . ':name', $user['realname']);
        Yii::app()->cache->set($prefix . ':iphone', $user['iphone']);
        Yii::app()->cache->set($prefix . ':role', $role['group']);

        if($this->changePwd==1){
            Yii::app()->cache->delete($prefix . ':username');
        }
        $this->errorCode = self::ERROR_NONE;
        return !self::ERROR_NONE;
    }

    public function getUserInfo($username, $password)
    {
        $db = Yii::app()->sdb_metric_meta;
        $sql = "select * from t_visual_user where user_name = '{$username}' and password='{$password}'";
        return $db->createCommand($sql)->queryRow();
    }

    // 取得用户所属的角色列表
    public function getUserGroupRoles($uid)
    {
        $db = Yii::app()->sdb_metric_meta;
        $sql = "select * from t_visual_user where id = {$uid}";
        return $db->createCommand($sql)->queryRow();
    }

    public static function checkUserPoint($user_id, $points)
    {
        $db = Yii::app()->sdb_metric_meta;
        $str_report_ids = implode(',', $points);
        $sql = "
            select t_r.report_id
            from t_eel_admin_relation_user t_u
                 left join t_eel_admin_relation_report t_r on t_u.role_id = t_r.role_id
            where t_u.user_id = {$user_id} and t_r.report_id in ({$str_report_ids})
            group by t_r.report_id
        ";
        return $db->createCommand($sql)->queryAll();
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getRealname() {
        return $this->_realname;
    }
}
