<?php
/**
 * Created by PhpStorm.
 * Date: 2015/10/30
 * Time: 上午10:00
 * email:manli@meilishuo.com
 */

class AppHomefocusController extends Controller
{
	function  __construct(){
		$this->curl = Yii::app()->curl;
	}
	//紧急上线申请 status＝0 replay_status=0; 紧急上线申请全部通过时更改 status＝0 replay_status=1;
	public $flowname = array("1"=>"source","2"=>"fashion","3"=>"banner","4"=>"online","5"=>"offline");
	public $statusName = array("1"=>"资源位申请","2"=>"时尚度申请","3"=>"banner图申请","4"=>"上线申请","5"=>"已上线","6"=>"上线并打通微信钱包","-1"=>"已下线","0"=>"紧急申请上线");
	public $replyName = array("-1"=>"已撤消","0"=>"待审核","1"=>"已通过","2"=>"审核不通过");

	public $category = array("女装","女鞋","女包","男装","男包","男鞋","男配","童装","童鞋","童包","童配","美妆","配饰","食品",
		"家居","数码小家电","优惠券","其他");
	//流程步骤邮件抄送人
	public $Ccsendmail = array("1"=>"","2"=>"xiwang@meilishuo.com;randu@meilishuo.com","3"=>"xiwang@meilishuo.com;randu@meilishuo.com;xinmiwang@meilishuo.com","4"=>"xiwang@meilishuo.com;randu@meilishuo.com");

	public function actionTest(){
		echo 'test';
	}

	public function actionChangeEntrust(){
		$content = $_REQUEST;
		$result=$this->AppHomefocus->updateEntrust($content);
	if($result==1){
		$this->jsonOutPut(0,'变更成功');
	}else if($result==0){
		$this->jsonOutPut(0,'无变化');
	}else{
		$this->jsonOutPut(1,'变更失败');
	}
	}

	public function actionIndex(){
		//$data=array("begin"=>"1");
		$data = $_REQUEST;
		if(isset($data['tag'])){
			$data[$data['tag']] = "";
		} else {
			$data['begin']="";
		}
		$result = $this->AppHomefocus->getSearchData($data,1,50);
		$datalist = $this->dealData($result['datalist']);
		$username = Yii::app()->user->username;
		$name = Yii::app()->user->name;

		//获取今天 明天pc、mob端的总帧数
		$locationTotal = $this->getTotallocation();
		//获取我的审核 、丹阳的汇总列表 按钮是否显示
		$roleshowbtn = $this->roleBtn();

		//是否有我的委托
		$entrust= $this->AppHomefocus->checkEntrust();

		$dataArr = array("result"=>$datalist,"total"=>$result['total'],"pages"=>$result['pages'],"name"=>$name,"username"=>$username,
			"locationTotal"=>$locationTotal,"roleshowbtn"=>$roleshowbtn,"tag"=>$data['tag'],"myentrust"=>$entrust);
		$dataArr['reply_arr']=$this->replyName;
		//print_r('<pre>');print_r($dataArr); exit;
		$this->render('homefocus/index.tpl',$dataArr);
	}

	public function actionEntrust(){
		$name=Yii::app()->user->username;
		$result = $this->AppHomefocus->entrust($name);
		if(!$result || $result['entrust']==''){
			$result['entrust']='暂无';
		}
		$this->render('homefocus/entrust.tpl',array("result"=>$result,"name"=>$name));
	}

	public function actionEntrustSave(){
		$content = $_REQUEST;
		$content['user']=Yii::app()->user->username;
		$result=$this->AppHomefocus->entrustSave($content);
		if($result==1){
			$this->jsonOutPut(0,'变更成功');
		}else if($result==0){
			if($content['entrust']!=''){
				//var_dump($content);die();
				$this->AppHomefocus->entrustInsert($content);
				$this->jsonOutPut(0,'添加委托成功');
			}else{
			$this->jsonOutPut(1,'无变化');}
		}else{
			$this->jsonOutPut(1,'变更失败');
		}
	}


	public function actionMyEntrust(){
		$result = $this->AppHomefocus->myEntrust();
		//我的委托,权限
		if(sizeof($result)==0){
			$msg = "您没有委托权限";
			$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
			$this->errordata($errorArr);
			return;
		}
		$this->render('homefocus/entrustlist.tpl',array("result"=>$result));
	}

	// ajax 交互获取列表信息
	public function actionGetData(){
		$data = $_REQUEST;
		$pageNum = (isset($data['page'])&&$data['page']>0)?$data['page']:1;
		$count = (isset($data['pagecount'])&&$data['pagecount']>0)?$data['pagecount']:50;
		if(isset($data['tag'])){
			$data[$data['tag']] = "";
		}
		//$count = 5;
		$getresult = $this->AppHomefocus->getSearchData($data,$pageNum,$count);
		$getresult['datalist'] = $this->dealData($getresult['datalist']);
		$getresult['reply_arr']=$this->replyName;
		$this->jsonOutPut(0,'sucess',$getresult);
	}

	//获取
	public function actionGetdatalist(){
		$data=$_REQUEST;
		$senddata = array();
		$senddata[$data['tag']] = "";
		$senddata['username'] = Yii::app()->user->username;
		$result = $this->AppHomefocus->getSearchData($senddata,1,50);
		$datalist = $this->dealData($result['datalist']);
		$username = Yii::app()->user->username;
		$name = Yii::app()->user->name;

		//获取今天 明天pc、mob端的总帧数
		$locationTotal = $this->getTotallocation();
		//获取我的审核 、丹阳的汇总列表 按钮是否显示
		$roleshowbtn = $this->roleBtn();
		$dataArr = array("result"=>$datalist,"total"=>$result['total'],"pages"=>$result['pages'],"name"=>$name,"username"=>$username,
			"locationTotal"=>$locationTotal,"roleshowbtn"=>$roleshowbtn,"tag"=>$data['tag']);
		$dataArr['reply_arr']=$this->replyName;
		$this->render('homefocus/index.tpl',$dataArr);
	}
	//获取我的申请－－－暂时没用
	/*public function actionMyapply(){
		$data=$_REQUEST;
		$senddata = array();
		$senddata['username'] = Yii::app()->user->username;
		$senddata['myapply'] = '';
		$result = $this->AppHomefocus->getSearchData($senddata,1,50);
		$datalist = $this->dealData($result['datalist']);
		$this->render('homefocus/applylist.tpl',array("result"=>$datalist,"total"=>$result['total'],"pages"=>$result['pages']));
	}*/

	//获取我的审核列表
	public function actionMyreply(){
		$data=$_REQUEST;
		$pageNum = (isset($data['page'])&&$data['page']>0)?$data['page']:1;
		$count = (isset($data['pagecount'])&&$data['pagecount']>0)?$data['pagecount']:50;
		//我的审核
		$data['myreply'] = '';
		$data['username'] = Yii::app()->user->username;
		$result = $this->AppHomefocus->getSearchData($data,$pageNum,$count);
		$datalist = $this->dealData($result['datalist']);
		//print_r('<pre>');print_r($datalist);exit;
		$this->render('homefocus/replylist.tpl',array("result"=>$datalist,"total"=>$result['total'],"pages"=>$result['pages']));
	}

	//获取我的审核 交互数据
	public function actionGetMyreply(){
		$data=$_REQUEST;
		$pageNum = (isset($data['page'])&&$data['page']>0)?$data['page']:1;
		$count = (isset($data['pagecount'])&&$data['pagecount']>0)?$data['pagecount']:50;
		//$senddata['username'] = Yii::app()->user->username;
		$data['myreply'] = '';
		$data['username'] = Yii::app()->user->username;

		$result = $this->AppHomefocus->getSearchData($data,$pageNum,$count);
		$datalist = $this->dealData($result['datalist']);
		$this->jsonOutPut(0,'sucess',array("datalist"=>$datalist,"total"=>$result['total'],"pages"=>$result['pages']));
	}
	//丹阳的资源申请通过的资源列表权限 status>1 or (status =1 and  reply_status =1 ) and starttime>now
	public function actionApplylist(){
		//获取审核人信息
		$status = 5;
		$processinfo = $this->AppHomefocus->getProcessinfo($status);
		$processname = $processinfo[0]['auditor'];
		$username = Yii::app()->user->username;
		//print_r($username);print_r('<pre>');print_r($processinfo);
		if(strpos($processname,$username) === false){
			$this->jsonOutPut(1,"您没有权限获取资源列表");
			return;
		}

		$where = array('applylist'=>"");
		$result = $this->AppHomefocus->getSearchData($where,1,50);
		$datalist = $this->dealData($result['datalist']);
		//print_r('<pre>');print_r($datalist);exit;
		//$this->render('homefocus/applylist.tpl',array("result"=>$datalist,"total"=>$result['total'],"pages"=>$result['pages']));


	}

	//申请页面
	public function actionApply(){
		$data = $_REQUEST;
		$result = array();
		$result['flowname'] = json_encode( $this->flowname);
		$datas=array();
		if(isset($data['id']) && $data['id'] > 0){
			$wherearr = array("id"=>$data['id']);
			$datas = $this->AppHomefocus->getList($wherearr);
			$datas = $this->dealData($datas);

			if(!empty($datas) && $datas[0]){
				$datas = $datas[0];

				//权限判断  测试

				if($data['flow'] > $datas['status'] && $datas['reply_status'] != '1'){
					//$this->jsonOutPut(1,'工单流程不对，请返回首页');
					$errorArr = array("status"=>1,"data"=>'','msg'=>"您的工单流程环节不对");
					$this->errordata($errorArr);
					return;
				}

				if($datas['role'] !="creater" ){
					//$this->jsonOutPut(1,'工单流程不对，请返回首页');
					$msg = ($datas['reply_status'] == '1')?"您的申请审核已通过":"您的申请已经在审核中";
					$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
					$this->errordata($errorArr);
					return;
				} else {
					//只要还没有人来审核都是可以修改的
					$auditinfo = json_decode($datas['auditinfo'],true);
					//print_r('<pre>');print_r($datas);exit;
					if(( ($datas['reply_status'] == '0' && !empty($auditinfo['do'])) ||$datas['reply_status'] == '1') && $data['flow'] == $datas['status']){
						//$this->jsonOutPut(1,'您的申请审核已通过，请返回首页');
						$msg = ($datas['reply_status'] == '1')?"您的申请审核已通过":"您的工单还处于审核中状态";
						$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
						$this->errordata($errorArr);
						return;
					}
				}

			} else {
				if($data['flow'] != '1'){
					$msg = "id=".$datas['id']."的数据未查询到";
					$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
					$this->errordata($errorArr);
					//$this->jsonOutPut(1,'此id的数据未查询到，状态流程不对，请返回首页查看');
					return;
				}
			}

		}

		$result['datas']=json_encode($datas);
		$result['category'] = json_encode($this->category);
		$result['statusName'] = json_encode($this->statusName);
		$this->render('homefocus/apply.tpl',$result);
	}
	//提交申请
	public function actionAdd(){
		$data = $_REQUEST['data'];
		$datas = json_decode($data,true);
		$datas['status'] = $_REQUEST['status'];
		$datas['creater'] = Yii::app()->user->name;
		$datas['creater_name'] = Yii::app()->user->username;
		$datas['status'] = isset($datas['status'])?$datas['status']:1;


		//添加获取审核信息
		$auditArr = $this->getAuditinfo($datas['status'],array(),0);
		$datas['auditinfo'] = json_encode($auditArr['auditinfo']);
		$datas['reply_status'] = $auditArr['reply_status'];
		$result = $this->AppHomefocus->addSource($datas);
		$datas['id'] = $result;

		if($result){
			//发邮件通知
			$title = $this->statusName[$datas['status']];
			$todo = $auditArr['auditinfo']['todo'];
			$host = Yii::app()->request->hostInfo;
			 //紧急上线申请待审核
			if($datas['status'] == 0){
				$html = "<p>当前流程：".$title."</p><p>处理位置：<a href='".$host."/AppHomefocus/reply?flow=".$datas['status']."&id=".$result."'>紧急上线申请待审核</a></p>";
			} else {
				$html = "<p>当前流程：".$title."</p><p>处理位置：<a href='".$host."/AppHomefocus/reply?flow=".$datas['status']."&id=".$result."'>资源位申请待审核</a></p>";
			}
			$this->AppHomefocus->sendMail($datas,$todo,$html,$title);

			$this->jsonOutPut(0,'sucess');
		} else {
			$this->jsonOutPut(1,'插入数据失败');
		}

	}
	//后续的流程编辑更新
	public function actionUpdate(){
		$data = $_REQUEST['data'];
		$datas = json_decode($data,true);
		$datas['status'] = $_REQUEST['status'];
		$datas['reply_status'] = $_REQUEST['reply_status']; //小状态 申请
		$id = $_REQUEST['id'];
		$status = $datas['status'];

		if(!$id || $id <=0){
			$this->jsonOutPut(1,'缺少参数id');
			return;
		}

		$wherearr = array("id"=>$id);
		$result = $this->AppHomefocus->getList($wherearr);
		$result = $result[0];
		$myresult = $result;

		$oldauditinfo = json_decode($result['auditinfo'],true);

		$auditarr = $this->getAuditinfo($datas['status'],$oldauditinfo,0);

		//print_r('<pre>');print_r($auditarr);exit;
		//发送给业务方leader
		$leader = $auditarr['leader'];
		$auditarr['auditinfo']['todo'] = ($leader != '')?$leader.','.$auditarr['auditinfo']['todo']:$auditarr['auditinfo']['todo'];

		//如果是最后一个人审核的 要判断是否通过审核
		$datas['auditinfo'] = json_encode($auditarr['auditinfo']);
		$datas['reply_status'] = $auditarr['reply_status'];
		$result = $this->AppHomefocus->update($id,$datas);
//var_dump($datas['getdata']);
		if($result){
			//发邮件通知
			$todo = $auditarr['auditinfo']['todo'];
			if($status == 4 ){
				$todoarr = explode(',',$auditarr['auditinfo']['todo']) ;
				$todo = $todoarr[0];
			}
			$host = Yii::app()->request->hostInfo;
			$title = $this->statusName[$datas['status']].$this->replyName[$datas['reply_status']];
			$html = "<p>当前流程：".$this->statusName[$datas['status']]."</p><p>处理位置：<a href='".$host."/AppHomefocus/reply?flow=".$datas['status']."&id=".$id."'>".$title."</a></p>";

			//$leader 为空时 不抄送
			//$cc = isset($this->Ccsendmail[$status])?$this->Ccsendmail[$status]:"";
			$send_mail_data=$myresult;
			$send_mail_data['status']=$datas['status'];
			$this->AppHomefocus->sendMail($send_mail_data,$todo,$html,$title,$cc);

			$this->jsonOutPut(0,'sucess');
			return;

		} else {
			$this->jsonOutPut(1,'插入数据失败');
			return;
		}

	}
	//审核页面
	public function actionReply(){

		$data = $_REQUEST;
		$id = isset($data['id'])?$data['id']:0;

		$result = array();
		$result['flowname'] = json_encode( $this->flowname);

		$wherearr = array("id"=>$id);
		$datas = $this->AppHomefocus->getList($wherearr);
		$datas = $this->dealData($datas);

		if(empty($datas)){
			$msg = "id=".$data['id']."未获取到相应数据";
			$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
			$this->errordata($errorArr);
			return;
		}

		$datas = $datas[0];

		if($data['flow']!='-1' && $data['flow'] > $datas['status']){
			$msg = "id=".$datas['id']."的工单审核还未到此流程环节";
			$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
			$this->errordata($errorArr);
			//$this->jsonOutPut(1,'工单流程不对，请返回首页');
			return;
		}
		//权限判断 测试

		if($datas['role'] !="reply" ){
			//$this->jsonOutPut(1,'您无相应的审核权限，请返回首页');
			$msg = "id=".$datas['id']."的工单无法查看";
			$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
			$this->errordata($errorArr);
			return;
		} else {
			if($datas['reply_status'] > 0 || $data['flow'] != $datas['status']){
				$msg = "id=".$datas['id']."的工单已审核过";
				$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
				$this->erro3rdata($errorArr);
				//$this->jsonOutPut(1,'您访问审核流程不对，请返回首页查看');
				return;
			}
		}

		$result['datas']=json_encode($datas);
		$result['statusName'] = json_encode($this->statusName);
		$this->render('homefocus/reply.tpl',$result);
	}

	//审核通过 不通过的方法
    public function actionReplyUpdate(){
		$data = $_REQUEST;
		$datamsg = $this->dealReplyUpdate($data);
		//干掉speed快捷回复
		$user=Yii::app()->user->username;
		$quick=$this->AppHomefocus->find_reply_id($data['id'],$user,$data['status']);
		$this->AppHomefocus->kick_quick_reply($quick['id']);
		//发送speed
		$msg[]='您通过其他方式审核了一个项目';
		$msg[]='回复码0'.$quick['id_for_user'].'已经失效';
		$this->AppHomefocus->sendspeed($user,$msg);
		$this->jsonOutPut($datamsg['status'],$datamsg['msg']);
		return;
	}

	//在线修复更新
	public function actionHotUpdate(){
		$data = $_REQUEST;
		$data['reply_info']='线上驳回';
		$host = Yii::app()->request->hostInfo;
		$result=$this->AppHomefocus->hotUpdate($data);
		$wherearr = array("id"=>$data['id'],"status"=>$data['status']);
		$getresult = $this->AppHomefocus->getList($wherearr);
		$myresult = $getresult[0];
		$creater = $getresult[0]['creater_name'];
		//成功后发送个驳回的email
		$title = $this->statusName[$data['status']];
		$html = "<p>审核状态：".$title."被驳回</p><p>查看位置：<a href='".$host."/AppHomefocus/detail?flow=".$data['status']."&id=".$data['id']."'>".$title."</a></p>";
		$this->AppHomefocus->sendMail($myresult,$creater,$html,$title);

		if($result==1){
			$this->jsonOutPut(0,"更改成功");
		}else{
			$this->jsonOutPut(1,"参数为空");
		}
	}

	//审核的数据处理方法 供给批量审核使用； $data接收的数据 $id
	public function dealReplyUpdate($data){
		$datamsg = array("status"=>"0","msg"=>"","data"=>array());
		if(!isset($data['id'])){
			$datamsg['status']= 1;
			$datamsg['msg']= "缺少id参数";
			return $datamsg;
		}
		if(!isset($data['status'])){
			$datamsg['status']= 1;
			$datamsg['msg']= "缺少status参数";
			return $datamsg;
		}

		$id = $data['id'];
		$reply_info = $data['reply_info'];
		$status = $data['status'];
		$reply_status = $data['reply_status'];

		$wherearr = array("id"=>$id,"status"=>$status);
		$getresult = $this->AppHomefocus->getList($wherearr);

		if(empty($getresult)){
			$datamsg['status']= 1;
			$datamsg['msg']= "您不能审核,数据库中没有相应的数据信息";
			return $datamsg;
		}

		$result = $getresult[0];
		$myresult = $getresult[0];

		$oldauditinfo = json_decode($result['auditinfo'],true);
		$creater = $result['creater_name'];
		//获取审核信息
		$auditarr = $this->getAuditinfo($status,$oldauditinfo,1,array("reply_info"=>$reply_info,"reply_status"=>$reply_status));
		//如果是最后一个人审核的 要判断是否通过审核

		$params['auditinfo'] = json_encode($auditarr['auditinfo']);
		$params['reply_status'] = $auditarr['reply_status'];
		$result = $this->AppHomefocus->Update($id,$params);

		if(!$result){
			$datamsg['status']= 1;
			$datamsg['msg']= "数据更新失败";
			return $datamsg;
		}

		//发送邮件 根据$auditarr['auditinfo']  的todo do 以及 reply_status 判断是否通过审核。审核完毕后均要发邮件通知
		//send email

		//全部审核后发邮件通知
		$sendmails = $creater;
		$host = Yii::app()->request->hostInfo;

		if($auditarr['reply_status']>0){
			$title = $this->statusName[$status].$this->replyName[$auditarr['reply_status']];
			if($auditarr['reply_status'] == 1){
				//进入下一步的申请 紧急上线status＝0 而且只有一步
				if(intval($status)<4 && intval($status)>0 ){
					$newstatus = intval($status)+1;
					$newtitle = $this->statusName[$newstatus];
					$html = "<p>审核状态：".$this->replyName[$auditarr['reply_status']]." (".$this->statusName[$status].")</p><p>处理位置：<a href='".$host."/AppHomefocus/apply?flow=".$newstatus."&id=".$id."'>".$newtitle."</a></p>";
				} else {
					//上线申请通过
					if(intval($status) == 4 || intval($status) == 0){
						$html = "<p>审核状态：".$this->replyName[$auditarr['reply_status']]." (".$this->statusName[$status].")</p><p>查看位置：<a href='".$host."/AppHomefocus/detail?flow=".$status."&id=".$id."'>查看详细</a></p>";
					}
				}
			} else {
				//不通过编辑
				$html = "<p>审核状态：".$this->replyName[$auditarr['reply_status']]." (".$this->statusName[$status].")</p><p>查看位置：<a href='".$host."/AppHomefocus/detail?flow=".$status."&id=".$id."'>查看详细</a>后，再进行修改编辑</p>";
			}

			$this->AppHomefocus->sendMail($myresult,$sendmails,$html,$title,'',true);


		} else {
			//上线申请 串行审核
			if(intval($status) == 4 && $auditarr['reply_status']== 0){
				$title = '上线申请待审核';

				if($auditarr['auditinfo']['todo'] !=''){

					$todoarr = explode(',',$auditarr['auditinfo']['todo']) ;
					$sendmails = $todoarr[0];
					//$sendmails = $auditarr['auditinfo']['todo'];
					//下一个人去审核
					$html = "<p>审核状态：".$title."</p><p>处理位置：<a href='".$host."/AppHomefocus/reply?flow=".$status."&id=".$id."'>上线申请审核</a></p>";
					$this->AppHomefocus->sendMail($myresult,$sendmails,$html,$title,'',true);

				}

			}

			//如果某个人审核不通过 发邮件
			if( $reply_status == 2 ){
				$title = $this->statusName[$status];
				$html = "<p>审核状态：".$title."被驳回</p><p>查看位置：<a href='".$host."/AppHomefocus/detail?flow=".$status."&id=".$id."'>".$title."</a></p>";
				$this->AppHomefocus->sendMail($myresult,$creater,$html,$title,'',true);

			}

		}

		$datamsg['status']= 0;
		$datamsg['msg']= "数据更新成功";
		return $datamsg;

	}


	//批量审核通过的方法
	public function actionBatchreply(){
		$request = $_REQUEST;
		$data = json_decode($request['datas'],true);
		//print_r('<pre>');print_r($data);exit;

		if(empty($data)){
			$this->jsonOutPut(1,"参数为空");
			return;
		}

		$count = 0;
		$newdatamsg = array();
		foreach($data as $k=>$val){
			$datamsg = $this->dealReplyUpdate($val);
			if($datamsg['status'] == 1){
				array_push($newdatamsg,"id=".$val['id'].$datamsg['msg']);
				$count++;
			}
		}

		if($count==0 ){
			$this->jsonOutPut(0,'数据更新成功');

		} else {
			$this->jsonOutPut(1,implode(",",$newdatamsg));
		}

	}

	//banner 上传
	public function actionUpload(){
		$data = $_REQUEST;
		$info = getimagesize($_FILES['file']["tmp_name"]);
		//Array([0] => 1239 [1] => 1242[2] => 2 [3] => width="1239" height="1242" [bits] => 8 [channels] => 3 [mime] => image/jpeg)
		$width = $info[0];
		$height = $info[1];

		$locationkeyArr = array("web_welcome_top_banner_carousel"=>array("width"=>960,"height"=>420),
		"home_Top_Banner_new"=>array("width"=>640,"height"=>240),
		"home_Top_Banner_6_6"=>array("width"=>750,"height"=>360),
		"home_Top_Banner"=>array("width"=>640,"height"=>340),
		//splash
		"pic_for_iphone"=>array("width"=>640,"height"=>960),
		"pic_for_iphone5"=>array("width"=>640,"height"=>1136),
		"pic_for_android"=>array("width"=>720,"height"=>1280),
		);
		//规定的尺寸
		$realsize = $locationkeyArr[$_REQUEST['pickey']];

		if($width != $realsize['width'] || $height !=$realsize['height']){
			$errmsg = "您上传的图片大小为".$width."*".$height.",不符合标准，必须上传".$realsize['width']."*".$realsize['height']."的图片";
			$this->jsonOutPut("1",$errmsg);
			return;
		}
		$picinfo = $this->getUploadpic($_FILES);

		if($picinfo['status'] != 0){
			$this->jsonOutPut("1",$picinfo['msg']);
			return;
		}
		//$picinfo = $picinfo['n_pic_file'];
		echo json_encode($picinfo) ;
		return;

	}

	//详细
	public function actionDetail(){
		$data = $_REQUEST;
		$status = $data['flow'];
		$id = $data['id'];
		$wherearr = array("status"=>$status,"id"=>$id);
		$result = $this->AppHomefocus->getlist($wherearr);
		$result = $this->dealData($result,1);
		//print_r('<pre>');print_r($result);exit;
		$this->render('homefocus/detail.tpl',array("result"=>json_encode($result)));
	}

	//撤消 status = 3 replay -1
	public function actionCancel(){
		$data = $_REQUEST;
		$status = $data['status'];
		$id = $data['id'];
		$wherearr = array("id"=>$id);
		$getresult = $this->AppHomefocus->getList($wherearr);
		$getresult = $getresult[0];
		$oldauditinfo = json_decode($getresult['auditinfo'],true);

		$auditarr = $this->getAuditinfo($data['status'],$oldauditinfo,-1);

		$wherearr = array("status"=>$status,"reply_status"=> -1,"auditinfo"=>json_encode($auditarr['auditinfo']));
		$result = $this->AppHomefocus->update($id,$wherearr);

		if($result){
			//发送邮件 根据$auditarr['auditinfo']  的todo do
			//send email
			//发邮件通知
			$todo = $oldauditinfo['todo'];
			if($todo != ''){

			}
			$stausname = $this->statusName[$status];
			$title = $stausname."已撤消";
			$html = "<p>审核状态：".$stausname."已撤消</p><p>您不需要审核 id=".$id."的工单</p><p></p>";
			$this->AppHomefocus->sendMail($getresult,$todo,$html,$title);

			$this->jsonOutPut(0,'数据撤消成功');

		} else {
			$this->jsonOutPut(1,'数据撤消失败');
		}
	}

	//上线同步代码
	public function actionLinecode(){
		$status = $_REQUEST['status'];
		$id = $_REQUEST['id'];
		$today = date('Y-m-d H:i:s',time());

		if(!$id || $id <0){
			$this->jsonOutPut(1,'缺少id参数');
			return;
		}

		$wherearr = array("id"=>$id);
		$result = $this->AppHomefocus->getList($wherearr);
		if(empty($result)){
			$this->jsonOutPut(1,'无查询到相应数据');
			return;
		}

		$result = $result[0];
		if($result['status'] == 4 && $result['reply_status'] == 1){
			//1.同步到正式线上库 2.本地库更新状态status为5
			//处理与线上库同步的数据字段
			$str=$result['location'];
			$reg1= "/(pc|mob)/i";
			$reg2= "/(splash)/i";

			// pc/mob 数据同步线上
			$relationid_arr = array();
			$replyinfo = array();

			$count = 0;
			if(preg_match($reg1, $str)){
				$getresult_pcmob= $this->onlinePcmob($result);
				if($getresult_pcmob['count'] >0){
					$this->jsonOutPut(1,'pc/mob端'.$getresult_pcmob['errmsg']);
					return;
				}
				$location_idarr =$getresult_pcmob['location_idarr'];
				$relationid_arr =$getresult_pcmob['relationid_arr'];
				$replyinfo[]="上线资源位的id为".implode(",",$location_idarr);
				$count = $getresult_pcmob['count'];
			}

			// splash数据同步
			if(preg_match($reg2, $str)){
				$getresult_splash= $this->onlineSplash($result);
				if($getresult_splash['count'] >0){
					$this->jsonOutPut('1','splash数据'.$getresult_splash['errmsg']);
					return;
				}

				$replyinfo[] = "splash 更新数据id为".$getresult_splash['content_id'];
				$relationid_arr[] = $getresult_splash['content_id'];
				$count = $getresult_splash['count'];
			}
			//更新本地数据库
			if($count == 0){
				//更新本地库 status＝5 为上线状态 replay_status =1;
				$username = Yii::app()->user->username;
				$name = Yii::app()->user->name;
				$history = array("reply_info"=>implode(",",$replyinfo),"reply_status"=>0,"name"=>$name,"username"=>$username,"time"=>$today,"flag"=>"已上线");
				$auditarr = array("todo"=>"","do"=>array(),"history"=>array("5"=>array(0=>$history)));
				$wherearr = array("status"=>5,"reply_status"=> 1,"relation_id"=>implode(',',$relationid_arr),"auditinfo"=>json_encode($auditarr));
				$this->AppHomefocus->update($id,$wherearr);

				$this->jsonOutPut(0,'上线更新成功');
				return;
			} else {
				$this->jsonOutPut(1,'上线更新失败');
				return;
			}

		} else {
			$this->jsonOutPut(1,'查询到该数据状态不能做上线操作，请返回首页查看详细');
			return;
		}
	}

	//下线同步代码 更新状态为－1
	public function actionOfflinecode(){
		$status = $_REQUEST['status'];
		$id = $_REQUEST['id'];
		$id = (int)$id;
		$today = date('Y-m-d H:i:s',time());
		$username = Yii::app()->user->username;
		$name = Yii::app()->user->name;

		$wherearr = array("id"=>$id);
		$datas = $this->AppHomefocus->getList($wherearr);
		if(!empty($datas) && $datas[0]){
			$relation_id = $datas[0]['relation_id'];

			if($relation_id != 0){
				//$relation_idarr = explode(',',$relation_id);

				/*$location_ids = array();
				foreach($relation_idarr as $key=>$val){
					$resultid = $this->AppHomefocus->updateOffline($val);
					$location_ids[] = $resultid;
				}*/

				$datalist = $datas[0];
				$str=$datalist['location'];
				$reg1= "/(pc|mob)/is";
				$reg2= "/(splash)/is";

				$replyinfo = array();

				// pc/mob 数据同步线上
				if(preg_match($reg1, $str)){
					$getresult_pcmob= $this->offlinePcmob($datalist);
					if($getresult_pcmob['count'] >0){
						$this->jsonOutPut('1','pc/mob端'.$getresult_pcmob['errmsg']);
						return;
					}

					$replyinfo[] = "下线的资源位id为:".implode(",",$getresult_pcmob['data']);
				}

				// splash数据同步
				if(preg_match($reg2, $str)){
					$getresult_splash= $this->offlineSplash($datalist);
					if($getresult_splash['count'] >0){
						$this->jsonOutPut('1','splash数据'.$getresult_splash['errmsg']);
						return;
					}
					$replyinfo[] = "splash下线成功";
				}

				//下线状态 为-1
				$history = array("reply_info"=>implode(",",$replyinfo),"reply_status"=>"-1","name"=>$name,"username"=>$username,"time"=>$today,"flag"=>"已下线");
				$auditarr = array("todo"=>"","do"=>array(),"history"=>array("-1"=>array(0=>$history)));
				$datas=array("status"=>"-1","reply_status"=>"1","auditinfo"=>json_encode($auditarr));
				$result = $this->AppHomefocus->update($id,$datas);

				if($result){
					$this->jsonOutPut(0,'下线成功');
				} else {
					$this->jsonOutPut(1,'下线数据同步失败');
				}
			}

		} else {
			$this->jsonOutPut(1,'数据库中获取不到此id的数据');
		}


	}


		//最后编辑页面
	public function actionEdit(){
		$request = $_REQUEST;
		$id = $request['id'];
		if(!$id || $id<0){
			$msg = "缺少id参数";
			$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
			$this->errordata($errorArr);
		}

		$wherearr = array("id"=>$id);
		$datas = $this->AppHomefocus->getList($wherearr);
		$datas = $this->dealData($datas);

		if($datas[0]) {
			$datas = $datas[0];
			if($datas['role'] != 'online'){
				$msg = "您没有上线权限";
				$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
				$this->errordata($errorArr);
				//$this->jsonOutPut(1,'您没有编辑权限，请您返回首页查看');
				return;
			}
		}


		$result['flowname'] = json_encode( $this->flowname);
		$result['datas']=json_encode($datas);
		$result['category'] = json_encode($this->category);
		$result['statusName'] = json_encode($this->statusName);
		$this->render('homefocus/edit.tpl',$result);


	}

	//在线修复
	public function actionHotEdit(){
		$data = $_REQUEST;
		$status = $data['flow'];
		$id = $data['id'];
		$wherearr = array("status"=>$status,"id"=>$id);
		$result = $this->AppHomefocus->getlist($wherearr);
		$result = $this->dealData($result,1);
		//print_r('<pre>');print_r($result);exit;
		$process=$this->appHomefocus->getProcesslist();
		foreach($process as $k=>$v){
			if($v['id']<2 || $v['id']>4){
				unset($process[$k]);
			}
		}
		$this->render('homefocus/hot_edit.tpl',array("result"=>json_encode($result),'processList'=>$process,'source_id'=>$id));
	}

	//微信钱包打通
	public function actionWechatPurse(){
		$request = $_REQUEST;
		$id = $request['id'];
		if(!$id || $id<0){
			$msg = "缺少id参数";
			$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
			$this->errordata($errorArr);
		}

		$wherearr = array("id"=>$id);
		$datas = $this->AppHomefocus->getList($wherearr);
		$datas = $this->dealData($datas);

		if($datas[0]) {
			$datas = $datas[0];
			if($datas['role'] != 'wechat'){
				$msg = "您没有上线权限";
				$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
				$this->errordata($errorArr);
				//$this->jsonOutPut(1,'您没有编辑权限，请您返回首页查看');
				return;
			}
		}


		$result['flowname'] = json_encode( $this->flowname);
		$result['datas']=json_encode($datas);
		$result['category'] = json_encode($this->category);
		$result['statusName'] = json_encode($this->statusName);

		$this->render('homefocus/wechatpurse.tpl',$result);
	}


	//微信钱包点击保存后
	public function actionWechatPurseEditSave(){

		$data = $_REQUEST['data'];
		$datas = json_decode($data,true);
		$datas['status'] = $_REQUEST['status'];
		$datas['reply_status'] = $_REQUEST['reply_status']; //小状态 申请
//var_dump($datas);die();
		$id = $_REQUEST['id'];
		//$status = $datas['status'];

		if(!$id || $id <0){
			$this->jsonOutPut(1,'缺少id参数');
			return;
		}
/*
 * 2次更新合为1次了
		//$result = $this->AppHomefocus->update($id,$datas);
*/
		$today = date('Y-m-d H:i:s',time());
		$wherearr = array("id"=>$id);
		$result = $this->AppHomefocus->getList($wherearr);
		if(empty($result)){
			$this->jsonOutPut(1,'无查询到相应数据');
			return;
		}
		$result = $result[0];

		//检查微信钱包时间冲突
		if(!$this->AppHomeFocus->checkWechat($datas)){
			$this->jsonOutPut(1,'微信上线时间冲突');
			return;
		}
		if(($result['status'] == 0 || $result['status'] == 5) && $result['reply_status'] == 1){
			//本地库更新状态status为6

			//更新本地数据库
				$replyinfo[] = "打通微信钱包";
				//更新本地库 status＝5 为上线状态 replay_status =1;
				$username = Yii::app()->user->username;
				$name = Yii::app()->user->name;
				$history = array("reply_info"=>implode(",",$replyinfo),"reply_status"=>0,"name"=>$name,"username"=>$username,"time"=>$today,"flag"=>"微信");
				$auditarr = array("todo"=>"","do"=>array(),"history"=>array("6"=>array(0=>$history)));
				$wherearr = array("reply_status"=> 1,"auditinfo"=>json_encode($auditarr));
				foreach($datas as $k => $v){
					$wherearr[$k]=$v;
				}
			//var_dump($datas);die();
				$wechatPurseId=$this->AppHomefocus->insertWechat($datas);
				$wherearr['wechat_id']=$wechatPurseId;
				$wherearr['status']=6;
				$this->AppHomefocus->update($id,$wherearr);
				//插入微信钱包数据库
				$this->jsonOutPut(0,'上线更新成功');
				return;
		} else {
			$this->jsonOutPut(1,'您没有打通微信钱包的权限');
			return;
		}

	}

	//微信钱包下线
	public function actionOfflineWechatPurse()
	{
		$id = $_REQUEST['id'];

		if(!$id || $id <0){
			$this->jsonOutPut(1,'缺少id参数');
			return;
		}

		$today = date('Y-m-d H:i:s',time());
		$wherearr = array("id"=>$id);
		$result = $this->AppHomefocus->getList($wherearr);
		if(empty($result)){
			$this->jsonOutPut(1,'无查询到相应数据');
			return;
		}
		$result = $result[0];

		if(($result['status'] == 0 || $result['status'] == 6) && $result['reply_status'] == 1){
			//本地库更新状态status为5

			//下线微信钱包数据库
			$offline_wechat=$this->AppHomefocus->offlineWechat($result['wechat_id']);
			if(!$offline_wechat){
				$this->jsonOutPut(1,'下线失败');
				return;
			}

			//更新本地数据库
			$replyinfo[] = "下线微信钱包";
			//更新本地库 status＝5 为上线状态 replay_status =1;
			$username = Yii::app()->user->username;
			$name = Yii::app()->user->name;
			$history = array("reply_info"=>implode(",",$replyinfo),"reply_status"=>0,"name"=>$name,"username"=>$username,"time"=>$today,"flag"=>"下微信");
			$auditarr = array("todo"=>"","do"=>array(),"history"=>array("7"=>array(0=>$history)));
			$wherearr = array("status"=>5,"reply_status"=> 1,"auditinfo"=>json_encode($auditarr));
			$this->AppHomefocus->update($id,$wherearr);
			$this->jsonOutPut(0,'下线成功');
			return;
		} else {
			$this->jsonOutPut(1,'您没有打通微信钱包的权限');
			return;
		}

	}


	//编辑保存同时上线
	public function actionEditSave(){

		$data = $_REQUEST['data'];
		$datas = json_decode($data,true);
		$datas['status'] = $_REQUEST['status'];
		$datas['reply_status'] = $_REQUEST['reply_status']; //小状态 申请
		$datas['location'] = $_REQUEST['location'];

		$id = $_REQUEST['id'];
		$status = $datas['status'];

		if(!$id || $id <0){
			$this->jsonOutPut(1,'缺少id参数');
			return;
		}

		if(isset($datas['hot_fix'])&&$datas['hot_fix']==1){
			$datas = json_decode($_REQUEST['data'],true);
			$datas['status'] =5;
			$datas['reply_status'] = 1;
		}
		$result = $this->AppHomefocus->update($id,$datas);

		$today = date('Y-m-d H:i:s',time());
		$wherearr = array("id"=>$id);
		$result = $this->AppHomefocus->getList($wherearr);

		if(empty($result)){
			$this->jsonOutPut(1,'无查询到相应数据');
			return;
		}
		$result = $result[0];

		if((($result['status'] == 0 || $result['status'] == 4) && $result['reply_status'] == 1)||(isset($datas['hot_fix'])&&$datas['hot_fix']==1)){
				$relation_id = $result['relation_id'];
				if($relation_id != 0){
					//$relation_idarr = explode(',',$relation_id);
					/*$location_ids = array();
                    foreach($relation_idarr as $key=>$val){
                        $resultid = $this->AppHomefocus->updateOffline($val);
                        $location_ids[] = $resultid;
                    }*/
					$datalist = $result;
					$str=$datalist['location'];
					$reg1= "/(pc|mob)/is";
					$reg2= "/(splash)/is";
					$replyinfo = array();
					// pc/mob 数据同步线上
					if(preg_match($reg1, $str)){
						$getresult_pcmob= $this->offlinePcmob($datalist);
						if($getresult_pcmob['count'] >0){
							$this->jsonOutPut('1','pc/mob端'.$getresult_pcmob['errmsg']);
							return;
						}
						$replyinfo[] = "下线的资源位id为:".implode(",",$getresult_pcmob['data']);
					}

					// splash数据同步
					if(preg_match($reg2, $str)){
						$getresult_splash= $this->offlineSplash($datalist);
						if($getresult_splash['count'] >0){
							$this->jsonOutPut('1','splash数据'.$getresult_splash['errmsg']);
							return;
						}
						$replyinfo[] = "splash下线成功";
					}
				}

			//1.同步到正式线上库 2.本地库更新状态status为5
			//处理与线上库同步的数据字段
			$str=$result['location'];
			$reg1= "/(pc|mob)/i";
			$reg2= "/(splash)/i";

			// pc/mob 数据同步线上
			$relationid_arr = array();
			$replyinfo = array();

			$count = 0;
			if(preg_match($reg1, $str)){
				$getresult_pcmob= $this->onlinePcmob($result);
				if($getresult_pcmob['count'] >0){
					$this->jsonOutPut(1,'pc/mob端'.$getresult_pcmob['errmsg']);
					return;
				}
				$location_idarr =$getresult_pcmob['location_idarr'];
				$relationid_arr =$getresult_pcmob['relationid_arr'];
				$replyinfo[]="上线资源位的id为".implode(",",$location_idarr);
				$count = $getresult_pcmob['count'];
			}

			// splash数据同步
			if(preg_match($reg2, $str)){
				$getresult_splash= $this->onlineSplash($result);
				if($getresult_splash['count'] >0){
					$this->jsonOutPut('1','splash数据'.$getresult_splash['errmsg']);
					return;
				}

				$replyinfo[] = "splash 更新数据id为".$getresult_splash['content_id'];
				$relationid_arr[] = $getresult_splash['content_id'];
				$count = $getresult_splash['count'];
			}
			//更新本地数据库
			if($count == 0){
				//更新本地库 status＝5 为上线状态 replay_status =1;
				$username = Yii::app()->user->username;
				$name = Yii::app()->user->name;
				$history = array("reply_info"=>implode(",",$replyinfo),"reply_status"=>0,"name"=>$name,"username"=>$username,"time"=>$today,"flag"=>"已上线");
				$auditarr = array("todo"=>"","do"=>array(),"history"=>array("5"=>array(0=>$history)));
				$wherearr = array("status"=>5,"reply_status"=> 1,"relation_id"=>implode(',',$relationid_arr),"auditinfo"=>json_encode($auditarr));
				$this->AppHomefocus->update($id,$wherearr);

				$this->jsonOutPut(0,'上线更新成功');
				return;
			} else {
				$this->jsonOutPut(1,'上线更新失败');
				return;
			}

		} else {
			$this->jsonOutPut(1,'查询到该数据状态不能做上线操作，请返回首页查看详细');
			return;
		}

	}

	//紧急上线申请页面
	public function actionFastapply(){
		$data = $_REQUEST;
		$id = isset($data['id'])?$data['id']:0;

		$datas = array();

		if($id>0){
			$wherearr = array("id"=>$id);
			$datas = $this->AppHomefocus->getList($wherearr);
			$datas = $this->dealData($datas);
			if(!empty($datas)){
				$datas = $datas[0];

				//权限判断 测试

				if($data['flow'] > $datas['status'] && $datas['reply_status'] != '1'){
					//$this->jsonOutPut(1,'工单流程不对，请返回首页');
					$errorArr = array("status"=>1,"data"=>'','msg'=>"您的工单流程环节不对");
					$this->errordata($errorArr);
					return;
				}

				if($datas['role'] !="creater" ){
					//$this->jsonOutPut(1,'工单流程不对，请返回首页');
					$msg = ($datas['reply_status'] == '1')?"您的申请审核已通过":"您的申请已经在审核中";
					$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
					$this->errordata($errorArr);
					return;
				} else {
					//只要还没有人来审核都是可以修改的
					$auditinfo = json_decode($datas['auditinfo'],true);
					//print_r('<pre>');print_r($datas);exit;
					if(( ($datas['reply_status'] == '0' && !empty($auditinfo['do'])) ||$datas['reply_status'] == '1') && $data['flow'] == $datas['status']){
						//$this->jsonOutPut(1,'您的申请审核已通过，请返回首页');
						$msg = ($datas['reply_status'] == '1')?"您的申请审核已通过":"您的工单还处于审核中状态";
						$errorArr = array("status"=>1,"data"=>'','msg'=>$msg);
						$this->errordata($errorArr);
						return;
					}
				}

			}
		}
		$result = array();
		$result['datas']=json_encode($datas);
		$result['statusName'] = json_encode($this->statusName);

		$this->render('homefocus/fastapply.tpl',$result);
	}

	//校验curl 是否有效合法的
	public function actionCheckurl(){
		$url = $_REQUEST['url'];
		if($url == ''){
			$this->jsonOutPut(1, "无效url资源！");
			return;
		}
		$this->jsonOutPut(0,'有效的url');
		/*$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		$result = curl_exec($curl);
		if ($result !== false) {
			$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ($statusCode != 404) {
				$this->jsonOutPut(0,'有效的url');
			} else {
				$this->jsonOutPut(1,'无效url资源');
			}
		} else {
			$this->jsonOutPut(1,'无效url资源');
		}*/

	}

	//获取审核流程信息 status流程状态＝流程id  $auditinfo审核信息 $apply 申请0 审核1 -1撤消  $newinfo={"reply_status":"","reply_info":""} $Cc 抄送人
	function getAuditinfo($status=false,$auditinfo=array(),$isapply=0,$newrelyinfo=null){
		$id = isset($status) ? (int)$status : 1;
		$result =$this->AppHomefocus->getProcessinfo($id);
		$params = array("reply_status"=>0); // 包含 reply_status 和 auditinfo两个字段的信息
		//添加获取审核信息
		foreach($result as $key=>$val){

			if($val['id'] == $status){
				$auditor = $val["auditor"];
				break;
			}
		}

		if(empty($auditinfo)){
			//审核信息
			$auditinfo=array("todo"=>"","do"=>array(),"history"=>null);
		}
		//print_r($result);print_r($auditor);exit;
		//申请 1、todo重新赋值审核人 2、清空do数组 3、更改status 小状态0

		//一个个测验委托人
		$auditor_arr=explode(',',$auditor);
		foreach($auditor_arr as $k =>$v){
			$auditor_arr[$k]=$this->get_entrust($v);
		}
		$auditor=implode(',',$auditor_arr);

		if($isapply==0) {
			$auditinfo['todo'] = $auditor;
			//清空do里的
			$auditinfo['do'] = array();
			//$auditinfo['history'] = array();
			$params['reply_status'] = 0;

		} else if($isapply == -1){
			//撤消
			$today = date('Y-m-d H:i:s',time());
			$auditinfo['todo'] = "";
			//清空do里的
			$auditinfo['do'] = array();
			$params['reply_status'] = -1;
			//$auditinfo['history'] = array();
			//清空
			$name = Yii::app()->user->name;
			$username = Yii::app()->user->username;

			//$do=array();
			$do = $auditinfo['history'][$status];
			$newrelyinfo['name'] = $name;
			$newrelyinfo['username'] = $username;
			$newrelyinfo['time'] = $today;
			$newrelyinfo['flag'] =$this->statusName[$status]."撤消";
			$newrelyinfo['reply_info']='';
			$do[] = $newrelyinfo;
			$auditinfo['history'][$status] = $do;

		} else {
			//审核1、todo减去一人 2、do添加一条信息 3.如果最后一个人审核 （3.1）清空todo  do  （3.2）do的内容添加到history
			$today = date('Y-m-d H:i:s',time());

			//审核todo 减 并记录审核信息
			$todo = $auditinfo['todo'];
			//$do = json_decode($auditinfo['do'],true);
			$do = $auditinfo['do'];
			//减去todo
			$name = Yii::app()->user->name;
			$username = Yii::app()->user->username;
			$todoarr = explode(",",$todo);
			$newtodo = array();
			if(!empty($todoarr)){
				foreach($todoarr as $key=>$val){
					//var_dump($val);var_dump($username);
					if($val != $username){
						$newtodo[]=$val;
					}
				}
			}

			//do 添加一条信息
			if($newrelyinfo){
				$newrelyinfo['name'] = $name;
				$newrelyinfo['username'] = $username;
				$newrelyinfo['time'] = $today;
				$newrelyinfo['flag'] = $newrelyinfo['reply_status'] == 1 ?"通过":"未通过";
				$do[] = $newrelyinfo;

			}
			//print_r($newtodo);print_r('----');print_r(count($do));exit;
			//最后一个人审核的时候 判断（3.1）清空todo  do  （3.2）do的内容添加到history
			if(count($newtodo)==0 && count($do)>0){
				$count = 0;
				foreach($do as $key=>$val){
					if($val['reply_status'] == 2 ){
						$count++;
					}
				}
				if(!empty($auditinfo['history']) && isset($auditinfo['history'][$status])){
					$history = array_merge($auditinfo['history'][$status],$do);
				} else {
					$history = $do;
				}
				$auditinfo['history'][$status] = $history;

				//清空do里的
				$do = array();

				//通过 不通过的
				$params['reply_status'] = ($count>0) ? 2 : 1;

			}

			$auditinfo['todo'] = implode(',',$newtodo);
			$auditinfo['do'] = $do;

		}
		$params['auditinfo'] = $auditinfo;
		$leadername = "";
		//第四步 上线申请需要leader首批 或者紧急上线时 leader的
		if($id == 4 ){
			//获取leader
			$leader = $this->getLeader();
			$leadername = $leader['name'];
			//委托
			$leadername=$this->get_entrust($leadername);
		}
		$params['leader']= $leadername;
		return $params;

	}

	function get_entrust($name){
		if($name==''){
			return '';
		}
		$result=$this->AppHomefocus->Entrust($name);
		if($result['entrust']==''){$result['entrust']=$name;}
		return $result['entrust'];
	}

	// 图片上传到图片服务器接口
	function getUploadpic($files){
		include Yii::app()->basePath . '/libs/commupload.php';
		//test
		//$url_pre = 'http://192.168.128.16:80';
		//online
		$url_pre = "http://pic.upload.meilishuo.net:2323";
		$url = $url_pre . "/pic/commupload";
		// print_r($_FILES['file']['tmp_name']);
		$content = file_get_contents($files['file']['tmp_name']);
		$bytes = sendHttpPostImageRequest($url, $content, 'pic');
		$result = json_decode($bytes,true);
		// {"ret":"0","msg":"success","data":{"picid":"1345823458","nwidth":"703","nheight":"1055","n_pic_file":"pic\/_o\/1b\/5c\/bf18478f8a9ec4ce825660822954_703_1055.c1.jpg","size":"103276"}}
		$result['status'] = $result['ret'];
		return $result;
	}

	//处理列表数据 result $operation列表按钮
	function dealData($datalist,$replyinfo=false){
		//datalist total pages

		if(count($datalist) == 1 && !$datalist[0]){
			return $datalist;
		}

		$locationarr = array("mob"=>"mob","pc"=>"pc","splash"=>"splash");

		foreach($datalist as $key=>$val){

			//是否为mob,是否需要打通微信钱包


			//新添字段locationstr 首焦位置转为汉字
			$location = explode(",",$val['location']);

			if(count($location)>0){
				foreach($location as $k=>$v){
					$location[$k] =  $locationarr[$location[$k]];
				}
			}
			$datalist[$key]['locationstr'] = implode("&",$location);

			//是否为mob,是否需要打通微信钱包
			$datalist[$key]['isMob']=0;
			if(in_array('mob',$location)) {
				$datalist[$key]['isMob']=1;
			}

			//状态
			$statusname = $this->statusName[$val['status']];
			if($val['status'] != '-1' && $val['status']!= 5 &&$val['status'] != '0'){
				$statusname=$this->replyName[$val['reply_status']]." <br/>(".$statusname.")";
			}

			$datalist[$key]['statusname'] = $statusname;

			//角色
			$rolearr = $this->checkRole($val);
			$datalist[$key]['role'] = $rolearr['role'];
			//$datalist[$key]['role'] = "online";
			$datalist[$key]['issuper'] = $rolearr['issuper'];

			//审核信息=>查看详细信息的 审核信息
			if($replyinfo){
				$datalist[$key]['replyinfo'] = $this->replyInfo($val);
			}

			//外链信息
			if($val['onlineinfo']){
				$datalist[$key]['onlineinfo'] = $this->dealURLdata($val);
			}

			$datalist[$key]['fashioninfostr'] = $this->getfashinfostr($val);

			//处理列表图片外链
			$urlarr = $this->dealImgurldata($datalist[$key]);
			$datalist[$key]['imgurl'] = $urlarr['img'];
			$datalist[$key]['outurl'] = $urlarr['url'];

		}
		return $datalist;

	}

	function sendMail($sendmails,$html,$title=false){
		if(!$title){
			$title = "资源位申请流程";
		}
		//$html="";
		//$sendmails = "manli,bangzhongpeng";
		$this->AppHomefocus->sendMail($sendmails,$html,$title);

	}

	//角色判断添加字段role 创建者creater  审核者reply  普通用户all  超级用户superuser
	function checkRole($datalist){

			//$user = Yii::app()->user->name;
			$username = Yii::app()->user->username;
			$status = $datalist['status'];
			//创建者
			$datausername = $datalist['creater_name'];

			//审核者
			$auditinfo = json_decode($datalist['auditinfo'],true);
			$todo = $auditinfo['todo'];

			//超级用户
			$superarr = $this->AppHomefocus->getSuper();
			$superuser = $superarr['superuser'];

			//普通用户
			$role = 'all';
			//是否超级用户
			$issuper = 0;

			//最后上线代码同步
		   $result =$this->AppHomefocus->getProcessinfo('5');
		   $onlinename = $result[0]['auditor'];


			//微信钱包打通
			$result2 =$this->AppHomefocus->getProcessinfo('6');
			$wechatname = $result2[0]['auditor'];

			$reg1= "/(".$username.")/i";

			if($datausername == $username){
				$role = 'creater';
			} else {

				//审核者 例外：状态为4时串行 表示为串行位置必须为首位
				if($todo != ''){
					$todoarr = explode(",",$todo);
					if( strstr($todo,$username) && $status != 4){
						$role ='reply';
					}
					//串行第一个人
					if($todoarr["0"] == $username && $status == 4 ){
						$role ='reply';
					}

				} else {
					//上线的权限
					if(($status== "6" || $status== "5" || $status== "4" || $status =="0") && $datalist['reply_status']== '1' && preg_match($reg1,$onlinename)){
						$role = 'online';
					}
					//微信钱包打通权限
					if(($status== "6" || $status== "5") && $datalist['reply_status']== '1' && preg_match($reg1,$wechatname)){
						$role = 'wechat';
					}
				}

			}

			//超级用户
			if(in_array($username,$superuser)){
				$issuper =1 ;
			}

		  return array("role"=>$role,"issuper"=>$issuper);
	}

	//审核信息
	function replyInfo($datalist){
		$status = $datalist['status'];
		$reply_status = $datalist['reply_status'];
		$auditinfo = json_decode($datalist['auditinfo'],true);
		$replyinfo = array();

		//取todo 的数据 审核中
		if($reply_status == 0){
			//未审核的人员
			$do = $auditinfo['do'];

			if(count($do)>0 ){
				$replyinfo = $do;
			}

			if( $auditinfo['todo']!=''){
				$todo = explode(',',$auditinfo['todo']);
				foreach($todo as $k=>$v ){
					$nameinfo = $this->getSpeedName($v);
					$replyinfo[] = array("reply_info"=>"","reply_status"=>0,"name"=>$nameinfo['name'],"flag"=>"未审核");
				}
			}

		} else {
		 //取history的
			$history = $auditinfo['history'];
			if($history != null && isset($history[$status])){
				$replyinfo = $history[$status];
			}
		}
		return $replyinfo;
	}

	//处理pc 和 mob 线上数据
	function onlinedata($datalist){
		//location_list 表1字段
		//location_key，start_time，end_time，is_active＝1，location_sort 位置排序

		//materiel 表2字段
		//title，image_url，url，ctime，operator ＝ 操作人的id

		//relation 表3字段
		//location_id, materiel_id, verify_status=1 ,verify_time,verify_operator 操作人的id
		$operator = Yii::app()->user->id;

		$mydata = array("title"=>$datalist['active_name'],"operator"=>$operator,
			"start_time"=>$datalist['starttime'],"end_time"=>$datalist['endtime'],
			"location_sort"=>$datalist['locationsort']);

		$onlineinfo = json_decode($datalist['onlineinfo'],true);
		$mydatalist = array();

		$bannerinfo = json_decode($datalist['bannerinfo'],true);

		if(isset($bannerinfo['banner_mob'])){
			foreach($bannerinfo['banner_mob'] as $key=>$val){
				$mydata['image_url'] = $val['n_pic_file'];
				$mydata['url'] = $this->encodeMeilishuoURL($onlineinfo['mob']['url_type'],$onlineinfo['mob']['url_params']);
				$mydata['location_key'] = $key;
				array_push($mydatalist,$mydata);
			}
		}

		if(isset($bannerinfo['banner_pc'])){
			foreach($bannerinfo['banner_pc'] as $key=>$val){
				$mydata['image_url'] = $val['n_pic_file'];
				$mydata['url'] = json_encode(array('url'=>$onlineinfo['pc']['url']));
				$mydata['location_key'] = $key;
				array_push($mydatalist,$mydata);
			}
		}


		return $mydatalist;
	}

	//insert pc和mob数据库
	function onlinePcmob($result){
		$count = 0;
		$mydatalist = $this->onlinedata($result);

		//组织查询条件数据规则 方便检测这个时间段的资源位是否存在
		$locationkey_arr = array();
		foreach($mydatalist as $k=>$val){
			array_push($locationkey_arr,"'".$val['location_key']."'");
		}
		$locationkey_str = implode(",",$locationkey_arr);
		//检测这个时间段的资源位是否存在
		$mydata = array("start_time"=>$result['starttime'],"end_time"=>$result['endtime'],
			"location_sort"=>$result['locationsort'],"locationkey_str"=>$locationkey_str);
		$getdata = $this->AppHomefocus->getonline($mydata);
		//var_dump(strtotime($mydata['end_time']));die();
		if(strtotime($mydata['end_time'])<time()){

		}
		if(!empty($getdata) && $getdata[0]){
			$resultData = array("count"=>1,"errmsg"=>"此资源位在这段时间已经被占用");
			return $resultData;
		}

//

		$relationid_arr = array();
		$location_idarr = array();
		foreach($mydatalist as $key=>$val){
			$result = $this->AppHomefocus->insertOnline($val);
			if(intval($result['realtion_id']) <0){
				$count++;
				break;
			} else {
				array_push($relationid_arr,$result['realtion_id']);
				array_push($location_idarr,$result['location_id']);
			}
		}

		//更新数据成功
		if($count == 0){
			$resultData = array("count"=>0,"errmsg"=>"数据更新成功","relationid_arr"=>$relationid_arr,"location_idarr"=>$location_idarr);
		} else {
			$resultData = array("count"=>$count,"errmsg"=>"数据更新失败");
		}
		return $resultData;

	}

	//处理splash 数据格式
	function onlineSplash($datalist){
		/*splash  数据库的数据字段 password 与data_json

		//password:Activity_splash_global
		//data_json=[{"acticity_title":"desire优惠券",
		//"pic_for_iphone":"http://d05.res.meilishuo.net/img/_o/79/e7/5ccd1a3ea31ba0b1f7eac05402bf_640_960.cf.jpg",
		//"pic_for_iphone5":"http://d06.res.meilishuo.net/img/_o/62/9b/7560bf6dc1760a1d5c284b795240_640_1136.cf.jpg",
		//"pic_for_android":"http://d05.res.meilishuo.net/img/_o/fb/fa/29c6704fb17eb0ee0975eba23502_720_1280.cf.jpg"
		//"pic_for_android_pad":"",
		//"begin_time":"2015-11-23 10:00:00",
		//"end_time":"2015-11-24 10:00:00",
		//"url":"http://mapp.meilishuo.com/zulily/newPush?cid=16197&hdtrc=desire_kaiji1123&src=desire_kaiji1123"}];
		*/

		$onlineinfo = json_decode($datalist['onlineinfo'],true);
		$bannerinfo = json_decode($datalist['bannerinfo'],true);
		$splashlist = array("acticity_title"=>$datalist['active_name'],"pic_for_android_pad"=>'',
			"begin_time"=>$datalist['starttime'],
			"end_time"=>$datalist['endtime'],
			"url"=>$onlineinfo['splash']['url']);
		//不包含splash
		if(!isset($bannerinfo['Activity_splash_global'])){
			return false;
		}

		$resultData = array("count"=>0,"errmsg"=>"");

		foreach($bannerinfo['Activity_splash_global'] as $key=>$val){
			$splashlist[$key] = 'http://d02.res.meilishuo.net/'.$val['n_pic_file'];
		}

		//获取原来的数据，然后更新数据
		$password = "Activity_splash_global";
		$getSplashdata = $this->AppHomefocus->getSplashData($password);

		if(empty($getSplashdata)){
			$resultData['count'] = 1;
			$resultData['errmsg'] = "未查询到相应的数据";
			return $resultData;
		}

		$id =$getSplashdata[0]['content_id'];
		$datajson =$getSplashdata[0]['data_json'];
		$datajsonArr = json_decode($datajson,true);
		array_push($datajsonArr,$splashlist);
		$where = array("data_json"=>json_encode($datajsonArr));
		$result = $this->AppHomefocus->updateSplash($id,$where,"Activity_splash_global");

		if($result>0){
			$resultData['count'] = 0;
			$resultData['errmsg'] = "数据更新成功";
			$resultData["content_id"]=$id;
		} else {
			$resultData['count'] = 1;
			$resultData['errmsg'] = "数据更新失败";
		}

		return $resultData;

	}

	//offlinepc 下线pcmob 更新状态即可
	function offlinePcmob($datalist){
		$resultData =  array();
		if(!is_array($datalist) || empty($datalist)){
			$resultData['count'] = 1;
			$resultData['errmsg'] = "数据为空";
			return $resultData;
		}
		$relation_id = $datalist['relation_id'];

		$relation_idarr = explode(',', $relation_id);
		$location_ids = array();
		foreach ($relation_idarr as $key => $val) {
			$resultid = $this->AppHomefocus->updateOffline($val);
			if($resultid){
				$location_ids[] = $resultid;
			}
		}

		$resultData['count'] = 0;
		$resultData['data'] = $location_ids;
		return $resultData;

	}
	//offlineSplash 下线splash 获取线上的数据json串，取出删除某个然后更新它
	function offlineSplash($datalist){
		$resultData =  array();
		if(!is_array($datalist) || empty($datalist)){
			$resultData['count'] = 1;
			$resultData['errmsg'] = "数据为空";
			return $resultData;
		}

		//获取原来的数据，然后更新数据
		$password = "Activity_splash_global";
		$getSplashdata = $this->AppHomefocus->getSplashData($password);
		if(empty($getSplashdata)){
			$resultData['count'] = 1;
			$resultData['errmsg'] = "未查询到相应的spalsh数据";
			return $resultData;
		}

		//$bannerinfo = json_decode($datalist['bannerinfo'],true);

		$starttime = $datalist['starttime'];
		$endtime = $datalist['endtime'];
		$active_name = $datalist['active_name'];

		//old数据
		$id =$getSplashdata[0]['content_id'];
		$datajson =$getSplashdata[0]['data_json'];
		$datajsonArr = json_decode($datajson,true);

		$newdatajsonArr = array();
		foreach( $datajsonArr as $key=>$val){
			if($val['acticity_title'] == $active_name && $val['begin_time'] == $starttime && $val['end_time'] == $endtime){
				//不做处理
			} else {
				$newdatajsonArr[] = $val;
			}
		}

		$where = array("data_json"=>json_encode($newdatajsonArr));
		$result = $this->AppHomefocus->updateSplash($id,$where,"Activity_splash_global");

		if($result>0){
			$resultData['count'] = 0;
			$resultData['errmsg'] = "splash数据下线成功";
			$resultData["content_id"]=$id;
		} else {
			$resultData['count'] = 1;
			$resultData['errmsg'] = "splash数据下线失败";
		}



	}

	//处理异常错误页面
	function errordata($error){
		$this->render('homefocus/error.tpl',$error);
		return;
	}
	//业务方的leader
	function getLeader(){
		$username = Yii::app()->user->username;
		$url="http://api.speed.meilishuo.com/user/get_superior?mail=".$username."&token=a595a9bd9909e72a792bb535379ed477";
		$result = $this->getCurlData($url);
		$mail = "";
		$name = "";

		//测试
		//$result="";

		if($result){
			$mail = $result['mail'];
			if($result['departid'] == 86){
				$mail="jiaqishen@meilishuo.com";
			}

			$namearr = explode('@',$mail);
			$name = $namearr[0];
		}
		return array("mail"=>$mail,"name"=>$name);
	}

	//speed接口 根据mail获取中文名
	function getSpeedName($mailname){
		$url="http://api.speed.meilishuo.com/user/show?mail=".$mailname."&token=a595a9bd9909e72a792bb535379ed477";
		$result = $this->getCurlData($url);
		if(!$result){
			$result = array("name"=>$mailname);
		}
		return $result;

	}


	//处理info信息
	function getfashinfostr($val){
		$fashion = json_decode($val['fashioninfo'],true);

		$str = "&lt;span style='font-weight:bold;color:#BEC1C3' &gt;文案：&lt;/span&gt;暂无";
		if(!empty($fashion)){
			$str = "&lt;span style='font-weight:bold;color:#BEC1C3'&gt;文案：&lt;/span&gt;".$fashion['tips'];
		}

		return $str;

	}
	//调用外部接口的
	function getCurlData($url){

		$output = $this->curl->get($url);
		$config = json_decode($output['body'], true);
		if($config['code'] == 200){
			$result = $config['data'];
		} else {
			$result = false;
		}
		return $result;
	}

	//首页获取 pc 和mob端 今天总帧数 明天总帧数
	function getTotallocation(){
		$date1 = date('Y-m-d',time());
		$starttime = date('Y-m-d H:i:s',time());
		//$endtime = date('Y-m-d 00:00:00',strtotime("+1 day"));

		$params1 = 'web_welcome_top_banner_carousel';//pc 或者web_welcome_top_banner_carousel
		$params2 = 'home_Top_Banner_6_6'; // home_Top_Banner_6_6 或者 mob

		$pc1 = $this->AppHomefocus->getTotalLocation($starttime,$starttime,$params1);
		$mob1 = $this->AppHomefocus->getTotalLocation($starttime,$starttime,$params2);

		$date2 = date('Y-m-d',strtotime("+1 day"));

		$starttime = date('Y-m-d 22:00:00',strtotime("+1 day"));//由于时间段开始时间有可能在一段时间
		$endtime = date('Y-m-d 00:00:00',strtotime("+1 day"));

		$pc2 = $this->AppHomefocus->getTotalLocation($starttime,$endtime,$params1);
		$mob2 = $this->AppHomefocus->getTotalLocation($starttime,$endtime,$params2);

		$data = array();
		$data[0] = array("date"=>$date1,"pc"=>$pc1,"mob"=>$mob1);
		$data[1] = array("date"=>$date2,"pc"=>$pc2,"mob"=>$mob2);
		//print_r('<pre>');print_r($data); exit;
		return $data;

	}


	//角色按钮 我的审核 丹阳的资源汇总 false＝0  true＝1
	function roleBtn(){
		$myreply = 0;//我的审核
		$myapplylist =0; // 丹阳的资源汇总

		$username = Yii::app()->user->username;
		//$name = Yii::app()->user->name;

		$reg1= "/(".$username.")/i";

		//资源汇总获取流程同步上线的人名
		$status = 5;
		$processinfo = $this->AppHomefocus->getProcessinfo($status);
		$processname = $processinfo['0']['auditor'];
		$myapplylist = (preg_match($reg1, $processname))?1:0;

		//我的审核  当id＝0时 获取1-5之间的流程审核人
		$status = 0;
		$getauditorArr = $this->AppHomefocus->getAuditorinfo($status);


		$myreply = 0;
		foreach($getauditorArr as $key=>$val){
			$auditor = $val['auditor'];

			if(preg_match($reg1, $auditor)){
				$myreply = 1;
				break;
			}
		}

		$result =  array("myreply"=>$myreply,"myapplylist"=>$myapplylist);

		return $result;

	}

	//处理外链信息
	function dealURLdata($val){
		$val['onlineinfo'] = json_decode($val['onlineinfo'],true);

		if(isset($val['onlineinfo']['mob'])){
			//print_r('<pre>');var_dump($val['onlineinfo']['mob']);exit;
			$urlparams = json_decode($val['onlineinfo']['mob']['url_params'],true);
			$lianjie=1;
			$type = $val['onlineinfo']['mob']['url_type'];
			switch($type){
				case "openURL":
					$moburl = $urlparams['url'];
					break;
				case "shop":
					$moburl = "http://www.meilishuo.com/shop/".$urlparams['shop_id'];
					break;
				case "twitter_single":
					$moburl = $this->encodeMeilishuoURL($val['onlineinfo']['mob']['url_type'],$val['onlineinfo']['mob']['url_params']);
					break;
				default:
					$moburl="类别 : ".$val['onlineinfo']['mob']['url_type']."    取值".$val['onlineinfo']['mob']['url_params'];
					$lianjie=0;
					break;
            }

			//$moburl = $this->encodeMeilishuoURL($val['onlineinfo']['mob']['url_type'],$val['onlineinfo']['mob']['url_params']);
			$val['onlineinfo']['mob']['moburl'] = urldecode($moburl);
			$val['onlineinfo']['mob']['mob_islianjie'] = $lianjie;
		}

		if(isset($val['onlineinfo']['pc'])) {
			$val['onlineinfo']['pc']['pcurl'] = $val['onlineinfo']['pc']['url'];
		}
		if(isset($val['onlineinfo']['splash'])) {
			$val['onlineinfo']['splash']['splashurl'] = $val['onlineinfo']['splash']['url'];
		}
		$onlineinfo = $val['onlineinfo'];

		return json_encode($onlineinfo);
	}

	//列表处理 url 和 img
	function dealImgurldata($val){
		$imgurl = array("img"=>"","url"=>"");
		if($val['bannerinfo'] != null){
			$bannerinfo = json_decode($val['bannerinfo'],true);

			if(isset($bannerinfo['banner_mob'])){
				$imgurl1 = $bannerinfo['banner_mob']['home_Top_Banner_6_6']['n_pic_file'];
			}
			if(isset($bannerinfo['banner_pc'])){
				$imgurl1 = $bannerinfo['banner_pc']['web_welcome_top_banner_carousel']['n_pic_file'];
			}
			//splash
			if(isset($bannerinfo['Activity_splash_global'])){
				$imgsplash = $bannerinfo['Activity_splash_global']['pic_for_iphone']['n_pic_file'];
			}

			$imgurl['img'] = $imgurl1 ? $imgurl1: $imgsplash;

		}

		if($val['onlineinfo']){
			$onlineinfo = json_decode($val['onlineinfo'],true);

			if(isset($onlineinfo['mob'])){
				$url1 = $onlineinfo['mob']['moburl'];
			}
			if(isset($onlineinfo['pc'])){
				$url1 = $onlineinfo['pc']['pcurl'];
			}
			if(isset($onlineinfo['splash'])){
				$url2=$onlineinfo['splash']['splashurl'];
			}
			$imgurl['url'] = $url1?$url1:$url2;
		}

		return $imgurl;
	}


	/*
	 * url 解码
	 * @param unknown_type $url
	 * @return multitype:mixed
	 */
	function decodeMeilishuoURL($url) {
		if(empty($url)){
			return false;
		}
		$url_array = parse_url($url);
		$url_params = explode('=', $url_array['query']);
		$explode = explode('.', $url_array['host']);
		return array(
			'url_type' => reset($explode),
			'url_params' => json_decode(urldecode(end($url_params)), TRUE),
		);
	}

	/*
	 * url 解码 WEB
	 * @param unknown_type $url
	 * @return multitype:mixed
	 */
	function decodeMeilishuoURLWeb($url) {
		if(empty($url)){
			return false;
		}
		$web_zhui = explode('json_params=',$url);
		$url_array = parse_url( urldecode( reset($web_zhui) ) );
		$url_array2 =  parse_url( urldecode( $web_zhui[1] ) );
		$explode = explode('.', $url_array['host']);
		return array(
			'url_type' => reset($explode),
			'url_params' => json_decode(urldecode(end($web_zhui)), TRUE),
		);
	}

	/*
	 * url 编码
	 * @param unknown_type $url_type
	 * @param unknown_type $params
	 * @param unknown_type $title
	 * @return string
	 */
	function encodeMeilishuoURL($url_type = 'openURL', $params = '', $title = '') {
		$params = json_decode($params, TRUE);
		if (!empty($title)) {
			$params['title'] = $title;
		}
		$params = urlencode(json_encode($params));
		return "meilishuo://{$url_type}.meilishuo?json_params={$params}";
	}


	//退出登录
	public function actionLogout(){
		setcookie('speed_token','/site/index',time()-3600,'/');
		$this->redirect('/site/index?lasturl=/AppHomefocus/index');
	}



}
