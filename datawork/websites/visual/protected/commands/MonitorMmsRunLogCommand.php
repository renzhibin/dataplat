<?php

class MonitorMmsRunLogCommand extends Command
{
    # 检测当日例行 mms_run_log 是否还有没跑完或者持续检测的情况
    public function main()
    {
        $this->echoInfo('', '脚本开始执行');
        # 需要检测 mms_run_log status 状态
        $status = "1, 9";
        $day    = date('Y-m-d', time() - 86400);
        $to     = 'yangzongqiang@qudian.com;yangyulong@qudian.com;';
        $title  = "【监控】例行任务异常概况报告 {$day}";

        $sql = "SELECT
                    *
                FROM `mms_run_log`
                WHERE
                    `creater` is null AND
                    `stat_date` = '{$day}' AND
                    `status` IN ({$status})
               ";

        $data = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();

        if (!empty($data)) {
            $html = $this->makeMailInfo($data, $title);

            (new CommonManager())->sendMail($to, $html, $title);

            $this->echoInfo('', '数据存在发送邮件');
        } else {
            $this->echoInfo('', '数据不存在略过');
        }

        $this->echoInfo('', '脚本结束执行');
    }

    private function echoInfo($pre, $info)
    {
        echo $pre . date("Y-m-d H:i:s") . ' ' . $info . PHP_EOL;
    }

    private function makeMailInfo($data, $title)
    {
        $statusName = [
            1  => '阻塞',
            2  => '就绪',
            3  => '运行',
            4  => 'hive结束',
            5  => '成功',
            6  => '失败',
            7  => '警告',
            8  => '超时',
            9  => '检查',
            11 => '杀死',
        ];

        $scheduleName = [
            'day'  => '天',
            'hour' => '小时',
        ];

        $htmlHead     = <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
</head>
<body style='white-space:nowrap;font-family: "Microsoft YaHei",Arial,Helvetica,"宋体",sans-serif; margin: 0; padding: 0; -webkit-font-smoothing: antialiased;
        height: 100%; -webkit-text-size-adjust: none;  width: 100% !important;'>
EOT;
        $tableHead    = <<<EOT
<table style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; width: 100%;"
       cellspacing="0" cellpadding="0">
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
        <td class="alert alert-warning"
            style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 20px; font-size: 14px; color: #fff; font-weight: bold; text-align: left; border-radius: 3px 3px 0 0; background: #ff9f00;">
            <h2 class="title"
                style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; padding: 0; font-weight: 200; line-height: 1.2em; font-size: 28px; margin: 0; color: #fff; text-align: left;">
                {$title}
            </h2>
        </td>
    </tr>
EOT;
        $tableContent = <<<EOT
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
        <td class="content-wrap"
            style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
            <h4 style="font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; ">
                <strong>>>&nbsp;&nbsp;异常列表 </strong></h4>
        </td>
    </tr>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
        <td class="content-wrap"
            style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
            <table cellpadding="0" cellspacing="0"
                   style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; margin: 10px 0; border-spacing: 0; border-collapse: collapse; border: 1px solid #f4f4f4; color: #333; width: 100%;">
                <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
                    <th class="txt-l"
                        style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
                        ID
                    </th>
                    <th class="txt-l"
                        style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
                        项目名称
                    </th>
                    <th class="txt-l"
                        style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
                        例行时间
                    </th>
                    <th class="txt-l"
                        style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
                        状态
                    </th>
                    <th class="txt-l"
                        style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
                        创建时间
                    </th>
                    <th class="txt-l"
                        style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
                        模块
                    </th>
                    <th class="txt-l"
                        style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
                        例行计划
                    </th>
                    <th class="txt-l"
                        style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;">
                        创建人
                    </th>
                </tr>
EOT;
        foreach ($data as $index => $row) {
            $id             = $row['id'];
            $app_name       = $row['app_name'];
            $stat_date      = $row['stat_date'];
            $status         = $row['status'];
            $create_time    = $row['create_time'];
            $run_module     = $row['run_module'];
            $schedule_level = $row['schedule_level'];
            $submitter      = $row['submitter'];

            $status         = isset($statusName[$status]) ? $statusName[$status] : $status;
            $schedule_level = isset($scheduleName[$schedule_level]) ? $scheduleName[$schedule_level] : $schedule_level;
            $submitter      = strstr($submitter, '@', true);

            if ($index % 2 == 0) {
                $row = <<<EOT
                <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;">
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $id
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $app_name
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $stat_date
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $status
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $create_time
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $run_module
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $schedule_level
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $submitter
                     </td>
                </tr>
EOT;
            } else {
                $row = <<<EOT
                <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $id
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                          $app_name
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $stat_date
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $status
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $create_time
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $run_module
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $schedule_level
                     </td>
                     <td class="txt-l"
                         style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left;">
                         $submitter
                     </td>
                </tr>
EOT;
            }

            $tableContent .= $row;
        }

        $tableContent .= <<<EOF
            </table>
        </td>
    </tr>
EOF;

        $tableFoot = <<<EOT
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
        <td class="content-wrap"
            style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 20px 0px;">
            <p style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px; font-weight: normal; margin-bottom: 10px;">
                感谢您的订阅！任何问题，欢迎联系 数据团队 
                <a href="mailto:di@qudian.com" target="_blank">
                    di@qudian.com
                </a>
            </p>
        </td>
    </tr>
    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
        <td class="alert alert-warning alert-footer"
            style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 20px; font-size: 14px; color: #fff; font-weight: bold; text-align: left; border-radius: 0 0 3px 3px; background: #ff9f00; line-height: 33px;">
            <p class="footer"
               style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; padding: 0; font-weight: normal; margin-bottom: 10px; margin: 0; text-align: left; color: #fff; font-size: 16px; line-height: 33px;">
                <span>祝您工作愉快</span>
            </p>
        </td>
    </tr>
</table>
EOT;
        $htmlFoot  = <<<EOT
</body>
</html>
EOT;
        return $htmlHead . $tableHead . $tableContent . $tableFoot . $htmlFoot;
    }
}