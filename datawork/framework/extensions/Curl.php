<?php
/**
 * CURL wrapper class
 * @author: KevinChen
 */
class Curl{
    var $callback = false;
    //var $user_agent = "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; Maxthon 2.0)";
    //var $user_agent = "MeilishuoSpider+(+http://www.meilishuo.com/spider.html))"; 
    var $user_agent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.94 Safari/537.36";
    // var $user_agent = "Sogou web spider/4.0(+http://www.sogou.com/docs/help/webmasters.htm#07)";
    var $cookie = false;
    var $proxy = "";
    var $timeout = 300;
    var $header = array('Meilishuo:uid:0;ip:172.16.0.40');

    function setCallback($func_name) {
        $this->callback = $func_name;
    }
    function setUserAgent($agent) {
        $this->user_agent = $agent;
    }
    function setCookie($cookie){
        $this->cookie = $cookie;
    }
    function setProxy($proxy) {
        $this->proxy = $proxy;
    }
    function setTimeout($num){
        $this->timeout = $num;
        return $this;
    }
    function setHeader($header){
        $this->header = $header;
        return $this;
    }
    
    public function init()
    {
        
    }

    
    function doHeadInfo($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FRESH_CONNECT,1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, 1); // important! set this option is using the "header method"
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT,$this->user_agent);
        //curl_setopt($ch, CURLOPT_REFERER,$url);
        curl_setopt($ch, CURLOPT_ENCODING,"gzip");
        curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //add for proxy by liangbo 2012.8.2
        if($this->proxy != "") {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }

        $data = curl_exec($ch);
        
        if(empty($data)){
            // $err = curl_error($ch);
            $err = false;
            curl_close($ch);
            return $err;
        }else{
            $info = curl_getinfo($ch);
            curl_close($ch);
            return $info;
        }
    }
    
    public function getDebugInfo(){
        return $this->debugInfo;
    }

    function doRequest($method, $url, $vars, $referer = '',$out_time=30) {
        $out_time=intval($out_time);
        //var_dump($out_time);die();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FRESH_CONNECT,1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT,$this->user_agent);
        curl_setopt($ch, CURLOPT_ENCODING,"gzip");
        curl_setopt($ch, CURLOPT_TIMEOUT,$out_time);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        
        if ($this->cookie != false){
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookie);
        }
        
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
        }
        if (!empty($referer)) {
            curl_setopt($ch, CURLOPT_REFERER , $referer);
        }

        if($this->proxy != "") {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }   

        if (!empty($this->header)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        }
        
        $data = curl_exec($ch);
        
        if ($data) {
            // 分析curl返回的结果
            $result = array();
            $header_size = curl_getinfo($ch,CURLINFO_HEADER_SIZE);
            $result['header'] = $this->pass_header( substr($data, 0, $header_size) );
            $result['body'] = substr( $data , $header_size );
            $result['http_code'] = curl_getinfo($ch , CURLINFO_HTTP_CODE);
            $result['last_url'] = curl_getinfo($ch , CURLINFO_EFFECTIVE_URL);
            $result['last_sent']=curl_getinfo($ch ,CURLINFO_HEADER_OUT );       
            $data = $result;
            $this->debugInfo = curl_getinfo($ch);
            if ($this->callback)
            {
                $callback = $this->callback;
                $this->callback = false;
                return call_user_func($callback, $data);
            } else {
                curl_close($ch);
                return $data;
            }
        } else {
            curl_close($ch);
            return false;
        }
    }
    public function pass_header($header)
    {
        $result=array();
        $varHader=explode("\r\n", $header);
        if(count($varHader)>0)
        {
            for($i=0;$i<count($varHader);$i++)
            {
                $varresult=explode(":",$varHader[$i]);
                if(is_array($varresult) && isset($varresult[1]))
                $result[$varresult[0]]=$varresult[1];
            }
        }
        return $result;
    }   
    function head($url){
        return $this->doHeadInfo($url);
    }
    
    function get($url, $referer = '',$out_time=30) {
        $checkPos = strpos ( $url , "#");
        if ( $checkPos !== false ) {
            $url = substr ( $url , 0 , $checkPos );
        }
        return $this->doRequest('GET', $url, 'NULL', $referer,$out_time);
    }

    function post($url, $vars, $referer='',$out_time=30) {
        return $this->doRequest('POST', $url, $vars, $referer,$out_time);
    }
}
