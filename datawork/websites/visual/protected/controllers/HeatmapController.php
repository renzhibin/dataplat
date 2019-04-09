<?php
/**
 * Created by PhpStorm.
 * User: gide
 * Date: 16/7/15
 * Time: 16:53
 * desc realtime order
 */

class HeatmapController extends Controller
{

    private $request_uri = 'http://api.data.lsh123.com/market/Heatmap';

    function __construct()
    {
        $this->objGPS = new GPSManager();
        $this->objHeatMap=new HeatMapManager();
    }

    public function actionIndex()
    {
        $this->redirect('/visual/index');
    }

    public function actionShowmap() {

        $data = $this->Realtime->fetchManagerAndSaler();
        if ( !empty($data) ) {
            $tmp_fuid = array();
            $kk = 0;
            foreach ($data as $value) {
                if ($value['level'] == 6) {
                    if (!in_array($value['uid'],$tmp_fuid)) {
                        $tmp_fuid[$kk]['uid'] = $value['uid'];
                        $tmp_fuid[$kk]['sales_name'] = $value['sales_name'];
                        $tmp_fuid[$kk]['zone_id'] = $value['zone_id'];
                        $tmp_fuid[$kk]['sales_list'] = array();
                        $kk++;
                    }
                }
            }

            foreach ($tmp_fuid as $key=>&$val) {
                $tmp_uid = array();
                $ii = 0;
                foreach ($data as $item) {
                    if ($val['uid'] == $item['f_uid']) {
                        $tmp_uid[$ii]['uid'] = $item['uid'];
                        $tmp_uid[$ii]['sales_name'] = $item['sales_name'];
                        $tmp_uid[$ii]['zone_id'] = $item['zone_id'];
                        $val['sales_list'] = $tmp_uid;
                        $ii++;
                    }
                }
            }
        }

        $wholesalerZone = $this->objGPS->getWholesalerZone();
        $tplArr['salesList'] = !empty($tmp_fuid) ? $tmp_fuid : array();
        $tplArr['wholesalerZone'] = $wholesalerZone;
        $this->render('heatmaptpl/showmap.tpl', $tplArr);
    }

    public function actionFetchMapdata() {
        //memory overflow
        ini_set ('memory_limit', '-1');

        $params = array();

        if ( isset($_REQUEST['index']) ) {
            $params['index'] = $_REQUEST['index'];
        }

        if ( isset($_REQUEST['offset']) ) {
            $params['offset'] = $_REQUEST['offset'];
        }

        if ( isset($_REQUEST['f_uid']) ) {
            $params['f_uid'] = $_REQUEST['f_uid'];
        }

        if ( isset($_REQUEST['uid']) ) {
            $params['uid'] = $_REQUEST['uid'];
        }

        if ( isset($_REQUEST['zone_id']) ) {
            $params['zone_id'] = $_REQUEST['zone_id'];
        }

        if ( !empty($_REQUEST['trans_limit']) ) {
            $params['trans_limit'] = $_REQUEST['trans_limit'];
        }


        $data = $this->Realtime->fetchApidata( $this->request_uri, $params );
        $tplArr['resultList'] = $data;
        $result = json_encode($tplArr);
        echo $result;
        exit;
    }

    public function actionFetchClosedMarket() {
        $params = array();

        if ( isset($_REQUEST['f_uid']) ) {
            $params['f_uid'] = $_REQUEST['f_uid'];
        }

        if ( isset($_REQUEST['uid']) ) {
            $params['uid'] = $_REQUEST['uid'];
        }

        if ( isset($_REQUEST['zone_id']) ) {
            $params['zone_id'] = $_REQUEST['zone_id'];
        }

        if ( isset($_REQUEST['address_id']) ) {
            $params['address_id'] = $_REQUEST['address_id'];
        }

        $result_url = 'http://api.data.lsh123.com/market/Closedmarket';
        $data = $this->Realtime->fetchApidata( $result_url, $params );
        $tplArr['resultList'] = !empty($data['data']) ? $data['data'] : array();
        $result = json_encode($tplArr);
        echo $result;
        exit;
    }

    public function actionCusterForce(){

        $this->render('heatmaptpl/custerforce.tpl');
    }


    public function actionSaleZoneCoords(){

        $region='all';
        $params=array();
        if(isset($_REQUEST['region'])){
            $params['region']=$_REQUEST['region'];
        }
        $data=$this->objGPS->getSaleZoneCoords($params);
        $result=array();
        foreach($data as $sz){
            $tmp=array();
            $tmp['sales_name']=$sz['sale_name'];
            $tmp['uid']=$sz['uid'];
            $tmp['zone_id']=$sz['zone_id'];
            $tmp['center']=json_decode($sz['center_coord']);
            $tmp['position']=json_decode($sz['position']);
            $tmp['style']=json_decode($sz['style']);
            array_push($result,$tmp);

        }

        echo $this->jsonOutPut(0,'success',$result);
        exit;

    }

    //高德超市分布与优供超市分布对比
    public function actionMarketLayout(){

        $this->render('heatmaptpl/marketlayout.tpl');
    }

    public function actionFetchGDMarket(){
        ini_set ('memory_limit', '-1');
        $market_type='all';
        $region='all';
        $params=array(
            "market_type"=>"all"
        );
        if ( isset($_REQUEST['market_type']) ) {
            $params['market_type'] = $_REQUEST['market_type'];
        }
        if(isset($_REQUEST['region'])){
            $params['region']=$_REQUEST['region'];
            $region=$_REQUEST['region'];
        }
        $data=$this->objHeatMap->getGDMarket($params);

        $result=array();
        foreach($data as $market){
            $tmp=array();
            $position=array();
            $position=array();
            $position['lng']=$market['longitude'];
            $position['lat']=$market['latitude'];
            $tmp['market_name']=$market['shop_name'];
            $tmp['address']=$market['address'];
            $tmp['position']=$position;
            $tmp['contact_phone']=$market['shop_tel'];
            $tmp['zone_id']='1000';
            if($region!='all'){
                $tmp['zone_id']=$region;
            }

            array_push($result,$tmp);

        }
        echo $this->jsonOutPut(0,'success',$result);
        exit;

    }
    public function actionFetchBaiduMarket(){
        ini_set ('memory_limit', '-1');
        $region='all';
        $params=array(
            "region"=>"all"
        );

        if(isset($_REQUEST['region'])){
            $params['region']=$_REQUEST['region'];
            $region=$_REQUEST['region'];
        }
        $data=$this->objHeatMap->getBaiduMarket($params);

        $result=array();
        foreach($data as $market){
            $tmp=array();
            $position=array();
            $position=array();
            $position['lng']=$market['longitude'];
            $position['lat']=$market['latitude'];
            $tmp['market_name']=$market['shop_name'];
            $tmp['address']=$market['address'];
            $tmp['position']=$position;
            $tmp['contact_phone']=$market['shop_tel'];
            $tmp['zone_id']='1000';
            if($region!='all'){
                $tmp['zone_id']=$region;
            }

            array_push($result,$tmp);

        }
        echo $this->jsonOutPut(0,'success',$result);
        exit;

    }



    public function actionGYzoneRate(){
        ini_set ('memory_limit', '-1');
        $params=array(
            "market_type"=>"all"
        );
        if(isset($_REQUEST['region'])){
            $params['region']=$_REQUEST['region'];
        }
        $data=$this->objHeatMap->getGYzoneRate($params);
        $result=array();
        $zone_low=array();
        $zone_high=array();
        //控制前端线框区域颜色
        //颜色加深分别 高德／优供>3 高德-优供>30

        $color_level_high=array(
            "strokeColor"=>"#F33",
            "fillColor"=>"#ff0000");
        $color_level_low=array(
            "strokeColor"=>"#ff8484",
            "fillColor"=>"#ff6464"
        );

        $tip_msg=array(
            "红色"=>array("高德超市数比优供超市数大于3","高德超市数减优供超市数大于30"),
            "淡红"=>array("高德超市数大于5且高德超市数减去优供超市数大于15")
        );


        $other=array(
            "color_level_low"=>$color_level_low,
            "color_level_high"=>$color_level_high
        );


        foreach($data as $zone){
            $tmp=array();
            $tmp['gaode']=json_decode($zone['gaode']);
            $tmp['yg']=json_decode($zone['yg']);
            $tmp['rate']=$zone['rate'];
            $tmp['gaode_num']=$zone['gaode_num'];
            $tmp['yg_num']=$zone['yg_num'];
            $tmp['zone_coords']=json_decode($zone['zone_coords']);
            $tmp['style_type']='color_level_low';
            if(($zone['rate']>=3 && $zone['gaode_num']>=30) || ($zone['gaode_num']-$zone['yg_num'])>=30) {
                $tmp['style_type'] = 'color_level_high';
                array_push($zone_high,$tmp);
            }else{
                array_push($zone_low,$tmp);
            }
//            array_push($result,$tmp);
            $result['zone_low']=$zone_low;
            $result['zone_high']=$zone_high;

        }
        echo json_encode(array('status' =>(int)0, 'msg' => 'success', 'data' => $result,'other'=>$other));
//        echo $this->jsonOutPut(0,'success',$result);
        exit;

    }

    /**
     * 销售当日拜访超市可视化
     */
    public function actionSalesvisit() {
        //获取所有区域 主管 销售信息
        $saler_list = array();
        $data = $this->Realtime->fetchManagerAndSaler();
        if (!empty($data)) {
            foreach ($data as $key=>$value) {
                $zone_id = $value['zone_id'];
                $slist[$zone_id] = array();
                if ($value['level'] == 6) {
                    $slist[$zone_id][$key]['uid'] = $value['uid'];
                    $slist[$zone_id][$key]['sales_name'] = $value['sales_name'];
                    $saler_list[$zone_id][] = $slist[$zone_id][$key];
                }
            }

            foreach ($saler_list as &$item) {
                foreach ($item as &$el) {
                    $cc = 0;
                    foreach ($data as $vl) {
                        if ($el['uid'] == $vl['f_uid']) {
                            $uid = $vl['f_uid'];
                            $el[$uid][$cc]['uid'] = $vl['uid'];
                            $el[$uid][$cc]['sales_name'] = $vl['sales_name'];
                            $cc++;
                        }
                    }
                }
            }
        }

        $tplArr['saler_list'] = json_encode($saler_list);
        $this->render('heatmaptpl/salesvisit.tpl', $tplArr);
    }

    public function actionSalesvisitData() {
        $params = array();
        $params['cur_date'] = isset($_REQUEST['date']) ? $_REQUEST['date'] : date('Y-m-d');
        $params['uid'] = isset($_REQUEST['uid']) ? intval($_REQUEST['uid']) : 0;
        $visit_data = $this->HeatMap->getVisitData($params);
        $market_data = $this->HeatMap->getMarketData($params['uid']);
        $data = array();
        $data['date'] = $params['cur_date'];

        if (!empty($visit_data) && !empty($market_data)) {
            foreach ($visit_data as &$val) {
                $vis_pos = json_decode($val['visit_position'],true);
                $val['visit_pos'] = json_encode($vis_pos['position']);
                unset($val['visit_position']);
                $vis_pos = json_decode($val['market_position'],true);
                $val['market_pos'] = json_encode($vis_pos['position']);
                unset($val['market_position']);
            }

            foreach ($market_data as &$value) {
                $rel_pos = json_decode($value['real_position'],true);
                $value['real_pos'] = json_encode($rel_pos['position']);
                unset($value['real_position']);
            }
        }
        $data['visit_list'] = $visit_data;
        $data['market_list'] = $market_data;
        echo $this->jsonOutPut(0,'success',$data);
        exit;
    }
}
