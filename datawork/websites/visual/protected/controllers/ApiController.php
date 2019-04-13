<?php
class ApiController extends Controller{
	function  __construct(){
		$this->objFackcube=new FackcubeManager();
		$this->objAuth=new AuthManager();
		$this->objProject=new ProjectManager();
		$this->objReport = new ReportManager();
	}

	//2015-4-21 开发者中心
	function actionIndex(){
		$data['project']=$this->objFackcube->get_project_list();
		$this->render('apitpl/index.tpl',$data);
	}

	//获取项目下的报表
	function actionGetReport($project=""){
		$project = $_REQUEST['project'];
		$reportList = $this->objReport->getReportList($project);
		$this->jsonOutPut(0,'success', $reportList);
	}

	//申请查看报表url
	//申请查看报表url
	function actionGetReportUrl(){
		$reportId = $_REQUEST['reportId'];
		$appName = $_REQUEST['appName'];
		$appToken = $_REQUEST['appToken'];

		$config = $this->objReport->getReoport($reportId);
		$params = isset($config['params']) ? $config['params']:array();

		$tablelist = array();
		$chart = array();

		//获取表格数据
		if(isset($params['table']) && !isset($params['tablelist'])){
			$params['tablelist']=array();
			$params['tablelist'][0]= $params['table'];
			$params['tablelist'][0]['title'] = $params['basereport']['cn_name'];
			$params['tablelist'][0]['type'] = $params['type'];
			unset($params['table']);
		}

		if(isset($params['tablelist'])){
			foreach($params['tablelist'] as $key=>$value){
				$value['api'] = 1;
				$value['appName'] = $appName;
				$value['appToken'] = $appToken;
				$tableurl = $this->objFackcube->getData($value,true);
				$table=array();
				$tableurl=$tableurl.'&'.'table_id='.$reportId;
				$table['tableurl'] = $tableurl;
				$table['title']= $value['title'];
				array_push($tablelist, $table);
			}
		}

		//获取图表数据
		
		if(isset($params['chart'])){
			foreach($params['chart'] as $key=>$value){
				$chartarr = array();
				$value['api'] = 1;
				$value['appName'] = $appName;
				$value['appToken'] = $appToken;

				$charturl = $this->objFackcube->getData($value,true);
				$chartTitle = $value['chartconf'][0]['chartTitle'];
				$chartType = $value['chartconf'][0]['chartType'];

				$charturl=$charturl.'&'.'table_id='.$reportId;
				$chartarr['charturl'] = $charturl;
				$chartarr['chartTitle'] = $chartTitle;
				$chartarr['chartType'] = $chartType;
				$chart[] = $chartarr;
			}
		}

		$datas = array("tablelist"=>$tablelist,"chart"=>$chart);
		$this->jsonOutPut(0,'success', $datas);
		exit;	

	}

}