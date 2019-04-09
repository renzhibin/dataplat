<?php
/*
if (parseResult($bytes)) {
    exit(0);
} else {
    exit(1);
}
 */

/*
 * 发送图片数据，用于图片上传接口
 * @param : $url 请求的url，$img_content用于发送的图片内容，$kind 用于发送的图片的类型（pic,ap,glogo,tmp,img）
 * @return : 返回上传的图片相应数据
 * */
function sendHttpPostImageRequest($url, $content, $kind) {
    $logStr = sprintf('act=sendHttpImageRequest,url=%s,$kind=%s', $url,$kind);
    $name = md5($content);
    $boundary = uniqid('------------------');
    $MPboundary = '--'.$boundary;
    $endMPboundary = $MPboundary. '--';

    $multipartbody = $MPboundary . "\r\n";
    $multipartbody .= 'Content-Disposition: form-data; filename='.$name. "\r\n";
    $multipartbody .= 'Content-Type: image/jpg'. "\r\n\r\n";
    $multipartbody .= $content."\r\n";
    $key = "kind";
    $value = $kind;
    $multipartbody .= $MPboundary . "\r\n";
    $multipartbody .= 'content-disposition: form-data;name="'.$key."\r\n\r\n";
    $multipartbody .= $value . "\r\n";

    $multipartbody .= "\r\n". $endMPboundary;
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt( $ch , CURLOPT_POST, 1 );
    curl_setopt( $ch , CURLOPT_POSTFIELDS , $multipartbody );

    $header_array = array("Content-Type: multipart/form-data; boundary=$boundary" , "Expect: ");

    curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array );
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER , true);
    curl_setopt($ch, CURLINFO_HEADER_OUT , true);

    $bytes = curl_exec($ch);
    $headersize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($curl_errno > 0) {
        printf('%s err=curl error:curl_errno=%d,curl_error=%s',$logStr, $curl_errno,$curl_error);
        $bytes = FALSE;
    }
    if ($code != 200) {
        printf('%s err=curl error:curl return code = %s', $logStr,$code);
        $bytes = FALSE;
    }

    //var_dump($bytes);
    $bytes = substr($bytes ,$headersize);
    return $bytes;
}
function parseResult($bytes) {
    $result_arr = json_decode($bytes, true);
    $code = $result_arr['ret'];
    if (0 == $code) {
        return true;
    } else {
        return false;
    }
}