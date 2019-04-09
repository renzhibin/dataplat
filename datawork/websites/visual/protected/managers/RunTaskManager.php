<?php
/**
 * Created by PhpStorm.
 * User: yangyulog
 * Date: 18/8/3
 * Time: 15:00
 */

class RunTaskManager extends Manager {

    const HIVE_TYPE = 3;
    const PYTHON_TYPE = 2;
    const HIVE_CRON_LEVEL = 'day';
    const JOB_NAME_PREFIX = 'dt_run_task_tool_';
    const HDFS_TMP_DIR = 'hdfs:///tmp/';

    const URL = 'http://scheduler.qudian.com';
    const CALL_BACK_URL = 'http://dt.qufenqi.com/RunTask/runtaskcallback?phantomjs=1';
    const APP_KEY = 'cac12f01e96b15f17a29c35aa30eee12';

    private $sids = [];

    public function __construct() {
        $this->objComm = new CommonManager();
    }

    public function saveRunTask2Di($data) {
        $id = $this->getDemandId($data);
        $start = new DateTime($data['start_time']);
        $end = new DateTime($data['end_time']);
        $end = $end->modify('+1 day');
        try {
            $date = $data['end_time'];
            foreach (new DatePeriod($start, new DateInterval('P1D'), $end) as $d) {
                $date = $d->format('Y-m-d');
                $result = $this->saveHql2Di($id, $data['start_time'], $date, $data['demand_hql']);
                if ($result['status'] != 0) {
                    $res = array(
                        'code' => 400,
                        'msg' => $result['msg']
                    );
                    return $res;
                }
                $this->sids[$date] = $result['sid'];
            }
            $result = $this->savePython2Di($id, $date);
            $this->sids['python'] = $result['sid'];
            if ($result['status'] != 0) {
                $res = array(
                    'code' => 400,
                    'msg' => $result['msg']
                );
                return $res;
            }
        } catch (\Exception $e) {
            $res = array(
                'code' => 400,
                'msg'  => $e->getMessage()
            );
            return $res;
        }
        $this->sendLogEmail($this->sids, $data['demand_name']);
        $res = array(
            'code' => 200,
            'msg'  => '任务保存成功!'
        );
        return $res;
    }

    public function getDemandId($data) {
        $obj = Yii::app()->db_metric_meta->createCommand()
            ->select('id')
            ->from('run_task_list')
            ->where('demand_name=:demand_name and start_time=:start_time and end_time=:end_time order by id desc limit 1', ['demand_name' => $data['demand_name'],'start_time' => $data['start_time'], 'end_time' => $data['end_time']]);
        $res = $obj->queryAll();
        return $res[0]['id'];
    }

    public function getDemandById($id) {
        $obj = Yii::app()->db_metric_meta->createCommand()
            ->select('*')
            ->from('run_task_list')
            ->where('id=:id', ['id' => $id]);
        $res = $obj->queryAll();
        return $res[0];
    }

    public function handlDemandData($data)
    {
        if ($data['end_time'] < $data['start_time']) {
            $res = array(
                'code' => 400,
                'msg'  => '开始时间必需大于结束时间！'
            );
            return $res;
        }
        $data['demand_user']  = Yii::app()->user->username;
        $sql = "insert into run_task_list (
                    `demand_name`,
                    `demand_user`,
                    `demand_hql`,
                    `start_time`,
                    `end_time`
                ) values (
                    :demand_name,
                    :demand_user,
                    :demand_hql,
                    :start_time,
                    :end_time
                )";
        $res = Yii::app()->db_metric_meta->createCommand($sql)->execute($data);
        if ($res == false) {
            $res = array(
                'code' => 400,
                'msg'  => '任务保存失败，请重新保存!'
            );
            return $res;
        }else{
            $res = array(
                'code' => 200,
                'msg'  => '任务保存成功!'
            );
            return $res;
        }
    }

    public function sendSuccessEmail($id, $user, $demandName) {
        $html = $user . '，您好:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您的跑数任务已完成，HDFS地址为：' . self::HDFS_TMP_DIR .  $this->getJobName($id);
        $title = '【批量跑数工具】您的需求' . $demandName . '的跑数任务已完成';
        $this->objComm->sendMail($user, $html, $title);
    }

    private function sendLogEmail($ids, $demandName) {
        $user = Yii::app()->user->username;
        $html = $user . '，您好:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您的跑数任务已提交,任务日志地址如下：<br/><br/>';
        foreach ($ids as $date => $id) {
            $html .= $date . ': ' . self::URL . "/job/render_log?id={$id}&unq_job_name=" . self::APP_KEY . "<br/>";
        }
        $title = '【批量跑数工具】您的需求' . $demandName . '的查看日志地址';
        $this->objComm->sendMail($user, $html, $title);
    }

    private function savePython2Di($id, $date) {
        $callBack = self::CALL_BACK_URL;
        $content = <<<EOF
#coding:utf-8
import requests
par = {'id':'{$id}'}
r=requests.post('{$callBack}', params=par)
print r.status_code
EOF;
        $api_params = [];
        $api_params['job_type']     = self::PYTHON_TYPE;
        $api_params['job_name']     = $this->getJobName($id) . '_python';
        $api_params['app_key']      = self::APP_KEY;
        $api_params['tag_depend']   = json_encode(['tags'=>[$this->getJobName($id) . '_' . $date]]);
        $api_params['tag_store']    = json_encode([]);
        $api_params['creater']      = Yii::app()->user->username;
        $api_params['cron_level']   = 'day';
        $api_params['job_time']     = date('Y-m-d', time());
        $api_params['job_content']  = $content;
        return $this->postCurl($api_params);
    }

    private function saveHql2Di($id, $startDate, $date, $hql) {
        $api_params = [];
        $api_params['job_type'] = self::HIVE_TYPE;
        $api_params['job_name'] = $this->getJobName($id);
        $api_params['app_key'] = self::APP_KEY;
        if ($startDate == $date) {
            $api_params['tag_depend'] = json_encode([]);
        } else {
            $lastDate = date('Y-m-d', strtotime($date . ' -1 day'));
            $api_params['tag_depend'] = json_encode(['tags' => [$this->getJobName($id) . '_' . $lastDate]]);
        }
        $api_params['tag_store'] = json_encode(['tags' => [$this->getJobName($id) . '_' . $date]]);
        $api_params['creater'] = Yii::app()->user->username;
        $api_params['cron_level'] = 'day';
        $api_params['job_time'] = date('Y-m-d', time());
        $api_params['job_content'] = $this->getHql($id, $date, $hql);
        return $this->postCurl($api_params);
    }

    private function getHql($id, $date, $hql) {
        $hql = str_ireplace('#dt', $date, $hql);
        $hql = "insert overwrite directory '". self::HDFS_TMP_DIR .  $this->getJobName($id) . '/' . $date . "' row format delimited fields terminated by ',' " . $hql;
        return $hql;
    }

    private function getJobName($id) {
        return self::JOB_NAME_PREFIX . $id;
    }

    //post接口调用
    private function postCurl($content)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::URL . '/job/run_job_ext');
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);

        $output = curl_exec($ch);
        $result = json_decode($output, true);
        curl_close($ch);

        return $result;
    }
}