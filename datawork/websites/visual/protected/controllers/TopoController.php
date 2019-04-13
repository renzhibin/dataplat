<?php
class TopoController extends Controller{
	function  __construct(){
		$this->objFackcube=new FackcubeManager();
                $this->common=new CommonManager();
                $this->objProject = new ProjectManager();
                $this->objBehavior = new BehaviorManager();
	}

	function actionIndex(){
		$this->render('topo/index.tpl');
	}
	function actionTopoData(){
		$params=array();
		if(isset($_REQUEST['show_task'])){
			$params['show_task']=$_REQUEST['show_task'];
		}
		if(isset($_REQUEST['is_single'])){
			$params['is_single']=$_REQUEST['is_single'];
		}
		if(isset($_REQUEST['is_parent'])){
			$params['is_parent']=$_REQUEST['is_parent'];
		}
		if(isset($_REQUEST['is_child'])){
			$params['is_child']=$_REQUEST['is_child'];
		}
		if (count($params) == 0) {
		    $this->jsonOutPut(-1,'请选择任务');
		    return;
        }
		$result=$this->objFackcube->get_fakecube('get_topo_data',$params,true);
		if($result['status'] == 0){
			$this->jsonOutPut(0,'',$result);
		} else {
			$this->jsonOutPut(1,$result['msg']);
		}
	}

	function actionTopoCondition(){
		$params=array();

		$result=$this->objFackcube->get_fakecube('get_topo_condition',$params,true);
		if($result['status'] == 0){
			$this->jsonOutPut(0,'',$result);
		} else {
			$this->jsonOutPut(1,$result['msg']);
		}
	}
        /**
         * 获取全部数据接口
         */
        function actionDiTaskInterface(){
            $task = $_REQUEST['unq_job_name'];
            $search  =[];
            if(!empty($task)){
                $taskArr = explode(",", $task);
                $search  =[];
                foreach ($taskArr as $t){
                    $tmT = explode("@", $t);
                    $search[] = "'".$tmT[1]."'";
                }
            }
            $taskArr = $this->objFackcube->getTaskDataAll($search);
            $tmp=[];
            foreach($taskArr  as $k1 =>$item){
                if(!isset($tmp[$item['task']]['rely_task'])) {
                    $tmp[$item['task']]= $item;
                    $tmp[$item['task']]['rely_task'] = [];
                }
                $tmp[$item['task']]['rely_task'][] = array('tag'=>$item['rely_task']);
                 
            }
            $keys = array_keys($tmp); 
            $projectArr =  $this->objFackcube->getRunLog($keys);
            $map  = $this->common->pickup($projectArr,NULL,'task_name');
            $newArr =[];
            foreach ($keys as $key){
                    $one['job_status']   = $map[$key]['status'];
                    $one['mod_time']     = $tmp[$key]['update_time'];
                    $one['job_name']     = $key;
                    $one['unq_job_name'] = $tmp[$key]['id']."@".$key;
                    $one['project_name'] = $map[$key]['app_name']? $map[$key]['app_name']: explode(".", $key)[0];
                    $one['creater']      = $tmp[$key]['creater'];
                    $one['start_time']   = $map[$key]['start_time'];
                    $one['end_time']     = $map[$key]['end_time'];
                    $one['level']        = $tmp[$key]['schedule_level'];
                    if(!empty($tmp[$key]['rely_task'])){
                        $one['tag_depend']['tags']   = $tmp[$key]['rely_task'];
                    }else{
                        $one['tag_depend']['tags']   =[];
                    }
                    if(!empty($tmp[$key]['ass_table'])){
                        $assTmp = array();
                        $assTmp = $tmp[$key]['ass_table'];
                        $new =[];
                        if(is_array($assTmp)){
                            foreach ($assTmp as $key=> $item){
                                $tmpOne['tag'] = $item;
                                $new[] = $tmpOne;
                            }
                            $one['tag_store']['tags']   = $new;
                        }else{
                            $one['tag_store']['tags']   = [['tag'=>$assTmp]];
                        }
                    }else{
                        $one['tag_store']['tags']   = [['tag'=>$key]];
                        #$one['tag_store']['tags']   =[];
                    }
                    $newArr[] = $one;
            }
            $returnArr['code'] =0;
            $returnArr['data'] = $newArr;
            $returnArr['msg']  = 'success';
            echo json_encode($returnArr);exit;   
        }
        /**
         * 获取
         */
        function  actionrollbackRun(){
            $srcData =  $_REQUEST['unq_job_name'];
            $time = $_REQUEST['time_str'];
            if(!empty($srcData)){
                $taskArr = explode(",", $srcData);
                $return =[];
                foreach ($taskArr as $taskItem){
                    set_time_limit(10000);
                    $info =[];
                    $task = explode("@", $taskItem)[1];
                    if (!$task || !$time) {
                        $this->jsonOutPut(1,'任务名称与重跑时间必须他填写');
                        exit();
                    }
                    $porjectAll = explode('.', $task);
                    $info['project'] = $porjectAll[0];
                    array_shift($porjectAll);
                    $info['run_module'] = [implode(".", $porjectAll)];
                    $info['step'] ='all';
                    $info['start_time'] =  $time." 00:00";
                    $info['end_time'] =  $time." 00:00";
                    $res=$this->objProject->saveRunList($info['project'],$info['run_module'],$info['start_time'],$info['end_time'],$info['step']);
                    $this->objBehavior->addUserBehaviorToLog('','','/project/saverun',$info);
                    $one['msg'] = $res['msg'];
                    $one['id'] = $taskItem;
                    $return[] = $one;
                    $this->objBehavior->addUserBehaviorToLog('','','/project/saveTopoRun',$info);
                }  
                $this->jsonOutPut(0,'success',$return); 
            }else{
                $this->jsonOutPut(1,'参数错误');
            }
        }

    ################## 批量插入重跑接口 #######################
    function actionDiRollbackRunV2()
    {
        if (!isset($_REQUEST['job'])) {
            $this->jsonOutPut(1, '参数job获取异常');
            exit();
        }

        set_time_limit(10000);
        $srcData = json_decode(urldecode($_REQUEST['job']), true);

        if (!empty($srcData)) {
            $return = [];
            foreach ($srcData as $taskItem) {
                $task  = explode("@", $taskItem['id'])[1];
                $time  = strtotime($taskItem['time']);
                $level = $taskItem['level'];
                if ('day' == $level) {
                    $time = date('Y-m-d', $time);
                } elseif ('hour' == $level) {
                    $time = date('Y-m-d H:i', $time);
                } else {
                    $time = date('Y-m-d', $time);
                }

                $projectAll      = explode('.', $task);
                if (count($projectAll) == 1) {
                    continue;
                }
                $info['project'] = $projectAll[0];
                array_shift($projectAll);
                $info['run_module']     = implode(".", $projectAll);
                $info['time']           = $time;
                $info['schedule_level'] = $level;

                $id = $this->saveRunList($info);

                $return[] = [
                    'msg'   => 'success',
                    'id'    => $taskItem['id'],
                    'db_id' => $id,
                ];
            }
            $this->jsonOutPut(0, 'success', $return);
        } else {
            $this->jsonOutPut(1, '参数错误');
        }
    }

    private function saveRunList($info)
    {
        $sql = "insert into `mms_run_log` (`app_name`, `stat_date`, `run_module`, `schedule_level`, `task_queue`, `submitter`, `conf_name`, `second_check`) values('{$info['project']}', '{$info['time']}', '{$info['run_module']}', '{$info['schedule_level']}', 'inf', 'di@system', 'inf01', '') ";
        Yii::app()->db_metric_meta->createCommand($sql)->execute();
        $id = Yii::app()->db_metric_meta->getLastInsertID();

        return $id;
    }

    ################## 批量查询 log 接口 #######################

    function actionDiSearchLog()
    {
        if (!isset($_REQUEST['id']) || empty($_REQUEST['id'])) {
            $this->jsonOutPut(1, '参数 id 获取异常');
            exit;
        }

        $id = $_REQUEST['id'];

        if (!preg_match('/^(\d+,)*(\d+)$/', $id)) {
            $this->jsonOutPut(1, '参数 id 为 数字,数字,数字 格式');
            exit;
        }

        $sql  = "select log.id as log_id, log.app_name, log.start_time, log.end_time, log.status, log.run_module, log.schedule_level, topo.rely_task, topo.ass_table, conf.creater, app_conf.updated_at as mod_time from mms_run_log as log left join t_rely_topo as topo on concat(log.app_name, '.', log.run_module) = topo.task left join mms_conf as conf on log.app_name = conf.appname left join mms_app_conf as app_conf on log.run_module = concat(app_conf.category_name, '.', app_conf.hql_name) where log.id in ({$id})";
        $data = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();

        if (!empty($data)) {
            $return = [];

            foreach ($data as $row) {
                $indexKey  = "{$row['app_name']}.{$row['run_module']}";
                $uniqueKey = "{$row['log_id']}@{$row['app_name']}.{$row['run_module']}";
                if (!isset($return[$uniqueKey])) {
                    $return[$uniqueKey] = [
                        'job_status'   => $row['status'],
                        'mod_time'     => $row['mod_time'],
                        'job_name'     => $indexKey,
                        'unq_job_name' => $uniqueKey,
                        'project_name' => $row['app_name'],
                        'creater'      => $row['creater'],
                        'start_time'   => $row['start_time'],
                        'end_time'     => $row['end_time'],
                        'level'        => $row['schedule_level'],
                        'tag_depend'   => [
                            'tags' => [],
                        ],
                        'tag_store'    => [
                            'tags' => [],
                        ],
                    ];

                    if ($row['ass_table']) {
                        $return[$uniqueKey]['tag_store']['tags'][] = [
                            'tag' => $row['ass_table'],
                        ];
                    }
                }
                $return[$uniqueKey]['tag_depend']['tags'][] = [
                    'tag' => $row['rely_task'],
                ];
            }
            $this->jsonOutPut(0, 'success', array_values($return));
        } else {
            $this->jsonOutPut(1, '数据获取异常');
        }
    }
}   