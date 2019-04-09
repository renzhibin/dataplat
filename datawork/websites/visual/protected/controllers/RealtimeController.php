<?php
/**
 * Created by PhpStorm.
 * User: gide
 * Date: 16/6/23
 * Time: 16:53
 * desc realtime order
 */

class RealtimeController extends Controller
{

    public $objArr = array();

    function __construct()
    {
        $this->actionRegister('RealtimeManager');
    }

    public function actionRegister($obj){

        if( !isset( $this->objArr[$obj] ) ) {
            return $this->objArr[$obj] = new $obj();
        }

        return $this->objArr[$obj];
    }

    public function actionIndex()
    {
        $this->redirect('/visual/index');
    }

    public function actionOmsorder() {
        $date = date("Y-m-d");

        if (!empty($_REQUEST['date'])) {
            $date = $_REQUEST['date'];
        }

        $yestoday = date("Y-m-d", strtotime("$date -1 days"));
        $last_7day = date("Y-m-d", strtotime("$date -7 days"));

        $version = isset($_REQUEST['version']) ? $_REQUEST['version'] : 2;
        $zone_id = isset($_REQUEST['zoneid']) ? $_REQUEST['zoneid'] : 1000;

        $result = $this->objArr['RealtimeManager']->fetchOmsOrderResult($date,$yestoday,$last_7day,$version,$zone_id);

        echo json_encode($result);
        exit;
    }

    public function actionFetchOrder() {

        $date = date("Y-m-d");

        if (!empty($_REQUEST['date'])) {
            $date = $_REQUEST['date'];
        }

        $yestoday = date("Y-m-d", strtotime("$date -1 days"));
        $last_7day = date("Y-m-d", strtotime("$date -7 days"));

        $version = isset($_REQUEST['version']) ? $_REQUEST['version'] : 2;
        $zone_id = isset($_REQUEST['zoneid']) ? $_REQUEST['zoneid'] : 1000;

        //$result = $this->objArr['RealtimeManager']->fetchSoldOrderResult($date,$yestoday,$last_7day,$version,$zone_id);

        //oms开关
        $result = $this->objArr['RealtimeManager']->fetchOmsOrderResult($date,$yestoday,$last_7day,$version,$zone_id);

        $data = array();

        //to do today prediction
        if ( empty($result['maxpre']) ) {
            $data['maxpre']['dt'] = $date;
            $data['maxpre']['hour'] = 0;
            $data['maxpre']['prediction'] = 0;
        } else {
            $data['maxpre'] = $result['maxpre'];
        }

        //to do lastorder
        if ( empty($result['lastorder']) ) {
            $data['yesorder'] = 0;
            $data['lastorder'] = 0;
        } else {
            foreach($result['lastorder'] as $value) {
                if (isset($value['dt']) && $value['dt'] == $yestoday) {
                    $data['yesorder'] = !empty($value['totalorder']) ? $value['totalorder'] : 0;
                }

                if (isset($value['dt']) && $value['dt'] == $last_7day) {
                    $data['lastorder'] = !empty($value['totalorder']) ? $value['totalorder'] : 0;
                }
            }
        }

        //to do last hour order
        if ( empty($result['hourorder']) ) {

            for ($i=0; $i<24; $i++) {
                $pub_array[] = 0;
            }
            $data['hourorder']['torder'] = $pub_array;
            $data['hourorder']['yorder'] = $pub_array;
            $data['hourorder']['lorder'] = $pub_array;

        } else {

            $tmp_arr = array();
            foreach($result['hourorder'] as $val) {
                if (isset($val['dt']) && $val['dt'] == $date) {
                    $tmp_arr['hourorder'][$date][] = $val;
                } else if (isset($val['dt']) && $val['dt'] == $yestoday) {
                    $tmp_arr['hourorder'][$yestoday][] = $val;
                } else if (isset($val['dt']) && $val['dt'] == $last_7day) {
                    $tmp_arr['hourorder'][$last_7day][] = $val;
                }
            }

            $t_hlist = array();
            $y_hlist = array();
            $l_hlist = array();

            foreach ($tmp_arr['hourorder'] as $key=>$item) {
                if ( $key== $date ) {

                    foreach ($item as $v1) {
                        $t_hlist[$v1['hour']] = $v1['hour_order'];
                    }

                } elseif ( $key== $yestoday ) {

                    foreach ($item as $v2) {
                        $y_hlist[$v2['hour']] = $v2['hour_order'];
                    }

                } elseif ( $key== $last_7day ) {

                    foreach ($item as $v3) {
                        $l_hlist[$v3['hour']] = $v3['hour_order'];
                    }
                }
            }

            $t_arr = array();
            $y_arr = array();
            $l_arr = array();

            $callback = function ( &$fn_list,&$fn_arr ) {
                for ( $i=0; $i<24; $i++ ) {
                    if ( isset( $fn_list[$i] ) ) {
                        $fn_arr[] = intval( $fn_list[$i] );
                    } else {
                        $fn_arr[] = 0;
                    }
                }
            };

            $callback($t_hlist,$t_arr);
            $callback($y_hlist,$y_arr);
            $callback($l_hlist,$l_arr);

            $hour_step = intval(date('H'));
            //时间没开始的不显示
            $t_arr = array_slice($t_arr,0,$hour_step+1);

            $data['hourorder']['torder'] = $t_arr;
            $data['hourorder']['yorder'] = $y_arr;
            $data['hourorder']['lorder'] = $l_arr;

        }

        //未来预测值
        $data['future']['data'] = $result['future'];
        $data['future']['intro'] = '由于订单数量受到临时到货和缺货影响比较明显,此数据仅供参考';
        $tplArr['resultList'] = $data;

        //获取商品名称

        if ( !empty($result['inventory']) ) {
            $sku_arr = array();
            $item_id = array_column($result['inventory'],'item_id');
            $item_id = array_flip(array_flip($item_id));
            $item_ids = implode(',',$item_id);
            $sku_arr =  $this->RealtimeManager->getSkuName( $item_ids );
            $sku_arrs = array_column($sku_arr,'name','item_id');

            $tag_name = array('已到货','即将缺货','已缺货');
            foreach ( $result['inventory'] as &$value ) {
                $value['name'] = $sku_arrs[$value['item_id']];
                $value['tag'] = $tag_name[$value['tag']];
                if ($value['hour'] < 10 ) {
                    $value['hour'] = '0'.$value['hour'];
                }
            }
            $tplArr['inventory'] = $result['inventory'];
        } else {
            $tplArr['inventory'] = array();
        }

        $this->render('realtimetpl/realtimeorder.tpl', $tplArr);
    }

    public function actionFetchYgorder() {

        if ( empty(Yii::app()->user->username) ) {
            Yii::app()->request->redirect('/visual/index');
        }

        $zone_id = isset($_REQUEST['zone_id']) ? $_REQUEST['zone_id'] : 1000;
        $params['zone_id'] = $zone_id;
        $data = array();
        //$data = $this->RealtimeManager->fetchYgorderResult($params);
        if ( $this->RealtimeManager->openCache() ) {
            $rediskey = $this->RealtimeManager->setYgongRkeys($zone_id);
            $cachedata = Yii::app()->cache->get($rediskey);
            if ( !empty($cachedata) ) {
                $data = json_decode($cachedata,true);
            } else {
                $data = $this->RealtimeManager->fetchYgorderResult($params);
                $cachedata = json_encode($data);
                Yii::app()->cache->set($rediskey, $cachedata, '120');
            }
        } else {

            $data = $this->RealtimeManager->fetchYgorderResult($params);
        }

        $data['flushtime'] = date('Y-m-d H:i:s');
        $tplArr['resultList'] = $data;
        $this->render('realtimetpl/realtimeygorder.tpl', $tplArr);
    }

    public function actionFetchYgData () {
        $zone_id = isset($_REQUEST['zone_id']) ? $_REQUEST['zone_id'] : 1000;
        $params['zone_id'] = $zone_id;
        //$start = microtime(true);
        $data = array();
        //$data = $this->RealtimeManager->fetchYgorderResult($params);
        if ( $this->RealtimeManager->openCache() ) {
            $rediskey = $this->RealtimeManager->setYgongRkeys($zone_id);
            $cachedata = Yii::app()->cache->get($rediskey);
            if ( !empty($cachedata) ) {
                $data = json_decode($cachedata,true);
            } else {
                $data = $this->RealtimeManager->fetchYgorderResult($params);
                $cachedata = json_encode($data);
                Yii::app()->cache->set($rediskey, $cachedata, '120');
            }
        } else {
            $data = $this->RealtimeManager->fetchYgorderResult($params);
        }
        //$spend = round(microtime(true) - $start,3);
        //file_put_contents('/home/inf/deploy/spend.txt',$spend.PHP_EOL,FILE_APPEND);
        $data['flushtime'] = date('Y-m-d H:i:s');
        echo json_encode($data);
        exit;
    }
}
