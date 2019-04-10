<?php

class AuthManager extends Manager
{
    //核心报表权限
    public $coreReportWhiteList = [

        '1260' => [//数据外发工具

        ]
    ];

    //普通白名单
    public $whiteUrl = array(
        'notAuth' => array(
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
        ),
        'userMenu' => array(
            '/fetch/addDemand',
            '/fetch/download',
            '/fetch/demand',
            '/fetch/uploadFile',
            '/fetch/saveDemand',
            '/fetch/downloadFile',
            '/tool/fileup',
            '/apphomefocus',
            //白名单
            '/visual',
            '/addition',
            '/chart',
            '/report/addcollect',
            '/report/deletecollect',
            '/tool/GetDataReport',
            '/report/savereportcustom',
            '/gps',
            '/heatmap',
            '/realtime',
            '/wap',
            //otherUrl
            '/project/comments',
            '/project/getall',
            '/project/savecomments',
            '/project/getcomments',
        ),
    );

    function __construct()
    {
        $this->menuTable = 't_visual_menu';
        $this->reportTable = 't_visual_table';
        $this->userTable = 't_visual_user';
        $this->favoriteTable = 't_visual_favorites';
        $this->groupTable = 't_visual_group';
        $this->behaviorTable = 't_visual_behavior';
        $this->username = Yii::app()->user->username;
        $this->adminGroupId = 1;
        $this->producerGroupId = 2;
        $this->coreGroupId = 3;
        $this->audit = 4;
        $this->superGroupId = 5;
        $this->confSuperProject = [
            'ares'
        ];
    }

    function checkAuthFromMenu($menu, $userName)
    {
        $menuMan = new MenuManager();
        $res = false;
        if (is_numeric($menu)) {
            if (isset($this->coreReportWhiteList[$menu]) && in_array($userName, $this->coreReportWhiteList[$menu])) {
                //如果在白名单不用验证数据库内的报表权限
                return true;
            }
            if (isset($this->coreReportWhiteList[$menu]) && !in_array($userName, $this->coreReportWhiteList[$menu])) {
                //如果菜单在白名单,用户不在白名单，没有权限
                return false;
            }
            if (Yii::app()->user->isSuper())
                return true;

            if (Yii::app()->user->isProducer()) {
                return true;
            }

            if (Yii::app()->user->isCore()) {
                return true;
            }

            $res = $this->checkReportPoint($menu);
            /*if(!(!empty($_REQUEST['id']) && $_REQUEST['id']<508)){
                return false;
            }*/
        } elseif (is_array($menu)) {

        } else {
            if (Yii::app()->user->isSuper()) {
                return true;
            }

            if ((Yii::app()->user->isProducer() || Yii::app()->user->isAdmin()) && !in_array(strtolower($menu), $menuMan->superList)) {
                return true;
            }

            //白名单url,不需要验证用户
            foreach($this->whiteUrl['notAuth'] as $url){
                if(strpos(strtolower($_SERVER['REQUEST_URI']),strtolower($url)) ===0)
                    return true;
            }
            //需要验证用户
            foreach($this->whiteUrl['userMenu'] as $url){
                if(strpos(strtolower($_SERVER['REQUEST_URI']),strtolower($url)) ===0)
                    return true;
            }

            //白名单配置
            $objTool=new ToolManager();
            if($objTool->checkRefer()){
                return true;
            }
        }

        return $res;
    }


    function getSuperName()
    {
        $res = $this->selectSuper();

        $superName = [];
        foreach ($res as $row) {
            $superName[] = strstr($row['user_name'], '@', true);
        }
        return $superName;
    }

    function getSuperProject()
    {
        return $this->confSuperProject;
    }


    function __createNewStr($orgStr, $addStr, $deleteFlag = false)
    {
        $tmpArr = explode(',', $orgStr);


        if ($deleteFlag == false) {
            if (in_array($addStr, $tmpArr)) {
                return false;
            }
            $tmpArr[] = $addStr;
            return implode(',', $tmpArr);
        } else {

            if (!in_array($addStr, $tmpArr)) {
                return false;
            }
            foreach ($tmpArr as $k => $v) {
                if ($v == $addStr) {
                    unset($tmpArr[$k]);
                    break;
                }
            }


            return implode(',', $tmpArr);
        }
    }

    function syncPoint($id)
    {
        $objReport = new ReportManager();
        $conf = $objReport->getReoport($id);
        $name = $conf['id'] . '_' . $conf['cn_name'];
        $res = $this->addAuthPoint($name, $conf['id']);
        //延迟提交5秒
        sleep(5);
        if ($res['status'] != 0) {
            return $res;
        }

        $res = $this->submitAuthPoint($name);

        //print_r($res);
        if ($res['code'] == 1) {
            return array('status' => 0, 'msg' => '成功');
        } else {
            return array('status' => 1, 'msg' => $res['code']);
        }
    }

    function submitAuthPoint($name)
    {

        $curl = yii::app()->curl;
        $url = DEVELOP_API . "/OutApi/AutoVerify";
        //$url = "localhost:8082/OutApi/AutoVerify";
        $vars = array(
            "business" => 'data平台',
            "funname" => $name,
        );
        $output = $curl->post($url, $vars);
        $res = $output['body'];
        if (empty($res)) {
            return array();
        }
        return json_decode($res, true);

    }

    function addAuthPoint($name, $id)
    {
        $curl = yii::app()->curl;
        $url = DEVELOP_API . "/OutApi/AutoItem";
        $vars = array(
            "business" => 'data平台',
            "funname" => $name,
            "description" => $name,
            "url" => '/visual/index/' . $id,
            "sign" => 'data/data'
        );
        $output = $curl->post($url, $vars);
        $res = $output['body'];
        if (empty($res)) {
            return array('status' => 1, 'msg' => '接口调用失败');
        }
        $retu = json_decode($res, true);

        if ($retu['code'] == 1) {
            return array('status' => 0, 'msg' => '成功');
        } else {
            return array('status' => 2, 'msg' => $retu['msg']);
        }


    }

    /**
     * 批量查询审核状态接口
     */
    function checkStatus($id)
    {
        $curl = yii::app()->curl;
        $url = DEVELOP_API . "/OutApi/checkstatus";
        $objReport = new ReportManager();
        $reprotInfo = $objReport->getReoport($id);
        $redisKey = $reprotInfo['id'] . "_" . $reprotInfo['cn_name'];
        $data = array();
        $userArray = array($redisKey);
        $vars = array(
            "business" => 'data平台',
            "funname" => $userArray
        );
        $output = $curl->post($url, http_build_query($vars));
        $retu = array();
        $res = $output['body'];
        $arrRes = json_decode($res, true);
        return $arrRes[0]['status'];
    }


    /**
     * 报表名称更改接口
     */
    function checkName($name, $newName)
    {
        if ($name == $newName) {
            return;
        }
        $curl = yii::app()->curl;
        $url = DEVELOP_API . "/OutApi/ChangeName?business=data平台&funname=" . $name . "&newName=" . $newName;
        $output = $curl->get($url);
        $retu = array();
        $res = $output['body'];
        if (empty($res)) {
            return $retu;
        }
        $arrRes = json_decode($res, true);
        return $arrRes;
    }

    /**
     * 下线权限点
     */

    function offlinePoint($point)
    {
        $curl = yii::app()->curl;
        if (empty(Yii::app()->user->username)) {
            $username = 'admin';
        } else {
            $username = Yii::app()->user->username;
        }
        $url = DEVELOP_API . '/outApi/Delfunction?business=data平台&user=' . $username . '&funname=' . $point;
        $output = $curl->get($url);
        $res = json_decode($output['body'], true);
        if ($res['errno'] == 1) {
            return true;
        }
        return false;
    }

    /**
     * 批量查询权限接口
     * @param $points
     * @return array
     */
    function checkPoint($points)
    {
        $uid = Yii::app()->user->id;
        if ($uid == null) {
            $uid = $_POST['user_id'];
        }
        //没有uid,不检查权限
        if ($uid == null) {
            return array();
        }
        $retu = array();
        $res = UserIdentity::checkUserPoint($uid, $points);
        if (empty($res)) {
            return $retu;
        }

        foreach ($res as $allow_point) {
            $retu[] = $allow_point['report_id'];
        }
        return $retu;
    }

    function checkReportPoint($id)
    {
        $objReport = new ReportManager();
        $conf = $objReport->getReoport($id);
        //$tmp = $conf['id'] . '_' . $conf['cn_name'];
        //todo,检查报表权限
        $res = $this->checkPoint(array($conf['id']));
        if ($res[0] == $conf['id']) {
            return true;
        }
        return false;
    }

    function checkAuthPoint($authId2name)
    {
        if (empty($authId2name)) {
            $authId2name = array();
        }
        $points = array_keys($authId2name);
        $res = $this->checkPoint($points);
        $retu = array();
        foreach ($res as $tmp) {
            $tmp_id = intval($tmp);
            $retu[$tmp_id] = array('cn_name' => $authId2name[$tmp]['cn_name']);
        }

        return $retu;
    }

    function getAllUsername()
    {
        $sql = 'select username from t_eel_admin_user';
        $result = Yii::app()->sdb_eel->createCommand($sql)->queryAll();
        return $result;
    }

    function getGroup()
    {
        $sql = "select id,name from    $this->groupTable";
        $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $result;
    }

    function getUsernamebyGroupId($groupid)
    {
        $sql = "select user_name  from    $this->userTable   where  `group` rlike '(^|,)$groupid($|,)'";
        $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $result;
    }

    function getUsernamebyReport($table_id)
    {

        $sql = "select user_name  from    $this->favoriteTable   where  table_id rlike '(^|,)$table_id($|,)'";
        $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $result;
    }

    function getChineseUserNamebyReport($table_id)
    {
        $sql = "select chinese_name  from    $this->favoriteTable   where  table_id rlike '(^|,)$table_id($|,)'";
        $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $result;
    }

    function deleteFavorites($table_id)
    {
        if (empty($table_id)) {
            return false;
        }
        $username = Yii::app()->user->username ?: $_REQUEST['user_name'];
        $result = Yii::app()->sdb_metric_meta->createCommand()
            ->select('table_id')->from($this->favoriteTable)
            ->where('user_name=:user_name', array(':user_name' => $username))
            ->queryRow();
        if ($result == false) {
            return false;
        } else {
            $tableStr = $this->__createNewStr($result['table_id'], $table_id, true);
            if ($tableStr === false) {
                return false;
            }
            $sql = "update  $this->favoriteTable set `table_id`=:table_id where  `user_name`=:username";
            $parament = array(':username' => $username, ':table_id' => $tableStr);
            $res = Yii::app()->db_metric_meta->createCommand($sql)->execute($parament);


        }


        return true;


    }


    function addFavorites($table_id)
    {
        $username    = Yii::app()->user->username ?: $_REQUEST['user_name'];
        $chineseName = Yii::app()->user->name ?: $_REQUEST['true_name'];
        $result = Yii::app()->db_metric_meta->createCommand()
            ->select('table_id')->from($this->favoriteTable)
            ->where('user_name=:user_name', array(':user_name' => $username))
            ->queryRow();
        if ($result == false) {
            $sql = "insert into  $this->favoriteTable (`user_name`,`table_id`,`chinese_name`) values (:username,:table_id,:chineseName)";
            $parament = array(':username' => $username, ':table_id' => $table_id, ':chineseName' => $chineseName);
            $res = Yii::app()->db_metric_meta->createCommand($sql)->execute($parament);

        } else {
            if (empty($result['table_id'])) {
                $tableidStr = $table_id;
            } else {
                $tableidStr = $this->__createNewStr($result['table_id'], $table_id);
            }

            if ($tableidStr == false) {
                return true;
            } else {
                $sql = "update  $this->favoriteTable set `table_id`=:table_id where  `user_name`=:username";
                $parament = array(':username' => $username, ':table_id' => $tableidStr);
                $res = Yii::app()->db_metric_meta->createCommand($sql)->execute($parament);
            }


        }


        return true;


    }

    function selectGroup($groupId = '')
    {
        $sql = 'select * from ' . $this->groupTable;
        if (empty($id)) {
            $sql .= ' where id=' . $groupId;

        }
        $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $result;

    }

    function selectSuper()
    {
        $sql    = "select * from {$this->userTable} where `group` = {$this->superGroupId}";
        $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $result;
    }

    function updateGroup($groupId, $nameArr, $deletename = '')
    {

        if (!is_array($nameArr)) {
            return false;
        }

        foreach ($nameArr as $name) {
            $result = $this->selectUserGroup($name);

            if ($result == false) {
                $sql = "insert into  " . $this->userTable . "(`user_name`,`group`) values(" . '\'' . $name . '\',' . '\'' . $groupId . '\'' . ") ";
                $res = Yii::app()->db_metric_meta->createCommand($sql)->execute();

            } else {
                $groupStr = $this->__createNewStr($result, $groupId);
                if ($groupStr == false) {
                    continue;
                }


                $sql = 'update ' . $this->userTable . ' set `group`=' . '\'' . $groupStr . '\'' . ' where user_name=' . '\'' . $name . '\'';

                $res = Yii::app()->db_metric_meta->createCommand($sql)->execute();


            }
            if ($res == false) {
                return false;
            }

        }


        if (!empty($deletename)) {
            foreach ($deletename as $name) {
                $result = $this->selectUserGroup($name);
                $groupStr = $this->__createNewStr($result, $groupId, true);
                if ($groupStr == false) {
                    continue;
                }


                $sql = 'update ' . $this->userTable . ' set `group`=' . '\'' . $groupStr . '\'' . ' where user_name=' . '\'' . $name . '\'';

                $res = Yii::app()->db_metric_meta->createCommand($sql)->execute();

                if ($res == false) {
                    return false;
                }


            }
        }

        return true;
    }

    function selectUserGroup($name = '', $retuArr = false)
    {
        if (empty($name)) {
            $name = $this->username;
        }
        $result = Yii::app()->sdb_metric_meta->createCommand()
            ->select('group')->from($this->userTable)
            ->where('user_name=:user_name', array(':user_name' => $name))
            ->queryRow();

        if ($retuArr == true) {
            return explode(',', $result['group']);
        }
        return $result['group'];


    }

    function isFavorites($table_id)
    {
        $result = Yii::app()->sdb_metric_meta->createCommand()
            ->select('table_id')->from($this->favoriteTable)
            ->where('user_name=:user_name', array(':user_name' => $this->username))
            ->queryRow();
        if (in_array($table_id, explode(',', $result['table_id']))) {
            return true;
        }
        return false;


    }

    function addUserBehavior()
    {
        $sql = "replace into  $this->behaviorTable (`cdate`,`user_name`) values (:cdate,:username)";
        $username = Yii::app()->user->username;
        //$cdate=date('Y-m-d H:i:s',time());
        $cdate = date('Y-m-d', time());
        $parament = array(':username' => $username, ':cdate' => $cdate);
        $res = Yii::app()->db_metric_meta->createCommand($sql)->execute($parament);
        return $res;

    }

    function selectUserBehavior()
    {

        $sql = "select cdate,user_name from $this->behaviorTable order by cdate desc,id desc";
        $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $result;

    }

    function checkTableAuth($table_id)
    {
        $result = Yii::app()->sdb_metric_meta->createCommand()
            ->select('auth')->from($this->reportTable)
            ->where('id=:tableid', array(':tableid' => $table_id))
            ->queryRow();
        $auth = $result['auth'];
        if ($auth == 'all') {
            return true;
        }

        $tableGroup = explode(',', $auth);
        if (empty($authStr)) {
            return false;
        }
        $userGroup = $this->selectUserGroup('', true);
        if (empty($userGroup)) {
            return false;
        }

        $tmp = array_intersect($tableGroup, $userGroup);
        if (empty($tmp)) {
            return false;
        }

        return true;
    }

    /**
     * @param $userInfoParameter
     * @return mixed
     */
    public static function getAdminInfo($userInfoParameter)
    {
        $sql  = "SELECT * FROM `t_visual_user` WHERE `iphone` = '$userInfoParameter'";
        $user = Yii::app()->sdb_metric_meta->createCommand($sql)->queryRow();

        return $user;
    }

    /**
     * 当SSO登陆时判断本地Auth无用户时进行插入操作
     * @param $newUserInfo
     * @return mixed
     */
    public static function addAdminInfo($newUserInfo)
    {
        $sql        = "INSERT INTO  `t_visual_user` (`user_name`,`group`,`password`, `realname`, `iphone`, `change_pwd`) 
                                      VALUES (:user_name, :group, :password, :realname, :iphone, :change_pwd)";
        $parameters = array(':user_name'  => isset($newUserInfo['user_name']) ? $newUserInfo['user_name'] : '',
                            ':group'      => isset($newUserInfo['username']) ? $newUserInfo['username'] : '',
                            ':password'   => isset($newUserInfo['password']) ? $newUserInfo['password'] : '',
                            ':realname'   => isset($newUserInfo['realname']) ? $newUserInfo['realname'] : '',
                            ':iphone'     => isset($newUserInfo['iphone']) ? $newUserInfo['iphone'] : '',
                            ':change_pwd' => isset($newUserInfo['change_pwd']) ? $newUserInfo['change_pwd'] : 0,
        );
        $res = Yii::app()->db_metric_meta->createCommand($sql)->execute($parameters);

        return $res;
    }

    /**
     * 查询admin_role信息
     * @param $roleNameStr
     * @return mixed
     */
    public static function getAdminRole($roleNameStr = [])
    {
        if (empty($roleNameStr)) {
            $sql = "select * from t_eel_admin_role";
        } else {
            $sql = "select * from t_eel_admin_role where role_name in ($roleNameStr)";
        }
        $db     = Yii::app()->db_metric_meta;
        $result = $db->createCommand($sql)->queryAll();

        return $result;
    }

    public static function getAdminRoleRelationUser($userId)
    {
        $sql = "select * from `t_eel_admin_relation_user` where `user_id` = $userId";

        $db = Yii::app()->db_metric_meta;
        $result = $db->createCommand($sql)->queryAll();

        return $result;
    }

    /**
     * 从SSO获取菜单信息插入角色表
     * @param $menuArray
     * @return mixed
     */
    public static function addAdminRole($menuArray)
    {
        //要插入role_name
        $adminRole = self::getAdminRole();
        $alreadyExistRoleName = array_column($adminRole, 'role_name');
        $WaitInsertRoleName   = array_column($menuArray, 'menu_name');
        $insertRoleName       = array_diff($WaitInsertRoleName, $alreadyExistRoleName);

        //插入操作
        $res = '';
        if(!empty($insertRoleName)) {
            $insertRoleNameArray = [];
            foreach ($insertRoleName as $roleName) {
                $insertRoleNameArray[] = "(SELECT  '$roleName'  FROM dual WHERE not exists  (SELECT * FROM `t_eel_admin_role`  WHERE `role_name` = '$roleName'))";
            }

            $insertRoleNameStr = join("UNION ALL", $insertRoleNameArray);
            $inserSql          = "INSERT INTO  `t_eel_admin_role` (`role_name`)" . $insertRoleNameStr;

            $db  = Yii::app()->db_metric_meta;
            $res = $db->createCommand($inserSql)->execute();
        }

        return $res;
    }

    /**
     * 给用户角色赋值
     * @param $adminUserId
     * @param $menuArray
     * @return bool
     */
    public static function addAdminRelationUser($adminUserId, $menuArray)
    {
        $roleNameArray = [];
        foreach ($menuArray as $value) {
            $role_name       = $value['menu_name'];
            $roleNameArray[] = "'" . $role_name . "'";
        }
        $roleNameStr = join(",", $roleNameArray);
        //分别从t_eel_admin_role与t_eel_admin_relation_user取得用户角色数据
        $adminRole          = self::getAdminRole($roleNameStr);
        $roleRelationUser   = self::getAdminRoleRelationUser($adminUserId);
        $RelationUserRoleId = array_column($roleRelationUser, 'role_id');
        $adminRoleId        = array_column($adminRole, 'role_id');
        //判断插入／删除role_id
        $insertRoleId       = array_diff($adminRoleId, $RelationUserRoleId);
        $deleteRoleId       = array_diff($RelationUserRoleId, $adminRoleId);

        //插入操作
        if (!empty($insertRoleId)) {
            //构造插入VALUES
            $group_defaulth_have = 1; //默认为普通用户
            $insertRoleIdArray   = [];
            foreach ($insertRoleId as $roleId) {
                $insertRoleIdArray[] = "(SELECT $roleId, $adminUserId, $group_defaulth_have FROM dual WHERE NOT EXISTS (SELECT * FROM `t_eel_admin_relation_user`  
                                    WHERE `role_id` = $roleId AND `user_id` = $adminUserId))";
            }

            $insertRoleIdStr = join("UNION ALL", $insertRoleIdArray);
            $insertSql = "INSERT INTO `t_eel_admin_relation_user` (`role_id`, `user_id`, `group_defaulth_have`)" . $insertRoleIdStr;
            $db           = Yii::app()->db_metric_meta;
            $insertResult = $db->createCommand($insertSql)->execute();
        }

        //删除操作
        if (!empty($deleteRoleId)) {
            $deleteRoleIdStr = join(",", $deleteRoleId);
            $deleteSql = "DELETE FROM `t_eel_admin_relation_user`  WHERE `role_id` IN ($deleteRoleIdStr) AND `user_id` = $adminUserId";
            $db           = Yii::app()->db_metric_meta;
            $deleteResult = $db->createCommand($deleteSql)->execute();
        }

        return true;
    }
    /**
     * 获取报表对应的用户信息
     */
    function getReportUsers($reportIds,$userID=null){
        $whereSql ="";
        if(!is_array($reportIds)){
            $reportIds = [$reportIds];
            $whereSql .=" and re.report_id in (".  implode(',', $reportIds).")";
        }
        if($userID){
            $whereSql .=" and role.user_id =$userID";
        }
        $sql ="select  re.report_id,re.role_id,role.user_id,role.id,role.created_at,role.updated_at from t_eel_admin_relation_report re left join "
                . " t_eel_admin_relation_user as role  on  re.role_id = role.role_id where 1=1 ".$whereSql." group by re.report_id,role.user_id ";
        $db = Yii::app()->db_metric_meta;
        $result = $db->createCommand($sql)->queryAll();
        return $result;
    }
    //获取用户信息
    public function getUserInfo($Id)
    {
        $sql  = "SELECT * FROM `t_visual_user` WHERE `id` = $Id";
        $user = Yii::app()->sdb_metric_meta->createCommand($sql)->queryRow();
        return $user;
    }
    /**
     * 检查某个报表是否所属多个分组
     */
    public function checkRoleReport($reportIds = array()){
        $sql ="SELECT count(distinct role_id) as num,report_id FROM `t_eel_admin_relation_report`  ";
        $where ='';
        if(!empty($reportIds)){
            $where  = " WHERE `report_id` in (".  implode(",", $reportIds).")";
        }
        $sql  =  $sql.$where.' group by report_id';
        $user = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $user;
    }
}
