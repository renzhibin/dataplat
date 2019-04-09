<?php

/**
 * Created by PhpStorm.
 * User: liangbo
 * Date: 16/5/10
 * Time: 上午11:07
 */
class PrivilegeController extends Controller
{
    function __construct()
    {
        $this->objMenu = new MenuManager();
        $this->objAuth = new AuthManager();
        $this->objProject = new ProjectManager();
        $this->objRoles = new RolesManager();
        $this->common   = new CommonManager();
        $this->objReport = new ReportManager();
    }

    public function actionIndex(){
        //面包屑导航
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "#", 'content' => '用户权限管理');
        $tplArr['guider'] = $indexStr;

        //获取有权限的报表
        $allReport = $this->objReport->getReportSingle(array('id','cn_name'));
        $reportIds = $this->common->pickup($allReport,'id'); 
        $roleIds  = $this->objRoles->getGroupsByReoport($reportIds);
        
        $roleTmp = $this->common->pickup($roleIds,'role_id','report_id'); 
        foreach ($allReport as $key => $val){
            $menuInfo = $this->objMenu->getMenuByReoprt($val['id']);
            if(empty($menuInfo)){ 
                unset($allReport[$key]);
            }else{
                $allReport[$key]['role_id'] = $roleTmp[$val['id']];
            } 
            
        }
        //过滤没有分组的报表
        foreach ($allReport as $key=>$val){
          if(empty($val['role_id'])){
              unset($allReport[$key]);
          }   
        }
        //获取报表对应的所有分组
        $tplArr['roleList'] = $allReport;
        $userList = $this->objRoles->getUserList();

        $tplArr['userList'] = $userList['rows'];
        
        $this->render('privilege/index.tpl', $tplArr);
    }

    /**
     * 获取用户tree
     */
    public function actionGetUserRoleTree(){
        $userId = $_REQUEST['user_id'];
        
        //获取所有报表
        $allReport = $this->objReport->getReportSingle(array('id','cn_name'));
        $allReportIds = $this->common->pickup($allReport,'cn_name','id'); 
        $numArr = $this->objAuth->checkRoleReport();
        $filterReportIds =[];
        foreach ($numArr as $num){
            if($num['num'] > 1 ){
                 $filterReportIds[] = $num['report_id'];
            }
        }
        $search['user_id'] = [$userId];
        $search['not_in'] = $filterReportIds;
        $roleReport = $this->objRoles->getRoleReport($search); 
        $reportIds = $this->common->pickup($roleReport,'report_id'); 
        
        //获取所有一级菜单，并排序
        $firstMenu = $this->objMenu->selectFirstMenu();
        foreach ($firstMenu as $first){
            //获取二级菜单
            $flag =0;
            $firstFlag  =0;
            $secondMenu = $this->objMenu->getSecondMenu($first['first_menu']);
            if(!empty($secondMenu)){
                foreach ($secondMenu as $second){
                    $checkFlag =0;
                    $tableArr = json_decode($second['table_id'],true);
                    if(!empty($tableArr)){
                         foreach ($tableArr as $key=> $table){
                            $flag =1;
                            //生成三级节点
                            $one =[];
                            $one['id']      = $table['id']."_".$second['id']."_".$key;
                            $one['pId']     = $second['id'];
                            $one['name']    = $table['id']."_". $allReportIds[$table['id']]; 
                            $one['title']   = '';
                            $one['src_id']  = $second['id'];
                            if( in_array($table['id'],$reportIds)){
                                $checkFlag = true;
                                $one['checked'] = true;
                            }
                            $tree[] = $one;
                         }
                    }
                    //生成二级节点
                    if($flag){
                        $secondone =[];
                        $secondone['id']      =  $second['id'];
                        $secondone['pId']     =  $first['id'];
                        $secondone['name']    =  $second['second_menu'];
                        $secondone['title']   =  '';
                        if($checkFlag){
                            $firstFlag  = true;
                            $secondone['checked'] = true;
                        }
                        $tree[] = $secondone;
                    }
                }
                //生成一级节点
                if($flag){
                    $firstone =[];
                    $firstone['id']      =  $first['id'];
                    $firstone['pId']     =  0;
                    $firstone['name']    =  $first['first_menu'];
                    $firstone['title']   =  '';
                    if($firstFlag){
                        $firstone['checked'] = true;
                    }
                    if($first['first_menu'] !='用户画像'){
                         $firstone['open']  = true;
                    }
                    $tree[] = $firstone; 
                }
                
            }
        }
        $this->jsonOutPut(0,'',$tree);   
    }

    public function actionIndexData(){
        $searchJson = $_REQUEST['search'];
        $searchArr  = json_decode($searchJson,TRUE);
        $page       = isset($_REQUEST['page'])?$_REQUEST['page']:'';
        $rows       = isset($_REQUEST['rows'])?$_REQUEST['rows']:'';
        if(!empty($searchArr['user_id'])){
            $searchArr['user_id'] = explode(",",  $searchArr['user_id']);
        }
        $searchArr['iphone'] = isset($searchArr['iphone'])?$searchArr['iphone']:'';
        $searchArr['reportIds'] = !empty($searchArr['reportIds'])? explode(",",$searchArr['reportIds']):array();
        #print_r($searchArr);exit;
        $userList = $this->objRoles->getUserList($searchArr,$page,$rows);
        foreach ($userList['rows'] as $key=>$val){
            $user_name = explode('@', $val['user_name'])[0];
            if (in_array($user_name, $this->objAuth->getSuperName())) {
                $userList['rows'][$key]['realname'] = $val['realname']."（超级管理员）";
            }
        }
        echo json_encode($userList);exit;
    }

    public function actionUserRoles()
    {
        //for 搜索
        $user_id = isset($_REQUEST['user_id'])?$_REQUEST['user_id']:'';
        $role_id = isset($_REQUEST['role_id'])?$_REQUEST['role_id']:'';

        if (empty($user_id) && empty($role_id)) {
            $userRolesList = [];
        } else {
            $userRolesList = $this->objRoles->getUserRolesList($user_id, $role_id);
            $userRolesList = $this->objRoles->formatUserRolesList($userRolesList);
        }

        $userList = $this->objRoles->getUserList();
        $roleList = $this->objRoles->getGroupList();

        //面包屑导航
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "../privilege/reportroles", 'content' => '报表分组管理');
        $indexStr[] = array('href' => "#", 'content' => '用户分组管理');

        $tplArr['guider'] = $indexStr;
        $tplArr['userRolesList'] = $userRolesList;
        $tplArr['userList']      = $userList['rows'];
        $tplArr['roleList']      = $roleList;
        $this->render('privilege/userroles.tpl', $tplArr);
    }

    public function actionAddUserRoles()
    {
        if(!isset($_REQUEST['user_id'])) {
            $this->jsonOutPut(1,'user_id为空');
            return;
        }
        $user_id = $_REQUEST['user_id'];

        if(!isset($_REQUEST['role_id'])) {
            $this->jsonOutPut(1,'role_id');
        }
        $role_id = $_REQUEST['role_id'];

        $ret = $this->objRoles->addUserRoles($user_id, $role_id,$message);

        $this->jsonOutPut($ret?0:1, $message);
    }

    public function actionAddUserRolesMultiple()
    {
        if(!isset($_REQUEST['user_id'])) {
            $this->jsonOutPut(1,'user_id为空');
            return;
        }
        $user_id = $_REQUEST['user_id'];

        if(!isset($_REQUEST['role_id'])) {
            $this->jsonOutPut(1,'role_id');
        }
        $role_id = $_REQUEST['role_id'];
        
        $ret = $this->objRoles->addUserRolesMultiple($user_id, $role_id,$message);

        $this->jsonOutPut($ret?0:1, $message);
    }

    public function actionDelUserRoles()
    {
        if(!isset($_REQUEST['user_id'])) {
            $this->jsonOutPut(1,'user_id为空');
            return;
        }
        $user_id = $_REQUEST['user_id'];

        if(!isset($_REQUEST['role_id'])) {
            $this->jsonOutPut(1,'role_id');
        }
        $role_id = $_REQUEST['role_id'];

        $ret = $this->objRoles->delUserRole($user_id,$role_id,$message);
        $this->jsonOutPut($ret?0:1, $message);
    }
    public function actionDelUserRolesMultiple()
    {
        if(!isset($_REQUEST['user_id'])) {
            $this->jsonOutPut(1,'user_id为空');
            return;
        }
        $user_id = $_REQUEST['user_id'];

        if(!isset($_REQUEST['role_id'])) {
            $this->jsonOutPut(1,'role_id');
        }
        $role_id = $_REQUEST['role_id'];
        $ret = $this->objRoles->delUserRole($user_id,$role_id,$message);
        $this->jsonOutPut($ret?0:1, $message);
    }
    public function actionReportRoles()
    {
 
        //面包屑导航
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "../privilege/userroles", 'content' => '用户分组管理');
        $indexStr[] = array('href' => "#", 'content' => '报表分组管理');

        $tplArr['guider'] = $indexStr;
        $this->render('privilege/reportRoles.tpl', $tplArr);
    }
    public function actionReportRoleData()
    {
        $searchJson = $_REQUEST['search'];
        $searchArr  = json_decode($searchJson,TRUE);
        $page       = isset($_REQUEST['page'])?$_REQUEST['page']:'';
        $rows       = isset($_REQUEST['rows'])?$_REQUEST['rows']:'';
        $report_id = isset($searchArr['report-id'])?$searchArr['report-id']:'';
        $role_id = isset($searchArr['role-id'])?$searchArr['role-id']:'';
        $totalArr = $this->objRoles->getReportRolesList($report_id,$role_id,$page,$rows);
         
        $totalArr['rows'] = $this->objRoles->formatReportRolesList($totalArr['rows']);
        echo json_encode($totalArr);exit;
    }

    public function actionAddReportRoles()
    {
        if(!isset($_REQUEST['report_id'])) {
            $this->jsonOutPut(1,'report_id为空');
            return;
        }
        $report_id = $_REQUEST['report_id'];

        if(!isset($_REQUEST['role_id'])) {
            $this->jsonOutPut(1,'role_id');
        }
        $role_id = $_REQUEST['role_id'];

        $ret = $this->objRoles->addReportRoles($report_id, $role_id,$message);

        $this->jsonOutPut($ret?0:1, $message);
        return true;
    }

    public function actionDelReportRoles()
    {
        if(!isset($_REQUEST['report_id'])) {
            $this->jsonOutPut(1,'report_id为空');
            return;
        }
        $report_id = $_REQUEST['report_id'];

        if(!isset($_REQUEST['role_id'])) {
            $this->jsonOutPut(1,'role_id');
        }
        $role_id = $_REQUEST['role_id'];
        $this->objRoles->recordRoleLog($report_id,$role_id,'delete','');

        $ret = $this->objRoles->delReportRole($report_id,$role_id,$message);
        $this->jsonOutPut($ret?0:1, $message);
        return true;
    }

    public function actionAddRoles()
    {
        if(!isset($_REQUEST['role_name'])) {
            $this->jsonOutPut(1,'role_name为空');
            return;
        }
        $role_name = $_REQUEST['role_name'];
        $ret = $this->objRoles->addRole($role_name,$message);
        $this->jsonOutPut($ret?0:1, $message);
    }

    public function actionUser()
    {
        //for 搜索
        $user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';

        $userRolesList = $this->objRoles->getUserList($user_id);

        //面包屑导航
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "../privilege/reportroles", 'content' => '报表分组管理');
        $indexStr[] = array('href' => "#", 'content' => '用户管理');

        $tplArr['guider']        = $indexStr;
        $tplArr['userRolesList'] = $userRolesList['rows'];
        $this->render('privilege/user.tpl', $tplArr);
    }

    public function actionModifyUser()
    {
        if (!isset($_REQUEST['user_name'])) {
            $this->jsonOutPut(1, 'user_name为空');
        }
        $user_name = $_REQUEST['user_name'];

        $group = $_REQUEST['group'];

        if (!isset($_REQUEST['realname'])) {
            $this->jsonOutPut(1, 'realname为空');
        }
        $realname = $_REQUEST['realname'];

        if (!isset($_REQUEST['iphone'])) {
            $this->jsonOutPut(1, 'iphone为空');
        }
        $iphone = $_REQUEST['iphone'];

        if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
            $user_id = $_REQUEST['id'];
            $ret     = $this->objRoles->updateUser($user_id, $user_name, $group, $realname, $iphone, $message);
        } else {
            $ret = $this->objRoles->addUser($user_name, $group, $realname, $iphone, $message);
        }
        $this->jsonOutPut($ret ? 0 : 1, $message);
    }

    public function actionDelUser()
    {
        if (!isset($_REQUEST['user_id'])) {
            $this->jsonOutPut(1, 'user_id为空');
            return;
        }
        $user_id = $_REQUEST['user_id'];

        $ret = $this->objRoles->delUser($user_id, $message);
        //删除用户关系
        $roleArr = $this->objRoles->getUserRolesList($user_id);
        $role_id = $this->common->pickup($roleArr,'role_id');  
        $ret = $this->objRoles->delUserRole($user_id,$role_id,$message);
        $this->jsonOutPut($ret ? 0 : 1, $message);
        return true;
    }

    //添加t_visual_menu 一级菜单信息(first_menu)
    public function actionAddFirstMenu()
    {
        if (!isset($_REQUEST['first_menu']) || empty($_REQUEST['first_menu'])) {
            $this->jsonOutPut(1, 'first_menu为空');

            return;
        }
        $first_menu = $_REQUEST['first_menu'];

        $ret = $this->objMenu->addFirstMenu($first_menu, $message);

        $this->jsonOutPut($ret ? 0 : 1, $message);
    }

    /**
     * 查看分组列表
     */
    public function actionGroup()
    {
        $groupList = $this->objRoles->getGroupList();
        //$reportRolesList = $this->objRoles->formatReportRolesList($reportRolesList);
        //面包屑导航
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "../privilege/userroles", 'content' => '分组管理');
        $indexStr[] = array('href' => "#", 'content' => '报表分组管理');

        $tplArr['guider'] = $indexStr;
        $tplArr['reportGroupList'] = $groupList;
        $this->render('privilege/group.tpl', $tplArr);
    }

    /**
     * 删除分组
     * @return bool
     */
    public function actionDelGroup()
    {
        if(!isset($_REQUEST['role_id'])) {
            $this->jsonOutPut(1,'role_id');
        }
        $role_id = $_REQUEST['role_id'];
        $this->objRoles->recordRoleLog('0',$role_id,'delete','');

        $ret = $this->objRoles->delGroup($role_id,$message);
        $this->jsonOutPut($ret?0:1, $message);
        return true;
    }
    
    public function actionGetUser(){
        if (!isset($_REQUEST['user_id'])) {
            $this->jsonOutPut(1, 'user_id为空');
            return;
        }
        $user_id = $_REQUEST['user_id'];

        $ret = $this->objRoles->getUser($user_id);
        $this->jsonOutPut($ret ? 0 : 1,'',$ret);
        return true;
    }
    /**
     * 修改用户权限
     */
    public function actionEditPower(){
        /*
        $userId = $_REQUEST['user_id'];
        //重新添加关系
        $reportIds =[];
        foreach ($_REQUEST['reportNode'] as $item){
             $reportIds[] = explode("_", $item)[0];
        }
        $roleArr = $this->objRoles->getUserRolesList($userId);
        if(!empty($roleArr)){
            $role_id = $this->common->pickup($roleArr,'role_id');  
            $ret = $this->objRoles->delUserRole($userId,$role_id,$message);
            #echo $message;
        }
        //通过报表获取分组
        $roleIds  = $this->objRoles->getGroupsByReoport($reportIds);
        $roleIds = $this->common->pickup($roleIds,'role_id');  
        $message ='';
        $ret = $this->objRoles->addUserRolesMultiple(array('user_id'=>$userId), $roleIds,$message);
        $this->jsonOutPut($ret?0:1, $message);
        */
        $userId   = $_REQUEST['user_id'];
        $addNodes = $delNodes = [];
        $addRet   = $delRet = [];
        $message  = '';

        foreach ($_REQUEST['addNodes'] ?: [] as $item) {
            $addNodes[] = explode("_", $item)[0];
        }
        foreach ($_REQUEST['delNodes'] ?: [] as $item) {
            $delNodes[] = explode("_", $item)[0];
        }

        if (!empty($addNodes)) {
            $addNodes = $this->objRoles->getGroupsByReoport($addNodes);
            $addNodes = $this->common->pickup($addNodes, 'role_id');
            $addRet   = $this->objRoles->addUserRolesMultiple(['user_id' => $userId], $addNodes, $message);
        }

        if (!empty($delNodes)) {
            $delNodes = $this->objRoles->getGroupsByReoport($delNodes);
            $delNodes = $this->common->pickup($delNodes, 'role_id');
            $delRet   = $this->objRoles->delUserRole($userId, $delNodes, $message);
        }

        $this->jsonOutPut($addRet && $delRet ? 0 : 1, $message);
    }
    /**
     * 所有权限列表页面
     */
    public function actionAll(){
        $tplArr =array();
        //获取所有报表
        $allReport = $this->objReport->getReportSingle(array('id','cn_name'));
        $allReportIds = $this->common->pickup($allReport,'cn_name','id');
        $firstMenu = $this->objMenu->selectFirstMenu();
        $firstAll=[];
        foreach ($firstMenu as $first){
            //获取二级菜单
            $secondMenu = $this->objMenu->getSecondMenu($first['first_menu']);
            if(!empty($secondMenu)){
                $secondAll  =[];
                foreach ($secondMenu as $second){ 
                    
                    $tableArr = json_decode($second['table_id'],true); 
                    $tableall =[];
                    foreach ($tableArr as $table){
                         $one['id'] = $table['id'];
                         $one['name'] = $allReportIds[$table['id']];
                         $tableall[] = $one;
                    }
                    $oneSecond =[];
                    $oneSecond['id'] = $second['id'];
                    $oneSecond['name'] = $second['second_menu'];
                    $oneSecond['children'] = $tableall;
                    $secondAll[] = $oneSecond;
                }
                $onefirst =[];
                $onefirst['id'] = $first['id'];
                $onefirst['name'] = $first['first_menu'];
                $onefirst['children'] = $secondAll;
                $firstAll[] = $onefirst;
            }
        }
        $tplArr['first'] = $firstAll;
        $this->render('privilege/all.tpl', $tplArr);
    }
}