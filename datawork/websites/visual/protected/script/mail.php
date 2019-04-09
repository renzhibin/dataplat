<?php
/**
 * Created by PhpStorm.
 * User: renzhibin
 * Date: 15-3-12
 * Time: 下午8:12
 */

function sendMail($mail, $body, $subject = '群圈数据', $from = 'di-inf@meilishuo.com', $header = '', $flag = true) {


    if ($header === '') {
        $header = 'MIME-Version: 1.0' . "\r\n";

        $header .= "From: $from\r\n" . "Content-type: text/html; charset=utf-8\r\n";

    }
    $strMergeMail='';

    if (strpos($mail, ';')) {
        $mail = explode(';', $mail);
    }
    $subject="=?UTF-8?B?".base64_encode($subject)."?=";
    if (is_array($mail)) {
        if ($flag == true) {
            foreach ($mail as $strMail) {
                if (strpos($strMail, '@') === false) {
                    $strMail = $strMail . '@meilishuo.com';
                }
                $strMergeMail.=$strMail . ',';
            }

            return mail($strMergeMail, $subject, $body, $header);
        } else {
            foreach ($mail as $strMail) {
                if (strpos($strMail, '@') === false) {
                    $strMail = $strMail . '@meilishuo.com';
                }
                mail($strMail, $subject, $body, $header);
            }
            return true;
        }
    } else {
        $strMail = $mail;
        if (strpos($strMail, '@') === false) {
            $strMail = $strMail . '@meilishuo.com';
        }
        return mail($strMail, $subject, $body, $header);
    }
}



function sendSms($name,$msg){
    if(empty($name)){
       //$name='zhibinren;jiejiaohan;xinghuazhang';
        $name='jingyangguo';
    }
    $arrname=explode(";",$name);
    $msg=urlencode($msg);
    foreach($arrname as $name){
        echo $name;
        $url=sprintf('http://smsapi.meilishuo.com/smssys/interface/smsapi.php?smsKey=1407399202710&type=both&name=%s&phone=&smscontent=%s&mailsubject=%s&mailcontent=%s', $name, $msg,$msg,$msg);
        file_get_contents($url);

    }
    return true;


}
function checkEmpty($data){
    if(empty($data) || $data=='不存在'|| $data==0)
        return True;
    return  False;

}

function getdivision($a,$b,$percnet=false,$int_flag=false){
    if(checkEmpty($a) || checkEmpty($b)){
        return 0;
    }else{
        $v=(float)($a)/(float)($b);
        if($int_flag==true){
            return round($v);
        }
        if($percnet==true){
            return sprintf("%.2f", $v*100);
        }
        else{
            return sprintf("%.2f", $v);
        }

    }
}

$inter=8;

$edate=date("Y-m-d",strtotime("-1 day"));
$date=date("Y-m-d",strtotime("-".$inter." day"));
$today=$edate;
$yesterday=date("Y-m-d",strtotime($today)-86400);
$sevendaysago=date("Y-m-d",strtotime($today)-86400*7);;
$cn2enMap=array(
    'chat_circle_user_data_dayly_user'=>'日访问用户',
    'chat_circle_gmv_new_gmv'=>'GMV',
    'chat_circle_gmv_new_goods_amount'=>'总销量',
    21=>'转化率',
    3=>'件单价',
    'chat_circle_gmv_new_buyer_pay'=>'购买人数',

    22=>'复购率',
    'chat_circle_all_onlinegoods_all_goods'=>'在线商品',
    'chat_circle_all_onlinegoods_today_addgoods_godosnum'=>'新上架商品',
    'chat_circle_gmv_new_goods_num'=>'有销量商品',
    23=>'圈动销率',
    'chat_circle_gmv_new_shop_num'=>'有销量圈子数',
    4=>'平均每店GMV',
    5=>'平均每店销量',

    'chat_circle_user_data_daynew_user'=>'新用户',
    'chat_circle_user_data_dayold_user'=>'老用户',
    1=>'新用户占比',
    'chat_circle_circle_message_send_messageuser'=>'发言用户',
    'chat_circle_user_data_all_user'=>'累计用户',

   # 'chat_circle_gmv_new_shop_num'=>'有销量店铺数',



    'all_circle_all_circle_all_circle'=>'总圈子',
    'all_circle_all_circle_today_circle'=>'新增圈子数',
    'all_circle_all_circle_all_circle_shop'=>'店铺圈子数',
    'all_circle_all_circle_today_circle'=>'新增店铺圈子数',
    'chat_circle_active_circle_num_active_circle_num'=>'有活跃圈子数',
    'chat_circle_circle_message_circle_interact_num'=>'有互动圈子数',
    'chat_circle_circle_message_all_message'=>'总消息数',
    'chat_circle_messagecontent_txtandpic_msam'=>'图片消息',
    'chat_circle_messagecontent_txt_msam'=>'文字消息',
    'chat_circle_messagecontent_goods_msam'=>'商品消息',
    24=>'圈子平均发言量',
    25=>'人均发言量',







    'chat_circle_circle_message_send_messagecircle'=>'有发言圈子数',
    'chat_circle_all_newandallcust_today_firstbuyer'=>'当日新增购买人数',


    // 2=>'提袋率',



);
$sort=array_values($cn2enMap);

$sort_num=count($sort);
unset($sort[$sort_num-1]);
unset($sort[$sort_num-2]);
$add_column=array('新用户占比'=>array('新用户','/','日访问用户'),'转化率'=>array('购买人数','/','日访问用户'),'件单价'=>array('GMV','/','总销量'),'平均每店GMV'=>array('GMV','/','有销量圈子数'),'平均每店销量'=>array('总销量','/','有销量圈子数'),

    '圈动销率'=>array('有销量圈子数','/','店铺圈子数'),
    '圈子平均发言量'=>array('总消息数','/','有发言圈子数'),
    '人均发言量'=>array('总消息数','/','发言用户'),
     );
$per_column=array('新用户占比','转化率','复购率','圈动销率');

//$sort=array('日访问用户','GMV','新用户','老用户','发言用户','累计用户','新用户占比','有销量店铺数','购买人数','提袋率','件单价','平均每店GMV','平均每店销量','总销量');

function checkReturnData($url){
    global $inter;
    try{
        $res = file_get_contents($url);
        $res=json_decode($res,true);
        if($res['status']!=0){
            sendSms('','群圈数据错误');
            return false;
        }
        if(empty($res['data']) ||  count($res['data'])!=$inter){
            sendSms('','群圈数据缺少某天数据,当前数据天数为'.count($res['data']));
            return  false;
        }
    }catch(Exception $e ){
        print $e->getMessage();
        return false;
    }
    return $res['data'];
}
function getMean($data,$k,$start,$inter){
    //$date_res[$sevendaysago][$k];
    //getMean($date_res,$k,$today,7)
    $num=0;
    $start=strtotime($start);
    $end=$start-$inter*86400;
    $int_flag=true;
    for($i=$start;$i>=$end;$i=$i-86400){
        if(!ctype_digit($data[date('Y-m-d',$i)][$k])){
            $int_flag=false;
        }
        $num+=$data[date('Y-m-d',$i)][$k];
    }
    return getdivision($num,$inter+1,'',$int_flag);
}
function getColor($num){
    if($num>=10){
        return 'red';
    }
    if($num<=-10){
        return 'green';
    }
    return 'black';
}
function getReturnData($url){
    $res=checkReturnData($url);
    if($res==false){
        return false;
    }
    global $cn2enMap;
    global $add_column;
    global $per_column;
    global $today;
    global $yesterday;
    global $sevendaysago;

    foreach($res as $v){
        $tmpv=array();
        foreach($v as $k=>$subv){
            if(isset($cn2enMap[$k])) {
                $k = $cn2enMap[$k];
                $tmpv[$k]=$subv;
            }

        }
        $date_res[$v['date']]=$tmpv;
    }
    foreach($date_res as $k=>$v){
        foreach($add_column as $subk=>$subv){
            if(in_array($subk,$per_column)) {
                $date_res[$k][$subk]=getdivision($v[$subv[0]],$v[$subv[2]],true);
            }
            else{
                $date_res[$k][$subk]=getdivision($v[$subv[0]],$v[$subv[2]]);

            }
        }
        $tmp=getdivision($v['当日新增购买人数'],$v['购买人数']);
        if($tmp==false){
            $date_res[$k]['复购率']=false;

        }else{
            $date_res[$k]['复购率']=(1-$tmp)*100;
        }



    }
    $final_res=array();
    foreach($date_res[$today] as $k=>$v){

        $final_res[$k][$today]=$v;
        $final_res[$k][$yesterday]=$date_res[$yesterday][$k];
        $final_res[$k][$sevendaysago]=$date_res[$sevendaysago][$k];

        $final_res[$k]['t2y']=getdivision(($final_res[$k][$today]-$final_res[$k][$yesterday]),$final_res[$k][$yesterday],true);
        $final_res[$k]['t2s']=getdivision(($final_res[$k][$today]-$final_res[$k][$sevendaysago]),$final_res[$k][$sevendaysago],true);
        $final_res[$k]['7m']=getMean($date_res,$k,$today,6);


        if(in_array($k,$per_column))
        {
            $final_res[$k][$today]=$final_res[$k][$today].'%';
            $final_res[$k][$yesterday]=$date_res[$yesterday][$k].'%';
            $final_res[$k]['7m']=$final_res[$k]['7m'].'%';
            $final_res[$k][$sevendaysago]=$date_res[$sevendaysago][$k].'%';

        }
    }
    /*
    if($flag==false){
        if(empty($subv) || $subv=='不存在'){
            $flag=false;
            break 2;
        }
        $res=sendSms($receiver,'群圈数据部分为空');
        $flag=false;
    }*/
    return $final_res;

}






$date_res=array();
$flag=false;
while($flag==false){
    $flag=true;
   // $url='http://172.16.2.232:8181/query_app?project=pr_app_source&group=dim&metric=chat_circle.circle_message.send_messageuser%2Cchat_circle.circle_gmv.buyer_pay%2Cchat_circle.circle_gmv.goods_amount%2Cchat_circle.circle_gmv.gmv%2Cchat_circle.circle_gmv.shop_num%2Cchat_circle.user_data.dayly_user%2Cchat_circle.user_data.daynew_user%2Cchat_circle.user_data.dayold_user%2Cchat_circle.user_data.all_user&filter=&total=1&udc=&offset=10&index=1&appName=data&appToken=7BTUhUuzeOmB&';
    //$url='http://172.16.2.232:8181/query_app?project=pr_app_source&group=dim&metric=chat_circle.circle_message.send_messageuser,chat_circle.user_data.dayly_user,chat_circle.user_data.daynew_user,chat_circle.user_data.dayold_user,chat_circle.user_data.all_user,chat_circle.gmv_new.buyer_pay,chat_circle.gmv_new.goods_amount,chat_circle.gmv_new.gmv,chat_circle.gmv_new.shop_num&appName=data&appToken=7BTUhUuzeOmB&';
    $url='http://172.16.2.232:9876/query_app?project=pr_app_source&group=dim&metric=chat_circle.member.all_user,chat_circle.member.black_user,chat_circle.circle_message.all_message,chat_circle.circle_message.del_message,chat_circle.circle_message.send_messageuser,chat_circle.circle_message.del_messageuser,chat_circle.circle_message.circle_interact_num,chat_circle.circle_message.send_messagecircle,chat_circle.messagecontent.txt_msam,chat_circle.messagecontent.txtandpic_msam,chat_circle.messagecontent.goods_msam,chat_circle.messagecontent.notice_msam,chat_circle.messagecontent.other_msam,chat_circle.messagecontent.all_msam,chat_circle.circle_gmv.buyer_pay,chat_circle.circle_gmv.goods_price,chat_circle.circle_gmv.goods_amount,chat_circle.circle_gmv.gmv,chat_circle.circle_gmv.circle_num,chat_circle.circle_gmv.shop_num,chat_circle.circle_gmv.goods_num,chat_circle.user_data.dayly_user,chat_circle.user_data.daynew_user,chat_circle.user_data.dayold_user,chat_circle.user_data.all_user,chat_circle.all_onlinegoods.all_shop,chat_circle.all_onlinegoods.all_goods,chat_circle.all_onlinegoods.today_addgoods_shopnum,chat_circle.all_onlinegoods.today_addgoods_godosnum,chat_circle.gmv_new.buyer_pay,chat_circle.gmv_new.goods_price,chat_circle.gmv_new.goods_amount,chat_circle.gmv_new.gmv,chat_circle.gmv_new.shop_num,chat_circle.gmv_new.goods_num,chat_circle.active_circle_num.active_circle_num,chat_circle.all_newandallcust.all_buyer,chat_circle.all_newandallcust.today_firstbuyer,all_circle.all_circle.all_circle,all_circle.all_circle.today_circle,all_circle.all_circle.all_circle_shop,all_circle.all_circle.today_circle_shop,all_circle.all_member.all_member,all_circle.all_mess_mecirmes.all_member_mess,all_circle.all_mess_mecirmes.all_circle_mess,all_circle.all_mess_mecirmes.all_message&appName=data&appToken=7BTUhUuzeOmB';
    $url.="&date=$date&edate=$edate";
    echo $url;

    $final_res=getReturnData($url);
    if($final_res==false){
        $flag=false;
    }



    if($flag==true){

        $str='<!DOCTYPE html>
<html lang="en"><style type="text/css">
 	table{ border:  1px solid #ddd}
	table tr td{ padding: 5px}
</style>';
        $str.="<table border='1' cellspacing='0' cellpadding='0'>";
        $str.='<thead><tr>
            <td style="text-align: right">指标项</td>
            <td style="text-align: right">当日值</td>
            <td style="text-align: right">前一日值</td>
            <td style="text-align: right">当日相比前一日变化率</td>
            <td style="text-align: right">上周同日值</td>
            <td style="text-align: right">当日相比上周同日变化率</td>
            <td style="text-align: right">周平均值</td>
            </tr>
       </thead>
       <tbody>';
        foreach($sort as $v){
            if(checkEmpty($final_res[$v][$today]) || checkEmpty($final_res[$v][$yesterday]) ||checkEmpty($final_res[$v][$sevendaysago])) {
                $flag=false;
                sendSms('', $v . '没有生成');
                break;
            }
            $t2ycolor='black';
            $t2scolor='black';
            if($v=='日访问用户' or $v=='GMV'){
                $t2ycolor=getColor($final_res[$v]['t2y']);
                $t2scolor=getColor($final_res[$v]['t2s']);
            }

            $final_res[$v]['t2y']=$final_res[$v]['t2y'].'%';
            $final_res[$v]['t2s']=$final_res[$v]['t2s'].'%';



            $str.='<tr>
                <td style="text-align: right;">'.trim($v).'</td>
                <td style="text-align: right">'.trim($final_res[$v][$today]).'</td>
                <td style="text-align: right">'.trim($final_res[$v][$yesterday]).'</td>
                <td style="text-align: right; color:'.$t2ycolor.'">'.trim($final_res[$v]['t2y']).'</td>
                <td style="text-align: right">'.trim($final_res[$v][$sevendaysago]).'</td>
                 <td style="text-align: right; color:'.$t2scolor.'">'.trim($final_res[$v]['t2s']).'</td>
                  <td style="text-align: right">'.trim($final_res[$v]['7m']).'</td>
            </tr>
            ';
        }
        $str.='</tbody>
      </table>
      </html>';
    }



    if($flag==false){
        //$min=date('i',time());
        //  if($min%10==0){
        sleep(600);
        //}
    }
}


echo $str;
$res=sendMail('circle-co;wenzhuoli;songhuang;jingyangguo;jiejiaohan;xinghuazhang;zhibinren',$str,'群圈数据 '.$edate);
//$res=sendMail('zhibinren;jiejiaohan;xinghuazhang',$str,'群圈数据 '.$edate);
//$res=sendMail('zhibinren',$str,'群圈数据 '.$edate);

exit();

