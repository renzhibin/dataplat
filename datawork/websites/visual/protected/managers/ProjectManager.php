<?php
 class ProjectManager extends Manager{
     private $diUrl = 'http://scheduler.qudian.com/';
     private $diStatus2DtStatus = [
         1 => '阻塞',
         2 => '就绪',
         3 => '运行中',
         5 => '成功',
         6 => '失败',
         9 => '检测中',
         10 => '手动杀死',
         11 => '等待超时',
         12 => '运行超时'
     ];
     private $dtStatus2DiStatus = [
         '阻塞' => 1,
         '就绪' => 2,
         '运行' => 3,
         'hive结束' => 3,
         '成功' => 5,
         '失败' => 6,
         '警告' => 5,
         '超时' => 12,
         '检查' => 9,
         '杀死' => 10
     ];
     function __construct(){
         $this->menuTable='t_visual_menu';
         $this->reportTable='t_visual_table';
         $this->userTable='t_visual_user';
         $this->favoriteTable='t_visual_favorites';
         $this->project='t_visual_project_data';
         $this->runlist='mms_run_log';
         $this->project_conf='mms_conf';
         $this->objFackcube=new FackcubeManager();


     }

     function actionGetRunListByDi($project, $filter, $jobStatus, $page, $pageSize) {
         $allAppConf = $this->getAppConfByAppName($project);
         $url = $this->diUrl . 'job/dt_job_run_list';
         $unqJobNames = '';
         foreach ($allAppConf as $appConf) {
             $unqJobNames = $unqJobNames . 'cube_' . $appConf['id'] . ',';
         }
         $unqJobNames = trim($unqJobNames, ',');
         $params['unq_job_names'] = $unqJobNames;
         $params['search'] = $filter;
         $params['job_status'] = $jobStatus;
         $params['page'] = $page;
         $params['page_size'] = $pageSize;
         if ($filter) {
             if (isset($this->dtStatus2DiStatus[$filter])) {
                 $params['job_status'] = $this->dtStatus2DiStatus[$filter];
             } else {
                 $params['search'] = $filter;
             }
         }
         $strparams = http_build_query($params);
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
         $data['cn_name'] = $this->user->name;
         $data['super']   = Yii::app()->user->isSuper();
         $data['currentPage']      = $page;
         $data['currentPageCount'] = count($body['data']);
         $data['pageSize']         = $pageSize;
         $data['totalPages']       = ceil($body['total_size'] / $pageSize);
         $data['totalCount']       = $body['total_size'];
         $data['list']             = [];
         foreach ($body['data'] as $row) {
             $step = $row['ext_json'];
             $one = [
                 'id' => $row['job_run_id'],
                 'app_name' => substr($row['job_name'], 0, strpos($row['job_name'], '.')),
                 'run_module' => substr($row['job_name'], strpos($row['job_name'], '.') + 1),
                 'stat_date' => $row['job_time_str'],
                 'status' => $this->diStatus2DtStatus[$row['job_status']],
                 'ori_status' => $row['job_status'],
                 'step' => $step ? $step['step'] : 'all',
                 'start_time' => $row['start_time'],
                 'end_time' => $row['end_time'],
                 'create_time' => $row['disp_start_time'],
                 'priority' => $row['priority'],
                 'data_size' => '--',
                 'creater' => in_array($row['state'], [0,1,2,6]) ? null : $row['creater'],
                 'submitter' => $row['creater'],
                 'load_time_spend' => '--',
                 'log' => $this->diUrl . "job/render_log?id={$row['job_run_id']}&unq_job_name={$row['unq_job_name']}",
                 'killtask' => '',
                 'download' => '',
             ];
             $one['download'] = WEB_API."/data/".$one['app_name'].'/'.$one['stat_date'].'.'.$one['app_name'].'.'.$one['run_module'];
             if (strpos($one['stat_date'], ' ')) {
                 $statDate = str_replace(" ",".",$one['stat_date']) . '.0';
                 $one['download'] = WEB_API."/data/".$one['app_name'].'/'.$statDate.'.'.$one['app_name'].'.'.$one['run_module'];
             }
             $one['real_log'] = "http://116.62.213.137:8001/get_run_detail_real?serial=" . $one['id'] . "&app_name=" . $one['app_name'] . "&stat_date=" . date('Y-m-d', strtotime($one['stat_date'])) . "&stat_time=" . urlencode($one['stat_date']) . "&module_name=" . $one['run_module'];
             $one['killtask'] = "serial=".$one['id']."&app_name=".$one['app_name']."&status=".$one['ori_status']."&stat_date=".$one['stat_date']."&module_name=".$one['run_module']."&username=".str_ireplace(['@qudian.com', '@qufenqi.com'], '', Yii::app()->user->username);
             array_push($data['list'], $one);
         }
         $returnData = ['totalCount' => $data['totalCount'], 'data' => []];
         foreach ($data['list'] as $key => $value) {
             $one = [];
             array_push($one, $value['id']);
             array_push($one, $value['app_name']);
             array_push($one, $value['run_module']);
             array_push($one, $value['stat_date']);
             array_push($one, $value['status']);
             array_push($one, $value['start_time']);
             array_push($one, $value['end_time']);
             array_push($one, $value['create_time']);
             $step = '';
             if ($value['step'] == 'all') {
                 $step = '全部';
             } else if ($value['step'] == 'hive') {
                 $step = 'hql任务';
             } else if ($value['step'] == 'mysql') {
                 $step = '导入数据';
             } else if ($value['step'] == 'delete') {
                 $step = '删除数据';
             }

             array_push($one, $step);
             array_push($one, $value['data_size']);
             array_push($one, $value['load_time_spend']);
             array_push($one, $value['priority']);
             array_push($one, $value['submitter']);
             $creater = '';
             if ($value['creater'] == null) {
                 $creater = '例行';
             } else {
                 $creater = '手动';
             }
             array_push($one, $creater);
             array_push($one, "<a target='_blank' href='{$value['log']}'>日志</a>");
             array_push($one, "<button class=\"btn btn-primary btn-xs btn-kill\" data='{$value['killtask']}'>杀死</button>
                      <button class=\"btn btn-primary btn-xs btn-reday\" data='{$value['killtask']}'>置为就绪</button>");
             array_push($one, "<a target=\"_blank\" href={$value['download']}>下载</a>");
             array_push($returnData['data'], $one);
         }
         return $returnData;
     }

     function  __handleRunlist($data){
         if(empty($data)){
             return array();
         }
         $statusMap=ConstManager::getStatusMap();
         foreach($data as $k=>$v){
             //"get_run_detail?serial=$i[0]&app_name=$i[1]&stat_date=$i[3]&module_name=$i[2]
             $v['status']=$statusMap[$v['status']];
             $v['log']=WEB_API."/get_run_detail?serial=".$v['id']."&app_name=".$v['app_name']."&stat_date=".$v['stat_date']."&module_name=".$v['run_module'];
             $v['killtask']="serial=".$v['id']."&app_name=".$v['app_name']."&stat_date=".$v['stat_date']."&module_name=".$v['run_module']."&username=".str_ireplace(['@qudian.com', '@qufenqi.com'], '', Yii::app()->user->username);
             $v['download']=WEB_API."/data/".$v['app_name'].'/'.$v['stat_date'].'.'.$v['app_name'].'.'.$v['run_module'];

             $v['real_log'] = "http://116.62.213.137:8001/get_run_detail_real?serial=" . $v['id'] . "&app_name=" . $v['app_name'] . "&stat_date=" . date('Y-m-d', strtotime($v['stat_date'])) . "&stat_time=" . urlencode($v['stat_date']) . "&module_name=" . $v['run_module'];
             $data[$k]=$v;
         }
        // echo '<pre/>';print_r($data);exit();
         return $data;
     }
    function  saveRunList($project,$arrHql,$start_time,$end_time,$step){
        $startstamp=strtotime($start_time);
        $endstamp=strtotime($end_time);
        $create=Yii::app()->user->username;
   

        //时间粒度需求－－直接调用python 接口
        $result =  $this->objFackcube->get_fakecube('save_run_list',array('project'=>$project,'creater'=>$create,'run_module'=>implode(',', $arrHql),'start_time'=>$start_time,'end_time'=>$end_time,'step'=>$step));
     
        /*$sql = "insert into  $this->runlist (`app_name`,`stat_date`,`run_module`,`creater`) values";
        $values='';
        for($date=$startstamp;$date<=$endstamp;$date+=86400){
                foreach($arrHql as $run_module){
                 $ymddate = date('Y-m-d',$date);
                 $values.="('$project','$ymddate','$run_module','$create'),";
            }
        }
        $sql=$sql.rtrim($values,',');
        $result=Yii::app()->db_metric_meta->createCommand($sql)->execute();*/
        //$result = json_decode($result,true);
       
        /*if($result['status'] == '0'){
            return true;
        } 
        return false;*/
        return $result;
    }

     function saveRealRunList($project, $arrHql, $start_time, $end_time, $step)
     {
         $start_time = date('Y-m-d H:00:00', strtotime($start_time));
         $end_time   = date('Y-m-d H:00:00', strtotime($end_time));

         $sql  = "select
                      *
                  from mms_realtime_app_conf
                  where app_name = '{$project}'
                 ";
         $data = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();

         if (!is_array($data)) {
             return [
                 'status' => 1,
                 'msg'    => '获取配置信息异常'
             ];
         }

         $hqlParam = [];
         foreach ($data as $row) {
             $enName = "{$row['category_name']}.{$row['hql_name']}";
             $param  = json_decode($row['other_params'], true);

             if (isset($param['schedule_interval']) && is_numeric($param['schedule_interval'])) {
                 $hqlParam[$enName] = [
                     'param'             => $param,
                     'schedule_interval' => $param['schedule_interval'],
                 ];
             }
         }

         $user = Yii::app()->user->username;

         foreach ($arrHql as $currentHql) {
             if (isset($hqlParam[$currentHql])) {
                 $this->insertRealLog($project, $currentHql, $start_time, $end_time, $hqlParam[$currentHql], $step, $user);
             }
         }

         return [
             'status' => 0,
             'msg'    => '插入数据完成'
         ];
     }

     function insertRealLog($project, $currentHql, $start_time, $end_time, $internal, $step, $user)
     {
         $insert      = [];
         $startSecond = strtotime($start_time);
         $endSecond   = strtotime($end_time);
         $addSecond   = $internal['schedule_interval'] * 60;

         while ($startSecond < $endSecond) {
             $insert[] = [
                 'app_name'       => $project,
                 'stat_date'      => date('Y-m-d H:i:s', $startSecond),
                 'run_module'     => $currentHql,
                 'step'           => $step,
                 'creater'        => $user,
                 'schedule_level' => 'realtime',
                 'submitter'      => $user,
             ];

             $startSecond += $addSecond;
         }

         if (!empty($insert)) {
             $insertDB = Yii::app()->sdb_metric_meta->createCommand();
             foreach ($insert as $insetData) {
                 $insertDB->insert('mms_realtime_run_log', $insetData);
             }
         }
     }

    function  saveTopoRunList($task, $time){
         $creater=Yii::app()->user->username;


         //时间粒度需求－－直接调用python 接口
         $result =  $this->objFackcube->get_fakecube(
             'save_topo_run_list',
             array(
                 'task'=>$task,
                 'creater'=>$creater,
                 'time'=>$time
             )
         );
         return $result;
    }

     function getRealRunList($project = '', $index = '', $offset = '')
     {
         $sql = "select
            id,
            app_name,
            run_module,
            stat_date,
            status,
            step,
            is_test,
            start_time,
            end_time,
            create_time,
            priority,
            data_size,
            creater,
            submitter,
            round(load_time_spend,5) as load_time_spend
        from mms_realtime_run_log";
         if (!empty($project)) {
             $sql .= ' where app_name=\'' . $project . '\'';
         }
         $sql .= ' order by id desc';
         if (!empty($index) && !empty($offset)) {
             /*$index=1;
             $offset=10;*/
             $start = ($index - 1) * $offset;
             $end   = $index * $offset;
             $sql   .= " limit $start,$end";
         }
         if (empty($index) && empty($offset)) {
             $sql .= " limit 0,3000";
         }

         $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
         $result = $this->__handleRunlist($result);

         return $result;
     }

     function getRealModuleByModule($project)
     {
         $sql  = "select
                      *
                  from mms_realtime_app_conf
                  where app_name = '{$project}'
                 ";
         $data = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();

         $result = [];
         if (!is_array($data)) {
             return $result;
         }

         foreach ($data as $row) {
             $enName   = "{$row['category_name']}.{$row['hql_name']}";
             $param    = json_decode($row['other_params'], true);
             $cnName   = isset($param['cn_name']) ? $param['cn_name'] : '--';
             $result[] = [
                 'cn_name' => $cnName,
                 'en_name' => $enName,
             ];
         }

         return $result;
     }

    function getRunlist($project='',$index='',$offset=''){
        $sql="select
            id,
            app_name,
            run_module,
            stat_date,
            status,
            step,
            is_test,
            start_time,
            end_time,
            create_time,
            priority,
            data_size,
            creater,
            submitter,
            round(load_time_spend,5) as load_time_spend
        from   $this->runlist";
        if(!empty($project)){
            $sql.=' where app_name=\''.$project.'\'';
        }
        $sql.=' order by id desc';
        if(!empty($index) && !empty($offset)){
            /*$index=1;
            $offset=10;*/
            $start=($index-1)*$offset;
            $end=$index*$offset;
            $sql.=" limit $start,$end";
        }
        if(empty($index) && empty($offset)){
            $sql.=" limit 0,3000";
        }


        $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        $result=$this->__handleRunlist($result);
        //echo '<pre/>';print_r($result);exit();

        return $result;


    }
     function  getProjectAuth(){
         $res=$this->getProjectList();
         $retu=array();
         foreach ($res as $tmp) {
                $retu[]=$tmp['project'];
         }
         return $retu;

     }
     #查询报表信息
     function getProjectList($project='')
     {

         return $this->objFackcube->get_project_list();
     }
     #过滤掉调度类项目
     function filterProject($projectInfo,$type){
         $data = array();
         foreach ($projectInfo as $key => $value) {
            if($value['hql_type'] == $type){
               $data[] = $value;
            }
         }
         return $data;
     }
     #获取项目的注释信息
     function  getProjectComment($project,$column=''){
         if(empty($project))
             return false;
         $sql="select  comments  from    $this->project   where  project='$project'";
        // print $sql;
         $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
         if(empty($result))
             return array();
         if(!empty($column)){
             $comments=json_decode($result[0]['comments'],true);

             if(isset($comments[$column])){
                 if(!empty($comments[$column]['content'])){
                    return $comments[$column]['content'];
                 }else{
                    //兼容以前的老json格式
                    return $comments[$column];
                 }
             }
         }else{
             return $result[0]['comments'];
         }
         return false;
     }

     function  getProjectCommentisReplaced($project,$column=''){
         if(empty($project))
             return '2';
         $sql="select  comments  from    $this->project   where  project='$project'";
         // print $sql;
         $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
         if(empty($result))
             return array();
         if(!empty($column)){
             $comments=json_decode($result[0]['comments'],true);

             if(isset($comments[$column])){
                 if(!empty($comments[$column]['isReplace'])){
                     return $comments[$column]['isReplace'];
                 }else{
                     //兼容以前的老json格式
                     return '2';
                 }
             }else{
                 //如果报表是新注释的则选中checkbox
                 return '1';
             }
         }

         return '2';

     }

     #保存项目的注释信息
     function  saveProjectComment($project,$column,$comments,$isReplace){
         if(empty($project))
             return false;
         $res=$this->getProjectComment($project);
         //该没有被注释的情况
         if(empty($res)){
             $insertContent = array($column=>array('content'=>$comments,'isReplace'=>$isReplace));
             $commentsCode = json_encode($insertContent);
             $commentsCode = addslashes($commentsCode);
             if(empty($comments) or $comments == 'NULL'){
                 return true;
             }
             $sql = "insert into  " .  $this->project. "(`project`,`comments`) values('$project','$commentsCode') ";
         //该项目已经被注释的情况
         }else{
             $updateContent = array('content'=>$comments,'isReplace'=>$isReplace);
             //$updateJson = json_encode($updateContent);
             $resComment=json_decode($res,true);
             if(empty($comments) or $comments == 'NULL'){
                   unset($resComment[$column]);
             }else{
                   $resComment[$column]=$updateContent;
             }
             $updateJson = json_encode($resComment);
             $updateJson = addslashes($updateJson);
             $sql='update '. $this->project.' set comments=\''.$updateJson.'\'  where project=\''.$project.'\'';

         }
       // print $sql;exit();
         $res=Yii::app()->db_metric_meta->createCommand($sql)->execute();



         return true;


     }
     function  getProjectData($project){
         if(empty($project))
             return false;
         $sql="select  *   from    $this->project   where  project='$project'";
         $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
         return $result;
     }

     function  saveProjectTimeline($project,$timeline){
         if(empty($project))
             return false;
         $res=$this->getProjectData($project);
         $timeline=addslashes(json_encode($timeline));

         if(empty($res)){
             $sql = "insert into  " .  $this->project. "(`project`,`timeline`) values('$project','$timeline') ";
         }else{
             $sql='update '. $this->project.' set timeline=\''.$timeline.'\'  where project=\''.$project.'\'';

         }
         $res=Yii::app()->db_metric_meta->createCommand($sql)->execute();
         return true;


     }




     function  getMetricandGroup($project,$cn_name=true,$onle_metric=false){
         $res=$this->objFackcube->get_app_conf(array('project'=>$project));
         $cat=$res['categories'];
         $metricarr=array();
         $dimarr=array();
         foreach($cat as $v){
             $catname=$v['name'];
             $realinfo=$v['groups'];
             foreach($realinfo as $subv){
                 $prefix=$catname."_".$subv['name'];
                 //$处理subv['metrics']为空的bug
                 if(!empty($subv['metrics'])){
                     foreach($subv['metrics'] as $mv){
                         $tmpname=$prefix."_".$mv['name'];
                         if($cn_name==true){
                             $mv['cn_name']=$mv['cn_name']."(".$tmpname.")";
                         }
                         $metricarr[$tmpname]=array('name'=>$tmpname,'cn_name'=>$mv['cn_name']);
                     }
                 }
                 if(!empty($subv['dimensions'])){
                     foreach($subv['dimensions'] as $dv){
                         if($cn_name==true){
                             $dv['cn_name']=$dv['cn_name']."(".$dv['name'].")";
                         }
                         $dimarr[$dv['name']]=array('name'=>$dv['name'],'cn_name'=>$dv['cn_name']);
                     }
                 }

             }

         }
         if($onle_metric===true){
                return $metricarr;
         }
         return array_merge($metricarr,$dimarr);
     }


     function  updatepriority($id,$val){
         if(empty($id)){
             return false;
         }
         $sql="update ". $this->project_conf." set priority={$val}  where id={$id}";
         $res=Yii::app()->db_metric_meta->createCommand($sql)->execute();
         return true;

     }

     function updateRunLogStatus($ids, $status) {
         $sql = "update " . $this->runlist . " set status={$status} where id in ({$ids})";
         $res=Yii::app()->db_metric_meta->createCommand($sql)->execute();
         return true;
     }

     # 更新创建者 负责人
     function updateCreateUser($id, $user)
     {
         return Yii::app()->db_metric_meta->createCommand()->update($this->project_conf, [
             'creater' => $user,
         ], ['in', 'id', $id]);
     }

     function getAppConfByAppNameAndCategorynameAndHqlname($appname, $categoryname, $hqlname) {
         $sql = "select * from mms_app_conf where app_name='{$appname}' and category_name='{$categoryname}' and hql_name='{$hqlname}' limit 1";
         $result = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();
         return $result;
     }

     function getAppConfByAppName($appname) {
         $sql = "select * from mms_app_conf where app_name='{$appname}'";
         $result = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();
         return $result;
     }

     function getMmsConfByAppName($appname) {
         $sql = "select conf from {$this->project_conf} where appname='{$appname}' limit 1";
         $result = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();
         if (!isset($result[0])) {
             return [];
         }
         return $result[0]['conf'];
     }

     function getMmsConfAllByAppName($appname) {
         $sql = "select * from {$this->project_conf} where appname='{$appname}' limit 1";
         $result = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();
         if (!isset($result[0])) {
             return [];
         }
         return $result;
     }

     function updateMmsConfByAppName($appname, $conf) {
         $sql = "select * from {$this->project_conf} where appname='{$appname}' limit 1";
         $result = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();
         $result = $result[0];
         $sql = "insert into mms_conf_log(date_s,date_e,date_n,creater,appname,create_time,priority,`explain`,cn_name,storetype,editor,authtype,authuser,mysql_weight,update_weight_time,
conf,weight_update_log,store_db) values('{$result['date_s']}','{$result['date_e']}','{$result['date_n']}','{$result['creater']}','{$result['appname']}','{$result['create_time']}','{$result['priority']}','{$result['explain']}','{$result['cn_name']}','{$result['storetype']}','di@qudian.com','{$result['authtype']}','{$result['authuser']}','{$result['mysql_weight']}','{$result['update_weight_time']}','$conf','{$result['weight_update_log']}','{$result['store_db']}')";
         $res=Yii::app()->db_metric_meta->createCommand($sql)->execute();
         return Yii::app()->db_metric_meta->createCommand()->update($this->project_conf, [
             'conf' => $conf,
         ], ['in', 'appname', $appname]);
     }

     function getDetailByConfs($confs) {
         $inConfs = implode("','", $confs);
         $inConfs = "'" . $inConfs . "'";
         $sql = "select mms_app_conf.id as job_id ,mms_app_conf.data_table_name as data_table_name,concat(app_name, '.', category_name, '.', hql_name) as job_name,mms_conf.cn_name as project_name,mms_app_conf.creater,mms_app_conf.editor,mms_app_conf.updated_at as mod_time,other_params as conf,mms_app_conf.created_at as create_time from mms_app_conf left join mms_conf on mms_app_conf.app_name=mms_conf.appname where concat(app_name, '.', category_name, '.', hql_name) in ($inConfs)";
         $res = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();
         return $res;
     }

     function getAllIncrementModifyOnlineAppConfByStartTimeAndEndTime($startTime, $endTime) {
         $sql = "select concat(app_name, '.', category_name, '.', hql_name) as app_name, 
              concat(category_name, '.', hql_name) as app_name_conf, 
              mms_conf.conf 
          from mms_app_conf_log
          join mms_conf on mms_conf.appname = mms_app_conf_log.app_name
          where mms_app_conf_log.id in (select max(id) from mms_app_conf_log where  created_at >= '$startTime' and created_at < '$endTime' group by app_name, category_name,hql_name)";
         $res = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();
         return $res;
     }

     function getAllIncrementOnlineAppConfByStartTimeAndEndTime($startTime, $endTime) {
         $sql = "select appname,date_s,date_e,conf from mms_conf_log where id in (select max(id) from mms_conf_log where  created_at >= '$startTime' and created_at < '$endTime' group by appname)";
         $res = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();
         return $res;
     }

     function getAllIncrementOnlineAppConfByEndTime($endTime) {
         $sql = "select appname,date_s,date_e,conf from mms_conf_log where id in (select max(id) from mms_conf_log where  created_at < '$endTime' group by appname)";
         $res = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();
         return $res;
     }

     function getAllOnlineAppConfByEndTime($endTime) {
         $sql = "select appname,conf from mms_conf_log where id in (select max(id) from mms_conf_log  group by appname) and date_s < '$endTime' and (date_e > '$endTime' or date_e = '0000-00-00 00:00:00') and created_at <= '$endTime'";
         $res = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();
         return $res;
     }

     function getAllAppConf() {
         $sql = "select * from mms_app_conf";
         $res = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();
         return $res;
     }

     # 获取当前人运行的项目数量
     function getProjectRunNum($userName)
     {
         $sql = "SELECT count(id) AS totalCount FROM {$this->runlist} WHERE submitter = '{$userName}' AND creater IS NOT NULL AND status IN (1, 2, 3)";
         $res = Yii::app()->db_metric_meta->createCommand($sql)->queryAll();

         if (isset($res[0]['totalCount'])) {
             $result = $res[0]['totalCount'];
         } else {
             $result = 0;
         }

         return $result;
     }
 }