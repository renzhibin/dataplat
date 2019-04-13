<?php

class  VisualManager extends Manager
{

    #获取数据接口
    /**
     *$params['project']
     *$params['group']
     *$params['metric']
     *$params['date']
     *$params['udc']
     */
    public $tableName;
    public $db;
    public $id2name;
    public $project;
    public $id2all;
    public $authId2name;
    public $superId2name;

    function __construct()
    {


        $this->tableName = "t_visual_table";
        $this->db = Yii::app()->sdb_metric_meta;

        $this->objFackecube = new FackcubeManager();


    }

    function  __orderColumn($order, $gardConfig)
    {

        $formergeArr = array();
        $fixed = array();
        $header = array();
        foreach ($gardConfig as $k => $v) {
            if (!($k == 'fiexd' or $k == 'header'))
                continue;

            foreach ($v as $suk => $subv) {

                $subv['name'] = strtolower($subv['name']);
                $formergeArr[$subv['name']] = $subv;
                if ($k == 'fiexd') {
                    $fixed[] = $subv['name'];
                } elseif ($k == 'header') {
                    $header[] = $subv['name'];
                }
            }

        }

        foreach ($order as $v) {
            $v = strtolower(str_replace('.', '_', $v));
            if (in_array($v, $fixed)) {
                $retu['fiexd'][] = $formergeArr[$v];
            }
            if (in_array($v, $header)) {
                $retu['header'][] = $formergeArr[$v];
            }

        }
        return $retu;


    }
    #获取表头
    function getDownHeader($gardConfig)
    {
        $headerArr = array();
        if(isset($gardConfig['fiexd']) && !empty($gardConfig['fiexd'])){
            foreach ($gardConfig['fiexd'] as $key => $value) {
                $headerArr['key'][] = $value['name'];
                if (!empty($value['cn_name'])) {
                    $headerArr['name'][] = $value['cn_name'];
                } else {
                    $headerArr['name'][] = $value['name'];
                }
            }
        }
        foreach ($gardConfig['header'] as $key => $value) {
            $headerArr['key'][] = $value['name'];
            if (!empty($value['cn_name'])) {
                $headerArr['name'][] = $value['cn_name'];
            } else {
                $headerArr['name'][] = $value['name'];
            }
        }
        return $headerArr;
    }
    /**
     * 新旧名称替换问题
     */
    public function nameReplace($configTable) {
        $config = $this->objFackecube->get_app_conf(array('project' => $configTable['project']), TRUE);
        $projectInfo = $config['data']['project'][0]['categories']; 
        $groupArr    = explode(",",$configTable['group']);
        $metricArr    = explode(",",$configTable['metric']);
        $tableInfo =[];
        $tableInfo['group'] = [];
        $tableInfo['metric'] = [];
        $tmpG =$tmpM =[];
        foreach ($projectInfo as $key=>$project){
            foreach ($project['groups'] as $group){
                //处理维度
                $groups = $group['dimensions'];
                foreach ($groups as $list){
                    if(in_array($list['name'], $groupArr) && !in_array($list['name'],$tmpG)  ){
                        $one =[];
                        $one['key'] = $list['name'];
                        $one['name'] = $list['cn_name'];
                        $one['explain'] = $list['explain'];
                        $tableInfo['group'][] = $one;
                        $tmpG[] = $list['name'];
                    }
                }
                //处理指标
                $metric  = $group['metrics'];
                foreach ($metric as $mlist){
                    $tmpkey = $project['name'].".".$group['name'].".".$mlist['name'];
                    if(in_array($tmpkey, $metricArr) && !in_array($tmpkey,$tmpM)  ){
                        $one =[];
                        $one['key'] =  $tmpkey;
                        $one['name'] = $mlist['cn_name'];
                        $one['explain'] = $mlist['explain'];
                        $tableInfo['metric'][] = $one;
                        $tmpM[] = $tmpkey;
                    }
                }
            }
        }   
                
        //获取维度
        foreach($configTable['grade']['data'] as $key =>$data){
            foreach ($tableInfo['group'] as $item){
                if($data['key'] == $item['key']){
                    $configTable['grade']['data'][$key]['key'] = $item['key'];
                    $configTable['grade']['data'][$key]['name'] = $item['name'];
                    $configTable['grade']['data'][$key]['explain'] = $item['explain'];
                }
            }
                
            foreach ($tableInfo['metric'] as $item){
                if($data['key'] == $item['key']){
                    $configTable['grade']['data'][$key]['key'] = $item['key'];
                    $configTable['grade']['data'][$key]['name'] = $item['name'];
                    $configTable['grade']['data'][$key]['explain'] = $item['explain'];
                }
            }
        }   
        return $configTable;

    }
    #处理普通表头
    function getNormalHeader($params){
        $headerArr = array();
        if( isset($params['grade']['data']) && !empty($params['grade']['data'])){
            $downArr = $params['grade']['data'];
            $tableType = $params['type'];
            foreach ($downArr as $key => $item) {
                if(!$item['hide'] && $item['key'] !='all'){
                    $tmp = implode("_", explode(".", $item['key'])); 
                    $headerArr['key'][]  =  strtolower($tmp);
                    $headerArr['name'][] =  $item['name'];
                    if ($tableType == 1 && isset($params['getDataType']) && isset($item['sum_ratio']) && $item['sum_ratio'] == 1) {
                        $headerArr['key'][]  =  strtolower($tmp) . '_sum_percent';
                        $headerArr['name'][] =  $item['name'] . '占比';
                    }
                }
            }
        }else{
            $gardConfig = $this->getTableConfig($params);
            $headerArr = $this->getDownHeader($gardConfig);
        }
        $headerArr['info'] = $this->getNormalHeaderInfo($params);

        return $headerArr;
    }
    #处理表头的相关信息
    function getNormalHeaderInfo($params) {
        $headInfo = [];

        if(isset($params['grade']['data'])) {
            foreach ($params['grade']['data'] as $item) {
                $headInfo[str_replace('.', '_', $item['key'])] = $item;
            }
        }

        return $headInfo;
    }
    #处理对报表表头
    function getContHeader($params){
        $headerArr = array();
        if(isset($params['contrast'])){
            foreach ($params['contrast'] as $k => $v) {
                $headerArr['key'][] = $v['key'];
                $headerArr['name'][] = $v['name'];
            }
            array_unshift($headerArr['name'],'指标名称');
            array_unshift($headerArr['key'],'name');
        }else{
            foreach ($params['grade']['contrast']['data'] as $key => $value) {
                $headerArr['name'][] = $value['name'];
                $headerArr['key'][] = $value['key'];
            }
            array_unshift($headerArr['name'],'指标名称');
            array_unshift($headerArr['key'],'name');
            //处理多维数据
            $searchMap = array();
            if(!empty($params['grade']['data'])){
                 $isSearchArr = $params['grade']['data'];
                 foreach ($isSearchArr as $key => $val){
                    if(is_array($val['search'])){
                        if($val['search']['reportdimensions'] ==1){
                            $tmpsearch = explode("\n", $val['search']['val']);
                            foreach ($tmpsearch as $tmpkey) {
                               $tmpArr = explode(":", $tmpkey);
                               $searchMap[trim($tmpArr[0])] = trim($tmpArr[1]); 
                            }
                            $dimKey =  $val['search']['key'];
                            $dimName = $val['name'];
                        }
                    }
                 }
            }
            if(!empty($searchMap)){
                array_unshift($headerArr['name'],$dimName);
                array_unshift($headerArr['key'],$dimKey);
            }            
        }
        return $headerArr;
    }
    //名称处理
    function getOneDimData($data,$nameres,$params){
        $inter = 8;
        $params['date'] = date("Y-m-d", strtotime($params['edate']) - 86400 * $inter);
        $today = $params['edate'];
        $yesterday = date("Y-m-d", strtotime($today) - 86400);
        $lastweek = date("Y-m-d", strtotime($today) - 86400 * 7);
        if (empty($data)) {
            return array();
        }
        //转换成数组时间key 为格式
        foreach ($data as $v) {
            $tmpv = array();
            foreach ($v as $k => $subv) {
                $tmpv[$k] = $subv;
            }
            $date_res[$v['date']] = $tmpv;
        }
        $final_res = array();
        foreach ($params['sort'] as $k => $v) {
            $tempToday = $date_res[$today][$v];
            $tempYesterday = $date_res[$yesterday][$v];
            $tempLastweek = $date_res[$lastweek][$v];
            $final_res[$v]['yesterday_percent'] = ConstManager::getdivision(($tempToday - $tempYesterday), $tempYesterday);
            $final_res[$v]['lastweek_percent'] = ConstManager::getdivision(($tempToday - $tempLastweek), $tempLastweek);
            $avgweek = ConstManager::getMean($date_res, $v, $today, 7);

            //今日值不存在时 默认为不存在
            $tempToday = $tempToday!=null ? $tempToday : '未生成';
            $tempYesterday = $tempYesterday!=null ? $tempYesterday : '未生成';
            $tempLastweek = $tempLastweek!=null ? $tempLastweek : '未生成';

            //数字添加 千分隔
            $final_today = $this->common->thousandPoints($tempToday);
            $final_yesterday = $this->common->thousandPoints($tempYesterday);
            $final_lastweek = $this->common->thousandPoints($tempLastweek);
            $final_avgweek = $this->common->thousandPoints($avgweek);


            //添加百分比
            if (isset($params['percentArrTemp']) && in_array($v, $params['percentArrTemp'])) {
                $final_today = ($final_today == 0 || $final_today == '不存在') ? $final_today : $final_today . "%";
                $final_yesterday = ($final_yesterday == 0 || $final_yesterday == '不存在') ? $final_yesterday : $final_yesterday . "%";
                $final_lastweek = ($final_lastweek == 0 || $final_lastweek == '不存在') ? $final_lastweek : $final_lastweek . "%";
                $final_avgweek = ($final_avgweek == 0 || $final_avgweek == '不存在') ? $final_avgweek : $final_avgweek . "%";
            }

            $final_res[$v]['today'] = $final_today;
            $final_res[$v]['yesterday'] = $final_yesterday;
            $final_res[$v]['lastweek'] = $final_lastweek;
            $final_res[$v]['avgweek'] = $final_avgweek;

        }
        //补上名称
        $retu_res = array();
        foreach ($params['sort'] as $v) {

            $v = trim($v);
            if (!isset($nameres[$v])) {
                //    echo $v;
                continue;
            }
            unset($tmp);
            $tmp['true_name'] = $nameres[$v]['name'];
            $tmp['name'] = $nameres[$v]['cn_name'];
            foreach ($params['conkeys'] as $ck) {
                $tmp[$ck] = $final_res[$v][$ck];
            }
            $retu_res[] = $tmp;

        }
        return $retu_res;

    }
    function  getContrast($params, $res)
    {
        //兼容新数据逻辑
        $show = array();
        if(isset($params['contrast'])){
            $sort = json_decode(str_replace('.', '_', json_encode($params['grade']['sort'])));
            $arrconkey = $params['contrast'];
        }else{
            $sort =array();
            foreach ($params['grade']['data'] as $ts => $tsv) {
                if(!$tsv['hide']  && $tsv['isgroup'] !=1 ){
                    $sort[] =  $tsv['key'];
                }
                if($tsv['percent'] ==1){
                    $show['percentArrTemp'][] = $tsv['key'];
                }
            }
            $sort = json_decode(str_replace('.', '_', json_encode($sort)));

            $show['percentArrTemp'] = json_decode(str_replace('.', '_', json_encode($show['percentArrTemp'])));
            $arrconkey = $params['grade']['contrast']['data'];
        }   
        //获取列信息
        $conkeys = array();
        foreach ($arrconkey as $tmp) {
            $conkeys[] = $tmp['key'];
        }

        $show['edate'] = $params['edate'];
        $show['sort'] = $sort;
        $show['conkeys'] = $conkeys;
        //获取指标信息
        $objProject = new ProjectManager();
        $nameres = $objProject->getMetricandGroup($params['project'], false, true);
        //补上Udc信息
        $data = $res['data'];
        if (!empty($params['udcconf'])) {
            $udcconf = json_decode(rawurldecode($params['udcconf']), true);
            foreach ($udcconf as $v) {
                $nameres[$v['name']] = $v;
            }
        }
        //处理多维数据
        $searchMap = array();
        if(!empty($params['grade']['data'])){
             $isSearchArr = $params['grade']['data'];
             foreach ($isSearchArr as $key => $val){
                if(is_array($val['search'])){
                    if($val['search']['reportdimensions'] ==1){
                        $tmpsearch = explode("\n", $val['search']['val']);
                        foreach ($tmpsearch as $tmpkey) {
                           $tmpArr = explode(":", $tmpkey);
                           $searchMap[trim($tmpArr[0])] = trim($tmpArr[1]); 
                        }
                        $dimKey =  $val['search']['key'];
                    }
                }
             }
        }
        if(!empty($searchMap)){
            $contrastArr = array();
            foreach ($searchMap as $k => $v) {
                //获取当前维度的数据
                $oneSearch = array();
                //print_r($data);exit;
                foreach ($data as  $item) {
                    if($item[$dimKey] == $k){

                        //判断是否是all 或者0
                        //在php 中  all ==0 永远为true
                        if( empty($item[$dimKey]) == empty($k) ){
                            $oneSearch[] = $item;
                        }

                    }
                }
                //获取当前数据
                $oneReal = $this->getOneDimData($oneSearch,$nameres,$show);
                foreach( $oneReal as $dk => $ritem){
                    $oneReal[$dk][$dimKey] = $k;
                }
                $contrastArr =  array_merge($contrastArr,$oneReal);
            }
            return $contrastArr;
        }else{
            return $this->getOneDimData($data,$nameres,$show);
        }
    }

    function getTableConfig($params)
    {

        //2015-06-19 时间问题 普通报表默认显示showsort 和 sort中 增加维度
        if (isset($params['grade']) && !isset($params['grade']['isfiexd'])) {

            if (is_array($params['grade']['sort']) && !in_array('date', $params['grade']['sort'])) {
                array_unshift($params['grade']['sort'], 'date');
            }
            if (is_array($params['grade']['showsort']) && !in_array('date', $params['grade']['showsort'])) {
                array_unshift($params['grade']['showsort'], 'date');
            }
        }
        //   Yii::trace('getTableparams'.CVarDumper::dumpAsString($params));

        //或者表头 格式为header 包括各个指标和新增列  fixed 包括时间和维度
        $gardConfig = $this->getVisualHeader($params);//返回带cnname explain 伪代码 和udc列的信息，按是否固定分fix、header两个数组
        // print_r($gardConfig);
        if (empty($gardConfig)) {
            return array();
        }

        //为了排序
        if (!empty($params['grade']['sort'])) {

            $gardConfig = $this->__orderColumn($params['grade']['sort'], $gardConfig);
        }

        $percentArr = array();
        if (!empty($params['grade']['percent'])) {
            foreach ($params['grade']['percent'] as $tmp) {
                $percentArr[] = str_replace('.', '_', $tmp);
            };
        };
        //为了百分比
        if (!empty($gardConfig['header'])) {

            foreach ($gardConfig['header'] as $k => $v) {
                $tmppercent = 0;
                if (in_array($v['name'], $percentArr)) {
                    $tmppercent = 1;
                }
                $v['percent'] = $tmppercent;
                $gardConfig['header'][$k] = $v;

            }
        }
        // Yii::trace('getHeaderparams'.CVarDumper::dumpAsString($gardConfig));
        $params['filter'] = json_encode($params['filter']);
        $gardConfig['url'] = '/visual/getData';//获取数据的链接
        //$gardConfig['url'] = '/visual/getData?'.http_build_query($params);//获取数据的链接
        $gardConfig['down'] = '/visual/downData?' . 'rsv_pq=' . $_REQUEST['rsv_pq'];//下载链接
        $gardConfig['downConfig'] = rawurlencode(json_encode($params));
        //去重 date
        if (isset($gardConfig['fiexd'])) {
            $gardConfig['fiexd'] = $this->common->arrayUnique($gardConfig['fiexd'], 'name');
        }

        return $gardConfig;
    }
    /*
    获取多条曲线
    */
    function getSplineData($realData,$group,$sVal,$rangeDate,$xais=array()){
        $groupVal = array();
        foreach ($realData as $time => $itemData) {
            $keyArr = array();
            $valArr = array();
            if(!empty($group)){
                foreach ($group as  $keyVal) {
                    if(!empty($xais)){
                        if(in_array($keyVal, $xais)){
                           continue;
                        }
                    }
                    if(is_array( $itemData[$keyVal]) ){
                        $keyArr[] = $keyVal ."$".$itemData[$keyVal]['commentdata'];
                    }else{
                        $keyArr[] = $keyVal ."$".$itemData[$keyVal];
                    }
                    $valArr[] = $itemData[$keyVal];
                }
                $checkKey = implode('@', $keyArr);
                if(!isset($groupVal[$checkKey])){
                    $groupVal[$checkKey] = $valArr;
                }
            }else{
                return array();
            }
        }
        $allGroupData = array();
        foreach ($groupVal as $gkey => $gVak) {
            $sp = array();
            foreach($realData as $it => $v) {
                $temArr =array();
                foreach ($group as  $keyVal) {
                    if(!empty($xais)){
                        if(in_array($keyVal, $xais)){
                           continue;
                        }
                    }
                    if( is_array($v[$keyVal])){
                        $temArr[] = $keyVal ."$".$v[$keyVal]['commentdata'];
                    }else{
                        $temArr[] = $keyVal ."$".$v[$keyVal];
                    }
                }
                $tmpCheck = implode('@',$temArr);  
                if($tmpCheck == $gkey){
                    $one =array();
                    if(!empty($xais)){
                        $xarr = array();
                        foreach ($xais as $t => $tv) {
                             $xarr[] = $v[$tv];
                        }
                        $one['name'] =  implode(",", $xarr);
                    }else{
                         $one['name'] = $v['date'];
                    }
                    $one['value'] = $v[strtolower($sVal)];
                    $sp[] = $one;
                }
            }
            $sp = $this->common->arrSort($sp,'name','asc');
            if(empty($xais)){
                $temMerge = $this->checkData($sp,$rangeDate);
            }else{
                $temMerge =$sp;
            }
            $allGroupData[$gkey] = $temMerge;
        }
        return $allGroupData;
    }

    /*
    检测数据完整性
    */
    function checkData($sp,$rangeDate){
        $newSp = array();
        foreach ($sp as $skey => $dateItem) {
            $newSp[$dateItem['name']] = $dateItem;
        }
        $temMerge = array();
        foreach ($rangeDate as $date) {
            $one['name'] = $date;
            if(isset($newSp[$date]['value']) && is_string($newSp[$date]['value']) && trim($newSp[$date]['value']) =='不存在' ){
//                $one['value'] = '-';
                $one['value'] = '0';
            }else{
                $one['value'] = $newSp[$date]['value'];
            }
            $temMerge[] = $one;
        }
        return $temMerge;
    }
    function  getChart($params)
    {
        $isCustomChart = false;
        if (isset($params['grade']['sql'])) {
            $isCustomChart             = true;
            $params['group']           = $params['grade']['group'];  # 兼容 table 的取数逻辑
            $params['metric']          = $params['grade']['metric']; # 兼容 table 的取数逻辑
            $params['title']           = '获取自定义chart数据'; # 兼容 table 的取数逻辑
            $params['type']            = 8; # 兼容 table 的取数逻辑
            $params['page']            = 1; # 兼容 table 的取数逻辑
            $params['rows']            = 1000000; # 不分页
            $params['offset']          = 1000000; # 不分页
            $params['total']           = 0; # 不获取总数
            $params['sql']             = $params['grade']['sql'];
            $params['customsql_start'] = $params['grade']['customsql_start'];
            $params['search']          = []; # 兼容 table 的取数逻辑
            $params['master']          = 0;  # 兼容 table 的取数逻辑
            $params['customSort']      = ''; # 排序字段置为空 防止代入 date 字段出错
            // 暂不支持小时
            $params['date']            = date('Y-m-d', strtotime( $params['date']));
            $params['edate']           = date('Y-m-d', strtotime( $params['edate']));
            $params['date_type']       = 'day';
        }

        if ($isCustomChart) {
            $gardConfig = $this->getCustomChartHead($params);
        } else {
            $gardConfig = $this->getVisualHeader($params);
        }

        if (!isset($gardConfig['header']))
            return '';
        $chartConfig = array();
        foreach ($params['chartconf'] as $chart_id => $chartVal) {
            $oneChart = array();
            $metricArr = explode(",", $params['metric']);
            $chartMetric = array();
            foreach ($chartVal['chartData'] as $did => $dVal) {
                foreach ($metricArr as $metid => $item) {
                    if ($dVal == implode("_", explode(".", $item))) {
                        $chartMetric[] = $item;
                    }
                }
            }
            $chartapi = $params;
            $oneChart = $chartVal;
            $chartapi['date_type'] = array_key_exists('date_type',$chartapi)?$chartapi['date_type']:'day';

            switch ($chartVal['chartType']) {
                case 'spline_time':
                case 'area':
                case 'cursor_line':
                    if ($params['date'] == $params['edate']) {
                        //如果是小时的就是当前 12小时内的数据
                        if($chartapi['date_type'] == 'hour'){
                          $chartapi['date'] = date("Y-m-d H:i", (strtotime($params['edate']) - 24 * 3600));  
                        } else if($chartapi['date_type'] == 'month'){
                          $chartapi['date'] = date("Y-m", (strtotime($params['edate']) - 24 * 3600 * 30));
                        } else {
                          $chartapi['date'] = date("Y-m-d", (strtotime($params['edate']) - 24 * 3600 * 6));
                        }
                    }
                    break;

                default:
                    $chartapi['date'] = $params['edate'];
                    $oneChart['chartTitle'] = $chartVal['chartTitle'] . "(" . $chartapi['date'] . ")";
                    break;
            }
            //请求数据的时候选择注释报表的方式r
            $data = $this->objFackecube->getData($chartapi,true);
            //2015-07-29 处理小时级数据 day 和 hour 合并一起作为date日期计算
            if($data['status'] == 0){
                switch ($chartapi['date_type']) {
                    case 'hour':
                        foreach ($data['data'] as $tempkey => $tempval) {
                            if(array_key_exists('hour',$tempval)){
                                $tempval['date'] = $tempval['date'].' '.$tempval['hour'].':00';
                                unset($tempval['hour']);
                                $data['data'][$tempkey] = $tempval;
                            }
                        }
                        foreach ($chartVal['group'] as $k1 => $v1) {
                            if($v1 == 'hour'){
                                unset($chartVal['group'][$k1]);
                            }
                        }
                        break;
                    case 'month':
                        //月份返回的date字段 2015-07-08 改成 2015-07
                        foreach ($data['data'] as $tempkey => $tempval) {
                            if(array_key_exists('date',$tempval)){
                                $tempval['date'] = substr($tempval['date'], 0,7) ;
                                $data['data'][$tempkey] = $tempval;
                            }
                        }
                        break;
                    default:
                        # code...
                        break;
                }
            }
            if ($data['status'] != 0) {
                $chartVal['chartTitle'] = $data['msg'];
                if($data['status'] == 5){

                    $chartVal=array();
                    $chartVal['msg']=$data['msg'];
                    $chartVal['code']=-1;
                    return $chartVal;                }
            }
            $oneChart['key'] = "container_" . date("ymd") . "_" . rand(1, 1000);
            $oneChart['dataConig'] = array($chartapi);
            $rangeDate = $this->common->getDateRangeArray($chartapi['date'],$chartapi['edate'],$chartapi['date_type']);
            foreach ($chartVal['chartData'] as $sVal) {
                $realData = $data['data'];
                $series = array();
                foreach ($gardConfig['header'] as $gid => $gVal) {
                    if (strtolower($gVal['name']) == strtolower($sVal)) {
                        //处理包含特殊字符
                        $gVal['cn_name'] = str_replace("\n", "", $gVal['cn_name']);
                        $gVal['cn_name'] = trim($gVal['cn_name']);
                        //处理指标或维度名称隐藏
                        if( isset($chartVal['name_hide'])){
                            if(!in_array($sVal, $chartVal['name_hide'])){
                             $series['name'] = $gVal['cn_name'];
                            }
                        }else{
                            $series['name'] = $gVal['cn_name'];
                        }  
                    }
                }
                switch ($chartVal['chartType']) {
                    case "area":
                    case 'spline_time':
                    case 'cursor_line':
                        $groupVal = array();
                        if(!empty($chartVal['group'])){
                            if ($isCustomChart) {
                                $currentGroup = $chartVal['group'];
                                foreach ($currentGroup as $key => $value) {
                                    if ($value == 'date') {
                                        unset($currentGroup[$key]);
                                    }
                                }
                                $allGroupData = $this->getSplineData($realData, $currentGroup, $sVal, $rangeDate, $chartVal['xaxis']);
                            } else {
                                $allGroupData = $this->getSplineData($realData, $chartVal['group'], $sVal, $rangeDate, $chartVal['xaxis']);
                            }
                        }
                        if(empty($allGroupData)){
                             $realData = $this->common->arrSort($realData, 'date', 'desc');
                             $series['data'] = array_reverse($this->chart->getChartData($realData, $sVal));
                             $series['data'] = $this->checkData($series['data'],$rangeDate);
                        }else{
                            $series['data'] = $allGroupData;
                            $series['more'] = true;
                        }

                        break;
                    default:
                        if ($chartVal['chartType'] == 'pie') {
                           
                            $realData = $this->common->arrSort($realData, $sVal, 'desc');
                        } else if ($chartVal['chartType'] == 'hour') {
                            $realData = $this->common->arrSort($realData, 'hour', 'desc');
                        }
                        //判断用户是否自已设置了维度隐藏
                        $dataNameHeader = $this->visual->getDimMetric($gardConfig['dim']);
                        if ( isset($chartVal['group']) &&  !empty($chartVal['group'])) {
                            $dataNameHeader = $chartVal['group'];
                        }
                        //print_r($realData);
                        //取前TOP数据
                        if (is_numeric($chartVal['chartTop'])) {
                            if ($chartVal['chartType'] == 'column') {
                                $realData = $this->common->arrSort($realData, $sVal, 'desc');
                            }
                            foreach($realData as $itKey => $itemVal){
                               if($itemVal[$sVal] =='不存在' ){
                                    unset($realData[$itKey]);
                               }   
                            }   
                            $newArr = array_slice($realData, 0, $chartVal['chartTop']);
                            $otherData = array_slice($realData, $chartVal['chartTop']);
                            $otherArr = array();
                            $num = 0;
                            foreach ($otherData as $one => $oneVal) {
                                $num += $oneVal[$sVal];
                            }
                            $otherArr[$sVal] = $num;
                            //只展示前10的数据
                            //判断用户是否自已选择了维度
                            foreach ($dataNameHeader as $it => $otheritem) {
                                $otherArr[$otheritem] = '其它(系统自动合并)';
                            }
                            array_push($newArr, $otherArr);
                            $realData = $newArr;
                        }
                        //print_r($realData);exit;
                        $series['data'] = $this->chart->getChartData($realData, $sVal, true, $dataNameHeader);
                        break;
                }
                $oneChart['series'][] = $series;
            }
            if ($chartVal['chartType'] == 'funnel') {
                $funnel = array();
                foreach ($gardConfig['header'] as $item => $itemVal) {
                    if (in_array($itemVal['name'], $chartVal['chartData'])) {
                        $one = array();
                        $one['name'] = $itemVal['cn_name'];
                        $one['value'] = $realData[0][$itemVal['name']];
                        $funnel['data'][] = $one;
                    }
                }
                $funnelName = array();
                if (isset($gardConfig['dim']) && !empty($gardConfig['dim'])) {
                    foreach ($gardConfig['dim'] as $name => $nameVal) {
                        if ($nameVal['name'] != 'date' && $nameVal['name'] != 'hour') {
                            $funnelName[] = $realData[0][$nameVal['cn_name']];
                        }
                    }
                }
                $funnel['name'] = implode("_", $funnelName);
                $funnel['data'] = $this->common->arrSort($funnel['data'], 'value', 'desc');
                $oneChart['series'] = array($funnel);
            }
            if(empty($data['data'])){
                $oneParams['key'] =  $oneChart['key'];
                $oneParams['showMsg'] = $data['showMsg'];
                $chartConfig[] = $oneParams;
            }else{
                $oneChart = $this->chart->getChartParams($oneChart);
                $chartConfig[] = $oneChart;
            }

            
        }

        $gardConfig['chart'] = $chartConfig;

        return $gardConfig;

    }


    function  gittableid2name()
    {
        $sql = 'select id,cn_name from t_visual_table where flag=1';
        $orgIdName = $this->db->createCommand($sql)->queryAll();
        foreach ($orgIdName as $v) {

            $id2name[$v['id']] = $v['cn_name'];
        }
        return $id2name;
    }

    function InitTableConf($allmenuResult)
    {
        $objauth =new AuthManager();
        $objProject=new ProjectManager();
        $superProject=$objauth->getSuperProject();
        $superFlag=$objauth->isSuper();

        //从cube中获取有权限的项目信息
        $projectList=$objProject->getProjectAuth();

        $sql = 'select id,cn_name,project from t_visual_table where flag=1';
        $orgIdName = $this->db->createCommand($sql)->queryAll();
        foreach ($orgIdName as $table_info) {
            if(!empty($table_info['project'])&&!in_array($table_info['project'],$projectList))
                continue;
            $id2name[$table_info['id']] = $table_info['cn_name'];
            if (isset($allmenuResult[$table_info['id']]) ) {
                //这里有个问题
                //在getProjectAuth已经去掉没有非super用户的super项目了
                //这里应该不用再处理了
                if(!empty($superProject) && in_array($table_info['project'],$superProject)){
                    if($superFlag){
                        $superId2name[$table_info['id']]=array(
                            'project'=>$table_info['project'],
                            'cn_name' => $table_info['cn_name'],
                            'all_name' => $table_info['id'] . '_' . $table_info['cn_name']
                        );
                    }
                }else{
                    $authId2name[$table_info['id'] . '_' . $table_info['cn_name']] = array(
                        'project'=>$table_info['project'],
                        'id' => $table_info['id'],
                        'cn_name' => $table_info['cn_name']
                    );
                    $id2all[$table_info['id']] = array(
                        'project'=>$table_info['project'],
                        'cn_name' => $table_info['cn_name'],
                        'all_name' => $table_info['id'] . '_' . $table_info['cn_name']);
                }
            }
        }

        $this->id2name = $id2name;
        $this->authId2name = $authId2name;
        $this->id2all = $id2all;
        $this->superId2name=$superId2name;
    }

    function  gittablename2id()
    {
        $sql = 'select id,cn_name from t_visual_table where flag=1';
        $orgIdName = $this->db->createCommand($sql)->queryAll();
        foreach ($orgIdName as $v) {

            $id2name[$v['id'] . '_' . $v['cn_name']] = array('id' => $v['id'], 'cn_name' => $v['cn_name']);
        }
        return $id2name;
    }

    function  gittableid2all()
    {
        $sql = 'select id,cn_name,auth from t_visual_table where flag=1';
        $orgIdName = $this->db->createCommand($sql)->queryAll();
        foreach ($orgIdName as $v) {

            $id2name[$v['id']] = array('cn_name' => $v['cn_name'], 'auth' => explode(',', $v['auth']), 'all_name' => $v['id'] . '_' . $v['cn_name']);
        }
        return $id2name;
    }


    #获取头部
    function getVisualHeader($params)
    {
        if ($params['udcconf']) {

            if (!is_array($params['udcconf'])) {
                $params['udcconf'] = rawurldecode($params['udcconf']);
                $tmp = json_decode($params['udcconf'], true);
            } else {
                $tmp = $params['udcconf'];
            }

            //udc 是否设置相对占比
            $isudc = (isset($params['isproportion']) && ($params['isproportion'] == '1' || $params['isproportion'] == 'true')) ? $params['isproportion'] : '0';

            foreach ($tmp as $k => $v) {
                $v['name'] = strtolower($v['name']);
                $v['isudc'] = $isudc;
                $tmp[$k] = $v;
            }
            $params['udcconf'] = $tmp;

        }
        $gardConfig = array();

      //  $project = Yii::app()->cache->get($params['project']);
        if (empty($project)) {
            $project = $this->objFackecube->get_app_conf(array('project' => $params['project'],'from_table'=>"hour"));
        //    Yii::app()->cache->set($params['project'], $project, '20');
        }
        if (empty($project)) {
            return '';
        }

        $groups = explode(',', strtolower($params['group']));
        $metricInfo = explode(',', strtolower($params['metric']));
        $map = array();
        foreach ($project['categories'] as $key => $value) {
            foreach ($value['groups'] as $groupInfo) {
                foreach ($groupInfo['dimensions'] as $g => $dim) {
                    foreach ($groups as $g => $gv) {
                        if ($gv == $dim['name']) {
                            //  strpos($gv,$dim['name']) !== false
                            if (!in_array($dim['name'], $map)) {
                                $gardConfig['fiexd'][] = $dim;
                                $gardConfig['dim'][] = $dim;  //添加dim 供 生产对比数据getContrastData 使用维度和指标
                                $map[] = $dim['name'];
                            }
                        }
                    }
                }
                foreach ($groupInfo['metrics'] as $k => $met) {
                    foreach ($metricInfo as $item => $metric) {
                        $metricArr = explode(".", $metric);
                        if ($metricArr[0] == $value['name'] && $metricArr[1] == $groupInfo['name'] && $metricArr[2] == $met['name']) {
                            $tmp = $met;
                            $tmp['name'] = implode('_', $metricArr);
                            $gardConfig['header'][] = $tmp;
                            $gardConfig['metric'][] = $tmp;
                        }
                    }
                }
            }
        }

        //时间维度设置  c_date 是否显示
        if (isset($params['date'])) {
            //兼容之前的固定时间  初始化时是没有但也要显示时间，高级设置后 如果隐藏就不显示如果不隐藏就显示
            $showsort_inarray = (is_array($params['grade']['showsort']) && in_array('date', $params['grade']['showsort'])) ? 1 : -1;
            $sort_inarray = (is_array($params['grade']['sort']) && in_array('date', $params['grade']['sort'])) ? 1 : -1;
            if ($sort_inarray == 1 || $showsort_inarray == -1 || !isset($params['grade']['isfiexd'])) {
                $fiexd['name'] = 'date';
                $fiexd['cn_name'] = '时间';
                $fiexd['explain'] = '';

                if (!isset($gardConfig['fiexd'])) {
                    $gardConfig['fiexd'] = array();
                    $gardConfig['dim'] = array();
                }
                array_unshift($gardConfig['fiexd'], $fiexd);
                array_unshift($gardConfig['dim'], $fiexd);
            }

        }

        if (isset($params['udc']) && !empty($params['udc']) && !empty($gardConfig['header'])) {
            $gardConfig['header'] = array_merge($gardConfig['header'], $params['udcconf']);
            $gardConfig['metric'] = array_merge($gardConfig['metric'], $params['udcconf']);
        }
        //重新组合固定列
        $newgradConfig = array();
        if ((isset($gardConfig['fiexd'])) && is_array($gardConfig['fiexd']) && is_array($gardConfig['header'])) {
            $newgradConfig = array_merge($gardConfig['fiexd'], $gardConfig['header']);
        }

        $config = array();
        $config['dim'] = isset($gardConfig['dim']) ? $gardConfig['dim'] : array();
        $config['metric'] = isset($gardConfig['metric']) ? $gardConfig['metric'] : array();
        //设置是否固定
        if (isset($params['grade']) && isset($params['grade']['isfiexd'])) {
            $config['fiexd'] = array();
            $config['header'] = array();
            $newFiexdArr = array();

            //begin fiexd  [name] => trade_poster_order_paid_order_uv 前端返回的是 trade.poster_order.order_uv
            //字符 点.=>_下划线
            if (isset($params['grade']['fiexd']) && is_array($params['grade']['fiexd'])) {
                foreach ($params['grade']['fiexd'] as $item => $group) {
                    $newFiexdArr[] = implode("_", explode(".", $group));
                }
            }
            //end

            foreach ($newgradConfig as $key => $value) {
                //数组
                if (in_array($value['name'], $newFiexdArr)) {
                    $config['fiexd'][] = $value;
                } else {
                    $config['header'][] = $value;
                }
            }

            return $config;
        } else {
            //兼容之前的固定列
            return $gardConfig;
        }
    }

    function getCustomChartHead($params)
    {
        if (isset($params['grade']['data']) && empty($params['grade']['data'])) {
            $gardConfig = [];
        } else {
            $gardConfig = [
                'fixed'  => [],
                'dim'    => [],
                'header' => [],
                'metric' => [],
            ];

            // 构造所有指标 维度
            $allItem = [];
            foreach ($params['grade']['data'] as $item) {
                $allItem[$item['key']] = $item;
            }

            $dim    = $params['grade']['group'] ? explode(',', $params['grade']['group']) : [];
            $metric = $params['grade']['metric'] ? explode(',', $params['grade']['metric']) : [];

            foreach ($dim as $item) {
                if ($item == 'date') {
                    continue;
                }
                $info                = $allItem[$item];
                $gardConfig['dim'][] = [
                    'name'        => $info['key'],
                    'cn_name'     => $info['name'],
                    'explain'     => '',
                    'pseudo_code' => '',
                    'type'        => 'varchar',
                ];
            }

            foreach ($metric as $item) {
                $info                   = $allItem[$item];
                $gardConfig['metric'][] = [
                    'name'        => $info['key'],
                    'cn_name'     => $info['name'],
                    'explain'     => '',
                    'pseudo_code' => '',
                    'type'        => 'decimal',
                ];
            }

            $gardConfig['fixed']  = $gardConfig['dim'];
            $gardConfig['header'] = $gardConfig['metric'];
        }

        return $gardConfig;
    }

    #获取指标维度
    function getDimMetric($config)
    {
        if (empty($config)) {
            return array();
        } else {
            $arr = array();
            foreach ($config as $key => $value) {
                if ($value['name'] != 'date') {
                    $arr[] = $value['name'];
                }
            }
            return $arr;
        }
    }

    #功能函数
    function jsonOutPut($code, $message, $data = array())
    {
        echo json_encode(array('status' => $code, 'msg' => $message, 'data' => $data));
    }

    #获取首页
    function getShowMenu($admin = false, $menuResult = null, $allmenutable=null,$menu_id=null)
    {


        if (empty($this->authId2name)) {
            $this->InitTableConf($allmenutable);
        }

        $authId2name = $this->authId2name;
        $id2name = $this->id2all;
        $superId2name=$this->superId2name;
        if ($admin != true) {
            $objAuth = new AuthManager();
            $id2name = $objAuth->checkAuthPoint($id2name);
        }

        if(!empty($superId2name)){
            $id2name+=$superId2name;
        }


        $objMenu = new MenuManager();
        $first_menu = $objMenu->selectFirstMenu();
        foreach ($first_menu as $tmp) {
            $retu[$tmp['first_menu']] = array();
        }
      //  echo '<pre/>';print_r($id2name);exit();


        $menu2info = array();
        foreach ($menuResult as $value) {
            $menuinfo = array();
            $menuinfo['menu_id'] = $value['id'];
            $menuinfo['type'] = $value['type'];
            $menuinfo['name'] = $value['second_menu'];
            $menuinfo['table'] = array();
            foreach ($value['all'] as $tableinfo) {
                if ($tableinfo['type'] == 1) {
                    $id = $tableinfo['id'];
                    if (isset($id2name[$id])) {
                        $menuinfo['table'][] = array('id' => $id, 'cn_name' => $id2name[$id]['cn_name'], 'type' => 1);
                    }
                } elseif ($tableinfo['type'] == 2) {
                    $menuinfo['table'][] = array('id' => '', 'cn_name' => $tableinfo['name'], 'name' => $tableinfo['name'], 'url' => $tableinfo['url'], 'type' => 2);;
                }elseif ($tableinfo['type'] == 3) {
                    $id = $tableinfo['id'];
                    if (isset($id2name[$id])) {
                        $menuinfo['table'][] = array('id' => $id, 'cn_name' => $id2name[$id]['cn_name'],'name' => $id2name[$id]['cn_name'], 'type' => 3);
                    }
                }

            }
            if (!empty($menuinfo['table'])){
                $retu[$value['first_menu']][$value['id']] = $menuinfo;
            }

            if(!empty($menu_id) && $menu_id==$menuinfo['menu_id']){

                $type2map=array(1=>'报表','2'=>'外链','3'=>'外链2');
                $res=array();
                foreach($menuinfo['table'] as $k=>$v){
                    $v['type']=$type2map[$v['type']];
                    $res[]=$v;
                }
                return $res;
            }


        }
        if(empty($retu)){
           return array(); 
        }
        foreach($retu as $k=>$v){
            if(empty($v)){
                unset($retu[$k]);
            }
        }
       // echo '<pre/>';print_r($retu);exit();

        return $retu;
    }

    #获取菜单下面的id
    function getMenuVisual($menu_id)
    {
        $sql = " select * from t_visual_menu where flag=1 and id =" . $menu_id;
        $data = $this->db->createCommand($sql)->queryAll();
        if (empty($data[0]['table_id']))
            return false;
        $sqlmenu = "select * from t_visual_table where  id in(" . $data[0]['table_id'] . ")";
        $visulInfo = $this->db->createCommand($sqlmenu)->queryAll();
        //  Yii::trace(CVarDumper::dumpAsString($visulInfo));
        return $visulInfo;
    }

    function getCustomCollect($name)
    {
        $sql  = 'select id, cn_name from t_visual_table_custom where  creater = \'' . $name . '\' ';
        $data = $this->db->createCommand($sql)->queryAll();
        if (empty($data)) {
            return array();
        } else {
            return $data;
        }
    }

    #获取用户搜藏夹
    function  getFavorites($name)
    {
        if (empty($name))
            return;
        if (empty($this->id2name)) {
            $this->id2name = $this->gittableid2name();

        }
        $sql = 'select table_id from t_visual_favorites where  user_name= \'' . $name . '\' ';
        $data = $this->db->createCommand($sql)->queryAll();
        if (empty($data)) {
            return array();
        }
        $arrId = explode(',', $data[0]['table_id']);
        if (!empty($arrId)) {
            foreach ($arrId as $id) {
                if (isset($this->id2name[$id])) {
                    $id2name[$id] = array('name' => $this->id2name[$id], 'id' => $id);
                }

            }
        } else {
            $id2name = array();
        }
        return $id2name;


    }

    #保存报表
    function saveVisual($params)
    {
        $db = Yii::app()->db_metric_meta;
        $valStr = "";
        $dataArr = array(
            "'" . $params['cn_name'] . "'",
            "'" . $params['explain'] . "'",
            "'" . $params['params']['table']['project'] . "'",
            "'" . $params['params']['table']['group'] . "'",
            "'" . $params['params']['table']['metric'] . "'",
            "'" . serialize($params['params']) . "'",
            "'" . Yii::app()->user->username . "'"
        );
        $sql = "insert into  " . $this->tableName . "(`cn_name`,`explain`,`project`,`group`,`metric`,`params`,`creater`) values(" . implode(",", $dataArr) . ") ";
        $db->createCommand($sql)->execute();
        $id = $db->getLastInsertID();
        return $id;
    }

    #更新报表
    function editorVisual($params)
    {
        $id = $params['id'];
        unset($params['id']);
        $db = Yii::app()->db_metric_meta;
        $valStr = "";
        $setParams = array('`cn_name`', '`explain`',
            '`project`', '`group`', '`metric`', '`params`', '`creater`');
        $dataArr = array(
            $params['cn_name'],
            $params['explain'],
            $params['params']['table']['project'],
            $params['params']['table']['group'],
            $params['params']['table']['metric'],
            serialize($params['params']),
            Yii::app()->user->username
        );
        $sqlParams = array();
        foreach ($setParams as $key => $value) {
            $sqlParams[] = $value . "='" . $dataArr[$key] . "'";
        }
        $sql = "update " . $this->tableName . " set   " . implode(",", $sqlParams) . " where id =" . $id;
        $db->createCommand($sql)->execute();
        return $id;
    }

    //检测是否有相同报表
    function checkVisual($name)
    {
        $sql = "select  * from  " . $this->tableName;
        if (!empty($name)) {
            $whereStr = "  where  cn_name ='" . $name . "'";
        } else {
            $whereStr = "";
        }
        $sql = $sql . $whereStr;
        $db = Yii::app()->sdb_metric_meta;
        $data = $db->createCommand($sql)->queryAll();
        return $data;
    }

    #删除报表
    function deleteVisual($id)
    {
        $db = Yii::app()->db_metric_meta;
        if ($id) {
            $sql = "delete  from " . $this->tableName . "  where  id= " . $id;
            $re = $db->createCommand($sql)->execute();
            return $re;
        } else {
            return false;
        }
    }

    #查询报表信息
    function getVisualList($whereArr = array())
    {
        $sql = "select  * from  " . $this->tableName;
        if (!empty($whereArr)) {
            $whereStr = "  where  project ='" . $whereArr['project'] . "'";
        } else {
            $whereStr = "";
        }
        $sql = $sql . $whereStr;
        $db = Yii::app()->sdb_metric_meta;
        $data = $db->createCommand($sql)->queryAll();
        return $data;
    }

    #显示报表
    function showVisual($id = false)
    {
        $sql = "select  * from  " . $this->tableName;
        if ($id) {
            $whereStr = "  where  id =" . $id;
        } else {
            $whereStr = "";
        }
        $sql = $sql . $whereStr;
        $db = Yii::app()->sdb_metric_meta;
        $data = $db->createCommand($sql)->queryAll();
        return $data;
    }
    /*
     处理 easy Ui 平均数，求和数
    */
    function getFooter($data,$countArr){
        if(!empty($countArr)){
            $countNum = array();            
            foreach ($countArr as $key => $value) {

                if($value['column_converge'] !=''){

                }
                $total  = 0;   
                foreach ($data  as  $dVal) {
                $total += $dVal[$value];
                }
                $countNum[$value]['avg'] = round($total/count($data),2);
                $countNum[$value]['total'] = number_format($total);
            }
            //处理成easyUi需要的数据
            $easyUiFooter= array();
            //取平均值
            $oneAvg = array();
            foreach ($countArr as $key => $Vavg) {
                    $oneAvg[$Vavg] = $countNum[$Vavg]['avg'];
            }
            $oneAvg['dt'] = "平均值";
            //取总和
            $oneTotal = array();
            foreach ($countArr as $key => $VTotal) {
                    $oneTotal[$VTotal] = $countNum[$VTotal]['total'];
            }
            $oneTotal['dt'] = "总和";
            array_push($easyUiFooter, $oneAvg);
            array_push($easyUiFooter, $oneTotal);
            return $easyUiFooter;
        }
    }

    function sendMail($names,$html,$title,$Cc="",$isreply=false){
        $this->objComm=new CommonManager();
        $from = "<data-dt@.com>";
        $this->objComm->sendMail($names,$html,$title,$from,'',true,$Cc);
    }

    function  selectFirstMenu(){
        $sql="select DISTINCT  name,id from t_visual_tool  where parent_id=0  order by sort asc";//概览型报表
        $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $result;

    }
    function toolAddMenu($params){

        $dataArr=array($params['name'],$params['content'],$params['parent_id'],$params['icon'],$params['new_window'],$params['url'],Yii::app()->user->username);
        $valueStr='';
        foreach($dataArr as $value){
            $valueStr.="'".$value."',";

        }
        $valueStr=trim($valueStr,',');
        $sql = "insert into t_visual_tool (`name`,`content`,`parent_id`,`icon`,`new_window`,`url`,`user_name`) values(" . $valueStr. ") ";

        Yii::app()->db_metric_meta->createCommand($sql)->execute();
        $id = Yii::app()->db_metric_meta->getLastInsertID();
        if($id>0){
            return $id;
        }
        return False;
    }


    function  selectTool(){
        $sql='select aa.*,bb.`name` as parent_name from t_visual_tool aa
              left join t_visual_tool bb on aa.parent_id = bb.id
              WHERE aa.name is not null and aa.parent_id !=0 order by sort,id asc';
        $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();

        return $result;
    }

    function  selectparentmenu(){
        $sql='select * from t_visual_tool
              WHERE name is not null and parent_id =0 order by sort asc';
        $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        return $result;
    }

    /**
     * @param null $menu_id
     * @return mixed
     */
    function selectMenu($id=0){
        if($id=='all'){
            $result=$this->selectTool();
        }else{
            $sql='select * from t_visual_tool where name is not null';
            $suffix=' and id='.$id;
            $suffix=$suffix.' order by parent_id,sort asc';
            $sql=$sql.$suffix;
            $result=Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
        }
        return $result;

    }

    function updateMenu($updateArr){
        $select_res=$this->selectMenu($updateArr['id']);
        $table_res=$select_res['all'];
        $sql_pefix='update t_visual_tool set ';
        $sql_suffix=' where id='.$updateArr['id'];
        $sql='';
        $tableItem = ['name', 'content', 'content', 'icon', 'new_window', 'sort', 'user_name', 'update_time', 'url'];
        foreach($updateArr as $k=>$v){
            if (!in_array($k, $tableItem)) {
                continue;
            }
            if($k=='table_id'&&is_array($v)){

                $inter=array_intersect_key($select_res['all'],$v);
                $diff=array_diff_key($v,$inter);
                $table_res=array();
                foreach($inter as $v){
                    $table_res[]=$v;
                }
                foreach($diff as $v){
                    $table_res[]=$v;
                }
                $v=addslashes(json_encode($table_res));
            }
            $sql.=''.$k.'='.'\''.$v.'\',';
        }
        $sql=trim($sql,',');
        $sql=$sql_pefix.$sql.$sql_suffix;
        $res=Yii::app()->db_metric_meta->createCommand($sql)->execute();
        return True;
    }

    function  selectSecnodMenu($parent_id){
        $result=Yii::app()->sdb_metric_meta->createCommand()
            ->select('id,name')
            ->from('t_visual_tool')
            ->where('parent_id=:parent_id order by sort asc', array(':parent_id' => $parent_id))
            ->queryAll();

        return $result;

    }

    function  saveSortMenu($sortinfo){
        $transaction=Yii::app()->db_metric_meta->beginTransaction();
        try{
            foreach($sortinfo as $k=>$v){
                $sql="update t_visual_tool set sort=$k where id=".$v[id];

                Yii::app()->db_metric_meta->createCommand($sql)->execute();
            }
            $transaction->commit();

        }catch(Exception $e){
            $transaction->rollback();
            return False;
        }
        return True;
    }

    //查询Mapdata
    function selectMapData($mapkey=''){
        try {
            $sql = "select * from t_visual_mapdata";
            $where = " where 1=1 ";
            $where_mk = '';
            if ($mapkey != '') {
                $where_mk = " and map_key='{$mapkey}' ";
            }
            $sql = $sql . $where . $where_mk;
            $orderby = " order by id desc ";
            $sql = $sql . $orderby;

            $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
            return $result;
        }catch(Exception $e){
            return [];
        }

    }
    //查询Mapdata
    function selectMapDataBySql($sql=''){
        try {
            $result = Yii::app()->sdb_dt_db->createCommand($sql)->queryAll();
            $join_arr=[];
            foreach($result as $key=>$value){
                $join_arr[]=implode(':',array_slice(array_values($value),0,2));
            }
            return $join_arr;
        }catch(Exception $e){
            throw new Exception($e->getMessage());
        }
        return [];

    }
    //查询Mapdata by cache
    function selectMapDataByCache($mapkey){
        try {
            $cache_key=$mapkey.":mapdata";
            $mapdata_cache=Yii::app()->cache->get($cache_key);
            if($mapdata_cache){
                $join_arr=explode(PHP_EOL,$mapdata_cache);
                return $join_arr;
            }
        }catch(Exception $e){

        }
        return [];

    }

    //保存Mapdata
    function saveMapData($map_name,$mapkey,$map_data){
        $username = Yii::app()->user->username;
        $cdate=date('Y-m-d H:i:s',time());
        $sql="insert into t_visual_mapdata(map_name,map_key,map_data,creater,updater,create_time,update_time) values ";
        $sql=$sql."('{$map_name}','{$mapkey}','{$map_data}','{$username}','{$username}','{$cdate}','{$cdate}') ";
        $sql=$sql."ON DUPLICATE KEY UPDATE map_name='{$map_name}',map_data='{$map_data}',updater='{$username}',update_time='{$cdate}'";

        $result=Yii::app()->sdb_metric_meta->createCommand($sql)->execute();
        return $result;

    }

    function addToolClassifyName($name, $content, &$message)
    {
        //检查是否已存在要分类菜单
        $classify_name_sql = "SELECT DISTINCT id, name FROM t_visual_tool WHERE parent_id = 0 AND name = '{$name}'";
        $db = Yii::app()->db_metric_meta;
        $alreadyExistToolClassifyName = $db->createCommand($classify_name_sql)->queryAll();
        if (!empty($alreadyExistToolClassifyName)) {
            $message = '分类菜单已存在';
            return false;
        }
        //添加
        $username = Yii::app()->user->username;
        $insert_sql = "insert into t_visual_tool (name, content, parent_id, icon, user_name, url) values ('{$name}', '{$content}', 0, 'glyphicon glyphicon-asterisk', '{$username}', '#')";
        $db = Yii::app()->db_metric_meta;
        $res = $db->createCommand($insert_sql)->execute();
        if ($res <= 0) {
            $message = '添加失败';
            return false;
        }
        $message = '添加成功';
        return true;
    }
    
    /**
     * 处理搜索默认值问题
     */
    function getSearchParams($params){
        $search = [];
        //[{"key":"user_type","val":["4"],"op":"like","defaultsearch":"4"}]
        foreach ($params['grade']['data'] as $item){     
            if( isset($item['issearch']['is_check']) && isset($item['search']['defaultsearch']) && $item['search']['defaultsearch'] !='' && $item['issearch']['is_check'] ==1 ){
                $one =[];
                $one['key']             = $item['key'];
                $one['val']             = array($item['search']['defaultsearch']);
                $one['op']              = $item['issearch']['is_accurate'] ? "=":'like';
                $one['defaultsearch']  =  $item['search']['defaultsearch'];
                $search[] = $one;
            }
        }
        return $search;
    }
}
