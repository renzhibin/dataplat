<?php

class VisualController extends Controller
{
    public $objVisual;

    public $overiewMap = array('group' => 3, 'menu_id' => 1);//kernel组对应的组id是3 概览的菜单id是1

    function __construct()
    {
        $this->objVisual = new VisualManager();
        $this->objFackcube = new FackcubeManager();
        $this->objuser = new AuthManager();
        $this->objReport = new ReportManager();
        $this->objoffline = new BehaviorManager();
        $this->common = new CommonManager();
    }

    function actionMail_all()
    {
        $recently = $this->objReport->getRecentlyUser(date("Y-m-d H:i:s", strtotime("-6 month")));
        $tplArr['user'] = Yii::app()->user->username;
        $tplArr['recently'] = $recently;
        $tplArr['count'] = sizeof($recently);
        //需要有admin权限
        if ($this->admin) {
            $this->render('visual/mail.tpl', $tplArr);
        } else {
            $this->render('error/404.tpl');
        }

    }

    function actionSend_all_mail()
    {
        //需要有admin权限
        if ($this->admin) {
            $data = $_REQUEST;
            if ($data['title'] == '') {
                echo '请返回输入标题';
                die();
            }
            if ($data['content'] == '') {
                echo '请返回输入内容';
                die();
            }
            if ($data['set'] == '2') {
                $data['content'] = nl2br($data['content']);
            }

            //获取最近半年的用户
            $recently = $this->objReport->getRecentlyUser(date("Y-m-d H:i:s", strtotime("-6 month")));
            $names = array();
            $file = 'user_notify.log';//要写入文件的文件名（可以是任意文件名），如果文件不存在，将会创建一个
            foreach ($recently as $k => $v) {
                $name = $v['user_name'];
                $names[] = $v['user_name'];
                $this->visual->sendMail($name, $data['content'], $title = $data['title'], '', true);
                $content = date("Y-m-d H:i:s") . ' 收件人: ' . $name . " 发送成功\r\n";
                if ($f = file_put_contents($file, $content, FILE_APPEND)) {// 这个函数支持版本(PHP 5)
                    echo $name . " 发送成功(已log)。<br />";
                }
            }
            //发送邮件
            //$this->visual->sendMail($names,$data['content'],$title=$data['title'],'',true);
            echo '发送成功,共计' . sizeof($names) . '人';

        } else {
            $this->render('error/404.tpl');
        }
    }

    function actionImg()
    {
        $data = $_REQUEST['data'];
        $this->common->addUserRequestToLog($data);

        $name = $_REQUEST['name'];
        $report_id = $_REQUEST['report_id'];
        if (empty($name)) {
            $name = 'report';
        }
        //识别脚本下载图片的行为
        @session_start();
        $toDownPng = $_SESSION['toDownPng'];
        if ($toDownPng == 2) {
            $name = $report_id . '_' . date('Y-m-d', time());
        } else if ($toDownPng == 3) {
            $name = 'test' . '_' . $report_id . '_' . $_SESSION['start_date'] . '_' . $_SESSION['end_date'];
        }
        //var_dump($_REQUEST);exit();
        $name = $name . '_' . $this->getDownFileNameUserEmailEncodeSuffix();
        $name = $name . '.png';
        $data = str_replace('data:image/png;base64,', '', $data);
        $data = str_replace(' ', '+', $data);

        $image = base64_decode($data);
        $size = strlen($image);
        header('Content-Type: image/png');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $name);
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);
        echo $image;


    }

    function actionGetContrast()
    {
        $params = $_REQUEST['allData']['table'];
        $inter = 8;
        $params['date'] = date("Y-m-d", strtotime($params['edate']) - 86400 * $inter);
        //var_dump($params);
        $res = $this->objFackcube->getData($params);
        if ($res['status'] != 0) {
            $this->jsonOutPut(-1, $res['msg'], array());
            exit();
        }
        // 新旧数据格式兼容
        if (isset($params['contrast'])) {
            if (empty($params['contrast'])) {
                $this->jsonOutPut(-1, '至少选择一个要对比的值( 例：当日值)', array());
                exit();
            }
        } else {
            if (empty($params['grade']['contrast']['data'])) {
                $this->jsonOutPut(-1, '至少选择一个要对比的值( 例：当日值)', array());
                exit();
            }
        }

        $data = $this->objVisual->getContrast($params, $res);
        $jsonArr = array('status' => 0, 'msg' => '成功', 'showMsg' => $res['showMsg'], 'data' => $data);

        $this->common->addUserRequestToLog($jsonArr);

        echo json_encode($jsonArr);
    }

    function actionUrlToName()
    {
        $urls = $_REQUEST['urls'];
        $url_arr = array();//url数组
        if (is_array($urls)) {
            $url_arr = $urls;
        } else if (is_string($urls)) {
            $url_arr[] = $urls;
        }

        $return = array();
        foreach ($url_arr as $key => $url) {
            $url_attr = explode('/', $url);//临时数组,用来识别id
            if (in_array('id', $url_attr)) {
                $id_key = array_search('id', $url_attr);
                $id = $url_attr[$id_key + 1];
            } else if (isset($url_attr[3]) && is_numeric($url_attr[3])) {
                $id = $url_attr[3];
            } else {
                $id = 0;
            }
            if ($id != 0) {
                $report = $this->objReport->getReoport($id);
                $return[] = $report['cn_name'];
            } else {
                $return[] = '首页';
            }
        }
        echo json_encode($return);
    }

    //报表展示主要入口
    /*
     * menu_id:目录id
     * id:报表id
     */
    function actionIndex()
    {
        //添加记录用户审计的功能,todo

        //输入参数解析
        $menu_id = $_REQUEST['menu_id'] ? $_REQUEST['menu_id'] : '0';
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : '0';
        $customFlag = $_REQUEST['custom'] === '1' ? true : false; # 是否为自定义收藏报表
        $userName = Yii::app()->user->username;
        $menuObj =  new MenuManager();
        //获取菜单
        $menuInfo = $menuObj->getMenu();

        $isWhiteTable = 0;//分特殊白名单报表
        //特殊报表处理，具有特殊报表权限默认打开特殊报表,特殊报表链接/visual/index/id
        if (empty($menu_id) && !empty($menuInfo['urlMenu'])) {
            foreach ($menuInfo['urlMenu'][0]['table_id'] as $table_item) {
                if (!empty($id) and $table_item['id'] == $id) {
                    $isWhiteTable = 1;
                    break;
                } elseif (empty($id)) {
                    $id = $table_item['id'];
                    $isWhiteTable = 1;
                    break;
                }
            }
        }

        //报表显示顺序
        if (!empty($menu_id)) {
            foreach ($menuInfo['menuTitle'] as $first_menu => $secondmenuinfo) {
                foreach ($secondmenuinfo as $second_menu_id => $menuinfo) {
                    if ($second_menu_id == $menu_id) {
                        $nowMenuinfo = $menuinfo;
                    }
                }
            }
            if (empty($id)) {
                $showtable = $nowMenuinfo['table'][0];
                if ($showtable['type'] == 1) {
                    $id = $showtable['id'];
                } elseif ($showtable['type'] == 2) {
                    $url = $showtable['url'];
                    $id = $url;
                } elseif ($showtable['type'] == 3) {
                    $id = $showtable['id'];
                }
            }
        } else if (!empty($menuInfo['collect']) && empty($id)) {
            $id = key($menuInfo['collect']);
        }
        if ($id != '0') {
            $params = array();
            $params['table_id'] = (string)$id;
            $params['menu_id'] = (string)$menu_id;
            $this->objoffline->addUserBehaviorToLog($id, $menu_id, '/visual/index/menu_id/' . $menu_id, $params);
        }

        $tplArr['menu_id'] = $menu_id;
        $tplArr['id'] = $id;
        $tplArr['collect'] = $menuInfo['collect'];//收藏报表
        $tplArr['collectCustom'] = $menuInfo['collectCustom'];//自定义收藏报表
        $tplArr['specialMenu'] = $menuInfo['specialMenu'];//报表管理等
        $tplArr['powerMenu'] = $menuInfo['powerMenu'];//报表管理等
        $tplArr['is_super'] = Yii::app()->user->isSuper();
        $tplArr['commonMenu'] = $menuInfo['commonMenu'];//常用工具等
        $tplArr['menuTitle'] = $menuInfo['menuTitle'];
        $tplArr['isCollect'] = $this->objuser->isFavorites($id);//在没有ID的时候直接判断$menuInfo数组内的，减少首页的查询
        //TODO
        $tplArr['showTable'] = $nowMenuinfo;
        $tplArr['urlMenu'] = $menuInfo['urlMenu'];
        $tplArr['isWhiteTable'] = $isWhiteTable;
        $reportauth = false;
        if ($id > 0 && $customFlag === false) {
            if ($this->objuser->checkAuthFromMenu($id, $userName)) {
                $reportauth = true;
            }
            if ($reportauth == true) {
                //报表展示数据
                $newArr = $this->objReport->showReport($id);
                if (!empty($newArr)) {
                    $tplArr = array_merge($newArr, $tplArr);
                }
            }
        }
        if ($id > 0 && $customFlag === true) {
            if ($this->objuser->checkAuthFromMenu($id, $userName)) {
                $reportauth = true;
            } else {
                $checkData = $this->objReport->checkReportCustomAuth(is_numeric($id) ? intval($id) : 0, Yii::app()->user->username ?: '');
                if (!empty($checkData)) {
                    $reportauth = true;
                }
            }
            if ($reportauth == true) {
                //报表展示数据
                $newArr = $this->objReport->showReportCustom($id);
                if (!empty($newArr)) {
                    $tplArr = array_merge($newArr, $tplArr);
                }
            }
        }

        if ($id == 0) {
            if ($this->objuser->checkAuthFromMenu($_SERVER['REQUEST_URI'], $userName)) {
                $reportauth = true;
            }
        }
        $tplArr['user_name'] = $userName;
        $tplArr['reportauth'] = $reportauth;
        $tplArr['url'] = $url;
        if (!empty($_REQUEST['allcontent'])) {
            $tplArr['allcontent'] = $_REQUEST['allcontent'];
        }
        $prod = Yii::app()->user->isProducer();

        //面包屑导航
        $objMenu = new MenuManager();
        $navigationMenu = $objMenu->getNavigationMenu($menu_id);
        $indexStr[] = array('href' => "/", 'content' => '首页');
        $indexStr[] = array('href' => "/visual/index/menu_id/$menu_id", 'content' => $navigationMenu['first_menu']);
        $indexStr[] = array('href' => "#", 'content' => $navigationMenu['second_menu']);
        $tplArr['guider'] = $indexStr;
        $tplArr['is_producer'] = $prod ? 1 : 0;

        // 添加是收藏页面 type=9
        if ($tplArr['isCollect'] == true && isset($tplArr['confArr']['type']) && $tplArr['confArr']['type'] == 9) {
            if (isset($tplArr['confArr']['url'])) {
                $tplArr['confArr']['url'] = str_replace('isCollect=0', "isCollect=1", $tplArr['confArr']['url']);
            }
        }
        if (isset($tplArr['confArr']['type']) && $tplArr['confArr']['type'] == 9) {
            $time = time();
            $md5 = MD5("CfQNGOKatc1s6TX2rT7OeG8FHdNJ3KvP" . $time . 'dt.xiaozhu.com');
            $tplArr['confArr']['url'] = str_replace('open=1', "open=1&token={$md5}&timestamp={$time}&user_name={$userName}", $tplArr['confArr']['url']);
            if (stripos(Yii::app()->getRequest()->queryString, 'param=1') >= 0) {
                $tplArr['confArr']['url'] = str_replace('open=1', 'open=1&' . Yii::app()->getRequest()->queryString, $tplArr['confArr']['url']);
            }
        }
        $tplArr['isCollect'] = $customFlag ? true : $tplArr['isCollect'];
        $tplArr['isCollectCustom'] = $customFlag;
        $tplArr['isMobile'] = $this->isMobile();
        $this->render('visual/index.tpl', $tplArr);
    }

    function isMobile()
    {
        $arr = array("iPhone", "iPad", "webOS", "BlackBerry", "Android");
        foreach ($arr as $value) {
            if (stripos($_SERVER['HTTP_USER_AGENT'], $value)) {
                return true;
            }
        }

        return false;
    }

    #兼容老报表的url
    function actionShowVisual()
    {
        $this->redirect(array('report/showreport', 'id' => $_REQUEST['id'], 'rsv_pq' => $_REQUEST['rsv_pq']));
    }


    function actionGetTable()
    {
        $allData = $_REQUEST['allData'];
        $tableParams = $allData['table'];
        if (isset($tableParams['metric']))
            $tableParams['metric'] = strtolower($tableParams['metric']);
        if (isset($tableParams['group']))
            $tableParams['group'] = strtolower($tableParams['group']);
        $gardConfig = $this->objVisual->getTableConfig($tableParams);
        $returnData = array();
        $returnData['table'] = $gardConfig;
        $chartInfo = $allData['chart'];
        //为了兼容项目配置页面的东西
        //$returnData['chart'] = $this->__getchart($chartInfo);
        if (!empty($returnData)) {
            $this->jsonOutPut(0, 'ok', $returnData);
        } else {
            $this->jsonOutPut(-1, '获取指标维度信息失败！', $returnData);
        }
    }


    function actionGetChart()
    {
        $chartInfo = $_REQUEST['chartInfo'];
        $chartData = $this->__getchart($chartInfo);
        //$this->jsonOutPut(-1, '生成图表失败',$chartData);die();
        if (!empty($chartData)) {
            if ($chartData['status'] == -1) {
                $this->jsonOutPut(-1, $chartData['msg'], $chartData);
            } else {
                $this->jsonOutPut(0, 'ok', $chartData);
            }
        } else {
            $this->jsonOutPut(-1, '生成图表失败');
        }
    }

    function __getchart($chartInfo)
    {
        if (empty($chartInfo)) {
            return array();
        }
        /*
        session_start();
        $startTime = $_SESSION['start_date'];
        $endTime = $_SESSION['end_date'];
         */
        if (!empty($startTime) and !empty($endTime)) {
            $chartInfo[0]['date'] = $startTime;
            $chartInfo[0]['edate'] = $endTime;
        }

        if (isset($chartInfo['metric']))
            $chartInfo['metric'] = strtolower($chartInfo['metric']);
        if (isset($chartInfo['group']))
            $chartInfo['group'] = strtolower($chartInfo['group']);
        $chartData = array();
        foreach ($chartInfo as $key => $params) {
            $params['udcconf'] = rawurldecode($params['udcconf']);
            $gardConfig = $this->objVisual->getChart($params);
            if (!empty($gardConfig) and $gardConfig['code'] != -1) {
                $chartData[] = $gardConfig['chart'][0];
            } else {
                //处理数据错乱逻辑
                $oneChart = array();
                $oneChart['key'] = "container_" . date("ymd") . "_" . rand(1, 1000);
                $oneChart['chartTitle'] = '需要重新配置图表';
                $chartData[] = $this->chart->getChartParams($oneChart);
                if ($gardConfig['code'] == -1) {
                    //$return=$gardConfig['msg'];
                    $chartData['status'] = -1;
                    $chartData['msg'] = $gardConfig['msg'];
                }
            }
        }

        return $chartData;
    }

    #生成数据列
    function actionGetColoum()
    {
        $params = $_REQUEST['coloum'];
        $params['udcconf'] = rawurldecode($params['udcconf']);
        $gardConfig = $this->objVisual->getTableConfig($params);
        if (!empty($gardConfig)) {
            $this->jsonOutPut(0, 'ok', $gardConfig);
        } else {
            $this->jsonOutPut(-1, '获取数据失败');
        }
    }

    #获取数据
    function actionGetData()
    {
        //header("Content-type: application/json");
        $result = $_REQUEST;
        $params = json_decode($result['datas'], true);
        foreach ($result as $key => $value) {
            if ($key != 'datas') {
                $params[$key] = $value;
            }
        }
        if (isset($params['grade']['pubdata']['reshape']) && $params['grade']['pubdata']['reshape'] == 1) {
            $params['page'] = 1;
            $params['rows'] = 1000000;
        }
        if ($params['type'] == 10) {
            $params['page'] = 1;
            $params['rows'] = 1000000;
        }
        if (isset($params['metric']))
            $params['metric'] = strtolower($params['metric']);
        if (isset($params['group']))
            $params['group'] = strtolower($params['group']);
        if (!isset($params['page']) || !isset($params['rows']) || empty($params['rows'])) {
            $params['page'] = 1;
            $params['rows'] = $params['grade']['pubdata']['pagesize'];
        }
        //如果不设置分页，不取总数
        if (isset($params['grade']['pubdata']['ispagesize']) && $params['grade']['pubdata']['ispagesize'] == 0) {
            $params['total'] = 0;
        }
        $params['getDataType'] = 'table';
        //  print_r($params);exit;
        $data = $this->objFackcube->getData($params, true, false);
        $data_show = $this->objFackcube->getData($params, true, true, $data);
        $tableData = $this->getReshapeTableFormater($data, $data_show, $params);
        if (isset($params['grade']['pubdata']['reshape']) && $params['grade']['pubdata']['reshape'] == 1) {
            if ($params['grade']['pubdata']['reshape_type'] == 1) {
                $tableData = $this->getReshapTypeData($tableData, $params);
            } else {
                $tableData = $this->getReshapeTableData($tableData, $params);
            }
        }
        $this->common->addUserRequestToLog($tableData);
        $tableHeader = $this->getTableHeader($params, $tableData['rows_show']);
        $tableData['tableHeader'] = $tableHeader;
        $tableData = $this->getTableDataByType($params['type'], $params['grade']['data'], $tableData);
        echo json_encode($tableData);
    }

    function getTableDataByType($type, $grade, $tableData)
    {
        switch ($type) {
            case 10:
                $tableData['rows_show'] = $this->getCorssTableData($tableData['rows_show'], $grade, $tableData['tableHeader']);
                $tableData['rows'] = $tableData['rows_show'];
                $tableData['total'] = count($tableData['rows']);
                break;
            default:
                break;
        }

        return $tableData;
    }

    function getCorssTableData($tableData, $grades, $tableHeader)
    {
        $tableDataMap = [];
        $tableDataNew = [];
        $dim1Key = $tableHeader[0]['key'];
        $dim2Key = $tableHeader[1]['origin_key'];
        $metricKey = '';
        foreach ($grades as $grade) {
            if ($grade['hide'] == 1) {
                continue;
            }
            if ($grade['key'] == $dim1Key || $grade['key'] == $dim1Key) {
                continue;
            }
            $metricKey = str_replace(".", "_", $grade['key']);
        }
        foreach ($tableData as $data) {
            if (!isset($tableDataMap[$data[$dim1Key]])) {
                $tableDataMap[$data[$dim1Key]] = [];
            }
            if (isset($tableDataMap[$data[$dim1Key]][$data[$dim2Key]])) {
                continue;
            }
            $tableDataMap[$data[$dim1Key]][$data[$dim2Key]] = $data[$metricKey];
        }
        foreach ($tableDataMap as $key => $map) {
            $row[$dim1Key] = $key;
            foreach ($map as $dim2 => $value) {
                $row[$dim2] = $value;
            }
            array_push($tableDataNew, $row);
        }

        return $tableDataNew;
    }

    function getTableHeader($params, $datas)
    {
        $newData = [];
        if ($params['type'] != 10) {
            return $newData;
        }
        $dim = '';
        $dimH = '';
        $grade = $params['grade']['data'];
        foreach ($grade as $data) {
            if ($data['hide'] == 1) {
                continue;
            }
            if ($data['type'] != '维度') {
                continue;
            }
            if (!$dim) {
                $dim = $data;
                continue;
            }
            $dimH = $data;
            break;
        }
        $newDims = [];
        foreach ($datas as $row) {
            $newDim = $row[$dim['key']];
            if (in_array($newDim, $newDims)) {
                continue;
            }
            array_push($newDims, $newDim);
            $dimSet = $dim;
            $dimSet['key'] = $newDim;
            $dimSet['origin_key'] = $dim['key'];
            $dimSet['name'] = $newDim;
            array_push($newData, $dimSet);
        }
        $dimH['name'] = ' ';
        $dimH['explain'] = '';
        array_unshift($newData, $dimH);

        return $newData;
    }

    public function getCrossTableDownloadData($tableData, $params)
    {
        if (count($tableData) == 0) {
            return [];
        }
        $tableHeader = $this->getTableHeader($params, $tableData);
        $data = $this->getCorssTableData($tableData, $params['grade']['data'], $tableHeader);

        return $data;
    }

    public function getCrossTableDownloadHeader($tableData, $params)
    {
        if (count($tableData) == 0) {
            return [];
        }
        $header = [
            'name' => [],
            'key'  => []
        ];
        $tableHeader = $this->getTableHeader($params, $tableData);
        foreach ($tableHeader as $rowHeader) {
            array_push($header['name'], $rowHeader['name']);
            array_push($header['key'], $rowHeader['key']);
        }

        return $header;
    }

    /**
     * 转换数据
     * @param type $tableData
     * @param type $params
     * @return type
     */
    function getReshapeTableFormater($data, $data_show, $params)
    {
        $gardData = array();
        // echo '<pre/>';print_r($data['data']);exit();
        if (!empty($data['data'])) {
            $gardData = $this->contrast->getContrastData($data_show['data'], $params);
        }

        $tableData['total'] = $data['total'];
        $tableData['rows'] = $data['data'];
        $tableData['rows_show'] = $gardData;
        $tableData['rows_comment'] = $data_show['data'];
        $tableData['msg'] = $data['msg'];
        $tableData['status'] = $data['status'];
        $tableData['showMsg'] = $data['showMsg'];
        $tableData['relyMsg'] = $data['relyMsg'];


        //to trick
        if ($tableData['status'] != 0) {
            $tmp = explode(',', $params['group']);
            if (empty($tmp)) {
                $tmp = explode(',', $params['metric']);
            }

            $tableData['rows'][0][$tmp[0]] = $tableData['msg'];

        }

        return $tableData;
    }

    /**
     * 获取指标-维度转换数据
     */
    public function getReshapTypeData($tableData, $params)
    {
        //取出最后一天日期
        //print_r($params);exit;
        $preData = [];
        foreach ($tableData['rows'] as $item) {
            if ($item['date'] == $params['edate']) {
                $preData[] = $item;
            }
        }
        //获取所有指标
        $metricArr = [];
        $groupArr = [];
        foreach ($params['grade']['data'] as $item) {
            if ($item['type'] != '维度' && $item['key'] != 'date') {
                $one['key'] = implode("_", explode(".", $item['key']));
                $one['name'] = $item['name'];
                $metricArr[] = $one;
            } else {
                if (!empty($item['search']['val']) && $item['search']['defaultsearch'] == '') {
                    $groupArr[] = $item;
                }
            }
        }
        $listTmp = explode("\n", $groupArr[0]['search']['val']);
        $srcData = $this->common->pickup($preData, NULL, $groupArr[0]['key']);
        $reshageData = [];
        foreach ($metricArr as $metricitem) {
            $one = [];
            $one['metric_name'] = $metricitem['name'];
            foreach ($listTmp as $keys) {
                $groupKey = explode(":", $keys)[0];
                if (isset($srcData[$groupKey][$metricitem['key']])) {
                    $one[$groupKey] = $srcData[$groupKey][$metricitem['key']];
                } else {
                    $groupVal = explode(":", $keys)[1];
                    $one[$groupKey] = $srcData[$groupVal][$metricitem['key']];
                }
            }
            $reshageData[] = $one;
        }
        $tableData['rows_comment'] = $reshageData;
        $tableData['rows_show'] = $reshageData;
        $tableData['rows'] = $reshageData;
        $tableData['total'] = count($reshageData);

        return $tableData;

    }

    public function getReshapeTableData($tableData, $params)
    {
        $tableData['hide_dates'] = $this->getReshapeTableHideDateByRowsAndParams($tableData['rows'], $params);
        $dimensionMaps = $this->getDimensions($params);
        $metricEnameMaps = $this->getMetricEnameMaps($params);
        $metricEname2MetricCnameMaps = $this->getMetricEname2MetricCnameMaps($params);
        $tableData['rows'] = $this->getReshapeTableDataByRowsType(
            $tableData['rows'],
            $dimensionMaps,
            $metricEnameMaps,
            $metricEname2MetricCnameMaps
        );
        $tableData['rows_show'] = $this->getReshapeTableDataByRowsType(
            $tableData['rows_show'],
            $dimensionMaps,
            $metricEnameMaps,
            $metricEname2MetricCnameMaps
        );
        $tableData['rows_comment'] = $this->getReshapeTableDataByRowsType(
            $tableData['rows_comment'],
            $dimensionMaps,
            $metricEnameMaps,
            $metricEname2MetricCnameMaps
        );

        return $tableData;
    }

    public function getReshapeTableDownloadData($tableData, $params)
    {
        $dimensionMaps = $this->getDimensions($params);
        $metricEnameMaps = $this->getMetricEnameMaps($params);
        $metricEname2MetricCnameMaps = $this->getMetricEname2MetricCnameMaps($params, 'download');
        $tableData = $this->getReshapeTableDataByRowsType(
            $tableData,
            $dimensionMaps,
            $metricEnameMaps,
            $metricEname2MetricCnameMaps
        );

        return $tableData;
    }

    public function getReshapeTableDownloadHeader($rows)
    {
        if (count($rows) == 0) {
            return [];
        }
        $header = [
            'name' => [
                ''
            ],
            'key'  => [
                'metric_name'
            ]
        ];
        foreach ($rows as $row) {
            foreach ($row as $metric => $value) {
                if (!strtotime($metric) || $metric == 'metric_name') {
                    continue;
                }
                if (!in_array($metric, $header['name'])) {
                    array_push($header['name'], $metric);
                }
                if (!in_array($metric, $header['key'])) {
                    array_push($header['key'], $metric);
                }
            }
        }

        return $header;
    }

    public function getReshapeTableHideDateByRowsAndParams($rows, $params)
    {
        $allDate = [];
        $showDate = [];
        foreach ($rows as $row) {
            if (in_array($row['date'], $showDate)) {
                continue;
            }
            array_push($showDate, $row['date']);
        }
        if (count($showDate) == 0) {
            return [];
        }
        if ($params['date_type'] == 'month') {
            for ($i = $params['date']; $i != date('Y-m', strtotime($params['edate'] . ' + 1 month')); $i = date('Y-m', strtotime($i . ' + 1 month'))) {
                array_push($allDate, $i);
            }
        } else {
            for ($i = $params['date']; $i != date('Y-m-d', strtotime($params['edate'] . ' + 1 day')); $i = date('Y-m-d', strtotime($i . ' + 1 day'))) {
                array_push($allDate, $i);
            }
        }

        return array_values(array_diff($allDate, $showDate));
    }

    public function getReshapeTableDataByRowsType($rows, $dimensionMaps, $metricEnameMaps, $metricEname2MetricCnameMaps)
    {
        $dimensions = [];
        $newTableData = [];
        $reshapeTalbeData = [];
        foreach ($rows as $row) {
            foreach ($row as $metricKey => $metricValue) {
                if (!isset($metricEnameMaps[$metricKey])) {
                    if ($metricKey == 'date' || !in_array($metricKey, $dimensionMaps)) {
                        continue;
                    }
                    $dimensions[$metricKey] = $metricValue;
                    continue;
                }
                if (!isset($newTableData[$metricKey])) {
                    $newTableData[$metricKey] = [];
                }
                $newTableData[$metricKey]['metric_name'] = $metricEname2MetricCnameMaps[$metricEnameMaps[$metricKey]];
                $date = $row['date'];
                $newTableData[$metricKey][$date] = $metricValue;
            }
        }
        foreach ($newTableData as &$row) {
            foreach ($dimensions as $dimensionKey => $dimensionValue) {
                $row[$dimensionKey] = $dimensionValue;
            }
        }
        foreach ($metricEnameMaps as $metric => $value) {
            if (!isset($newTableData[$metric])) {
                continue;
            }
            array_push($reshapeTalbeData, $newTableData[$metric]);
        }

        return $reshapeTalbeData;
    }

    public function getMetricEname2MetricCnameMaps($params, $type = 'table')
    {
        $map = [];
        $indent = '&nbsp;&nbsp';
        if ($type == 'download') {
            $indent = "&nbsp;";
        }
        foreach ($params['grade']['data'] as $metric) {
            if ($metric['type'] == '维度') {
                continue;
            }
            if (isset($metric['indent_count'])) {
                if ($type == 'download') {
                    $metric['indent_count'] = $metric['indent_count'] / 2;
                }
                for ($i = 0; $i < $metric['indent_count']; $i++) {
                    $metric['name'] = $indent . $metric['name'];
                }
            }
            $map[$metric['key']] = $metric['name'];
        }

        return $map;
    }

    public function getDimensions($params)
    {
        $dimensions = [];
        foreach ($params['grade']['data'] as $metric) {
            if ($metric['type'] != '维度' || $metric['key'] == 'date') {
                continue;
            }
            array_push($dimensions, $metric['key']);
        }

        return $dimensions;
    }

    public function getMetricEnameMaps($params)
    {
        $maps = [];
        foreach ($params['grade']['data'] as $metric) {
            if ($metric['type'] == '维度' || $metric['key'] == 'date') {
                continue;
            }
            if ($metric['hide'] == 1) {
                continue;
            }
            $maps[str_replace('.', '_', $metric['key'])] = $metric['key'];
        }

        return $maps;
    }

    #自定义获取数据
    function actionGetDefineData()
    {
        //header("Content-type: application/json");
        $result = $_REQUEST;
        $params = json_decode($result['datas'], true);
        foreach ($result as $key => $value) {
            if ($key != 'datas') {
                $params[$key] = $value;
            }
        }
        $params['offset'] = $result['rows'];

        if (!isset($params['page']) || !isset($params['rows']) || empty($params['rows'])) {
            $params['page'] = 1;
            $params['rows'] = $params['grade']['pubdata']['pagesize'];
        }
        //如果不设置分页，不取总数
        if (isset($params['grade']['pubdata']['ispagesize']) && $params['grade']['pubdata']['ispagesize'] == 0) {
            $params['total'] = 0;
        }

        $data = $this->objFackcube->getData($params, true);

        //获取colums
        $params2 = $params;
        $params2['check'] = 1;
        $data2 = $this->objFackcube->getData($params2, true);

        $group = array();
        if (isset($data2['colums']) && sizeof($data2['colums']) > 0) {
            foreach ($data2['colums'] as $k => $dim) {
                if ($dim['type'] == 'dim' && $k != 'cdate')
                    $group[] = $k;
            }
        }
        $group = implode(',', $group);
        $data['group'] = $group;


        $otherlink = array();
        $imglink = array();
        if (is_array($params['grade']['data'])) {
            foreach ($params['grade']['data'] as $linkkey => $linkvalue) {
                //外链功能
                if ($linkvalue['otherlink'] != '-' && $linkvalue['otherlink'] != '') {
                    $urlkey = implode("_", explode(".", $linkvalue['key']));
                    $otherlink[$urlkey] = $linkvalue['otherlink'];
                }
                //图片显示功能
                if ($linkvalue['img_link'] != '-' && $linkvalue['img_link'] != '') {
                    $urlkey = implode("_", explode(".", $linkvalue['key']));
                    $imglink[$urlkey] = $linkvalue['img_link'];
                }
            }
        }

        if (!empty($data['data'])) {
            $gardData = $this->Contrast->definedgrade($data, $otherlink, $imglink);
        } else {
            $gardData = array();
        }

        $tableData['total'] = $data['total'];
        $tableData['rows'] = $gardData;
        $tableData['msg'] = $data['msg'];
        $tableData['status'] = $data['status'];
        $tableData['showMsg'] = $data['showMsg'];
        $tableData['relyMsg'] = $data['relyMsg'];
        $tableData['group'] = $data['group'];

        if (!empty($data['data'])) {
            $keyarr = array_keys($data['data'][0]);
            $key = $keyarr[0];
            //to trick
            if ($tableData['status'] != 0) {
                $tableData['rows'][0][$key] = $tableData['msg'];
            }
        }

        $this->common->addUserRequestToLog($tableData);

        echo json_encode($tableData);
    }


    #对比查询界面
    function actionContrastSearch()
    {
        $params = $_REQUEST['configall'];
        $configData = json_decode(rawurldecode($params['config']), true);
        foreach ($configData as $key => $val) {
            $configData[$key]['source']['date'] = $params['date'];
            $configData[$key]['source']['edate'] = $params['edate'];
        }
        $data = $this->objReport->deriveReport($configData, false);
        $setting = $this->chart->settingDecode($_POST['srcSecting']);
        $setting['date_from'] = $params['date'];
        $setting['date_to'] = $params['edate'];
        $chartInfo = $this->chart->setConfig($setting);

        if (!empty($data)) {
            $data['easyInfo'] = json_decode($data['easyInfo'], true);
            //$data['chartData'] = json_decode($data['chartData'],true);
            $data['chartInfo'] = $chartInfo;
            $this->jsonOutPut(0, 'success', $data);
        } else {
            $this->jsonOutPut(1, '数据为空');
        }
    }

    #对比下载
    function actionContrastDown()
    {
        $params = $_REQUEST['downConfig'];
        $params = json_decode($params, true);
        $configData = json_decode(rawurldecode($params['config']), true);
        foreach ($configData as $key => $val) {
            $configData[$key]['source']['date'] = $params['date'];
            $configData[$key]['source']['edate'] = $params['edate'];
        }
        $data = $this->objReport->deriveReport($configData, false);
        $easyInfo = json_decode($data['easyInfo'], true);
        $headerName = $easyInfo['easyHeader'];
        $titleArr = array_values($headerName);
        array_unshift($titleArr, '时间');
        $rowArr = array_keys($headerName);
        array_unshift($rowArr, 'dt');

        $filename = $_REQUEST['report_title'] . "_"
            . date("Ymd", strtotime($params['date'])) . "_"
            . date("Ymd", strtotime($params['edate'])) . ".xls";

        $this->common->exportHtml($titleArr, $rowArr, $easyInfo['easyData']['rows'], $filename);
    }

    #对比显示页面
    function actionContrast()
    {
        $objAuth = new AuthManager();
        if (Yii::app()->user->isProducer()) {
            $data['analyst'] = 1;
        } else {
            $data['analyst'] = 0;
        }
        //allcontent传递
        $allcontent = '';
        if (isset($_REQUEST['allcontent'])) {
            $allcontent = $_REQUEST['allcontent'];
        }
        $data['allcontent'] = $allcontent;


        //对比报表只有endtime没有starttime,要进行特殊处理
        if (isset($_REQUEST['endTime']) && !isset($_REQUEST['startTime'])) {
            $end = $_REQUEST['endTime'];
            $_REQUEST['startTime'] = date("Y-m-d", strtotime("$end - 7 days"));
        }

        $data['startTime'] = $_REQUEST['startTime'] ? $_REQUEST['startTime'] : date("Y-m-d", strtotime('-7 day'));
        $data['endTime'] = $_REQUEST['endTime'] ? $_REQUEST['endTime'] : date("Y-m-d", strtotime('-1 day'));
        $data['keysCon'] = json_decode($_REQUEST['keysCon'], true);
        $data['origin_keysCon'] = $_REQUEST['keysCon'];
        foreach ($data['keysCon'] as $k => &$v) {
            $v['data'] = urldecode($v['data']);
        }
        $keyArr = $data['keysCon'];
        $groupInfo = array();
        $chartInfo = array();
        foreach ($keyArr as $key => $value) {
            $one = array();
            $contrastInfo = json_decode($value['data'], true);
            //判断type
            if (isset($contrastInfo['sql']) && $contrastInfo['sql'] != '') {
                $contrastInfo['type'] = 8;
            } else {
                $contrastInfo['order'] = 'asc';
            }
            $contrastInfo['date'] = $data['startTime'];
            $contrastInfo['edate'] = $data['endTime'];
            $contrastInfo['sort'] = 'date';
            if (isset($contrastInfo['search']) && $contrastInfo['search'] == '') {
                unset($contrastInfo['search']);
            }

            if (!isset($contrastInfo['search']) && is_array($contrastInfo['filter'])) {
                $contrastInfo['search'] = json_encode($contrastInfo['filter']);
                $contrastInfo['filter'] = null;
            } else {
                if (!isset($contrastInfo['search'])) {
                    $contrastInfo['filter'] = json_encode($contrastInfo['filter']);
                } else {
                    //search与filter互换
                    $swap = $contrastInfo['search'];
                    $contrastInfo['search'] = json_encode($contrastInfo['filter']);
                    $contrastInfo['filter'] = $swap;
                }
            }

            $one['source'] = $contrastInfo;
            $one['name'] = $value['keyname'];
            $groupInfo[] = $one;
            $oneChart = $contrastInfo;
            //$metricKey = $this->objReport->getMetricName($one);
            $metricKey = $contrastInfo['showthis'];
            $oneChart['chartconf'][0]['chartData'][] = $metricKey;
            $oneChart['chartconf'][0]['chartKeys'][$metricKey] = $value['keyname'];
            $oneChart['chartconf'][0]['chartType'] = 'spline';
            $oneChart['chartconf'][0]['chartTitle'] = $value['keyname'];
            $oneChart['order'] = 'desc';
            $chartInfo[] = $oneChart;
        }
        $data['params'] = urlencode(json_encode($groupInfo));
        $dataInfo = $this->objReport->deriveReport($groupInfo, false);
        //生成图表
        $configSpine = array();
        $configSpine['chart_type'] = 'spline';
        $configSpine['chart_title'] = '';
        $configSpine['dataConig'] = $chartInfo;
        $html = $this->chart->getChartContiner($configSpine);
        $data['charthtml'] = $html;
        $data['easyInfo'] = $dataInfo['easyInfo'];
        $data['chartData'] = $dataInfo['chartData'];
        if ($_REQUEST['down'] == 1) {
            $dataInfo['easyInfo'] = json_decode($dataInfo['easyInfo'], true);
            $dataInfo['chartData'] = json_decode($dataInfo['chartData'], true);
            $headerName = $dataInfo['easyInfo']['easyHeader'];
            $easyData = $dataInfo['easyInfo']['easyData']['rows'];
            $titleArr = array_values($headerName);
            array_unshift($titleArr, '时间');
            $rowArr = array_keys($headerName);
            array_unshift($rowArr, 'dt');
            $filename = "data数据分析平台_"
                . date("Ymd", strtotime($data['startTime'])) . "_"
                . date("Ymd", strtotime($data['endTime'])) . ".xls";
            $this->common->exportHtml($titleArr, $rowArr, $easyData, $filename);
            exit;
        }

        $this->common->addUserRequestToLog($data);

        $this->render('visual/contrast.tpl', $data);
    }

    #获取指标
    function actionGetMetric()
    {

        $req['project'] = $_REQUEST['project'];
        $req['dim_set'] = $_REQUEST['dimensions'];
        echo json_encode($this->objFackcube->get_metric($req));
    }

    function actionToolGuider()
    {

        //面包屑效果
        $indexStr[] = array('href' => "/visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "#", 'content' => '常用工具');

        $tplArr['isauth'] = "false";
        //分析师的权限
        $objAuth = new AuthManager();
        if (Yii::app()->user->isProducer()) {
            $tplArr['isauth'] = "true";
        }

        //获取工具列表并按parent_id(分类)整理
        $menuList = $this->objVisual->selectMenu('all');
        $list = array();
        foreach ($menuList as $k => $v) {
            $list[$v['parent_id']][] = $v;
        }
        $tplArr['list'] = $list;

        //去掉无工具的menu
        $menu = $this->objVisual->selectparentmenu();
        foreach ($menu as $km => $vm) {
            if (!isset($list[$vm['id']])) {
                unset($menu[$km]);
            }
        }

        $tplArr['menu'] = $menu;

        $tplArr['guider'] = $indexStr;
        $this->render('visual/guider.tpl', $tplArr);
    }


    function actionToolGuiderList()
    {
        if (!Yii::app()->user->isProducer()) {
            echo '只有分析师才能管理工具哦~';
            exit();
        }
        //获取当前ur
        $menuList = $this->objVisual->selectMenu('all');
        $tplArr['list'] = $menuList;

        //面包屑效果
        $indexStr[] = array('href' => "index", 'content' => '首页');
        $indexStr[] = array('href' => "toolguider", 'content' => '常用工具');
        $indexStr[] = array('href' => "#", 'content' => '常用工具列表');
        $tplArr['isauth'] = "true";


        $tplArr['guider'] = $indexStr;
        $this->render('visual/guiderlist.tpl', $tplArr);
    }


    #配置报表
    function actionVisualConfig()
    {
        $project = $_REQUEST['project'];
        if (!$project) {
            echo "<script>alert('没有选择项目'); window.location.href ='/project/index'</script>";
        }
        $source = $_REQUEST['source'];
        if (!empty($source)) {
            $source = rawurldecode($source);
            //获取当前报表信息
            $tplArr['source'] = $source;
        }
        $curl = Yii::app()->curl;
        #获取项目配置
        $apiUrl = WEB_API . "/get_app_conf/?project=" . $project;
        $reJson = $curl->get($apiUrl);
        $tplArr['config'] = $reJson['body'];
        #获取所有维度组合
        $apiUrl = WEB_API . "/get_dimset/?project=" . $project;
        $reJson = $curl->get($apiUrl);
        $tplArr['dimensions'] = $reJson['body'];
        //面包屑效果
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "../project/index", 'content' => '管理工具');
        $indexStr[] = array('href' => "../project/index", 'content' => '项目管理');
        $indexStr[] = array('href' => "#", 'content' => '多维查询');

        $tplArr['guider'] = $indexStr;
        $this->render('visual/visualconfig.tpl', $tplArr);
    }

    public function actionToolGuiderAdd($id = '')
    {
        if (!Yii::app()->user->isProducer()) {
            $this->jsonOutPut(1, '只有分析师才能管理工具哦~');
            exit();
        }
        $tplArr['type'] = 'add';
        $tplArr['firstMenu'] = $this->objVisual->selectFirstMenu();
        //面包屑效果
        $indexStr[] = array('href' => "index", 'content' => '首页');
        $indexStr[] = array('href' => "toolguider", 'content' => '常用工具');
        $indexStr[] = array('href' => "#", 'content' => '添加工具');
        $tplArr['guider'] = $indexStr;
        $this->render('visual/guideradd.tpl', $tplArr);
    }

    public function actionEditool($id)
    {
        if (!Yii::app()->user->isProducer()) {
            $this->jsonOutPut(1, '只有分析师才能管理工具哦~');
            exit();
        }
        $tplArr['type'] = 'editor';
        $tplArr['id'] = $id;
        $tplArr['firstMenu'] = $this->objVisual->selectFirstMenu();
        $tplArr['menuInfo'] = $this->objVisual->selectMenu($id);
        $tplArr['menuInfo'] = json_encode($tplArr['menuInfo'][0]);
        //面包屑效果
        $indexStr[] = array('href' => "index", 'content' => '首页');
        $indexStr[] = array('href' => "toolguider", 'content' => '常用工具');
        $indexStr[] = array('href' => "#", 'content' => '编辑工具');

        $tplArr['guider'] = $indexStr;

        $this->render('visual/guideradd.tpl', $tplArr);
    }


    public function actionToolAddSave()
    {
        if (!Yii::app()->user->isProducer()) {
            $this->jsonOutPut(1, '只有分析师才能管理工具哦~');
            exit();
        }
        $params = $_REQUEST;
        $params['name'] = trim($params['name']);

        if (!empty($params['id'])) {
            //$updateArr = array('first_menu'=>$params['first_menu'],'second_menu'=>$params['second_menu'],'table_id'=>$arrTableid);
            $res = $this->objVisual->updateMenu($params);

        } else {
            $res = $this->objVisual->toolAddMenu($params);
            if (!empty($res))
                $res = True;
        }

        echo $this->jsonOutPut($res);

    }

    public function actionToolSort()
    {
        if (!Yii::app()->user->isProducer()) {
            $this->jsonOutPut(1, '只有分析师才能管理工具哦~');
            exit();
        }
        $tplArr['menuinfo'] = $this->objVisual->selectFirstMenu();
        //面包屑效果
        $indexStr[] = array('href' => "index", 'content' => '首页');
        $indexStr[] = array('href' => "toolguider", 'content' => '工具列表');
        $indexStr[] = array('href' => "#", 'content' => '工具排序');

        $tplArr['guider'] = $indexStr;
        $this->render('visual/guidersort.tpl', $tplArr);
    }


    #下载报表
    function actionDownData()
    {
        set_time_limit(0);
        ini_set('memory_limit', '2048M');
        $params = $_REQUEST['downConfig'];
        $params = rawurldecode($params);
        $params = json_decode($params, true);
        //$params['getDataType'] = 'tableDownload';
        $userName = Yii::app()->user->username;
        $fileNamePrefix = 'unknown';
        if (is_string($userName) && strpos($userName, '@') !== false) {
            $fileNamePrefix = trim(strstr($userName, '@', true));
        }
        $fileNamePrefix = base64_encode($fileNamePrefix);
        $dataArray = [];
        $item_title = '';
        foreach ($params as $key => $items) {
            if (isset($items['type']) && $items['type'] != 2) {
                $dataArray[$key]['getDataType'] = 'tableDownload';
                $headerArr = array();
                $headerArr = $this->objVisual->getNormalHeader($items);
                //获取下载的信息
                $items['rows'] = 1000000;

                $data = $this->objFackcube->getData($items, false, false);

                //替换注释
                $obj = new ProjectManager();
                $commentRes = $obj->getProjectComment($items['project']);
                if ($commentRes != false) {
                    $commentRes = json_decode($commentRes, true);
                    foreach ($data['data'] as $k => $v) {
                        foreach ($v as $subk => $subv) {
                            if (isset($commentRes[$subk]) && isset($commentRes[$subk]['content'][$subv])) {
                                $data['data'][$k][$subk] = array();
                                if ($commentRes[$subk]['isReplace'] == '1') {
                                    $data['data'][$k][$subk] = $commentRes[$subk]['content'][$subv];
                                } else if ($commentRes[$subk]['isReplace'] == '2') {
                                    $data['data'][$k][$subk] = $subv . '(' . $commentRes[$subk]['content'][$subv] . ')';
                                }
                            }
                        }

                    }
                }

                if (isset($data['data'])) {
                    //百分位添加(表项设置都可以在此变动)
                    foreach ($data['data'] as $k => $v) {
                        foreach ($v as $subk => $subv) {
                            foreach ($items['grade']['data'] as $pa_k => $pa_v) {
                                if ($subk == $pa_v['key'] && $pa_v['percent'] == 1 && is_numeric($data['data'][$k][$subk])) {
                                    $data['data'][$k][$subk] = $data['data'][$k][$subk] . '%';
                                }
                            }
                        }

                    }
                }
                if (isset($items['grade']['pubdata']['reshape']) && $items['grade']['pubdata']['reshape'] == 1) {
                    $data['data'] = $this->getReshapeTableDownloadData($data['data'], $items);
                    $headerArr = $this->getReshapeTableDownloadHeader($data['data']);
                }
                if (!empty($data['data'])) {
                    /*$filename = $_REQUEST['report_title']
                        . date("Ymd", strtotime($items['date'])) . "_"
                        . date("Ymd", strtotime($items['edate'])) ;//. '_' . $this->getDownFileNameUserEmailEncodeSuffix() . ".xls";
                    $this->common->exportHtml($headerArr['name'], $headerArr['key'], $data['data'], $filename);*/
                    $dataArray[$key]['name'] = $headerArr['name'];
                    $dataArray[$key]['key'] = $headerArr['key'];
                    $dataArray[$key]['data'] = $data['data'];
                    $dataArray[$key]['title'] = $items['title'];
                    $item_title = $items['title'];
                }
            } elseif (isset($items['type'])) {
                $dataArray[$key]['getDataType'] = 'tableDownload';
                $inter = 8;
                $items['date'] = date("Y-m-d", strtotime($items['edate']) - 86400 * $inter);
                $res = $this->objFackcube->getData($items);
                if ($res['status'] != 0) {
                    echo $res;
                    exit();
                }
                $data = $this->objVisual->getContrast($items, $res);
                //获取表头
                $headerArr = $this->objVisual->getContHeader($items);
                /*$filename = $_REQUEST['report_title']
                    . date("Ymd", strtotime($items['date'])) . "_"
                    . date("Ymd", strtotime($items['edate'])) . '_' . $this->getDownFileNameUserEmailEncodeSuffix() . ".xls";*/
                //$this->common->exportHtml($headerArr['name'], $headerArr['key'], $data, $filename);
                $dataArray[$key]['name'] = $headerArr['name'];
                $dataArray[$key]['key'] = $headerArr['key'];
                $dataArray[$key]['data'] = $data;
                $dataArray[$key]['title'] = isset($items['title']) ? $items['title'] : '';
                $item_title = $items['title'];
            }
        }
        if (count($dataArray) > 1) {
            $dataArray['filename'] = date('Ymd') . $this->getDownFileNameUserEmailEncodeSuffix() . ".xls";
        } else {
            $dataArray['filename'] = $item_title . $this->getDownFileNameUserEmailEncodeSuffix() . ".xls";
        }
        $this->common->exportAllHtml($dataArray);
        /*
        if($params['type'] !=2 ){
            // $gardConfig = $this->objVisual->getTableConfig($params);
            // $headerArr = $this->objVisual->getDownHeader($gardConfig);
            // echo "<pre>";
            // print_r($headerArr);exit;
            $headerArr = array();
            // if( isset($params['grade']['data']) && !empty($params['grade']['data'])){
            //     $downArr = $params['grade']['data'];
            //     foreach ($downArr as $key => $item) {
            //         if(!$item['hide']){
            //             $tmp = implode("_", explode(".", $item['key']));
            //             $headerArr['key'][]  =  strtolower($tmp);
            //             $headerArr['name'][] =  $item['name'];
            //         }
            //     }
            // }else{
            //     $gardConfig = $this->objVisual->getTableConfig($params);
            //     $headerArr = $this->objVisual->getDownHeader($gardConfig);
            // }
            $headerArr = $this->objVisual->getNormalHeader($params);
            //获取下载的信息
            $params['rows'] = 1000000;

            $data = $this->objFackcube->getData($params,false,false);


            //替换注释
            $obj=new ProjectManager();
            $commentRes=$obj->getProjectComment($params['project']);
            if($commentRes!=false){
                $commentRes=json_decode($commentRes,true);
                foreach($data['data'] as $k=>$v){
                    foreach($v as $subk=>$subv){
                        if(isset($commentRes[$subk]) && isset($commentRes[$subk]['content'][$subv])) {
                            $data['data'][$k][$subk] = array();
                            if ($commentRes[$subk]['isReplace'] == '1') {
                                $data['data'][$k][$subk] = $commentRes[$subk]['content'][$subv];
                            } else if ($commentRes[$subk]['isReplace'] == '2') {
                                $data['data'][$k][$subk] = $subv . '(' . $commentRes[$subk]['content'][$subv] . ')';
                            }
                        }
                    }

                }
            }

            if(isset($data['data'])) {
                //百分位添加(表项设置都可以在此变动)
                foreach ($data['data'] as $k => $v) {
                    foreach ($v as $subk => $subv) {
                        foreach ($params['grade']['data'] as $pa_k => $pa_v) {
                            if ($subk == $pa_v['key'] && $pa_v['percent'] == 1 && is_numeric($data['data'][$k][$subk])) {
                                $data['data'][$k][$subk] = $data['data'][$k][$subk] . '%';
                            }
                        }
                    }

                }
            }
            if (isset($params['grade']['pubdata']['reshape']) && $params['grade']['pubdata']['reshape'] == 1) {
                $data['data'] = $this->getReshapeTableDownloadData($data['data'], $params);
                $headerArr = $this->getReshapeTableDownloadHeader($data['data']);
            }
            if ($params['type'] == 10) {
                $headerArr = $this->getCrossTableDownloadHeader($data['data'], $params);
                $data['data'] = $this->getCrossTableDownloadData($data['data'], $params);
            }
            if (!empty($data['data'])) {
                $filename = $_REQUEST['report_title']
                    . date("Ymd", strtotime($params['date'])) . "_"
                    . date("Ymd", strtotime($params['edate'])) . '_' . $this->getDownFileNameUserEmailEncodeSuffix() . ".xls";
                $this->common->exportHtml($headerArr['name'], $headerArr['key'], $data['data'], $filename);
            } else {
                echo $data['msg'];
            }
        }else{
            $inter=8;
            $params['date']=date("Y-m-d",strtotime($params['edate'])-86400*$inter);
            $res=$this->objFackcube->getData($params);
            if($res['status']!=0){
                echo $res;
                exit();
            }
            $data=$this->objVisual->getContrast($params,$res);
            //获取表头
            $headerArr = $this->objVisual->getContHeader($params);
            $filename = $_REQUEST['report_title']
                . date("Ymd", strtotime($params['date'])) . "_"
                . date("Ymd", strtotime($params['edate'])) . '_' . $this->getDownFileNameUserEmailEncodeSuffix() . ".xls";
            $this->common->exportHtml($headerArr['name'], $headerArr['key'], $data, $filename);
        }*/

    }


    function actionReportSitemap()
    {
        $menuObj = new MenuManager();
        //获取数据
        $menu_id = $_REQUEST['menu_id'] ? $_REQUEST['menu_id'] : 0;
        $id = $_REQUEST['id'] ? $_REQUEST['id'] : 0;
        $userName = Yii::app()->user->username;
        $menuSensitiveInfo = $menuObj->getMenu_admin();
        $menuInfo = $menuObj->getMenu();
        $menuInfo_auth = $menuObj->getMenu();

        //报表显示顺序
        if (!empty($menu_id)) {
            foreach ($menuInfo['menuTitle'] as $first_menu => $secondmenuinfo) {
                foreach ($secondmenuinfo as $second_menu_id => $menuinfo) {
                    if ($second_menu_id == $menu_id) {
                        $nowMenuinfo = $menuinfo;
                    }
                }
            }
        } else if (!empty($menuInfo['collect']) && empty($id)) {
            $id = key($menuInfo['collect']);
        }
        $menu_arr = $menuSensitiveInfo['menuTitle'];
        $menu_auth_arr = $menuInfo_auth['menuTitle'];
        foreach ($menu_arr as $key => $menu) {
            if (isset($menu[1151])) {
                unset($menu_arr[$key]);
                continue;
            }
        }
        //获取权限
        foreach ($menu_arr as $first_k => &$first_v) {
            foreach ($first_v as $second_k => &$second_v) {
                foreach ($second_v['table'] as $tk => &$tv) {
                    if (@in_array($tv, $menu_auth_arr[$first_k][$second_k]['table'])) {
                        $tv['auth'] = '1';
                    } else {
                        $tv['auth'] = '0';
                    }
                }
            }
        }
        $tplArr = $menuInfo['menuTitle'];
        $tplArr['menuSensitiveTitle'] = $menu_arr;

        $reportauth = true;
        if ($id > 0) {
            if (!empty($menu_id)) {
                $reportauth = false;
                if (!empty($nowMenuinfo)) {
                    foreach ($nowMenuinfo['table'] as $tmp) {
                        if ($tmp['id'] == $id) {
                            $reportauth = true;
                            break;
                        }
                    }
                }
            } else {
                $reportauth = $this->objuser->checkAuthFromMenu($id, $userName);
            }

            // $reportauth=$this->__checkAuth($id);
            if ($reportauth == true) {
                $newArr = $this->objReport->showReport($id);

                if (!empty($newArr))
                    $tplArr = array_merge($newArr, $tplArr);
            }
        }
        $tplArr['reportauth'] = $reportauth;
        $tplArr['url'] = 'test';
        if (!empty($_REQUEST['allcontent'])) {
            $tplArr['allcontent'] = $_REQUEST['allcontent'];

        }

        $tplArr['WEB_API'] = WEB_API;
        $this->render('visual/report.tpl', $tplArr);

    }

    function getDownFileNameUserEmailEncodeSuffix()
    {
        return base64_encode(Yii::app()->user->username);
    }

    function actionRun_task()
    {

        $data = $_REQUEST;
        $data['ext_json'] = json_encode(['step' => 'all']);
        $res = $this->objFackcube->get_fakecube('run_task', $data);
        echo json_encode($res);
    }

    public function actionGetTool()
    {
        $sort = $_REQUEST['sort'];
        if (empty($sort))
            return;
        $res = $this->objVisual->selectSecnodMenu($sort);
        echo $this->jsonOutPut(0, '', $res);

    }

    public function actionSaveSortTool()
    {

        //$first_menu=$_REQUEST['parent_id'];
        $sortinfo = $_REQUEST['sortinfo'];
        $res = $this->objVisual->saveSortMenu($sortinfo);

        $this->jsonOutput($res);

    }

    //添加工具分类菜单
    public function actionAddToolClassifyName()
    {
        if (!Yii::app()->user->isProducer()) {
            $this->jsonOutPut(1, '只有分析师才能管理工具哦~');
            exit();
        }

        if (!isset($_REQUEST['name']) || empty($_REQUEST['name'])) {
            $this->jsonOutPut(1, 'name为空');

            return;
        }
        if (!isset($_REQUEST['content']) || empty($_REQUEST['content'])) {
            $this->jsonOutPut(1, 'content为空');

            return;
        }
        $name = $_REQUEST['name'];
        $content = $_REQUEST['content'];

        $ret = $this->objVisual->addToolClassifyName($name, $content, $message);

        $this->jsonOutPut($ret ? 0 : 1, $message);
    }
}
