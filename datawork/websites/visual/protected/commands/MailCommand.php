<?php

class MailCommand extends Command
{
    public $objReport;
    public $objMail;

    function __construct()
    {
        $this->objReport = new ReportManager();
        $this->objMail = new TimeMailManager();
        $this->objComm = new CommonManager();
        $this->behavior = new BehaviorManager();
    }

    public function main()
    {
        $cmd = "ps -ef | grep -v grep | grep 'php script/crons.php mail' | wc -l";
        $res = array();
        exec($cmd, $res);
        if ($res[0] > 2) {
            $this->objMail->mail_log('提示', "  进程已存在，正常退出");
            exit();
        }
        $this->objMail->mail_log('提示', "  *********  脚本已启动  *********");

        set_time_limit(0);
        $result = $this->objMail->getMailInfo();
        $str = date("Y-m-d H:i");//本日 时:分
        $strH = date("H:00");//本日 时:分
        $dayStr = date("Y-m-d");//本日(将在之后加入时:分)
        if (empty($result)) {
            //为空直接跳出
            return;
        }
        foreach ($result as $key => $val) {
            $send_date = date('Y-m-d');
            $start_datetime = date('Y-m-d H:i:s');
            $sedHourM = date("Y-m-d H:i", strtotime($val['send_time']));
            $sedH = date("H:00", strtotime($val['send_time']));
            try {
                ###################### try ######################

                # 判断执行类型 0 天 1 小时
                switch ($val['run_type']) {
                    case 0:
                    default :
                        $runDate = date("Y-m-d", strtotime("-1 day"));
                        break;
                    case 1:
                        $hours = date('H');
                        $minute = explode(':', $val['time']);
                        $minute = $minute[1];
                        $val['time'] = $hours . ':' . $minute;
                        $runDate = date("Y-m-d H:i", strtotime("-1 hours"));
                        break;
                }

                # 检测邮件是否暂停
                if ($val['alive'] != 1) {
                    $this->objMail->mail_log('提示', "  邮件[{$val['title']}]已暂停例行发送");
                    continue;
                }

                # 开始结束时间判断
                if ($val['run_type'] == 1 and $val['begin_at'] > $strH ) {
                    if ($strH != $val['end_at']) {
                        $this->objMail->mail_log('提示', "  邮件[{$val['title']}]未到开始/结束时间");
                        continue;
                    }
                }
                
                # 检测时间是否达到发送邮件的设置，未到则跳过
                $sendMailDate = $dayStr . ' ' . $val['time'];
                if ($str < $sendMailDate) {
                    $this->objMail->mail_log('提示', "  邮件[{$val['title']}]未到发送时间");
                    continue;
                }

                # 检查小时邮件是否发送过
                if ($sedHourM < $str && $sedH == $strH && $val['run_type'] == 1) {
                    $this->objMail->mail_log('提示', "  邮件[{$val['title']}]已发送过");
                    continue;
                }

                //检测数据是否跑完
                list($dataStatus, $relmsg) = $this->objMail->checkReportDataMain($val['report_id'], $runDate);
                if ($dataStatus) {
                    //跑完了,准备发送邮件
                    if ($this->objReport->hasChart($val['report_id'])) {
                        //有图情况,无论图片是否存在,都重新抓取
                        $out = $this->objMail->phantomjs_get($val['report_id']);
                        if ($out === 'true') {
                            $content = "发送带图片的邮件   " . $val['title'];
                            $status = 'success';
                        } else {
                            $content = "图片抓取错误邮件未发送   " . $val['title'];
                            $status = 'error';
                        }
                    } else {
                        //无图情况,直接发送邮件
                        $content = "发送不带图片的邮件   " . $val['title'];
                        $status = 'success';
                    }
                    if ($status == 'success') {
                        $status = $this->objMail->send($val);
                        $end_datetime = date('Y-m-d H:i:s');
                        $this->objComm->insertMailLog([
                            'mail_id'     => $val['mail_id'],
                            'send_date'   => $send_date,
                            'start_at'    => $start_datetime,
                            'end_at'      => $end_datetime,
                            'send_status' => 1,
                            'send_type'   => 1,
                        ]);
                        $userAction = '/report/showreport/' . $val['report_id'];
                        $param = ['table_id' => $val['report_id']];
                        $this->behavior->addUserBehaviorToLog('', '', $userAction, $param);
                    }
                    $this->objMail->mail_log($status, $content);
                } else {
                    $minTimeStr = $dayStr . ' ' . $val['time'];
                    $strSecond = strtotime($str); // 当前时间
                    $minTimeStrSecond = strtotime($minTimeStr); // 推送时间
                    # 当前时间大于等于推送时间邮件数据未生成
                    # 当前时间 与 推送时间 时间差在120s则认为不是第一次检测  不是第一次检测则20分钟推送一封邮件
                    if ($strSecond >= $minTimeStrSecond && (($strSecond - $minTimeStrSecond) < 120 || $strSecond % 1200 == 0)) {
                        if (is_array($relmsg) && !empty($relmsg)) {
                            $relayInfo = '';
                            foreach ($relmsg as $k => $v) {
                                foreach ($v as $k1 => $k2) {
                                    $errorHql = implode(' 和 ', $k2);
                                    $relayInfo .= "日期：{$k1}，HQL：{$errorHql}；<br>";
                                }
                            }
                            $this->objMail->sendCustomMail($val, '邮件超时报警', '邮件存在未执行完成项目：<br>' . $relayInfo);
                            $this->objMail->mail_log('提示', "  邮件存在未执行完成项目：{$relayInfo}");
                        } else {
                            $this->objMail->sendCustomMail($val, '未知异常', '未知异常');
                        }
                    } else {
                        $this->objMail->mail_log('提示', "  邮件[{$val['title']}]报警邮件不符合条件已被忽略");
                    }
                }

                ###################### try ######################
            } catch (Exception $exception) {
                $this->objMail->mail_log('提示', "  邮件[{$val['title']}]执行出现异常");
                $this->objMail->sendCustomMail($val, '邮件执行异常', $exception->getMessage() . '<br>' . $exception->getTraceAsString());

                $end_datetime = date('Y-m-d H:i:s');
                $this->objComm->insertMailLog([
                    'mail_id'     => $val['mail_id'],
                    'send_date'   => $send_date,
                    'start_at'    => $start_datetime,
                    'end_at'      => $end_datetime,
                    'send_status' => 2,
                    'send_type'   => 1,
                ]);
            }
            ###################### catch ######################
        }

        $this->objMail->mail_log('提示', "  *********  脚本已结束  *********");
        echo PHP_EOL;
        echo PHP_EOL;
    }
}
