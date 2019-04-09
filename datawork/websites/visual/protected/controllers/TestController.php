<?php

class TestController extends Controller
{

     public function actionPointer(){
        // $sql = "select * from t_dolphin_stat_easytable_info  where  author !='' ";
        // $db = Yii::app()->sdb_dolphin_stat;
        // $data = $db->createCommand($sql)->queryAll();

        // foreach ($data as $key => $value) {
        //  $body ="";
        //  if( $key  > 160){
        //     $one = $this->report->getWorkReport($value['id']);
        //     $arr = explode("/", $one['url']);
        //     array_shift($arr);
        //     $one['sign']  =  implode("/", $arr);
        //     $url = DEVELOP_API."/outApi/UpdateFunctionItem";
        //     $vars = array(
        //         'source'=>'9aa78c2fd72a888d53a5da5f0c28d37b',
        //         "business"=>'works',
        //         "funname"=>$one['name'],
        //         "description"=>$value['comment'],
        //         "url"=>$one['url'],
        //         "sign"=>$one['sign']
        //     );

        //     $inSql = " select  * from developer_function where url ='".$one['url']."' and business ='works' ";
        //     $eelDb =  Yii::app()->sdb_eel;
        //     $inArr = $eelDb->createCommand($inSql)->queryAll();
        //     if(!empty($inArr)){
        //         echo  $one['name']."?".$one['url']."?".$inArr[0]['funname']."?已经存在功能<br>";
        //     }else{
        //         $curl = yii::app()->curl;
        //         $url  = $url."?".http_build_query($vars);

        //         $output = $curl->get($url);
        //         $return = json_decode($output['body'],true);

        //         if($return['code'] ===0  && $return['msg'] =='功能不存在，请检查！'){
        //             $addUrl = DEVELOP_API."/outApi/AutoItem";
        //             $inArr = $curl->post($addUrl,http_build_query($vars));
        //         }
        //         echo  $one['name']."?".$one['url']."?".$return['msg']."<br>";
        //     }
        //     $body .=" 背景：为了数据的安全，权限平台对没有菜单的报表进行权限控制,目前works后台没有添加菜单的报表如果没有申请权限无法访问。<br>";
        //     $body .= "亲爱的报表创建者：您好，您在works平台配置的报表：".$one['name']."<br>";
        //     $body .= "对应的链接:<a href='http://works.meiliworks.com/".$one['url']."'>http://works.meiliworks.com/".$one['url']."</a><br>";
        //     $body .="没有在对应的开放平台添加菜单,请前往 <a href='http://developer.meiliworks.com//publish/index'>http://developer.meiliworks.com//publish/index</a> 为报表添加菜单";
        //     $body .="地址没有权限访问请找 苗迎雪或 戚玲玲 开通菜单管理权限，如果确认报表已经下线，请忽略些邮件";
        //     //echo $body;exit;
        //     // echo "<pre>";
        //     // print_r($value);
        //     // echo $value['author'];exit;
        //     $this->common->sendMail($value['author'],$body,'developer@meilishuo.com');
        //  }
        // }
     }
    /* 功能: 
     * 此算法用于截取中文字符串 
     * 函数以单个完整字符为单位进行截取,即一个英文字符和一个中文字符均表示一个单位长度 
     * 参数: 
     * 参数$string为要截取的字符串, 
     * 参数$start为欲截取的起始位置, 
     * 参数$length为要截取的字符个数(一个汉字或英文字符都算一个) 
     * 返回值: 
     * 返回截取结果字符串 
     * */ 
     function  substr_cn($string_input,$start,$length){   
        $str_input=$string_input; 
        $len=$length; 
        $return_str=""; 
        //定义空字符串 
        for ($i=0;$i<2*$len+2;$i++) 
            $return_str=$return_str." "; 
        $start_index=0; 
        //计算起始字节偏移量 
        for ($i=0;$i<$start;$i++){ 
            if (ord($str_input{$start_index}>=161)){          //是汉语   
                $start_index+=2; 
            }else{                                          //是英文  
                $start_index+=1; 
            }         
        }     
        $chr_index=$start_index; 
        //截取 
        for ($i=0;$i<$len;$i++){ 
            $asc=ord($str_input{$chr_index}); 
            if ($asc>=161){ 
                $return_str{$i}=chr($asc); 
                $return_str{$i+1}=chr(ord($str_input{$chr_index+1})); 
                $len+=1; //结束条件加1 
                $i++;    //位置偏移量加1 
                $chr_index+=2; 
                continue;             
            }else{ 
                $return_str{$i}=chr($asc); 
                $chr_index+=1; 
            } 
        }     
        return trim($return_str); 
    }//end of substr_cn 

    public function actionCc(){

        $this->render('report/test.tpl', $data);

    }
   public function actionUpall(){

        $sql ="select * from  t_visual_table ";
        $db = Yii::app()->sdb_metric_meta;
        $report  = $db->createCommand($sql)->queryAll();
        foreach ($report as $key => $value) {
            //if(empty($value['chinese_name'])){
               $nameApi = 'http://speed.meilishuo.com/api/userInfo?mail='.$value['creater'];
                 $curl = yii::app()->curl;
                 $output = $curl->get($nameApi);
                 $nameInfo = json_decode($output['body'],true);
                 if(!empty($nameInfo)){
                    $chinaName= $nameInfo['data']['name_c'];
                 }else{
                    $chinaName ='';
               }
               $upsql='update t_visual_table set chinese_name="'.$chinaName.' "  where id='.$value['id'];
               $updb = Yii::app()->db_metric_meta;
               $updb->createCommand($upsql)->execute();
               echo  "报表Id:".$value['id']."_报表中文名称:".$chinaName."<br>";
            //}
        }
       
       
   }

    public function actionAnalyst(){
        $curl = yii::app()->curl;
        $objAuth = new AuthManager();
        $url = DEVELOP_API."/OutApi/RoleUserAdd";
        $data = $objAuth->getUsernamebyGroupId(2);
        $data = $this->common->DataToArray($data,'user_name');
        $var = array(
            "departid"=>'46',
            "role"=>'data平台-分析师',
            'selected'=>$data
        );
        $output = $curl->post($url,http_build_query($var));
        $redata = $output['body'];
        echo "<pre>";
        print_r($redata);
    }
    public function  actionIndex()
    {
       //  $curl = yii::app()->curl;
       // // $url = "developer.meiliworks.com/api/DivideAuth";
       //  $url = DEVELOP_API."/OutApi/DivideAuth";
       //  echo '<pre/>';
       //  $objReport = new ReportManager();
       //  $objAuth = new AuthManager();
       //  $res = $objReport->getReportList();
       //  $id2name = array();
       //  foreach ($res as $tmp) {
       //      $name = $tmp['id'] . '_' . $tmp['cn_name'];
       //      $auth = $tmp['auth'];
       //      $arrAuth = explode(',', $auth);
       //      $nameReport = array();
       //      foreach ($arrAuth as $authTmp) {
       //          if (is_numeric($authTmp)) {
       //              if (!isset($id2name[$authTmp])) {
       //                  $userArr = $objAuth->getUsernamebyGroupId($authTmp);
       //                  foreach ($userArr as $nameTmp) {
       //                      $id2name[$authTmp][] = $nameTmp['user_name'];
       //                  }
       //              }

       //          } else {
       //              continue;
       //          }
       //          if (!is_array($id2name[$authTmp])) {

       //              continue;
       //          }

       //          foreach ($id2name[$authTmp] as $tmpName) {
       //              $nameReport[] = $tmpName;
       //          }
       //          if(!empty($nameReport)){
       //             $res= $objAuth->syncPoint( $tmp['id'] );
       //              echo 'add auth';echo $tmp['id'];
       //              var_dump($res);




       //              $userArray = array_unique($nameReport);
       //              $vars = array(
       //                  "business"=>'data平台',
       //                  "funname"=>$name,
       //                  "userArray"=>$userArray
       //              );
       //              $output = $curl->post($url, http_build_query($vars));
       //              $redata = $output['body'];
       //             print_r($vars);
       //              print_r($redata);
       //              //exit();



       //          }

       //      }
       //  }
    }




     //   print_r(array_unique($nameReport));exit();

    public function actionTest2($id = '')
    {
        $curl = yii::app()->curl;
        $url = "developer.meiliworks.com/OutApi/AutoItem";
        $vars = array(
            "business"=>'data平台',
            "funname"=>"171_移动设备基本指标",
            "description"=>'171_移动设备基本指标',
            "url"=>'/data/data',
            "sign"=>'data/data'
        );
        $output = $curl->post($url, $vars);
        $redata = $output['body'];
        print_r($redata);




    }
    public function actionCheckGroup(){


        $objAuth = new AuthManager();

        $res=$objAuth->checkReportPoint($_REQUEST['id']);
        var_dump($res);exit;
//        $points = array('281_MOB频道页流量来源','543_MOB单宝五级来源');
//
//        $objAuth->checkPoint($points);




    }

    public function actionAddAuthPoint($id = '')
    {
        $curl = yii::app()->curl;
        $url = "developer.meiliworks.com/OutApi/AutoVerify";
        //$url = "localhost:8082/api/AutoVerify";
        $vars = array(
            "business"=>'data平台',
            "funname"=>"测试111",
        );
        $output = $curl->post($url, $vars);
        $redata = $output['body'];
        print_r($redata);


$this->debug($_SERVER);

        return  '';
       $obj=new ReportManager();
     //var_dump($obj->updateGroup(5100,array('zhibinren')));
       // $res=$obj->addFavorites('8898');
      //  $res=$obj->checkTableAuth(169);
       // $res=$obj->isFavorites('171');
        $res=$obj->deleteReport($id);
//    public  function  actionCheck(){
//        $curl = yii::app()->curl;
//        $url = "developer.meiliworks.com/OutApi/CheckGroup";
//        $points = array('报表120','111');
//
//        var_dump($res);

    }
}