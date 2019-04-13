<?php
class MenuController extends Controller
{
    function __construct(){
        $this->objMenu=new MenuManager();
        $this->objVisual=new VisualManager();
        $this->objReport=new ReportManager();
        $this->objAuth=new AuthManager();
        $this->objBehavior = new BehaviorManager();
    }

    //菜单管理首页，管理入口
    public function actionIndex($id = '')
    {
        //面包屑效果
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"index",'content'=>'管理工具');
        $indexStr[] = array('href'=>"#",'content'=>'菜单管理');
        $tplArr['guider'] = $indexStr;

        $tplArr['isSuper'] = $this->objAuth->isSuper();
        //获取当前菜单列表
        $menuList =$this->objMenu->selectMenu();
        $tplArr['list'] = $menuList;

        $this->render('menu/index.tpl', $tplArr);
    }

    //二级菜单添加入口
    public function actionAdd($id = '')
    {
        //面包屑效果
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"index",'content'=>'管理工具');
        $indexStr[] = array('href'=>"index",'content'=>'菜单管理');
        $indexStr[] = array('href'=>"#",'content'=>'添加二级菜单');
        $tplArr['guider'] = $indexStr;

        $tplArr['type'] = 'add';
        $tplArr['firstMenu'] =$this->objMenu->selectFirstMenu();
        //获取当前报表list
        $tplArr['visualList'] =  $this->objReport->getReportList('',false,false,false,true);;

        $this->render('menu/menuadd.tpl', $tplArr);
    }

    //二级菜单排序入口
    public  function  actionSort()
    {
        //面包屑效果
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"index",'content'=>'管理工具');
        $indexStr[] = array('href'=>"index",'content'=>'菜单管理');
        $indexStr[] = array('href'=>"#",'content'=>'二级菜单排序');
        $tplArr['guider'] = $indexStr;

        $tplArr['menuinfo'] = $this->objMenu->selectFirstMenu();
        $this->render('menu/sort.tpl', $tplArr);
    }

    //一级菜单排序入口
    public  function  actionFirstSort()
    {
        //面包屑效果
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"index",'content'=>'管理工具');
        $indexStr[] = array('href'=>"index",'content'=>'菜单管理');
        $indexStr[] = array('href'=>"#",'content'=>'一级菜单排序');
        $tplArr['guider'] = $indexStr;

        $tplArr['menuinfo'] = $this->objMenu->selectFirstMenu();
        $this->render('menu/firstsort.tpl', $tplArr);
    }

    //菜单编辑入口
    public function actionEditor($id)
    {
        //面包屑效果
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"index",'content'=>'管理工具');
        $indexStr[] = array('href'=>"index",'content'=>'菜单管理');
        $indexStr[] = array('href'=>"#",'content'=>'编辑菜单');
        $tplArr['guider'] = $indexStr;

        $tplArr['type'] = 'editor';
        $tplArr['id'] = $id;
        $visualList =   $this->objReport->getReportList('',false,false,false,true);
        //获取当前ur
        $tplArr['visualList'] = $visualList;
        $tplArr['menuInfo'] = json_encode($this->objMenu->selectMenu($id));
        $tplArr['firstMenu'] =$this->objMenu->selectFirstMenu();
        $this->render('menu/menuadd.tpl', $tplArr);
    }

    //菜单下报表排序入口
    public  function  actionReport(){
        //面包屑效果
        $indexStr[] = array('href'=>"../visual/index",'content'=>'首页');
        $indexStr[] = array('href'=>"index",'content'=>'管理工具');
        $indexStr[] = array('href'=>"index",'content'=>'菜单管理');
        $indexStr[] = array('href'=>"#",'content'=>'报表排序');

        $sort_id=$_REQUEST['sort_id'];
        $objMenu=new MenuManager();
        $objVisual=new VisualManager();
        $menuResult=$objMenu->selectMenu();

        $objVisual->InitTableConf($objMenu->allmenutable);

        //获取菜单信息
        $res=$objVisual->getShowMenu($this->admin,$menuResult,$objMenu->allmenutable,$sort_id);//展示各级菜单

        $this->render('menu/report.tpl', array('reportinfo'=>$res,'sort_id'=>$sort_id,'guider'=>$indexStr));
    }

    public function actionAddSave()
    {
        if(!$this->objAuth->isProducer()){
            $this->jsonOutPut(1,'只有分析师才能新建菜单哦~');
            exit();
        }

        $params = $_REQUEST;
        $params['second_menu'] = trim($params['second_menu']);
        $res = ConstManager::checkName($params['second_menu']);
        if($res===false){
            $this->jsonOutPut(1,'菜单名必须是中英文、数字、小括号或者下划线且不超过15个字符');
            exit();
        }

        //所有报表信息
        $visualList =   $this->objReport->getReportList('',false,false,false,true);
        $tableInfo=array();
        foreach($visualList as $t){
            $tableInfo[$t['id']]=$t;
        }
        // for test
       // $params['table_id']=array('501','503');
       // $params['url']=array(array('name'=>'百度','url'=>'www.baidu.com'),array('name'=>'qq','url'=>'www.qq.com'));
        
        $arrTableid=array();
        if(!empty($params['table_id']) && is_array($params['table_id'])){
            foreach($params['table_id'] as $tmp){
                if(array_key_exists($tmp,$tableInfo) && $tableInfo[$tmp]['type']==9){
                    $arrTableid[$tmp]=array('type'=>3,'id'=>$tmp);
                }else{
                    $arrTableid[$tmp]=array('type'=>1,'id'=>$tmp);
                }
            }
        }

        if(is_string($params['url']) && !empty($params['url'])){
            $arrUrl=array();
            foreach ( explode("\n",$params['url']) as $v) {
                if(empty($v))
                    continue;
                $tmp=explode(":",$v,2);
                if(count($tmp)!=2){
                    $this->jsonOutPut(1,$v.'不符合规范');
                    exit();
                }
                $arrUrl[trim($v)]=array('name'=>trim($tmp[0]),'url'=>trim($tmp[1]));

            }
            $params['url']=$arrUrl;
        }

        if(!empty($params['url']) && is_array($params['url'])){
            foreach($params['url'] as $k=>$tmp){
                $arrTableid[$k]=array('type'=>2,'name'=>$tmp['name'],'url'=>$tmp['url']);
            }
        }

        // echo '<pre/>';print_r($arrTableid);exit();
        list($newRuleList, $removeRuleList) = $this->objMenu->getAddAndRemoveMenuList($params['table_id'], explode(',', $params['old_report_id']));
        if (!empty($params['menu_id'])) {
            $updateArr = array('first_menu'=>$params['first_menu'],'second_menu'=>$params['second_menu'],'table_id'=>$arrTableid);
            $srcMenu=$this->objMenu->selectMenu($params['menu_id']);
            $plusArr = array_diff_key($updateArr['table_id'], $srcMenu['all']);
            $minusArr = array_diff_key( $srcMenu['all'],$updateArr['table_id']);
            
            $res =$this->objMenu->updateMenu($params['menu_id'], $updateArr);
            // 创建权限分组 包含新增以及删除
            $this->objMenu->addRuleListForAUTH($params['menu_id'], $newRuleList, $removeRuleList);
            //为了避免用户行为表的主键冲突，在action层保存修改菜单的用户行为记录
            $tmpArr = $updateArr;
            $tmpArr['plus'] = $plusArr;
            $tmpArr['minus'] = $minusArr;
            $this->objBehavior->addUserBehaviorToLog(0,$params['menu_id'],'/menu/updatemenu/menu_id/'.$params['menu_id'],$tmpArr);
        } else {
            $res=$this->objMenu->selectBymenuName($params['first_menu'], $params['second_menu']);
            if(!empty($res)){
                echo $this->jsonOutPut(1,$params['second_menu'].'已存在');
                exit();
            }
            $res =$this->objMenu->addMenu($params['first_menu'], $params['second_menu'],$arrTableid);
            // 创建权限分组 只包含新增
            $this->objMenu->addRuleListForAUTH($res, $newRuleList, $removeRuleList);
            if(!empty($res))
                $res=True;
        }
        echo $this->jsonOutPut($res);
    }

    public  function  actionSaveSort(){
       // echo '<pre/>';print_r($_REQUEST);
        $first_menu=$_REQUEST['first_menu'];
        $sortinfo=$_REQUEST['sortinfo'];
        $res=$this->objMenu->saveSortMenu($sortinfo);

        $this->jsonOutput($res);

    }

    public  function  actionSaveReport(){
        $sortinfo=$_REQUEST['sortinfo'];
        if(empty($sortinfo)){
            $this->jsonOutput(1);
            exit();
        }
        $menu_id=$_REQUEST['menu_id'];
        $info=array();
        $cn2en=array('报表'=>1,'外链'=>2,'外链2'=>3);

        foreach($sortinfo as $k=>$v){
            $v['type']=$cn2en[$v['type']];

            $v['name']=trim($v['name']);
            $info[]=$v;
        }
        $res=$this->objMenu->updateMenu($menu_id,array('table_id'=>$info));
        $this->jsonOutput($res);
    }

    public function actionDeleteMenu()
    {
        if(!$this->objAuth->isProducer()){
            $this->jsonOutPut(1,'只有分析师才能删除菜单');
            exit();
        }
        $params = $_REQUEST;
        $res =$this->objMenu->deleteMenu($params['id']);
        echo $this->jsonOutPut($res);
    }

    public function actionGetSecondMenu($first_menu){
        if(empty($first_menu))
            return;
        $res=$this->objMenu->selectSecnodMenu($first_menu);
        echo $this->jsonOutPut(0,'',$res);
    }
}