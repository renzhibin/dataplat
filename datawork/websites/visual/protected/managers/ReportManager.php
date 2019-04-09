<?php
 class ReportManager extends Manager{
     function __construct(){
         $this->menuTable='t_visual_menu_xy';
         $this->reportTable='t_visual_table';
         $this->userTable='t_visual_user';
         $this->favoriteTable='t_visual_favorites';
         $this->unit ='t_visual_unit_map';
         $this->objFackcube=new FackcubeManager();
         $this->objComm=new CommonManager();
         $this->objBehavior = new BehaviorManager();
         $this->objMenu = new MenuManager();
         $this->comquery = new MysqlCModel();
         $this->objVisual= new VisualManager();
     }
     /*获取名称*/
     function getUnit($name =''){
        if($name !=''){
            $sql ="select * from $this->unit where name ='".$name."'";
        }else{
             $sql ="select * from $this->unit ";
        }
        $db = Yii::app()->sdb_metric_meta;
        $data = $db->createCommand($sql)->queryAll();
        return $data;
     }
     function addUnit($name){
         $sql =" insert  $this->unit (name) values ('$name')";
         $db = Yii::app()->db_metric_meta;
         $id = $db->createCommand($sql)->execute();
         return $id;
     }
     function isOwner($id){

         if(empty($id)){
             return true;
         }

         $res=$this->getReoport($id);
         if(empty($res)){
             return false;
         }
         if($res['creater'] != Yii::app()->user->username){
             return false;
         }

         return true;

     }

     # 校验自定义收藏是否有相同报表
     function checkReportCustom($name, $id = '')
     {
         $sql = 'select  * from  t_visual_table_custom';
         if (!empty($name)) {
             $whereStr = "  where  cn_name ='" . $name . "'";
             if (!empty($id)) {
                 $whereStr .= ' and id!=' . $id;
             }
         } else {
             $whereStr = "";
         }
         $sql  = $sql . $whereStr;
         $db   = Yii::app()->sdb_metric_meta;
         $data = $db->createCommand($sql)->queryAll();
         return $data;
     }

     function checkReportCustomAuth($id = '', $creater = '')
     {
         $sql = 'select * from  t_visual_table_custom ';

         $whereStr = 'where 1 = 1';

         if (!empty($id)) {
             $whereStr .= " and id = '{$id}'";
         }

         if (!empty($creater)) {
             $whereStr .= " and creater = '{$creater}'";
         }

         $sql  = $sql . $whereStr;
         $db   = Yii::app()->sdb_metric_meta;
         $data = $db->createCommand($sql)->queryAll();
         return $data;
     }

     //检测是否有相同报表
     function checkReport($name,$id='')
     {
         $sql = "select  * from  " .  $this->reportTable;
         if (!empty($name)) {
             $whereStr = "  where  cn_name ='" . $name . "'";
             if(!empty($id)){
                 $whereStr.=' and id!='.$id;
             }
         } else {
             $whereStr = "";
         }
         $sql = $sql . $whereStr;
         $db = Yii::app()->sdb_metric_meta;
         $data = $db->createCommand($sql)->queryAll();
         return $data;
     }

     # 保存为收藏
     function saveReportCustom($params, $project = null)
     {
         if (empty($params['basereport']['group'])) {
             $params['basereport']['group'] = 0;
         }
         if (empty($params['type'])) {
             $type = 1;
         } else {
             $type = $params['type'];
         }
         //不是复制的报表 衍生报表没有project
         if ($project == null) {
             $project = isset($params['basereport']['project']) ? $params['basereport']['project'] : "";
             $group   = '';
             $metric  = '';
         } else {
             $group  = $params['group'];
             $metric = $params['metric'];
         }
         $dataArr = array(
             'cn_name'      => $params['basereport']['cn_name'],
             'explain'      => $params['basereport']['explain'],
             'project'      => $project,
             'group'        => $group,
             'metric'       => $metric,
             'params'       => serialize($params),
             'creater'      => Yii::app()->user->username,
             'modify_user'  => Yii::app()->user->username,
             'chinese_name' => Yii::app()->user->name,
             'type'         => $type,
             'create_date'  => date("Y-m-d H:i:s")
         );
         $sqlArr  = array(
             'table' => 't_visual_table_custom',
             'data'  => $dataArr
         );

         $id = $this->comquery->runInsert($sqlArr, 'metric_meta');
         //添加用户行为记录
         $menu_id           = 0;
         $param             = array();
         $param['table_id'] = $id;
         $param['menu_id']  = $menu_id;
         $this->objBehavior->addUserBehaviorToLog($id, $menu_id, '/report/addreport/table_id/' . $id, $param);
         return $id;
     }

    //复制报表时需要的参数 $project
     function saveReport($params,$project=null)
     {
          if(empty($params['basereport']['group'])){
            $params['basereport']['group']=0;
         }
         if(empty($params['type'] )){
             $type=1;
         }else{ 
            $type = $params['type'];
        }
         //不是复制的报表 衍生报表没有project
        if($project == null){
            $project =  isset($params['basereport']['project'])?$params['basereport']['project']:"";
            $group = '';
            $metric = '';
        } else {
            $group = $params['group'];
            $metric = $params['metric'];
        }
        $dataArr = array(
            'cn_name'=> $params['basereport']['cn_name'],
            'explain'=> $params['basereport']['explain'],
            'project'=> $project,
            'group'=> $group,
            'metric'=>$metric,
            'params'=> serialize($params),
            'creater'=> Yii::app()->user->username,
            'modify_user'=>Yii::app()->user->username,
            'chinese_name'=> Yii::app()->user->name,
            'type'=>$type,
            'create_date'=>date("Y-m-d H:i:s")
         );
         $sqlArr = array(
            'table'=>$this->reportTable,
            'data'=>$dataArr
         );
      
         $id =  $this->comquery->runInsert($sqlArr,'metric_meta');
         //添加用户行为记录
         $menu_id = 0;
         $param = array();
         $param['table_id'] =  $id;
         $param['menu_id'] = $menu_id;
         $this->objBehavior->addUserBehaviorToLog($id,$menu_id,'/report/addreport/table_id/'.$id,$param);
         return $id;
     }

     function updateReport($params)
     {
         if(empty($params['basereport']['group'])){
             $params['basereport']['group']=0;
         }

         $id = $params['id'];
         unset($params['id']);

          
         $sqlParams = array(
            'table'=>$this->reportTable,
            'data'=>array(
                'cn_name'=>$params['basereport']['cn_name'],
                'explain'=>$params['basereport']['explain'],
                'project'=>$params['basereport']['project'],
                'group'=>$params['table']['group'],
                'metric'=>$params['table']['metric'],
                'params'=>serialize($params),
                'modify_user'=>Yii::app()->user->username,
                'type'=>$params['type'],
            ),
            'where'=>array(
                'id'=>$id
            )
         );
         $re =  $this->comquery->runUpate($sqlParams);
          

         $menuList = $this->objMenu->getMenuByReoprt($id);
         $menuStr = '';
         if(sizeof($menuList) > 0){
             foreach($menuList as $menuInfo){
                 $menu_id = $menuInfo['id'];
                 if(empty($menuStr)){
                     $menuStr = $menu_id;
                 }else{
                     $menuStr = $menuStr.'/'.$menu_id;
                 }
             }
             $params['menu_id'] = $menuStr;
             //挂了菜单的情况
             $this->objBehavior->addUserBehaviorToLog($id,$menuStr,'/report/editreport/table_id/'.$id,$params);
         }else{
             //没挂菜单的情况
             $this->objBehavior->addUserBehaviorToLog($id,'0','/report/editreport/table_id/'.$id,$params);
         }

         //权限名称同步
         //$this->auth->checkName();
         return $id;
     }

     /**
      * 获取报表信息
      */
     function  getReportSingle($paramArr){
         $params = implode(",",$paramArr);
         $sql = "select $params from  " . $this->reportTable." where flag =1 ";
         $db = Yii::app()->sdb_metric_meta;
         $data = $db->createCommand($sql)->queryAll();
         return $data;
     }
     #查询报表信息
     function getReportList($project='',$getAll=false,$getSuperProject=false,$authFlag=false,$openurl=false)
     {
         $sql = "select  * from  " . $this->reportTable;

         $whereStr= " where 1=1";
         if(ALYAUTH === TRUE && $authFlag==True){
             $whereStr.= ' and creater=\''.Yii::app()->user->username.'\'  ';
         }
         $objauth =new AuthManager();
         $superProject=$objauth->getSuperProject();

         if (!empty($project)) {
             $whereStr .= "  and  project ='" .$project . "'";
         }
         if(!$getAll){
             $whereStr .= " and flag=1 ";
         }
        if(!$openurl){
            $whereStr .= " and type!=9 ";
        }
         if(!$getSuperProject && !empty($superProject) && ! Yii::app()->user->isSuper() ){
             $objComm=new CommonManager();
             $superProject=$objComm->addSinglequote($superProject);

             $whereStr .= ' and project not in ('.implode(',',$superProject).')';

         }
         $orderStr = " order by flag asc,id desc";

         $sql = $sql . $whereStr.$orderStr;
         $db = Yii::app()->sdb_metric_meta;
         $data = $db->createCommand($sql)->queryAll();

         $objProject=new ProjectManager();
         $projectList=$objProject->getProjectAuth();
         $finalRes=array();
         if(!$getSuperProject){
         foreach($data as $tmp){
             if(empty($tmp['project']) || in_array($tmp['project'],$projectList)){
                 $finalRes[]=$tmp;
             }
            }
         }
         return $data;
     }

     function  isOldReport($id){
         if(empty($id)){
             return false;
         }
         $sql = "select  * from  " . $this->reportTable." where  id =" . $id;

         $db = Yii::app()->sdb_metric_meta;
         $confArr = $db->createCommand($sql)->queryRow();

         $params=unserialize($confArr['params']);
         if(!empty($params['timereport'])){
             return false;
         }
         return true;
     }

     function getReportCustom($id = false)
     {
         $sql = "select  * from  t_visual_table_custom";
         if ($id) {
             $whereStr = "  where  id =" . $id;
         } else {
             $whereStr = "";
         }
         $sql     = $sql . $whereStr;
         $db      = Yii::app()->sdb_metric_meta;
         $confArr = $db->createCommand($sql)->queryRow();
         if (empty($confArr)) {
             return array();
         }
         //外链
         if ($confArr['type'] == 9) {
             return $confArr;
         }


         $unserParams = unserialize($confArr['params']);

         if (!isset($unserParams['basereport'])) {

             $baseReport['cn_name']     = $confArr['cn_name'];
             $baseReport['explain']     = $confArr['explain'];
             $baseReport['first_menu']  = '';
             $baseReport['second_menu'] = '';
             $unserParams['basereport'] = $baseReport;

         }
         if (!isset($unserParams['timereport'])) {
             $timeReport['date_type'] = $unserParams['datetype'];
             if ($timeReport['date_type'] == true || $timeReport['date_type'] == 'true') {
                 $timeReport['shortcut']  = array(7, 30);
                 $timeReport['interval']  = 7;
                 $timeReport['date_type'] = true;
             } else {
                 $timeReport['date_type'] = false;
                 $timeReport['shortcut']  = array(1, 2);
                 $timeReport['interval']  = 0;
             }
             $timeReport['offset'] = 1;


             $unserParams['timereport'] = $timeReport;


         }
         // if(!isset($unserParams['table']['grade']['search'])){
         //     foreach(explode(',',$unserParams['table']['group'])  as $k=>$v){

         //         $unserParams['table']['grade']['search'][]=array('reportkey'=>$v);

         //     }

         // }


         //2  true 区间  1 false  单天
         if ($unserParams['timereport']['date_type'] === true) {
             $unserParams['timereport']['date_type'] = 2;
         } elseif ($unserParams['timereport']['date_type'] === false) {
             $unserParams['timereport']['date_type'] = 1;
         }
         $unserParams['basereport']['group'] = explode(',', $confArr['auth']);
         if (isset($unserParams['table']['grade']['sort']) && !empty($unserParams['table']['grade']['sort']))
             $unserParams['table']['grade']['sort'] = json_decode(strtolower(json_encode($unserParams['table']['grade']['sort'])), true);

         if (isset($unserParams['table']['metric'])) {
             $unserParams['table']['metric'] = strtolower($unserParams['table']['metric']);
         }
         if (isset($unserParams['table']['group'])) {
             $unserParams['table']['group'] = strtolower($unserParams['table']['group']);
         }
         if (isset($unserParams['table']['group']) || isset($unserParams['table']['metric'])) {
             $confArr['metric'] = strtolower($unserParams['table']['metric']);
             $confArr['group']  = strtolower($unserParams['table']['group']);
         }
         $confArr['params'] = $unserParams;

         /*if(!empty($confArr['params']['table']['grade']['sort'])){
            array_unshift($confArr['params']['table']['grade']['sort'],'date');
         }*/

         //echo '<pre/>';print_r($confArr);exit();
         return $confArr;
     }

     function getReoport($id = false)
     {
         $sql = "select  * from  " . $this->reportTable;
         if ($id) {
             $whereStr = "  where  id =" . $id;
         } else {
             $whereStr = "";
         }
         $sql = $sql . $whereStr;
         $db = Yii::app()->sdb_metric_meta;
         $confArr = $db->createCommand($sql)->queryRow();
         if(empty($confArr)){
             return array();
         }
        //外链
         if($confArr['type']==9){
             return  $confArr;
         }


         $unserParams = unserialize($confArr['params']);

         if(!isset($unserParams['basereport'])){

             $baseReport['cn_name']=$confArr['cn_name'];
             $baseReport['explain']=$confArr['explain'];
             $baseReport['first_menu']='';
             $baseReport['second_menu']='';
             $unserParams['basereport'] =$baseReport;

         }
         if(!isset($unserParams['timereport'])){
             $timeReport['date_type']=$unserParams['datetype'];
             if($timeReport['date_type']==true || $timeReport['date_type']=='true') {
                 $timeReport['shortcut']=array(7,30);
                 $timeReport['interval']=7;
                 $timeReport['date_type']=true;
             }else{
                 $timeReport['date_type']=false;
                 $timeReport['shortcut']=array(1,2);
                 $timeReport['interval']=0;
             }
             $timeReport['offset']=1;


             $unserParams['timereport'] =$timeReport;


         }
         // if(!isset($unserParams['table']['grade']['search'])){
         //     foreach(explode(',',$unserParams['table']['group'])  as $k=>$v){

         //         $unserParams['table']['grade']['search'][]=array('reportkey'=>$v);

         //     }

         // }



         //2  true 区间  1 false  单天
         if($unserParams['timereport']['date_type']===true){
             $unserParams['timereport']['date_type']=2;
         }elseif($unserParams['timereport']['date_type']===false){
             $unserParams['timereport']['date_type']=1;
         }
         $unserParams['basereport']['group']=explode(',',$confArr['auth']);
         if(isset($unserParams['table']['grade']['sort'])  && !empty($unserParams['table']['grade']['sort']))
             $unserParams['table']['grade']['sort']= json_decode(strtolower(json_encode($unserParams['table']['grade']['sort'])),true);

         if(isset($unserParams['table']['metric'])){
             $unserParams['table']['metric']=strtolower($unserParams['table']['metric']);
         }
         if(isset($unserParams['table']['group'])){
             $unserParams['table']['group']=strtolower($unserParams['table']['group']);
         }
         if(isset($unserParams['table']['group']) ||  isset($unserParams['table']['metric']) ){
            $confArr['metric']=strtolower($unserParams['table']['metric']);
            $confArr['group']=strtolower($unserParams['table']['group']);
         }
         $confArr['params']=$unserParams;
         
         /*if(!empty($confArr['params']['table']['grade']['sort'])){
            array_unshift($confArr['params']['table']['grade']['sort'],'date');
         }*/
         
        //echo '<pre/>';print_r($confArr);exit();
         return $confArr;
     }

     function  saveReoportbyAuth($id,$group){

         $sql = 'update ' . $this->reportTable . ' set `auth`=' . '\'' . $group . '\'' . ' where id='  . $id ;


         $res = Yii::app()->db_metric_meta->createCommand($sql)->execute();


         if ($res === False)
             return False;
         return true;


     }


     function  deleteReport($table_id){
         //$username = Yii::app()->user->username;
         $res=$this->objMenu->removeAllmenuReportbyTableid($table_id);


         if($res===false)
             return false;

         $sql='update '.$this->reportTable.' set flag=2  where id='.$table_id;
         $res=Yii::app()->db_metric_meta->createCommand($sql)->execute();
         if($res>0) {
             return true;
             /*
             $authObj=new AuthManager();
             $objReport=new ReportManager();
             $reprotInfo  =  $objReport->getReoport($table_id);
             $redisKey = $reprotInfo['id']."_".$reprotInfo['cn_name'];
             return   $authObj->offlinePoint($redisKey);
             */
         }

         return False;
     }

     function deleteReportCustom($table_id)
     {
         $sql = 'delete from t_visual_table_custom where id=' . $table_id;
         $res = Yii::app()->db_metric_meta->createCommand($sql)->execute();
         if ($res > 0) {
             return true;
         }

         return False;
     }

     function  upReport($table_id){
         $sql='update '.$this->reportTable.' set flag=1  where id='.$table_id;
         $res=Yii::app()->db_metric_meta->createCommand($sql)->execute();
         $param['table_id'] = $table_id;
         $this->objBehavior->addUserBehaviorToLog(0,$table_id,'/report/onlinereport/table_id/'.$table_id,$param);
         if($res>0) {
             $authObj=new AuthManager();
             $authObj->syncPoint($table_id);
             return True;
         }
         return False;
     }

     # 显示自定义报表
     function showReportCustom($id, $REQUEST = null, $preview = false)
     {
         if ($preview == false) {
             $confArr = $this->getReportCustom($id);
             if (empty($confArr)) {
                 return array();
             }

             $unserParams         = $confArr['params'];
             $unserParams['type'] = $confArr['type'];
         } else if ($id == false) {
             $unserParams = $preview;
         } else {
             return array();
         }
         //获取所有mapkey
         $mapdata = $this->objVisual->selectMapData();
         $mapkeys = array();
         foreach ($mapdata as $key => $mk) {
             $mapkeys[$mk['map_key']] = $mk['map_data'];
         }

         //获取名称
         if (!empty($confArr)) {
             $unserParams['basereport']['author'] = $confArr['chinese_name'];
             $unserParams['reportId']             = $id;
         }
         //合并及时过滤
         $tablelist = isset($unserParams['tablelist']) ? $unserParams['tablelist'] : [];
         foreach ($tablelist as $key => $table) {
             $table_data = $table['grade']['data'];
             foreach ($table_data as $k => $tv) {

                 if (array_key_exists('search', $tv) and is_array($tv['search']) and array_key_exists('mapkey', $tv['search']) and $tv['search']['mapkey'] != '-' and trim($tv['search']['mapkey']) != '') {
                     $search_mapkey = $tv['search']['mapkey'];
                     if (array_key_exists($search_mapkey, $mapkeys) and trim($mapkeys[$search_mapkey]) != '') {
                         $mapkey_vs = $this->objVisual->selectMapDataByCache($search_mapkey);
                         $search_vs = array();
                         if (array_key_exists('val', $tv['search']) and trim($tv['search']['val']) != '') {

                             $search_vs = explode(PHP_EOL, trim($tv['search']['val']));
                         }
                         $unserParams['tablelist'][$key]['grade']['data'][$k]['search']['val'] = implode(PHP_EOL, array_unique(array_merge($search_vs, $mapkey_vs)));

                     }
                 }
             }
         }

         $tplArr['params'] = json_encode($unserParams);

         $tplArr['confArr'] = $confArr;
         if ($confArr['type'] == 3) {
             $configData = json_decode(rawurldecode($confArr['params']['config']), true);
             $chartInfo  = array();
             foreach ($configData as $key => $contrastInfo) {
                 $contrastInfo['source']['date']                    = date("Y-m-d", strtotime('-7 day'));
                 $contrastInfo['source']['edate']                   = date("Y-m-d", strtotime('-1 day'));
                 $oneChart                                          = $contrastInfo['source'];
                 $metricKey                                         = $this->getMetricName($contrastInfo);
                 $oneChart['chartconf'][0]['chartData'][]           = $metricKey;
                 $oneChart['chartconf'][0]['chartKeys'][$metricKey] = $contrastInfo['name'];
                 $oneChart['chartconf'][0]['chartType']             = 'spline';
                 $oneChart['chartconf'][0]['chartTitle']            = '';
                 $oneChart['order']                                 = 'desc';
                 $chartInfo[]                                       = $oneChart;

             }
             $data                       = $this->deriveReport($configData);
             $configSpine                = array();
             $configSpine['chart_type']  = 'spline';
             $configSpine['chart_title'] = '';
             $configSpine['dataConig']   = $chartInfo;
             $html                       = $this->chart->getChartContiner($configSpine);
             $tplArr['charthtml']        = $html;
             $tplArr['easyInfo']         = $data['easyInfo'];
             $tplArr['chartData']        = $data['chartData'];
         }
         $tplArr['config'] = json_encode($this->objFackcube->get_app_conf(array('project' => $confArr['project']), true));
         $tplArr['isCollectCustom'] = true;

         return $tplArr;
     }

     function showReport($id,$REQUEST=null,$preview=false){
         $isOffline = $_REQUEST['isOffline'];
         if($preview==false){
             $confArr = $this->getReoport($id);
             if($confArr['flag'] == '2' and $isOffline != '1'){
                 //unset($_REQUEST['isOffline']);
                 echo "<script>alert('该报表已被下线,请在报表管理页搜索并查看该报表'); window.location.href ='/report/reportlist'</script>";
                 exit ();
             }
             if(empty($confArr))
                 return array();
             //外链报表
             if($confArr['type']==9){
                 $params=json_decode($confArr['params'],true);
                 $confArr['url'] = str_replace('open=1', "open=1&id={$confArr['id']}&isCollect=0", $params['url']);
                 $tplArr['confArr'] = $confArr;
                 return $tplArr;
             }

             $unserParams = $confArr['params'];
             $unserParams['type'] = $confArr['type'];
         }else if($id==false){
             $unserParams=$preview;
         }else{
             return array();
         }
         //获取所有mapkey
         $mapdata=$this->objVisual->selectMapData();
         $mapkeys=array();
         foreach($mapdata as $key=>$mk){
             $mapkeys[$mk['map_key']]=$mk['map_data'];
         }

         //获取名称
         if(!empty($confArr)){
             $unserParams['basereport']['author'] = $confArr['chinese_name'];
             $unserParams['reportId'] = $id;
         }
         //合并及时过滤
         $tablelist = isset($unserParams['tablelist']) ? $unserParams['tablelist'] : [];
         foreach($tablelist as $key=>$table){
            $table_data=$table['grade']['data'];
            foreach($table_data as $k=>$tv){

                if(array_key_exists('search',$tv) and is_array($tv['search']) and array_key_exists('mapkey',$tv['search']) and $tv['search']['mapkey']!='-' and trim($tv['search']['mapkey'])!=''){
                    $search_mapkey=$tv['search']['mapkey'];
                    if(array_key_exists($search_mapkey,$mapkeys) and trim($mapkeys[$search_mapkey])!=''){
                        $mapkey_vs=$this->objVisual->selectMapDataByCache($search_mapkey);
//                        $mapkey_vs=explode(PHP_EOL,trim($mapkeys[$search_mapkey]));
                        $search_vs=array();
                        if(array_key_exists('val',$tv['search']) and trim($tv['search']['val'])!=''){

                            $search_vs=explode(PHP_EOL,trim($tv['search']['val']));
                        }
                        $unserParams['tablelist'][$key]['grade']['data'][$k]['search']['val']=implode(PHP_EOL,array_unique(array_merge($search_vs,$mapkey_vs)));

                    }
                }
            }
         }
        
         $tplArr['params'] = json_encode($unserParams);

         # 定时刷新报表
         $tplArr['refreshSet']  = isset($unserParams['basereport']['refresh_set']) ? $unserParams['basereport']['refresh_set'] : '0';
         $tplArr['refreshTime'] = isset($unserParams['basereport']['refresh_time']) ? $unserParams['basereport']['refresh_time'] : '5';

         $tplArr['confArr'] = $confArr;
         if($confArr['type']==3 ){
             $configData  = json_decode(rawurldecode($confArr['params']['config']),true);
             $chartInfo = array();
             foreach ($configData as $key => $contrastInfo) {
                $contrastInfo['source']['date'] = date("Y-m-d", strtotime('-7 day'));
                $contrastInfo['source']['edate'] = date("Y-m-d", strtotime('-1 day'));
                $oneChart = $contrastInfo['source'];
                $metricKey = $this->getMetricName($contrastInfo);
                $oneChart['chartconf'][0]['chartData'][] =  $metricKey;
                $oneChart['chartconf'][0]['chartKeys'][$metricKey] = $contrastInfo['name'];
                $oneChart['chartconf'][0]['chartType'] = 'spline';
                $oneChart['chartconf'][0]['chartTitle'] = '';
                $oneChart['order'] = 'desc';
                $chartInfo[] = $oneChart;

             }
             $data  = $this->deriveReport($configData);
             $configSpine =  array();
             $configSpine['chart_type'] = 'spline';
             $configSpine['chart_title'] = '';
             $configSpine['dataConig'] = $chartInfo;
             $html = $this->chart->getChartContiner($configSpine);
             $tplArr['charthtml'] = $html;
             $tplArr['easyInfo'] = $data['easyInfo'];
             $tplArr['chartData'] = $data['chartData'];
         }
         $tplArr['config'] = json_encode($this->objFackcube->get_app_conf(array('project' => $confArr['project']), true));
//        echo '<pre/>'; print_r($tplArr);exit();
         return $tplArr;
     }
     function getMetricName($contrastInfo){
        if (isset($contrastInfo['source']['udcconf'])) {
             if($contrastInfo['source']['udc'] !=''){
                 $metricall = explode("=", $contrastInfo['source']['udc']);
             }
         $metricKey = $metricall[0];
        } else {
            $metricKey = implode("_", explode(".", $contrastInfo['source']['metric']));
        }
        return $metricKey;
     }

     function  deriveReport($groupInfo,$status=true){
         //var_dump($groupInfo);
         foreach ($groupInfo as $kid => $keyVal) {
             $checkedData = array();
             $checkedData['name'] = $keyVal['name'];
             $keyVal['source']['total'] = 0;
             if ($status) {
                 $keyVal['source']['date'] = date("Y-m-d", strtotime('-7 day'));
                 $keyVal['source']['edate'] = date("Y-m-d", strtotime('-1 day'));
             }
             if ($keyVal['source']['type'] == 8 && !isset($keyVal['source']['offset'])) {
                 $keyVal['source']['offset']='1000000';
             }
             //var_dump($keyVal['source']);
             $returnData = $this->objFackcube->getData($keyVal['source']);
//var_dump($returnData);
             if ($keyVal['source']['type'] == 8) {
                 $re_new = $returnData['data'];

                 foreach ($returnData['data'] as $k2 => $v2) {
                     $re_new[$k2]['date']=$re_new[$k2]['cdate'];
                     unset($re_new[$k2]['cdate']);
                     //处理数据
                     if(is_array(json_decode($groupInfo[$kid]['source']['filter'],true))){
                         foreach(json_decode($groupInfo[$kid]['source']['filter'],true) as $k3 =>$v3) {


                             if($re_new[$k2][$v3['key']]!=$v3['val'])
                                 unset($re_new[$k2]);
                         }
                     }

                 }
                 $returnData['data']=$re_new;
             }

             if ($returnData['status'] == 0) {
                 if (empty($returnData['data'])) {
                     echo "<div class='container' style='color:#ff0000'>该时间段没有数据，请选择其它时间段</div>";
                 } else {
                     // if (isset($keyVal['source']['udcconf'])) {
                     //     if($keyVal['source']['udc'] !=''){
                     //         $metricall = explode("=", $keyVal['source']['udc']);
                     //     }
                     //     $metricKey = $metricall[0];
                     // } else {
                     //     $metricKey = implode("_", explode(".", $keyVal['source']['metric']));
                     // }

                     if(isset($keyVal['source']['showthis'])){
                     $metricKey=$keyVal['source']['showthis'];
                     }else{
                         $metricKey = $this->getMetricName($keyVal);
                     }

                     $checkedData['data'] = $this->chart->getChartData($returnData['data'], $metricKey);
                     $chartData[] = $checkedData;
                 }

             } else {
                 echo "<div class='container' style='color:red'>" . $returnData['msg'] . "</div>";
             }
         }
         $newMerge = $this->common->chartTable($chartData);
         if(empty($newMerge)){
            $data['easyInfo'] = '[]';
            $data['chartData'] = '[]';
            return $data;
         }
         $headerName = array();
         $num = 0;
         foreach ($newMerge['data'] as $key => $val) {
             //获取最长名称
             if (count($val) > $num) {
                 foreach ($val as $keyid => $val) {
                     if (!in_array($keyid, $headerName)) {
                         array_push($headerName, $keyid);
                     }
                 }
                 $num = count($val);
             }
         }
         $easyData = array();
         $easyData['total'] = count($newMerge['data']);
         foreach ($newMerge['data'] as $it => $one) {
             $oneitem = array();
             $oneitem['dt'] = $it;
             $oneitem = array_merge($oneitem, $one);
             $easyData['rows'][] = $oneitem;
         }
         $easyInfo = array();
         $easyInfo['easyHeader'] = $newMerge['header'];
         $easyData['rows'] = $this->common->arrSort($easyData['rows'],'dt','desc');
         $easyInfo['easyData'] = $easyData;
         $data['easyInfo'] =json_encode($easyInfo);
         $data['chartData'] = json_encode($chartData);
         return $data;
     }
     function getWorkReport($id){
        $sql = "select  * from  t_dolphin_stat_easytable_info  where  id =".$id;
        $db = Yii::app()->sdb_dolphin_stat;
        $data = $db->createCommand($sql)->queryAll();
        switch ($data[0]['tpl_name']) {
            case 'tablelist':
                $url = '/biData/tplShow/tablelist/'.$id;
                $sign = 'biData/tplShow/tablelist/'.$id;
                break;
            case 'multitable':
                $url = '/biData/tplShow/multitable/'.$id;
                $sign = 'biData/tplShow/multitable/'.$id;
                break;
            case 'chartlist':
                $url = '/biData/tplShow/chartlist/'.$id;
                $sign = 'biData/tplShow/chartlist/'.$id;
                break;
            case 'treelist':
                $url = '/biData/tplShow/treelist/'.$id;
                $sign = 'biData/tplShow/treelist/'.$id;
                break;
            case 'waterfall':
                $url = '/biData/tplShow/waterfall/'.$id;
                $sign = 'biData/tplShow/waterfall/'.$id;
                break;
        }
        return  array('url'=>$url,'name'=>$data[0]['title'],'id'=>$id);
     }

     function hasChart($id){
         $confArr = $this->getReoport($id);
         $reportParams = $confArr['params'];
         $chartInfo = $reportParams['chart'];
         if(isset($chartInfo) && !empty($chartInfo) ){
            return true;
         }else{
            return false;
         }

     }

         //小工具报表 保存
    function saveToolReport($params){
        $type = $params['type'] ? $params['type'] : 4;
        $project = $params['project'] ? $params['project'] : "";
        //print_r($params);exit();
        //$params['hql']=addslashes($params['hql']);
        $temparrhql= array("hql"=>$params['hql'],"hqldata"=>$params['hqldata']);
        $params['params'] = json_encode( $temparrhql);
        $params['params']=addslashes($params['params']);
        //$params['params']['hql']=addslashes($params['params']['hql']);

         //获取中文名称
         $dataArr = array(
             "'" . $params['cn_name'] . "'",
             "'" . $params['explain'] . "'",
             "'" . $project . "'",
             "'" . $params['params']. "'",
             "'" . Yii::app()->user->username . "'",
             "'" . Yii::app()->user->name . "'",
             "'" .$type . "'",
         );
         $sql = "insert into  " .  $this->reportTable . "(`cn_name`,`explain`,`project`,`params`,`creater`,`chinese_name`,`type`) values(" . implode(",", $dataArr) . ") ";
         //print_r($sql);exit();
         $db = Yii::app()->db_metric_meta;
         $db->createCommand($sql)->execute();

         $id = $db->getLastInsertID();
         //添加用户行为记录
         $menu_id = 0;
         $param = array();
         $param['table_id'] =  $id;
         $param['menu_id'] = $menu_id;
         $this->objBehavior->addUserBehaviorToLog($id,$menu_id,'/report/addreport/table_id/'.$id,$param);
         return $id;
     }

     //小工具报表 保存
    function updateToolReport($params){
         $id = $params['id'];
         unset($params['id']);
         $temparrhql= array("hql"=>$params['hql'],"hqldata"=>$params['hqldata']);
         $params['params'] = json_encode( $temparrhql);
         $params['params']=addslashes($params['params']);

         $setParams = array('`cn_name`', '`explain`','`params`', '`modify_user`');
         $dataArr = array(
             "'" . $params['cn_name'] . "'",
             "'" .$params['explain']  . "'",
             "'" . $params['params'] . "'",
             "'" . Yii::app()->user->username . "'",
         );

         $sqlParams = array();
         foreach ($setParams as $key => $value) {
             $sqlParams[] = $value . "=" . $dataArr[$key];
         }

         $sql = "update " . $this->reportTable . " set " . implode(",", $sqlParams) . " where id =" . $id;
        // print_r($sql);exit();
         $db = Yii::app()->db_metric_meta;
         $db->createCommand($sql)->execute();

         //权限名称同步
         //$this->auth->checkName();
         return $id;

     }

    function getToolReport($id = false){
        $sql = "select  * from  " . $this->reportTable;
         if ($id) {
             $whereStr = "  where  id =" . $id;
         } else {
             $whereStr = "";
         }
         $sql = $sql . $whereStr;
         $db = Yii::app()->sdb_metric_meta;
         $confArr = $db->createCommand($sql)->queryRow();
         if(empty($confArr)){
             return array();
         }
         return $confArr;
    }

    /*
        wap站获取最近访问的报表
        'all',date("Y-m-d H:i:s",strtotime("-3 day"))
    */
    function getRecentlyReport($type,$time){
        $username=Yii::app()->user->username;
        if($type =='wap'){
            $sql =" select  distinct(user_action),cdate,user_name,param from t_visual_behavior_log  where  user_action
             like '%wap/report%'  and   cdate  >='{$time}' and user_name like '{$username}' group by  user_action";
        }else{
            $sql="select  distinct(param_id),param,cdate,user_name,count(*) as count_ua from(
SELECT substring(param,14,5) as param_id,param,cdate,user_name,user_action from t_visual_behavior_log
where  (user_action like '%wap/report%' OR user_action like '%visual/index%')
and user_action like '%menu_id%' and cdate  >='{$time}'
and user_name like '{$username}'
)aa
where param_id !='http:' group by param_id ORDER BY count_ua DESC limit 15";
        }

        $db =  Yii::app()->sdb_metric_meta;
        $re =  $db->createCommand($sql)->queryAll();
        return $re;
    }

     /*
        获取最近30天访问者
        'all',date("Y-m-d H:i:s",strtotime("-30 day"))
    */
     function getRecentlyUser($time){
         $username=Yii::app()->user->username;

             $sql =" select  distinct(user_name),cdate from t_visual_behavior_log  where
  cdate  >='{$time}'  group by  user_name";

         $db =  Yii::app()->sdb_metric_meta;
         $re =  $db->createCommand($sql)->queryAll();
         return $re;
     }

    /*
       根据关键字搜索报表
    */
    function getKeyWords($keyword){
        $sql =" select  * from  t_visual_table where cn_name  like '%".$keyword."%' group by cn_name";
        $db =  Yii::app()->sdb_metric_meta;
        $re =  $db->createCommand($sql)->queryAll();
        //去掉用户
        if(!empty($re)){
            foreach ($re as $key => $value) {
                $menuInfo  =  $this->objMenu->getMenuByReoprt($value['id']);
                if(empty($menuInfo)){
                   unset($re[$key]); 
                }
            }
        }
        return $re;
    }
 }

