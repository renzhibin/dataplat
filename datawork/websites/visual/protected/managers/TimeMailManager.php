<?php

class TimeMailManager extends Manager
{
    public $timeTable = 't_visual_mail';
    public $user = 't_visual_user';
    public $mms_log = 'mms_run_log';

    function __construct()
    {
        $this->objReport = new ReportManager();
        $this->objVisual = new VisualManager();
        $this->objFackcube = new FackcubeManager();
        $this->objComm = new CommonManager();
        $path = Yii::app()->basePath;
        require $path . "/controllers/VisualController.php";
        $this->visualCon = new VisualController();

    }

    function getMailList()
    {
        $db = Yii::app()->sdb_metric_meta;
        $mailSql = "select  * from  $this->timeTable ";
        $data = $db->createCommand($mailSql)->queryAll();

        return $data;
    }

    //保存配置
    function saveMail($insertData)
    {

        $insertData = array(
            "'" . $insertData['report_id'] . "'",
            "'" . Yii::app()->user->username . "'",
            "'" . $insertData['time'] . "'",
            "'" . $insertData['title'] . "'",
            0,
            "'" . $insertData['addressee'] . "'",
            "'" . $insertData['warning_address'] . "'",
            "'" . $insertData['commentdatants'] . "'",
            "'" . $insertData['type'] . "'",
            "'" . $insertData['run_type'] . "'",
            "'" . $insertData['begin_at'] . "'",
            "'" . $insertData['end_at'] . "'",
        );
        $sql = 'insert ' . $this->timeTable . '(report_id,author,time,title,status,addressee,warning_address,comments,type,run_type,begin_at,end_at) values(' . implode(',', $insertData) . ' )';
        $res = Yii::app()->db_metric_meta->createCommand($sql)->execute();

        return $res;
    }

    //更新配置
    function updateMail($updateData)
    {
        $sqlParams = array();
        foreach ($updateData as $key => $value) {
            if ($key == 'mail_id') {
                continue;
            }
            $sqlParams[] = $key . "='" . $value . "'";
        }
        $sql = 'update ' . $this->timeTable . ' set ' . implode(',', $sqlParams) . ' where mail_id = ' . $updateData['mail_id'];
        $res = Yii::app()->db_metric_meta->createCommand($sql)->execute();

        return $res;
    }

    //删除配置
    function delMail($id)
    {
        $sql = 'delete from ' . $this->timeTable . ' where mail_id =' . $id;
        $res = Yii::app()->db_metric_meta->createCommand($sql)->execute();

        return $res;
    }

    //获取用户权限
    function getGroup($user_name)
    {
        $db = Yii::app()->sdb_metric_meta;
        $usersql = "select  * from  $this->user  where  user_name ='" . $user_name . "'";
        $data = $db->createCommand($usersql)->queryAll();

        return $data;
    }


    //生成报表时间
    function getTimeOffset($offset, $type = 2, $interval = 0)
    {
        $str = $offset + $interval;
        switch ($type) {
            case 1:
                $timeStr = date("Y-m-d H:00", strtotime("- " . $str . " hours"));//修改为小时级别
                break;
            case 2:
                $timeStr = date("Y-m-d", strtotime("- " . $str . " day"));
                break;
            case 3:
                $timeStr = date("Y-m", strtotime("- " . $str . " day"));
                break;
            default:
                $timeStr = date("Y-m-d", strtotime("-1 day"));
        }

        return $timeStr;
    }

    //验证报表数据是否跑完
    function checkReportDataMain($id, $date)
    {
        $reprotInfo = $this->objReport->getReoport($id);
        //$metricAll = array();
        $relmsg = array();
        $offset = $reprotInfo['params']['timereport']['offset'];
        $type = $reprotInfo['params']['timereport']['dateview_type'];
        $interval = $reprotInfo['params']['timereport']['interval'];
        $date = $this->getTimeOffset($offset, $type, $interval);
        $edate = $this->getTimeOffset($offset, $type, 0);
        if (!empty($reprotInfo['params']['chart'])) {
            foreach ($reprotInfo['params']['chart'] as $key => $val) {
                $config = $val;
                $config['date'] = $date;
                $config['edate'] = $edate;
                $re = $this->objFackcube->getData($config, false, false);
                if (!empty($re['relyMsg'])) {
                    $relmsg[] = $re['relyMsg'];
                }
            }
        }
        //转换新的的表格数据格式
        if (!isset($reprotInfo['params']['tablelist']) && isset($reprotInfo['params']['table'])) {
            $reprotInfo['params']['tablelist'] = array();
            array_push($reprotInfo['params']['tablelist'], $reprotInfo['params']['table']);
            $reprotInfo['params']['tablelist'][0]['type'] = $reprotInfo['type'];
            $reprotInfo['params']['tablelist'][0]['title'] = $reprotInfo['cn_name'];
            unset($reprotInfo['params']['table']);
        }
        if (!empty($reprotInfo['params']['tablelist'])) {
            $tablelist = $reprotInfo['params']['tablelist'];
            foreach ($tablelist as $key => $val) {
                $config = $val;
                $config['date'] = $date;
                $config['edate'] = $edate;
                $re = $this->objFackcube->getData($config, false, false);
                if (!empty($re['relyMsg'])) {
                    $relmsg[] = $re['relyMsg'];
                }
            }
        }
        if (!empty($relmsg)) {
            return [false, $relmsg];
        } else {
            return [true, $relmsg];
        }
    }

    function checkReportData($id, $date)
    {
        list($status, $relmsg) = $this->checkReportDataMain($id, $date);

        return $status;
    }

    function getMetricAll($metricInfo, $metricAll)
    {

        if (!empty($metricInfo)) {
            foreach ($metricInfo as $item => $mVal) {
                $mArr = explode(".", $mVal);
                array_pop($mArr);
                if (!in_array(implode('.', $mArr), $metricAll)) {
                    array_push($metricAll, implode('.', $mArr));
                }
            }
        }

        return $metricAll;
    }

    //获取runlog任务日志信息
    function checkRunlogSql($project, $metricAll, $date)
    {
        $checkSql = " select  app_name,start_time,run_module
         from  $this->mms_log  where
         app_name='" . $project . "'

         and  status in(5,7)  and  run_module in(" . implode(",", $metricAll) . ")
         and stat_date = '{$date}'  group by run_module";
        $db = Yii::app()->sdb_metric_meta;
        $data = $db->createCommand($checkSql)->queryAll();

        return $data;

    }


    function getFie($fileName)
    {
        if (file_exists($fileName)) {
            return file_get_contents($fileName);
        } else {
            return 2;
        }
    }

    function createChartHtml($imgArr)
    {
        $basePath = dirname(__FILE__) . "/../runtime/mailchart/";
        $html = "";
        $html .= "<div class='row' style='margin:0px 0px 10px 0px'>";
        foreach ($imgArr as $key => $value) {
            $html .= "<div class='chartlist";
            if (count($imgArr) == 1) {
                $html .= "  chartOne";
            } else if (count($imgArr) % 2 == 0) {
                $html .= " chartbetwen";
            } else {
                if ($key == count($imgArr) - 1) {
                    $html .= " chartOne";
                } else {
                    $html .= " chartbetwen";
                }
            }
            $html .= "'>";
            $fileName = $basePath . $value['img'];
            $str = $this->getFie($fileName);
            $str = base64_encode($str);
            //echo $str."<br>";
            $html .= "<img src='data:image/png;base64," . $str . "' />";
            $html .= "</div>";
        }
        $html .= "<div style='clear:both'></div>";
        $html .= "</div>";

        return $html;
    }

    function createPNGHtml($report_id)
    {
        $basePath = dirname(__FILE__) . "/../runtime/mailchart/";
        $html = "";
//        $html.= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
//        $html.= "<td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
//        $html.= "<h4 style=\"font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; \">";
//        $html.= "<strong>>> 图表展示</strong></h4>";
//        $html.= "</td></tr>";
        $html .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
        $html .= "<td class=\"content-wrap txt-c\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 20px 32px 0px 32px;; text-align: center;\">";
        $fileName = $basePath . $report_id . "_" . date("Y-m-d") . ".png";

        $str = $this->getFie($fileName);
        if ($str == '2') {
            //$html .="警告：邮件图片未生成";
            //在这里写图片生成策略
        } else {
            $str = base64_encode($str);
            $html .= "<img src=\"data:image/png;base64," . $str . "\" style=\"width:960px;height: auto;margin:0 auto;\"/>";
        }
        $html .= "</td>";
        $html .= "</tr>";

        return $html;
    }

    function createChart($chartData)
    {
        include "libchart.php";
        $imgStr = array();
        foreach ($chartData as $key => $value) {

            if (count($chartData) == 1) {
                $width = 1000;
            } else if (count($chartData) % 2 == 0) {
                $width = 500;
            } else {
                if ($key == count($chartData) - 1) {
                    $width = 1000;
                } else {
                    $width = 500;
                }
            }
            switch ($value['chart']['type']) {
                case 'pie':
                    $chart = new PieChart($width, 300);
                    break;
                case 'column':
                    $chart = new VerticalBarChart($width, 400);
                    break;
                case 'spline':
                    $chart = new LineChart($width, 400);
                    break;
                default:
                    $chart = new PieChart($width, 300);
                    break;
            }


            if ($value['chart']['type'] == 'spline') {
                $dataSet = new XYSeriesDataSet();
                foreach ($value['series'] as $item => $list) {
                    $series = new XYDataSet();
                    foreach ($list['data'] as $it => $iv) {
                        $dt = date("Y-m-d", $iv[0] / 1000);
                        $series->addPoint(new Point($dt, $iv[1]));
                    }
                    $dataSet->addSerie($list['name'], $series);
                }
            } else {
                $dataSet = new XYDataSet();
                foreach ($value['series'][0]['data'] as $item => $lit) {
                    $dataSet->addPoint(new Point($lit[0], $lit[1]));
                }
            }
            //$chart->getPlot()->setGraphPadding(new Padding(5, 5, 5, 5));
            $chart->setDataSet($dataSet);
            $chart->setTitle($value['title']['text']);
            $chart->render($value['chart']['renderTo'] . ".png");
            $one = array();
            $one['img'] = $value['chart']['renderTo'] . ".png";
            $imgStr[] = $one;
        }

        return $imgStr;
    }

    function createTableHtml($data, $headerArr, $iscont = false)
    {
        $html = '';
        $html .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
        $html .= "<td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0px 32px 0px 32px;\">";
        $html .= "<table cellpadding=\"0\" cellspacing=\"0\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; margin: 10px 0; border-spacing: 0; border-collapse: collapse; border: 1px solid #f4f4f4; color: #333; width: 100%;\">";
        $html .= "<tbody>";
        $html .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;\">";
        foreach ($headerArr['name'] as $name) {
            $html .= "<th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;\">
                     " . trim($name) . "</th>";
        }
        $html .= "</tr>";

        if (sizeof($data) > 0) {
            foreach ($data as $key => $val) {
                if ($key % 2 == 0) {
                    $html .= '<tr style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 0; font-size: 12px;">';
                } else {
                    $html .= '<tr style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 0; font-size: 12px;background-color:rgba(82, 62, 62, 0.03);">';
                }
                //数据字段
                foreach ($headerArr['key'] as $k => $v) {
                    //if(isset($val[$v]) && !empty($val[$v]) ){
                    //$realdata = preg_replace('/(?<=[0-9])(?=(?:[0-9]{3})+(?![0-9]))/', ',', $val[$v]);
                    if ($v != 'date' & $v != 'cdate') {
                        if (is_array($val[$v])) {
                            $realdata = $val[$v]['commentdata'];
                            //$realdata = preg_replace('/(?<=[0-9])(?=(?:[0-9]{3})+(?![0-9]))/', ',', $realdata);
                        } else {
                            //$realdata = preg_replace('/(?<=[0-9])(?=(?:[0-9]{3})+(?![0-9]))/', ',', $val[$v]);
                            //含有date id time的字段不能被 千分隔
                            $realdata = $val[$v];

                            // if (!preg_match("/(date|time|id)/i", $v)) {
                            // 判断标准 首先是数字而且有千分位标志且无百分位标志
                            if (is_numeric($realdata) && isset($headerArr['info'][$v]['thousand']) && $headerArr['info'][$v]['thousand'] == 0
                                && isset($headerArr['info'][$v]['percent']) && $headerArr['info'][$v]['percent'] != 1
                            ) {
                                $realdata = preg_replace('/(?<=[0-9])(?=(?:[0-9]{3})+(?![0-9]))/', ',', $val[$v]);
                            }
                        }
                    } else {
                        $realdata = $val[$v];
                    }
                    if ($iscont) {
                        // if (strpos($v, 'percent') !== false) {
                        $realdata = trim($realdata);
                        // 判断标准 首先是数字而且有百分位标志
                        if ($headerArr['reshape'] && $this->toThousands($realdata)) {
                            $realdata = preg_replace('/(?<=[0-9])(?=(?:[0-9]{3})+(?![0-9]))/', ',', $realdata);
                        }
                        if (is_numeric($realdata) && isset($headerArr['info'][$v]['percent']) && $headerArr['info'][$v]['percent'] == 1) {
                            $html .= '<td class="txt-l"
                                        style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;">' . number_format($realdata, 2) . '%
                                    </td>';
                            /*if ($val[$v] > 0) {

                                $html .= '<td class="txt-l"
                                        style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;">' . trim($realdata) . '%
                                    </td>';
                            } elseif ($val[$v] == 0) {
                                $html .= '<td class="txt-l"
                                        style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;">' . $val[$v] . '
                                    </td>';
                            } else {

                                $html .= '<td class="txt-l"
                                        style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;">' . trim($realdata) . '%
                                    </td>';
                            }*/
                        } else {

                            $html .= '<td class="txt-l"
                                        style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;">' . trim($realdata) . '
                                </td>';
                        }
                    } else {
                        $html .= '<td class="txt-l"
                                        style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;">' . trim($realdata) . '
                            </td>';
                    }
                    //}else{
                    //  $html .='<td>0</td>';
                    //}
                }
            }
        } else {
            $html_col = '<tr><td style="padding: 6px" colspan="' . sizeof($headerArr['name']) . '">无数据</td></tr>';
            $html .= $html_col;
        }
        $html .= '</tbody></table></td></tr>';

        return $html;
    }

    function toThousands($num)
    {
        if (is_numeric($num)) {
            return TRUE;
        }
        $count = explode("%", $num);
        if (count($count) > 1) {
            return FALSE;
        }

        return false;

    }

    function tipHtml($str)
    {
        $url = $this->getUrlLink($str);
        $html = "";
        $html .= <<<HTML
        <div class="row">
            <span style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; font-size: 12px;">注：上表数据行数过多，只显示前500行，更多数据请至
            <a style="font-style:normal;font-weight: normal" href="$url">Data报表</a>查看
            </span>
        </div>
HTML;

        return $html;
    }

    function getUrlLink($str)
    {
        $arr = $this->menu->getMenuByReoprt($str);
        if ($str == 4694 or $str == 4977) {
            return 'http://dt.qufenqi.com/visual/index/' . $str;
        }
        if (!empty($arr)) {
            $url = "http://dt.qufenqi.com/visual/index/menu_id/" . $arr[0]['id'] . "/id/" . $str;
        } else {
            if ($str == 4694 or $str == 4977) {
                $url = 'http://dt.qufenqi.com/visual/index/' . $str;
            } else {
                $url = "http://dt.qufenqi.com/report/showreport/{$str}";
            }
        }

        return $url;
    }

    function warning($val, $istest = false)
    {
        $url = $this->getUrlLink($val['report_id']);
        $metric = Yii::app()->sdb_metric_meta;
        $reportInfo = "select  * from t_visual_table where  id =" . $val['report_id'];
        $tableInfo = $metric->createCommand($reportInfo)->queryAll();
        $html = "";
        $html .= <<<HTML
        <div class="row">
            <p style="color:#ff0000;line-height:18px">
                报表名称：{$tableInfo[0]['cn_name']}({$val['report_id']})
            </p>
            <p style="color:#ff0000;line-height:18px">
                订阅邮件名称：{$val['title']}
            </p>
            <p style="color:#ff0000;line-height:18px">
                报表链接：<a href="$url">$url</a>
            </p>
            <p style="color:#ff0000;line-height:18px">
                原因：数据在邮件设定的最迟发送时间内没有发送,请及时处理。
            </p>
        </div>
HTML;

        $allConfig = unserialize($tableInfo[0]['params']);
        $allConfig = $this->setTime($allConfig);
        //处理tablelist  table的数据
        //转换新的的表格数据格式
        if (!isset($allConfig['tablelist']) && isset($allConfig['table'])) {
            $allConfig['tablelist'] = array();
            array_push($allConfig['tablelist'], $allConfig['table']);
            $allConfig['tablelist'][0]['type'] = $allConfig['type'];
            $allConfig['tablelist'][0]['title'] = $allConfig['cn_name'];
            $date = $allConfig['tablelist'][0]['date'];
            $edate = $allConfig['tablelist'][0]['edate'];
            unset($allConfig['table']);
        } else {
            //如果表格为空 就取图表的时间
            if (!empty($allConfig['tablelist'])) {
                $date = $allConfig['tablelist'][0]['date'];
                $edate = $allConfig['tablelist'][0]['edate'];
            } else {
                $date = $allConfig['chart'][0]['date'];
                $edate = $allConfig['chart'][0]['edate'];
            }

        }

        if ($allConfig['table']['date'] == $allConfig['table']['edate']) {
            $title = "【报警】订阅邮件未发出_" . $val['title'];
        } else {
            $title = "【报警】订阅邮件未发出_" . $val['title'];
        }
        $address = explode(",", $val['warning_address']);
        $address[] = 'yangyulong@qudian.com';
        $this->objComm->sendMail(implode(';', $address), $html, $title);
        if (!$istest) {
            //$setSql = "update  t_visual_mail set status =1  where  mail_id= ".$val['mail_id'];
            $timeStr = date("Y-m-d H:i:s");
            $setSql = "update  t_visual_mail set warning_time ='" . $timeStr . "'  where  mail_id= " . $val['mail_id'];
            $maDb = Yii::app()->db_metric_meta;
            $maDb->createCommand($setSql)->execute();

        }

        return 'success';
    }

    function sendCustomMail($val, $titleMsg, $bodyMsg)
    {
        $url = $this->getUrlLink($val['report_id']);
        $reportInfo = "select  * from t_visual_table where  id =" . $val['report_id'];
        $tableInfo = Yii::app()->sdb_metric_meta->createCommand($reportInfo)->queryAll();

        $address = explode(",", $val['warning_address']);
        $address[] = 'yangyulong@qudian.com';

        $html = <<<HTML
        <div class="row">
            <p style="color:#ff0000;line-height:18px">
                报表名称：{$tableInfo[0]['cn_name']}({$val['report_id']})
            </p>
            <p style="color:#ff0000;line-height:18px">
                订阅邮件名称：{$val['title']}
            </p>
            <p style="color:#ff0000;line-height:18px">
                报表链接：<a href="$url">$url</a>
            </p>
            <p style="color:#ff0000;line-height:18px">
                {$bodyMsg}
            </p>
        </div>
HTML;
        $title = "【报警】{$titleMsg}_{$val['title']}";

        $this->objComm->sendMail(implode(';', $address), $html, $title);
    }


    /*phantomjs邮件图片抓取方法
    report_id  报表id
    path  command所在位置
    */
    function phantomjs_get($report_id)
    {
        $PNGname = $report_id . '_' . date("Y-m-d") . ".png";
        // $file_path=dirname(__FILE__). "/../runtime/mailchart/" . $PNGname;
        // $script = dirname(__FILE__) . "/../script/";
        $rsv_pq = md5('a6f45faf0000934' . floor(time() / 86400));
        // $out = exec($script . 'phantomjs/phantomjs ' . $script . 'downChart.js '."'".PHANTOMJS_SITE.'/report/showreport/' . $report_id . '?phantomjs=1&rsv_pq=' . $rsv_pq ."'". ' '.$file_path);

        $basePath = dirname(dirname(__FILE__));
        $phantomjsPath = $basePath . '/script/phantomjs/';
        // $phantomjsPath = '/home/apple/phantomjs-2.1.1-linux-x86_64/bin/';
        $downChartPath = $basePath . '/script/';
        $file_path = $basePath . '/runtime/mailchart/' . $PNGname;
        $chartURL = "'" . PHANTOMJS_SITE . "/report/showreport/{$report_id}?phantomjs=1&rsv_pq={$rsv_pq}'";
        $command = "{$phantomjsPath}phantomjs {$downChartPath}downChart.js {$chartURL} {$file_path}";
        $out = exec($command);
        sleep(1);//等待1秒

        return $out;
    }

    //邮件log

    function mail_log($status, $content)
    {
        echo '[' . $status . '] ' . date("Y-m-d H:i:s") . $content . "\r\n";
    }

    function footer($str)
    {

        $url = $this->getUrlLink($str);
        $html = "";

        if (strpos($url, 'http://dt.qufenqi.com/visual/index/menu_id/') !== false) {
            $html .= <<<HTML
                    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
                        <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 20px 0px 0px 0px;"> <p style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0px 32px 0px 32px; font-size: 12px; font-weight: normal; margin-bottom: 10px;"> 报表原链接：<a href="$url">$url</a> </p> </td>
                    </tr>
HTML;
        }

        $html .= <<<HTML
                    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;">
                        <td class="content-wrap" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 20px 0px;"> <p style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0px 32px 0px 32px; font-size: 12px; font-weight: normal; margin-bottom: 10px;"> 感谢您的订阅！任何问题，欢迎联系 数据团队 <a href="mailto:di@qudian.com" target="_blank"> di@qud<wbr />ian.com </a> </p> </td> 
                    </tr> 
                    <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;"> 
                        <td class="alert alert-warning alert-footer" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 20px; font-size: 14px; color: #fff; font-weight: bold; text-align: left; border-radius: 0 0 3px 3px; background: #ff9f00; line-height: 33px;"> <p class="footer" style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; padding: 0; font-weight: normal; margin-bottom: 10px; margin: 0; text-align: left; color: #fff; font-size: 16px; line-height: 33px;"> <span>祝您工作愉快</span> </p> </td> 
                    </tr> 
                </tbody>
            </table>
HTML;

        return $html;
    }

    function headHtml($headerTitle)
    {
        $html = "";
        $html .= <<<HTML
                <table style="font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0; width: 100%;" cellspacing="0" cellpadding="0">
                    <tbody>
                        <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
                            <td class="alert alert-warning" style="white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 20px; font-size: 14px; color: #fff; font-weight: bold; text-align: left; border-radius: 3px 3px 0 0; background: #ff9f00;">
                                <h2 class="title" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; padding: 0; font-weight: 200; line-height: 1.2em; font-size: 28px; margin: 0; color: #fff; text-align: left;">
                                    $headerTitle
                                </h2>
                            </td>
                        </tr>
                        
                        <tr style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;">
                            <td class="head-wrap" style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 20px 32px 0px 32px; padding-bottom: 0;">
                                <p style="font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px; font-weight: normal; margin-bottom: 10px; color: #000;">
                                亲爱的用户，您好！根据数据平台统计，您昨日的主要业务数据如下，请您查阅！
                                </p>
                            </td>
                        </tr>
HTML;

        return $html;
    }


    function setTime($allConfig)
    {
        $edate = date("Y-m-d", strtotime('-1 day'));
        //时间视图 1小时 2天 3月级
        //dateview_type  时间粒度 1小时 2天 3月

        $dateview_type = (isset($allConfig["timereport"]["dateview_type"])) ? $allConfig["timereport"]["dateview_type"] : 2;
        $date_typearr = array("day", "hour", "day", "month");
        $date_type = $date_typearr[$dateview_type];
        $offset = $allConfig["timereport"]["offset"];

        //1单天／2区间
        if ($allConfig["timereport"]["date_type"] == 1) {
            $date = $edate = $this->getOffset($offset, 0, $dateview_type);
        } else {
            $date = $this->getOffset($offset, $allConfig["timereport"]["interval"], $dateview_type);
            $edate = $this->getOffset($offset, 0, $dateview_type);
        }

        if (!empty($allConfig['chart'])) {
            foreach ($allConfig['chart'] as $item => $chart) {
                if ($chart['chartconf'][0]['chartType'] != 'spline') {
                    $allConfig['chart'][$item]['date'] = $edate;
                    $allConfig['chart'][$item]['edate'] = $edate;
                } else {
                    $allConfig['chart'][$item]['date'] = $date;
                    $allConfig['chart'][$item]['edate'] = $edate;
                }
                $allConfig['chart'][$item]['date_type'] = $date_type;
            }
        }
        if (!empty($allConfig['table'])) {
            $allConfig['table']['date'] = $date;
            $allConfig['table']['edate'] = $edate;
        }

        if (!empty($allConfig['tablelist'])) {
            foreach ($allConfig['tablelist'] as $key => $value) {
                $allConfig['tablelist'][$key]['date'] = $date;
                $allConfig['tablelist'][$key]['edate'] = $edate;
                $allConfig['tablelist'][$key]['date_type'] = $date_type;
            }

        }

        return $allConfig;
    }

    function resetStatau()
    {
        $sql = "update  t_visual_mail set status =0";
        $metric = Yii::app()->db_metric_meta;
        $metric->createCommand($sql)->execute();

        return true;
    }

    function send($val, $istest = false)
    {
        $reportId = $val['report_id'];
        $metric = Yii::app()->sdb_metric_meta;
        $chartHtml = "";
        $html = "";
        $tipHtml = "";
        $reportInfo = "select  * from t_visual_table where  id =" . $val['report_id'];
        $tableInfo = $metric->createCommand($reportInfo)->queryAll();
        $allConfig = unserialize($tableInfo[0]['params']);
        $allConfig = $this->setTime($allConfig);
        //获取图表数据
        if (!empty($allConfig['chart'])) {
            $chartInfo = $allConfig['chart'];
            if (isset($chartInfo['metric'])) {
                $chartInfo['metric'] = strtolower($chartInfo['metric']);
            }
            if (isset($chartInfo['group'])) {
                $chartInfo['group'] = strtolower($chartInfo['group']);
            }

            $chartData = array();
            foreach ($chartInfo as $key => $params) {
                $params['udcconf'] = urldecode($params['udcconf']);
                $params['query_mysql_type'] = 'master';
                $chart = $this->objVisual->getChart($params);
                if (!empty($chart)) {
                    $chartData[] = $chart['chart'][0];
                }
                $date = $params['date'];
                $edate = $params['edate'];
            }

            //$imgArr =  $this->createChart($chartData);
            //$chartHtml =  $this->createChartHtml($imgArr);
            $chartHtml = $this->createPNGHtml($reportId);
        }


        //处理tablelist  table的数据
        //转换新的的表格数据格式
        if (!isset($allConfig['tablelist']) && isset($allConfig['table'])) {
            $allConfig['tablelist'] = array();
            array_push($allConfig['tablelist'], $allConfig['table']);
            $allConfig['tablelist'][0]['type'] = $allConfig['type'];
            $allConfig['tablelist'][0]['title'] = $allConfig['cn_name'];
            unset($allConfig['table']);
        }

        //表格不为空
        if (!empty($allConfig['tablelist'])) {
            $tablelen = count($allConfig['tablelist']);

            foreach ($allConfig['tablelist'] as $key => $value) {
                $tipHtmlTable = "";

                $value['getDataType'] = 'table';
                $date = $value['date'];
                $edate = $value['edate'];
                $value = $this->objVisual->nameReplace($value);
                if ($value['type'] != 2) {
                    $headerArr = array();
                    $headerArr = $this->objVisual->getNormalHeader($value);
                    $value['total'] = 0;
                    $value['rows'] = 500;
                    //发送邮件时查主库
                    $value['query_mysql_type'] = 'master';

                    //2015-06-19 时间问题 普通报表默认显示showsort 和 sort中 增加维度
                    if (isset($value['grade']) && !isset($value['grade']['isfiexd'])) {

                        if (is_array($value['grade']['sort']) && !in_array('date', $value['grade']['sort'])) {
                            array_unshift($value['grade']['sort'], 'date');
                        }
                        if (is_array($value['grade']['showsort']) && !in_array('date', $value['grade']['showsort'])) {
                            array_unshift($value['grade']['showsort'], 'date');
                        }
                    }
                    //处理老数据兼容
                    $customArr = array();
                    if (isset($value['grade']['orderbyarr'])) {
                        if (is_array($value['grade']['orderbyarr'])) {
                            foreach ($value['grade']['orderbyarr'] as $sort) {
                                $oneCustom = array();
                                $oneCustom['key'] = $sort;
                                $oneCustom['order'] = 'desc';
                                $customArr[] = $oneCustom;
                            }
                        }
                    } else {
                        //自定义表格是不需要默认排序date的
                        if (empty($value['customSort']) && $value['type'] != 8) {
                            $one['key'] = 'date';
                            $one['order'] = 'desc';
                            $customArr[] = $one;
                            $value['customSort'] = json_encode($customArr);
                        }
                    }
                    $searchApi = $this->objVisual->getSearchParams($value);
                    if (!empty($searchApi)) {
                        $value['search'] = json_encode($searchApi);
                    }
                    //判断是否设置了自定义显示条数
                    if ($value['grade']['pubdata']['data_num'] > 0 && $value['grade']['pubdata']['reshape'] != 1) {
                        $value['rows'] = $value['grade']['pubdata']['data_num'];
                    }
                    if (isset($value['grade']['pubdata']['reshape']) && $value['grade']['pubdata']['reshape'] == 1) {
                        if (empty($searchApi)) {
                            $value['search'] = '';
                        }
                    }
                    $data = $this->objFackcube->getData($value, true, false);
                    $tableData = $this->objFackcube->getData($value, true, true, $data);
                    //$tableData = $this->objFackcube->getData($value,true);
                    if (isset($value['grade']['pubdata']['reshape']) && $value['grade']['pubdata']['reshape'] == 1) {

                        $headerArr['reshape'] = 1;
                        $reshageData = $this->visualCon->getReshapeTableFormater($data, $tableData, $value);

                        $tableData['rows'] = $tableData['data'];
                        if ($value['grade']['pubdata']['reshape_type'] == 1) {
                            $new = $this->visualCon->getReshapTypeData($reshageData, $value);
                        } else {
                            $new = $this->visualCon->getReshapeTableData($reshageData, $value);
                        }
                        //重新处理表头
                        $headerArr['key'] = array_keys($new['rows'][0]);
                        foreach ($headerArr['key'] as $key1 => $itemKey) {
                            foreach ($value['grade']['data'] as $item) {
                                if ($item['hide'] && ($item['key'] === $itemKey)) {
                                    unset($headerArr['key'][$key1]);
                                }
                            }
                        }
                        $headerArr['name'] = $headerArr['key'];
                        if ($value['grade']['pubdata']['reshape_type'] == 1) {
                            $groupArr = [];
                            foreach ($value['grade']['data'] as $itemGroup) {

                                if ($itemGroup['type'] == '维度' && ($itemGroup['key'] != 'date') && !empty($itemGroup['search']['val']) && $itemGroup['search']['defaultsearch'] == '') {
                                    $groupArr[] = $itemGroup;
                                }
                            }
                            $listTmp = explode("\n", $groupArr[0]['search']['val']);
                            $headerMap = [];
                            foreach ($listTmp as $tmp) {
                                $keyMap = explode(":", $tmp);
                                $headerMap[$keyMap[0]] = $keyMap[1];
                            }
                        }
                        foreach ($headerArr['name'] as $key2 => $itemVal) {
                            if ($itemVal == 'metric_name') {
                                $headerArr['name'][$key2] = '';
                            }
                            if ($value['grade']['pubdata']['reshape_type'] == 1) {
                                if (isset($headerMap[$itemVal])) {
                                    $headerArr['name'][$key2] = $headerMap[$itemVal];
                                }
                            }
                        }
                        $tableData['data'] = $new['rows'];
                    }
                    $tmpConfig = $value;
                    $tmpConfig['rows'] = 501;
                    $tmpData = $this->objFackcube->getData($tmpConfig, true);

                    if (count($tmpData['data']) > count($tableData['data']) && $value['grade']['pubdata']['reshape'] != 1 && $value['grade']['pubdata']['data_num'] < 1) {
                        // $tipHtml =  $this->tipHtml($reportId);
                        $tipHtmlTable = $this->tipHtml($reportId);
                    }
                    if (!empty($tableData['data'])) {
                        if ($tablelen > 1) {
                            //$html.= "<div style='padding:20px 0 10px 5px;font-weight:900;border-bottom:1px solid #CCC;margin-bottom:10px;'>>> ".$value['title']."</div>";
                            $html .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
                            $html .= "<td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0px 32px 0px 32px;\">";
                            $html .= "<h4 style=\"font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; \">";
                            $html .= "<strong>>> " . $value['title'] . "</strong></h4>";
                            $html .= "</td></tr>";

                        }
                        $html .= $this->createTableHtml($tableData['data'], $headerArr, true);

                        $msgHtml = '';
                        $msgHtml .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
                        $msgHtml .= "<td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0px 32px 0px 32px;\">";
                        $msgHtml = $msgHtml . $tipHtmlTable . '</td></tr>';

                        $html .= $msgHtml;
                    } else {
                        $html .= "<div><p>请注意,数据为空</p></div>";
                        //$html .= "<div style='padding:20px 0 10px 5px;font-weight:900;border-bottom:1px solid #CCC;margin-bottom:10px;'>>> ".$value['title']."</div>";
                        $html .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
                        $html .= "<td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
                        $html .= "<h4 style=\"font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; \">";
                        $html .= "<strong>>> " . $value['title'] . "</strong></h4>";
                        $html .= "</td></tr>";
                        $html .= $this->createTableHtml($tableData['data'], $headerArr);
                    }

                } else {
                    $inter = 7;
                    $value['date'] = date("Y-m-d", strtotime($value['edate']) - 86400 * $inter);
                    $value['query_mysql_type'] = 'master';
                    $res = $this->objFackcube->getData($value, true);
                    if ($res['status'] != 0) {
                        $html .= "<div><p>请注意,数据为空</p></div>";
                        //$html .= "<div style='padding:20px 0 10px 5px;font-weight:900;border-bottom:1px solid #CCC;margin-bottom:10px;'>>> ".$value['title']."</div>";
                        $html .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
                        $html .= "<td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
                        $html .= "<h4 style=\"font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; \">";
                        $html .= "<strong>>> " . $value['title'] . "</strong></h4>";
                        $html .= "</td></tr>";
                        $html .= $this->createTableHtml($tableData['data'], $headerArr);
                    }
                    $headerArr = $this->objVisual->getContHeader($value);
                    $data = $this->objVisual->getContrast($value, $res);
                    if (!empty($data)) {
                        if ($tablelen > 1) {
                            //$html.= "<div style='padding:20px 0 10px 5px;font-weight:900;border-bottom:1px solid #CCC;margin-bottom:10px;'>>> ".$value['title']."</div>";
                            $html .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
                            $html .= "<td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
                            $html .= "<h4 style=\"font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; \">";
                            $html .= "<strong>&gt;&gt;" . $value['title'] . "</strong></h4>";
                            $html .= "</td></tr>";
                        }
                        $html .= $this->createTableHtml($data, $headerArr, true);
                    } else {
                        //$html.= "<div style='padding:20px 0 10px 5px;font-weight:900;border-bottom:1px solid #CCC;margin-bottom:10px;'>>> ".$value['title']."</div>";
                        $html .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
                        $html .= "<td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
                        $html .= "<h4 style=\"font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; \">";
                        $html .= "<strong>>> " . $value['title'] . "</strong></h4>";
                        $html .= "</td></tr>";
                    }
                }
            }
        }

        if ($date == $edate) {
            $headerTitle = $val['title'] . "_" . $edate;
        } else {
            $headerTitle = $val['title'] . "_" . $date . "~" . $edate;
        }

        $headerHtml = $this->headHtml($headerTitle);
        $footerHtml = $this->footer($val['report_id']);
        //加入邮件注释
        if (!empty($val['comments'])) {
            $comments_place = $val['type'];
            //顶部
            $commentsTop = $val['comments'];
            $commentsTop = str_replace("\n", "<p style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px; font-weight: normal; margin-bottom: 10px; color: #000;\">&nbsp;&nbsp;", $commentsTop);
            $commentsTop = '<tr style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 0;">
                            <td class="head-wrap" style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 20px 32px; padding-bottom: 0;">
                            <p style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 0; font-size: 12px; font-weight: normal; margin-bottom: 10px; color: #000;">'
                . "&nbsp;&nbsp;" . $commentsTop . '</p></td></tr>';
            //底部
            $commentsBottom = $val['comments'];
            $commentsBottom = str_replace("\n", "<p style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px; font-weight: normal; margin-bottom: 10px; color: #000;\">", $commentsBottom);
            $commentsBottom = "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0px 32px 0px 32px;\">
                                   <td class=\"head-wrap info-wrap\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 20px 32px; color: #999; padding-bottom: 0;\">
                                   <p style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 10px 0px 10px 0px; font-size: 12px; font-weight: normal; margin-bottom: 10px; color: #000; border-bottom: 1px solid #d4d4d4;\"><strong>数据说明：</strong>
                                   </p>
                                   <p style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px; font-weight: normal; margin-bottom: 10px; color: #000;\">"
                . $commentsBottom . '</p></td>
                                       <td style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 0;"></td></tr>';
            if ($comments_place == 1) {
                $html = $headerHtml . $commentsTop . $chartHtml . $html . $tipHtml . $footerHtml;
            } else if ($comments_place == 2) {
                $html = $headerHtml . $chartHtml . $html . $tipHtml . $commentsBottom . $footerHtml;
            }
        } else {
            $html = $headerHtml . $chartHtml . $html . $tipHtml . $footerHtml;
        }

        if ($date == $edate) {
            $title = "【订阅】" . $val['title'] . "_" . $edate;
        } else {
            $title = "【订阅】" . $val['title'] . "_" . $date . "~" . $edate;
        }
        $address = explode(",", $val['addressee']);

        $this->objComm->sendMail(implode(';', $address), $html, $title);

        if (!$istest) {
            //$setSql = "update  t_visual_mail set status =1  where  mail_id= ".$val['mail_id'];
            $timeStr = date("Y-m-d H:i:s");
            $setSql = "update  t_visual_mail set send_time ='" . $timeStr . "'  where  mail_id= " . $val['mail_id'];
            $maDb = Yii::app()->db_metric_meta;
            $maDb->createCommand($setSql)->execute();
        }

        return 'success';
    }

    function getMailInfo($id = false)
    {
        if ($id) {
            $mailInfo = "select * from  t_visual_mail where  mail_id= " . $id;
        } else {
            $mailInfo = "select * from  t_visual_mail  ";
        }
        $metric = Yii::app()->sdb_metric_meta;
        //获取配置
        $result = $metric->createCommand($mailInfo)->queryAll();

        if (!empty($result)) {
            //如果邮件发送时间比今天凌晨大，说明邮件已经发送。
            if (!$id) {
                $realResult = array();
                foreach ($result as $key => $value) {
                    if ($value['run_type'] == 1) {
                        $nowTime = strtotime(date("Y-m-d H:i:s"));
                    } else {
                        $nowTime = strtotime(date("Y-m-d"));
                    }
                    if (strtotime($value['send_time']) < $nowTime) {
                        $realResult[] = $value;
                    }
                }
                $result = $realResult;
            }
        }

        return $result;
    }

    //一张报表只能被一封邮件发出
    function uniqueMailCheck($report_id)
    {
        $mail_return = array();
        $mailList = $this->getMailList();
        foreach ($mailList as $k => $v) {
            if ($v['report_id'] == $report_id) {
                $mail_return['mail_id'] = $v['mail_id'];
                $mail_return['author'] = $v['author'];

                return $mail_return;
            }
        }

        return null;
    }

    function getAllMailInfo()
    {
        $mailInfo = "select * from  t_visual_mail";
        $metric = Yii::app()->sdb_metric_meta;
        //获取配置
        $result = $metric->createCommand($mailInfo)->queryAll();

        return $result;
    }

    function modifyMailAlive($id, $alive)
    {
        $sql = "update t_visual_mail set alive = {$alive} where mail_id = {$id}";
        Yii::app()->sdb_metric_meta->createCommand($sql)->execute();
    }

    function getOffset($offset, $interval = 0, $dateviewType = 2)
    {

        $minuesdays = intval($offset) + intval($interval);
        switch ($dateviewType) {
            case "1":
                $date = date("Y-m-d H:00", strtotime("-" . $minuesdays . " hours"));//修改为小时级别
                break;
            case "2":
                $date = date("Y-m-d", strtotime("-" . $minuesdays . " day"));
                break;
            case "3":
                $date = date("Y-m", strtotime("-" . $minuesdays . " day"));
                break;
        }

        return $date;

    }

    function getMailLogList($id, $index = '', $offset = '')
    {
        $sql = "select
                    log.mail_id as mail_id,
                    log.send_date as send_date,
                    log.start_at as start_at,
                    log.end_at as end_at,
                    log.send_status as send_status,
                    log.send_type as send_type,
                    mail.title as mail_title
                from t_visual_mail_log as log
                inner join t_visual_mail as mail on log.mail_id = mail.mail_id
                where log.mail_id = {$id}
                order by id desc
                ";

        if (!empty($index) && !empty($offset)) {
            $start = ($index - 1) * $offset;
            $end = $index * $offset;
            $sql .= " limit $start, $end";
        }
        if (empty($index) && empty($offset)) {
            $sql .= " limit 0, 1000";
        }

        $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();

        return $result;
    }

    function sendOpenApi($val, $istest = false)
    {
        $html = "";

        //表格不为空
        if (!empty($val['data'])) {

            $valData = $val['data'];
            foreach ($valData as $key => $valItem) {
                $value = $valItem;
                $html .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 10px;\">";
                $html .= "<td class=\"txt-l\"  style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;\">
                {$value['name']}</td>";
                $html .= "<td class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;\">
                {$key}</td>";
                if (!empty($value['data'])) {
                    $html .= "<td><table cellpadding=\"0\" cellspacing=\"0\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; margin: 10px 0; border-spacing: 0; border-collapse: collapse; border: 1px solid #f4f4f4; color: #333; width: 100%;\">
                                        <th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; border-top: none; text-align: left;\">任务名称</th>
                                         <th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; border-top: none; text-align: left;\">最后执行时间</th>
                                         <th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; border-top: none; text-align: left;\">任务周期</th>
                                         <th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; border-top: none; text-align: left;\">报表</th>";
                    foreach ($value['data'] as $index => $items) {
                        $html .= "<tr style=\"font - family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans - serif; margin: 0; padding: 0; font - size: 12px;\">";
                        $html .= "<td class=\"txt-l\"style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;\">
                                        {$index}
                                </td>";
                        $html .= "<td class=\"txt-l\"style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;\">
                                        {$items['end_time']}
                                </td>";
                        $html .= "<td class=\"txt-l\"style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;\">
                                        {$items['schedule_level']}
                                </td>";

                        if (count($items['table']) > 0) {
                            $html .= "<td><table cellpadding=\"0\" cellspacing=\"0\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; margin: 10px 0; border-spacing: 0; border-collapse: collapse; border: 1px solid #f4f4f4; color: #333; width: 100%;\">
                                        <th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; border-top: none; text-align: left;\">名称</th>
                                        <th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; border-top: none; text-align: left;\">上线状态</th>
                                        <th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; border-top: none; text-align: left;\">url状态</th>";
                            foreach ($items['table'] as $report =>$tableInfo) {
                                $html .= "<tr style=\"font - family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans - serif; margin: 0; padding: 0; font - size: 12px;\">";
                                $html .= "<td class=\"txt-l\"style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;\">
                                        {$report}</td>";
                                $html .= "<td class=\"txt-l\"style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; ".($tableInfo['flag'] == 1 ? '': 'color:red;')."border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;\">
                                        ".($tableInfo['flag'] == 1 ? '已上线': '已下线')."</td>";
                                $html .= "<td class=\"txt-l\"style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; ".($tableInfo['access'] == 1 ? '': 'color:red;')." border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;\">
                                        ".($tableInfo['access'] == 1 ? '正常': '无法访问')."</td>";
                                $html .= "</tr>";
                            }
                            $html .= "</table></td>";
                        } else {
                            $html .= "<td></td>";
                        }
                        $html .= "</tr>";
                    }
                    $html .= "</table></td>";
                }
                $html .= "</tr>";
            }
        }

        $headerTitle = "DT平台开放API" . "_" . date('Y-m-d');

        $headerHtml = "<table style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0; width: 100%;\" cellspacing=\"0\" cellpadding=\"0\"><tbody><tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">
                            <td class=\"alert alert-warning\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 20px; font-size: 14px; color: #fff; font-weight: bold; text-align: left; border-radius: 3px 3px 0 0; background: #ff9f00;\">
                                <h2 class=\"title\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; padding: 0; font-weight: 200; line-height: 1.2em; font-size: 28px; margin: 0; color: #fff; text-align: left;\">
                                    {$headerTitle}
                                </h2>
                            </td>
                        </tr>
                        <tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">
                            <td class=\"head-wrap\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 20px 32px 0px 32px; padding-bottom: 0;\">
                                <p style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px; font-weight: normal; margin-bottom: 10px; color: #000;\">
                                亲爱的用户，您好！根据数据平台统计，昨日的开放API运行情况如下，请您查阅！
                                </p>
                            </td>
                        </tr>
                        <tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\"><td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0px 32px 0px 32px;\">
                            <h4 style=\"font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; \"><strong>>> 开放API</strong></h4></td>
                        </tr>
                        <tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">
                            <td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0px 32px 0px 32px;\">
                                <table cellpadding=\"0\" cellspacing=\"0\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; margin: 10px 0; border-spacing: 0; border-collapse: collapse; border: 1px solid #f4f4f4; color: #333; width: 100%;\">
                                    <tbody>
                                        <tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;\">
                                            <th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;\">项目名称</th>
                                            <th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;\">TOKEN</th>
                                            <th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;\">详细</th>
                                        </tr>";
        $footerHtml = "</tbody></table></td></tr><tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\"><td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0px 32px 0px 32px;\"></td></tr><tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\"><td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0px 32px 0px 32px;\"><h4 style=\"font-size: 14px;padding: 10px 0 10px 5px; border-bottom: 1px solid #d4d4d4;font-weight: 20; \">
                        <tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;\">
                        <td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 20px 0px;\"> <p style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0px 32px 0px 32px; font-size: 12px; font-weight: normal; margin-bottom: 10px;\"> 感谢您的订阅！任何问题，欢迎联系 数据团队 <a href=\"mailto:di@qudian.com\" target=\"_blank\"> di@qud<wbr />ian.com </a> </p> </td>
                    </tr>
                    <tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; margin: 0; padding: 0;\">
                        <td class=\"alert alert-warning alert-footer\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica,  sans-serif; margin: 0; padding: 20px; font-size: 14px; color: #fff; font-weight: bold; text-align: left; border-radius: 0 0 3px 3px; background: #ff9f00; line-height: 33px;\"> <p class=\"footer\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, sans-serif; padding: 0; font-weight: normal; margin-bottom: 10px; margin: 0; text-align: left; color: #fff; font-size: 16px; line-height: 33px;\"> <span>祝您工作愉快</span> </p> </td>
                    </tr>
                </tbody>
            </table>";

        $html = ($headerHtml.$html.$footerHtml);
        $title = $headerTitle;

        $address = explode(",", $val['addressee']);

        $status = $this->objComm->sendMail(implode(';', $address), $html, $title);

        return $status;
    }

}
