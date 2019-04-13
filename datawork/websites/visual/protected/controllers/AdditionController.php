<?php
class AdditionController extends Controller{
	function  __construct(){
		$this->objFackcube=new FackcubeManager();
		$this->objAuth=new AuthManager();
		$this->objProject=new ProjectManager();

	}
	function  actionGetFakecube(){
		$action=$_REQUEST['action'];
		$parament=$_REQUEST;

		if(empty($action)||empty($parament)){
			$this->jsonOutPut(1);
			exit();
		}
		unset($parament['action']);
		echo json_encode($this->objFackcube->get_fakecube($action,$parament));
	}
	function  actionShowTimeline(){
		$data['event_id'] = $_REQUEST['event_id']? $_REQUEST['event_id']:1;
		$data['project']= $this->addition->getTimeLineList();
        //面包屑效果
        $indexStr[] = array('href'=>"/visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"/visual/toolguider",'content'=>'常用工具');
        $indexStr[] = array('href'=>"#",'content'=>'项目时间线');
        $data['guider'] = $indexStr;

		$this->render('timeline/index.tpl',$data);
	}
	function  actionEditorTimeline(){
		
		$data['event_id'] = $_REQUEST['event_id']?$_REQUEST['event_id']:0;
		$data['project']=$this->addition->getTimeLineList($data['event_id']);
		if($data['event_id']){
			$data['eventData'] = $this->getTimeLineData($data['event_id'],'underline');
		}else{
			$data['eventData'] = "";
		}
		$this->render('timeline/timelineoprate.tpl',$data);
	}
	function getTimeLineData($event_id,$timetype){
		$objres=$this->addition->getTimeLineList($event_id);
		if(!empty($objres) && !empty($objres[0]['event_data'])){
			$date=json_decode($objres[0]['event_data'],true);
			if($timetype=='underline' && is_array($date)){
				foreach ($date  as $k=>$v) {
					$date[$k]['startDate']=str_replace(',','-',$date[$k]['startDate']);
					$date[$k]['endDate']=str_replace(',','-',$date[$k]['endDate']);
				}
			}
		}
		$date = $this->common->arrSort($date,'startDate','asc');
		$res['timeline']=array('headline'=>$event_id,'type'=>'default','text'=>'','date'=>$date);

		return  json_encode($res);
	}
	function actionGetTimeLineJson(){
		$event_id=$_REQUEST['event_id'];
		$timetype=$_REQUEST['timetype'];
		if(empty($event_id)){
			$this->jsonOutPut(1);
			exit();
		}
		$date='';
		echo $this->getTimeLineData($event_id,$timetype);

	}
	function actionSaveTimeline(){

		$eventData = $_REQUEST['eventInfo'];
		$eventData  = json_decode($eventData,true);
		$arrDate  = $eventData['data'];
		unset($eventData['data']);
		if(empty($arrDate)){
			$arrDate=array();
		}
		$resDate=array();
		foreach($arrDate as $k=>$v){
			$v['asset']=array();
			$v['startDate']=str_replace('-',',',$v['startDate']);
			$v['endDate']=str_replace('-',',',$v['endDate']);
			$resDate[$k]=$v;
		}
		$eventData['event_data'] = json_encode($resDate);
		$res=$this->addition->saveTimeline($eventData);
		if($res){
			$this->jsonOutput(0,'成功',$res);
		}else{
			$this->jsonOutput(1,'数据没有变化');
		}
	}
	/*
		验证名称
	*/
	function actionCheckName(){
		$event_name = $_REQUEST['event_name'];
		$event_id = $_REQUEST['event_id'];
		$result = $this->addition->checkName($event_name,$event_id);
		if( $event_id ==0 ){
			if( empty($result) ){
				$this->jsonOutPut(0);
			}else{
				$this->jsonOutPut(1,'名称重复');
			}
		}
		
	}

}

