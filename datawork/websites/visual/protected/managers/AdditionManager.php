<?php
  class  AdditionManager extends Manager{
  	 function __construct(){
  	 	$this->comquery = new MysqlCModel();
  	 }
  	 /*获取时间线信息*/
  	 public $timeLine = 't_visual_timeline';
  	 public function getTimeLineList($event_id = false){
  	 	$sql =" select  *  from ".$this->timeLine." ";
  	 	if($event_id){
  	 		$whereStr = " where   event_id =".$event_id;
  	 	}else{
  	 		$whereStr ="";
  	 	}
  	 	$sql = $sql.$whereStr;
  	 	$result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
  	 	return $result;
  	 }

  	 function  saveTimeline($eventData){
        $params['table']  = $this->timeLine;
        $params['data']   = $eventData;
        $event_id = $eventData['event_id'];
        unset($eventData['event_id']);
        if($event_id >0){
        	$params['where'] = array('event_id'=>$event_id);
        	$params['data']  = $eventData;
        	$re = $this->comquery->runUpate($params);
        }else{
        	$params['data']  = $eventData;
        	$re = $this->comquery->runInsert($params);
        }
        return $re;
     }

     /*
      名称验证
     */
     function checkName($event_name,$event_id =0){
      if($event_id >0){
        $sql =" select *  from  ".$this->timeLine." where  event_name = '".$event_name."'  and  event_id = ".$event_id;
      }else{
        $sql =" select *  from  ".$this->timeLine." where  event_name = '".$event_name."' ";
      }
     	$result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
     	return $result;
     }
  }