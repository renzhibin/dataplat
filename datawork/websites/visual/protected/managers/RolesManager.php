<?php

/**
 * Created by PhpStorm.
 * User: liangbo
 * Date: 16/5/10
 * Time: 下午4:28
 */
class RolesManager extends Manager
{
    function __construct()
    {
        $this->rolesTable = 't_eel_admin_role';
        $this->userTable = 't_visual_user';
        $this->reportTable = 't_visual_table';
        $this->userRolesTable = 't_eel_admin_relation_user';
        $this->userRolesTableDel = 't_eel_admin_relation_user_revoke';
        $this->reportRolesTable = 't_eel_admin_relation_report';
        $this->t_role_behavior_log='t_role_behavior_log';
        $this->comquery = new MysqlCModel();
        $this->common   = new CommonManager();
        $this->menuTable = 't_visual_menu';
    }

    // 获取 用户列表 信息
    function getUserList($searchArr =array(),$page=1,$limit=0){
        
        $offset = $limit * ($page -1);
        $sql = "select `id`, `user_name`, `group`, `realname`, `iphone` from {$this->userTable} where 1 = 1 ";
        $whereSql = "";
        if (!empty($searchArr['user_id']) ) {
            if(!is_array($searchArr['user_id'])){
                $searchArr['user_id'] =[$searchArr['user_id']];
            }
            $whereSql .= " and  id in (". implode(',', $searchArr['user_id']).") ";
        }
        if (!empty($searchArr['realname'])  ) {
            $whereSql .= " and realname like '%{$searchArr['realname']}%' ";
        }
        if (!empty($searchArr['iphone'])) {
            $whereSql .= " and iphone ={$searchArr['iphone']}";
        }
        if (!empty($searchArr['group']) ) {
            switch($searchArr['group']){
                case 'all':
                    break;
                case 'normal':
                    $whereSql .= " and `group` not in(3,2,5)";
                    break;
                default:
                    $whereSql .= " and `group`={$searchArr['group']}";
                    break;
            }
        }
        
        
        if (!empty($searchArr['reportIds']) ) {
            //获取具体当前报表权限的用户ID
            $userIds = $this->getRoleReport($searchArr,'role.user_id');
            $userIds = $this->common->pickup($userIds,'user_id');
            
            if(!empty($searchArr['user_id'])){
                $userIds = array_intersect($searchArr['user_id'], $userIds);
            }
            // $userIds 为索引数组，要求不为空则使用 测试第一行内容存在切为数字 判断
            if (isset($userIds[0]) && is_numeric($userIds[0])) {
                $whereSql .= " and id in (".  implode(',', $userIds).")";
            }else{
                return array('rows'=>array(),'total'=>null);
            }
        }
        //计算总数
        $countSql = "select count(*) as total from {$this->userTable} where 1 = 1 ";
        $countSql = $countSql." ".$whereSql;
        
        $sql = $sql." ".$whereSql." ".$this->comquery->parseLimit($limit,$offset);
        $db    = Yii::app()->sdb_metric_meta;
        $user_data = $db->createCommand($sql)->queryAll();
        $total  =   $db->createCommand($countSql)->queryRow();
        
        return array('rows'=>$user_data,'total'=>$total['total']);
    }

    function getUserRoleList($filter = [])
    {
        $where = '1 = 1';
        foreach ($filter as $k => $v) {
            $where .= " AND {$v[0]} {$v[1]} {$v[2]}";
        }

        # 561 挂载了两个地方，处理不了
        # 814 为临时表常见报表，权限不回收
        # updated_at 用于获取其获取权限的时间
        $sql = 'SELECT
                    users.id AS user_id,
                    users.user_name,
                    users.realname,
                    rel_report.role_id,
                    rel_report.report_id,
                    user_table.cn_name,
                    menu.id AS menu_id,
                    menu.first_menu,
                    menu.second_menu,
                    rel_user.updated_at
                FROM t_visual_user AS users
                INNER JOIN t_eel_admin_relation_user AS rel_user ON users.id = rel_user.user_id
                INNER JOIN
                (
                    SELECT
                        report_id, role_id
                    FROM t_eel_admin_relation_report
                    WHERE report_id != 0
                    GROUP BY report_id HAVING COUNT(1) = 1
                ) AS rel_report ON rel_report.role_id = rel_user.role_id
                INNER JOIN t_visual_table AS user_table ON user_table.id = rel_report.report_id AND user_table.flag = 1
                    AND rel_report.report_id NOT IN (561, 814)
                INNER JOIN t_visual_menu AS menu ON menu.table_id like concat("%\"id\":\"", rel_report.report_id, "\"%") AND menu.flag = 1
                WHERE ' . $where;

        $data = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $data;
    }

    //添加 新用户
    function addUser($user_name, $group, $realname, $iphone, &$message)
    {
        //检查同名
        $user_name_sql  = "select id from {$this->userTable} where user_name = '{$user_name}'";
        $db             = Yii::app()->sdb_metric_meta;
        $user_name_data = $db->createCommand($user_name_sql)->queryAll();
        if (count($user_name_data) > 0) {
            $message = '用户名已经存在';
            return false;
        }

        //添加
        $insert_sql = "insert into {$this->userTable} (`user_name`, `group`, `realname`, `iphone`) ";
        $insert_sql .= "values ('{$user_name}', '{$group}', '{$realname}', '{$iphone}')";
        $db  = Yii::app()->db_metric_meta;
        $res = $db->createCommand($insert_sql)->execute();
        if ($res <= 0) {
            $message = '添加失败';
            return false;
        }

        return true;
    }

    //更新 指定用户
    function updateUser($id, $user_name, $group, $realname, $iphone, &$message)
    {
        // 更新
        $insert_sql = "update {$this->userTable} set `user_name` = '{$user_name}', `group` = '{$group}', `realname` = '{$realname}', `iphone` = '{$iphone}' where id = '{$id}'";
        $db         = Yii::app()->db_metric_meta;
        $res        = $db->createCommand($insert_sql)->execute();
        if ($res <= 0) {
            $message = '没有更新';
            return false;
        }

        return true;
    }

    //删除 用户
    function delUser($user_id, &$message)
    {
        $del_sql = "delete from {$this->userTable} where id = '{$user_id}'";
        $db      = Yii::app()->db_metric_meta;
        $res     = $db->createCommand($del_sql)->execute();
        if ($res <= 0) {
            $message = '删除失败';
            return false;
        }
        $message = '删除成功';
        return true;
    }

    //获取user role 关系信息
    function getUserRolesList($user_id = '', $role_id = '')
    {
        $sql = "select user_id,role_id from {$this->userRolesTable} where 1=1 ";
        if ($user_id != '') {
            $sql .= " and user_id = {$user_id}";
        }
        if ($role_id != '') {
            $sql .= " and role_id = {$role_id}";
        }
        $db = Yii::app()->sdb_metric_meta;
        $user_roles_data = $db->createCommand($sql)->queryAll();

        return $user_roles_data;
    }

    //格式化user role 关系信息
    function formatUserRolesList($user_roles_data)
    {
        if(empty($user_roles_data)) {
            return array();
        }

        $user_ids_array = array();
        $role_ids_array = array();
        foreach ($user_roles_data as $item) {
            $user_ids_array[] = $item['user_id'];
            $role_ids_array[] = $item['role_id'];
        }
        $user_ids_str = implode($user_ids_array,',');
        $role_ids_str = implode($role_ids_array,',');

        //获取相关用户信息
        $sql_user = "select id,user_name from {$this->userTable} where id in ({$user_ids_str})";
        $db = Yii::app()->sdb_metric_meta;
        $user_data = $db->createCommand($sql_user)->queryAll();
        $user_data_map = array();
        foreach($user_data as $user) {
            $user_data_map[$user['id']] = $user['user_name'];
        }

        //获取相关分组信息
        $sql_role = "select role_id,role_name from {$this->rolesTable} where role_id in ({$role_ids_str})";
        $db = Yii::app()->sdb_metric_meta;
        $role_data = $db->createCommand($sql_role)->queryAll();
        $role_data_map = array();
        foreach($role_data as $role) {
            $role_data_map[$role['role_id']] = $role['role_name'];
        }

        // 获取报表相关信息
        $sql_report      = "select id,cn_name from {$this->reportTable}";
        $db              = Yii::app()->sdb_metric_meta;
        $report_data     = $db->createCommand($sql_report)->queryAll();
        $report_data_map = array();
        foreach ($report_data as $report) {
            $report_data_map[$report['id']] = $report['cn_name'];
        }

        // 获取菜单相关信息
        $sql_role      = "select id, first_menu, second_menu from {$this->menuTable}";
        $db            = Yii::app()->sdb_metric_meta;
        $menu_data     = $db->createCommand($sql_role)->queryAll();
        $menu_data_map = array();
        foreach ($menu_data as $menuData) {
            $menu_data_map[$menuData['id']] = "{$menuData['first_menu']}_{$menuData['second_menu']}";
        }

        foreach($user_roles_data as &$user_roles) {
            $user_roles['user_name'] = $user_data_map[$user_roles['user_id']];
            $user_roles['role_name'] = $role_data_map[$user_roles['role_id']];

            if (isset($user_roles['role_name']) && preg_match('/^\d+_\d+$/', $user_roles['role_name'])) {
                $allId    = explode('_', $user_roles['role_name']);
                $menuId   = $allId[0];
                $reportId = $allId[1];
                if (isset($menu_data_map[$menuId]) && isset($report_data_map[$reportId])) {
                    $user_roles['role_name'] = "{$menu_data_map[$menuId]}_{$report_data_map[$reportId]}({$user_roles['role_name']})";
                }
            }
        }

        return $user_roles_data;
    }

    //获取user role 关系信息
    function getReportRolesList($report_id = '', $role_id = '',$page=1,$limit=0)
    {
        $offset = $limit * ($page -1);
        $sql = "select * from {$this->reportRolesTable} where 1=1 ";
        $whereSql ='';
        if ($report_id != '') {
            $whereSql .= " and report_id = {$report_id}";
        }
        if ($role_id != '') {
            $whereSql .= " and role_id = {$role_id}";
        }
        $countSql ="";
        $countSql = "select count(*) as total from {$this->reportRolesTable} where 1 = 1 ";
        $countSql = $countSql." ".$whereSql;
        $sql = $sql." ".$whereSql." ".$this->comquery->parseLimit($limit,$offset);
        
        $db = Yii::app()->sdb_metric_meta;
        $report_roles_data = $db->createCommand($sql)->queryAll();
        $total  =   $db->createCommand($countSql)->queryRow();
        return array('rows'=>$report_roles_data,'total'=>$total['total']);
    }

    //格式化report role 关系信息
    function formatReportRolesList($report_roles_data)
    {
        if(empty($report_roles_data)) {
            return array();
        }

        $report_ids_array = array();
        $role_ids_array = array();
        foreach ($report_roles_data as $item) {
            $report_ids_array[] = $item['report_id'];
            $role_ids_array[] = $item['role_id'];
        }
        $report_ids_str = implode($report_ids_array,',');
        $role_ids_str = implode($role_ids_array,',');

        // 获取报表相关信息
        $sql_report = "select id,cn_name from {$this->reportTable} where id in ({$report_ids_str})";
        $db = Yii::app()->sdb_metric_meta;
        $report_data = $db->createCommand($sql_report)->queryAll();
        $report_data_map = array();
        foreach($report_data as $report) {
            $report_data_map[$report['id']] = $report['cn_name'];
        }

        // 获取分组相关信息
        $sql_role = "select role_id,role_name from {$this->rolesTable} where role_id in ({$role_ids_str})";
        $db = Yii::app()->sdb_metric_meta;
        $role_data = $db->createCommand($sql_role)->queryAll();
        $role_data_map = array();
        foreach($role_data as $role) {
            $role_data_map[$role['role_id']] = $role['role_name'];
        }

        // 获取菜单相关信息
        $sql_role      = "select id, first_menu, second_menu from {$this->menuTable}";
        $db            = Yii::app()->sdb_metric_meta;
        $menu_data     = $db->createCommand($sql_role)->queryAll();
        $menu_data_map = array();
        foreach ($menu_data as $menuData) {
            $menu_data_map[$menuData['id']] = "{$menuData['first_menu']}_{$menuData['second_menu']}";
        }

        foreach($report_roles_data as &$report_roles) {
            $report_roles['report_name'] = $report_data_map[$report_roles['report_id']];
            $report_roles['role_name'] = $role_data_map[$report_roles['role_id']];
            if ($report_roles['report_id'] == 0) {
                $report_roles['report_name'] = '规则默认报表(勿删)';
            }
            if (isset($report_roles['role_name']) && preg_match('/^\d+_\d+$/', $report_roles['role_name'])) {
                $allId    = explode('_', $report_roles['role_name']);
                $menuId   = $allId[0];
                $reportId = $allId[1];
                if (isset($menu_data_map[$menuId]) && isset($report_data_map[$reportId])) {
                    $report_roles['role_name'] = "{$menu_data_map[$menuId]}_{$report_data_map[$reportId]}({$report_roles['role_name']})";
                }
            }
        }

        return $report_roles_data;
    }

    //添加user role 关系
    function addUserRoles($user_id,$role_id,&$message)
    {
        //检查user是不是存在

        //检查role是不是存在

        //检查是不是已经添加过关系
        $user_role_sql = "select user_id,role_id from {$this->userRolesTable} ";
        $user_role_sql .= "where user_id = {$user_id} and role_id = {$role_id}";
        $db = Yii::app()->sdb_metric_meta;
        $user_role_data = $db->createCommand($user_role_sql)->queryAll();
        if(count($user_role_data) > 0) {
            $message = '关系已经存在';
            return false;
        }

        //添加
        $insert_sql = "insert into {$this->userRolesTable} (user_id,role_id,group_defaulth_have) ";
        $insert_sql .= "values ({$user_id},{$role_id},1)";
        $db = Yii::app()->db_metric_meta;
        $res = $db->createCommand($insert_sql)->execute();
        if ($res <= 0) {
            $message = '添加失败';
            return false;
        }
        $message = '添加成功';
        return true;
    }

    function addUserRolesMultiple($user_id, $role_id, &$message)
    {
        $list = [];

        foreach ($user_id as $user) {
            foreach ($role_id as $role) {
                $list["{$user}_{$role}"] = [
                    'user' => $user,
                    'role' => $role,
                ];
            }
        }

        $user_id_multi = implode("','", $user_id);
        $role_id_multi = implode("','", $role_id);
        //检查是不是已经添加过关系
        $user_role_sql  = "select user_id, role_id, concat(user_id, '_', role_id) as user_role from {$this->userRolesTable} ";
        $user_role_sql .= "where user_id in ('{$user_id_multi}') and role_id in ('{$role_id_multi}')";
        $db             = Yii::app()->sdb_metric_meta;
        $user_role_data = $db->createCommand($user_role_sql)->queryAll();

        $messageString = [];
        $insertList    = $list;

        foreach ($user_role_data as $existUserRole) {
            if (isset($list[$existUserRole['user_role']])) {
                unset($insertList[$existUserRole['user_role']]);
                $messageString[] = "用户：{$existUserRole['user_id']}, 规则：{$existUserRole['role_id']}";
            }
        }

        if (count($messageString) > 0) {
            $message = implode('；', $messageString) . '。以上关系已经存在';
        }

        if (count($insertList) <= 0) {
            $message = '所有的关系都已存在，不要再添加了';
            return false;
        }

        //添加
        $insert_sql   = "insert into {$this->userRolesTable} (user_id, role_id, group_defaulth_have) ";
        $inserpartSQL = [];
        foreach ($insertList as $currentRule) {
            $inserpartSQL[] = "({$currentRule['user']}, {$currentRule['role']} ,1)";
        }
        $insert_sql .= "values " . implode(', ', $inserpartSQL);
        $db  = Yii::app()->db_metric_meta;
        $res = $db->createCommand($insert_sql)->execute();

        if ($res <= 0) {
            $message .= '状态：添加失败';
            return false;
        }

        if (empty($message)) {
            $message = '状态：添加成功';
        } else {
            $message .= '。状态：添加成功';
        }
        return true;
    }

    //添加report role 关系
    function addReportRoles($report_id,$role_id,&$message)
    {
        //检查reprot是不是存在

        //检查role是不是存在

        //检查是不是已经添加过关系
        $report_role_sql = "select report_id,role_id from {$this->reportRolesTable} ";
        $report_role_sql .= "where report_id = {$report_id} and role_id = {$role_id}";
        $db = Yii::app()->sdb_metric_meta;
        $report_role_data = $db->createCommand($report_role_sql)->queryAll();
        if(count($report_role_data) > 0) {
            $message = '关系已经存在';
            return false;
        }

        //添加
        $insert_sql = "insert into {$this->reportRolesTable} (report_id,role_id,level_id) ";
        $insert_sql .= "values ({$report_id},{$role_id},0)";
        $db = Yii::app()->db_metric_meta;
        $res = $db->createCommand($insert_sql)->execute();
        if ($res <= 0) {
            $message = '添加失败';
            return false;
        }

        $message = '添加成功';
        return true;
    }

    //添加report role 关系
    function addRole($role_name,&$message)
    {
        //检查reprot是不是存在

        //检查role是不是存在

        //检查同名
        $role_name_sql = "select role_id from {$this->rolesTable} ";
        $role_name_sql .= "where role_name like '{$role_name}'";
        $db = Yii::app()->sdb_metric_meta;
        $role_name_data = $db->createCommand($role_name_sql)->queryAll();
        if(count($role_name_data) > 0) {
            $message = '同名role已经存在';
            return false;
        }

        //添加
        $insert_sql = "insert into {$this->rolesTable} (role_name) ";
        $insert_sql .= "values ('{$role_name}')";
        $db = Yii::app()->db_metric_meta;
        $res = $db->createCommand($insert_sql)->execute();
        if ($res <= 0) {
            $message = '添加失败';
            return false;
        }
        $role_id = $db->getLastInsertID();

        //添加
        $insert_sql = "insert into {$this->reportRolesTable} (report_id,role_id,level_id) ";
        $insert_sql .= "values (0,{$role_id},0)";
        $db = Yii::app()->db_metric_meta;
        $res = $db->createCommand($insert_sql)->execute();
        if ($res <= 0) {
            $message = '添加失败';
            return false;
        }

        $message = '添加成功,role_id='.$role_id;
        return true;
    }

    # 此数据写入 t_eel_admin_relation_user_bak 表
    function addDelRole($insertList)
    {
        $insert_sql = "insert into {$this->userRolesTableDel} (del_date, role_id, user_id) ";
        $insertData = [];
        foreach ($insertList as $reportId => $currentRule) {
            $insertData[] = "('{$currentRule['del_date']}', {$currentRule['role_id']}, {$currentRule['user_id']})";
        }
        $insert_sql .= "values " . implode(', ', $insertData);
        $res        = Yii::app()->db_metric_meta->createCommand($insert_sql)->execute();

        return $res <= 0 ? false : true;
    }

    //删除user role 关系
    function delUserRole($user_id,$role_id,&$message)
    {
        //检查reprot是不是存在

        //检查role是不是存在
        
        //添加
        $del_sql = "delete from {$this->userRolesTable} ";
        if(!is_array($user_id)){
            $user_id = array($user_id);
        }
        if(!is_array($role_id)){
            $role_id = array($role_id);
        }
        if(empty($role_id)){
             $message = '分组ID不能为空';
            return false;
        }
        $del_sql .= "where user_id in (".implode(",", $user_id).") and role_id in (".implode(",", $role_id).")";
        $db = Yii::app()->db_metric_meta;
        $res = $db->createCommand($del_sql)->execute();
        #$this->recordRoleLog(json_encode($user_id), json_encode($role_id), 'delete', Yii::app()->user->id);
        if ($res <= 0) {
            $message = '删除失败';
            return false;
        }
        $message = '删除成功';
        return true;
    }
    /**
     * 按ID删除权限
     */
    function  delUserRoleById($id){
        $del_sql = "delete from {$this->userRolesTable} ";
        $del_sql .= "where id =$id ";
        $db = Yii::app()->db_metric_meta;
        $res = $db->createCommand($del_sql)->execute();
        if ($res <= 0) {
            $message = '删除失败';
            return false;
        }
        $message = '删除成功';
        return true;
    }

    //删除report role 关系
    function delReportRole($report_id,$role_id,&$message)
    {
        //检查reprot是不是存在

        //检查role是不是存在

        //添加
        $del_sql = "delete from {$this->reportRolesTable} ";
        $del_sql .= "where report_id = {$report_id} and role_id = {$role_id}";
        $db = Yii::app()->db_metric_meta;
        $res = $db->createCommand($del_sql)->execute();
        if ($res <= 0) {
            $message = '删除失败';
            return false;
        }
        $message = '删除成功';
        return true;
    }

    //记录报表角色操作日志
    function recordRoleLog($report_id,$role_id,$action,$username){
        $now=date('Y-m-d H:i:s');
        $sql="insert into {$this->t_role_behavior_log}(cdate,user_name,user_action,report_id,role_id) values('{$now}','{$username}','{$action}',{$report_id},{$role_id})";
        $db=Yii::app()->db_metric_meta;
        $res = $db->createCommand($sql)->execute();

        if ($res <= 0) {
            return false;
        }
        return true;
    }

    //获取删除权限组13报表关系
    function get13Deletereports(){

        $sql = "select * from {$this->t_role_behavior_log} where role_id=13 and user_action='delete'";

        $db = Yii::app()->sdb_metric_meta;
        $report_roles_data = $db->createCommand($sql)->queryAll();

        return $report_roles_data;


    }

    /**
     * @date 2016-06-30
     * @author jideyue
     */
    function addRole13Group($reportId,$roleId=13) {
        if ( isset($reportId) ) {
            $db = Yii::app()->db_metric_meta;
            $sql = "select id from {$this->reportRolesTable} where report_id = {$reportId} and role_id = {$roleId}";
            $res = $db->createCommand($sql)->queryRow();
            if ( $res == false ) {
                $isql = "insert into {$this->reportRolesTable} (report_id,role_id,level_id) values ({$reportId},{$roleId},0)";
                $ires = $db->createCommand($isql)->execute();
                return $ires ? true : false;
            }
        }
        return false;
    }


    function resetPwd($username,$pwd,$newpwd,&$message){

        if ( isset($newpwd) ) {
            $db = Yii::app()->db_metric_meta;
            $sql = "select id from {$this->userTable} where user_name = '{$username}' and password = '{$pwd}'";
            $res = $db->createCommand($sql)->queryRow();
            if ( $res !=false ) {
                $isql = "update {$this->userTable} set password='{$newpwd}',change_pwd=0 where user_name = '{$username}' and password = '{$pwd}'";
                $ires = $db->createCommand($isql)->execute();
                $message='修改密码成功';
                return $ires ? true : false;
            }else{
                $message='修改密码失败';
                return false;
            }
        }
        $message='修改密码失败，密码为空。';
        return false;
    }

    //得到分组列表
    public function getGroupList ()
    {
        // 获取分组信息
        $db = Yii::app()->db_metric_meta;
        $sql = "select * from {$this->rolesTable} order by role_id asc";
        $groupData = $db->createCommand($sql)->queryAll();

        // 获取报表相关信息
        $sql_report      = "select id,cn_name from {$this->reportTable}";
        $db              = Yii::app()->sdb_metric_meta;
        $report_data     = $db->createCommand($sql_report)->queryAll();
        $report_data_map = array();
        foreach ($report_data as $report) {
            $report_data_map[$report['id']] = $report['cn_name'];
        }

        // 获取菜单相关信息
        $sql_role      = "select id, first_menu, second_menu from {$this->menuTable}";
        $db            = Yii::app()->sdb_metric_meta;
        $menu_data     = $db->createCommand($sql_role)->queryAll();
        $menu_data_map = array();
        foreach ($menu_data as $menuData) {
            $menu_data_map[$menuData['id']] = "{$menuData['first_menu']}_{$menuData['second_menu']}";
        }

        foreach ($groupData as &$roleData) {
            if (preg_match('/^\d+_\d+$/', $roleData['role_name'])) {
                $allId    = explode('_', $roleData['role_name']);
                $menuId   = $allId[0];
                $reportId = $allId[1];
                if (isset($menu_data_map[$menuId]) && isset($report_data_map[$reportId])) {
                    $roleData['role_name'] = "{$menu_data_map[$menuId]}_{$report_data_map[$reportId]}({$roleData['role_name']})";
                }
            }
        }

        return $groupData;
    }
    //获取新分组信息
    public function getGroup($search=array()){
        $db = Yii::app()->db_metric_meta;
        $whereSql ='';
        $sql = "select * from {$this->rolesTable} where role_name regexp '^[0-9]+_[0-9]+$' ";
        if(!empty($search['role_id'])){
            $whereSql .=" and role_id  in (".implode(',', $search['role_id']).") ";
        }
        $sql = $sql.$whereSql;
        $groupData = $db->createCommand($sql)->queryAll();
        return $groupData;
    }
    //删除 group 关系
    function delGroup($role_id,&$message)
    {
        //开始事务
        $transaction= Yii::app()->db_metric_meta->beginTransaction();
        try {
            $delRoleSql = "delete from {$this->rolesTable} where  role_id = {$role_id}";
            $delReportsRoleSql = "delete from {$this->reportRolesTable} where  role_id = {$role_id}";
            Yii::app()->db_metric_meta->createCommand($delRoleSql)->execute();
            Yii::app()->db_metric_meta->createCommand($delReportsRoleSql)->execute();
            //提交事务
            $transaction->commit();
            $message = '删除成功';
            return true;
        } catch (Exception $e) {
            //如果操作失败, 数据回滚
            $transaction->rollback();
            $message = '删除失败';
            return false;
        }
    }
    
    /**
     * 获取有权限的报表
     */
    public function getRoleReport($search = array(),$goupby=''){
        
        $sql ="select  re.report_id,re.role_id,role.user_id,role.id,re.created_at from t_eel_admin_relation_report re left join "
                . " t_eel_admin_relation_user as role  on  re.role_id = role.role_id where  1=1 and report_id !=0 ";
        
        if(!empty($search['reportIds'])){
            $sql .=" and re.report_id in (".implode(',', $search['reportIds']).") ";
        }
        if(!empty($search['not_in'])){
            $sql .=" and re.report_id not in (".implode(',', $search['not_in']).") ";
        }
        if(!empty($search['user_id'])){
            if(!is_array($search['user_id'])){
                $search['user_id'] = [$search['user_id']];
            }
            $sql .=" and role.user_id in (".implode(',', $search['user_id']).") ";
        }
        if(!empty($goupby)){
            $sql .= 'group by '.$goupby;
        }else{
            $sql .= 'group by re.report_id ';
        }
        $db = Yii::app()->db_metric_meta;
        $result = $db->createCommand($sql)->queryAll();
        return $result;
    }
    /**
     * 通过报表获取分组
     */
    public function getGroupsByReoport($reportIds){
        $whereSql ="";
        if(!empty($reportIds)){
            $whereSql .=" and  report_id in(".  implode(",", $reportIds).")";
        }
        $sql =" select * from  $this->reportRolesTable where  1=1 ";
        $sql = $sql.$whereSql."group by role_id,report_id";
        $db = Yii::app()->db_metric_meta;
        $result = $db->createCommand($sql)->queryAll();
        return $result;
    }
    //获取用户信息
    public function getUser($userId){
        $db = Yii::app()->db_metric_meta;
        $sql = "select * from {$this->userTable} where id = $userId ";
        $userArr = $db->createCommand($sql)->queryRow();
        return $userArr;
    }
}
