<?php
class ProjectController extends Controller{
	function  __construct(){
		$this->objFackcube=new FackcubeManager();
		$this->objAuth=new AuthManager();
		$this->objProject=new ProjectManager();
        $this->objBehavior = new BehaviorManager();

	}

	function actionComments(){

        $data['project']=$this->objFackcube->get_project_list();
        //面包屑效果
        $indexStr[] = array('href'=>"/visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"/visual/toolguider",'content'=>'常用工具');
        $indexStr[] = array('href'=>"#",'content'=>'报表注释');
        $data['guider'] = $indexStr;
		$this->render('reporttip/index.html',$data);

	}
	function actionIndex(){
		if($_GET['type']=='myname'){
			$data['list'] =$this->objFackcube->get_project_list(Yii::app()->user->username);
		}else{
			$data['list'] =$this->objFackcube->get_project_list();
		}
		$data['cn_name'] =$this->user->name;
		//project 对应enname cn_name对应cnname
        //面包屑导航
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"index",'content'=>'管理工具');
        $indexStr[] = array('href'=>"#",'content'=>'项目管理');
        $data['super'] = $this->objAuth->isSuper();
        $data['guider'] = $indexStr;
		$this->render('project/projectlist.tpl',$data);
	}

    function actionReal()
    {
        if ($_GET['type'] == 'myname') {
            $data['list'] = $this->objFackcube->get_real_list(Yii::app()->user->username);
        } else {
            $data['list'] = $this->objFackcube->get_real_list();
        }
        $data['cn_name'] = $this->user->name;
        //面包屑导航
        $indexStr[]     = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[]     = array('href' => "index", 'content' => '管理工具');
        $indexStr[]     = array('href' => "#", 'content' => '实时管理');
        $data['super']  = $this->objAuth->isSuper();
        $data['guider'] = $indexStr;
        $this->render('project/reallist.tpl', $data);
    }

	function actionSaveRun(){
		$info=$_REQUEST['runinfo'];
		$project=$info['project'];
		$arrHql=$info['run_module'];
		$start_time=$info['start_time'];
		$end_time=$info['end_time'];
		$step = $info['step'];
		$interval=strtotime($end_time)-strtotime($start_time);
		if($interval<0){
			$this->jsonOutPut(1,'终止时间必须大于起始时间');
			exit();
		}
        /*
		if($interval>86400*30){
			$this->jsonOutPut(1,'一次性启动项目不可超过30天');
			exit();
        }*/

		$res=$this->objProject->saveRunList($project,$arrHql,$start_time,$end_time,$step);
        $this->objBehavior->addUserBehaviorToLog('','','/project/saverun',$info);
        echo json_encode($res);

	}

    function actionSaveRealRun()
    {
        $info       = $_REQUEST['runinfo'];
        $project    = $info['project'];
        $arrHql     = $info['run_module'];
        $start_time = $info['start_time'];
        $end_time   = $info['end_time'];
        $step       = $info['step'];

        $interval = strtotime($end_time) - strtotime($start_time);
        if ($interval < 0) {
            $this->jsonOutPut(1, '终止时间必须大于起始时间');
            exit();
        }

        if ($interval > 86400 * 2) {
            $this->jsonOutPut(1, '一次性启动项目不可超过2天');
            exit();
        }

        $res = $this->objProject->saveRealRunList($project, $arrHql, $start_time, $end_time, $step);

        $this->objBehavior->addUserBehaviorToLog('', '', '/project/saverun', $info);

        echo json_encode($res);
    }

	function actionSaveTopoRun() {
        set_time_limit(10000);
        $info = $_REQUEST['runinfo'];
        $task = $info['task'];
        $time = $info['time'];
        if (!$task || !$time) {
            $this->jsonOutPut(1,'任务名称与重跑时间必须他填写');
            exit();
        }
        $res=$this->objProject->saveTopoRunList($task, $time);
        $this->objBehavior->addUserBehaviorToLog('','','/project/saveTopoRun',$info);
        echo json_encode($res);
    }

	/*
	保存hql接口
	*/
	public  function actionSaveHql(){
		$hqlInfo=$_REQUEST;
		$this->user = Yii::app()->user;
		$hqlInfo['creater'] = $this->user->username;
		$hqlInfo['editor']  = $this->user->username;
		$res=$this->objFackcube->get_fakecube('save_hql_params',$hqlInfo,true);
        echo json_encode($res);

	}

    function actionRealRunlist()
    {
        $project         = $_REQUEST['project'];
        $data['project'] = $project;
        $data['list']    = $this->objProject->getRealRunList($project);
        $data['module']  = $this->objProject->getRealModuleByModule($project);

        //面包屑效果
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "index", 'content' => '管理工具');
        $indexStr[] = array('href' => "index", 'content' => '实时管理');
        $indexStr[] = array('href' => "#", 'content' => '运行详情');

        $data['guider'] = $indexStr;
        $this->render('project/realstart.tpl', $data);
    }

	function actionRunlist(){
		$project=$_REQUEST['project'];
		$data['project'] = $project;
		$data['list']=$this->objProject->getRunlist($project);
		if(!empty($project))
			$data['module']=$this->objFackcube->get_hql(array('project'=>$project));

        //面包屑效果
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"index",'content'=>'管理工具');
        $indexStr[] = array('href'=>"index",'content'=>'项目管理');
        $indexStr[] = array('href'=>"#",'content'=>'运行详情');

        $data['guider'] = $indexStr;
		$this->render('project/start.tpl',$data);
	}
	public  function actionGetall(){
		$project=$_REQUEST['project'];
		$res=$this->objProject->getMetricandGroup($project);
		$this->jsonOutPut(0,'',$res);

	}
	public function actionGetComments(){
		$project=$_REQUEST['project'];
		$column=$_REQUEST['column'];
		$res=$this->objProject->getProjectComment($project,$column);
		$output='';
		if(is_array($res)){
			$tmpres=array();
			foreach($res as $k=>$v){
				$tmpres[]=$k.":".$v;
             }
			$output=implode("\n",$tmpres);

		}

		$this->jsonOutPut('','',$output);
    }

    public function actionGetCommentsisReplaced(){
        $project=$_REQUEST['project'];
        $column=$_REQUEST['column'];
        $res=$this->objProject->getProjectCommentisReplaced($project,$column);
        $output=$res;

        $this->jsonOutPut('','',$output);
    }

	public  function  actionSaveComments(){
		$data=$_REQUEST['data'];
		$project=$data['project'];
		$column=$data['column'];
		$comments=$data['comment'];
        $isReplace=$data['isReplace'];

	/*	$project='mob_content';
		$column='client_device';
		$comments='android:安卓';*/
        //将报表注释信息保存为json串。

		foreach ( explode("\n",$comments) as $v) {
			if(empty($v))
				continue;
			$tmp=explode(":",$v);
			//数据拆出的数级长度大于2 以最近一个拆出的字符串为准
			if(count($tmp) >2){
				$tmp1 = end($tmp);
				array_pop($tmp);
				$tmp0 = implode(":",$tmp); 
				$arrComments[trim($tmp0)]=trim($tmp1);
			}else if(count($tmp) ==2 ){
				$arrComments[trim($tmp[0])]=trim($tmp[1]);
			}else{
				if(count($tmp)!=2){
					$this->jsonOutPut(1,$v.'不符合规范');
					exit();
				}
			}
			
			

		}
		$res=$this->objProject->saveProjectComment($project,$column,$arrComments,$isReplace);
		$this->jsonOutPut($res);

	}

    function actionRealMain()
    {
        $obj = new AuthManager();
        if (!$obj->isProducer()) {
            $this->jsonOutPut(1, '只有分析师才可以新建项目哦~');
            exit();
        }
        $schedule_interval = $this->objFackcube->get_real_schedule_interval();
        $source_db         = $this->objFackcube->get_real_source_db();
        $target_db         = $this->objFackcube->get_real_target_db();

        $field_type                         = $this->objFackcube->get_fakecube('get_field_type', array());
        $tplArr['field_type']               = json_encode($field_type['data']);
        $tplArr['schedule_interval_offset'] = json_encode($schedule_interval['data']);
        $tplArr['schedule_interval']        = $schedule_interval['data'];
        $tplArr['source_db']                = $source_db['data'];
        $tplArr['target_db']                = $target_db['data'];

        //面包屑效果
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "index", 'content' => '管理工具');
        $indexStr[] = array('href' => "index", 'content' => '实时管理');
        $indexStr[] = array('href' => "#", 'content' => '添加实时项目');

        $tplArr['guider']  = $indexStr;
        $tplArr['is_edit'] = false;
        $this->render('project/realmain.tpl', $tplArr);

    }

	function actionMain(){
		$obj=new AuthManager();
		if(!$obj->isProducer()){
			$this->jsonOutPut(1,'只有分析师才可以新建项目哦~');
			exit();
		}
		$schedule_interval=$this->objFackcube->get_fakecube('get_schedule_interval',array());

		$field_type=$this->objFackcube->get_fakecube('get_field_type',array());
		$tplArr['field_type'] = json_encode($field_type['data']);
		$tplArr['schedule_interval_offset'] =  json_encode($schedule_interval['data']);
		$tplArr['schedule_interval']=$schedule_interval['data'];

        //面包屑效果
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"index",'content'=>'管理工具');
        $indexStr[] = array('href'=>"index",'content'=>'项目管理');
        $indexStr[] = array('href'=>"#",'content'=>'添加项目');

        $tplArr['guider'] = $indexStr;
        $tplArr['is_edit'] = false;
		$this->render('project/main.tpl',$tplArr);

	}

    function actionRealCubeeidtor()
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

        $tplArr['id']              = $_REQUEST['id'];
        $res                       = $this->objFackcube->get_real_app_conf(array('project' => $project, 'get_hql' => 1), true);
        $tplArr['is_core_project'] = 0;
        $tplArr['config']          = json_encode($res['data']);
        $tplArr['msg']             = json_encode($res['msg']);
        $schedule_interval         = $this->objFackcube->get_real_schedule_interval();
        $source_db                 = $this->objFackcube->get_real_source_db();
        $target_db                 = $this->objFackcube->get_real_target_db();

        $field_type                         = $this->objFackcube->get_fakecube('get_field_type', array());
        $tplArr['field_type']               = json_encode($field_type['data']);
        $tplArr['schedule_interval']        = $schedule_interval['data'];
        $tplArr['schedule_interval_offset'] = json_encode($schedule_interval['data']);
        $tplArr['source_db']                = $source_db['data'];
        $tplArr['target_db']                = $target_db['data'];
        $referUrl                           = $_SERVER['HTTP_REFERER'];
        //面包屑效果
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');

        $indexStr[] = array('href' => "index", 'content' => '管理工具');
        $indexStr[] = array('href' => "index", 'content' => '实时管理');
        $indexStr[] = array('href' => "#", 'content' => '编辑项目');

        $tplArr['guider']   = $indexStr;
        $tplArr['authtype'] = $res['data']['run']['authtype'];
        $tplArr['authuser'] = $res['data']['run']['authuser'];
        $tplArr['is_edit']  = true;

        $this->render('project/editorreal.tpl', $tplArr);
    }

	function actionCubeeidtor(){
		if(!$this->objAuth->isProducer()){
			$this->jsonOutPut(1,'只有分析师才能查看项目');
			exit();
		}


		$project = $_REQUEST['project'];
		if(empty($project)){
			$this->jsonOutPut(1);
			exit;
		}

		$tplArr['id'] = $_REQUEST['id'];
		$res=$this->objFackcube->get_app_conf(array('project'=>$project,'get_hql'=>1),true);
		//判断是否是核心项目
        $coreProject = ['trade', 'subject_index', 'core_data'];
        $projectEname = $res['data']['project'][0]['name'];
        $tplArr['is_core_project'] = 0;
        if (in_array($projectEname, $coreProject)) {
            $tplArr['is_core_project'] = 1;
        }
		$tplArr['config'] = json_encode($res['data']);
		$tplArr['msg'] = json_encode($res['msg']);
		$schedule_interval=$this->objFackcube->get_fakecube('get_schedule_interval',array());

		$field_type=$this->objFackcube->get_fakecube('get_field_type',array());
		$tplArr['field_type'] = json_encode($field_type['data']);
		$tplArr['schedule_interval']=$schedule_interval['data'];
		$tplArr['schedule_interval_offset'] =  json_encode($schedule_interval['data']);
        $referUrl = $_SERVER['HTTP_REFERER'];
        //面包屑效果
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');

        if(strpos($referUrl,'reportlist')!==false){
            $indexStr[] = array('href'=>"../report/reportlist",'content'=>'管理工具');
            $indexStr[] = array('href'=>"../report/reportlist",'content'=>'报表管理');
        }else{
            $indexStr[] = array('href'=>"index",'content'=>'管理工具');
            $indexStr[] = array('href'=>"index",'content'=>'项目管理');
        }
        $indexStr[] = array('href'=>"#",'content'=>'编辑项目');

        $tplArr['guider'] = $indexStr;
		$tplArr['authtype']=$res['data']['run']['authtype'];
		$tplArr['authuser']=$res['data']['run']['authuser'];
        $tplArr['is_edit'] = true;

		$this->render('project/editorproject.tpl',$tplArr);

	}

    function actionHistoryAppConfLog()
    {
        $historyLog = $this->objFackcube->getHistoryAppConfLog($_REQUEST['project'], $_REQUEST['category_name'], $_REQUEST['groupname'], intval($_REQUEST['num']));

        $historyLogData = [];
        foreach ($historyLog as $currentLog) {
            $params               = json_decode($currentLog['other_params'], true);
            $currentLog['hql']    = isset($params['hql']) ? $params['hql'] : '未能正确获取HQL';
            $currentLog['editor'] = strstr($currentLog['editor'], '@', true);
            $historyLogData[]     = $currentLog;
        }

        $data = array_chunk(is_array($historyLogData) ? $historyLogData : [], 4);

        $this->jsonOutPut(0, 'success', $data);
    }

    function actionHistoryConfLog()
    {
        $historyLog = $this->objFackcube->getHistoryConfLog($_REQUEST['project'], intval(intval($_REQUEST['num'])));

        if (isset($historyLog[0]) && (strtotime(date('Y-m-d H:i:s')) - strtotime($historyLog[0]['updated_at']) <= 1800)) {
            $data['creater']    = strstr($historyLog[0]['creater'], '@', true);
            $data['updated_at'] = $historyLog[0]['updated_at'];
            $this->jsonOutPut(0, 'success', $data);
        } else {
            $this->jsonOutPut(1, '暂无数据');
        }
    }

	function actionGetGroups(){
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
		foreach($searchArr as $key=>$val){
			$searchArr[$key] = trim($val);
		}

		echo  json_encode($this->objFackcube->get_profile($searchArr,true));
	}

    function actionGetRealGroups()
    {
        $searchArr = $_REQUEST;
        foreach ($searchArr as $key => $val) {
            $searchArr[$key] = trim($val);
        }

        $data = $this->objFackcube->get_real_profile($searchArr);
        $this->jsonOutPut(0, 'success', $data);
    }

    function actionSaveRealProject()
    {
        if (!$this->objAuth->isProducer()) {
            $this->jsonOutPut(1, '只有分析师才能保存项目');
            exit();
        }
        $config                   = $_REQUEST['config'];
        $config['run']['creater'] = $this->user->username;

        $params = [
            'project' => $config,
        ];

        if (isset($_REQUEST['id'])) {
            $params['id'] = $_REQUEST['id'];
        }

        list($status, $msg) = $this->objFackcube->save_real_project($params, true);

        if ($status) {
            $this->jsonOutPut(0, 'success');
        } else {
            $this->jsonOutPut(1, $msg);
        }
    }

    function actionCheckTargetTable()
    {
        $target_db    = $_REQUEST['db'];
        $target_table = $_REQUEST['table'];

        $data = $this->objFackcube->check_real_target_table($target_db, $target_table);

        return $this->jsonOutPut($data);
    }

	function actionSaveProject(){

		if(!$this->objAuth->isProducer()){
			$this->jsonOutPut(1,'只有分析师才能保存项目');
			exit();
		}
		$config=$_REQUEST['config'];
		$config['run']['creater']=$this->user->username;

		$params = array(
			'project'=> json_encode($config),
		);


		if(isset($_REQUEST['id'])){
			$params['id'] = $_REQUEST['id'];
		}
		$res=$this->objFackcube->save_project($params,true);

        if(empty($params['id'])){
            $this->objBehavior->addUserBehaviorToLog('','','/project/addproject/project_id/',$config);
        }else{
            $this->objBehavior->addUserBehaviorToLog('','','/project/updateproject/project_id/'.$params['id'],$config);
        }

		echo json_encode($res);
	}


	//2015-04-20
	function actionDimConf(){
		$project=$_REQUEST['project'];
		$url = "get_project_dim_conf";
		$data['project'] = $project;
		$data['result']=$this->objFackcube->get_fakecube($url,array('project_name'=>$project));
		/*echo "<pre>";
		print_r(json_encode($data));exit();*/
        //面包屑效果
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"index",'content'=>'管理工具');
        $indexStr[] = array('href'=>"index",'content'=>'项目管理');
        $indexStr[] = array('href'=>"#",'content'=>'配置查询');

        $data['guider'] = $indexStr;

		$this->render('project/dimconf.tpl',$data);

	}

	//2015－07-03
	function actionKillTask(){
		$data = $_REQUEST;
		$url="kill_task";
		$res=$this->objFackcube->get_fakecube($url,$data);

        if($res['status']=='0'){
            $this->objBehavior->addUserBehaviorToLog('','','/project/killtask/'.$data['serial'],$data);
        }

		echo json_encode($res);
	}

	//2015－10-20
	function actionSetReady(){
		$data = $_REQUEST;
		$url="set_ready";
		$res=$this->objFackcube->get_fakecube($url,$data);
		if($res['status']=='0'){
			$this->objBehavior->addUserBehaviorToLog('','','/project/set_ready/'.$data['serial'],$data);
		}

		echo json_encode($res);
	}

    function actionCheckRealProjectData($item_val = "", $hql_type = "", $store_type = "", $item_type = "")
    {
        $info = array("val" => $item_val, "hql_type" => $hql_type, "storetype" => $store_type, "type" => $item_type);

        list($status, $msg) = $this->objFackcube->check_real_name($info);

        if ($status) {
            $this->jsonOutPut(0, 'success');
        } else {
            $this->jsonOutPut(1, $msg);
        }
    }

    function actionCheckProjectData($item_val="",$hql_type="",$store_type="",$item_type=""){
        $reqArray = array("val"=>$item_val,"hql_type"=>$hql_type,"storetype"=>$store_type,"type"=>$item_type);

        $return = $this->objFackcube->get_fakecube("check_name",$reqArray);
        echo json_encode($return);
    }
    function actionSetpriority(){
        $data = $_REQUEST;		
        $res= $this->objProject->updatepriority($data['id'],$data['number']);
        $this->jsonOutPut(0,'操作成功',array());
    }
}

