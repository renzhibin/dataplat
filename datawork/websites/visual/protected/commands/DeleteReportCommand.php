<?php
//30天无人下线轮询脚本
define('DEVELOP_API','http://developer.meiliworks.com');

class DeleteReportCommand extends Command{
    function __construct(){
        $this->objReport = new ReportManager();
        $this->objComm = new CommonManager();
        $this->objMenu = new MenuManager();
        $this->objBehavior = new BehaviorManager();
    }

    function main(){
        //获取用户行为表和报表信息
        $reportList = $this->objReport->getReportList();
        //从参数days中取到轮询检查的天数
        $days = $this->args['days'];
        if(!isset($days)){
            $days = 30;
        }
        $userInfoList = $this->objBehavior->getUserInfoInDays($days);
        $reportID_visit = array();

        //获取30天内用户访问过的报表ID
        foreach($userInfoList as $key => $report){
            $JasonParam = $report["param"];
            $reportJason = Json_decode($JasonParam);
            if(is_numeric($reportJason->table_id)){
                $reportID_visit[$reportJason->table_id] = $reportJason->table_id;
            }
        }
        //获取所有报表ID，并检查30天内未被访问的ID并下线
        foreach($reportList as $key => $report){
            $reportID = $report["id"];
            $flag = $report["flag"];
            if(!in_array($reportID,$reportID_visit) and $flag == 1){
                $this->objReport->deleteReport($reportID);
            }

        }
    }
}
