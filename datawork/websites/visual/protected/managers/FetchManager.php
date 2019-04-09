<?php
class FetchManager extends Manager
{
    function __construct()
    {
        $this->objComm = new CommonManager();
        $this->fetch_data_path = Yii::app()->basePath . "/runtime/fetch/data/" . date("Ymd");
        if(!is_dir($this->fetch_data_path)){
            mkdir($this->fetch_data_path);
        }
        $this->fetch_water_mark = 'mono /home/apple/WaterMark/WaterMark.exe ';
        $this->white_user_list = ['liuweiqi@qufenqi.com','duguosheng@qudian.com','yangzongqiang@qufenqi.com'];
        $this->fetchRequestList = "fetch_request_list";
        $this->fetchDownloadLog = "fetch_download_log";
    }

    /*
     * 检查微信钱包冲突
     * false代表冲突,失败
     * true代表成功,无重复
     */
    public function handlDemandData($data)
    {
        $data['created_email']  = Yii::app()->user->username ? : $_REQUEST['user_name'];
        $data['created_user'] = Yii::app()->user->name ? : $_REQUEST['true_name'];
        $password = substr(md5($data['created_email'] . time()),16);

        $zip = $this->getFileFromHadoop($data, $password);
        if($zip['code'] !== 200 ){
            return $zip;
        }
        $data['zip_path'] = $zip['msg'];
        $data['password'] = $password;

        $sql = "insert into  $this->fetchRequestList (
                    `demand_name`,
                    `demand_user`,
                    `demand_email`,
                    `company_name`,
                    `eml_path`,
                    `data_path`,
                    `zip_path`,
                    `password`,
                    `created_user`,
                    `created_email`
                ) values (
                    :demand_name,
                    :demand_user,
                    :demand_email,
                    :company_name,
                    :eml_path,
                    :data_path,
                    :zip_path,
                    :password,
                    :created_user,
                    :created_email
                )";
        $res = Yii::app()->db_metric_meta->createCommand($sql)->execute($data);
        if ($res == false) {
            $res = array(
                'code' => 400,
                'msg'  => '保存失败，请重新保存!'
            );
            return $res;
        }else{
            $html = $data['created_user'] . '，上午好:<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您的文件已成功上传,提取码为' . $password . ',可接收人为' . $data['demand_email'] . '<br/><br/>谢谢!';
            $title = '【文件处理工具】您的需求' . $data['demand_name'] . '已成功处理';
            $this->objComm->sendMail($data['created_email'], $html, $title);
            $res = array(
                'code' => 200,
                'msg'  => '保存成功!'
            );
            return $res;
        }
    }

    public function getFileFromHadoop($data, $password)
    {
        ini_set ('memory_limit', '-1');
        require "PHPExcel/PHPExcel.php";
        $res = [];
        $pathList = explode(',', $data['data_path']);
        $filePath = $this->fetch_data_path;
        $fetch_water_mark = $this->fetch_water_mark;
        $zipPath = $filePath . '/' . date('Y_m_d_H_i_s-') . substr(md5(time() . rand(0, 1000000)), 16) . '.zip';
        $zipExec = 'zip -jP ' . $password . ' ' . $zipPath;
        foreach ($pathList as $list) {
            $fileName = explode('/', $list); //文件名
            $fileName = end($fileName);
            $fileType = explode('.',$fileName); //文件类型
            $fileType = end($fileType); //文件类型
            $outFile = $filePath . '/' . $fileName; //最后文件的地址

            if (!in_array($fileType, ['txt','csv','xls','xlsx'])) {
                $res = array(
                    'code' => 400,
                    'msg'  => '文件格式不正确,请确认!'
                );
                return $res;
            }
            if (file_exists($filePath . '/' . $fileName)) {
                rename($filePath . '/' . $fileName, $filePath . '/' . $fileName . date('Y_m_d_H_i_s-') . substr(md5(time() . rand(0, 1000000)), 16) . '.del');
            }
            exec('export PATH=/usr/lib/sqoop-current/bin:/usr/lib/spark-current/bin:/usr/lib/pig-current/bin:/usr/lib/hive-current/hcatalog/bin:/usr/lib/hive-current/bin:/usr/local/bin:/bin:/usr/bin:/usr/local/sbin:/usr/sbin:/usr/lib/hadoop-current/bin:/usr/lib/hadoop-current/sbin:/usr/lib/hadoop-current/bin:/usr/lib/hadoop-current/sbin:/home/apple//.local/bin:/home/apple//bin;export JAVA_HOME=/usr/lib/jvm/java-1.8.0;export HADOOP_CONF_DIR=/etc/ecm/hadoop-conf;export HADOOP_HOME=/usr/lib/hadoop-current;hadoop fs -get ' . $list . ' ' . $filePath . ' 2>&1', $info, $status);
            if ($status <> 0) {
                $res = array(
                    'code' => 400,
                    'msg'  => '无此文件' . $list . ' ,请确认!'
                );
                return $res;
            }

            switch ($fileType) {
                case 'csv':
                    try {
                        $fileData = file($outFile);
                        $exportData = [];
                        $objPHPExcel = new PHPExcel();
                        $objSheet = $objPHPExcel->getActiveSheet();
                        $objSheet->setTitle("sheet1");
                        $column = count(explode(',', $fileData[0]));
                        $index_list = [];
                        $index = 0;
                        for ($i = 0; $i <= 26; $i++) {
                            $a = $i == 0 ? '' : chr(64 + $i);
                            for ($j = 1; $j <= 26; $j++) {
                                $index_list[] = $a . chr(64 + $j);
                                $index++;
                                if ($index >= $column) {
                                    break;
                                }
                            }
                            if ($index >= $column) {
                                break;
                            }
                        }
                        foreach ($fileData as $key => $row) {
                            $row = explode(',', $row);
                            foreach ($row as $k => $v) {
                                if (strlen((string)$v) >= 15) {
                                    $v = $v . "\t";
                                }
                                $objSheet->setCellValue($index_list[$k] . ($key + 1), $v);
                            }
                        }

                        $outPath = $filePath . '/' . str_replace('csv', 'xlsx', $fileName); //最后文件的地址
                        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                        $objWriter->save($outPath);
                        exec($fetch_water_mark . $outPath . ' ' . $data['company_name'] . ' 2>&1', $info, $status);
                        if ($status <> 0) {
                            $res = array(
                                'code' => 400,
                                'msg' => $list . '水印添加失败 ,请确认!'
                            );
                            return $res;
                        }
                    } catch (\Exception $e) {
                        $res = array(
                            'code' => 400,
                            'msg'  => '不是正规的csv文件,请确认!'
                        );
                        return $res;
                    }
                    break;
                case 'xlsx':
                case 'xls':
                    $outPath = $filePath . '/' . $fileName;
                    exec($fetch_water_mark . $outPath . ' ' . $data['company_name'].' 2>&1', $info, $status);
                    if ($status <> 0) {
                        $res = array(
                            'code' => 400,
                            'msg'  => $list . '水印添加失败 ,请确认!'
                        );
                        return $res;
                    }
                    break;
                default:
                    $outPath = $filePath . '/' . $fileName;
                    break;
            }
            $zipExec .= ' '.$outPath;
        }
        exec($zipExec . ' 2>&1', $info, $status);
        if($status <> 0) {
            $res = array(
                'code' => 400,
                'msg'  => $zipExec.'文件打包失败！',
            );
        }else {
            $res = array(
                'code' => 200,
                'msg'  => $zipPath
            );
        }
        return $res;
    }

    //获取需求列表
    public function getDemandList($param = array())
    {
        $obj = Yii::app()->db_metric_meta->createCommand()
            ->select('id AS demand_id,demand_name,company_name,created_user,eml_path,zip_path')
            ->from($this->fetchRequestList);
        if(!empty($param)){
            if(isset($param['demand_id']) && !empty($param['demand_id'])){
                $obj->where('id=:demand_id', array('demand_id' => $param['demand_id']));
            }
        }
        $result = $obj->queryAll();
        return $result;
    }

    //获取需求结果结果
    public function getDemandDownLog($params)
    {
        $result = Yii::app()->db_metric_meta->createCommand()
            ->select('id AS demand_id,download_email,created_at')
            ->from($this->fetchDownloadLog)
            ->where('demand_id=:demand_id', $params)
            ->queryAll();
        return $result;
    }


    //获取需求结果结果
    public function SaveDemandDownLog($data)
    {
        $sql = "insert into  $this->fetchDownloadLog (
                    `demand_id`,
                    `download_email`
                ) values (
                    :demand_id,
                    :download_email
                )";
        $res = Yii::app()->db_metric_meta->createCommand($sql)->execute($data);
        return $res;
    }

    //根据提取码进行下载文件
    public function downloadDemandFile($password)
    {
        $username = Yii::app()->user->username ? : $_REQUEST['user_name'];
        $result = Yii::app()->db_metric_meta->createCommand()
            ->select('id AS demand_id,demand_email,zip_path')
            ->from($this->fetchRequestList)
            ->where('password=:password', array('password' => $password))
            ->queryAll();

        if(empty($result) || !file_exists($result[0]['zip_path'])) {
            return array(
                'code' => 400,
                'msg'  => '没有符合条件的文件'
            );
        }

        $emailList = explode(',',$result[0]['demand_email']);

        if(in_array($username, $emailList) || in_array($username, $this->white_user_list)) {
            $data = array(
                'demand_id' => $result[0]['demand_id'],
                'download_email' => $username,
            );
            $res = $this->SaveDemandDownLog($data);
            if ($res == false) {
                return array(
                    'code' => 400,
                    'msg'  => '文件下载失败，请重新下载'
                );
            }else{
                return array(
                    'code' => 200,
                    'msg'  => $result[0]['zip_path']
                );
            }
        }else {
            return array(
                'code' => 400,
                'msg'  => '您没有该文件的权限!'
            );
        }
    }

}