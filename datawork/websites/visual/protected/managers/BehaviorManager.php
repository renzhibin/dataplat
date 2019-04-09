<?php
//处理30天无人下线的逻辑
class BehaviorManager extends Manager
{
    function __construct()
    {
        $this->menuTable = 't_visual_menu';
        $this->behaviorLogTable='t_visual_behavior_log';
        $this->username = Yii::app()->user->username;
         $this->objAuth = new AuthManager();
    }

    function addUserBehaviorToLog($table_id,$menu_id,$action_url,$param_array){
        try{

            $param_json = $this->json_encode_ex($param_array);
            $param_json = stripslashes($param_json);
            $param_json = str_replace('`','"',$param_json);
            $param_json = str_replace('\'','"',$param_json);
            $app_user_name = '';
            if (isset($param_array['app_user_name']) && !empty($param_array['app_user_name'])) {
                $app_user_name= $param_array['app_user_name'];
            }

            $sql = "insert into  $this->behaviorLogTable (`cdate`,`user_name`,`user_action`,`param`) values (:cdate,:username,:action_url,
            :param_id)";
            $username = !empty($app_user_name) ? $app_user_name : Yii::app()->user->username;
            $cdate=date('Y-m-d H:i:s',time());
            if(!isset($username)){
                $username = 'datasystem';
            }
            $parament = array(':username' => $username, ':cdate' => $cdate,':action_url' => $action_url,':param_id' => $param_json);

            $res = Yii::app()->db_metric_meta->createCommand($sql)->execute($parament);
        }catch (Exception $exception){
            Yii::log('Exception when recording user behaviors');
        }
        return $res;
    }

    function getUserInfoInDays($days){
        date_default_timezone_set("PRC");
        $whitelist = "('yaoxiao','zhibinren','manli','xinsongrao','bangzhongpeng')";
        $dateLine = date("Y-m-d",strtotime('-'.$days.'day'));
        $sql = "select  * from  " . $this->behaviorLogTable;
        $whereStr = " where (user_action like '/visual/index%' or user_action like '/report/addreport%' or user_action like '/report/showreport%' or user_action like '/report/onlinereport%')
        and user_name not in ".$whitelist." and date(`cdate`) >= date('".$dateLine."')";
        $sql = $sql . $whereStr;
        $db = Yii::app()->sdb_metric_meta;
        $data = $db->createCommand($sql)->queryAll();
        return $data;
    }
    /**
     * 获取用户最近报表访问记录
     * @param type $value
     * @return type
     */
    function getUserVisit($days,$userName){
        $sql = "select  * from  " . $this->behaviorLogTable;
        $dateLine = date("Y-m-d",strtotime('-'.$days.'day'));
        $whereStr = " where (user_action like '/visual/index%' or user_action like '/report/addreport%' or user_action like '/report/showreport%' or user_action like '/report/onlinereport%')
        and user_name ='{$userName}' and date(`cdate`) >= date('".$dateLine."')";
        $sql = $sql . $whereStr;
        $db = Yii::app()->sdb_metric_meta;
        $data = $db->createCommand($sql)->queryAll();
        return $data;
    }

    function getUserVisitV2($startAt, $endAt, $userName)
    {
        $sql = "SELECT
                    *
                FROM  {$this->behaviorLogTable}
                WHERE cdate >= '{$startAt}' AND cdate < '{$endAt}'
                    AND user_action like '/visual/index/menu_id/%'
                    AND user_name = '{$userName}'";

        $data = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $data;
    }

    function getMonthlyReportId() {
        $sql = 'select id from t_visual_table where params like \'%s:13:"dateview_type";s:1:"3"%\' and flag=1';
        $data = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $data;
    }
            
    function json_encode_ex( $value){
        $str = json_encode( $value);
        $str =  preg_replace_callback(
            "#\\\u([0-9a-f]{4})#i",
            function( $matchs)
            {
                return  iconv('UCS-2BE', 'UTF-8',  pack('H4',  $matchs[1]));
            },
            $str
        );
        return  $str;
    }
     
}