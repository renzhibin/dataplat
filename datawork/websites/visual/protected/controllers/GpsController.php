<?php
/**
 * Created by PhpStorm.
 * User: liangbo
 * Date: 16/6/25
 * Time: 上午10:23
 */

class GpsController extends Controller
{
    function __construct()
    {
        $this->objMenu = new MenuManager();
        $this->objAuth = new AuthManager();
        $this->objProject = new ProjectManager();
        $this->objGPS = new GPSManager();
    }

    public function actionIndex()
    {
        $this->redirect('/gps/showroute');
    }

    public function actionShowRoute()
    {
        $trans_bill = isset($_REQUEST['trans_bill']) ? $_REQUEST['trans_bill'] : '';
        $trans_name = isset($_REQUEST['trans_name']) ? $_REQUEST['trans_name'] : '';
        $trans_phone = isset($_REQUEST['trans_phone']) ? $_REQUEST['trans_phone'] : '';
        $trans_uid = isset($_REQUEST['trans_uid']) ? $_REQUEST['trans_uid'] : '';
        $date = isset($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m-d');
        $runtime = !empty($_REQUEST['runtime']) ? intval($_REQUEST['runtime']) : 24;
        $version = isset($_REQUEST['version']) ? intval($_REQUEST['version']) : 2;

        if ( empty($trans_bill) && empty($trans_name) && empty($trans_phone) && empty($trans_uid) ) {
            $wholesalerZone = $this->objGPS->getWholesalerZone();
            $tplArr['waybillInfos'] = array();
            $tplArr['gpsPosition'] = array();
            $tplArr['wholesalerZone'] = $wholesalerZone;
            $tplArr['transInfo'] = array();
            $this->render('gps/showroute.tpl', $tplArr);
            exit;
        }

        if ( !empty( $trans_uid ) ) {
            $trans_uid =  $_REQUEST['trans_uid'];
        } else {
            if ( !empty( $trans_bill ) ) {
                $trans_uid = $this->objGPS->getTransuidToWillno( $trans_bill, $version );
            } else {
                $trans_uid = $this->objGPS->getTransuidToName( $trans_name, $trans_phone );
            }
        }

        $waybillInfos = array();
        $gpsPosition = array();
        $transInfo = array();

        if ( !empty($trans_uid) ) {

            $gps_arr = array();
            $arrive_st = strtotime($date.' 00:00:00');
            $arrive_end = $arrive_st + 3600 * $runtime;
            if ($version == 3) {
                $gps_arr['arrive_st'] = $arrive_st;
                $gps_arr['arrive_end'] = $arrive_end;
            }
            $waybillInfos = $this->objGPS->getWaybillInfo($trans_uid, $date, $version, $gps_arr);
            if (count($waybillInfos) > 0) {

                $timestamp_st = '';
                $timestamp_end = '';
                if ($version == 3) {
                    $timestamp_st = $arrive_st;
                    $timestamp_end = $arrive_end;
                } else {
                    $timestamp_st = $waybillInfos[0]['shipped_at'];
                    $timestamp_end = $timestamp_st + 60 * 60 * $runtime;
                }

                $gpsPosition = $this->objGPS->getClassifyPositions($trans_uid, $timestamp_st, $timestamp_end, $version);
            }
            $transInfo = $this->objGPS->getTransInfo($trans_uid);
        }

        $wholesalerZone = $this->objGPS->getWholesalerZone();
        /*
        //面包屑导航
        $indexStr[] = array('href' => "../visual/index", 'content' => '首页');
        $indexStr[] = array('href' => "../privilege/reportroles", 'content' => '报表分组管理');
        $indexStr[] = array('href' => "#", 'content' => '用户分组管理');
        */

        //获取备注信息
        if ( !empty($gpsPosition) ) {
            foreach ($gpsPosition as &$value) {
                if ($value['pos_type'] == 1) {
                    $value['remarkinfo'] = $this->objGPS->fetchRemarkInfo($trans_uid,$value['location_time']);
                }
            }
        }

        $tplArr['waybillInfos'] = $waybillInfos;
        $tplArr['gpsPosition'] = $gpsPosition;
        $tplArr['wholesalerZone'] = $wholesalerZone;
        $tplArr['transInfo'] = $transInfo;
        file_put_contents('/tmp/liangbo_gps',print_r($tplArr,1));
        $this->render('gps/showroute.tpl', $tplArr);
    }

    public function actionGpsTrace () {

        $search_scence = isset($_REQUEST['search_scence']) ? $_REQUEST['search_scence'] : '';
        $trans_name = isset($_REQUEST['trans_name']) ? $_REQUEST['trans_name'] : '';
        $trans_phone = isset($_REQUEST['trans_phone']) ? $_REQUEST['trans_phone'] : '';
        $trans_bill = isset($_REQUEST['trans_bill']) ? $_REQUEST['trans_bill'] : '';
        $date = date('Y-m-d');
        $gpsPosition = array();
        $waybillInfos = array();
        if ($search_scence == 1) {

            //实时轨迹
            $trans_uid = array();
            if (!empty($trans_name) && !empty($trans_phone)) {
                $trans_uid = $this->objGPS->getTransuidToName( $trans_name, $trans_phone );
            }
            if ( !empty($trans_uid) ) {
                $param_date['date'] = $date;
                $gpsPosition = $this->objGPS->getClassifyPositionsInfo( $trans_uid, $param_date );

                //$start_time = strtotime($date.' 00:00:00');
                $yes_date = date("Y-m-d");
                $start_time = strtotime($date.' 00:00:00');
                $end_time = strtotime($date.' 23:59:59');
                $waybillInfos = $this->objGPS->getWaybillInfov3( $trans_uid, $start_time, $end_time,$search_scence );
                if ( !empty($waybillInfos) ) {
                    foreach ($waybillInfos as $kk=>&$val) {
                        //1为异常订单 0为正常
                        $val['waybill_st'] = empty($val['arrived_at']) ? 1 : 0;
                        if (isset($val['arrived_at']) && $val['arrived_at'] !=0) {
                            $yes_arrived_at = date('Y-m-d',$val['arrived_at']);
                            if ($yes_arrived_at < $yes_date) {
                                unset($waybillInfos[$kk]);
                            }
                        }
                    }
                }
                $waybillInfos = array_values($waybillInfos);
            }

        } else if ($search_scence == 2) {

            //历史运单轨迹
            $waybill_time = $this->objGPS->getwaybilltime( $trans_bill );
            if ( !empty($waybill_time[0]['trans_uid']) && !empty($waybill_time[0]['shipped_at']) && !empty($waybill_time[0]['arrived_max_at']) ) {
                $trans_uid = $waybill_time[0]['trans_uid'];
                $start_time = $waybill_time[0]['shipped_at'];
                $end_time = $waybill_time[0]['arrived_max_at'];
                $waybillInfos = $this->objGPS->getWaybillInfov3( $trans_uid, $start_time, $end_time,$search_scence,$trans_bill );
                if ( !empty($waybillInfos) ) {
                    foreach ($waybillInfos as &$val) {
                        //1为异常订单 0为正常
                        $val['waybill_st'] = empty($val['arrived_at']) ? 1 : 0;
                    }

                    $param_date['start_time'] = $start_time;
                    $param_date['end_time'] = $end_time;
                    $gpsPosition = $this->objGPS->getClassifyPositionsInfo( $trans_uid, $param_date );
                }
            }

        } else {

            //根据司机信息查询历史轨迹
            $trans_uid = $this->objGPS->getTransuidToName( $trans_name, $trans_phone );
            $start_time = strtotime($_REQUEST['start_date'].' 00:00:00');
            $end_time = strtotime($_REQUEST['end_date'].' 23:59:59');
            if ( !empty($trans_uid) ) {
                $waybillInfos = $this->objGPS->getWaybillInfov3( $trans_uid, $start_time, $end_time,$search_scence );
                if ( !empty($waybillInfos) ) {
                    foreach ($waybillInfos as &$val) {
                        //1为异常订单 0为正常
                        $val['waybill_st'] = empty($val['arrived_at']) ? 1 : 0;
                    }

                    $param_date['start_time'] = $start_time;
                    $param_date['end_time'] = $end_time;
                    $gpsPosition = $this->objGPS->getClassifyPositionsInfo( $trans_uid, $param_date );
                }
            }

        }

        $transInfo = array();
        if ( isset($trans_uid) ) {
            $transInfo = $this->objGPS->getTransInfo($trans_uid);
        }

        $wholesalerZone = $this->objGPS->getWholesalerZone();

        if ( $this->objGPS->openCache() ) {
            $rediskey = $this->objGPS->setGpsRkeys('gps_trans_user_list');
            $cachedata = Yii::app()->cache->get($rediskey);
            if ( !empty($cachedata) ) {
                $gps_trans_user_list = json_decode($cachedata,true);
            } else {
                $gps_trans_user_list = $this->objGPS->getGpsTransUserList();
                $cachedata = json_encode($gps_trans_user_list);
                Yii::app()->cache->set($rediskey, $cachedata, '300');
            }
        } else {
            $gps_trans_user_list = $this->objGPS->getGpsTransUserList();
        }

        $tplArr['gpsPosition'] = $gpsPosition;
        $tplArr['waybillInfos'] = $waybillInfos;
        $tplArr['wholesalerZone'] = $wholesalerZone;
        $tplArr['gpstransuserlist'] = $gps_trans_user_list;
        $tplArr['transInfo'] = $transInfo;
        $this->render('gps/showgpstrace.tpl', $tplArr);
    }


    public function actionGetPhone ( $name ) {

        $result = $this->objGPS->getPhoneToName( $name );
        echo json_encode($result);
        exit;

    }

    public function actionAddRemark () {

        $response = array();
        if ( !empty($_REQUEST['remarkInfo']) ) {
            $mark_arr = $_REQUEST['remarkInfo'];
            $remark_time = date('Y-m-d H:i:s');
            $params = array();
            if ( !empty($mark_arr) ) {
                $params['trans_uid'] = $mark_arr['trans_info']['uid'];
                $params['trans_name'] = $mark_arr['trans_info']['name'];
                $params['trans_phone'] = $mark_arr['trans_info']['cellphone'];
                $params['still_timestamp'] = $mark_arr['still_timestamp'];
                $params['remark_name'] = Yii::app()->user->username;
                $params['remark_time'] = $remark_time;
                $params['remark_content'] = addslashes( $mark_arr['remark_content'] );
                $result = $this->objGPS->addRemark( $params );
                if ($result) {
                    $response['ret'] = 1;
                    $response['msg'] = '成功';
                } else {
                    $response['ret'] = 0;
                    $response['msg'] = '失败';
                }
                echo json_encode($response);
                exit;
            }
        }

        $response['ret'] = 0;
        $response['msg'] = '没有查询信息';
        echo json_encode($response);
        exit;
    }
}


