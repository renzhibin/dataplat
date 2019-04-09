<?php

class TimemailController extends Controller
{
    function __construct()
    {
        $this->objMenu = new MenuManager();
        $this->objVisual = new VisualManager();
        $this->objReport = new ReportManager();
        $this->objAuth = new AuthManager();
        $this->objProject = new ProjectManager();
        $this->objMail = new TimeMailManager();
        $this->objComm = new CommonManager();
    }
//    function  actionPie(){
//        require_once ('jpgraph.php');
//        require_once ('jpgraph_pie.php');
//        $data = array(40,21,17,14,23);
//        $lenthArr =array('小猪','不知道','鑫','杨森','sbu平');
//        $graph = new PieGraph(500,400,'auto');
//        //$graph->SetBox(true);
//        $graph->title->SetFont(FF_CHINESE,FS_NORMAL,10);
//        $graph->title->Set('对比示例一');
//        // Create
//        $p1 = new PiePlot($data);
//        $graph->legend->SetFont(FF_CHINESE,FS_NORMAL);
//        $p1->SetLegends($lenthArr);
//        $graph->Add($p1);
//        $p1->ShowBorder();
//        $p1->SetColor('black');
//        $p1->SetSliceColors(array('#1E90FF','#2E8B57','#ADFF2F','#DC143C','#BA55D3'));
//        $graph->Stroke();
//    }
//    function  actionColoum(){
//        require_once ('jpgraph/jpgraph.php');
//        require_once ('jpgraph/jpgraph_bar.php');
//
//        $data1y=array(47,80,40,116);
//        $data2y=array(61,30,82,105);
//        $data3y=array(115,50,70,93);
//        $graph = new Graph(350,200,'auto');
//        $graph->SetScale("textlin");
//        $graph->SetBox(false);
//        $graph->ygrid->SetFill(false);
//        $graph->xaxis->SetTickLabels(array('A','B','C','D'));
//        //$graph->yaxis->HideLine(false);
//        $graph->yaxis->HideTicks(false,false);
//        $b1plot = new BarPlot($data1y);
//        $b2plot = new BarPlot($data2y);
//        $b3plot = new BarPlot($data3y);
//        $gbplot = new GroupBarPlot(array($b1plot,$b2plot,$b3plot));
//        $graph->Add($gbplot);
//
//
//        $b1plot->SetColor("white");
//        $b1plot->SetFillColor("#cc1111");
//
//        $b2plot->SetColor("white");
//        $b2plot->SetFillColor("#11cccc");
//
//        $b3plot->SetColor("white");
//        $b3plot->SetFillColor("#1111cc");
//
//        $graph->title->Set("Bar Plots");
//
//// Display the graph
//        $graph->Stroke();
//    }
    function actionIndex()
    {
        $visualList = $this->objReport->getReportList();
        $mailList = $this->objMail->getMailList();
        $nowTime = strtotime(date("Y-m-d"));
        foreach ($visualList as $t => $v) {
            if ($v['type'] == 3) {
                unset($visualList[$t]);
            }
        }
        foreach ($mailList as $key => $val) {
            foreach ($visualList as $item => $reort) {
                if ($val['report_id'] == $reort['id']) {
                    $mailList[$key] = array_merge($val, $reort);
                }
            }
            if (strtotime($val['send_time']) > $nowTime) {
                $mailList[$key]['status'] = 1;
            }
        }
        //去掉衍生报表

        //面包屑导航
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "index", 'content' => '管理工具');
        $indexStr[] = array('href' => "#", 'content' => '邮件订阅');

        $tplArr['guider'] = $indexStr;
        $tplArr['visualList'] = $visualList;
        $tplArr['mailList'] = $mailList;
        $this->render('timemail/index.tpl', $tplArr);
    }

    /**
     * 编辑邮件
     */
    function actionEdit($id)
    {
        if (empty($id)) {
            echo "<script>window.location.href ='/timemail/index'</script>";

            return;
        }
        $mailInfo = $this->objMail->getMailInfo($id);
        if (empty($mailInfo)) {
            echo "<script>window.location.href ='/timemail/index'</script>";

            return;
        }
        $reportInfo = $this->objReport->getReoport($mailInfo[0]['report_id']);
        $time = $mailInfo[0]['time'];
        $beginAt = $mailInfo[0]['begin_at'];
        $endAt = $mailInfo[0]['end_at'];
        $visualList = $this->objReport->getReportList();
        $tplArr['id'] = $id;
        $tmp = explode(":", $time);
        $beginTmp = explode(":", $beginAt);
        $endTmp = explode(":", $endAt);
        $tplArr['time_h'] = $tmp[0];
        $tplArr['time_m'] = $tmp[1];
        $tplArr['begin_time_h'] = $beginTmp[0];
        $tplArr['begin_time_m'] = $beginTmp[1];
        $tplArr['end_time_h'] = $endTmp[0];
        $tplArr['end_time_m'] = $endTmp[1];
        $tplArr['title'] = $mailInfo[0]['title'];
        $tplArr['addressee'] = $mailInfo[0]['addressee'];
        $tplArr['report_name'] = $reportInfo['id'] . '_' . $reportInfo['cn_name'];
        $tplArr['report_id'] = $reportInfo['id'];
        $tplArr['visualList'] = $visualList;
        $tplArr['warning_address'] = $mailInfo[0]['warning_address'];
        $tplArr['comments'] = $mailInfo[0]['comments'];
        $tplArr['type'] = $mailInfo[0]['type'];
        $tplArr['run_type'] = $mailInfo[0]['run_type'];
        //面包屑导航
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "index", 'content' => '管理工具');
        $indexStr[] = array('href' => "index", 'content' => '邮件订阅');
        $indexStr[] = array('href' => "#", 'content' => '邮件编辑');

        $tplArr['guider'] = $indexStr;

        $this->render('timemail/mailedit.tpl', $tplArr);
    }

    /**
     * 保存
     */
    function actionSaveMail()
    {
        $mailInfo = $_REQUEST['mailInfo'];
        $hour= explode(':', $mailInfo['time']);
        $hour = $hour[0];
        $mailInfo['begin_at'] =  $hour . ':00';
        $mailInfo['end_at'] = '01:00';
        $titleInfo = $this->objReport->getReoport($mailInfo['report_id']);
        $auth = $titleInfo['auth'];
        $authArr = explode(",", $auth);
        $authorArr = explode(",", $mailInfo['addressee']);
        //验证用户权限
        foreach ($authorArr as $key => $val) {
            $groupInfo = $this->objMail->getGroup($val);
            $product = Yii::app()->user->isProducer($val);
            if (!$product) {
                if ($val == 'all') {
                    $this->jsonOutPut(1, '该报表除分析师外其它人没有报表访问权限,不能发送邮件');
                    exit;
                }
                if (empty($groupInfo)) {
                    $this->jsonOutPut(1, $val . '没有报表访问权限,不能发送邮件');
                    exit;
                } else {
                    $re = array_intersect($authArr, explode(",", $groupInfo[0]['group']));
                    if (empty($re)) {
                        $this->jsonOutPut(1, $val . '没有报表访问权限,不能发送邮件');
                        exit;
                    }
                }
            }
        }


        $mailInfo['title'] = trim($mailInfo['title']);
        if (empty($mailInfo['title'])) {
            $mailInfo['title'] = $titleInfo['cn_name'];
        }
        //print_r($mailInfo);exit;
        //一张报表只能被一封邮件发出
        $checkStatus = $this->objMail->uniqueMailCheck($mailInfo['report_id']);
        $data = $this->objMail->uniqueMailCheck($mailInfo['report_id']);
        if (!empty($data)) {
            $this->jsonOutPut(2, '邮件已经被发送', $data);

            return;
        }
        $re = $this->objMail->saveMail($mailInfo);
        if ($re) {
            $this->jsonOutPut(0, '成功');
        } else {
            $this->jsonOutPut(1, '失败');
        }
    }

    /**
     * 更新邮件
     */
    function actionUpdateMail()
    {
        $mailInfo = $_REQUEST['mailInfo'];
        $hour= explode(':', $mailInfo['time']);
        $hour = $hour[0];
        $mailInfo['begin_at'] =  $hour . ':00';
        $mailInfo['end_at'] = '01:00';
        $titleInfo = $this->objReport->getReoport($mailInfo['report_id']);
        $auth = $titleInfo['auth'];
        $authArr = explode(",", $auth);
        $authorArr = explode(",", $mailInfo['addressee']);
        //验证用户权限
        foreach ($authorArr as $key => $val) {
            $groupInfo = $this->objMail->getGroup($val);
            $product = Yii::app()->user->isProducer($val);
            if (!$product) {
                if ($val == 'all') {
                    $this->jsonOutPut(1, '该报表除分析师外其它人没有报表访问权限,不能发送邮件');
                    exit;
                }
                if (empty($groupInfo)) {
                    $this->jsonOutPut(1, $val . '没有报表访问权限,不能发送邮件');
                    exit;
                } else {
                    $re = array_intersect($authArr, explode(",", $groupInfo[0]['group']));
                    if (empty($re)) {
                        $this->jsonOutPut(1, $val . '没有报表访问权限,不能发送邮件');
                        exit;
                    }
                }
            }
        }


        $mailInfo['title'] = trim($mailInfo['title']);
        if (empty($mailInfo['title'])) {

            $mailInfo['title'] = $titleInfo['cn_name'];
        }
        $re = $this->objMail->updateMail($mailInfo);
        if ($re) {
            $this->jsonOutPut(0, '成功');
        } else {
            $this->jsonOutPut(1, '失败');
        }
    }

    /**
     * 删除
     */
    function actionDelMail()
    {
        $id = $_REQUEST['id'];
        $re = $this->objMail->delMail($id);
        if ($re) {
            $this->jsonOutPut(0, '成功');
        } else {
            $this->jsonOutPut(1, '失败');
        }
    }

    /**
     * sendMail
     */
    function actionSendMail()
    {
        $send_date = date('Y-m-d');
        $start_datetime = date('Y-m-d H:i:s');

        $id = $_REQUEST['id'];
        //传入id只会返回一个mailinfo
        $result = $this->objMail->getMailInfo($id);
        //获取报表信息
        if (!empty($result)) {
            foreach ($result as $key => $val) {
                //检测数据是否跑完
                $dataStatus = $this->objMail->checkReportData($val['report_id'], date("Y-m-d", strtotime("-1 day")));
                if (!$dataStatus) {
                    //数据未跑完,直接退出
                    $this->jsonOutPut(1, '数据未跑完,请耐心等待');
                    exit;
                }
                $hasChart = $this->objReport->hasChart($val['report_id']);
                //手动发送时,如果有图片,需要每次都重新抓取图片
                // $hasChart=false;
                if ($hasChart) {
                    $out = $this->objMail->phantomjs_get($val['report_id']);
                    if ($out !== 'true') {
                        //图片抓取错误,退出
                        $this->jsonOutPut(1, '图片抓取错误');
                        //错误加入日志
                        Yii::log(date('Y-m-d H:i:s') + '  ' + $out, 'info');
                        exit;
                    }
                }
                $status = $this->objMail->send($val);
            }
        }

        $end_datetime = date('Y-m-d H:i:s');
        $this->objComm->insertMailLog([
            'mail_id'     => $id,
            'send_date'   => $send_date,
            'start_at'    => $start_datetime,
            'end_at'      => $end_datetime,
            'send_status' => 1,
            'send_type'   => 2,
        ]);

        $this->jsonOutPut(0, $val['title'] . '发送成功');
    }

    function actionSendMailLog()
    {
        $id = $_REQUEST['id'];
        $data['list'] = $this->objMail->getMailLogList($id);

        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "#", 'content' => '管理工具');
        $indexStr[] = array('href' => "index", 'content' => '邮件订阅');
        $indexStr[] = array('href' => "#", 'content' => '运行详情');

        $data['guider'] = $indexStr;

        $this->render('timemail/maillog.tpl', $data);
    }

    function actionCheckChart()
    {
        $id = $_REQUEST['id'];
        $result = $this->objMail->getMailInfo($id);
        $hasChart = $this->objReport->hasChart($result[0]['report_id']);
        //获取报表信息
        $tip = '';
        if ($hasChart) {
            $tip = '正在抓取图片,请等候大约10秒';
            $this->jsonOutPut(0, $tip);
        } else {
            $this->jsonOutPut(0, $tip);
        }
    }

    /**
     * 邮件测试
     */
    function actionTestMail()
    {
        $str = date("Y-m-d H:i");
        $mailInfo = $_REQUEST['mailInfo'];
        $titleInfo = $this->objReport->getReoport($mailInfo['report_id']);
        $auth = $titleInfo['auth'];
        $authArr = explode(",", $auth);
        $authorArr = explode(",", $mailInfo['addressee']);
        //验证用户权限
        foreach ($authorArr as $key => $val) {
            $groupInfo = $this->objMail->getGroup($val);
            $product = Yii::app()->user->isProducer($val);
            if (!$product) {
                if ($val == 'all') {
                    $this->jsonOutPut(1, '该报表除分析师外其它人没有报表访问权限,不能发送邮件');
                    exit;
                }
                if (empty($groupInfo)) {
                    $this->jsonOutPut(1, $val . '没有报表访问权限,不能发送邮件');
                    exit;
                } else {
                    $re = array_intersect($authArr, explode(",", $groupInfo[0]['group']));
                    if (empty($re)) {
                        $this->jsonOutPut(1, $val . '没有报表访问权限,不能发送邮件');
                        exit;
                    }
                }
            }
        }
        $dataStatus = $this->objMail->checkReportData($mailInfo['report_id'], date("Y-m-d", strtotime("-1 day")));
        if ($dataStatus) {
            $tip = $this->objMail->send($mailInfo, true);
        } else {
            $tip = $this->objMail->warning($mailInfo, true);
        }
        $this->jsonOutPut(0, $tip);
    }

    //提供邮件信息以供爬取
    function actionUrllibMail()
    {
        $mailList = $this->objMail->getAllMailInfo();
        foreach ($mailList as $key => $info) {
            $reportId = $info['report_id'];
            if ($this->objReport->hasChart($reportId)) {
                $dataStatus = $this->objMail->checkReportData($reportId, date("Y-m-d", strtotime("-1 day")));
                print_r('{"report_id":"' . $info['report_id'] . '",' . '"dataStatus":"' . $dataStatus . '"}' . "\n");
            }
        }
    }

    function actionModifyAlive()
    {
        $data = $_REQUEST['id'];

        $mailId = $alive = '';
        sscanf($data, "%[^,],%s", $mailId, $alive);

        if (is_numeric($mailId) && is_numeric($alive) && in_array($alive, [0, 1, '0', '1'])) {
            $this->objMail->modifyMailAlive($mailId, 0 == $alive ? 1 : 0);
            $this->jsonOutPut(0, '修改成功');
        } else {
            $this->jsonOutPut(1, '参数校验异常');
        }
    }
}
