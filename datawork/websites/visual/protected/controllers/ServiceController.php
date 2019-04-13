<?php
   class  ServiceController extends  Controller{

   	function __construct(){
   		 $this->menu=new MenuManager();
   		 $this->report = new ReportManager();
   	}
    public  function actionGetMenu(){
    	 $menuList =$this->menu->selectMenu();
    	 $data = array();
    	 foreach ($menuList as $key => $value) {
    	 	if(!empty($value['all'])){
    	 	 	foreach ($value['all'] as $item => $iVal) {
    	 	 		$one = array();
				$one['menu_id']=$value['id'];
    	 	 		$one['first_menu'] = $value['first_menu'];
    	 	 		$one['second_menu'] = $value['second_menu'];
    	 	 		$one['buiness'] ='data平台';
    	 	 		if($iVal['type'] ==1){

	 	 				$nameArr = $this->report->getReoport($iVal['id']);
	 	 				$one['funname'] = $iVal['id']."_".$nameArr['cn_name'];
	 	 				$one['url'] = '/visual/index/'.$iVal['id'];
    	 	 		}else{
    	 	 			if(strrpos($iVal['url'],'works.meiliworks.com/biData/tplShow') !==false){
    	 	 				$urlArr = explode("/", $iVal['url']);
    	 	 				$onlyId = explode("?", end($urlArr) );
    	 	 				$workId = $onlyId[0];
    	 	 				//获取work报表名称
    	 	 				$worksTable = $this->report->getWorkReport($workId);
    	 	 				$one['funname'] = $worksTable['name'];
    	 	 				$one['url'] = $worksTable['url'];
    	 	 			} 
    	 	 		}
    	 	 		$data[] = $one; 		
    	 	 	}
    	 	}
    	 }
    	 if(empty($data)){
    	 	echo json_encode(array('status'=>0,'msg'=>'数据为空'));
    	 }else{
    	 	//$this->jsonOutPut(,'success',$data);
            echo json_encode(array('status'=>1,'msg'=>'success','data'=>$data));
    	 }
    }
   }
