<?php
/**
 * Created by PhpStorm.
 * User: gide
 * Date: 16/6/23
 * Time: 16:56
 */

class RealtimeManager extends Manager {

    static $cache_open = true;
    function __construct() {
        $this->soldOrderTable = 't_sold_order';
        $this->soldOrderTable2 = 't_sold_order2';
        $this->orderheadTable = 'order_head';
        $this->two_days_prediction = 'two_days_prediction';
        $this->salesmaninfoTable = 'salesman_info';
        $this->item_inventory_upt = 'item_inventory_upt';

        //通过接口调用
        $this->token = 'aHR0cDovL2FwaS5kYXRhLmxzaDEyMy5jb20v';
        //$this->rquest_url = 'http://api.data.lsh123.com/market/Optimalsupplyinfo';
        $this->rquest_url = 'http://api.data.lsh123.com/a/b';
    }

    function fetchOmsOrderResult ($date, $yestoday, $last_7day, $version, $zone_id) {
        $result = array();

        $sold_table = ($version == 1) ? $this->soldOrderTable : $this->soldOrderTable2;

        //获取今天的预测最大值
        $sql = "select dt,hour,prediction from {$sold_table} where dt = '{$date}' and hour = (select max(hour) from {$sold_table} where dt='{$date}')";
        $today_max_pre = Yii::app()->sdb_lsh_predict->createCommand($sql)->queryRow();
        $result['maxpre'] = !empty($today_max_pre) ? $today_max_pre : array();

        //未来预测值
        $sql = "select dt,pre_dt,prediction from two_days_prediction where dt in(select max(dt) from {$this->two_days_prediction} where pre_dt>'{$date}' group by pre_dt) order by dt asc";
        $future_pre = Yii::app()->sdb_lsh_predict->createCommand($sql)->queryAll();
        $result['future'] = !empty($future_pre) ? $future_pre : array();

        $sql = "select from_unixtime(order_time,'%Y-%m-%d') as dt,count(order_code) as totalorder from {$this->orderheadTable} where from_unixtime(order_time,'%Y-%m-%d') in ('{$yestoday}','{$last_7day}') and valid = 1 AND order_status not in (12,94,95) and region_code = {$zone_id} group by from_unixtime(order_time,'%Y-%m-%d') order by from_unixtime(order_time,'%Y-%m-%d') desc";

        $last_total_order = Yii::app()->sdb_lsh_oms->createCommand($sql)->queryAll();
        $result['lastorder'] = !empty($last_total_order) ? $last_total_order : array();

        $sql = "select from_unixtime(order_time,'%Y-%m-%d') as dt,from_unixtime(order_time,'%k') as hour,count(order_code) as hour_order from {$this->orderheadTable} where valid = 1 AND order_status not in (12,94,95) and from_unixtime(order_time,'%Y-%m-%d') in ('{$date}','{$yestoday}','{$last_7day}') and region_code = {$zone_id} group by from_unixtime(order_time,'%Y-%m-%d'),from_unixtime(order_time,'%k') order by from_unixtime(order_time,'%Y-%m-%d') asc,CAST(from_unixtime(order_time,'%k') as UNSIGNED INTEGER) asc";

        $last_hour_order = Yii::app()->sdb_lsh_oms->createCommand($sql)->queryAll();
        $result['hourorder'] = !empty($last_hour_order) ? $last_hour_order : array();

        //到货\缺货
        $sql = "select * from(select dt,hour,item_id,tag from {$this->item_inventory_upt} where dt='{$date}' and tag in(1,2) group by item_id union all select dt,hour,item_id,tag from {$this->item_inventory_upt} where dt='{$date}' and tag = 0)t order by hour desc";
        $result['inventory'] = Yii::app()->sdb_lsh_predict->createCommand($sql)->queryAll();

        return $result;
    }

    function fetchSoldOrderResult( $date, $yestoday, $last_7day, $version, $zone_id ) {

        $result = array();

        $sold_table = ($version == 1) ? $this->soldOrderTable : $this->soldOrderTable2;

        //获取今天的预测最大值
        $sql = "select dt,hour,prediction from {$sold_table} where dt = '{$date}' and hour = (select max(hour) from {$sold_table} where dt='{$date}')";
        $today_max_pre = Yii::app()->sdb_lsh_predict->createCommand($sql)->queryRow();
        $result['maxpre'] = !empty($today_max_pre) ? $today_max_pre : array();

        //未来预测值
        $sql = "select dt,pre_dt,prediction from two_days_prediction where dt in(select max(dt) from {$this->two_days_prediction} where pre_dt>'{$date}' group by pre_dt) order by dt asc";
        $future_pre = Yii::app()->sdb_lsh_predict->createCommand($sql)->queryAll();
        $result['future'] = !empty($future_pre) ? $future_pre : array();

        //获取昨日\上周同期的订单总量
        #$sql = "select dt,sum(hour_order) as totalorder from {$this->soldOrderTable} where dt in('{$yestoday}','{$last_7day}') group by dt order by dt desc";

        $sql = "select from_unixtime(ordered_at,'%Y-%m-%d') as dt,count(order_id) as totalorder from {$this->orderheadTable} where from_unixtime(ordered_at,'%Y-%m-%d') in ('{$yestoday}','{$last_7day}') and status not in(3,5,8) and zone_id = {$zone_id} group by from_unixtime(ordered_at,'%Y-%m-%d') order by from_unixtime(ordered_at,'%Y-%m-%d') desc";

        $last_total_order = Yii::app()->sdb_lsh_market->createCommand($sql)->queryAll();
        $result['lastorder'] = !empty($last_total_order) ? $last_total_order : array();

        //今天\昨天\上周同期小时订单数
        //$sql = "select dt,hour,hour_order from {$this->soldOrderTable} where dt in('{$date}','{$yestoday}','{$last_7day}') order by dt desc";

        $sql = "select from_unixtime(ordered_at,'%Y-%m-%d') as dt,from_unixtime(ordered_at,'%k') as hour,count(order_id) as hour_order from {$this->orderheadTable} where status not in (3,5,8) and from_unixtime(ordered_at,'%Y-%m-%d') in ('{$date}','{$yestoday}','{$last_7day}') and zone_id = {$zone_id} group by from_unixtime(ordered_at,'%Y-%m-%d'),from_unixtime(ordered_at,'%k') order by from_unixtime(ordered_at,'%Y-%m-%d') asc,CAST(from_unixtime(ordered_at,'%k') as UNSIGNED INTEGER) asc";

        $last_hour_order = Yii::app()->sdb_lsh_market->createCommand($sql)->queryAll();
        $result['hourorder'] = !empty($last_hour_order) ? $last_hour_order : array();

        //到货\缺货
        $sql = "select * from(select dt,hour,item_id,tag from {$this->item_inventory_upt} where dt='{$date}' and tag in(1,2) group by item_id union all select dt,hour,item_id,tag from {$this->item_inventory_upt} where dt='{$date}' and tag = 0)t order by hour desc";
        $result['inventory'] = Yii::app()->sdb_lsh_predict->createCommand($sql)->queryAll();

        return $result;
    }

    function getSkuName ( $item_ids ) {
        $sql = "select item_id,name from item_sku where item_id in ({$item_ids}) and status = 2 and is_valid = 1";
        $data = Yii::app()->sdb_lsh_market->createCommand($sql)->queryAll();
        return !empty($data) ? $data : array();
    }

    function fetchYgorderResult ($param) {
        $timestamp = time();
        $tmpArr = array($this->token, $timestamp);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        $params['signature'] = $tmpStr;
        $params['timestamp'] = $timestamp;
        $params['zone_id'] = $param['zone_id'];
        $strparams = http_build_query($params);
        $result = Yii::app()->curl->get($this->rquest_url . "?" . $strparams);
        $data = array();

        if ($result['http_code'] == 200) {
            if ( strlen($result['body']) > 0 ) {
                $tmp_data = json_decode($result['body'],true);
                $data = $tmp_data['data'];
            }
        }
        return $data;
    }

    function fetchManagerAndSaler ($zone_id=null) {

        $sql = "select a.uid,a.f_uid,a.zone_id,b.sales_name,b.level from salesman_info a, user_info b where a.main_uid = b.uid and b.level in (6,7) and a.status = 1";

        if (isset($zone_id)) {
            $sql .= " and a.zone_id = {$zone_id}";
        }

        $result = Yii::app()->sdb_lsh_sales->createCommand($sql)->queryAll();
        return !empty($result) ? $result : array();
    }

    //公共获取api数据
    function fetchApidata ( $request_uri, $param_arr = array() ) {

        $timestamp = time();
        $tmpArr = array($this->token, $timestamp);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        $params['signature'] = $tmpStr;
        $params['timestamp'] = $timestamp;
        $params = array_merge($params,$param_arr);
        $strparams = http_build_query($params);
        $result = Yii::app()->curl->get($request_uri . "?" . $strparams);
        $data = array();

        if ($result['http_code'] == 200) {
            if ( strlen($result['body']) > 0 ) {
                $tmp_data = json_decode($result['body'],true);
                $data = $tmp_data['data'];
            }
        }
        return $data;
    }

    function setYgongRkeys ($zone_id) {
        $key = 'YGKEY_';
        if ($zone_id == 0) {
            $key.='QuanGuo_0';
        } elseif ($zone_id == 1000) {
            $key.='BeiJing_1000';
        } elseif ($zone_id == 1001) {
            $key.='TianJing_1001';
        }
        return md5($key);
    }

    function openCache () {
        return self::$cache_open;
    }


    function exportXls($titles, $columns, $rows, $filename=''){
        if(empty($filename)) $filename = date('Ymd') . '.xls';
        Header ( "Content-type:   application/octet-stream " );
        Header ( "Accept-Ranges:   bytes " );
        Header ( "Content-type:application/vnd.ms-excel;charset=utf-8" );
        Header ( "Content-Disposition:attachment;filename=" . $filename );
        header ( 'content-Type:application/vnd.ms-excel;charset=utf-8' );

        $html  = "";
        $html .='<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $html .='<table border=1><thead><tr>';

        foreach ($titles as $key=>$val){
            $html .= '<td style="background:cornsilk">'.$val.'</td>';
        }

        $html .='</tr></thead>';
        $html .='<tbody>';
        foreach ( $rows as $key=>$val){
            $html .='<tr>';
            //数据字段
            foreach ($columns as $k=>$v){

                if(isset($val[$v])){
                    //字符替换
                    $valStr  = $val[$v];
                    $valStr  = str_replace('<', '&lt;', $valStr);
                    $valStr  = str_replace('>', '&gt;', $valStr);
//                    $html .='<td>'.$valStr.'</td>';
                    if(is_numeric($valStr) ){
                        if( strlen($valStr) >=12 ){
                            $html .='<td style="mso-number-format:\@;">'.$valStr.'</td>';
                        }else{
                            $html .='<td>'.$valStr.'</td>';
                        }
                    }else{
                        $html .='<td>'.$valStr.'</td>';
                    }
                }else {
                    foreach($val['date_list'] as $date_key=>$date_val){
                        $dayNum='';
//                        $sStyle ='';//'display:inline-block;margin: 0 -6px;width:117%;text-align:center;';
                        if($date_val['date']==$v){
                            $sStyle ='text-align:center;width:117%;';//'display:inline-block;margin: 0 -6px;width:117%;text-align:center;';
                            if(intval($date_val['day_nums'])>0){
                                $sStyle .= 'font-weight:bold;font-size:13px;';
                            }

                            if(intval($date_val['is_tejia'])>0){
                                $sStyle .= 'font-weight:bold;font-size:13px;font-style:italic;';
                            }
                            if(intval($date_val['is_manjian'])>0){
                                $sStyle .= 'font-weight:bold;font-size:13px;text-decoration: underline;';
                            }
                            if(intval($date_val['is_visit'])>0){
                                $sStyle .= 'height:30px;font-weight:bold;font-size:13px;background-color:green;color: white;';
                            }
                            if(intval($date_val['is_taocan'])>0){
                                $sStyle .= 'font-weight:bold;font-size:13px;color:red;';
                            }

                            if(intval($date_val['is_miaosha'])>0){
                                $sStyle .= 'font-weight:bold;font-size:14px;';
                            }

                            $dayNum = ($date_val['day_nums'] == 0) ? '' : $date_val['day_nums'];

                            if(intval($date_val['is_miaosha'])>0){
                                $dayNum .= '*';
                            }

                            $html .='<td style="'.$sStyle.'">'.$dayNum.'</td>';
                        }
                    }
                }
            }
            $html .='</tr>';
        }
        $html .='</tbody></table>';
        echo $html;
    }

}