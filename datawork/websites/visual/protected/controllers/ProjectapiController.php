<?php

class ProjectAPiController extends Controller
{
    private $objFackcube = null;
    private $objAuth = null;
    private $objProject = null;
    private $objBehavior = null;
    private $curl = null;
    private $diUrl = 'http://scheduler.qudian.com/';

    function __construct()
    {
        $this->objFackcube = new FackcubeManager();
        $this->objAuth     = new AuthManager();
        $this->objProject  = new ProjectManager();
        $this->objBehavior = new BehaviorManager();
        $this->curl = Yii::app()->curl;
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
        $data['super']   = Yii::app()->user->isSuper();

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

    function actionSaveRun() {
        $info       = $_REQUEST['runinfo'];
        $project    = $info['project'];
        $arrHql     = $info['run_module'];
        $start_time = $info['start_time'];
        $end_time   = $info['end_time'];
        $step       = $info['step'];
        $interval   = strtotime($end_time) - strtotime($start_time);
        $unqJobNames = [];
        if ($interval < 0) {
            $this->jsonOutPut(1, '终止时间必须大于起始时间');
            exit();
        }
        foreach ($arrHql as $hql) {
            $categoryName = substr($hql, 0, strpos($hql, '.'));
            $hqlName =  substr($hql, strpos($hql, '.') + 1);
            $appConf = $this->objProject->getAppConfByAppNameAndCategorynameAndHqlname($project, $categoryName, $hqlName);
            foreach ($appConf as $conf) {
                array_push($unqJobNames, 'cube_' . $conf['id']);
            }
        }
        $params['unq_job_name'] = implode(',', $unqJobNames);
        $params['run_start_time'] = date('Y-m-d H:i:s', strtotime($start_time));
        $params['run_end_time'] = date('Y-m-d H:i:s', strtotime($end_time));
        $params['creater'] = str_ireplace(['@qudian.com', '@qufenqi.com'], '', Yii::app()->user->username);
        $params['ext_json'] = json_encode(['step' => $step]);
        $strparams = http_build_query($params);
        $url = $this->diUrl . 'job/run_job';
        $projectData = $this->curl->post($url,$strparams,'', 60);
        if ($projectData['http_code'] != 200) {
            $this->jsonOutPut(1, 'di接口请求失败', []);
            return;
        }
        $body = json_decode($projectData['body'], true);
        if ($body['status'] != 0) {
            $this->jsonOutPut(1, $body['msg'], []);
            return;
        }
        $this->objBehavior->addUserBehaviorToLog('', '', '/project/saverun', $info);
        $this->jsonOutPut(0, $body['msg'], []);
    }

    function actionSaveRunBack()
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

    function actionRunlist()
    {
        $project = $_GET['project'];
        $jobStatus = '';
        $filter = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
        $page = ($_GET['start'] + $_GET['length']) / $_GET['length'];
        $pageSize = $_GET['length'];
        $runList = $this->objProject->actionGetRunListByDi($project, $filter, $jobStatus, $page, $pageSize);
        $data = [
            'draw' => intval($_GET['draw']),
            'recordsTotal' => intval($runList['totalCount']),
            'recordsFiltered' => intval($runList['totalCount']),
            'data' => $runList['data']
        ];
        echo json_encode($data);
        #$this->jsonOutPut(0, '', $data);
    }

    # 新版 2017-10-12  运行列表页
    function actionRunlistback()
    {
        $project = $_GET['project'];

        $AllData =  $this->objProject->getRunlist($project);
        $data['cn_name'] = $this->user->name;
        $data['super']   = Yii::app()->user->isSuper();

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

    function actionRunOprate() {
        $type = $_REQUEST['type'];
        $reuestData = $_REQUEST['list'];
        $jobStatus = [];
        $jobRunIds = '';
        foreach ($reuestData as $one) {
            $runId = substr($one, 7, strpos($one, '&') - 7);
            $jobRunIds = $jobRunIds . ',' . $runId;
            array_push($jobStatus, substr($one, strpos($one, 'status=') + 7, 1));
        }
        $jobRunIds = trim($jobRunIds, ',');
        switch ($type) {
            case 'kill':
                foreach ($jobStatus as $status) {
                    if (!in_array($status, [1,2,3])) {
                        $this->jsonOutPut(0, '任务不是阻塞、就绪或运行中状态', [['name' => '', 'status'=>'任务不是阻塞、就绪或运行中状态']]);
                        return;
                    }
                }
                $url = $this->diUrl . 'job/kill_job_local';
                $param['job_run_ids'] = $jobRunIds;
                $param['creater'] = str_ireplace(['@qudian.com', '@qufenqi.com'], '', Yii::app()->user->username);
                $strparams = http_build_query($param);
                $res = $this->curl->post($url,$strparams,'', 60);
                if ($res['http_code'] != 200) {
                    $this->jsonOutPut(1, 'di接口请求失败', []);
                    return;
                }
                $body = json_decode($res['body'], true);
                $this->objProject->updateRunLogStatus($jobRunIds, 11);
                $this->jsonOutPut(0, $body['msg'], [['name' => '', 'status' => $body['msg']]]);
                break;
            case 'ready':
                foreach ($jobStatus as $status) {
                    if ($status != 1) {
                        $this->jsonOutPut(0, '任务不是阻塞状态', [['name' => '', 'status'=>'任务不是阻塞状态']]);
                        return;
                    }
                }
                $url = $this->diUrl . 'job/set_job_ready';
                $param['job_run_ids'] = $jobRunIds;
                $param['creater'] = str_ireplace(['@qudian.com', '@qufenqi.com'], '', Yii::app()->user->username);
                $strparams = http_build_query($param);
                $res = $this->curl->post($url,$strparams,'', 60);
                if ($res['http_code'] != 200) {
                    $this->jsonOutPut(1, 'di接口请求失败', []);
                    return;
                }
                $body = json_decode($res['body'], true);
                $this->jsonOutPut(0, $body['msg'], [['name' => '', 'status' => $body['msg']]]);
                break;
        }
    }
    
    # 新版 运行详情 批量操作接
    function actionRunOprateback(){
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

        /*    $project='mob_content';
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
        if (!Yii::app()->user->isProducer()) {
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
        if (!Yii::app()->user->isProducer()) {
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

        if (!Yii::app()->user->isProducer()) {
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
    function actionKillTaskBack()
    {
        $data = $_REQUEST;
        $url  = "kill_task";
        $res  = $this->objFackcube->get_fakecube($url, $data);

        if ($res['status'] == '0') {
            $this->objBehavior->addUserBehaviorToLog('', '', '/project/killtask/' . $data['serial'], $data);
        }

        echo json_encode($res);
    }

    function actionKillTask()
    {
        $data = $_REQUEST;
        if (!in_array($data['status'], [1,2,3])) {
            $this->jsonOutPut(1, '任务不是阻塞、就绪或运行中状态');
            return;
        }
        $url = $this->diUrl . 'job/kill_job_local';
        $param['job_run_ids'] = $data['serial'];
        $param['creater'] = str_ireplace(['@qudian.com', '@qufenqi.com'], '', Yii::app()->user->username);
        $strparams = http_build_query($param);
        $res = $this->curl->post($url,$strparams,'', 60);
        if ($res['http_code'] != 200) {
            $this->jsonOutPut(1, 'di接口请求失败', []);
            return;
        }
        $body = json_decode($res['body'], true);
        if ($body['status'] != 0) {
            $this->jsonOutPut(1, $body['msg'], []);
            return;
        }
        $this->objProject->updateRunLogStatus($data['serial'], 11);
        if ($res['status'] == '0') {
            $this->objBehavior->addUserBehaviorToLog('', '', '/project/killtask/' . $data['serial'], $data);
        }

        echo json_encode($body);
    }

    function actionSetReady() {
        $data = $_REQUEST;
        if ($data['status'] != 1) {
            $this->jsonOutPut(1, '任务不是阻塞状态');
            return;
        }
        $url = $this->diUrl . 'job/set_job_ready';
        $param['job_run_ids'] = $data['serial'];
        $param['creater'] = str_ireplace(['@qudian.com', '@qufenqi.com'], '', Yii::app()->user->username);
        $strparams = http_build_query($param);
        $res = $this->curl->post($url,$strparams,'', 60);
        if ($res['http_code'] != 200) {
            $this->jsonOutPut(1, 'di接口请求失败', []);
            return;
        }
        $body = json_decode($res['body'], true);
        if ($body['status'] != 0) {
            $this->jsonOutPut(1, $body['msg'], []);
            return;
        }
        if ($body['status'] == '0') {
            $this->objBehavior->addUserBehaviorToLog('', '', '/project/set_ready/' . $data['serial'], $data);
        }
        echo json_encode($body);
    }
    //2015－10-20
    function actionSetReadyBack()
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


    function actionOffline() {
        if (!isset($_GET['job_name']) && !isset($_POST['job_name'])) {
            $this->jsonOutPut(1, '缺少job_name');
            return;
        }
        $jobName = $_GET['job_name'] ? $_GET['job_name'] : $_POST['job_name'];
        $appname = explode('.', $jobName);
        if (count($appname) != 3) {
            $this->jsonOutPut(1, 'job_name参数错误');
            return;
        }
        $runModule = $appname[1] . '.' . $appname[2];
        $appname = $appname[0];
        $oldConf = $this->objProject->getMmsConfByAppName($appname);
        if(!$oldConf) {
            $this->jsonOutPut(0, '操作成功', array());
            return;
        }
        $oldConf = json_decode($oldConf, true);
        $runInstance = $oldConf['run']['run_instance']['group'];
        $index = 0;
        $isUpdate = false;
        foreach ($runInstance as $key => $group) {
            if ($group['name'] == $runModule) {
                $index = $key;
                $isUpdate = true;
                break;
            }
        }
        if (!$isUpdate) {
            $this->jsonOutPut(0, '操作成功', array());
            return;
        }
        $newConf = $oldConf;
        unset($newConf['run']['run_instance']['group'][$index]);
        $newConf['run']['run_instance']['group'] = array_values($newConf['run']['run_instance']['group']);
        $this->objProject->updateMmsConfByAppName($appname, json_encode($newConf));
        $this->jsonOutPut(0, '操作成功', array());
    }

    function actionJobNameToJobDetail() {
        if (!isset($_GET['job_name'])) {
            $this->jsonOutPut(1, 'job_name为必传参数');
            return;
        }
        $jobName = explode('.', $_GET['job_name']);
        $id = $this->objProject->getMmsConfAllByAppName($jobName[0]);
        if (!$id) {
            $this->jsonOutPut(1, '找不到对应任务');
            return;
        }
        $id = $id[0]['id'];
        $this->redirect(array("/project/cubeeidtor?project={$jobName[0]}&id={$id}&groupname={$jobName[2]}&category_name={$jobName[1]}"));
    }

    function actionGetByincrement() {
        if (!isset($_GET['end_time']) && !isset($_POST['end_time'])) {
            $this->jsonOutPut(1, 'end_time为必传参数');
            return;
        }
        $endTime = $_GET['end_time'] ? $_GET['end_time'] : $_POST['end_time'];
        if (!$this->checkDateIsValid($endTime)) {
            $this->jsonOutPut(1, 'end_time参数格式错误');
            return;
        }
        $startTime = false;
        if (isset($_GET['start_time']) || isset($_POST['start_time'])) {
            $startTime = $_GET['start_time'] ? $_GET['start_time'] : $_POST['start_time'];
            if (!$this->checkDateIsValid($endTime)) {
                $this->jsonOutPut(1, 'start_time参数格式错误');
                return;
            }
        }
        if (!$startTime) {
            $onlineConfName = $this->getAllOnlineByEndTime($endTime);
        } else {
            $onlineConfName = $this->getIncrementOnlineByStartTimeAndEndTime($startTime, $endTime);
        }
        $result = $this->getDetailOnlineByConfNames($onlineConfName);
        echo json_encode(['status' => 0, 'msg' => 'success', 'data' => $result]);exit;
        #$this->jsonOutPut(0,'',$result);
        #测试pushonline 7

    }

    private function getDetailOnlineByConfNames($confNames) {
        $confs = [];
        $stateMaps = [];
        foreach ($confNames as $conf) {
            array_push($confs, $conf['app_name']);
            $stateMaps[$conf['app_name']] = $conf['state'];
        }
        $details = $this->objProject->getDetailByConfs($confs);
        $result = [];
        foreach ($details as $detail) {
            $one = [
                'job_id' => $detail['job_id'],
                'job_name' => $detail['job_name'],
                'app_name' => 'cube',
                'project_name' => $detail['project_name'],
                'job_file_path' => '/home/apple/bi.analysis/fakecube/src/mms/bin/run_task_single_dispatch.py',
                'job_params' => '',
                'job_desc' => '',
                'creater' => $detail['creater'],
                'tag_depend' => [],
                'tag_store' => [],
                'cron' => '',
                'job_time_func' => '',
                'editor' => $detail['editor'],
                'mod_time' => date('Y-m-d H:i:s', time()),
                'create_time' => $detail['create_time'],
                'state' => $stateMaps[$detail['job_name']]
            ];
            $conf = json_decode($detail['conf'], true);
            $one['job_desc'] = $conf['explain'];
            $one['product_name'] = isset($conf['hive_queue']) ? $conf['hive_queue'] : '';
            $one['cron'] = $conf['schedule_interval'];
            //预警相关设置
            $one['latest_end_time'] = isset($conf['latest_end_time']) && !empty($conf['latest_end_time']) ? $conf['latest_end_time'] : '';
            $one['alarm_users'] = isset($conf['alarm_users']) && !empty($conf['alarm_users']) ? $conf['alarm_users'] : '';
            $one['alarm_type'] = isset($conf['alarm_type']) ? $conf['alarm_type'] : '-1';
            foreach ($conf['tables'] as $dependTable) {
                if ($dependTable['ischecktables'] != 1) {
                    continue;
                }
                if (!$dependTable['time_depend']) {
                    array_push($one['tag_depend'], [
                        'tag' => $dependTable['name'],
                        'time_func' => ''
                    ]);
                    continue;
                }
                $dependTime = explode('/', $dependTable['time_depend']);
                $dependStartTime = $dependTime[0];
                $dependEndTime = $dependTime[1];
                if ($dependStartTime == $dependEndTime) {
                    array_push($one['tag_depend'], [
                        'tag' => $dependTable['name'],
                        'time_func' => str_replace('$HOUR', 'hour', str_replace('$DATE','day', $dependStartTime))
                    ]);
                    continue;
                }
                $start = substr($dependStartTime, strpos($dependStartTime, '(') + 1, strpos($dependStartTime, ')') -strpos($dependStartTime, '(') -1);
                $end = substr($dependEndTime, strpos($dependEndTime, '(') + 1, strpos($dependEndTime, ')') -strpos($dependEndTime, '(') -1);
                $timeFun = strpos($dependStartTime, '$DATE') !== false ? 'day' : 'hour';
                for ($i = $start;$i <= $end;$i++) {
                    array_push($one['tag_depend'], [
                        'tag' => $dependTable['name'],
                        'time_func' => $timeFun . '(' . $i . ')'
                    ]);
                }
            }
            $timeFun = strpos($conf['schedule_interval_offset'], 'day') !== false ? 'day' : 'hour';
            if ($detail['data_table_name']) {
                array_push($one['tag_store'], ['tag' => $detail['data_table_name'], 'time_func' => $timeFun . '(0)']);
            }
            $one['job_time_func'] = $conf['schedule_interval_offset'];
            array_push($result, $one);
        }
        return $result;
    }


    private function getIncrementOnlineByStartTimeAndEndTime($startTime, $endTime) {
        $modify = $this->getIncrementModifyOnlineByStartTimeAndEndTime($startTime, $endTime);
        $createOrDel = $this->getIncrementNewCreateOrDelOnlineByStartTimeAndEndTime($startTime, $endTime);
        $newMap = [];
        foreach ($createOrDel as $conf) {
            array_push($merge, $conf);
        }
        $merge = [];
        foreach ($modify as $key => $conf) {
            if (!in_array($conf['app_name'], $newMap)) {
                array_push($merge, $conf);
            }
        }
        $merge = array_merge($merge, $createOrDel);
        return $merge;
    }

    private function getIncrementModifyOnlineByStartTimeAndEndTime($startTtime, $endTime) {
        $result = [];
        $online = $this->objProject->getAllIncrementModifyOnlineAppConfByStartTimeAndEndTime($startTtime, $endTime);
        foreach ($online as $conf) {
            $conf_json = json_decode($conf['conf'], true);
            $conf_res = [];
            foreach ($conf_json['run']['run_instance']['group'] as $conf_app) {
                $conf_res[] = $conf_app['name'];
            }

            if (isset($conf_json['run']['run_instance']['group']) && in_array($conf['app_name_conf'], $conf_res)) {
                array_push($result, ['app_name' => $conf['app_name'], 'state' => 1]);
            } else {
                array_push($result, ['app_name' => $conf['app_name'], 'state' => 2]);
            }
        }
        return $result;

    }

    private function getIncrementNewCreateOrDelOnlineByStartTimeAndEndTime($startTime, $endTime) {
        $result = [];
        $newOnline = $this->objProject->getAllIncrementOnlineAppConfByStartTimeAndEndTime($startTime, $endTime);
        $oldOnline = $this->objProject->getAllIncrementOnlineAppConfByEndTime($startTime);
        $newOnline = $this->getOnlineMap($newOnline);
        $oldOnline = $this->getOnlineMap($oldOnline);
        foreach ($newOnline as $appName => $conf) {
            if (!isset($oldOnline[$appName])) {
                if (($conf['date_s'] > $endTime) || ($conf['date_e'] < $endTime)) {
                    continue;
                }
                foreach ($conf['run'] as $run) {
                    array_push($result, ['app_name' => $run, 'state' => 1]);
                }
                continue;
            }
            if (($conf['date_s'] != $oldOnline[$appName]['date_s']) || ($conf['date_e'] != $oldOnline[$appName]['date_e'])) {
                if (($conf['date_s'] < $endTime) && ($conf['date_e'] > $endTime)) {
                    foreach ($conf['run'] as $run) {
                        array_push($result, ['app_name' => $run, 'state' => 1]);
                    }
                    continue;
                }
                if (($conf['date_s'] > $endTime) || ($conf['date_e'] < $endTime)) {
                    foreach ($conf['run'] as $run) {
                        array_push($result, ['app_name' => $run, 'state' => 2]);
                    }
                    continue;
                }

            }
            foreach ($conf['run'] as $appConf) {
                if (!in_array($appConf, $oldOnline[$appName]['run'])) {
                    array_push($result, ['app_name' => $appConf, 'state' => 1]);
                }
            }
        }
        foreach ($oldOnline as $appName => $conf) {
            foreach ($conf['run'] as $appConf) {
                if (isset($newOnline[$appName]['run']) && !in_array($appConf, $newOnline[$appName]['run'])) {
                    array_push($result, ['app_name' => $appConf, 'state' => 2]);
                }
            }
        }
        return $result;
    }

    private function getOnlineMap($onlineApps) {
        $onlineMap = [];
        foreach ($onlineApps as $conf) {
            $appName = $conf['appname'];
            $onlineMap[$appName] = [
                'date_e' => $conf['date_e'],
                'date_s' => $conf['date_s'],
                'run' => [],
            ];
            $conf = json_decode($conf['conf'], true);
            $runs = [];
            $runInstance = $conf['run']['run_instance']['group'];
            foreach ($runInstance as $run) {
                array_push($runs, $appName . '.' . $run['name']);
            }
            $onlineMap[$appName]['run'] = $runs;
        }
        return $onlineMap;
    }

    private function getAllOnlineByEndTime($endTime) {
        $allOnline = $this->objProject->getAllOnlineAppConfByEndTime($endTime);
        $result = [];
        $onlineMap = [];
        foreach ($allOnline as $appConf) {
            if (!isset($onlineMap[$appConf['appname']])) {
                $onlineMap[$appConf['appname']] = [];
            }
            $conf = json_decode($appConf['conf'], true);
            $runInstance = $conf['run']['run_instance']['group'];
            foreach ($runInstance as $run) {
                array_push($onlineMap[$appConf['appname']], $run['name']);
            }

        }
        $allAppConf = $this->objProject->getAllAppConf();
        foreach ($allAppConf as $conf) {
            if (!isset($onlineMap[$conf['app_name']]) || !in_array($conf['category_name'] . '.' . $conf['hql_name'], $onlineMap[$conf['app_name']])) {
                array_push($result, ['app_name' => $conf['app_name'] . '.' . $conf['category_name'] . '.' . $conf['hql_name'], 'state' => 2]);
                continue;
            }
            array_push($result, ['app_name' => $conf['app_name'] . '.' . $conf['category_name'] . '.' . $conf['hql_name'], 'state' => 1]);
        }
        return $result;
    }

    private function checkDateIsValid($date, $formats = array("Y-m-d H:i:s"))
    {
        $unixTime = strtotime($date);
        if (!$unixTime) { //strtotime转换不对，日期格式显然不对。
            return false;
        }
//校验日期的有效性，只要满足其中一个格式就OK
        foreach ($formats as $format) {
            if (date($format, $unixTime) == $date) {
                return true;
            }
        }

        return false;
    }
}



