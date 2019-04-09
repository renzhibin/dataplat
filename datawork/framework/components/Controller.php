<?php

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
    public $user = null;
    public  $admin=false;
    public  $core = false;
    public  $super = false;
    public $auth = null;

    public function init()
    {
        $this->user = Yii::app()->user;
        $username = $this->user->username;
        $this->auth = new AuthManager();

        $toDownPng = $_REQUEST["toDownPng"];
        if($toDownPng==1){
            exit;
        }

        if(md5_file(HOMEPAHT.'/protected/script/md5.key')==$_COOKIE['down_png_request']){
            return;
        }

        if($_SERVER['REQUEST_URI']=='/'){
            $this->redirect('/visual/index');
            return ;
        }

        //白名单url,不需要验证用户
        foreach($this->auth->whiteUrl['notAuth'] as $url){
            if(strpos(strtolower($_SERVER['REQUEST_URI']),strtolower($url)) ===0)
                return true;
        }

        //邮件订阅截图时使用
        $isScriptRequest = $_REQUEST['phantomjs'] == 1 ? true : false;
        if($isScriptRequest){
            return true;
        }

        //没有登录跳转到登录页面
        $useInnerLogin = env('INNER_LOGIN_INTERFACE', false);
        if ($useInnerLogin) {
            if(empty($username) &&$_SERVER['REQUEST_URI']!='/site/index' )
            {
                $this->redirect('/site/index?lasturl='.$_SERVER['REQUEST_URI']);
                return ;
            }
        } else {
            if(!AuthService::isLogin()){
                $url = AuthService::SsoLogout();
                Yii::app()->curl->get($url);
                $url = SSO_LOGIN_URL . '?app_key=' . PROJECT_KEY . '&tarurl=' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $this->redirect($url);
                return '';
            }
        }

        if (Yii::app()->user->isSuper() || Yii::app()->user->isCore() || Yii::app()->user->isProducer()) {
            $this->admin = true;
        }

        //访问记录,todo
        //$objBehavior=new AuthManager();
        //$objBehavior->addUserBehavior();
        //用户权限验证
        if($this->auth->checkAuthFromMenu($_SERVER['REQUEST_URI'], Yii::app()->user->username)){

        }else{
            echo '报表访问方式不正确，请联系提供报表的分析师/工程师。';
            exit();
        }

    }

    public function jsonOutPut($status, $message='', $data = array())
    {

        // header("Content-type: application/json");
        if($status===false)
            $status=1;
        if($status===true)
            $status=0;
        if($status!=0 && empty($message))
            $message='失败';
        if(empty($message))
            $message='成功';

        $rs = array('status' =>(int)$status, 'msg' => $message, 'data' => $data);
        $this->common->addUserRequestToLog($rs);
        
        echo json_encode($rs);
    }

    public function beforeAction($action)
    {
        //过滤掉相关接口
        $url = '/timemail/urllibmail';
        $whiteUrl = array(
            '/timemail/urllibmail',
            '/service/getmenu',
            '/wap/speed'
        );
        foreach($whiteUrl as $url){
            if(strpos(strtolower($_SERVER['REQUEST_URI']),$url) ===0)
                return true;
        }
        $log = array(
            'url' => $_SERVER['REQUEST_URI'],
            'method' => $_SERVER['REQUEST_METHOD'],
            'para' => $_REQUEST
        );
        Yii::log($this->getLogStr($log),'info','action');
        $userbrowser = $_SERVER['HTTP_USER_AGENT'];
        $isScriptRequest = $_REQUEST['phantomjs'] == 1 ? true : false;
        if (preg_match('/Firefox/i', $userbrowser) ||
            preg_match('/Mozilla/i', $userbrowser) ||
            preg_match('/Chrome/i', $userbrowser)
        ) {
            return true;
        } else {
            if(!$isScriptRequest){
                $this->render('error/browser.tpl');
            }
            return TRUE;
        }
    }

    public  function  getLogStr($logArr,$logStr='',$depth=0){

        foreach ($logArr as $k=>$v) {
            if(is_string($v))
                $logStr.=" [$k:$v] ";
            else{
                $sublogStr='';
                foreach($v as $subk=>$subv){
                    if(is_string($subv)){
                        $sublogStr.=' {'.$subk.':'.$subv.'} ';
                    }
                }
                $logStr.=" [$k:$sublogStr] ";
            }

        }

        return $logStr;


    }

    public function afterAction($action)
    {
        //xhprof性能分析
        if (XHPROF_ON && extension_loaded('xhprof')) {
            $xhprof_data = xhprof_disable();
            include_once XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
            include_once XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
            $xhprof_runs = new XHProfRuns_Default();
            $namespace = "focus-" . $this->getId() . "-" . $action->id;
            $xhprof_runs->save_run($xhprof_data, $namespace);
        }
    }

    public  function debug($info){

        echo Yii::trace(CVarDumper::dumpAsString($info));
    }

    public function assign($key, $value)
    {
        Yii::app()->smarty->assign($key, $value);
    }

    public function render($view, $data = NULL, $return = false)
    {
        if($_REQUEST['format'] == 'json') {
            print json_encode($data);
            die;
        }

        # 使用原始登录界面（true 原始界面、false auth界面）
        Yii::app()->smarty->assign('login_type', env('INNER_LOGIN_INTERFACE', false));
        $menuObj = new MenuManager();
        //非pc端不必每次获取菜单栏
        $realUrl =  explode("?", $_SERVER['REQUEST_URI']);
        $index = explode("/", $realUrl[0]);
        if($index[1]!='wap') {
            //这里site应该也不需要菜单相关的东西
            if (empty($data['menuTitle']) && $index[1] != 'site') {
                $menuInfo = $menuObj->getMenu();
                //赋值menu信息
                Yii::app()->smarty->assign('collect', $menuInfo['collect']);
                Yii::app()->smarty->assign('specialMenu', $menuInfo['specialMenu']);
                Yii::app()->smarty->assign('powerMenu', $menuInfo['powerMenu']);
                Yii::app()->smarty->assign('commonMenu', $menuInfo['commonMenu']);
                Yii::app()->smarty->assign('menuTitle', $menuInfo['menuTitle']);
                Yii::app()->smarty->assign('urlMenu', $menuInfo['urlMenu']);
                Yii::app()->smarty->assign('collectCustom', $menuInfo['collectCustom']);
            }

            $this->objuser=new AuthManager();
            //$points[]='报表地图';
            //$return=$this->objuser->checkPoint($points);
            $is_producer = Yii::app()->user->isProducer();
            Yii::app()->smarty->assign('is_super',Yii::app()->user->isSuper());
            if($is_producer){
                Yii::app()->smarty->assign('show_sitemap', 1);
            }else{
                Yii::app()->smarty->assign('show_sitemap', 0);
            }

        }
        //判断是否是管理功能
        if(stripos($_SERVER['REQUEST_URI'],'visual/index') === false){

            //针对报表注释作特殊逻辑

            //$realUrl =  explode("?", $_SERVER['REQUEST_URI']);
            //$index = explode("/", $realUrl[0]);
            //这两行放到上面了

            switch ($realUrl[0]) {
                case '/visual/VisualConfig':
                    $url = 'project';
                    break;
                case '/project/comments':
                    $url = 'explain';
                    break;
                case '/visual/toolguider':
                    $url = 'tool';
                    break;
                case '/addition/showtimeline':
                    $url = 'tool';
                    break;
                case '/privilege/index':
                    $url = 'index';
                    break;
                case '/privilege/userroles':
                    $url = 'userroles';
                    break;
                case '/privilege/reportroles':
                    $url = 'reportroles';
                    break;
                default:
                    $url = $index[1];
                    break;
            }
            Yii::app()->smarty->assign('url_tpl',$url);

        }
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                Yii::app()->smarty->assign($key, $value);
            }
        }


        Yii::app()->smarty->assign('version',$this->getJSVersion());
        Yii::app()->smarty->assign('is_admin', $this->admin);
        Yii::app()->smarty->assign('controller', $this->getId());
        Yii::app()->smarty->assign('action', $this->getAction()->getId());
        Yii::app()->smarty->display($view);
    }


    function getJSVersion(){
        $version= file_get_contents(HOMEPAHT.'/protected/config/version.txt');

        if(!empty($version)){
            return trim($version);
        }
        else{
            return time();
        }
    }

    public function fetch($view, $data = NULL)
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                Yii::app()->smarty->assign($key, $value);
            }
        }
        return Yii::app()->smarty->fetch($view);
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        if (isset($this->$name))
            return $this->$name;
        else {
            return Creator::getInstance()->spawn($name);
        }
    }

    public function setTitle()
    {

    }

    public function trace($trace)
    {
        if (defined('YII_DEBUG') || isset($_GET['debug'])) {
            Yii::trace($trace, 'debug');
        }
    }
}
