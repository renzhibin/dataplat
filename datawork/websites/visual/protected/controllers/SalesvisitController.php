<?php

/**
 * Class SalesvisitController
 * @author gide
 * @date 2016-08-31
 */

class SalesvisitController extends Controller
{

    private $request_uri = 'http://api.data.lsh123.com/market/Salesvisitlist';

    static $zone_arr = array('1000'=>'北京', '1001'=>'天津', '1002'=>'杭州');
    static $act_type = array('参加套餐活动','参加特价活动','参加满减活动','销售拜访','参加秒杀活动');

    public function actionIndex() {
        $start = date('Y-m-d',strtotime('-30 day'));
        $end = date('Y-m-d',strtotime('-1 day'));
        $time_from = strtotime($start);
        $time_to = strtotime($end);
        while($time_from <= $time_to) {
            $date = date("'Y-m-d'", $time_from);
            $time_from += 24*60*60;
            $dates[] = str_replace("'",'',$date);
        }

        //$date_list = array_reverse($dates);
        $date_list = $dates;

        $data = $this->Realtime->fetchManagerAndSaler();
        $saler_list = array();
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

        $tplArr['dt_list'] = json_encode($date_list);
        $tplArr['saler_list'] = json_encode($saler_list);
        $tplArr['zone_arr'] = json_encode(self::$zone_arr);
        $this->render('salesvisittpl/index.tpl',$tplArr);
    }

    public function actionFetchSaledata() {

        $params = array();

        $params['index'] = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $params['offset'] = isset($_REQUEST['rows']) ? $_REQUEST['rows'] : 10;

        if ( !empty($_REQUEST['sales_name']) ) {
            $params['sales_name'] = $_REQUEST['sales_name'];
        }

        if ( !empty($_REQUEST['leader_name']) ) {
            $params['leader_name'] = $_REQUEST['leader_name'];
        }

        if ( !empty($_REQUEST['market_account']) ) {
            $params['market_account'] = $_REQUEST['market_account'];
        }

        if ( !empty($_REQUEST['date_sort']) ) {
            $params['date_sort'] = $_REQUEST['date_sort'];
        }

        $params['zone_id'] = $_REQUEST['zone_id'] ? $_REQUEST['zone_id'] : 1000;
        $params['total_num_sort'] = $_REQUEST['total_num_sort'] ? $_REQUEST['total_num_sort'] : 'desc';

        $data = $this->Realtime->fetchApidata($this->request_uri, $params);

        $conf['zone_type'] = self::$zone_arr;
        $conf['act_type'] = self::$act_type;

        $tmp_data = array();
        if (!empty($data['list'])) {
            foreach ($data['list'] as $key=>&$value) {
                foreach ($value['date_list'] as $jj=>&$val) {
                    $val['date'] = $jj;
                }
                krsort($value['date_list']);
                $value['date_list'] = array_values($value['date_list']);
                array_push($tmp_data,$data['list'][$key]);
            }
        }

        $tplArr['total'] = $data['total'];
        $tplArr['resultlist'] = $tmp_data;
        $tplArr['conf'] = $conf;
        $result = json_encode($tplArr);
        echo $result;
        exit;
    }

    public function actionLoadSaledata() {
        ini_set ('memory_limit', '-1');
        $params = array();
        $xls_title=array();
        $params['index'] = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $params['offset'] = isset($_REQUEST['rows']) ? $_REQUEST['rows'] : 20000;
        $params['zone_id'] = $_REQUEST['zone_id'] ? $_REQUEST['zone_id'] : 1000;

        $conf['zone_type'] = self::$zone_arr;
        $conf['act_type'] = self::$act_type;

        array_push($xls_title,$conf['zone_type'][$params['zone_id']]);

        if ( !empty($_REQUEST['leader_name']) ) {
            $params['leader_name'] = $_REQUEST['leader_name'];
            array_push($xls_title,$_REQUEST['leader_name']);
        }

        if ( !empty($_REQUEST['sales_name']) ) {
            $params['sales_name'] = $_REQUEST['sales_name'];
            array_push($xls_title,$_REQUEST['sales_name']);
        }

        if ( !empty($_REQUEST['market_account']) ) {
            $params['market_account'] = $_REQUEST['market_account'];
            array_push($xls_title,$_REQUEST['leader_name']);
        }

        if ( !empty($_REQUEST['date_sort']) ) {
            $params['date_sort'] = $_REQUEST['date_sort'];
        }

        $params['total_num_sort'] = $_REQUEST['total_num_sort'] ? $_REQUEST['total_num_sort'] : 'desc';
        $data = $this->Realtime->fetchApidata($this->request_uri, $params);

        $tmp_data = array();

        if (!empty($data['list'])) {
            foreach ($data['list'] as $key=>&$value) {
                foreach ($value['date_list'] as $jj=>&$val) {
                    $val['date'] = $jj;
                }
                krsort($value['date_list']);

                $value['date_list'] = array_values($value['date_list']);
                array_push($tmp_data,$data['list'][$key]);
            }
        }

        /*$tmp_data[0]['date_list'][0]['is_maizeng'] = 1;
        $tmp_data[0]['date_list'][0]['is_visit'] = 1;
        $tmp_data[0]['date_list'][0]['is_manjian'] = 0;
        $tmp_data[0]['date_list'][0]['is_taocan'] = 0;*/
        $export_title=array();
        $export_title['market_name']='超市名称';
        $export_title['market_account']='帐号';
        $export_title['sales_name']='销售姓名';
        $export_title['leader_name']='所属主管';
        $export_title['total_order_nums']='总订单量';
        $export_title['regier_time']='注册时间';

        if(!empty($tmp_data)){
            $reverse_data=array_reverse($tmp_data[0]['date_list']);
            foreach($reverse_data as $key=>$value){
                $export_title[$value['date']]=$value['date'];
            }
        }
        array_push($xls_title,'销售拜访分析表');
        $this->Realtime->exportXls(array_values($export_title),array_keys($export_title),$tmp_data,implode('_',$xls_title).'.xls');
        exit;
    }

    //product show
    public function actionGoodshow() {
        $start = date('Y-m-d',strtotime('-14 day'));
        $end = date('Y-m-d',strtotime('-1 day'));
        $time_from = strtotime($start);
        $time_to = strtotime($end);
        while($time_from <= $time_to) {
            $date = date("'Y-m-d'", $time_from);
            $time_from += 24*60*60;
            $dates[] = str_replace("'",'',$date);
        }

        $date_list = array_reverse($dates);
        $date_list = $dates;
        $tplArr['dt_list'] = json_encode($date_list);
        $tplArr['zone_arr'] = json_encode(self::$zone_arr);
        $this->render('salesvisittpl/goods_show.tpl',$tplArr);
    }

    public function actionGoodsdata() {
        $request_url = "http://api.data.lsh123.com/market/Goodsshow";
        $params = array();
        if ( !empty($_REQUEST['zone_id']) ) {
            $params['zone_id'] = $_REQUEST['zone_id'];
        }

        if ( !empty($_REQUEST['item_id']) ) {
            $params['item_id'] = $_REQUEST['item_id'];
        }

        $params['index'] = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $params['offset'] = isset($_REQUEST['rows']) ? $_REQUEST['rows'] : 10;
        $data = $this->Realtime->fetchApidata($request_url, $params);
        $result = json_encode($data);
        echo $result;
        exit;
    }

    public function actionDowngoodsdata(){
        ini_set ('memory_limit', '-1');
        require "PHPExcel/PHPExcel.php";
        $request_url = "http://api.data.lsh123.com/market/Goodsshow";
        $params = array();
        if ( !empty($_REQUEST['zone_id']) ) {
            $params['zone_id'] = $_REQUEST['zone_id'];
        }

        if ( !empty($_REQUEST['item_id']) ) {
            $params['item_id'] = $_REQUEST['item_id'];
        }
        $data = $this->Realtime->fetchApidata($request_url, $params);
        if ($data['total'] > 0) {
            $params['index'] = 1;
            $params['offset'] = $data['total'];
            $res = $this->Realtime->fetchApidata($request_url, $params);
            $objPHPExcel = new PHPExcel();
            $objSheet = $objPHPExcel->getActiveSheet();
            $objSheet->setTitle("SalegoodsList");
            $date_arr = array_keys($res['list'][0]);
            $date_arr = array_slice($date_arr,18);
            $date_list = array_reverse($date_arr);
            $objSheet->setCellValue("A1", "日期")
                ->setCellValue("B1", "地域")
                ->setCellValue("C1", "商品名称")
                ->setCellValue("D1", "一级品类")
                ->setCellValue("E1", "二级品类")
                ->setCellValue("F1", "品牌")
                ->setCellValue("G1", "商品码")
                ->setCellValue("H1", "物美码");
            $mm = 72;
            foreach($date_list as $val) {
                $tt = chr($mm+1);
                $objSheet->setCellValue($tt."1",$val);
                $mm++;
            }

            $objSheet->setCellValue("W1", "14天非售罄日销量")
                ->setCellValue("X1", "日均销量*7")
                ->setCellValue("Y1", "库存量")
                ->setCellValue("Z1", "售卖规则")
                ->setCellValue("AA1", "创建时间")
                ->setCellValue("AB1", "状态")
                ->setCellValue("AC1", "等级")
                ->setCellValue("AD1", "北京南皋仓")
                ->setCellValue("AE1", "北京寄售仓")
                ->setCellValue("AF1", "北京冻品仓");

            $objSheet->getStyle()->getFont()->setSize('14');
            $objSheet->getDefaultColumnDimension()->setWidth(14);
            $objSheet->getDefaultRowDimension()->setRowHeight(20);
            $num = 2;
            foreach ($res['list'] as $value) {
                $objSheet->setCellValue("A".$num,$value['cdate'])
                    ->setCellValue("B".$num,$value['zone_id'])
                    ->setCellValue("C".$num,$value['sku_name'])
                    ->setCellValue("D".$num,$value['first_cat_name'])
                    ->setCellValue("E".$num,$value['second_cat_name'])
                    ->setCellValue("F".$num,$value['brand'])
                    ->setCellValue("G".$num,$value['item_id'])
                    ->setCellValueExplicit("H".$num,$value['w_code'])
                    ->setCellValueExplicit("I".$num,$value[$date_list[0]])
                    ->setCellValueExplicit("J".$num,$value[$date_list[1]])
                    ->setCellValueExplicit("K".$num,$value[$date_list[2]])
                    ->setCellValueExplicit("L".$num,$value[$date_list[3]])
                    ->setCellValueExplicit("M".$num,$value[$date_list[4]])
                    ->setCellValueExplicit("N".$num,$value[$date_list[5]])
                    ->setCellValueExplicit("O".$num,$value[$date_list[6]])
                    ->setCellValueExplicit("P".$num,$value[$date_list[7]])
                    ->setCellValueExplicit("Q".$num,$value[$date_list[8]])
                    ->setCellValueExplicit("R".$num,$value[$date_list[9]])
                    ->setCellValueExplicit("S".$num,$value[$date_list[10]])
                    ->setCellValueExplicit("T".$num,$value[$date_list[11]])
                    ->setCellValueExplicit("U".$num,$value[$date_list[12]])
                    ->setCellValueExplicit("V".$num,$value[$date_list[13]])
                    ->setCellValueExplicit("W".$num,$value['qty_x_14'])
                    ->setCellValueExplicit("X".$num,$value['avg_sale'])
                    ->setCellValueExplicit("Y".$num,$value['inventory_num'])
                    ->setCellValue("Z".$num,$value['rule_name'])
                    ->setCellValue("AA".$num,$value['created_at'])
                    ->setCellValue("AB".$num,$value['status'])
                    ->setCellValue("AC".$num,$value['sku_level'])
                    ->setCellValueExplicit("AD".$num,$value['DC10'])
                    ->setCellValueExplicit("AE".$num,$value['DC31'])
                    ->setCellValueExplicit("AF".$num,$value['DC41']);
                $num++;
            }
            //导出Excel文件
            $day = date('Ymd', time());
            $exportName = 'Goodslist_' . $day . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header("Content-Disposition: attachment; filename=\"$exportName\"");
            header('Cache-Control: max-age=0');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;
        }
    }
}
