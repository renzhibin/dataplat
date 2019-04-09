<?php
define('NAME_PATTERN', "/^(\p{Han}|[0-9]|[a-z]|[A-Z]|[_\(\)（）\+])+$/u");

Class ConstManager extends Manager
{

    static function getName()
    {
        $path = HOMEPAHT . '/protected/config/whitename.txt';
        $fp = fopen($path, 'r');
        $nameList = array();
        while (!feof($fp)) {
            $nameList[] = trim(fgets($fp));
        }
        return $nameList;
    }

    static function checkWords($name,$find){
        //检测是否包含 spam关建字
        if(stripos($name,$find) ===false){
            return true;
        }else{
            return false;
        }
    }
    static function  checkName($name, $length = 15)
    {

        if (preg_match(NAME_PATTERN, $name)) {
            if (mb_strlen($name, 'utf8') <= $length)
                return true;
        }

        return false;


    }
    static  function  getStatusMap(){
        // 'WAITING=1,READY=2,RUNNING=3,HIVEEND=4,SUCCESS=5,FAILED=6,WARNING=7'

        return array(1=>'阻塞','2'=>'就绪','3'=>'运行',4=>'hive结束',5=>'成功',6=>'失败',7=>'警告',8=>'超时',9=>'检查',11=>'杀死');
    }

    static function  getConfigName($config)
    {

        if (empty($config)) {
            return array();
        }

        $arr = array();
        foreach ($config as $key => $value) {
            if ($value['name'] != 'date') {
                $arr[] = $value['name'];
            }
        }
        return $arr;


    }
    static  function checkEmpty($data){
        if(empty($data) || $data=='不存在'|| $data==0 || $data=='未生成')
            return True;
        return  False;

    }

    static  function getdivision($a,$b,$percnet=false,$int_flag=false,$avg=false){
        if($a ==0){
            return 0;
        }
        if(self::checkEmpty($a) || self::checkEmpty($b)){
            return '不存在';
        }else{
            $v=(float)($a)/(float)($b);
            //周均值有两种可能 可以为小数 可以为整数 单独判断
            if($int_flag==true){
                return round($v);
            }else if($avg ==true){
                return sprintf("%.2f", $v);
            }
            if($percnet==true){
                return sprintf("%.2f%%", $v*100);
            }
            else{
                return sprintf("%.2f", $v*100);
            }

        }
    }

    static  function getMean($data,$k,$end,$inter){
        //$date_res[$sevendaysago][$k];
        //getMean($date_res,$k,$today,7)
        $num=0;
        $end=strtotime($end);
        $start=$end-($inter-1)*86400;
        $int_flag=true;
        for($i=$end;$i>=$start;$i=$i-86400){
            if(!ctype_digit($data[date('Y-m-d',$i)][$k])){
                $int_flag=false;

            }
            $num+=$data[date('Y-m-d',$i)][$k];
        }
        return self::getdivision($num,$inter,'',$int_flag,true);
    }


    static  function  getUrl($style_id=NULL,$twitter_id=NULL,$shop_id=NULL,$itemid=NULL){
        $href=self::getHref($style_id,$twitter_id,$shop_id);
        $pic=self::getPic($style_id,$twitter_id,$itemid);
        //TODO
        if(empty($href['realdata'])){
            return array('realdata'=>$itemid,'commentdata'=>$pic);
        }


        if($pic==false){
            return $href;
        }
        // var_dump($href.$pic);exit();


        return array('realdata'=>$href['realdata'],'commentdata'=>$href['commentdata'].$pic);

    }

    static  function  getHref($style_id=NULL,$twitter_id=NULL,$shop_id=NULL){
        if(!empty($style_id)){
            $real_data=$style_id;
            $comment_data=$style_id;
            if(is_array($style_id)){
                $real_data=$style_id['realdata'];
                $comment_data=$style_id['commentdata'];

            }
            $href='http://m.meilishuo.com/zulily/detail/?style_id='.$real_data."'";

        }elseif(!empty($twitter_id)){
            $real_data=$twitter_id;
            $comment_data=$twitter_id;
            if(is_array($twitter_id)){
                $real_data=$twitter_id['realdata'];
                $comment_data=$twitter_id['commentdata'];

            }
            $href='http://www.meilishuo.com/share/item/'.$real_data;


        }elseif(!empty($shop_id)){
            $real_data=$shop_id;
            $comment_data=$shop_id;
            if(is_array($shop_id)){
                $real_data=$shop_id['realdata'];
                $comment_data=$shop_id['commentdata'];

            }
            $href='http://www.meilishuo.com/shop/'.$real_data;



        }

        $prefix='<div style="height:auto"><a style="padding-left:5px" target="_blank" href="'.$href;

        $suffix='?from=fakecube">' . $comment_data . '</a>';

        $url=$prefix.$suffix;
        return array('realdata'=>$real_data,'commentdata'=>$url);

    }



    static  function  getPic($style_id=NULL,$twitter_id=NULL,$itemid=NULL){

        if(empty($style_id)&&empty($twitter_id)&&empty($itemid)){
            return false;
        }
        if(!empty($style_id)){
            $sql="select main_img from t_cheetah_style where ";

            $pic_key='main_img';
            if(is_array($style_id)){
                $style_id=$style_id['realdata'];
            }
            $suffix='id="'.$style_id.'"';
            $sql=$sql.$suffix;
            $res= Yii::app()->sdb_cheetah->createCommand($sql)->queryAll();
        }elseif(!empty($twitter_id)){
            $sql="select goods_img from brd_goods_info where ";
            $pic_key='goods_img';

            if(is_array($twitter_id)){
                $twitter_id="'". $twitter_id['realdata']."'";
            }
            $suffix='twitter_id="'.$twitter_id.'"';
            $sql=$sql.$suffix;
            $res= Yii::app()->sdb_brd_goods->createCommand($sql)->queryAll();

        }elseif(!empty($itemid)){
            $sql="select img_url from t_coral_mogujie_items where  ";
            $pic_key='img_url';

            if(is_array($itemid)){
                $itemid="'". $itemid['realdata']."'";
            }
            $suffix='itemid="'.$itemid.'"';
            $sql=$sql.$suffix;
            $res= Yii::app()->sdb_coral_snap->createCommand($sql)->queryAll();
            if($res&&!empty($res['0'][$pic_key])){
                $html='<img style="padding-left:5px; border-left:1px dotted #ccc" src="'.$res[0][$pic_key].'" width="120px" height="160px"></div>';

                return $html;
            }
            return '';
        }

        if($res){
            $html='<img style="padding-left:5px; border-left:1px dotted #ccc" src="http://d06.res.meilishuo.net/'.$res[0][$pic_key].'" width="60px" height="80px"></div>';

            return $html;
        }
        return false;
    }




}
