<?php
 class MenuManager extends Manager{
     function __construct(){
         $this->menuTable='t_visual_menu';
         $this->reportTable='t_visual_table';
         $this->userTable='t_visual_user';
         $this->favoriteTable='t_visual_favorites';
         $this->rolesTable = 't_eel_admin_role';
         $this->deleteRoleTable='t_eel_admin_delete_role';
         $this->userRolesTable = 't_eel_admin_relation_user';
         $this->reportRolesTable = 't_eel_admin_relation_report';
         $this->objBehavior = new BehaviorManager();
         $this->objComm = new CommonManager();
         $this->allmenutable=array();
     }


     //兼容menu表中json格式的tableid
     function  removeAllmenuReportbyTableid($table_id){
         $username = Yii::app()->user->username;
         $username = str_ireplace(['@.com', '@.com'], '', $username);
         $objReport = new ReportManager();
         $reprotInfo = $objReport->getReoport($table_id);
         $reportName = $reprotInfo["cn_name"];
         $reportCreater = $reprotInfo["creater"];
         $reportCreater = str_ireplace(['@.com', '@.com'], '', $reportCreater);
         $reportModifyer = $reprotInfo["modify_user"];
         $reportModifyer = str_ireplace(['@.com', '@.com'], '', $reportModifyer);
         unset($objReport);
         $menuInfoList = $this->selectMenu();
         $flag = 1; //标识变量，表示是否找到了要更改的menu记录
         $removeMenuStr = '';
         $removeIdStr = '';
         foreach($menuInfoList as $key => $menuInfo){
              //表示找到要下线的菜单
             $menuId = $menuInfo['id'];
             $tableInfo = $menuInfo['all'];
             $firstMenu = $menuInfo['first_menu'];
             $secondMenu  = $menuInfo['second_menu'];
             $table_visit = array();
             $updateArray = array();
             foreach($tableInfo as $key => $table_val){
                 $tableId = $table_val['id'];
                 if($tableId==$table_id){
                     $flag = 2;
                     //记录下线通知邮件中的菜单信息
                     if(empty($removeMenuStr)){
                         $removeMenuStr = $removeMenuStr.$firstMenu.'->'.$secondMenu;
                     }else{
                         $removeMenuStr = $removeMenuStr.'，'.$firstMenu.'->'.$secondMenu;
                     }

                     //记录用户行为表中的菜单信息
                     if(empty($removeIdStr)){
                        $removeIdStr = $menuId;
                     }else{
                        $removeIdStr = $removeIdStr.'/'.$menuId;
                     }
                     Yii::log("Table id:".$tableId." will be offline",'info');
                     continue;
                 }
                 $table_visit[] = $table_val;
             }
             $updateArray['table_id'] = $table_visit;
             if($flag == 2){
                $res = $this->updateMenu($menuId,$updateArray);
                $flag = 1;
             }
         }
         //记录下线报表的用户行为
         $param = array();
         $param['table_id'] = $table_id;
         $param['menu_id'] = $removeIdStr;
         $this->objBehavior->addUserBehaviorToLog($table_id,'0','/report/deletereport/table_id/'.$table_id,$param);
         //发送邮件通知创建人和修改人

         $reason = '';
         if(!isset($username)){
             $reason = '该报表在30天前被创建，在此期间，无人从菜单页或报表管理页中点击访问该报表。';
         }else{
             $reason = '该报表已被'.$username.'手动下线。';
         }
         if(!empty($removeMenuStr)){
             $removeMenuStr.='。';
         }
         $mailBody = '<div>监控内容：您创建或编辑的报表<b> '.$reportName.'(ID:'.$table_id.') </b>已做下线处理。</div>
                    <div>触发原因：'.$reason.'</div>
                    <div>所属菜单：'.$removeMenuStr.'</div>
                    <div>恢复方法：请进入<a href="http://dt..com/report/reportlist">报表管理页面</a>，在"search"查询框输入 报表id（'.$table_id.'）或者报表名（'.$reportName.'） 进行查询，点击上线按钮即可完成报表上线。
                    如需将该报表挂到菜单下，请在<a href="http://dt..com/menu/index">菜单管理页面</a>添加该报表。</div>';

         $mailAddress = 'bi-service@.com';
         if(!empty($reportCreater)){
             $mailAddress = $mailAddress.";".$reportCreater."@.com";
         }
         if(!empty($reportModifyer)){
             $mailAddress = $mailAddress.";".$reportModifyer."@.com";
         }
         $this->objComm->sendMail($mailAddress,$mailBody,'【监控】data平台报表下线通知');

         if($res===false)
             return false;
         return True;
     }


     function  getMenuByReoprt($table_id){
         if (empty($table_id))
             return False;
         //去掉了flag = 1的条件
         $sql="select * from  $this->menuTable where flag = 1 and table_id  like  '%\"id\":\"$table_id\"%'";
         $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
         return $result;
     }


    function  selectBymenuName($first_menu,$second_menu){

        $result = Yii::app()->sdb_metric_meta->createCommand()
            ->select('*')->from($this->menuTable)
            ->where('first_menu=:first_menu and second_menu=:second_menu and flag=1', array(':first_menu' => $first_menu,
                ':second_menu'=>$second_menu ))
            ->queryRow();

        return $result;

    }
     function addMenu($first_menu,$second_menu,$table_id='',$type=0){

         $result = $this->selectBymenuName($first_menu,$second_menu);

         if(!empty($result))
             return false;

         if(empty($first_menu))
             return false;
         if(is_array($table_id)){
             //  $table_id=implode(',',$table_id);
             $tmp_table_id=array();
             foreach($table_id as $subv){
                 $tmp_table_id[]=$subv;
             }
             $table_id=addslashes(json_encode($tmp_table_id/*,JSON_UNESCAPED_UNICODE*/));

         }

         $dataArr=array($first_menu,$second_menu,$type,$table_id,Yii::app()->user->username);
         $valueStr='';

         //添加用户行为param
         $param = array();
         $param['first_menu'] = $first_menu;
         $param['second_menu'] = $second_menu;
         $param['type'] = $type;
         $table_id = stripslashes($table_id);
         $param['table_id'] = $table_id;
         foreach($dataArr as $value){
             $valueStr.="'".$value."',";

         }
         $valueStr=trim($valueStr,',');
         $sql = "insert into " .  $this->menuTable. "(`first_menu`,`second_menu`,`type`,`table_id`,`user_name`) values(" . $valueStr. ") ";
         Yii::Log($sql,'trace','MenuManager');

         Yii::app()->db_metric_meta->createCommand($sql)->execute();
         $id = Yii::app()->db_metric_meta->getLastInsertID();
         $this->objBehavior->addUserBehaviorToLog(0,$id,'/menu/addmenu/menu_id/'.$id,$param);

         if($id>0){
             return $id;
         }
         return False;
     }

     function updateMenu($menu_id,$updateArr){
        unset($updateArr['id']);
         $select_res=$this->selectMenu($menu_id);
         $table_res=$select_res['all'];
         $sql_pefix='update '. $this->menuTable.' set type=0,';
         $sql_suffix=' where id='.$menu_id;
         $sql='';
             foreach($updateArr as $k=>$v){
             if($k=='table_id'&&is_array($v)){
                 
                 $inter=array_intersect_key($select_res['all'],$v);
              //   var_dump($inter);
                 $diff=array_diff_key($v,$inter);
                // var_dump($diff);
                // exit();
                 $table_res=array();

                 foreach($inter as $v){
                     $table_res[]=$v;
                 }
                 foreach($diff as $v){
                     $table_res[]=$v;
                 }
                 $v=addslashes(json_encode($table_res/*,JSON_UNESCAPED_UNICODE*/));
             }
            $sql.=''.$k.'='.'\''.$v.'\',';
         }
         $sql=trim($sql,',');
         $sql=$sql_pefix.$sql.$sql_suffix;
         $res=Yii::app()->db_metric_meta->createCommand($sql)->execute();


        /* if($res>0) {

             return True;
         }

         return False;*/
         return True;




     }

     function  deleteMenu($menu_id){
         $sql='update '.$this->menuTable.' set flag=2  where id='.$menu_id;
         $res=Yii::app()->db_metric_meta->createCommand($sql)->execute();
         if($res>0) {

             return True;
         }

         return False;



     }
     /*
      * type=3特殊菜单没有二级菜单,直接访问
      * */
     function selectURLMenu(){
         $sql='select * from '.$this->menuTable.' where flag=1 and  (second_menu is null or second_menu=\'\') and type=3 and id!=1';
         $suffix=' order by id asc';
         $sql=$sql.$suffix;
         $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();

         return $result;
     }
     function  selectJsonMenu($menu_id=NULL){


         $sql='select * from '.$this->menuTable.' where flag=1 and  second_menu is not null and second_menu!=\'\' and id!=1';
         $suffix='';
         if(!empty($menu_id)){
             $suffix=' and id='.$menu_id;
         }
         $suffix=$suffix.' order by first_menu,sort asc';
         $sql=$sql.$suffix;
         $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();

         return $result;
     }

     /**
      * @param null $menu_id
      * @return mixed
      */
     function selectMenu($menu_id=NULL){
         $result=$this->selectJsonMenu($menu_id);
         foreach($result as $k=>$tmp){
             $resultUrl=array();
             $resultTable=array();
             $resultall=array();
             if($tmp['type'] ==1){
                 $resultTable=explode(',',$tmp['table_id']);
                 foreach($resultTable as $subtmp){
                     $resultall[$subtmp]=array('type'=>1,'id'=>$subtmp);
                     $this->allmenutable[$subtmp]=1;

                 }
             }elseif($tmp['type']==0){
                 $arrTableid=json_decode($tmp['table_id'],true);
                 if(is_array($arrTableid)){
                     foreach($arrTableid as $tmpTable){
                         if($tmpTable['type'] ==1 ){
                             $resultTable[]=$tmpTable['id'];
                             $resultall[$tmpTable['id']]=$tmpTable;
                             $this->allmenutable[$tmpTable['id']]=1;
                         }elseif($tmpTable['type']==2){
                             $suburl=implode(':',array('name'=>$tmpTable['name'],'url'=>$tmpTable['url']));
                             $resultall[$suburl]=$tmpTable;
                             $resultUrl[]=$suburl;
                         }elseif($tmpTable['type'] ==3 ){
                             $resultTable[]=$tmpTable['id'];
                             $resultall[$tmpTable['id']]=$tmpTable;
                             $this->allmenutable[$tmpTable['id']]=3;
                         }

                     }
                 }

             }
             unset($tmp['table_id']);
             $result[$k]=$tmp;
             $result[$k]['table_id']=implode(',',$resultTable);
             $result[$k]['url']=implode("\n",$resultUrl);
             $result[$k]['all']=$resultall;
             $result[$k]['arr_table']=$resultTable;
             $result[$k]['arr_url']=$resultUrl;

         }
         if(!empty($menu_id) && $result){
             return $result[0];
         }
         return $result;

     }
     function  selectFirstMenu(){
         $sql="select DISTINCT  first_menu,id from ".$this->menuTable."  where flag=1 and second_menu='' order by sort asc, id asc";//概览型报表
         $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
         return $result;

     }

     function  saveSortMenu($sortinfo){
         $transaction=Yii::app()->db_metric_meta->beginTransaction();
         try{
             foreach($sortinfo as $k=>$v){
                 if (!empty($v['second_menu'])) {
                     $sql = "update $this->menuTable set sort=$k where id=" . $v[id];
                     Yii::app()->db_metric_meta->createCommand($sql)->execute();
                 }
             }
             $transaction->commit();

         }catch(Exception $e){
             $transaction->rollback();
             return False;
         }
         return True;
     }

     function  selectSecnodMenu($first_menu){
         $result=Yii::app()->sdb_metric_meta->createCommand()
             ->select('id,second_menu')
             ->from($this->menuTable)
             ->where('first_menu=:first_menu  and flag=1 order by sort asc', array(':first_menu' => $first_menu))
             ->queryAll();

         return $result;

     }
     function  getSecondMenu($first_menu){
         $sql="select * from ".$this->menuTable."  where flag=1 and first_menu ='".$first_menu."' and second_menu !='' order by sort asc, id asc"; 
         $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
         return $result;

     }

     //添加t_visual_menu 一级菜单信息(first_menu)
     function addFirstMenu($first_menu, &$message)
     {
         //检查是否已存在要添加菜单
         $first_menu_sql = "select id, first_menu from {$this->menuTable} where flag=1 and second_menu='' and first_menu = '{$first_menu}'";
         $db = Yii::app()->db_metric_meta;
         $alreadyExistFirstMenu = $db->createCommand($first_menu_sql)->queryAll();
         if (!empty($alreadyExistFirstMenu)) {
             $message = '一级菜单已存在';
             return false;
         }
         //添加
         $username = Yii::app()->user->username;
         $insert_sql = "insert into {$this->menuTable} (first_menu, second_menu,table_id, user_name, flag) values ('{$first_menu}','', '', '{$username}', 1)";
         $db = Yii::app()->db_metric_meta;
         $res = $db->createCommand($insert_sql)->execute();
         if ($res <= 0) {
             $message = '添加失败';
             return false;
         }
         $message = '添加成功';
         return true;
     }

     //根据menu_id得到导航栏
     function getNavigationMenu($menu_id = '')
     {
         $result=Yii::app()->sdb_metric_meta->createCommand()
             ->from($this->menuTable)
             ->where('id=:menu_id', array(':menu_id' => $menu_id))
             ->queryRow();

         return $result;
     }

     function getAddAndRemoveMenuList($new, $old)
     {
         $addRuleList    = [];
         $removeRuleList = [];

         foreach ($old as $oldItem) {
             if (!in_array($oldItem, $new) && !empty($oldItem)) {
                 $removeRuleList[] = $oldItem;
             }
         }

         foreach ($new as $newItem) {
             if (!in_array($newItem, $old) && !empty($newItem)) {
                 $addRuleList[] = $newItem;
             }
         }

         return [$addRuleList, $removeRuleList];
     }

     function addRuleListForAUTH($menuId, $newList, $oldList)
     {
         // 删除后挂载在另一菜单位置规则
         $updateMoveList = [];
         $updateMoveData = Yii::app()->sdb_metric_meta->createCommand()
             ->from($this->reportRolesTable)
             ->where(array('in', 'report_id', $newList))
             ->queryAll();
         foreach ($updateMoveData as $updateMoveItem) {
             $updateMoveList["{$menuId}_{$updateMoveItem['report_id']}"] = [
                 'menu_id'  => $menuId,
                 'table_id' => $updateMoveItem['report_id'],
                 'role_id'  => $updateMoveItem['role_id'],
             ];
         }

         // 删除后重新挂载同一菜单位置规则
         // 从【删除后挂载在另一菜单位置规则】剔除
         $updateResetList = [];
         $allRoleName     = array_keys($updateMoveList);
         $result          = Yii::app()->sdb_metric_meta->createCommand()
             ->from($this->rolesTable)
             ->where(array('in', 'role_name', $allRoleName))
             ->queryAll();

         foreach ($result as $item) {
             $currentRoleName   = $item['role_name'];
             $updateResetList[] = $currentRoleName;
             unset($updateMoveList[$currentRoleName]);
         }

         // 新增规则
         $insertList = [];
         foreach ($newList as $newItem) {
             if (!empty($newItem) && !isset($updateMoveList["{$menuId}_{$newItem}"]) && !in_array("{$menuId}_{$newItem}", $updateResetList)) {
                 $insertList["{$menuId}_{$newItem}"] = [
                     'menu_id'  => $menuId,
                     'table_id' => $newItem,
                 ];
             }
         }

         // 删除规则
         $removeList = [];
         foreach ($oldList as $oldItem) {
             if (!empty($oldItem)) {
                 $removeList["{$menuId}_{$oldItem}"] = [
                     'menu_id'  => $menuId,
                     'table_id' => $oldItem,
                 ];
             }
         }

         $html = "";
         $this->insertRules($insertList, $html);           // 新增规则
         $this->updateResetRules($updateResetList, $html); // 删除后重新挂载同一菜单位置规则
         $this->updateMoveRules($updateMoveList, $html);   // 删除后挂载在另一菜单位置规则
         $this->removeRule($removeList, $html);            // 删除规则
     }

     private function insertRules($insertList, &$html)
     {
         $user = Yii::app()->user->username ?: '';

         // 添加新的权限分组
         if (!empty($insertList)) {
             $transaction = Yii::app()->db_metric_meta->beginTransaction();
             try {
                 foreach ($insertList as $k => $v) {
                     Yii::app()->sdb_metric_meta->createCommand()
                         ->insert($this->rolesTable, array('role_name' => $k));
                     $id = Yii::app()->sdb_metric_meta->getLastInsertID();

                     Yii::app()->sdb_metric_meta->createCommand()
                         ->insert($this->reportRolesTable, array('report_id' => $v['table_id'], 'role_id' => $id, 'level_id' => 0));
                 }
                 $transaction->commit();

             } catch (Exception $e) {
                 $html .= '1. 新添加部分: ' . '<br>';
                 foreach ($insertList as $k => $v) {
                     $html .= "role_name: {$k}, report_id: {$v['table_id']}, user: {$user}" . '<br>';
                 }

                 $transaction->rollback();
             }
         }
     }

     private function removeRule($removeList, &$html)
     {
         $user = Yii::app()->user->username ?: '';

         if (!empty($removeList)) {
             $transaction = Yii::app()->db_metric_meta->beginTransaction();
             try {
                 foreach ($removeList as $removeItem => $removeItemDetail) {
                     Yii::app()->sdb_metric_meta->createCommand()
                         ->insert($this->deleteRoleTable, [
                             'role_name'   => $removeItem,
                             'status'      => 1,
                             'create_user' => $user,
                             'modify_user' => $user,
                         ]);
                 }
                 $transaction->commit();

             } catch (Exception $e) {
                 $html .= '4. 删除部分: ' . '<br>';
                 foreach ($removeList as $removeItem => $removeItemDetail) {
                     $html .= "role_name: {$removeItem}, user: {$user}" . '<br>';
                 }

                 $transaction->rollback();
             }
         }

         if (!empty($html)) {
             ob_start();
             $this->objComm->sendMail('bi-service@.com', $html, '【重要】小伙子们，菜单添加规则出错了！！');
             ob_get_clean();
             ob_end_flush();
         }
     }

     private function updateMoveRules($insertMoveList, &$html)
     {
         if (!empty($insertMoveList)) {
             $transaction = Yii::app()->db_metric_meta->beginTransaction();
             try {
                 foreach ($insertMoveList as $moveItem => $moveItemDetail) {
                     Yii::app()->sdb_metric_meta->createCommand()
                         ->update($this->rolesTable, array('role_name' => $moveItem), 'role_id=:role_id', array(':role_id' => $moveItemDetail['role_id']));
                 }
                 $transaction->commit();
             } catch (Exception $e) {
                 $html .= '3. 删除后挂载在另一菜单位置规则部分: ' . '<br>';
                 foreach ($insertMoveList as $moveItem => $moveItemDetail) {
                     $html .= "role_name: {$moveItem}, role_id: {$moveItemDetail['role_id']}" . '<br>';
                 }

                 $transaction->rollback();
             }
         }
     }

     private function updateResetRules($updateResetList, &$html)
     {
         $user = Yii::app()->user->username ?: '';

         // 重新激活权限分组
         if (!empty($updateResetList)) {
             $transaction = Yii::app()->db_metric_meta->beginTransaction();
             try {
                 Yii::app()->sdb_metric_meta->createCommand()
                     ->update($this->deleteRoleTable, array('status' => 0, 'modify_user' => $user,), array('and', 'status = 1', array('in', 'role_name', $updateResetList)));

                 $transaction->commit();
             } catch (Exception $e) {
                 $html .= '4. 删除后重新挂载同一菜单位置规则部分: ' . '<br>';
                 foreach ($updateResetList as $k => $v) {
                     $html .= "role_name: {$v}, user: {$user}" . '<br>';
                 }

                 $transaction->rollback();
             }
         }
     }
 }
