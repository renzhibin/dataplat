<?php
 class ProjectManager extends Manager{
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

     function  __handleRunlist($data){
         if(empty($data)){
             return array();
         }
         $statusMap=ConstManager::getStatusMap();
         foreach($data as $k=>$v){
             //"get_run_detail?serial=$i[0]&app_name=$i[1]&stat_date=$i[3]&module_name=$i[2]
             $v['status']=$statusMap[$v['status']];
             $v['log']=WEB_API."/get_run_detail?serial=".$v['id']."&app_name=".$v['app_name']."&stat_date=".$v['stat_date']."&module_name=".$v['run_module'];
             $v['killtask']="serial=".$v['id']."&app_name=".$v['app_name']."&stat_date=".$v['stat_date']."&module_name=".$v['run_module']."&username=".str_ireplace(['@.com', '@.com'], '', Yii::app()->user->username);
             $v['download']=WEB_API."/data/".$v['app_name'].'/'.$v['stat_date'].'.'.$v['app_name'].'.'.$v['run_module'];

             $v['real_log'] = "http://118.31.236.5:8001/get_run_detail_real?serial=" . $v['id'] . "&app_name=" . $v['app_name'] . "&stat_date=" . date('Y-m-d', strtotime($v['stat_date'])) . "&stat_time=" . urlencode($v['stat_date']) . "&module_name=" . $v['run_module'];
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

     # 更新创建者 负责人
     function updateCreateUser($id, $user)
     {
         return Yii::app()->db_metric_meta->createCommand()->update($this->project_conf, [
             'creater' => $user,
         ], ['in', 'id', $id]);
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
