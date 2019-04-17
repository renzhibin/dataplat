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

    public $name2url=array(
        '报表管理'=>'/report/reportlist',
        '项目管理'=>'/projecttpl/projecttplindex#/',
        '实时管理'=>'/project/real',
        '菜单管理'=>'/menu/index',
        '邮件订阅'=>'/timemail/index',
        '开发者中心'=>'/api/index',
        '站点地图'=>'/privilege/all',
        '数据拓扑'=>'/topo/index'
        // '权限管理'=>'/privilege/userroles',
    );

    public $powerUrl =array(
        '权限管理'=>'/privilege/index',
        //'老版权限'=>'/privilege/userroles',
        '分组管理'=>'/privilege/reportroles',
    );
    //获取菜单
    function getMenu(){
        $menuInfo = array();
        $menuConf=array();
        $menuCommon = array();
        $objauth = new AuthManager();
        $superFlag = $objauth->isSuper();
        $superItem = ['实时管理', '开发者中心', '权限管理', '站点地图'];

        //配置管理类菜单
        //admin的值在init中已获得
        if($this->admin && !$this->core){
            foreach($this->name2url as $name=>$url){
                if(!$superFlag && in_array($name, $superItem)) {
                    continue;
                }
                $index = explode("/", $url);
                $menuConf[$name]=array(
                    'name'=>$name,
                    'url'=>$url,
                    'index'=>$index[1]
                );
            }
            foreach($this->powerUrl as $name=>$url){
                if(!$superFlag && in_array($name, $superItem)) {
                    continue;
                }
                $index = explode("/", $url);
                $powerConf[$name]=array(
                    'name'=>$name,
                    'url'=>$url,
                    'index'=>$index[2]
                );
            }
        }

        //这两个菜单所有人都 可见
        //hue,todo
        //$menuCommon[]=array('name'=>'HUE','url'=>'http://10.6.3.21:50070/','index'=>'HUE');
        $menuCommon[]=array('name'=>'报表注释','url'=>'/project/comments','index'=>'explain');
        $menuCommon[]=array('name'=>'项目时间线','url'=>'/addition/showtimeline','index'=>'addition');

        //获取菜单信息
        $objMenu=new MenuManager();
        $menuResult=$objMenu->selectMenu();
        $URLMenuRes=$objMenu->selectURLMenu();
        $URLMenu=array();
        $objAuth=new AuthManager();
        if(is_array($URLMenuRes)){
            foreach($URLMenuRes as $key=>$value){
                $value['table_id']=json_decode($value['table_id'],true);
                $table_value=$value;
                $table_value['table_id']=array();
                foreach($value['table_id'] as $v_table_id) {
                    if ($this->admin) {
                        $table_value['table_id'][] = $v_table_id;
                    } else {
                        $checkRes = $objAuth->checkPoint(array($v_table_id['id']));
                        if (!empty($checkRes)) {
                            $table_value['table_id'][] = $v_table_id;
                        }
                    }

                }
                $URLMenu[]=$table_value;
            }
        }
        //$userCollect=array();
        //获取报表信息
        $objVisual=new VisualManager();
        $objVisual->InitTableConf($objMenu->allmenutable);

        //展示各级菜单
        $menuRes=$objVisual->getShowMenu($this->admin,$menuResult,$objMenu->allmenutable);
        //用户收藏相关
        $userCollect=$objVisual->getFavorites(Yii::app()->user->username);
        $resUserCollent=array();
        if(is_array($userCollect)){
            foreach($menuRes as $first_menu=>$secondmenuinfo){
                foreach($secondmenuinfo as $second_menu_id =>$menuinfo){
                    foreach($userCollect as $uk=>$uv){
                        if(in_array($uk,$menuinfo['table'])){
                            $uv['first_menu']=$first_menu;
                            $uv['second_menu']=$menuinfo['name'];
                            $resUserCollent[$uk]=$uv;
                            unset($userCollect[$uk]);
                        }
                    }
                }
            }
            foreach($userCollect as $uk=>$uv){
                $uv['first_menu']='';
                $uv['second_menu']='';
                $resUserCollent[$uk]=$uv;
            }
        }
        // 如果有集团日报权限 默认打开 (这只是一个具体要求，后期完全可以删除)
        if(isset($menuRes['集团数据'][972]['table'])) {
            foreach ($menuRes['集团数据'][972]['table'] as $table) {
                if($table['id'] == 554) {
                    $resUserCollent[554] = [
                        'name'        => '集团总览',
                        'id'          => '554',
                        'first_menu'  => '',
                        'second_menu' => '',
                    ];
                }
            }
        }
        //收藏的报表信息
        $menuInfo['collect'] = $resUserCollent;
        //管理工具的信息
        $menuInfo['specialMenu'] = $menuConf;
        //权限工具信息
        $menuInfo['powerMenu'] = $powerConf;
        //常用工具
        $menuInfo['commonMenu'] = $menuCommon;
        //设置默认菜单格式
        foreach ($menuRes as $one => $oneVal) {
            foreach ($oneVal as $two => $twoVal) {
                $menuRes[$one][$two]['default_id'] = $twoVal['table'][0]['id'];
            }
        }
        //菜单信息
        $menuInfo['menuTitle'] = $menuRes;
        $menuInfo['urlMenu']=$URLMenu;

        // 获取用户自定义收藏
        $userCollectCustom = $objVisual->getCustomCollect(Yii::app()->user->username);
        foreach ($userCollectCustom as $k => $v) {
            $currentID                             = $v['id'];
            $menuInfo['collectCustom'][$currentID] = [
                'name'        => $v['cn_name'],
                'id'          => $currentID,
                'first_menu'  => '',
                'second_menu' => '',
            ];
        }

        return $menuInfo;

    }

    function  checkAuth(){
        $objAuth=new AuthManager();
        if($objAuth->isProducer()){
            $this->admin=true;
        }

        if ($objAuth->isSuper()) {
            $this->super = true;
        }

        if ($objAuth->isCore()) {
            $this->admin = true;
            $this->core  = true;
        }

        // 超级管理员才有的权限
        $superList = [
            '开发者中心' => '/api/index',
            '站点地图'  => '/privilege/all',
            '权限管理'  => '/privilege/index',
            '老版权限'  => '/privilege/userroles',
            '分组管理'  => '/privilege/reportroles',
            '实时管理'  => '/project/real',
        ];

        if ($this->super) {
            return true;
        }

        if ($this->admin && !in_array(strtolower($_SERVER['REQUEST_URI']), $superList)) {
            return true;
        }

        //白名单url,所有人都可以看/调用
        $whiteUrl = array(
            '/apphomefocus',
            '/visual',
            '/addition',
            '/chart',
            '/report/addcollect',
            '/report/deletecollect',
            '/tool/GetDataReport',
            '/report/savereportcustom',
//            '/gps/showroute',
//            '/gps/getPhone',
//            '/gps/getphone',
            '/gps',
            '/heatmap',
            '/realtime',
            '/wap',
        );
        foreach($whiteUrl as $url){
            if(strpos(strtolower($_SERVER['REQUEST_URI']),$url) ===0)
                return true;
        }

        $otherurl=array(
            '/',
            '/project/comments',
            '/project/getall',
            '/project/savecomments',
            '/project/getcomments',
        );
        foreach($otherurl as $url){
            if(strtolower($_SERVER['REQUEST_URI'])==$url){
                return true;
            }
        }

        //白名单配置
        $objTool=new ToolManager();
        if($objTool->checkRefer()){
            return true;
        }

        if(!(!empty($_REQUEST['id']) && $_REQUEST['id']<508)){
            return false;
        }


    }

    public function init()
    {
        $this->user = Yii::app()->user;
        $username = $this->user->username;

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
        $whiteUrl = array(
            '/site/index',
            '/site/logout',
            '/service/getmenu',
            '/visual/getcontrast',
            '/visual/gettable',
            '/visual/getdata',
            '/visual/getchart',
            '/chart/showchart',
            '/timemail/urllibmail',
	        '/realtime/fetchygorder',
            '/realtime/fetchorder',
            '/heatmap/showmap',
            '/heatmap/MarketLayout',
            '/heatmap/GYzoneRate',
            '/salesvisit',
            '/wap/speed',
            '/site/login',
            '/site/PwdPage',
            '/site/ResetPwd',
            '/tool/fileup',
            '/tool/getFileUp',
            '/tool/CreateHiveData',
//          '/tool/ListMapData',
//          '/tool/MapData',
            '/tool/BehaviorLog'
        );
        foreach($whiteUrl as $url){
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

        //访问记录,todo
        //$objBehavior=new AuthManager();
        //$objBehavior->addUserBehavior();
        
        //用户权限验证
        if($this->checkAuth()){

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

        //非pc端不必每次获取菜单栏
        $realUrl =  explode("?", $_SERVER['REQUEST_URI']);
        $index = explode("/", $realUrl[0]);
        if($index[1]!='wap') {
            //这里site应该也不需要菜单相关的东西
            if (empty($data['menuTitle']) && $index[1] != 'site') {
                $menuInfo = $this->getMenu();
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
            $is_producer = $this->objuser->isProducer();
            Yii::app()->smarty->assign('is_super',$this->objuser->isSuper());
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
