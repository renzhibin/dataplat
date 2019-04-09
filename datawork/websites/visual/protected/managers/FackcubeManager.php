<?php

Class FackcubeManager extends Manager
{

    function __construct()
    {
        $this->api = new Fackcube();
    }
    public  function  get_fakecube($action,$parament,$post=false){
        if($post==true){
            return $this->api->get($action,$parament,true,120,'',true);
        }
        return $this->api->get($action,$parament,false,120,'',true);;
    }
    public function  get_project_list($username=null)
    {
        $orgretu= $this->api->list_app();
        $retu=array();
        if(!empty($username)){
            foreach ($orgretu  as $tmp) {
                if($tmp['creater']==$username){
                    $retu[]=$tmp;
                }

            }

        }else{
            $retu=$orgretu;
        }
        $finalRetu=array();
        foreach($retu as $tmp){
            if($tmp['authtype']==2){
                if(!empty($tmp['authuser'])){
                    if(in_array(Yii::app()->user->username,explode(',',$tmp['authuser']))){
                        $finalRetu[]=$tmp;
                    }
                }
            }else{
                $finalRetu[]=$tmp;
            }
        }

        $objauth =new AuthManager();
        $superProject=$objauth->getSuperProject();


        if(empty($superProject) ||  Yii::app()->user->isSuper())
            return $finalRetu;
        $superRetu=array();
        foreach($finalRetu as $tmp){

            if(in_array($tmp['project'],$superProject))
                continue;
            $superRetu[]=$tmp;
        }



        return $superRetu;

    }

    public function get_real_list($userName = null)
    {
        $sql = 'select * from mms_realtime_conf where 1 = 1';

        if (!empty($userName)) {
            $sql .= " and creater = '{$userName}'";
        }

        $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();

        return $result;
    }

    public function check_real_name($info)
    {
        $enName = $info['val'];
        switch ($info['type']) {
            case 'project' :
                $tipName   = '项目';
                $searchCol = 'app_name';
                break;
            case 'group' :
                $tipName   = '类目';
                $searchCol = 'category_name';
                break;
            case 'hql' :
                $tipName   = 'SQL';
                $searchCol = 'hql_name';
                break;
            default:
                $tipName   = '项目';
                $searchCol = 'app_name';
        }

        if (!preg_match("/^[0-9a-zA-Z_]{1,20}$/", $enName)) {
            return [false, $tipName . '英文名必须小于20个字符，格式必须为数字、字符串或者下划线'];
        }

        // 检查是否重复名称
        $sql    = "select `id` from `mms_realtime_app_conf` where `{$searchCol}` = '{$enName}'";
        $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryRow();

        if (!empty($result)) {
            return [false, $tipName . '英文名存在重复项目'];
        }

        return [true, 'success'];
    }

    public function  get_profile($params, $getAll = false)
    {
        $params['show_type'] = 'json';
        return $this->api->get_profile($params, $getAll);
    }

    public function get_real_profile($params, $getAll = false)
    {
        $result = [
            'name'       => '',
            'cn_name'    => '',
            'explain'    => '',
            'dimensions' => [],
            'metrics'    => [],
            'dim_sets'   => [],
            'tables'     => [],
        ];

        $sql = strtolower($params['hql']);
        $sql = str_replace(PHP_EOL, ' ', $sql);
        $sql = preg_replace("/\s(?=\s)/", "\\1", $sql);

        // 获取维度以及指标
        preg_match("/select([\s\S]+?)from[\s\S]+/", $sql, $partRegData);

        $dimensions = $metrics = $tables = [];
        if (isset($partRegData[1]) && !empty($partRegData[1])) {
            foreach (explode(',', $partRegData[1]) as $currentItem) {
                $itemData = explode('as', trim($currentItem));

                $itemLength = count($itemData);
                if ($itemLength == 1) {
                    $firstItem              = trim($itemData[0]);

                    if(empty($firstItem)) {
                        continue;
                    }

                    $dimensions[$firstItem] = [
                        'name'    => $firstItem,
                        'cn_name' => '',
                        'explain' => '',
                        'type'    => '',
                    ];
                }

                if ($itemLength == 2) {
                    $secondItem           = trim($itemData[1]);

                    if(empty($secondItem)) {
                        continue;
                    }

                    $metrics[$secondItem] = [
                        'name'    => $secondItem,
                        'cn_name' => '',
                        'explain' => '',
                        'type'    => '',
                    ];
                }
            }
        }

        // 获取依赖表
        preg_match_all("/(?:from|(?:(?:inner |cross |(?:left(?: outer)? )|(?:right(?: outer)? ))?join)) ([0-9a-zA-Z_]*\.?[0-9a-zA-Z_]+)/", $sql, $partRegData);

        if (isset($partRegData[1]) && !empty($partRegData[1])) {
            foreach ($partRegData[1] as $currentItem) {
                $name          = trim($currentItem);
                $tables[$name] = [
                    'name'          => $name,
                    'cn_name'       => '',
                    'ischecktables' => '',
                ];
            }
        }

        // 获取结果
        $appName      = $params['app_name'];
        $categoryName = $params['category_name'];
        $sqlName      = $params['hql_name'];

        $sql     = "select other_params from `mms_realtime_app_conf` where `app_name` = '{$appName}' and `category_name` = '{$categoryName}' and `hql_name` = '{$sqlName}'";
        $appConf = Yii::app()->sdb_metric_meta->createCommand($sql)->queryRow();

        if (!empty($appConf)) {
            $currentConf    = json_decode($appConf['other_params'], true);
            $dimensionsConf = $currentConf['dimensions'];
            $metricsConf    = $currentConf['metrics'];
            $tablesConf     = $currentConf['tables'];

            foreach ($dimensionsConf as $item) {
                $name = trim($item['name']);
                if (isset($dimensions[$name])) {
                    $dimensions[$name]['type']    = $item['type'];
                    $dimensions[$name]['cn_name'] = $item['cn_name'];
                    $dimensions[$name]['explain'] = $item['explain'];
                }
            }

            foreach ($metricsConf as $item) {
                $name = trim($item['name']);
                if (isset($metrics[$name])) {
                    $metrics[$name]['type']    = $item['type'];
                    $metrics[$name]['cn_name'] = $item['cn_name'];
                    $metrics[$name]['explain'] = $item['explain'];
                }
            }

            foreach ($tablesConf as $item) {
                $name = trim($item['name']);
                if (isset($tables[$name])) {
                    $tables[$name]['cn_name']       = $item['cn_name'];
                    $tables[$name]['ischecktables'] = $item['ischecktables'];
                }
            }
        }

        // 返回结果
        $result['dimensions'] = array_values($dimensions);
        $result['metrics']    = array_values($metrics);
        $result['tables']     = array_values($tables);

        return $result;
    }

    public function check_real_target_table($db, $table)
    {
        $status = false;
        $db     = "sdb_{$db}";

        try {
            $sql  = "SHOW TABLES LIKE '{$table}'";
            $data = Yii::app()->{$db}->createCommand($sql)->queryRow();

            if (is_array($data) && isset($data["Tables_in_metric_real_data ({$table})"])) {
                $status = true;
            }

        } catch (Exception $exception) {
            $status = false;
        }

        return $status;
    }

    public function save_real_project($params, $getAll = false)
    {
        $params['user'] = Yii::app()->user->username;

        if (isset($params['id'])) {
            $data = $this->update_real_project($params);
        } else {
            $data = $this->insert_real_project($params);
        }

        return $data;
    }

    public function insert_real_project($params)
    {
        $dateStart  = isset($params['project']['run']['date_s']) && !empty($params['project']['run']['date_s'])
            ? $params['project']['run']['date_s'] : '0000-00-00 00:00:00';
        $dateEnd    = isset($params['project']['run']['date_e']) && !empty($params['project']['run']['date_e'])
            ? $params['project']['run']['date_e'] : '0000-00-00 00:00:00';
        $user       = $params['user'];
        $appName    = isset($params['project']['project'][0]['name']) ? $params['project']['project'][0]['name'] : '';
        $cnName     = isset($params['project']['project'][0]['cn_name']) ? $params['project']['project'][0]['cn_name'] : '';
        $explain    = isset($params['project']['project'][0]['explain']) ? $params['project']['project'][0]['explain'] : '';
        $createTime = date('Y-m-d H:i:s');
        $storeType  = isset($params['project']['project'][0]['storetype']) ? $params['project']['project'][0]['storetype'] : '';

        $db = Yii::app()->db_metric_meta;

        // appname 不能重复
        $sql = "select * from mms_realtime_conf where appname = '{$appName}'";
        $row = $db->createCommand($sql)->queryRow();
        if (!empty($row)) {
            return [false, '英文名称不能重复'];
        }

        // 处理conf
        $conf = $params['project'];
        foreach ($conf['project'] as $projectId => $projectValue) {
            foreach ($conf['project'][$projectId]['categories'] as $categoriesId => $categoriesValue) {
                foreach ($categoriesValue['groups'] as $sqlId => $sqlInfo) {
                    unset(
                        $conf['project'][$projectId]['categories'][$categoriesId]['groups'][$sqlId]['dimensions'],
                        $conf['project'][$projectId]['categories'][$categoriesId]['groups'][$sqlId]['metrics'],
                        $conf['project'][$projectId]['categories'][$categoriesId]['groups'][$sqlId]['hql']
                    );
                }
            }
        }
        $jsonConf = addslashes(json_encode($conf));

        // 数据写入 mms_realtime_conf
        $conf_sql = "insert into `mms_realtime_conf` (`date_s`, `date_e`, `creater`, `appname`, `create_time`, `explain`, `cn_name`, `storetype`, `editor`, `conf`) ";
        $conf_sql .= "values ('{$dateStart}', '{$dateEnd}', '{$user}', '{$appName}', '{$createTime}', '{$explain}', '{$cnName}', '{$storeType}', '{$user}', '{$jsonConf}')";
        $conf_res = $db->createCommand($conf_sql)->execute();


        // 数据写入 mms_realtime_app_conf 循环写入 数据
        $conf = $params['project'];
        foreach ($conf['project'] as $projectId => $projectValue) {
            foreach ($conf['project'][$projectId]['categories'] as $categoriesId => $categoriesValue) {
                $categoriesName = $categoriesValue['name'];
                foreach ($categoriesValue['groups'] as $sqlId => $sqlInfo) {
                    $sqlName     = $sqlInfo['name'];
                    $dimensions  = addslashes(json_encode(['dimensions' => $sqlInfo['dimensions']]));
                    $metrics     = addslashes(json_encode(['metrics' => $sqlInfo['metrics']]));
                    $otherParams = addslashes(json_encode($sqlInfo));

                    $app_conf_sql = "insert into `mms_realtime_app_conf` (`app_name`, `category_name`, `hql_name`, `dimensions`, `metrics`, `other_params`) ";
                    $app_conf_sql .= "values ('{$appName}', '{$categoriesName}', '{$sqlName}', '{$dimensions}', '{$metrics}', '{$otherParams}')";
                    $app_conf_res = $db->createCommand($app_conf_sql)->execute();
                }
            }
        }

        return [true, ''];
    }

    public function update_real_project($params)
    {
        $id        = $params['id'];
        $dateStart = isset($params['project']['run']['date_s']) && !empty($params['project']['run']['date_s'])
            ? $params['project']['run']['date_s'] : '0000-00-00 00:00:00';
        $dateEnd   = isset($params['project']['run']['date_e']) && !empty($params['project']['run']['date_e'])
            ? $params['project']['run']['date_e'] : '0000-00-00 00:00:00';
        $user      = $params['user'];
        $appName   = isset($params['project']['project'][0]['name']) ? $params['project']['project'][0]['name'] : '';
        $cnName    = isset($params['project']['project'][0]['cn_name']) ? $params['project']['project'][0]['cn_name'] : '';
        $explain   = isset($params['project']['project'][0]['explain']) ? $params['project']['project'][0]['explain'] : '';
        $storeType = isset($params['project']['project'][0]['storetype']) ? $params['project']['project'][0]['storetype'] : '';

        $db = Yii::app()->db_metric_meta;

        $conf = $params['project'];
        foreach ($conf['project'] as $projectId => $projectValue) {
            foreach ($conf['project'][$projectId]['categories'] as $categoriesId => $categoriesValue) {
                foreach ($categoriesValue['groups'] as $sqlId => $sqlInfo) {
                    unset(
                        $conf['project'][$projectId]['categories'][$categoriesId]['groups'][$sqlId]['dimensions'],
                        $conf['project'][$projectId]['categories'][$categoriesId]['groups'][$sqlId]['metrics'],
                        $conf['project'][$projectId]['categories'][$categoriesId]['groups'][$sqlId]['hql']
                    );
                }
            }
        }
        $jsonConf = addslashes(json_encode($conf));

        // 更新 mms_realtime_conf
        $conf_sql = "update `mms_realtime_conf` set `date_s` = '{$dateStart}', `date_e` = '{$dateEnd}', `explain` = '{$explain}', `cn_name` = '{$cnName}', `storetype` = '{$storeType}', `editor` = '{$user}', `conf` = '{$jsonConf}' where `id` = '$id'";
        $conf_res = $db->createCommand($conf_sql)->execute();

        // 查询 appname 对应的全部 sql
        $appSql  = "select * from mms_realtime_app_conf where app_name = '{$appName}'";
        $appData = $db->createCommand($appSql)->queryAll();
        $appRows = [];
        if (!empty($appData)) {
            foreach ($appData as $row) {
                $appRows["{$row['app_name']}_{$row['category_name']}_{$row['hql_name']}"] = $row;
            }
        }

        // 更新 新增 SQL
        $conf = $params['project'];
        foreach ($conf['project'] as $projectId => $projectValue) {
            foreach ($conf['project'][$projectId]['categories'] as $categoriesId => $categoriesValue) {
                $categoriesName = $categoriesValue['name'];
                foreach ($categoriesValue['groups'] as $sqlId => $sqlInfo) {
                    $sqlName     = $sqlInfo['name'];
                    $indexKey    = "{$appName}_{$categoriesName}_{$sqlName}";
                    $dimensions  = addslashes(json_encode(['dimensions' => $sqlInfo['dimensions']]));
                    $metrics     = addslashes(json_encode(['metrics' => $sqlInfo['metrics']]));
                    $otherParams = addslashes(json_encode($sqlInfo));

                    if (isset($appRows[$indexKey])) {
                        $currentId    = $appRows[$indexKey]['id'];
                        $app_conf_sql = "update `mms_realtime_app_conf` set `dimensions` = '{$dimensions}', `metrics` = '{$metrics}', `other_params` = '{$otherParams}' where id = '{$currentId}'";
                        $app_conf_res = $db->createCommand($app_conf_sql)->execute();
                        unset($appRows[$indexKey]);
                    } else {
                        $app_conf_sql = "insert into `mms_realtime_app_conf` (`app_name`, `category_name`, `hql_name`, `dimensions`, `metrics`, `other_params`) ";
                        $app_conf_sql .= "values ('{$appName}', '{$categoriesName}', '{$sqlName}', '{$dimensions}', '{$metrics}', '{$otherParams}')";
                        $app_conf_res = $db->createCommand($app_conf_sql)->execute();
                    }
                }
            }
        }

        // 删除 SQL
        if (count($appRows) > 0) {
            $delID = [];
            foreach ($appRows as $rowIndex => $rowData) {
                $delID[] = $rowData['id'];
            }

            if (count($delID) > 0) {
                $allDelID = '\'' . implode("', '", $delID) . '\'';

                $app_del_sql = "delete from `mms_realtime_app_conf` where id in ({$allDelID})";
                $app_del_res = $db->createCommand($app_del_sql)->execute();
            }
        }

        return [true, ''];
    }

    public function  save_project($params, $getAll = false)
    {
        return $this->api->save_project($params, $getAll);
    }

    public function get_dimset($params)
    {
        return $this->api->get_dimset($params);
    }


    public function get_metric($params)
    {
        return $this->api->get_metric($params);
    }

    public function get_hql($params){
        return $this->api->get_hql($params);
    }

    public function get_real_app_conf($params, $getAll = false)
    {
        $project = $params['project'];

        $confSql    = 'select appname, conf from mms_realtime_conf where 1 = 1';
        $appConfSql = 'select app_name, category_name, hql_name, other_params from mms_realtime_app_conf where 1 = 1';

        if (!empty($project)) {
            $confSql    .= " and appname = '{$project}'";
            $appConfSql .= " and app_name = '{$project}'";
        }

        $conf    = Yii::app()->sdb_metric_meta->createCommand($confSql)->queryRow();
        $appConf = Yii::app()->sdb_metric_meta->createCommand($appConfSql)->queryAll();

        $allAppConf = [];
        foreach ($appConf as $current) {
            $allAppConf["{$current['app_name']}_{$current['category_name']}_{$current['hql_name']}"] = json_decode($current['other_params'], true);
        }

        $res = [
            'status' => 1,
            'msg'    => '获取配置数据失败',
            'data'   => [],
        ];

        if (!empty($conf)) {
            $conf = json_decode($conf['conf'], true);

            $appName = $conf['project'][0]['name'];
            foreach ($conf['project'] as $projectId => $projectValue) {
                foreach ($projectValue['categories'] as $categoriesId => $categoriesValue) {
                    $categoriesName = $categoriesValue['name'];
                    foreach ($categoriesValue['groups'] as $sqlId => $sqlInfo) {
                        $sqlName = $sqlInfo['name'];
                        $index   = "{$appName}_{$categoriesName}_{$sqlName}";
                        if (isset($allAppConf[$index])) {
                            $conf['project'][$projectId]['categories'][$categoriesId]['groups'][$sqlId] = $allAppConf[$index];
                        }
                    }
                }
            }

            $res['status'] = 0;
            $res['msg']    = 'success';
            $res['data']   = $conf;
        }

        return $res;
    }

    public function get_hive_queue() {
        return [
            'status' => 0,
            'msg'    => 'success',
            'data'   => [
                [
                    'value'  => '集团',
                    'key'    => 'bloc'
                ],
                [
                    'value'  => '汽车',
                    'key'    => 'car'
                ]
            ]
        ];
    }

    public function get_real_schedule_interval()
    {
        return [
            'status' => 0,
            'msg'    => 'success',
            'data'   => [
                [
                    'value'  => '每五分钟',
                    'key'    => '5',
                    'offset' => '0',
                ],
                [
                    'value'  => '每十分钟',
                    'key'    => '10',
                    'offset' => '0',
                ],
                [
                    'value'  => '每三十分钟',
                    'key'    => '30',
                    'offset' => '0',
                ],
                [
                    'value'  => '每一小时',
                    'key'    => '60',
                    'offset' => '0',
                ],
            ],
        ];
    }

    public function get_real_source_db()
    {
        return [
            'status' => 0,
            'msg'    => 'success',
            'data'   => [
                [
                    'value' => 'auto_account',
                    'key'   => 'auto_account',
                ],
                [
                    'value' => 'crm_store_real_statistics',
                    'key'   => 'crm_store_real_statistics',
                ],
                [
                    'value' => 'auto_alive_account',
                    'key'   => 'auto_alive_account',
                ],
                [
                    'value' => 'toy_order',
                    'key'   => 'toy_order',
                ],
                [
                    'value' => 'realtime_market',
                    'key'   => 'realtime_market',
                ],
            ],
        ];
    }

    public function get_real_target_db()
    {
        return [
            'status' => 0,
            'msg'    => 'success',
            'data'   => [
                [
                    'value' => 'metric_real_data',
                    'key'   => 'metric_real_data',
                ],
            ],
        ];
    }

    public function get_app_conf($params, $getAll = false)
    {

        return $this->api->get_app_conf($params, $getAll);
    }

    public function getData($params,$contrastFlag=false,$handleFlag=true,$result=null)
    {
        $dataConfig = array();
        if(!empty($params['query_mysql_type'])){
            $dataConfig['query_mysql_type'] = $params['query_mysql_type'];
        }
        $isDateSlice = isset($params['grade']['pubdata']['isdateslice']) ? $params['grade']['pubdata']['isdateslice'] : '';
        $dateSlice = isset($params['grade']['pubdata']['dateslice']) ? $params['grade']['pubdata']['dateslice'] : '';
        if (!empty($isDateSlice) and $isDateSlice == '1' and !empty($dateSlice)) {
            $dateOffset = intval($dateSlice) - 1;
            switch (strtolower($params['date_type'])) {
                case 'day':
                    //设置开始、结束时间
                    $params['date'] = date('Y-m-d', strtotime('-' . $dateOffset . 'day', strtotime($params['edate'])));
                    break;
                case 'month':
                    //设置开始、结束时间
                    $params['date'] = date('Y-m', strtotime('-' . $dateOffset . 'month', strtotime($params['edate'])));
                    break;
                case 'hour':
                    //设置开始、结束时间
                    $params['date'] = date('Y-m-d h:i', strtotime('-' . $dateOffset . 'day', strtotime($params['edate'])));
                    break;
                default:
                    echo "时间类型(date_type)未定义";
            }
        }
        $dataConfig['project'] = $params['project'];
        $dataConfig['group'] = $params['group'];
        $dataConfig['metric'] = $params['metric'];
        $dataConfig['date'] = $params['date'];
        $dataConfig['edate'] = $params['edate'];
        //hour小时 day天 month月 以及默认小时排序问题
        $dataConfig['date_type'] = $params['date_type']?$params['date_type']:"day";

        if(($dataConfig['date_type'] == "hour") && ($dataConfig['group'] && strpos("hour",$dataConfig['group']) !== false) ){
            $dataConfig['order']  = 1;
            $dataConfig['ordermetric']  = 'date,hour';
        }
        if (!empty($params['search']) && is_string($params['search'])) {
            $dataConfig['search'] = $params['search'];
        }
        if (!empty($params['search']) && is_array($params['search'])) {
            $dataConfig['search'] = json_encode($params['search']);
        }

        if ($dataConfig['search'] && is_string($dataConfig['search'])) {
            $dataConfig['search'] = json_decode($dataConfig['search'], true);
            foreach ($dataConfig['search'] as $k => $v) {
                if(is_string($v['val'])){
                    $v['val']=explode('?', $v['val']);

                }
                $dataConfig['search'][$k] = $v;
            }
            $dataConfig['search'] = json_encode($dataConfig['search']);
        }

        if (is_string($params['filter'])) {
            try {
                $params['filter'] = json_decode($params['filter'], true);
            } catch (Exception $e) {
                Yii::log('json_decode_error', 'trace');

            }
            if(is_array($params['filter']) ){

                foreach ($params['filter'] as $key => $value) {
                    if($value['op'] == 'REGEXP' ){
                        $params['filter'][$key]['val'][0] = rawurldecode($value['val'][0]);
                    }
                }
            }
        }

        if (is_array($params['filter']) && !empty($params['filter'])) {
            foreach ($params['filter'] as $k => $v) {
                if (is_string($v['val'])) {
                    $tmp = explode('?', $v['val']);
                    unset($v['val']);
                    $v['val'] = $tmp;
                }

                if (is_array($v['val'])) {
                    foreach ($v['val'] as $subk => $subv) {
                        $v['val'][$subk] = strip_tags($v['val'][$subk]);
                    }

                    $params['filter'][$k] = $v;
                }
                if($v['op'] == 'REGEXP' ){
                    $params['filter'][$k]['val'][0] = rawurldecode($v['val'][0]);
                }

            }

            $params['filter'] = json_encode($params['filter'], true);
            $dataConfig['filter'] = $params['filter'];
        }

        $dataConfig['filter'] = rawurlencode($dataConfig['filter']);
        $dataConfig['total'] = 1;
        if (isset($params['total']) && $params['total'] == 0) {
            unset($dataConfig['total']);
        }

        if (strpos($params['udc'], '$') === false && !empty($params['udcconf'])) {
            if (is_array($params['udcconf'])) {
                $params['udcconf'] = json_encode($params['udcconf']);
            }

            $params['udcconf'] = rawurldecode($params['udcconf']);
            $dataConfig['addcolumn'] = $params['udcconf'];
        } else {
            $dataConfig['udc'] = $params['udc'];

        }

        if (!empty($dataConfig['addcolumn'])) {
            $tmp = json_decode($dataConfig['addcolumn'], true);
            foreach ($tmp as $k => $v) {
                $v['name'] = strtolower($v['name']);
                // UDC支持小数逻辑
                $v['expression'] = preg_replace_callback('/([a-zA-Z0-9_]+\.){2}([a-zA-Z0-9_]+)+?|(\d+)(\.\d+)?/', function ($matches) {
                    $item = $matches[0];

                    if (is_numeric($item)) {
                        $itemLength = strlen($item) - strlen(intval($item));
                        if ($itemLength > 1) {
                            $itemPowTen = pow(10, $itemLength - 1);
                            $item       = '(' . $item * $itemPowTen . ' / ' . $itemPowTen . ')';
                        }
                    }

                    return $item;
                }, $v['expression']);

                if (isset($params['grade']['sort']) && !in_array($v['name'], $params['grade']['sort'])) {
                    continue;
                }
                $tmp[$k] = $v;
            }
            $dataConfig['addcolumn'] = json_encode($tmp);
        }

        if (isset($params['rows'])) {
            $dataConfig['offset'] = $params['rows'];
        }
        if (isset($params['page'])) {
            $dataConfig['index'] = $params['page'];
        }
        //处理排序
        if($params['customSort'] !=''){
             $dataConfig['customSort'] = $params['customSort'];
        }
         //处理排序
        if($params['converge'] !=''){
             $dataConfig['converge'] = $params['converge'];
        }
        if(isset($params['order']) && $params['order'] !=''){
             $one = array();
             $one['order'] = $params['order'];
             $one['key'] = $params['sort'];
             $dataConfig['customSort'] = array($one);
             $dataConfig['customSort'] = json_encode($dataConfig['customSort']);
        }

        /*
         if (isset($params['order'])) {
             if ($params['order'] == 'asc') {
                 $dataConfig['order'] = 1;
             } else {
                 $dataConfig['order'] = 2;
             }
         }
        //默认排序问题  以及兼容默认排序一般日期 和指标的第一个来排序
         if (isset($params['sort'])) {
             $dataConfig['ordermetric'] = $params['sort'];
         } else {
             // 排序问题
             if(isset($params['grade'])){
                  $orderbyarr= array();
                 //兼容默认排序问题 排序一般日期 和指标的第一个来排序
                 if(!isset($params['grade']['isorderby'])){
                     //第一个纬度：时间date
                     if($params['grade']['sort'][0] == 'date'){
                        $orderbyarr[] = 'date';
                     }
                     //第一个指标
                     $firstmetric = explode(',', $params['metric']);
                     $orderbyarr[] = $firstmetric[0];
                 }

                 if(isset($params['grade']['isorderby']) && isset($params['grade']['orderbyarr'])){
                     $orderbyarr = $params['grade']['orderbyarr'];
                 }
             }

             if(count($orderbyarr)>0){
               $dataConfig['ordermetric'] = str_replace('.', '_', implode(',', $orderbyarr));
             }
         }
        */

        if ($dataConfig['date'] > $dataConfig['edate']) {
            $dataConfig['date'] = $dataConfig['edate'];
            $dataConfig['edate'] = $dataConfig['date'];
        }

        $fakecubeurl = "query_app";

        //自定义表格接口
        if($params['type']==8){
            $dataConfig['sql'] = $params['sql'];
            $dataConfig['check'] = $params['check'];
            if(isset($params['offset'])&&$params['offset']!=''&&$params['offset']!=null){}
            # $dataConfig['offset'] = $params['offset'];
            $dataConfig['offset'] = $params['rows'];
            $fakecubeurl = "custom_query_app";
            $dataConfig['customSort']=str_replace('date','cdate',$dataConfig['customSort']);
        }

        if(isset($params['api'])){
            $dataConfig['api'] = $params['api'] ;
            $dataConfig['appName'] = $params['appName'];
            $dataConfig['appToken'] = $params['appToken'];

            return $this->get_fakecube($fakecubeurl,$dataConfig,true);
        }

        $totalMetricConfig           = $dataConfig;
        $totalMetricConfig['index']  = 1;
        $totalMetricConfig['offset'] = 1000000;
        $data=$result;
        $yesterdayData =[];
        if(is_null($result)){
            $data = $this->get_fakecube($fakecubeurl,$dataConfig,true);
            //开始计算环比／升降
            if($this->checkRelativeRationStatus($params) && isset($params['getDataType']) && $params['getDataType'] == 'table') {
            //处理search
            if(!empty($dataConfig['search']) or !isset($dataConfig['search']) or $dataConfig['search'] == "[]") {
                $dataConfig['search'] = $this->setSearch($data, $dataConfig['group']);
            }

            //计算环比时临时将前一天分页修改为1000；
            $tmpOffset = $dataConfig['offset'];
            $tmpIndex = $dataConfig['index'];
            $dataConfig['index'] = 1;
            $dataConfig['offset'] = 1000;
            //根据date_type处理时间
            switch (strtolower($dataConfig['date_type'])) {
                case 'day':
                    //设置开始、结束时间
                    $dataConfig['date'] = date('Y-m-d', strtotime('-1 day', strtotime($dataConfig['date'])));
                    $dataConfig['edate'] = date('Y-m-d', strtotime('-1 day', strtotime($dataConfig['edate'])));
                    //获取前一天数据
                    $yesterdayData = $this->get_fakecube($fakecubeurl,$dataConfig,true);
                    //重置开始、结束时间
                    $dataConfig['date'] = date('Y-m-d', strtotime('+1 day', strtotime($dataConfig['date'])));
                    $dataConfig['edate'] = date('Y-m-d', strtotime('+1 day', strtotime($dataConfig['edate'])));
                    break;
                case 'month':
                    //设置开始、结束时间
                    $dataConfig['date'] = date('Y-m',strtotime('-1 month', strtotime($dataConfig['date'])));
                    $dataConfig['edate'] = date('Y-m',strtotime('-1 month', strtotime($dataConfig['edate'])));
                    //获取前月数据
                    $yesterdayData = $this->get_fakecube($fakecubeurl,$dataConfig,true);
                    //重置开始、结束时间
                    $dataConfig['date'] = date('Y-m',strtotime('+1 month', strtotime($dataConfig['date'])));
                    $dataConfig['edate'] = date('Y-m',strtotime('+1 month', strtotime($dataConfig['edate'])));
                    break;
                case 'hour':
                    //设置开始、结束时间
                    $dataConfig['date'] = date('Y-m-d h:i', strtotime('-1 day', strtotime($dataConfig['date'])));
                    $dataConfig['edate'] = date('Y-m-d h:i', strtotime('-1 day', strtotime($dataConfig['edate'])));
                    //获取前月数据
                    $yesterdayData = $this->get_fakecube($fakecubeurl,$dataConfig,true);
                    //重置开始、结束时间
                    $dataConfig['date'] = date('Y-m-d h:i', strtotime('+1 day', strtotime($dataConfig['date'])));
                    $dataConfig['edate'] = date('Y-m-d h:i', strtotime('+1 day', strtotime($dataConfig['edate'])));
                    break;
                default:
                    echo "时间类型(date_type)未定义";
            }

            //计算环比当前一天数据为NULL时，特殊处理
            if (empty($yesterdayData['data'])) {
                $yesterdayData = $data;
            }
            //重置分页
            $dataConfig['offset'] = $tmpOffset;
            $dataConfig['index'] = $tmpIndex;
            }
        }

        if($handleFlag==true){
            $data['data'] = $this->__handleData($dataConfig, $data['data'],$contrastFlag);
            $yesterdayData['data'] = $this->__handleData($dataConfig, $yesterdayData['data'],$contrastFlag);
        }

        //处理环比数据
        $groupList = explode(",", $dataConfig['group']);
        //group中增加date
        array_push($groupList, 'date');
        $metricList = explode(",", $dataConfig['metric']);
        $metricArray = [];
        foreach ($metricList as $row) {
            $metricArray[] = str_replace('.', '_', $row);
        }
        $addUdc = json_decode($dataConfig['addcolumn'], true);
        if (is_array($addUdc)) {
            foreach ($addUdc as $udc) {
                array_push($metricArray, $udc['name']);
            }
        }
        //得到指标行配置 "查看趋势"判断$params['grade']['data'])是否为空
        if (!empty($params['grade']['data'])) {
            $rowConfig = $this->getRowConfig($params['grade']['data'], $groupList);

            // 根据当前的过滤条件计算total数据
            if($this->checkSumRationStatus($params)) {
            $totalMetricData = $this->get_fakecube($fakecubeurl, $totalMetricConfig, true);
            if (isset($params['getDataType']) && in_array($params['getDataType'], ['tableDownload', 'table'])) {
                // 计算所有指标的累计
                $totalMetricSum = [];
                foreach ($totalMetricData['data'] as $key => $row) {
                    $currentDate = $row['date'];
                    foreach ($metricArray as $metric) {
                        if (isset($rowConfig[$metric]['sum_ratio']) && $rowConfig[$metric]['sum_ratio'] == 1) {
                            $totalMetricSumKey = $currentDate . '_' . $metric . '_total_metric_sum';
                            if (isset($totalMetricSum[$totalMetricSumKey])) {
                                $totalMetricSum[$totalMetricSumKey] += $row[$metric];
                            } else {
                                $totalMetricSum[$totalMetricSumKey] = $row[$metric];
                            }
                        }
                    }
                }
                foreach ($data['data'] as $key => $row) {
                    $currentDate = $row['date'];
                    foreach ($metricArray as $metric) {
                        if (isset($rowConfig[$metric]['sum_ratio']) && $rowConfig[$metric]['sum_ratio'] == 1) {
                            $totalMetricSumKey = $currentDate . '_' . $metric . '_total_metric_sum';
                            if (isset($totalMetricSum[$totalMetricSumKey]) && $totalMetricSum[$totalMetricSumKey] != 0) {
                                $data['data'][$key][$metric . '_sum_percent'] = number_format(str_replace(',', '', $row[$metric]) / $totalMetricSum[$totalMetricSumKey] * 100, 2, '.', '') . '%';
                            } else {
                                $data['data'][$key][$metric . '_sum_percent'] = 0;
                            }
                        }
                    }
                }
            }
            }

            // 计算环比
            if (!empty($yesterdayData['data'])) {
                $yesterdayDataArray = [];
                foreach ($yesterdayData['data'] as $yesterdayRow) {
                    $yesterdayRowKey = $this->generateGroupKey($groupList, $yesterdayRow);
                    $yesterdayDataArray[$yesterdayRowKey] = $yesterdayRow;
                }
                if (isset($params['getDataType']) && $params['getDataType'] == 'table') {
                    foreach ($data['data'] as $key => $row) {
                        $rowKey = $this->generateGroupKeyByDataConfig($groupList, $row, $dataConfig);
                        foreach ($metricArray as $metric) {
                            switch ($rowConfig[$metric]['thousand']) {
                                //处理千分位
                                case 0:
                                    if (is_numeric($data['data'][$key][$metric]) === false) {
                                        break;
                                    }
                                    if (strpos($data['data'][$key][$metric], '.') === false) {
                                        $data['data'][$key][$metric] = number_format($data['data'][$key][$metric]);
                                        break;
                                    }
                                    $data['data'][$key][$metric] = number_format($data['data'][$key][$metric], 2);
                                    break;
                                case 1:
                                    break;
                                default:
                                    break;
                            }
                            //计算百分比
                            switch ($rowConfig[$metric]['percent']) {
                                case 0:
                                    break;
                                case 1:
                                    if (is_numeric(str_replace(',', '', $data['data'][$key][$metric]))) {
                                        $data['data'][$key][$metric] = $data['data'][$key][$metric] . '%';
                                    }
                                    break;
                            }
                            switch ($rowConfig[$metric]['relative_ratio']) {
                                //当relative_ratio == "0" 不做任何处理
                                case 0:
                                    break;

                                //当relative_ratio ==1 时计算环比
                                case 1:
                                    //判断前一天数据中是否存在当天纬度组合KEY
                                    $data['data'][$key][$metric] = $data['data'][$key][$metric] . "<br>" . $this->getLinkRelativeRatio($row[$metric], $yesterdayDataArray[$rowKey][$metric]);
                                    #$data['data'][$key][$metric . "_rate"] = $this->getLinkRelativeRatio($row[$metric], $yesterdayDataArray[$rowKey][$metric]);
                                    break;

                                //当relative_ratio ==2 时计算升降
                                case 2:
                                    //判断当天数据中纬度组合KEY是否存在
                                    $data['data'][$key][$metric] = $data['data'][$key][$metric] . "<br>" . $this->getLinkRelative($row[$metric], $yesterdayDataArray[$rowKey][$metric]);
                                    break;

                                default:
                                    Yii::log('===>>{getData}relative_ratio未定义');
                                    $data['data'][$key][$metric] = $row[$metric];
                            }
                        }
                    }
                }
            }
            if (isset($params['getDataType']) && $params['getDataType'] == 'table') {
                //计算百分比
                foreach ($data['data'] as $key => $row) {
                    foreach ($metricArray as $metric) {
                        switch ($rowConfig[$metric]['percent']) {
                            case 0:
                                break;
                            case 1:
                                $metric = strtolower($metric);
                                if (is_numeric(str_replace(',', '', $data['data'][$key][$metric])) && strpos($data['data'][$key][$metric], '%') === false) {
                                    $data['data'][$key][$metric] = $data['data'][$key][$metric] . '%';
                                }
                                break;
                        }
                    }
                }
            }
        }

        return $data;
    }

    private function __handleData($dataConfig, $data,$contrastFlag)
    {
        if (empty($data))
            return array();

        if($contrastFlag==true)
        {
            $obj=new ProjectManager();
            $commentRes=$obj->getProjectComment($dataConfig['project']);
            if($commentRes!=false){
                $commentRes=json_decode($commentRes,true);
                if (!empty($commentRes)) {
                    foreach ($data as $k => $v) {
                        foreach ($v as $subk => $subv) {
                            //兼容老的json格式
                            if (isset($commentRes[$subk]) && !isset($commentRes[$subk]['content'])) {
                                $data[$k][$subk] = array();
                                $data[$k][$subk]['commentdata'] = $subv . '(' . $commentRes[$subk][$subv] . ')';
                                $data[$k][$subk]['realdata'] = $subv;
                            } else if (isset($commentRes[$subk]) && isset($commentRes[$subk]['content'][$subv])) {
                                $data[$k][$subk] = array();
                                if ($commentRes[$subk]['isReplace'] == '1') {
                                    $data[$k][$subk]['commentdata'] = $commentRes[$subk]['content'][$subv];
                                } else if ($commentRes[$subk]['isReplace'] == '2') {
                                    $data[$k][$subk]['commentdata'] = $subv . '(' . $commentRes[$subk]['content'][$subv] . ')';
                                }
                                $data[$k][$subk]['realdata'] = $subv;
                            }
                        }

                    }
                }
            }
        }

        foreach ($data as $k => $v) {
            foreach ($v as $keyid => $keyName) {
                switch ($keyid) {
                    case 'twitter_id':
                        $data[$k]['twitter_id']=ConstManager::getUrl(NULL,$data[$k]['twitter_id']);
                        if(isset($v['style_id'])){
                            $data[$k]['style_id']=ConstManager::getHref($data[$k]['style_id']);
                         }
                        break;
                    case 'style_id':
                            $data[$k]['style_id']=ConstManager::getHref($data[$k]['style_id']);
                        break;

                        case 'shop_circle_id':
                            $url="<a style='padding-left:5px' target='_blank' href='http://circle.meilishuo.com/circle/circle_highlight_list?circle_id=".$data[$k]['shop_circle_id']."'>".$data[$k]['shop_circle_id']."</a>";
                            $data[$k]['shop_circle_id'] = array('realdata'=>$data[$k]['shop_circle_id'],'commentdata'=>$url);
                            break;
                    case 'circle_id':
                        $url ="<a style='padding-left:5px' target='_blank' href='http://circle.meilishuo.com/circle/circle_highlight_list?circle_id=".$data[$k]['circle_id']."'>".$data[$k]['circle_id']."</a>";
                        $data[$k]['circle_id'] = array('realdata'=>$data[$k]['circle_id'],'commentdata'=>$url);
                        break;
                    case 'shop_id':
                        $data[$k]['shop_id']=ConstManager::getHref(null,null,$data[$k]['shop_id']);
                        break;
                    case 'org_id':
                        $url ="<a style='padding-left:5px' target='_blank' href='http://pages.w.meilishuo.com/cooper/wd/".$data[$k]['org_id']."?from=singlemessage&isappinstalled=1'>".$data[$k]['org_id']."</a>";
                        $data[$k]['org_id'] = array('realdata'=>$data[$k]['org_id'],'commentdata'=>$url);
                        break;
                    case 'mgj_item_id':
                        $data[$k]['mgj_item_id']=ConstManager::getUrl(null,null,null,$data[$k]['mgj_item_id']);
                        break;
                    default:

                        break;
                }
            }
        }
        return $data;
    }

    /**
     * 根据Group组合key；
     * @param $groupList
     * @param $row
     * @return string
     */
    public function generateGroupKey($groupList, $row)
    {
        $groupValue = [];
        foreach ($groupList as $group) {
            $groupValue[] = $row[$group];
        }
        $groupKey = implode('_', $groupValue);

        return $groupKey;
    }

    public function generateGroupKeyByDataConfig($groupList, $row ,$dataConfig = []) {
        if (count($dataConfig) == 0) {
            return $this->generateGroupKey($groupList, $row);
        }
        $date = '';
        switch (strtolower($dataConfig['date_type'])) {
            case 'day':
                $date = date('Y-m-d', strtotime('-1 day', strtotime($row['date'])));
                break;
            case 'month':
                $date = date('Y-m',strtotime('-1 month', strtotime($row['date'])));
                break;
            case 'hour':
                $date = date('Y-m-d h:i', strtotime('-1 day', strtotime($row['date'])));
                break;
            default:
                break;
        }
        $row['date'] = $date;
        return $this->generateGroupKey($groupList, $row);
    }

    /**
     * 环比计算
     * @param $currentValue
     * @param $yesterdayValue
     * @return float|int|string
     */
    public function getLinkRelativeRatio($currentValue, $yesterdayValue)
    {
        if (!is_numeric($currentValue) || !is_numeric($yesterdayValue)) {
            return '';
        }

        if ($yesterdayValue == 0 && $currentValue != 0) {
            $rate = "<span style='color:green'>↑100%</span>";
        } elseif ($yesterdayValue == 0 && $currentValue == 0) {
            $rate = "<span>0%</span>";
        } elseif($yesterdayValue != 0 && $currentValue == 0) {
            $rate = "<span style='color:red'>↓100%</span>";
        } else {
            $rate = round(($currentValue - $yesterdayValue) / $yesterdayValue, 4) * 100;
            $rateAbs = abs($rate);
            if($rate > 0) {
                $rate = "<span style='color:green'>↑{$rateAbs}%</span>";
            } else if ($rate < 0) {
                $rate =  "<span style='color:red'>↓{$rateAbs}%</span>";
            } else {
                #$rate = $rate . '%';
                $rate = "<span>{$rateAbs}%</span>";
            }
        }

        return $rate;
    }

    public function getLinkRelative($currentValue, $yesterdayValue)
    {
        if (empty($yesterdayValue)) {
            $diff = $currentValue;
        } else {
            $diff = $currentValue - $yesterdayValue;
        }
        if (strpos($diff, '.')) {
            $diffAbsNumberFormated = number_format(round(abs($diff), 2), 2);
        } else {
            $diffAbsNumberFormated = number_format(abs($diff));
        }
        if($diff > 0) {
            $diff = "<span style='color:green'>↑{$diffAbsNumberFormated}</span>";
        } else if ($diff < 0) {
            $diff =  "<span style='color:red'>↓{$diffAbsNumberFormated}</span>";
        } else {
            #$rate = $rate . '%';
            $diff = "<span>$diffAbsNumberFormated</span>";
        }
        return $diff;
    }

    /**
     * 设置search
     * @param $data
     * @param $groupFiled
     * @return string
     */
    public function setSearch($data, $groupFiled)
    {
        $groupFiled = explode(",", $groupFiled);
        $dataGroupValue = [];
        foreach ($groupFiled as $group) {
            $dataValueArray = [];
            $dataUniqueValues = array_unique(array_column($data['data'],  $group));
            foreach ($dataUniqueValues as $value) {
                $dataValueArray[] = '"' . $value. '"';
            }
            $valueStr = join(',' , $dataValueArray);
            $dataGroupValue[] = "{\"val\":[{$valueStr}],\"key\":\"{$group}\",\"op\":\"in\",\"defaultsearch\":\"\"}";
        }

        return "[". join(',', $dataGroupValue). "]";
    }

    /**
     * 得到纬度数据的配置
     * @param $rowParams
     * @param $groupList
     * @return array
     */
    public function getRowConfig($rowParams, $groupList)
    {
        $rowConfig = [];
        foreach ($rowParams as $row) {
            if (!in_array($row['key'], $groupList)) {
                $metricKey = str_replace('.', '_', $row['key']);
                $rowConfig[$metricKey] = $row;
            }
        }

        return $rowConfig;
    }

    public function getGroupAndMetricName($params)
    {
        $result = [];

        if (isset($params['grade']['data'])) {
            foreach ($params['grade']['data'] as $item) {
                $result[str_replace('.', '_', $item['key'])] = $item['name'];
            }
        }

        return $result;
    }

    public function getDateList($params)
    {
        $date  = $params['date'];
        $edate = $params['edate'];

        switch (strtolower($params['date_type'])) {
            case 'day':
                $dateList = $this->genDateList($date, $edate, '+1 day', 'Y-m-d');
                break;
            case 'month':
                $dateList = $this->genDateList($date, $edate, '+1 month', 'Y-m');
                break;
            case 'hour':
                $dateList = $this->genDateList($date, $edate, '+1 hour', 'Y-m-d H:i');
                break;
            default:
                $dateList = [];
                echo "时间类型(date_type)未定义";
        }

        return $dateList;
    }

    public function genDateList($date, $edate, $time, $format)
    {
        $dateList = [];

        while ($date <= $edate) {
            $dateSeconds = strtotime($date);
            $dateList[]  = date($format, $dateSeconds);
            $date        = date($format, strtotime($time, $dateSeconds));
        }

        return $dateList;
    }

    private function checkRelativeRationStatus($param)
    {
        $status = false;

        if (isset($param['grade']['data'])) {
            foreach ($param['grade']['data'] as $item) {
                if (isset($item['relative_ratio']) && is_numeric($item['relative_ratio']) && $item['relative_ratio'] >= 1 && $item['relative_ratio'] <= 2) {
                    $status = true;
                    break;
                }
            }
        }

        return $status;
    }

    private function checkSumRationStatus($param)
    {
        $status = false;

        if (isset($param['grade']['data'])) {
            foreach ($param['grade']['data'] as $item) {
                if(isset($item['sum_ratio']) && is_numeric($item['sum_ratio']) && $item['sum_ratio'] == 1) {
                    $status = true;
                    break;
                }
            }
        }

        return $status;
    }
    /**
     * 获取文件
     */
    public function getTaskData(){
        $sql ="select * from t_rely_task  where is_vaild =1";
        $db    = Yii::app()->sdb_metric_meta;
        $taskArr = $db->createCommand($sql)->queryAll();
        return $taskArr;
         
    }
    
    /**
     * 获取文件
     */
    public function getTaskDataAll($search = array(), $type = 'all'){
        $where = "  ";
        if(!empty($search)){
            $where .=" where  t.task in (".implode(",", $search).")";
        }
        if ($type <> 'all') {
            $where .= " and t.update_time is null and p.ass_table is null ";
        }
        $sql ="select t.id,t.task,creater,p.rely_task,p.ass_table,t.update_time,t.schedule_level from  t_rely_task  as t join t_rely_topo  p on t.task = p.task ".$where."   group by t.task,rely_task";
        $db    = Yii::app()->sdb_metric_meta;
        $taskArr = $db->createCommand($sql)->queryAll();
        return $taskArr;
         
    }


    /**
     * 获取任务依赖信息
     */
    public function getTaskRely($task){
        $sql ="select * from  t_rely_topo where task='{$task}' ";
        $db    = Yii::app()->sdb_metric_meta;
        $taskArr = $db->createCommand($sql)->queryAll();
        return $taskArr;
    }
    /**
     * 获取任务名称详细信息
     */
    public function getRunlog($taskArr){
        $sqlIn =[];
        foreach ($taskArr as $item){
            $sqlIn [] = "'".$item."'";
        }

        if (empty($sqlIn)) {
            return [];
        }

        $timeStr = date("Y-m-d 00:00:00", time());
        $sql ="select * from ( select  max(id) as id,app_name,status,create_time,start_time,end_time,concat(app_name,'.',run_module) as  task_name from mms_run_log  where create_time >='{$timeStr}' and concat(app_name,'.',run_module) in (". implode(",", $sqlIn).") group by task_name,id order by id desc )  as a  
        join mms_conf on mms_conf.appname =  a.app_name and mms_conf.storetype = 2
        group by a.task_name  ";
        $db    = Yii::app()->sdb_metric_meta;
        $taskArr = $db->createCommand($sql)->queryAll();
        return $taskArr;
    }

    public function getHistoryAppConfLog($project, $category, $hql, $num)
    {
        $sql = "select * from mms_app_conf_log where app_name = '{$project}' and category_name = '{$category}' and hql_name = '{$hql}' order by id desc limit {$num}";
        return Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
    }

    public function getHistoryConfLog($project, $num)
    {
        $sql = "select * from mms_conf_log where appname = '{$project}' order by id desc limit {$num}";
        return Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
    }
}
