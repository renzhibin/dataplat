<?php
class ToolController extends Controller{
	function  __construct(){
		$this->objFackcube=new FackcubeManager();
		$this->objAuth=new AuthManager();
		$this->objProject=new ProjectManager();
		$this->objReport = new ReportManager();
		$this->objoffline = new BehaviorManager();
		$this->objVisual= new VisualManager();
		$this->objTool = new ToolManager();
	}

	//2015-4-21 小工具
	function actionIndex(){
		$data = array('isEdit'=>'0','params'=>"");
		$this->render('tooltpl/addreport.tpl',$data);
	}

	//新增report
	function actionAddReport(){
		$data = array('isEdit'=>'0','params'=>"");
		$this->render('tooltpl/addreport.tpl',$data); 
	}

	//编辑report
	function actionEditReport(){
		$id = $_REQUEST['id'];
		if(!$id){
			$this->jsonOutPut(-1, '编辑报表id不能为空');
            exit;
		}

		$data = $this->objReport->getToolReport($id);
		$data['isEdit']='1';
		$data = json_encode($data);
		$params = array("params"=>$data);
		$this->render('tooltpl/addreport.tpl',$params); 
	}
	//编辑report savereport
	function actionSaveReport(){
		$datas = $_REQUEST['sendData'];
		$user = Yii::app()->user->username;
		$data = json_decode($datas,true);

		$res=ConstManager::checkName(trim($data['cn_name']),20);
        if($res===false){
            $this->jsonOutPut(1,'报表名必须是中英文、数字、小括号或者下划线且不超过20个字符');
            exit();
        }
        $res=ConstManager::checkWords(trim($data['cn_name']),'spam');
        if($res===false){
            $this->jsonOutPut(1,'报表名称不能包含spam关建字');
            exit();
        }
        if(!$this->objAuth->isProducer()){
            $this->jsonOutPut(1,'只有分析师组才可以编辑报表哦');
            exit();
        }

		if($data['isEdit'] == '0'){
			$checkData = $this->objReport->checkReport($data['cn_name']);
			if (!empty($checkData)) {
	            $this->jsonOutPut(-1, '已经存在相同名称的报表');
	            exit;
        	}

			//新增
			$id = $this->objReport->saveToolReport($data);

			if($id){
				$this->jsonOutPut(0, '保存成功','ok');
			} else {
				$this->jsonOutPut(-1, '保存失败');
			}
			
		} else {
			//编辑
			$checkData = $this->objReport->checkReport($data['cn_name'], $data['id']);

			if (!empty($checkData)) {
	            $this->jsonOutPut(-1, '已经存在相同名称的报表');
	            exit;
	        }

			//获取元报表名称
            $reprotInfo = $this->objReport->getReoport($data['id']);
            $srcName = $data['id']."_".$reprotInfo['cn_name'];
            $newName = $data['id']."_".$data['cn_name'];
            
            //调用developer名称更改接口
            $re = $this->objAuth->checkName($srcName,$newName);
			$id = $this->objReport->updateToolReport($data);
			if($id){
				$this->jsonOutPut(0, '更新成功','ok');
			} else {
				$this->jsonOutPut(-1, '更新失败');
			}
		}
	}

	//编辑report
	function actionHqlAnalyse(){
		$data = $_REQUEST['hql'];
		$result = $this->objFackcube->get_fakecube('get_query_tools_profile',array('hql'=>$data));	
		echo json_encode($result);
	}


	//查看report
	/*function actionShowReport(){
		$reportId = $_REQUEST['id'];
		if(!$reportId){
			$this->jsonOutPut(-1, '缺少报表id');
			return ;
		}
		$data = $this->objReport->getToolReport($reportId);
		$data['WEB_API'] = WEB_API;
		$this->render('tooltpl/showreport.tpl',$data); 

	}*/

	function actionGetDataReport(){
		$user = Yii::app()->user->username;
		$id = $_REQUEST['id'];
		$replace_params = $_REQUEST['pramsreplace'];
		$email_users = $_REQUEST['emaillist'];
		$starttime = $_REQUEST['starttime'];
		$endtime = $_REQUEST['endtime'];

		$data = $this->objReport->getToolReport($id);
		
		if(empty($data)){
			echo json_encode(array("status"=>-1,"msg"=>"提交失败","data"=>""));
			return;
		}

		$params = json_decode($data['params'],true);
		$data["params"] = $params;
		$dataArr = array('id'=>$id,"replace_params"=>$replace_params,"starttime"=>$starttime,
			"endtime"=>$endtime, "cols_params"=>json_encode($params['hqldata']),
		"tool_creater"=>$data['creater'],"task_creater"=>$user, "email_users"=>$email_users,"hql"=>$params['hql']);

		$data_arr = array_merge($dataArr,$data);
		$result = $this->objFackcube->get_fakecube('run_query_tool_task',$data_arr,true);
		$result['WEB_API'] = WEB_API;
		//print_r($result);exit();
		echo json_encode($result);

	}

	function actionListMapData(){
		$retu_type='';
		if ( isset($_REQUEST['retu_type']) ) {
			$retu_type = $_REQUEST['retu_type'];
		}
		$mapData=$this->objVisual->selectMapData();
		$tplArr=array('mapdata'=>$mapData);
		if ($retu_type!=''){
			echo $this->jsonOutPut(0,'success',$tplArr);
			exit;
		}
		$this->render('tooltpl/listmapdata.tpl',$tplArr);
	}
	function actionMapData(){
		$retu_type='';
		if ( isset($_REQUEST['retu_type']) ) {
			$retu_type = $_REQUEST['retu_type'];
		}
		$mapkey = $_REQUEST['mapkey'];
		$mapData=[];
		if($mapkey){
			$mapData=$this->objVisual->selectMapData($mapkey);
		}
		$tplArr=array('mapdata'=>$mapData);
		if ($retu_type!=''){
			echo $this->jsonOutPut(0,'success',$tplArr);
			exit;
		}

		$this->render('tooltpl/mapdata.tpl',$tplArr);
	}

	function actionCheckMapData(){
		if (!isset($_REQUEST['map_data'])||!isset($_REQUEST['map_data']) ) {
			echo $this->jsonOutPut(1,'参数为空');
			exit;
		}
		try{
			$map_data=$_REQUEST['map_data'];
			$cache_list=$this->objVisual->selectMapDataBySql(trim($map_data));
			echo $this->jsonOutPut(0,'success',$cache_list);
			exit;

		}catch(Exception $e){
			echo $this->jsonOutPut(1,'校验失败',$e->getMessage());
			exit;
		}



	}

	function actionSaveMapData(){

		if (!isset($_REQUEST['mapname'])||!isset($_REQUEST['mapkey']) ) {
			echo $this->jsonOutPut(1,'参数为空');
			exit;
		}
		$map_name=$_REQUEST['mapname'];
		$mapkey = $_REQUEST['mapkey'];
		$map_data=$_REQUEST['map_data'];
		try{
			$cache_list=$this->objVisual->selectMapDataBySql(trim($map_data));
			$cache_data=implode(PHP_EOL,$cache_list);
			$cache_key=$mapkey.':mapdata';
			$ret=Yii::app()->cache->set($cache_key,$cache_data);
			if(!$ret){
				echo $this->jsonOutPut(1,'保存缓存失败',$ret);
				exit;
			}
		}catch(Exception $e){
			echo $this->jsonOutPut(1,'保存缓存失败',$e->getMessage());
			exit;
		}

		$res=$this->objVisual->saveMapData($map_name,$mapkey,addslashes($map_data));
		if(!$res){
			echo $this->jsonOutPut(1,'fail',$res);
			exit;
		}

		echo $this->jsonOutPut(0,'success',$res);
		exit;
	}


	//记录外链访问记录
	function actionBehaviorLog(){

		$menu_id = $_REQUEST['menu_id'];
		$url = $_REQUEST['openurl'];
		$params = array();
		$params['table_id'] = (string)$url;
		$params['menu_id'] = (string)$menu_id;
		$res=$this->objoffline->addUserBehaviorToLog('',$menu_id,'/visual/index/menu_id/'.$menu_id,$params);
		$this->jsonOutPut($res);
	}


	function actionOperWhiteInterface(){
		$id='';
		$name=$_REQUEST['name'];
		$refers=$_REQUEST['refers'];
		$url=$_REQUEST['url'];
		if(isset($_REQUEST['id'])){
			$id=$_REQUEST['id'];
		}
		$parse_url=parse_url($url);

		if(!array_key_exists('path',$parse_url)){
			echo $this->jsonOutPut(1,'请填写正确格式接口');
			exit;
		}
		$ck_url=$this->objTool->selectWhiteInterface('',$url);
		if($ck_url){
			if($id!=''){
				foreach($ck_url as $cn){
					if($cn['id']!=$id){
						echo $this->jsonOutPut(1,'接口已经配置，请勿重复添加');
						exit;
					}
				}
			}else{
				echo $this->jsonOutPut(1,'接口已经配置，请勿重复添加');
				exit;
			}

		}
		$url=$parse_url['path'];
		if($id!=''){
			$res=$this->objTool->updateWhiteInterface($id,$name,$refers,$url);
		}else{
			$res=$this->objTool->saveWhiteInterface($name,$refers,$url);
		}
		if(!$res){
			echo $this->jsonOutPut(1,'fail',$res);
			exit;
		}
		echo $this->jsonOutPut(0,'success',$res);
		exit;



	}
	//白名单接口
	function actionWhiteInterface(){

		$id='';
		if(isset($_REQUEST['id']) and $_REQUEST['id']){
			$id=$_REQUEST['id'];
		}
		$interface_info=$this->objTool->selectWhiteInterface($id);
		$tplArr=array('data'=>$interface_info);
		if(isset($_REQUEST['retu_tpye'])){
			echo $this->$this->jsonOutPut(0,'success',$tplArr);
			exit;
		}

		$this->render('tooltpl/white_interface.tpl',$tplArr);
	}


	function actionOperOpenUrl(){
		$id='';
		$name=$_REQUEST['name'];
		$desc=$_REQUEST['desc'];
		$url=$_REQUEST['url'];
		if(isset($_REQUEST['id'])){
			$id=$_REQUEST['id'];
		}
		$params=array('url'=>$url);
		$res='';
		$ck_name=$this->objTool->selectOpenUrl('',$name);
		if($ck_name){
			if($id!=''){
				foreach($ck_name as $cn){
					if($cn['id']!=$id){
						echo $this->jsonOutPut(1,'名称已经被占用');
						exit;
					}
				}
			}else{
				echo $this->jsonOutPut(1,'名称已经被占用');
				exit;
			}

		}

		if($id!=''){
			$res=$this->objTool->updateOpenUrl($id,$name,$desc,$params);
		}else{

			$res=$this->objTool->saveOpenUrl($name,$desc,$params);
		}

		if(!$res){
			echo $this->jsonOutPut(1,'fail',$res);
			exit;
		}
		echo $this->jsonOutPut(0,'success',$res);
		exit;

	}
	//外链报表
	function actionOpenUrl(){
        if (!$this->objAuth->isSuper()) {
            $this->render('error/error.tpl', ['msg' => ['抱歉，您没有访问权限']]);
            exit;
        }

		$id='';
		if(isset($_REQUEST['id']) and $_REQUEST['id']){
			$id=$_REQUEST['id'];
		}
		$opeurl_info=$this->objTool->selectOpenUrl($id);
		$retu_openurl=array();
		foreach($opeurl_info as $openurl){
			$tmp=array();
//			id,cn_name,explain,creater,modify_user,create_time,params
			$tmp['id']=$openurl['id'];
			$tmp['name']=$openurl['cn_name'];
			$tmp['desc']=$openurl['explain'];
			$tmp['url']=json_decode($openurl['params'],true)['url'];
			$retu_openurl[]=$tmp;

		}
		$tplArr=array('data'=>$retu_openurl);
		if(isset($_REQUEST['retu_tpye'])){
			echo $this->$this->jsonOutPut(0,'success',$retu_openurl);
			exit;
		}

		$this->render('tooltpl/open_url.tpl',$tplArr);
	}
        
        function actionFileUp(){
            //$fileUp = new CUploadedFile($name, $tempName, $type, $size, $error);
            $res=$this->objAuth->checkReportPoint(1534);
            if($res || $this->objAuth->isAdmin() || $this->objAuth->isProducer() || $this->objAuth->isSuper()){
                $tplArr=array('data'=>'dfdf');
                $this->render('tooltpl/file.tpl',$tplArr);
            }else{
                echo '抱歉，您没有访问权限';
            }
        }
        function actionGetFileUp(){  
      
            $dir =Yii::app()->basePath."/runtime/file";
            if(!is_dir($dir)){
                mkdir($dir);
            }
            $name =  Yii::app()->basePath."/runtime/file/".date("ymdhis").'.csv';
            $fileUp = new CUploadedFile($_FILES['imexcel']['name'], $_FILES['imexcel']['tmp_name'], $_FILES['imexcel']['type'], $_FILES['imexcel']['size'], $_FILES['imexcel']['error']);
            $fileUp->saveAs($name);
            $result = $this->objTool->inputCsv($name);
            $data['row']= $result;
            $data['file']= $name;
            $this->jsonOutPut(0,'',$data);exit;
            
        }
        function actionCreateHiveData(){
            //删除文件第一行 
            $file = $_REQUEST['file'];
            $name = $_REQUEST['name'];
            $isAdd =(int)$_REQUEST['is_add'];
            
            $re = $this->objTool->checkTableExsits($name);
            if(!$isAdd && $re){
                echo '表名已经存在，请选择追加数据';
                exit;
            }
            if($isAdd){
                $this->objTool->loadData($file,$name);
            }else{
              $this->objTool->createTable($file,$name);
              $this->objTool->loadData($file,$name);
            }
           
            exit;
        }

    function actionRunTaskSingle()
    {
        $id  = $_REQUEST['id'];
        $key = $_REQUEST['key'];

        if (!is_string($id) || empty($id)) {
            echo 'id 参数校验失败';
            exit;
        }

        if (!is_string($key) || $key != 'bi_sec') {
            echo '再见，嘻嘻！';
            exit;
        }

        $totalID = explode(',', $_REQUEST['id']);

        foreach ($totalID as $currentID) {
            $currentID = trim($currentID);

            if (is_numeric($currentID)) {
                $shell = "export PATH=/usr/lib/jdk1.7.0/bin:/usr/lib/jdk1.7.0/jre/lib:/di_software/hadoop-2.5.2/bin:/di_software/hive/bin:/di_software/sqoop-1.O4.6/bin:/usr/local/bin:/usr/bin:/bin:/usr/local/games:/usr/games:/usr/sbin; export JAVA_HOME=/usr/lib/jdk1.7.0; export HADOOP_VERSION=2.5.2; nohup python /home/apple/bi.analysis/fakecube/src/mms/bin/run_task_single.py -l {$currentID} >> /tmp/{$currentID}.log 2>&1 &";
                shell_exec($shell);
            }
        }

        echo '执行完成';
    }

}