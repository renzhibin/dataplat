<?php
/**
 * Created by PhpStorm.
 * User: yangyulog
 * Date: 18/8/3
 * Time: 10:07
 */

class RunTaskController extends Controller {

    function __construct()
    {
        $this->objRunTask = new RunTaskManager();
    }

    public function actionAddRunTask() {
        $this->render('runtask/add_run_task.tpl',[]);
    }

    public function actionRunTask() {
        $data = $_REQUEST;
        $result = $this->objRunTask->handlDemandData($data);
        if ($result['code'] == 400) {
            $this->jsonOutPut(1,$result['msg'],[]);exit;
        }
        $result = $this->objRunTask->saveRunTask2Di($data);
        if ($result['code'] == 400) {
            $this->jsonOutPut(1,$result['msg'],[]);exit;
        }
        $this->jsonOutPut(0,$data['msg'],[]);exit;
    }

    public function actionRunTaskCallBack() {
        $id = $_REQUEST['id'];
        $demand = $this->objRunTask->getDemandById($id);
        $this->objRunTask->sendSuccessEmail($id, $demand['demand_user'], $demand['demand_name']);
        $this->jsonOutPut(0,'',[]);exit;
    }
}