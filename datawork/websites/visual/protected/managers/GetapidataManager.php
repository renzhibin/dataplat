<?php
/**
 * Created by PhpStorm.
 * User: gide
 * Date: 16/7/15
 * Time: 16:56
 */

class GetapidataManager extends Manager {

    function __construct() {

        //通过接口调用
        $this->token = 'aHR0cDovL2FwaS5kYXRhLmxzaDEyMy5jb20v';
    }

    function fetchHeatmapResult ( $request_uri ) {

        $data = array();

        if ( !empty( $request_uri ) ) {
            $timestamp = time();
            $tmpArr = array($this->token, $timestamp);
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode( $tmpArr );
            $tmpStr = sha1( $tmpStr );
            $params['signature'] = $tmpStr;
            $params['timestamp'] = $timestamp;
            $strparams = http_build_query($params);
            $result = Yii::app()->curl->get($request_uri . "?" . $strparams);
            if ($result['http_code'] == 200) {
                if ( strlen($result['body']) > 0 ) {
                    $tmp_data = json_decode($result['body'],true);
                    $data = $tmp_data['data'];
                }
            }
        }

        return $data;
    }
}