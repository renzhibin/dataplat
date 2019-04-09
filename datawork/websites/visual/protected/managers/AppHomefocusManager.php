<?php
class  AppHomefocusManager extends Manager
{
    function __construct()
    {
        $this->processTable = "s_home_process";
        $this->sourceTable = "s_home_source";

        $this->entrustTable = "s_home_entrust";

        $this->quickTable = "s_home_speed_reply";


        //$this->wofkflowTable = "s_home_workflow";

        //同款库 pc和mob端同步代码的数据的库
        $this->location_listTable = "t_dolphin_con_location_list";
        $this->location_materielTable = "t_dolphin_con_materiel";
        $this->location_relationTable = "t_dolphin_con_relation";
        //同款库 splash端的数据表
        $this->location_splashTable = "t_dolphin_content_data";

        $this->db = Yii::app()->db_tongkuan;
        $this->dolphin_db=Yii::app()->db_dolphin;
        //$this->dolphin_db = Yii::app()->db_tongkuan; // 测试

        $this->dbname1="tongkuan";
        $this->dbname2="dolphin";
        //$this->dbname2="tongkuan"; // 测试

        $this->db_wechat= Yii::app()->db_swan;

        $this->comquery = new MysqlCModel();
        $this->objComm=new CommonManager();

    }

    /*
     * 检查微信钱包冲突
     * false代表冲突,失败
     * true代表成功,无重复
     */
    function checkWechat($datas){
        $return=$this->db_wechat->createCommand()->select('*')
            ->from('t_swan_wechat_mall_banner')
            ->where('((start_time >= :starttime and start_time < :endtime) or (end_time >= :starttime and end_time < :endtime ) or (start_time <= :starttime and end_time >= :endtime)) and page_id = "11" and status = 2 and `order` = ":order"',array(':starttime'=>$datas['starttime'],':endtime'=>$datas['endtime'],$datas['locationsort']))->queryRow();
        $return=$return?false:true;
        return $return;
    }

    function checkEntrust($name=null){
        if(!isset($name)||$name==null||$name==''){
            $name=Yii::app()->user->username;
        }
        $return=$this->db->createCommand()->select('*')
            ->from($this->processTable)
            ->where("owner like '%".$name."%'")
            ->queryRow();
        $return=$return?1:0;
        return $return;
    }

    function Entrust($name=null){
        if(!isset($name)||$name==null||$name==''){
            $name=Yii::app()->user->username;
        }
        $return=$this->db->createCommand()
            ->select('*')
            ->from($this->entrustTable)
            ->where("user ='".$name."'")
            ->queryRow();
        return $return;
    }
    function myEntrust($name=null){
        if(!isset($name)||$name==null||$name==''){
            $name=Yii::app()->user->username;
        }
        $return=$this->db->createCommand()
            ->select('*')
            ->from($this->processTable)
            ->where("owner like '%".$name."%'")
            ->queryAll();
        return $return;
    }

    function updateEntrust($content=null){

        $return=$this->db->createCommand()
        ->update($this->processTable,array('auditor'=>$content['content']),'id=:id',array(':id'=>$content['id']));
        return $return;
    }

    function entrustSave($content=null){
        $return=$this->db->createCommand()
            ->update($this->entrustTable,array('entrust'=>$content['entrust']),'user=:user',array(':user'=>$content['user']));
        return $return;
    }

    function entrustInsert($content=null){


$arr=array(
    'entrust'=>$content['entrust'],
    'user'=>$content['user'],
);
        $return=$this->db->createCommand()
            ->insert($this->entrustTable, $arr);

        return $return;
    }

    function insertWechat($datas){

        $bannerinfo=json_decode($datas['bannerinfo'],true);
        $onlineinfo=json_decode($datas['onlineinfo'],true);
        $url_params=json_decode($onlineinfo['mob']['url_params'],true);
        if(!isset($url_params['url'])||$url_params['url']==''||$url_params['url']==null){
            $url_params['url']='http://m.meilishuo.com';
        }
        $wechatArr=array(
            'page_id'=>11,
            'image'=>$bannerinfo['banner_mob']['home_Top_Banner']['n_pic_file'],
            'link'=>$url_params['url'],
            'start_time'=>$datas['starttime'],
            'end_time'=>$datas['endtime'],
            'position'=>1,
            'order'=>$datas['locationsort'],
            'status'=>2,
        );
        $this->db_wechat->createCommand()->insert('t_swan_wechat_mall_banner',$wechatArr);
        $id=$this->db_wechat->getLastInsertID();
        return $id;
    }

    function offlineWechat($id){
       $result = $this->db_wechat->createCommand()->update('t_swan_wechat_mall_banner',array('status'=>1),'id=:id',array(':id'=>$id));
        return $result;
    }

    //获取列表信息 $where =array("key"=>"val");
    function getList($wherearr=false,$pageNum=1,$count=20){
        $sql = "select  * from  " .$this->sourceTable." where 1=1 ";
        $pageNum == (isset($pageNum)&&$pageNum)>0?$pageNum:1;
        $beginNum = ($pageNum-1)*$count;
        //$today = date('Y-m-d H:i:s',time());
        $whereStr="";

        if ($wherearr && !empty($wherearr)){
            foreach($wherearr as $key=>$val){
                $whereStr.= " and ".$key."=".$val ;
            }
        }

        $orderstr = ' order by starttime,endtime,locationsort asc limit '.$beginNum.','.$count;
        $sql = $sql . $whereStr.$orderstr;
        $db = $this->db;
        $result = $db->createCommand($sql)->queryAll();
        return $result;

    }

    //模糊查询数据
    function getSearchData($wherearr,$pageNum=1,$count=20){
        $sql = "select  * from  " .$this->sourceTable." where 1=1 ";
        //$pageNum = (isset($wherearr['page'])&&$wherearr['page']>0)?$wherearr['page']:1;
        //$count = (isset($wherearr['pagecount'])&&$wherearr['pagecount']>0)?$wherearr['pagecount']:20;
        $beginNum = ($pageNum-1)*$count;
        $where = '';
        $today = date('Y-m-d 00:00:00',time());
        $datetime = date('Y-m-d H:i:s',time());
        $orderby="";

        if($wherearr && !empty($wherearr)){
            //搜素条件模糊查询
            if(isset($wherearr['id']) && $wherearr['id'] != ''){
                $where.=" and id like '%".$wherearr['id']."%'";
            }
            if(isset($wherearr['active_name']) && $wherearr['active_name'] != ''){
                $where.=" and active_name like '%".$wherearr['active_name']."%'";
            }
            if(isset($wherearr['creater']) && $wherearr['creater'] != ''){
                $where.=" and creater like '%".$wherearr['creater']."%'";
            }
            if(isset($wherearr['reply_status']) && $wherearr['reply_status'] != ''){
                $where.=" and reply_status = '".$wherearr['reply_status']."'";
            }
            if(isset($wherearr['starttime']) && $wherearr['starttime'] != ''){

                $where.=" and date_format(starttime,'%Y-%m-%d %H:%i:%s') >= '".$wherearr['starttime']."'";
            }
            if(isset($wherearr['endtime']) && $wherearr['endtime'] != ''){

                $where.=" and date_format(endtime,'%Y-%m-%d %H:%i:%s') <= '".$wherearr['endtime']."'";
            }
            //资源申请中begin  资源在线的line 资源过期的over
            if(isset($wherearr['begin'])){
                $where.=" and status < 5 and status != -1 and date_format(endtime,'%Y-%m-%d %H:%i:%s') >='" .$datetime."'";
            }
            if(isset($wherearr['reply_over'])){
                $where.=" and status < 5 and status != -1 and date_format(endtime,'%Y-%m-%d %H:%i:%s') <='" .$datetime."'";
            }
            if(isset($wherearr['line'])){
                $where.=" and status >= 5 and date_format(endtime,'%Y-%m-%d %H:%i:%s') >= '".$datetime."'";
            }
            if(isset($wherearr['over'])){
                $where.=" and (status = -1 or (status = 5 and date_format(endtime,'%Y-%m-%d %H:%i:%s') <= '".$datetime."'))";
                $orderby = ' order by starttime desc limit '.$beginNum.','.$count;
            }

            //资源申请通过所有汇总列表 紧急上线通过的 status=0 reply_status＝1
            if(isset($wherearr['applylist'])){
                $where.= " and (status >1 or ((status=0 or status=1) and reply_status =1)) and date_format(endtime,'%Y-%m-%d %H:%i:%s') > '".$datetime."'" ;
            }

            //需要我审核的资源
            if(isset($wherearr['myreply'])){
                $username = $wherearr['username'];
                //$where.= "  auditinfo like '{\"todo\":\"%".$username."%'";
                $where.= " and status < 5 and status != -1 and reply_status = 0 and auditinfo REGEXP '^{\"todo\".*".$username.".*\"do\".*$'> 0 ";
            }
        }

        $orderstr = ($orderby == "")?' order by starttime,endtime,locationsort asc limit '.$beginNum.','.$count :$orderby;

        $sql = $sql.$where.$orderstr;
        $db = $this->db;
        $result = $db->createCommand($sql)->queryAll();

        $sql1 = "select count(*) as total from ".$this->sourceTable." where 1=1";
        $sql1 = $sql1.$where;
        $total = $db->createCommand($sql1)->queryScalar();
        //print_r($sql1);exit;
        $res['datalist'] = $result;
        $res['total'] = $total?$total:0;
        $res['pages'] = $total == 0 ? 1 : ceil($total/$count);
        return $res;

    }

    //申请资源位
    function addSource($params){
        $today = date('Y-m-d H:i:s',time());
        //$dataArr = $params;
        $dataArr = $this->getSqlParams($params);
        $dataArr['create_time'] = $today;
        $dataArr["status"]=isset($params['status'])?$params['status']:1;
        $sqlParams = array(
            'table'=>$this->sourceTable,
            'data'=>$dataArr,
            /*'where'=>array(
                'id'=>$id
            )*/
        );
        $result = $this->comquery->runInsert($sqlParams,$this->dbname1);
        return $result;

    }
    //申请更改
    function Update($id,$params){

        $result = $this->getList(array(id=>$id));
        if(empty($result)){
            return false;
        }
        $dataArr = $this->getSqlParams($params);

        $sqlParams = array(
            'table'=>$this->sourceTable,
            'data'=>$dataArr,
            'where'=>array('id'=>$id)
        );
        $result = $this->comquery->runUpate($sqlParams,$this->dbname1);
        return $result;
    }

    function hotUpdate($params){
        return $this->db->createCommand()->update($this->sourceTable,array('status'=>$params['status'],'reply_status'=>$params['reply_status']),'id=:id',array(':id'=>$params['id']));
    }

    //过滤sql的字段名
    function getSqlParams($params){
        //sql 字段名
        $sqlArr = array("active_name"=>"","product_categroy"=>"","sourceinfo"=>"","fashioninfo"=>"","bannerinfo"=>"","onlineinfo"=>"","auditinfo"=>"","fashioninfo"=>"","locationsort"=>"","location"=>"","starttime"=>"","endtime"=>"","status"=>"","reply_status"=>"","creater"=>"","creater_name"=>"","create_time"=>"","relation_id"=>"","wechat_id"=>"");
        $dataArr = array();
        foreach($params as $key=>$val){
            if(isset($sqlArr[$key])){
                $dataArr[$key]=$val;
            }
        }
        return $dataArr;
    }

    //获取审核流程信息 status流程状态＝流程id
    function getProcessinfo($status=false){
        $id = isset($status) ? (int)$status : 1;
        $sql = "select  * from  " .$this->processTable." where 1=1 and id=".$id;
        $db = $this->db;
        $result = $db->createCommand($sql)->queryAll();
        return $result;

    }

    //获取审核流程信息 status流程状态＝流程id
    function getProcesslist($status=false){
        $id = isset($status) ? (int)$status : 1;
        $sql = "select  * from  " .$this->processTable;
        $db = $this->db;
        $result = $db->createCommand($sql)->queryAll();
        return $result;

    }

    //获取审核流程信息 status流程状态＝流程id  当id＝0时 获取1-5之间的流程审核人
    function getAuditorinfo($status=false){
        $id = isset($status) ? (int)$status : 1;
        $where = "";

        if($id == 0){
            $where = " and id <=5 ";
        } else {
            $where = " and id = ".$id;
        }

        $sql = "select auditor from  " .$this->processTable." where 1=1 ".$where;
        $db = $this->db;
        $result = $db->createCommand($sql)->queryAll();
        return $result;

    }


    //同步到线上库
    function getonline($data){
        //location_list 表1字段
        //location_key，start_time，end_time，is_active＝1，location_sort 位置排序

        //materiel 表2字段
        //title，image_url，url，ctime，operator ＝ 操作人的id

        //$location_key = $data['location_key'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $location_sort = $data['location_sort'];
        $locationkey_str = $data['locationkey_str'];

        //有效的数据
        $sql = "select l.* from t_dolphin_con_location_list l, t_dolphin_con_materiel m, t_dolphin_con_relation r where l.id=r.location_id and r.materiel_id=m.id and l.is_active=1 and r.verify_status=1 and ((l.start_time>=:start_time and l.start_time<:end_time) or (l.end_time>:start_time and l.end_time<=:end_time) or(l.start_time<:end_time and l.end_time >=:end_time)) and l.location_sort=:location_sort and m.is_active=1 and l.location_key in (".$locationkey_str.") order by location_sort";
        //$sql = "select * from t_dolphin_con_location_list where ((start_time>=:start_time and start_time<:end_time) or (end_time>:start_time and end_time<=:end_time) or(start_time<:end_time and end_time >=:end_time)) and location_sort=:location_sort and is_active=1 and location_key in (".$locationkey_str.")";
        //$sql = "select l.* from t_dolphin_con_location_list l, t_dolphin_con_materiel m, t_dolphin_con_relation r where l.id=r.location_id and r.materiel_id=m.id and l.is_active=1 and r.verify_status=1 and ((l.start_time>='2015-11-17 11:00:00' and l.start_time<"2015-11-19 12:00:00") or (l.end_time>'2015-11-17 11:00:00' and l.end_time<="2015-11-19 12:00:00") or(l.start_time<"2015-11-19 12:00:00" and l.end_time >="2015-11-19 12:00:00")) and m.is_active=1 and l.location_key in ("home_Top_Banner_6_6") order by location_sort;”

        $db =$this->dolphin_db;
        $command = $db->createCommand($sql);
        $results = $command->bindValues(array(":start_time"=>$start_time,":end_time"=>$end_time,":location_sort"=>$location_sort))->queryAll();

        return $results;

    }

    //上线同步到线上库
    function insertOnline($data){
        //location_list 表1字段
        //location_key，start_time，end_time，is_active＝1，location_sort 位置排序

        //materiel 表2字段
        //title，image_url，url，ctime，operator ＝ 操作人的id

        $today = date('Y-m-d H:i:s',time());
        $dataArr1 = array('location_key'=>$data['location_key'], 'location_sort'=>$data['location_sort'],
            'start_time'=>$data['start_time'], 'end_time'=>$data['end_time'], 'is_active'=>1);

        $sqlParams1 = array(
            'table'=>$this->location_listTable,
            'data'=>$dataArr1
        );
        //$sql = "insert into ".$this->location_listTable." (location_key,start_time,end_time,is_active,location_sort) VALUES (:location_key,:start_time,:end_time,1,:location_sort) ";
        //$command = $this->location_listTable->createCommand($sql);
        //$results = $command->bindValues(array(":start_time"=>$start_time,":end_time"=>$end_time,":location_sort"=>$location_sort,":location_key"=>$location_key))->execute();

        $dataArr2 = array('title'=>$data['title'], 'image_url'=>$data['image_url'],
            'url'=>$data['url'], 'ctime'=>$today, 'is_active'=>1,'operator'=>$data['operator']);

        $sqlParams2 = array(
            'table'=>$this->location_materielTable,
            'data'=>$dataArr2
        );

        //relation 表3字段
        //location_id, materiel_id, verify_status=1 ,verify_time,verify_operator 操作人的id

        $transaction=$this->db->beginTransaction();
        try {
            $listid = $this->comquery->runInsert($sqlParams1,$this->dbname2);
            $materid = $this->comquery->runInsert($sqlParams2,$this->dbname2);

            $sqlParams3 = array(
                'table'=>$this->location_relationTable,
                'data'=>array("location_id"=>$listid,"materiel_id"=>$materid,"verify_status"=>1,"verify_time"=>$today,"verify_operator"=>$data['operator'])
            );

            $relationid = $this->comquery->runInsert($sqlParams3,$this->dbname2);
            $transaction->commit();
            return array('realtion_id'=>$relationid,"location_id"=>$listid);

        } catch(Exception $e) {
            $transaction->rollback();
            return false;
        }

    }

    //下线同步到线上库 updateOffline()
    //关系表verify_status =4  location_list 位置列表 is_active =4;
    function updateOffline($id){
        $location_list_table = $this->location_listTable;
        $location_relationTable = $this->location_relationTable;
        $result = $this->getRealtionData($id,$location_relationTable);

        if(!empty($result) && $result[0]){
            $location_id = $result[0]['location_id'];
            $location_arr = array("is_active"=>0);
            $result = $this->UpdateDolphinData($location_id,$location_list_table,$location_arr);

            $location_arr = array("verify_status"=>4);
            $result = $this->UpdateDolphinData($id,$location_relationTable,$location_arr);

            return $location_id;

        } else {
            return false;
        }

    }
    //获取dolphin库的数据
    function getRealtionData($id,$table){
        //查询有效的is_active=1
        $sql = "select  * from  ".$table." where 1=1 and verify_status=1 and id=".$id;
        $db =$this->dolphin_db;
        $result = $db->createCommand($sql)->queryAll();
        return $result;
    }

    //获取dolphin库的splash表的数据 password=Activity_splash_global and is_active=1 获取 id 和 data_json
    function getSplashData($password){
        //查询有效的is_active=1
        $sql = "select * from ".$this->location_splashTable." where is_active=1 and password='".$password."'";
        $db =$this->dolphin_db;
        $result = $db->createCommand($sql)->queryAll();
        return $result;
    }

    //更新update splashData
    function updateSplash($id,$dataArr,$password){
        $table = $this->location_splashTable;

        $sqlParams = array(
            'table'=>$table,
            'data'=>$dataArr,
            'where'=>array('content_id'=>$id,'password'=>$password)
        );
        $result = $this->comquery->runUpate($sqlParams,$this->dbname2);
        if($result==0){
            return false;
        }
        return true;

    }

    //获取dolphin库的数据 is_active状态
    function UpdateDolphinData($id,$table,$dataarr){
        $sqlParams = array(
            'table'=>$table,
            'data'=>$dataarr,
            'where'=>array('id'=>$id)
        );
        $result = $this->comquery->runUpate($sqlParams,$this->dbname2);
        return $result;
    }

    //get total 当天 明天的总数 params = pc/mob  and startime<=$datetime<=endtime and status=1
    function getTotalLocation($starttime,$endtime,$params){
        //获取同款库的数据 不太准确
        /*$sql1 = "select count(*) as total from ".$this->sourceTable." where 1=1 ";
        $where = "and status=5 and date_format(starttime,'%Y-%m-%d %H:%i:%s') <='".$starttime."' and date_format(endtime,'%Y-%m-%d %H:%i:%s')>'".$endtime."' and location like '%".$params."%'";
        $sql1 = $sql1.$where;
        $total = $this->db->createCommand($sql1)->queryScalar();*/

        $datetime = date('Y-m-d H:i:s',time());

        //dolphin 线上库查询
        $sql1 = "select count(*) as total from ".$this->location_listTable." where 1=1 ";
        $where = " and is_active=1 and date_format(start_time,'%Y-%m-%d %H:%i:%s')<'".$starttime."' and date_format(end_time,'%Y-%m-%d %H:%i:%s')>'".$endtime."'";
        $where1 = " and location_key = '".$params."'";
        $sql1 = $sql1.$where.$where1;
        //print_r($sql1);print_r('<br/>');
        $total = $this->dolphin_db->createCommand($sql1)->queryScalar();
        $total = $total?$total:0;
        return $total;
    }

    function find_reply_id($source_id,$user,$flow){
        $return=$this->db->createCommand()->select('*')
            ->from($this->quickTable)
            ->where('`source_id` = '.$source_id.' and `user` = "'.$user.'" and `flow` = "'.$flow.'"')
            ->queryRow();
        return $return;
    }

    function get_quick_reply($user,$id_for_user){
        $return=$this->db->createCommand()->select('*')
            ->from($this->quickTable)
            ->where('`id_for_user` = "'.$id_for_user.'" and `user` = "'.$user.'" and status = 0')
            ->queryRow();
        return $return;
    }

    function kick_quick_reply($id){
        $return=$this->db->createCommand()->select('*')
            ->update($this->quickTable,array('status'=>1),'id=:id',array(':id'=>$id));
        return $return;
    }

    //添加speed快速审核库
    function addQuick($source_id,$user,$flow){
        $return=$this->db->createCommand()
            ->select('*')
            ->from($this->quickTable)
            ->where("`user` = '".$user."'")
            ->order('id_for_user desc')
            ->queryRow();
        if($return){
            $id_for_user=$return['id_for_user'];
        }else{
            $id_for_user=0;
        }
        $id_for_user=$id_for_user+1;

        $Arr=array(
            'id_for_user'=>$id_for_user,
            'status'=>0,
            'flow'=>$flow,
            'user'=>$user,
            'source_id'=>$source_id,
        );
        $this->db->createCommand()->insert($this->quickTable,$Arr);
        //$id=$this->db->getLastInsertID();
        return $id_for_user;
    }

    //$cc 抄送人是
    function sendMail($datas,$sendmails,$htmls,$title,$Cc="",$isreply=false){
        $html = '';
        $html= "<div>您好！</div><p>申请人：".$datas['creater']."</p><p>活动名称：".$datas['active_name']."</p><p>申请帧位：".$datas['locationsort']."</p><p>投放时间：".$datas['starttime']." ~ ".$datas['endtime']."</p>".$htmls."<p>需要您的处理，谢谢！</p><p>来自：首焦资源位申请流程化工具 (此邮件为系统自动发送，请勿回复)</p>";
        $sendmail_data =explode(",",$sendmails);
        $sendmail =implode(";",explode(",",$sendmails));
        $from = "<di-inf@meilishuo.com>";
        $source_id=$datas['id'];
        $flow=$datas['status'];
        //添加speed快速审核库
        if(!$isreply){
        $reply_id=$this->addQuick($source_id,$sendmail_data[0],$flow);}
        //$reply_id=$this->find_reply_id($source_id,$sendmail_data[0],$flow);
        $msg[]="您好！";
        $msg[]="申请人：".$datas['creater'];
        $msg[]="活动名称：".$datas['active_name'];
        $msg[]="需要您的处理!";
        $msg[]="快捷回复0".$reply_id."将直接通过审批";
        $msg[]="快捷回复0".$reply_id."n[驳回理由]将快速驳回";
        $msg[]="来自：首焦资源位申请流程化工具";
        //测试
        //$Cc = '';
        //print_r($Cc);exit;
        //抄送人
        $this->sendspeed($sendmails,$msg);
        $this->objComm->sendMail($sendmail,$html,"首焦资源位申请流程化工具申请处理通知",$from,'',true,$Cc);
    }

    function sendspeed($mail,$msg){
        $curl = yii::app()->curl;
        $sendmail =explode(",",$mail);
        $apiUrl = 'http://api.speed.meilishuo.com';
        $token ='5d48ae7e6ff180886639fb435d52222a';
        $url = $apiUrl."/im/publicMsg";
        foreach($sendmail as $key=>$v) {
            $params1 = array(
                'token' => $token,
                'mail' => $v
            );

            $url1 = 'http://api.speed.meilishuo.com/user/show?token=' . $token . '&mail=' . $v;
            $u_info = $curl->get($url1, $params1);
            $response = json_decode($u_info['body'], 1);
            $use_id = $response['data']['id'];
            $params = array(
                'token' => $token,
                'msg' => implode("\n", $msg),
                'user_ids' => $use_id,
                'msg_type' => 0,
                'source' => 'eg:speed'
            );
            $output = $curl->post($url, $params);
        }

    }

    //超级用户
    function getSuper(){
        //超级用户 有上线 编辑的功能
        $superuser = array("");
        $whiteurl = array("onlineurl"=>"");

        return array("superuser"=>$superuser,"whiteurl"=>$whiteurl);
    }


}