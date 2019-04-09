<?php
/**
 * Created by PhpStorm.
 * User: gide
 * Date: 16/12/23
 * Time: 18:08
 */
class GetapiController extends Controller {
    private $request_uri = 'http://api.data.lsh123.com/market/';

    public function actionGettotalsalerzone(){
        return $this->actionRequestdata(__FUNCTION__);
    }

    public function actionGetmanagerandsaler(){
        return $this->actionRequestdata(__FUNCTION__);
    }

    private function actionRequestdata($method, $params = array()) {
        $method = substr($method,6);
        ini_set ('memory_limit', '-1');
        $request_url = $this->request_uri.$method;
        $data = $this->Realtime->fetchApidata($request_url,$params);
        echo $this->jsonOutPut(0,'success',$data);
        exit;
    }
}