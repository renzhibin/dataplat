<?php

class ReportController extends Controller
{
    function __construct()
    {
        $this->objMenu = new MenuManager();
        $this->objVisual = new VisualManager();
        $this->objReport = new ReportManager();
        $this->objAuth = new AuthManager();
        $this->objProject = new ProjectManager();
        $this->objFackcube = new FackcubeManager();
        $this->objBehavior = new BehaviorManager();
    }

    function  actionPreview()
    {
        if (empty($_REQUEST['preview'])) {
            echo $this->jsonOutPut(false,'该模式只供预览使用,请保存后在使用搜索等功能');
            exit();
        }

        $config = json_decode($_REQUEST['preview'], true);
        $tplArr = $this->objReport->showReport(false, $_REQUEST, $config);

        $this->render('visual/showvisual.tpl', $tplArr);

    }

    #显示用户自定义 衍生报表
    function actionShowReportCustom()
    {
        $id = $_REQUEST['id'];
        // 校验是否有权限访问
        if (!$this->objAuth->checkAuthFromMenu($id, $this->username)) {
            $checkData = $this->objReport->checkReportCustomAuth(is_numeric($id) ? intval($id) : 0, Yii::app()->user->username ?: '');

            if (empty($checkData)) {
                $this->render('error/error.tpl', ['msg' => ['抱歉，您没有访问权限']]);
                exit;
            }
        }

        $tplArr = '';
        if (!empty($id)) {
            $tplArr              = $this->objReport->showReportCustom($id, $_REQUEST);
            $tplArr['isCollect'] = false;
        }

        $params             = array();
        $params['table_id'] = $id;
        $this->objBehavior->addUserBehaviorToLog($id, -1, '/report/showreport/' . $id, $params);
        $this->render('visual/showvisual.tpl', $tplArr);
    }

    #显示报表
    function actionShowReport() {
        $id = $_REQUEST['id'];
        //识别脚本的抓取行为
        $toDownPng = $_REQUEST['toDownPng'];
        if(empty($toDownPng) && ALYAUTH === True){
            if($this->objReport->isOwner($id) !== TRUE){
                echo '您没有该报表调试页面的访问权限';
                exit();
            }
        }

        $tplArr = '';
        if (!empty($id)) {
            $tplArr = $this->objReport->showReport($id, $_REQUEST);
            $tplArr['isCollect']=$this->objAuth->isFavorites($id);
        }

        $params = array();
        $params['table_id'] = $id;
        $this->objBehavior->addUserBehaviorToLog($id,-1,'/report/showreport/'.$id,$params);
        //图片抓取验证
        $phantomjs=$_REQUEST["phantomjs"];
        $tplArr['downpic'] = 0;
        if($phantomjs==1){
            $tplArr['downpic'] = 1;
        }
        $tplArr['WEB_API'] = WEB_API;
        $this->render('visual/showvisual.tpl', $tplArr);

    }

    function  actionAddReport(){

       /* $project = $_REQUEST['project'];
        $type = $_REQUEST['type'];
        if (empty($project) || empty($type)) {
            echo '<script>history.back(-1)</script>';
            return;
        }

        $res = $this->objMenu->selectFirstMenu();
        $tplArr['first_menu'] = $res;
        $tplArr['group'] = $this->objAuth->getGroup();
        $tplArr['config'] = json_encode($this->objFackcube->get_app_conf(array('project' => $project), true));
        $tplArr['dimensions'] = json_encode($this->objFackcube->get_dimset(array('project' => $project)));
        $tplArr['type'] = $type;
        $tplArr['unit'] = $this->objReport->getUnit();
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');

        $referUrl = $_SERVER['HTTP_REFERER'];
        //面包屑效果
        if(strpos($referUrl,'reportlist')!==false){
            $indexStr[] = array('href'=>"reportlist",'content'=>'管理工具');
            $indexStr[] = array('href'=>"reportlist",'content'=>'报表管理');
        }else{
            $indexStr[] = array('href'=>"../project/index",'content'=>'管理工具');
            $indexStr[] = array('href'=>"../project/index",'content'=>'项目管理');
        }
        $indexStr[] = array('href'=>"#",'content'=>'新增报表');

        $tplArr['guider'] = $indexStr;

        if($type==2){
            $this->render('report/addcontrast.tpl', $tplArr);
        }else{
            $this->render('report/addreport.tpl', $tplArr);
        }*/
        //2015-8-25 报表组合
        $tplArr['timeline']= $this->addition->getTimeLineList();
        $tplArr['unit'] = $this->objReport->getUnit();
        $tplArr['project'] = $this->objProject->getProjectList();
        $tplArr['project'] = $this->objProject->filterProject($tplArr['project'],1);
        $this->render('report/addreport.tpl', $tplArr);

    }

    function actionGetconfig(){
        $project = $_REQUEST['project'];
        if(empty($project)){
            echo $this->jsonOutPut(1,'项目名不能为空');
            return;
        } else {
            $tplArr['config'] = json_encode($this->objFackcube->get_app_conf(array('project' => $project), true));
            $tplArr['dimensions'] = json_encode($this->objFackcube->get_dimset(array('project' => $project)));
            echo $this->jsonOutPut('0','',$tplArr);
        }
    }


    function actionEditorReport()
    {
        $id = $_REQUEST['id'];
        if (!$id) {
            echo "<script>alert('没有选择报表'); window.location.href ='/project/index'</script>";
            exit;
        }
        $tplArr['timeline']= $this->addition->getTimeLineList();
        $tplArr['project'] = $this->objProject->getProjectList();
        $tplArr['project'] = $this->objProject->filterProject($tplArr['project'],1);
        $confArr = $this->objReport->getReoport($id);
        $type = $confArr['type'];
        $tplArr['unit'] = $this->objReport->getUnit();
        $tplArr['first_menu'] = $this->objMenu->selectFirstMenu();
        $tplArr['group'] = $this->objAuth->getGroup();
        $tplArr['params'] = json_encode($confArr['params']);

        $tplArr['config'] = json_encode($this->objFackcube->get_app_conf(array('project' => $confArr['project']), true));
        $tplArr['dimensions'] = json_encode($this->objFackcube->get_dimset(array('project' => $confArr['project'])));
        $tplArr['id'] = $id;
        $tplArr['type'] = $type;
        $fromURL = $_SERVER['HTTP_REFERER'];

        //面包屑导航
        $indexStr[] = array('href'=>"/visual/index",'content'=>'首页');

        if(strstr($fromURL,"project")){
            $indexStr[] = array('href'=>"../../project/index",'content'=>'管理工具');
            $indexStr[] = array('href'=>"../../project/index",'content'=>'项目管理');
            $indexStr[] = array('href'=>$fromURL,'content'=>'查看报表');
        }else{
            $indexStr[] = array('href'=>"../reportlist",'content'=>'管理工具');
            $indexStr[] = array('href'=>"../reportlist",'content'=>'报表管理');
        }
        $indexStr[] = array('href'=>"#",'content'=>'编辑报表');
        $tplArr['guider'] = $indexStr;
        //print_r('<pre>');print_r($tplArr);exit();
        $this->render('report/editorreport.tpl', $tplArr);

        /*if($type==2){
            $this->render('report/editcontrast.tpl', $tplArr);
        }else{
            $this->render('report/editorreport.tpl', $tplArr);
        }*/
    }

    function actionAddUnit(){
        $name = trim($_REQUEST['name']);
        $info = $this->objReport->getUnit($name);
        if(empty($info)){
            $re = $this->objReport->addUnit($name);
            if($re){
                 $this->jsonOutPut(0,'ok');
            }else{
                 $this->jsonOutPut(1,'添加失败！');
            }
        }else{
            $this->jsonOutPut(1,'已经存在相同名称的单位');
        }
    }

    function actionSaveReport()
    {

        $params = $_REQUEST['params'];
        $params = json_decode($params,true);
        $res=ConstManager::checkName(trim($params['basereport']['cn_name']),20);
        if($res===false){
            $this->jsonOutPut(1,'报表名必须是中英文、数字、小括号或者下划线且不超过20个字符');
            exit();
        }
        $res=ConstManager::checkWords(trim($params['basereport']['cn_name']),'spam');
        if($res===false){
            $this->jsonOutPut(1,'报表名称不能包含spam关建字');
            exit();
        }
        if(!Yii::app()->user->isProducer()){
            $this->jsonOutPut(1,'只有分析师组才可以编辑报表哦');
            exit();
        }
     /*   if(!$this->objReport->isOwner($_REQUEST['id'])){
            $this->jsonOutPut(1,'只有本人才可以编辑报表哦');
            exit();
        }*/




        $checkData = $this->objReport->checkReport($params['basereport']['cn_name'], $params['id']);
    /*    $first_munu = $params['basereport']['first_menu'];
        $second_menu = $params['basereport']['second_menu'];

        if (empty($first_munu) && empty($second_menu)) {
            $this->visual->jsonOutPut(-1, '一级菜单和二级菜单必须存在');
            exit;
        }
        $retuMen = $this->objMenu->selectBymenuName($first_munu, $second_menu);

        $menuId = $retuMen['id'];

        $arrTableId = explode(',', $retuMen['table_id']);

        if (empty($menuId)) {
            $this->visual->jsonOutPut(-1, '菜单不存在');
            exit;
        }
    */
        if (!empty($checkData)) {
            $this->jsonOutPut(-1, '已经存在相同名称的报表');
            exit;
        }
       // $this->debug($params);
        if (empty($params['id'])) {
            $id = $this->objReport->saveReport($params);

            if($params['basereport']['auth']){
                $authObj = new AuthManager();
                $authObj->syncPoint($id);
            }
        } else {
            //获取元报表名称
            $reprotInfo = $this->objReport->getReoport($params['id']);
            $srcName = $params['id']."_".$reprotInfo['cn_name'];
            $newName = $params['id']."_".$params['basereport']['cn_name'];
            //调用developer名称更改接口
            $re = $this->objAuth->checkName($srcName,$newName);
            $id = $this->objReport->updateReport($params);
        }
        if (!$id) {
            $this->jsonOutPut(-1, '保存失败');
        }

       /* $arrTableId[] = $id;
        $retuMen = $this->objMenu->updateMenu($menuId, array('table_id' => $arrTableId));
       */
        if ($id) {
            //$url = "/visual/index?menu_id=$menuId&id=$id";
           // $url = "/report/showreport?id=".$id;

            $this->jsonOutPut('0', 'ok', $id);
        } else {

        }
    }

    function actionSaveReportCustom()
    {
        $params = $_REQUEST['params'];
        $params = json_decode($params, true);
        $res    = ConstManager::checkName(trim($params['basereport']['cn_name']), 20);
        if ($res === false) {
            $this->jsonOutPut(1, '报表名必须是中英文、数字、小括号或者下划线且不超过20个字符');
            exit();
        }
        $res = ConstManager::checkWords(trim($params['basereport']['cn_name']), 'spam');
        if ($res === false) {
            $this->jsonOutPut(1, '报表名称不能包含spam关建字');
            exit();
        }

        $checkData = $this->objReport->checkReportCustom($params['basereport']['cn_name'], $params['id']);

        if (!empty($checkData)) {
            $this->jsonOutPut(-1, '已经存在相同名称的报表');
            exit;
        }

        $id = $this->objReport->saveReportCustom($params);

        if ($id) {
            $this->jsonOutPut('0', 'ok', $id);
        } else {
            $this->jsonOutPut(-1, '保存失败');
        }
    }

    function actionGetAuth(){
        $id=$_REQUEST['id'];
        if(empty($id)){
            $this->jsonOutPut(1);
            exit();
        }
        $res=$this->objReport->getReoport($id);
        $group=explode(',',$res['auth']);
        $this->jsonOutPut(0,'',$group);
    }


    function actionSaveAuth(){

        $res=Yii::app()->user->isaudit();
        if(!$res){
            $this->jsonOutPut(1,'只有审核组的人才可以修改~');
            exit();
        }
        $id=$_REQUEST['id'];
        $group=$_REQUEST['group'];
        if(empty($id) ){
            $this->jsonOutPut(1);
            exit();
        }
        if(! is_array($group)){
            $this->jsonOutPut(1,'请至少保留一个权限组');
            exit();
        }

        $res=$this->objReport->saveReoportbyAuth($id,implode(',',$group));

        $this->jsonOutPut($res);
    }




    /*获取报表审核状态*/
    function actionGetReportPower($id){
        $id = $_REQUEST['id']?$_REQUEST['id']:0;
        if($id){
            $status = $this->objAuth->checkStatus($id);
            $this->jsonOutPut(0,'success',array('code'=>$status));
        }else{
            $this->jsonOutPut(1,'id为空');
        }
    }
    function actionReportList()
    {
        $visualList = $this->objReport->getReportList($_REQUEST['project'],true);
        $tplArr['project'] = $this->objProject->getProjectList();
        foreach( $visualList  as  $key=>$val){
           foreach($tplArr['project'] as $pid =>$pVal){
               if($pVal['project']  == $val['project']){
                   $visualList[$key]['pid'] = $pVal['id'];
                   $visualList[$key]['pname'] = $pVal['cn_name'];
               }
           }
        }
        //面包屑导航
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');

        if(isset($_REQUEST['project'])){
            $indexStr[] = array('href'=>"../project/index",'content'=>'管理工具');
            $indexStr[] = array('href'=>"../project/index",'content'=>'项目管理');
            $indexStr[] = array('href'=>"#",'content'=>'查看报表');
        }else{
            $indexStr[] = array('href'=>"reportlist",'content'=>'管理工具');
            $indexStr[] = array('href'=>"#",'content'=>'报表管理');
        }

        $tplArr['guider'] = $indexStr;
        $tplArr['visualList'] = $visualList;
        $tplArr['projectname'] = $_REQUEST['project'];
        $this->render('report/reportlist.tpl', $tplArr);

    }
    //2015-06-29 报表复制功能 更改 报表名称 报表创建人 报表审核状态
    function actionCopyReport(){
        $id=$_REQUEST['id']?$_REQUEST['id']:0;
        if($id){
            $config = $this->objReport->getReoport($id);
            $cn_name = 'copy_'.$config['cn_name'];
            $config['cn_name'] = $cn_name;
            $config['modify_user'] = '';//报表编辑者
            $config['params']['basereport']['cn_name'] = $cn_name;

            //判断是否已经复制报表
            $checkData = $this->objReport->checkReport($cn_name);
            if(!empty($checkData)){
                $this->jsonOutPut(1,'此报表已复制过，请查看报表：'.$cn_name);
                return;
            }
            $project = $config['project'];
            $id = $this->objReport->saveReport($config['params'],$project);
            if($id){
                //审核状态
                $this->objAuth->syncPoint($id);
                $this->jsonOutPut(0,'success');
            } else{
                $this->jsonOutPut(1,'复制报表失败');
            }
 
        }else{
            $this->jsonOutPut(1,'id为空');
        }
    }


    public function actionDeleteReport($id = '')
    {
        ob_start();
        //开放上下线报表的权限
        /*if(! $this->objReport->isOwner($id)){
            $this->jsonOutPut(1,'只有本人才可以下线报表哦~');
            exit();
        }*/
        $status = 1;

        $res = $this->objReport->deleteReport($id);
        if ($res == true)
            $status = 0;
        ob_end_clean();

        echo $this->jsonOutPut($status);

    }

    public function actionDeleteCollectCustom($id = '')
    {
        ob_start();
        $status = 1;

        $res = $this->objReport->deleteReportCustom($id);
        if ($res == true)
            $status = 0;
        ob_end_clean();

        echo $this->jsonOutPut($status);
    }

    public function actionUpReport($id = '')
    {
        /*if(! $this->objReport->isOwner($id)){
            $this->jsonOutPut(1,'只有本人才可以上线报表哦~');
            exit();
        }
        */
        $status = 1;
        $res = $this->objReport->upReport($id);
        if ($res == true)
            $status = 0;
        echo $this->jsonOutPut($status);
    }

    public function actionGetMenu($id = '')
    {

        $res = $this->objMenu->getMenuByReoprt($id);
        $nameRes = $this->objAuth->getChineseUserNamebyReport($id);
        $stutus = 0;
        if ($res === false)
            $status = 1;
        if (empty($res))
            $res = array();
        $data = array('menu' => $res, 'collect' => $nameRes);

        echo $this->jsonOutPut(0, '', $data);

    }

    public function actionDeleteCollect($id)
    {
        $res = $this->objAuth->deleteFavorites($id);
        if ($res) {
            $this->jsonOutPut(0, '取消收藏成功');
        } else {
            $this->jsonOutPut(1, '取消收藏失败');
        }

    }

    #收藏报表
    function actionAddCollect()
    {
        $id = $_REQUEST['id'];

        $res = $this->objAuth->addFavorites($id);
        if ($res) {
            $this->jsonOutPut(0, '收藏成功');
        } else {
            $this->jsonOutPut(1, '收藏失败');
        }
    }

    #收藏报表 callBack
    function actionAddCollectCallBack()
    {
        echo $_REQUEST['callback'] . '(';
        $this->actionAddCollect();
        echo ')';
    }

    #取消收藏报表 callBack
    function actionDeleteCollectCallBack()
    {
        echo $_REQUEST['callback'] . '(';
        $this->actionDeleteCollect($_REQUEST['id']);
        echo ')';
    }

    #校验sql
    function actionCheckSql(){
        $params = $_REQUEST;
        //调用
        $params['check']=true;
        $result = $this->objFackcube->get_fakecube("custom_query_app",$params,true);
        if($result['status'] == 0){
            $this->jsonOutPut(0,'',$result);
        } else {
            $this->jsonOutPut(1,$result['msg']);
        }

    }

    function actionTest(){
        $this->render('report/test.tpl', array());
    }
}
