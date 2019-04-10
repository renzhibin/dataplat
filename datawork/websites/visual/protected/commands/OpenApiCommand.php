<?php

class OpenApiCommand extends Command
{
    public $objReport;
    public $objMail;

    function __construct()
    {
        $this->objReport = new ReportManager();
        $this->objComm = new CommonManager();
        $this->objMail = new TimeMailManager();
        $this->behavior = new BehaviorManager();
        $this->objFackcube=new FackcubeManager();
    }

    public function main()
    {
        $cmd = "ps -ef | grep -v grep | grep 'php script/crons.php openapi' | wc -l";
        $res = array();
        exec($cmd, $res);
        if ($res[0] > 2) {
            $this->mail_log('提示', "  进程已存在，正常退出");
            exit();
        }
        $this->to_log('提示', "  *********  脚本已启动  *********");

        set_time_limit(0);

        # 获取所有开放的API
        $result = $this->getTokenList();
        $data = [];
        $data['title'] = 'DT开放API检测';
        $data['addressee'] = 'lvluole@xiaozhu.com,yangyulong@xiaozhu.com';
        foreach ($result as $items) {
            $reportTable = [];
            $parameter['appToken'] = $items['token_val'];
            $parameter['appName'] = $items['app_name'];

            if(isset($items['table_id']) && !empty($items['table_id'])) {
                $projectTables = explode(',', $items['project_name']);
                foreach ($projectTables as $tableProject) {
                    $reportTable[$tableProject] = $this->getCheckReportFlag($tableProject);
                }
                $tableList = json_decode($items['table_id'], true);
                # 检查报表状态 t_visual_table.flag
                foreach ($tableList as $tableProject => $tables) {
                    foreach ($tables as $tableId => $res) {
                        $parameter['reportId'] = $tableId;
                        $result = $this->getCheckTableFlag($tableId);
                        $flag = isset($result[0]['flag']) && $result[0]['flag'] == 1 ? 1 : 0;

                        $reportTable[$tableProject]['table'][$res['report_name']]['flag'] = $flag;

                        $resultTable = $this->getTestUrlStatus($parameter);
                        foreach ($resultTable['tablelist'] as $tableItems) {
                            $this->to_log('提示', "  *********  检查{$tableItems['title']}url  *********");
                            $rent = Yii::app()->curl->get($tableItems['tableurl']);
                            if($rent['http_code']!=200){
                                $this->to_log('提示', "  *********  检查{$tableItems['title']}url无法访问  *********");
                                $reportTable[$tableProject]['table'][$res['report_name']]['access'] = 0;
                            } else {
                                $reportTable[$tableProject]['table'][$res['report_name']]['access'] = 1;
                            }

                        }

                        foreach ($resultTable['chart'] as $tableItems) {
                            $this->to_log('提示', "  *********  检查URL:{$tableItems['title']}  *********");
                            $rent = Yii::app()->curl->get($tableItems['tableurl']);
                            if($rent['http_code']!=200){
                                $this->to_log('提示', "  *********  报表url:{$tableItems['title']}无法访问  *********");
                                $reportTable[$tableProject]['table'][$res['report_name']]['access'] = 0;
                            } else {
                                $reportTable[$tableProject]['table'][$res['report_name']]['access'] = 1;
                            }
                        }
                    }
                }
                $data['data'][$parameter['appToken']]['data'] = $reportTable;
                $data['data'][$parameter['appToken']]['name'] = $items['app_name'];
            }
        }

        if (!empty($data)) {
            $status = $this->objMail->sendOpenApi($data);
            if (!$status) {
                $log = '失败';
            } else {
                $log = '成功';
            }
            $this->to_log('提示', "  *********  邮件发送:{$log}  *********");
        }
    }

    function getTokenList() {
        $sql = 'select * from  t_app_token ';
        $whereStr = 'where 1 = 1';
        $whereStr .= " and status = 1";
        $sql  = $sql . $whereStr;
        $db   = Yii::app()->sdb_metric_meta;
        $data = $db->createCommand($sql)->queryAll();
        return $data;
    }

    function getCheckTableFlag($reportId) {
        $sql = 'select flag from  t_visual_table ';
        $whereStr = 'where 1 = 1';
        $whereStr .= " and id = $reportId";
        $sql  = $sql . $whereStr;
        $db   = Yii::app()->sdb_metric_meta;
        $data = $db->createCommand($sql)->queryAll();

        return $data;
    }

    function getCheckReportFlag($reportName){
        $sql = 'select end_time,schedule_level from  mms_run_log ';
        $whereStr = 'where 1 = 1';
        $whereStr .= " and app_name = '$reportName'";
        $sql  = $sql . $whereStr;
        $db   = Yii::app()->sdb_metric_meta;
        $data = $db->createCommand($sql)->queryRow();

        return $data;
    }

    function getTestUrlStatus($request) {
        $reportId = $request['reportId'];
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
        $url['tablelist'] = $this->getTableList($params, $request);

        //获取图表数据
        $url['chart'] = $this->getCharList($params, $request);

        return $url;
    }


    function getTableList($params, $request) {
        $reportId = $request['reportId'];
        $appName = $request['appName'];
        $appToken = $request['appToken'];
        $tableList = array();
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

        return $chart;
    }

}
