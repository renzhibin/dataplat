<?php
class ApiController extends Controller{
	function  __construct(){
		$this->objFackcube=new FackcubeManager();
		$this->objAuth=new AuthManager();
		$this->objProject=new ProjectManager();
		$this->objReport = new ReportManager();
        $this->objoffline = new BehaviorManager();
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

	function actionGetQuery()
    {
        $action='get_check_report';
        $parament['project'] = $_REQUEST['project'];
        $parament['app_name'] = $_REQUEST['appName'];
        $parament['app_token'] = $_REQUEST['appToken'];
        $reportId = $_REQUEST['table_id'];

        if(empty($action)||empty($parament)){
            $this->jsonOutPut(1);
            exit();
        }
        //检查接口
        $result = ($this->objFackcube->get_fakecube($action,$parament));

        $retu = [];
        if ($result['status'] != 0 ) {
            $retu['status'] = 1;
            $retu['msg'] = $result['msg'];
            $retu['relyMsg'] = [];
            $retu['data'] = [];
            $retu['showMsg'] = [];

            echo json_encode($retu);
            exit;
        }

        if (!isset($result['data']['app_list']) || empty($result['data']['app_list'])) {
            $retu['status'] = 1;
            $retu['msg'] = "appName或appToken错误！";
            $retu['relyMsg'] = [];
            $retu['data'] = [];
            $retu['showMsg'] = [];

            echo json_encode($retu);
            exit;
        }

        //判断报表上下线状态
        if (isset($result['data']['app_list']) && !empty($result['data']['app_list'])) {
            $table_list = json_decode($result['data']['app_list'][0]['table_id'], true);
            if (!isset($result['data']['app_list'][0]['status']) || $result['data']['app_list'][0]['status'] == 0) {
                $retu['status'] = 1;
                $retu['msg'] = '项目:'.$result['data']['app_list'][0]['app_name']."已下线！";
                $retu['relyMsg'] = [];
                $retu['data'] = [];
                $retu['showMsg'] = [];

                echo json_encode($retu);
                exit;
            };

            if (isset($table_list[$_REQUEST['project']]) && !empty($table_list[$_REQUEST['project']])) {
                if (isset($table_list[$_REQUEST['project']][$reportId]) && !empty($table_list[$_REQUEST['project']][$reportId])) {
                    $report_project = $table_list[$_REQUEST['project']][$reportId];
                    if (isset($report_project['flag']) && $report_project['flag'] != 1) {
                        $retu['status'] = 1;
                        $retu['msg'] = '报表:'.$report_project['report_name'] ."的报表已下线！";
                        $retu['relyMsg'] = [];
                        $retu['data'] = [];
                        $retu['showMsg'] = [];
                        echo json_encode($retu);
                        exit;
                    }
                } else {
                    $retu['status'] = 1;
                    $retu['msg'] = '报表ID:'.$reportId."的报表不存在！";
                    $retu['relyMsg'] = [];
                    $retu['data'] = [];
                    $retu['showMsg'] = [];
                    echo json_encode($retu);
                    exit;
                }
            } else {
                $retu['status'] = 1;
                $retu['msg'] = $_REQUEST['project']."项目不存在！";
                $retu['relyMsg'] = [];
                $retu['data'] = [];
                $retu['showMsg'] = [];
                echo json_encode($retu);
                exit;
            }
        }

        $params = array();
        $params['table_id'] = (string)$reportId;
        $params['appToken'] = $parament['app_token'];
        $params['app_user_name'] = $result['data']['app_list'][0]['user_name'];
        $this->objoffline->addUserBehaviorToLog('', '', '/report/showreport/' . $params['table_id'], $params);

        $url= WEB_API . "/query_app?" . http_build_query($_REQUEST);
        $retu = Yii::app()->curl->get($url);

        if($retu['http_code']!=200){
            $retu['status'] = 1;
            $retu['msg'] = "服务出现故障！";
            $retu['relyMsg'] = [];
            $retu['data'] = [];
            $retu['showMsg'] = [];

            echo json_encode($retu);
            exit;
            die();
        }

        echo ($retu['body']);
        exit;
    }

	//申请查看报表url
	function actionGetReportUrl(){
		$reportId = $_REQUEST['reportId'];
		$config = $this->objReport->getReoport($reportId);

		$params = isset($config['params']) ? $config['params']:array();

		if(isset($params['table']) && !isset($params['tablelist'])){
			$params['tablelist']=array();
			$params['tablelist'][0]= $params['table'];
			$params['tablelist'][0]['title'] = $params['basereport']['cn_name'];
			$params['tablelist'][0]['type'] = $params['type'];
			unset($params['table']);
		}

        //获取表格数据
        $tablelist = $this->getTableList($params, $_REQUEST);
        //获取图表数据
        $chart = $this->getCharList($params, $_REQUEST);

        $result = $this->saveReport($_REQUEST, $params);
        $status = json_decode($result, true);

        if ($status['status'] == 1) {
            $this->jsonOutPut(1,$status['msg'], []);
            exit;
        }

		$datas = array("tablelist"=>$tablelist,"chart"=>$chart);
		$this->jsonOutPut(0,'success', $datas);
		exit;
	}

	function saveReport($request, $parameter) {
        $arr = $this->getReportList($request, $parameter);
        $reportId = $request['reportId'];
        $appName = $request['appName'];

        $arrJson = json_encode($arr);

        $par['arr_json'] = $arrJson;
        $par['app_name'] = $appName;
        $par['user_name'] = Yii::app()->user->username;
        $action='save_report_json';

        if(empty($action)||empty($arrJson)){
            $this->jsonOutPut(1);
            exit();
        }
        $result = json_encode($this->objFackcube->get_fakecube($action,$par));

        return $result;
    }

    function actionDeleteReport() {
        $action='save_report_json';
        $arr = $this->deleteReportList($_REQUEST, 0);

        $arrJson = json_encode($arr);
        $par['arr_json'] = $arrJson;
        $par['app_name'] = $_REQUEST['appName'];
        $par['user_name'] = Yii::app()->user->username;

        if(empty($action)||empty($arrJson)){
            $this->jsonOutPut(1);
            exit();
        }

        $result = json_decode($this->objFackcube->get_fakecube($action,$par), true);

        if ($result['status'] == 1) {
            $this->jsonOutPut(1,$result['msg'], []);
            exit;
        }

        echo $this->jsonOutPut(0,'success', []);
        exit;
    }

    function deleteReportList($request, $flag) {
        $action='get_list_report';
        $reportId = $request['reportId'];
        $par['user_name'] =  Yii::app()->user->username;
        $par['app_name'] =  $request['appName'];
        $list = ($this->objFackcube->get_fakecube($action,$par));

        if (isset($list['data']['app_list']) && !empty($list['data']['app_list'])) {
            $table_list = json_decode($list['data']['app_list'][0]['table_id'], true);
            if (isset($table_list[$request['project']][$reportId]) && !empty($table_list[$request['project']][$reportId])) {
                $table_list[$request['project']][$reportId] = array(
                    'report_name' => $table_list[$request['project']][$reportId]['report_name'],
                    'flag' => $flag,
                );
            }
        }

        return $table_list;
    }

    function getReportList($request, $parameter) {
        $action='get_list_report';
        $reportId = $request['reportId'];
        $par['user_name'] =  Yii::app()->user->username;
        $par['app_name'] =  $request['appName'];
        $list = ($this->objFackcube->get_fakecube($action,$par));

        if (isset($list['data']['app_list']) && !empty($list['data']['app_list'])) {
            $table_list = json_decode($list['data']['app_list'][0]['table_id'], true);
        }

        if (isset($table_list) && !empty($table_list)) {
            if (isset($table_list[$parameter['basereport']['project'] ]) && !empty($table_list[$parameter['basereport']['project'] ] )) {
                $table_list[$parameter['basereport']['project']][$reportId] = array(
                    'report_name' => $parameter['basereport']['cn_name'],
                    'flag' => 1,
                );
            } else {
                $arr = array(
                    $parameter['basereport']['project'] => array(
                        $reportId => array(
                            'report_name' => $parameter['basereport']['cn_name'],
                            'flag' => 1,
                        ),
                    ),
                );

                $table_list = array_merge($table_list, $arr);
            }
        } else {
            $table_list = array(
                $parameter['basereport']['project'] => array(
                    $reportId => array(
                        'report_name' => $parameter['basereport']['cn_name'],
                        'flag' => 1,
                    ),
                ),
            );
        }

        return $table_list;
    }

    function getTableList($params, $request) {
        $reportId = $request['reportId'];
        $appName = $request['appName'];
        $appToken = $request['appToken'];
        $tableList = array();
        $baseUrl = Yii::app()->request->hostInfo.'/api/getquery?phantomjs=1&';
        if(isset($params['tablelist'])){
            foreach($params['tablelist'] as $key=>$value){
                $value['api'] = 1;
                $value['appName'] = $appName;
                $value['appToken'] = $appToken;
                $tableurl = $this->objFackcube->getData($value,true);
                $table=array();
                $tableurl = str_ireplace(WEB_API . "/query_app?", $baseUrl, $tableurl);

                $tableurl=$tableurl.'&'.'table_id='.$reportId;
                $table['tableurl'] = $tableurl;
                $table['title']= $value['title'];
                array_push($tableList, $table);
            }
        }

        return $tableList;
    }

    function getCharList($params, $request) {
        $reportId = $request['reportId'];
        $appName = $request['appName'];
        $appToken = $request['appToken'];
        $chart = array();
        $baseUrl = Yii::app()->request->hostInfo.'/api/getquery?phantomjs=1&';
        if(isset($params['chart'])){
            foreach($params['chart'] as $key=>$value){
                $chartarr = array();
                $value['api'] = 1;
                $value['appName'] = $appName;
                $value['appToken'] = $appToken;

                $charturl = $this->objFackcube->getData($value,true);
                $chartTitle = $value['chartconf'][0]['chartTitle'];
                $chartType = $value['chartconf'][0]['chartType'];

                $charturl = str_ireplace(WEB_API . "/query_app?", $baseUrl, $charturl);

                $charturl=$charturl.'&'.'table_id='.$reportId;
                $chartarr['charturl'] = $charturl;
                $chartarr['chartTitle'] = $chartTitle;
                $chartarr['chartType'] = $chartType;
                $chart[] = $chartarr;
            }
        }

        return $chart;
    }
}