<?php

class ProjectAPiController extends Controller
{
    private $objFackcube = null;
    private $objAuth = null;
    private $objProject = null;
    private $objBehavior = null;

    function __construct()
    {
        $this->objFackcube = new FackcubeManager();
        $this->objAuth     = new AuthManager();
        $this->objProject  = new ProjectManager();
        $this->objBehavior = new BehaviorManager();
    }

    # 新版 2017-10-10 全部、个人的分页、搜索功能
    function actionIndex()
    {
        if ($_GET['type'] == 'myname') {
            $AllData = $this->objFackcube->get_project_list(Yii::app()->user->username);
        } else {
            $AllData = $this->objFackcube->get_project_list();
        }
        $data['cn_name'] = $this->user->name;
        $data['super']   = $this->objAuth->isSuper();

        // 过滤数据
        $filter = $_GET['filter'];
        if ($filter) {
            $filterData = array_filter($AllData, function ($v) use ($filter) {
                return strripos($v['cn_name'], $filter) !== false
                    || strripos($v['project'], $filter) !== false
                    || strripos($v['creater'], $filter) !== false;
            });
        } else {
            $filterData = $AllData;
        }

        $pageSize = is_numeric($_GET['pageSize']) ? intval($_GET['pageSize']) : 10;
        $page     = is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
        $pageData = new CArrayDataProvider($filterData, [
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        // currentPage 当前页
        // currentPageCount 当前页数目(一般情况下等于pageSize，最后一页可能不等于pageSize)
        // pageSize 每页条数
        // totalPages 所有的页数
        // totalCount 所有的条数
        $totalCount               = $pageData->getTotalItemCount();
        $data['currentPage']      = $page;
        $data['currentPageCount'] = $pageData->getItemCount();
        $data['pageSize']         = $pageSize;
        $data['totalPages']       = ceil($totalCount / $pageSize);
        $data['totalCount']       = $totalCount;
        $data['list']             = $pageData->getData();

        $this->jsonOutPut(0, '', $data);
    }

    # 新版 2017-10-10 转换报表负责人
    function actionChangeUser()
    {
        $id   = $_GET['id'];
        $user = $_GET['user'];

        $idPattern    = "/^(?:\d+)(?:,\d+)*$/i";
        $emailPattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if (preg_match($idPattern, $id) && preg_match($emailPattern, $user)) {
            $id = explode(',', $id);

            if ($this->objProject->updateCreateUser($id, $user)) {
                $this->jsonOutPut(0, '更新成功');
            } else {
                $this->jsonOutPut(1, '更新失败');
            }
        } else {
            $this->jsonOutPut(1, '参数异常');
        }
    }

    function actionSaveRun()
    {
        $info       = $_REQUEST['runinfo'];
        $project    = $info['project'];
        $arrHql     = $info['run_module'];
        $start_time = $info['start_time'];
        $end_time   = $info['end_time'];
        $step       = $info['step'];
        $interval   = strtotime($end_time) - strtotime($start_time);
        if ($interval < 0) {
            $this->jsonOutPut(1, '终止时间必须大于起始时间');
            exit();
        }
        /*
		if($interval>86400*30){
			$this->jsonOutPut(1,'一次性启动项目不可超过30天');
			exit();
        }*/

        $res = $this->objProject->saveRunList($project, $arrHql, $start_time, $end_time, $step);
        $this->objBehavior->addUserBehaviorToLog('', '', '/project/saverun', $info);
        echo json_encode($res);

    }

    function actionSaveTopoRun()
    {
        set_time_limit(10000);
        $info = $_REQUEST['runinfo'];
        $task = $info['task'];
        $time = $info['time'];
        if (!$task || !$time) {
            $this->jsonOutPut(1, '任务名称与重跑时间必须他填写');
            exit();
        }
        $res = $this->objProject->saveTopoRunList($task, $time);
        $this->objBehavior->addUserBehaviorToLog('', '', '/project/saveTopoRun', $info);
        echo json_encode($res);
    }

    public function actionSaveHql()
    {
        $hqlInfo            = $_REQUEST;
        $this->user         = Yii::app()->user;
        $hqlInfo['creater'] = $this->user->username;
        $res                = $this->objFackcube->get_fakecube('save_hql_params', $hqlInfo, true);
        $this->jsonOutPut(0, '', $res);

    }

    # 新版 2017-10-12  运行列表页
    function actionRunlist()
    {
        $project = $_GET['project'];
         
        $AllData =  $this->objProject->getRunlist($project);
        $data['cn_name'] = $this->user->name;
        $data['super']   = $this->objAuth->isSuper();

        // 过滤数据
        $filter = $_GET['filter'];
        if ($filter) {
            $filterData = array_filter($AllData, function ($v) use ($filter) {
                return strripos($v['app_name'], $filter) !== false
                    || strripos($v['project'], $filter) !== false
                    || strripos($v['run_module'], $filter) !== false
                    || strripos($v['creater'], $filter) !== false
                    || strripos($v['submitter'], $filter) !== false 
                    || strripos($v['priority'], $filter) !== false
                    || strripos($v['status'], $filter) !== false
                    || strripos($v['id'], $filter) !== false
                    ;
            });
        } else {
            $filterData = $AllData;
        }

        $pageSize = is_numeric($_GET['pageSize']) ? intval($_GET['pageSize']) : 10;
        $page     = is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
        $pageData = new CArrayDataProvider($filterData, [
            'pagination' => [
                'pageSize' => $pageSize,
            ],
        ]);

        // currentPage 当前页
        // currentPageCount 当前页数目(一般情况下等于pageSize，最后一页可能不等于pageSize)
        // pageSize 每页条数
        // totalPages 所有的页数
        // totalCount 所有的条数
        $totalCount               = $pageData->getTotalItemCount();
        $data['currentPage']      = $page;
        $data['currentPageCount'] = $pageData->getItemCount();
        $data['pageSize']         = $pageSize;
        $data['totalPages']       = ceil($totalCount / $pageSize);
        $data['totalCount']       = $totalCount;
        $data['list']             = $pageData->getData();

        $this->jsonOutPut(0, '', $data);
    }

    function actionRunModule()
    {
        $project = $_REQUEST['project'];

        if (!empty($project)) {
            $data = $this->objFackcube->get_hql(['project' => $project]);
        } else {
            $data = [];
        }

        $this->jsonOutPut(0, '', $data);
    }
    
    # 新版 运行详情 批量操作接
    function actionRunOprate(){
        //批量操作   1杀死 2
        $type = $_REQUEST['type'];
        $reuestData = $_REQUEST['list'];
        $allData =[];
        foreach ($reuestData as $key=>$val){
            $tmp = explode("&", $val);
            $one=[];
            foreach ($tmp as $item){
                $tmp1 = explode("=", $item);
                $one[$tmp1[0]] = $tmp1[1]; 
            }
            $allData[] = $one;
        }
        $reDdata =[];
        switch ($type){
            case 'kill':
                if(count($allData) >0){
                    foreach ($allData as $data){
                        $url  = "kill_task";
                        $res  = $this->objFackcube->get_fakecube($url, $data);
                        $one =[];
                        $one['name'] = $data['app_name'];
                        if ($res['status'] == '0') {
                            $this->objBehavior->addUserBehaviorToLog('', '', '/project/killtask/' . $data['serial'], $data);
                            $one['status'] = '操作成功';
                        }else{
                            $one['status'] = $res['msg'];
                        }
                        $reDdata[] = $one;
                    }
                }
                break;
            case 'ready':
                if(count($allData) >0){
                    foreach ($allData as $data){
                        $url  = "set_ready";
                        $res  = $this->objFackcube->get_fakecube($url, $data);
                        $one =[];
                        $one['name'] = $data['app_name'];
                        if ($res['status'] == '0') {
                            $this->objBehavior->addUserBehaviorToLog('', '', '/project/set_ready/' . $data['serial'], $data);
                            $one['status'] = '操作成功';
                        }else{
                            $one['status'] = $res['msg'];
                        }
                        $reDdata[] = $one;
                    }
                }
                break;
        }
        $this->jsonOutPut(0, '', $reDdata);
    }

    # 新版 2017-10-12 获取用户运行项目个数 可以运行项目的最大个数
    function actionTaskNum()
    {
        $runNum = $this->objProject->getProjectRunNum(Yii::app()->user->username);

        $this->jsonOutPut(0, '', [
            'user'   => is_numeric($runNum) ? intval($runNum) : '未知',
            'common' => 15,
            'super'  => 60,
        ]);
    }

    public function actionGetall()
    {
        $project = $_REQUEST['project'];
        $res     = $this->objProject->getMetricandGroup($project);
        $this->jsonOutPut(0, '', $res);

    }

    public function actionGetComments()
    {
        $project = $_REQUEST['project'];
        $column  = $_REQUEST['column'];
        $res     = $this->objProject->getProjectComment($project, $column);
        $output  = '';
        if (is_array($res)) {
            $tmpres = array();
            foreach ($res as $k => $v) {
                $tmpres[] = $k . ":" . $v;
            }
            $output = implode("\n", $tmpres);

        }

        $this->jsonOutPut('', '', $output);
    }

    public function actionGetCommentsisReplaced()
    {
        $project = $_REQUEST['project'];
        $column  = $_REQUEST['column'];
        $res     = $this->objProject->getProjectCommentisReplaced($project, $column);
        $output  = $res;

        $this->jsonOutPut('', '', $output);
    }

    public function actionSaveComments()
    {
        $data      = $_REQUEST['data'];
        $project   = $data['project'];
        $column    = $data['column'];
        $comments  = $data['comment'];
        $isReplace = $data['isReplace'];

        /*	$project='mob_content';
            $column='client_device';
            $comments='android:安卓';*/
        //将报表注释信息保存为json串。

        foreach (explode("\n", $comments) as $v) {
            if (empty($v))
                continue;
            $tmp = explode(":", $v);
            //数据拆出的数级长度大于2 以最近一个拆出的字符串为准
            if (count($tmp) > 2) {
                $tmp1 = end($tmp);
                array_pop($tmp);
                $tmp0                     = implode(":", $tmp);
                $arrComments[trim($tmp0)] = trim($tmp1);
            } else if (count($tmp) == 2) {
                $arrComments[trim($tmp[0])] = trim($tmp[1]);
            } else {
                if (count($tmp) != 2) {
                    $this->jsonOutPut(1, $v . '不符合规范');
                    exit();
                }
            }


        }
        $res = $this->objProject->saveProjectComment($project, $column, $arrComments, $isReplace);
        $this->jsonOutPut($res);

    }

    function actionMain()
    {
        $obj = new AuthManager();
        if (!$obj->isProducer()) {
            $this->jsonOutPut(1, '只有分析师才可以新建项目哦~');
            exit();
        }
        $schedule_interval = $this->objFackcube->get_fakecube('get_schedule_interval', array());

        $field_type                         = $this->objFackcube->get_fakecube('get_field_type', array());
        $tplArr['field_type']               = json_encode($field_type['data']);
        $tplArr['schedule_interval_offset'] = json_encode($schedule_interval['data']);
        $tplArr['schedule_interval']        = $schedule_interval['data'];

        //面包屑效果
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "index", 'content' => '管理工具');
        $indexStr[] = array('href' => "index", 'content' => '项目管理');
        $indexStr[] = array('href' => "#", 'content' => '添加项目');

        $tplArr['guider'] = $indexStr;
        $this->render('project/main.tpl', $tplArr);

    }

    function actionCubeeidtor()
    {
        if (!$this->objAuth->isProducer()) {
            $this->jsonOutPut(1, '只有分析师才能查看项目');
            exit();
        }


        $project = $_REQUEST['project'];
        if (empty($project)) {
            $this->jsonOutPut(1);
            exit;
        }

        $tplArr['id'] = $_REQUEST['id'];
        $res          = $this->objFackcube->get_app_conf(array('project' => $project, 'get_hql' => 1), true);
        //判断是否是核心项目
        $coreProject               = ['trade', 'subject_index', 'core_data'];
        $projectEname              = $res['data']['project'][0]['name'];
        $tplArr['is_core_project'] = 0;
        if (in_array($projectEname, $coreProject)) {
            $tplArr['is_core_project'] = 1;
        }
        $tplArr['config']  = json_encode($res['data']);
        $tplArr['msg']     = json_encode($res['msg']);
        $schedule_interval = $this->objFackcube->get_fakecube('get_schedule_interval', array());

        $field_type                         = $this->objFackcube->get_fakecube('get_field_type', array());
        $tplArr['field_type']               = json_encode($field_type['data']);
        $tplArr['schedule_interval']        = $schedule_interval['data'];
        $tplArr['schedule_interval_offset'] = json_encode($schedule_interval['data']);
        $referUrl                           = $_SERVER['HTTP_REFERER'];
        //面包屑效果
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');

        if (strpos($referUrl, 'reportlist') !== false) {
            $indexStr[] = array('href' => "../report/reportlist", 'content' => '管理工具');
            $indexStr[] = array('href' => "../report/reportlist", 'content' => '报表管理');
        } else {
            $indexStr[] = array('href' => "index", 'content' => '管理工具');
            $indexStr[] = array('href' => "index", 'content' => '项目管理');
        }
        $indexStr[] = array('href' => "#", 'content' => '编辑项目');

        $tplArr['guider']   = $indexStr;
        $tplArr['authtype'] = $res['data']['run']['authtype'];
        $tplArr['authuser'] = $res['data']['run']['authuser'];

        $this->render('project/editorproject.tpl', $tplArr);

    }

    function actionGetGroups()
    {
        /*$searchArr = array(
            'hql'=>trim($_REQUEST['hql']),
            'type'=>trim($_REQUEST['type']),
            'hql_type'=>trim($_REQUEST['hql_type']),
            'app_name'=>trim($_REQUEST['app_name']),
            'hql_name'=>trim($_REQUEST['hql_name']),
            'custom_cdate'=>trim($_REQUEST['custom_cdate']),
            'attach'=>trim($_REQUEST['attach']),
            'category_name'=>trim($_REQUEST['category_name'])
        );*/
        $searchArr = $_REQUEST;
        foreach ($searchArr as $key => $val) {
            $searchArr[$key] = trim($val);
        }

        echo json_encode($this->objFackcube->get_profile($searchArr, true));
    }

    function actionSaveProject()
    {

        if (!$this->objAuth->isProducer()) {
            $this->jsonOutPut(1, '只有分析师才能保存项目');
            exit();
        }
        $config                   = $_REQUEST['config'];
        $config['run']['creater'] = $this->user->username;

        $params = array(
            'project' => json_encode($config),
        );


        if (isset($_REQUEST['id'])) {
            $params['id'] = $_REQUEST['id'];
        }
        $res = $this->objFackcube->save_project($params, true);

        if (empty($params['id'])) {
            $this->objBehavior->addUserBehaviorToLog('', '', '/project/addproject/project_id/', $config);
        } else {
            $this->objBehavior->addUserBehaviorToLog('', '', '/project/updateproject/project_id/' . $params['id'], $config);
        }

        echo json_encode($res);
    }

    //2015-04-20
    function actionDimConf()
    {
        $project         = $_REQUEST['project'];
        $url             = "get_project_dim_conf";
        $data['project'] = $project;
        $data['result']  = $this->objFackcube->get_fakecube($url, array('project_name' => $project));
        /*echo "<pre>";
        print_r(json_encode($data));exit();*/
        //面包屑效果
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "index", 'content' => '管理工具');
        $indexStr[] = array('href' => "index", 'content' => '项目管理');
        $indexStr[] = array('href' => "#", 'content' => '配置查询');

        $data['guider'] = $indexStr;

        $this->render('project/dimconf.tpl', $data);

    }

    //2015－07-03
    function actionKillTask()
    {
        $data = $_REQUEST;
        $url  = "kill_task";
        $res  = $this->objFackcube->get_fakecube($url, $data);

        if ($res['status'] == '0') {
            $this->objBehavior->addUserBehaviorToLog('', '', '/project/killtask/' . $data['serial'], $data);
        }

        echo json_encode($res);
    }

    //2015－10-20
    function actionSetReady()
    {
        $data = $_REQUEST;
        $url  = "set_ready";
        $res  = $this->objFackcube->get_fakecube($url, $data);
        if ($res['status'] == '0') {
            $this->objBehavior->addUserBehaviorToLog('', '', '/project/set_ready/' . $data['serial'], $data);
        }

        echo json_encode($res);
    }

    function actionCheckProjectData($item_val = "", $hql_type = "", $store_type = "", $item_type = "")
    {
        $reqArray = array("val" => $item_val, "hql_type" => $hql_type, "storetype" => $store_type, "type" => $item_type);

        $return = $this->objFackcube->get_fakecube("check_name", $reqArray);
        echo json_encode($return);
    }

    function actionSetpriority()
    {
        $data = $_REQUEST;
        $res  = $this->objProject->updatepriority($data['id'], $data['number']);
        $this->jsonOutPut(0, '操作成功', array());
    }
}

