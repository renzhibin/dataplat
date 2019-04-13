<?php
/**
 * Created by PhpStorm.
 * User: gide
 * Date: 16/6/23
 * Time: 16:56
 */
class ToolManager extends Manager {

    static $cache_open = true;
    function __construct() {
        $this->t_white_interface='t_white_interface';
    }
    //保存更新
    function saveWhiteInterface($name,$refers,$url){
        $username = Yii::app()->user->username;
        $cdate=date('Y-m-d H:i:s',time());

        $sql ="insert into t_white_interface(`name`,url,refers,creater,updater,create_time,update_time) values ";
        $sql .="('{$name}','{$url}','{$refers}','{$username}','{$username}','{$cdate}','{$cdate}') ";
//        $sql .="ON DUPLICATE KEY UPDATE `name`='{$name}',url='{$url}',updater='{$username}',update_time='{$cdate}'";

        $result=Yii::app()->sdb_metric_meta->createCommand($sql)->execute();
        return $result;
    }

    function updateWhiteInterface($id,$name,$refers,$url){
        try{
            $username = Yii::app()->user->username;
            $cdate=date('Y-m-d H:i:s',time());

            $sql ="update t_white_interface set `name`='{$name}',url='{$url}',refers='{$refers}',updater='{$username}',update_time='{$cdate}' where id='{$id}'";

            Yii::app()->sdb_metric_meta->createCommand($sql)->execute();
            return true;
        }catch(Exception $e){
            return false;
        }

    }

    function selectWhiteInterface($id='',$url=''){

        try {
            $sql = "select id,`name`,url,refers,creater,updater,create_time,update_time from t_white_interface";
            $where = " where 1=1 ";
            if ($id != '') {
                $where .= " and id='{$id}' ";
            }
            if ($url != '') {
                $where .= " and `url`='{$url}' ";
            }
            $sql = $sql . $where;
            $orderby = " order by id desc ";
            $sql = $sql . $orderby;

            $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
            return $result;
        }catch(Exception $e){
            return [];
        }

    }

    function selectOpenUrl($id='',$name=''){

        try {
            $sql = "select id,cn_name,`explain`,creater,modify_user,create_date,params from t_visual_table ";
            $where = " where 1=1 and type=9 ";
            if ($id != '') {
                $where .= " and id='{$id}' ";
            }
            if($name!=''){
                $where .= " and cn_name='{$name}' ";
            }
            $sql = $sql . $where;
            $orderby = " order by id desc ";
            $sql = $sql . $orderby;

            $result = Yii::app()->sdb_metric_meta->createCommand($sql)->queryAll();
            return $result;
        }catch(Exception $e){
            return [];
        }

    }


    //
    function saveOpenUrl($name,$desc,$params){
        $username = Yii::app()->user->username;
        $cdate=date('Y-m-d H:i:s',time());
        $params=json_encode($params);

        $dataArr = array(
            "'" . $name . "'",
            "'" .$desc  . "'",
            "'" . $params . "'",
            "'" . 9 . "'",
            "'" . $username . "'",
            "'" . $username . "'",
            "'" . $cdate . "'",

        );

        $sql ="insert into t_visual_table(cn_name,`explain`,params,`type`,creater,modify_user,create_date) values ";
        $sql .=" (" . implode(",", $dataArr) . ")";
        $result=Yii::app()->sdb_metric_meta->createCommand($sql)->execute();
        return $result;
    }



    function updateOpenUrl($id,$name,$desc,$params){
        try{
            $username = Yii::app()->user->username;
            $params=json_encode($params);

            $sql ="update t_visual_table set cn_name='{$name}',`explain`='{$desc}',params='{$params}',modify_user='{$username}' where id='{$id}' and type=9 ";
            Yii::app()->sdb_metric_meta->createCommand($sql)->execute();
            return true;
        }catch(Exception $e){
            return false;
        }

    }

    //校验是否匹配白名单接口 refer
    function checkRefer(){

        $interface=$this->selectWhiteInterface();
        foreach($interface as $item){
            if(strpos(strtolower($_SERVER['REQUEST_URI']),strtolower($item['url']))===0){
                $refers=explode(PHP_EOL,$item['refers']);
                foreach($refers as $ref){
                    if(strpos(strtolower($_SERVER['HTTP_REFERER']),strtolower($ref))===0){
                        return true;
                    }
                }
            }
        }
        return false;

    }

    /**
     * 查询表是否存在
     */
    public function checkTableExsits($name){
        $path = $this->loadEnvironment();
        $commond = $path."hive -e 'use tmp;show tables;' 2>&1 ";
        exec($commond,$result);
        $tableArr = implode("\n", $result);
        if(in_array($name, $result)){
            return true;
        }else{
            return false;
        }
    }
    public function loadEnvironment(){
        //$path ='source /Users/raosong/.bash_profile;';
        $path ='export JAVA_HOME=/usr/lib/jdk1.7.0;export JRE_HOME=${JAVA_HOME}/jre;export HADOOP_HOME=/di_software/hadoop-2.5.2;export HIVE_HOME=/di_software/hive;export SQOOP_HOME=/di_software/sqoop-1.4.6;export PATH=${JAVA_HOME}/bin:${JRE_HOME}/lib:${HADOOP_HOME}/bin:${HIVE_HOME}/bin:${SQOOP_HOME}/bin:$PATH;';
        return $path;
    }
    /**
     * 读取文件
     * @param type $name
     * @return type
     */
    function inputCsv($name){   
        $out = array ();   
        $handle = fopen($name, 'r');  
        $num =0;
        while ($data = fgetcsv($handle))   
        {   $num ++;
            $out[] = $data;
            if($num >2){
                break;
            }
        } 
        return $out;   
    }  
    /**
     * 创建数据表
     */
    public function createTable($file,$table){
        $filed  =  $this->inputCsv($file);
        $filedArr = $filed[0];
        $tmpArr =[];
        foreach ($filedArr as $item){
            $tmpArr[] = $item." string";
        }
        $path = $this->loadEnvironment();
        $createStr = $path.'hive -e " create table tmp.'.$table.'('.  implode(",", $tmpArr).') ROW FORMAT DELIMITED FIELDS TERMINATED BY \',\' " 2>&1 ';
        exec($createStr,$output,$returnVal);
        echo "建表执行结果：";
        if(!$returnVal){
            echo '创建成功！ 创建的表名：'.$table;
        }else{
            echo '创建失败！ 失败原因：'.  end($output);
        }
        return $output;
    }
    /**
     * load数据
     */
    public function loadData($file,$table){
        exec("sed -i '1d' ".$file);
        $path = $this->loadEnvironment();
        $commodStr = $path.'hive -e "load data local inpath \''.$file.'\' into table tmp.'.$table.' " 2>&1 ';
        exec($commodStr,$output,$returnVal);
        echo "<br>数据导入执行结果：";
        if(!$returnVal){
            echo '导入成功！ 导入的表名：'.$table;
        }else{
            echo '导入失败！ 失败原因：'.  end($output);
        }
        return $output;
    }
    

}