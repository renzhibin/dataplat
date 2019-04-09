<?php
/**
 * Created by PhpStorm.
 * User: liangbo
 * Date: 16/6/25
 * Time: 上午10:27
 */

class GPSManager extends Manager
{
    static $cache_open = true;
    function __construct()
    {
        $this->classifyTable = 'gps_classify_position';
        $this->origTable = 'gps_orig_position';
        $this->statisticsTable = 'gps_waybill_statistics';
        $this->classifyTable_v2 = 'gps_classify_position_v2';
        $this->classifyTable_v3 = 'gps_classify_position_v3';
        $this->waybillTable = 'gps_waybill_info';
        $this->waybillTable_v2 = 'gps_waybill_info_v2';
        $this->waybillTable_v3 = 'v_gps_waybill_info_v3';
        $this->wholesalerTable = 'gps_wholesaler_zone';
        $this->transUserTable = 'trans_user';
        $this->orderWaybillTable = 'order_waybill';
        $this->remarkInfoTable = 'gps_remark_info';
        $this->sale_zone_coords='sale_zone_coords';
        $this->sale_zone_coords_hz='sale_zone_coords_hz';
    }

    //获取gps运动轨迹信息
    function getClassifyPositions($trans_uid = '', $min_time = '', $max_time = '', $version)
    {

        $classifyTable = ($version == 1) ? $this->classifyTable : $this->classifyTable_v2;
        $sql = "select latitude,longitude,pos_type,stay_span,location_time from {$classifyTable} where 1=1 ";
        if ($trans_uid != '') {
            $sql .= " and trans_uid = {$trans_uid}";
        }
        if ($min_time != '') {
            $sql .= " and location_time >= '{$min_time}'";
        }
        if ($max_time != '') {
            $sql .= " and location_time <= '{$max_time}'";
        }
        $sql .= " order by location_time";

        $db = Yii::app()->sdb_gps;
        $position_data = $db->createCommand($sql)->queryAll();

        return $position_data;
    }

    //获取批发市场区域
    function getWholesalerZone()
    {
        $sql = "select wholesaler_name,zone_latitude,zone_longitude from {$this->wholesalerTable} where 1=1 ";
        $db = Yii::app()->sdb_gps;
        $wholesaler_data = $db->createCommand($sql)->queryAll();

        $wholesaler_map = array();
        if(!empty($wholesaler_data)) {
            foreach($wholesaler_data as $wholesaler_pos) {
                $wholesaler_map[$wholesaler_pos['wholesaler_name']][] = $wholesaler_pos;
            }
        }

        return $wholesaler_map;
    }

    //获取运单信息
    function getWaybillInfo($trans_uid = '', $date = '', $version, $gps_arr)
    {

        $waybillTable = '';
        if ($version == 1) {
            $waybillTable = $this->waybillTable;
        }else if ($version == 2) {
            $waybillTable = $this->waybillTable_v2;
        }else if ($version == 3) {
            $waybillTable = $this->waybillTable_v3;
        }

        $sql = "select waybill_no,shipping_order_id,shipped_at,receipt_at,shop_latitude,shop_longitude,nearest_latitude,nearest_longitude,nearest_time,receipt_latitude,receipt_longitude";
        $sql .= " from {$waybillTable} where 1=1 ";

        if ($trans_uid != '') {
            $sql .= " and trans_uid = {$trans_uid}";
        }

        if ($date != '') {

            if($version == 1) {
                $sql .= " and FROM_UNIXTIME(shipped_at + (60*60*24),'%Y-%m-%d') = '{$date}'";
            } else if($version == 3) {
                if ( !empty($gps_arr) ) {
                    $arrive_st = $gps_arr['arrive_st'];
                    $arrive_end = $gps_arr['arrive_end'];
                    $sql .= " and arrived_at >= {$arrive_st} and arrived_at <= {$arrive_end}";
                }
            } else {
                $sql .= " and FROM_UNIXTIME(shipped_at,'%Y-%m-%d') = '{$date}'";
            }
            $sql .= " order by shipped_at asc";
        }

        $db = Yii::app()->sdb_gps;
        $waybill_data = $db->createCommand($sql)->queryAll();

        if ( !empty($waybill_data) ) {
            $order_list = array_column($waybill_data,'shipping_order_id');
            $order_str = implode(',',$order_list);

            $sql = "select
              a.market_name,
              a.contact_name,
              a.contact_phone,
              a.telephone,
              a.emerg_cellphone,
              a.address_id,
              a.address,
              b.shipping_order_id
              from user_address a inner join order_shipping_head b
              on a.address_id = b.address_id
              where b.shipping_order_id in({$order_str})";
            $mdb = Yii::app()->sdb_lsh_market;
            $market_data = $mdb->createCommand($sql)->queryAll();

            $tmp_arr = array();
            if ( !empty($market_data) ) {
                foreach( $market_data as $k=>$val ) {
                    $tmp_arr[$val['shipping_order_id']] = $market_data[$k];
                }
            }

            foreach ( $waybill_data as $key=>$value ) {
                if ( isset($tmp_arr[$value['shipping_order_id']]) ) {
                    $waybill_data[$key] = array_merge($waybill_data[$key],$tmp_arr[$value['shipping_order_id']]);
                }
            }
        }

        return !empty($waybill_data) ? $waybill_data : array();
    }

    function getTransuidToName ( $name, $phone ) {

        $data = array();

        $where = ' where 1=1 and is_deleted = 0';
        if ( !empty( $name ) ) {
            $where .= " and name = '{$name}'";
        }

        if ( !empty( $phone ) ) {
            $where .= " and cellphone = '{$phone}'";
        }

        $trans_user = $this->transUserTable;
        $sql = "select uid from {$trans_user}{$where}";
        $db = Yii::app()->sdb_gps;
        $res = $db->createCommand($sql)->queryRow();
        return !empty($res) ? $res['uid'] : $data;
    }

    function getPhoneToName ( $name ) {

        $trans_user = $this->transUserTable;
        $where = ' where 1=1 and is_deleted = 0';
        if ( !empty( $name ) ) {
            $where .= " and `name` = '{$name}'";
        }
        $sql = "select DISTINCT cellphone from {$trans_user}{$where}";
        $db = Yii::app()->sdb_gps;
        $res = $db->createCommand($sql)->queryAll();
        return !empty($res) ? $res : array();

    }

    function getTransInfo ( $trans_id ) {

        $trans_user = $this->transUserTable;
        $sql = "select uid,name,cellphone from {$trans_user} where `uid` = '{$trans_id}' and is_deleted = 0";
        $db = Yii::app()->sdb_gps;
        $res = $db->createCommand($sql)->queryRow();
        return !empty($res) ? $res : array();

    }

    function getTransuidToWillno ( $trans_bill, $version ) {

        $waybillTable = '';
        if ($version == 1) {
            $waybillTable = $this->waybillTable;
        }else if ($version == 2) {
            $waybillTable = $this->waybillTable_v2;
        }else if ($version == 3) {
            $waybillTable = $this->waybillTable_v3;
        }

        $sql = " select trans_uid from {$waybillTable} where `waybill_no` = {$trans_bill}";
        $db = Yii::app()->sdb_gps;
        $res = $db->createCommand($sql)->queryRow();
        return !empty($res) ? $res['trans_uid'] : array();

    }

    function addRemark( $params ) {

        $keyArr = array();
        $keyValue = array();
        $parament = array();
        foreach ($params as $k => $v) {
            $keyArr[] = "`".$k."`";
            $keyValue[] = ":".$k."";
            $parament[":".$k.""] = $v;
        }
        $keyArr_str = implode(',',$keyArr);
        $keyValue_str = implode(',',$keyValue);
        $sql = "REPLACE INTO {$this->remarkInfoTable}({$keyArr_str}) VALUES ({$keyValue_str})";
        $res = Yii::app()->db_gps->createCommand($sql)->execute($parament);
        return $res > 0 ? true : false;

    }

    function fetchRemarkInfo( $trans_id, $loc_time ) {
        $trans_table = $this->remarkInfoTable;
        $sql = "select trans_uid,trans_name,trans_phone,still_timestamp,remark_name,remark_time,remark_content from {$trans_table} where `trans_uid` = '{$trans_id}' and `still_timestamp` = '{$loc_time}'";
        $db = Yii::app()->sdb_gps;
        $res = $db->createCommand($sql)->queryRow();
        return !empty($res) ? $res : array();
    }

    function getClassifyPositionsInfo( $trans_uid,$param_date ) {

        $classifyTable = $this->classifyTable_v3;
        $sql = "select latitude,longitude,pos_type,stay_span,start_time,end_time,st_area from {$classifyTable} where 1=1";

        if ( isset($trans_uid) ) {
            $sql .= " and trans_uid = {$trans_uid}";
        }

        if ( count($param_date) == 1 ) {
            $sql .= " and FROM_UNIXTIME(start_time,'%Y-%m-%d') = '{$param_date['date']}'";
        } else {
            $sql .= " and start_time >= {$param_date['start_time']} and start_time <= {$param_date['end_time']}";
        }
        $sql .= " order by start_time asc";

        //echo $sql;exit;
        $db = Yii::app()->sdb_gps;
        $position_data = $db->createCommand($sql)->queryAll();

        return $position_data;
    }

    function getGpsTransUserList () {
        $date = date("Y-m-d");
        $start_time = strtotime($date.' 00:00:00');
        $end_time = strtotime($date.' 23:59:59');
        $classifyTable = $this->classifyTable_v3;
        $trans_user = $this->transUserTable;

        $sql = "select b.name,b.cellphone FROM(
	select distinct trans_uid from {$classifyTable} where start_time>={$start_time} and end_time<={$end_time}
) a inner join (
	select uid,name,cellphone from {$trans_user} where is_deleted=0
) b on a.trans_uid = b.uid";
        $db = Yii::app()->sdb_gps;
        $trans_data = $db->createCommand($sql)->queryAll();
        return !empty($trans_data) ? $trans_data : array();
    }

    function getWaybillInfov3( $trans_uid,$start_time,$end_time,$search_scence,$waybill_no=null ) {

        $waybillTable = $this->waybillTable_v3;
        if ($search_scence == 1) {
            //$sql = "select waybill_no,shipping_order_id,arrived_at,shipped_at,receipt_at,shop_latitude,shop_longitude,nearest_latitude,nearest_longitude,nearest_time,receipt_latitude,receipt_longitude,market_name,contact_name,contact_phone,address,address_id from {$waybillTable} where trans_uid = {$trans_uid} and shipped_at>={$start_time} and shipped_at<={$end_time}";
            $sql_waybill = "select extend from ods_whd_gps_trail_run_mail where loca_time>={$start_time} and loca_time<={$end_time} and trans_uid = {$trans_uid}";
            $waybill_list = Yii::app()->sdb_gps->createCommand($sql_waybill)->queryAll();
            $tmp_bill = array();
            if (!empty($waybill_list)) {
                foreach ($waybill_list as $val) {
                    if (!empty($val['extend'])) {
                        $json_bill = json_decode($val['extend'],true);
                        if (!empty($json_bill['ship_list'])) {
                            $att = explode(',',$json_bill['ship_list']);
                            foreach ($att as $item) {
                                $btt = explode(':',$item);
                                $ctt = "'".$btt[0]."'";
                                if(!in_array($ctt,$tmp_bill)){
                                    array_push($tmp_bill,$ctt);
                                }
                            }
                        }
                    }
                }
            }
            if (empty($tmp_bill)) {
                return array();
            }

            $tmp_bill_str = implode(',',$tmp_bill);
            $sql = "select waybill_no,shipping_order_id,arrived_at,shipped_at,receipt_at,shop_latitude,shop_longitude,nearest_latitude,nearest_longitude,nearest_time,receipt_latitude,receipt_longitude,market_name,contact_name,contact_phone,address,address_id from {$waybillTable} where waybill_no in({$tmp_bill_str})";
        } else if($search_scence == 2){
            $sql = "select waybill_no,shipping_order_id,arrived_at,shipped_at,receipt_at,shop_latitude,shop_longitude,nearest_latitude,nearest_longitude,nearest_time,receipt_latitude,receipt_longitude,market_name,contact_name,contact_phone,address,address_id from {$waybillTable} where trans_uid = {$trans_uid} and waybill_no = {$waybill_no}";
        } else {
            $sql = "select waybill_no,shipping_order_id,arrived_at,shipped_at,receipt_at,shop_latitude,shop_longitude,nearest_latitude,nearest_longitude,nearest_time,receipt_latitude,receipt_longitude,market_name,contact_name,contact_phone,address,address_id from {$waybillTable} where waybill_no in(select waybill_no from {$waybillTable} where trans_uid = {$trans_uid} and arrived_at>={$start_time} and arrived_at<={$end_time})";
        }

        //运单顺序
        $sql2 = "select a.*,b.seq from ({$sql}) a left join (select order_id,seq from order_wave_detail where status = 1) b on a.shipping_order_id = b.order_id";

        $db = Yii::app()->sdb_gps;
        $waybill_data = $db->createCommand($sql2)->queryAll();
        return !empty($waybill_data) ? $waybill_data : array();
    }

    function getwaybilltime ( $trans_bill ) {

        $waybillTable = $this->waybillTable_v3;
        $sql = "select trans_uid,min(shipped_at) as shipped_at,max(arrived_at) as arrived_max_at from {$waybillTable} where waybill_no = '{$trans_bill}'";
        $db = Yii::app()->sdb_gps;
        $waybill_data = $db->createCommand($sql)->queryAll();
        return $waybill_data;
    }

    function setGpsRkeys ($gps_key) {
        $key = 'GPSKEY_';
        return $key.md5($gps_key);
    }

    function openCache () {
        return self::$cache_open;
    }

    function getSaleZoneCoords($params){
        $sale_zone_coords=$this->sale_zone_coords;
        $sale_zone_coords_hz=$this->sale_zone_coords_hz;
        $bj_sql="select sale_name,uid,zone_id,center_coord,`position`,style,`desc` from {$sale_zone_coords}";
        $union=' union ';
        $hz_sql="select sale_name,uid,zone_id,center_coord,`position`,style,`desc` from {$sale_zone_coords_hz}";

        $sql=$bj_sql.$union.$hz_sql;

        if(array_key_exists('region',$params) and strval($params['region'])=='1000'){
            $sql=$bj_sql;
        }
        if(array_key_exists('region',$params) and strval($params['region'])=='1002'){
            $sql=$hz_sql;
        }
        if(array_key_exists('region',$params) and strval($params['region'])=='1001'){
           return array();
        }

        $db=Yii::app()->sdb_dt_db;
        $data=$db->createCommand($sql)->queryAll();
        return $data;

    }

}
