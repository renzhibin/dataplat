<?php
class FetchController extends Controller
{
    function __construct()
    {
        $this->objFetch=new FetchManager();
    }

    //需求添加
    public function actionAddDemand()
    {
        $this->render('fetch/add_demand.tpl',[]);
    }

    public function actionDelDemand() {
        $data = $this->objFetch->delDemandData();
        $this->jsonOutPut(0,'',$data);exit;
    }

    //邮件eml文件上传
    function actionUploadFile(){
        $dir = Yii::app()->basePath . "/runtime/fetch/eml/" . date("Ymd");
        if(!is_dir($dir)){
            mkdir($dir);
        }
        $name =  $dir . "/" . $_FILES['imexcel']['name'];
        $fileType = explode('.',$name); //文件类型
        $fileType = end($fileType); //文件类型
        if($fileType !== 'eml'){
            $this->jsonOutPut(400,'success', array('file' => '请上传正确的邮件EML文件'));
            exit;
        }
        $fileUp = new CUploadedFile($_FILES['imexcel']['name'], $_FILES['imexcel']['tmp_name'], $_FILES['imexcel']['type'], $_FILES['imexcel']['size'], $_FILES['imexcel']['error']);
        $fileUp->saveAs($name);

        $this->jsonOutPut(0,'success', array('file' => $name));
    }

    //保存需求&处理需求
    public function actionSaveDemand()
    {
        $data = $_REQUEST;
        $data = $this->objFetch->handlDemandData($data);
        $this->jsonOutPut(0,'',$data);exit;
    }

    //需求列表展示
    public function actionDemand()
    {
        $tplArr['visualList'] = $this->objFetch->getDemandList();
        $this->render('fetch/demand_list.tpl',$tplArr);
    }

    //需求列表中的相关下载
    public function actionDownloadFile()
    {
        switch ($_REQUEST['action']) {
            case 'eml_path':
                $data = $this->objFetch->getDemandList(array('demand_id' => $_REQUEST['id']));
                if(!empty($data)){
                    $fileName = $data[0][$_REQUEST['action']];
                    Yii::app()->request->sendFile($fileName,file_get_contents($fileName)); 
                    exit;
                }
                break;
            case 'zip_path':
                $data = $this->objFetch->getDemandList(array('demand_id' => $_REQUEST['id']));
                if(!empty($data)){
                    $fileName = $data[0][$_REQUEST['action']];
                    $data = array(
                        'demand_id' => $_REQUEST['id'],
                        'download_email' => Yii::app()->user->username,
                    );
                    $data = $this->objFetch->SaveDemandDownLog($data);
                    Yii::app()->request->sendFile($fileName,file_get_contents($fileName)); 
                    exit;
                }
                break;
            default:
                $data = $this->objFetch->getDemandDownLog(array('demand_id' => $_REQUEST['id']));
                if(!empty($data)){
                    $html ='';
                    foreach ($data as $value) {
                        $html .= trim($value['download_email']) . "\t" . trim($value['created_at']) . "\n";
                    }
                    $fileName = '需求' . $_REQUEST['id'] . '下载日志.txt';
                    Yii::app()->request->sendFile($fileName,$html); 
                    exit;
                }
                break;
        }
    }

    //数据结果下载
    public function actionDownload()
    {
        $dataArr = [];
        if($_REQUEST['down_action']){
            if(empty($_REQUEST['demand_pwd'])){
                $dataArr = array(
                    'code' => 400,
                    'msg' => '请输入提取码!',
                );
            }else{
                $data = $this->objFetch->downloadDemandFile($_REQUEST['demand_pwd']);
                if($data['code'] == 200){
                    $filePath = $data['msg'];
                    Yii::app()->request->sendFile($filePath,file_get_contents($filePath)); 
                }else{
                    $dataArr = $data;
                }
            } 
        }
        $this->render('fetch/download_demand.tpl',$dataArr);
    }
}


