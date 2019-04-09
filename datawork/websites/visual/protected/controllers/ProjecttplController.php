<?php

class ProjectTplController extends Controller
{
    private $objFackcube = null;
    private $objAuth = null;
    private $objProject = null;
    private $objBehavior = null;

    function __construct()
    {
        $this->objFackcube = new FackcubeManager();
        $this->objAuth     = new AuthManager();
        $this->objProject  = new ProjectManager();
        $this->objBehavior = new BehaviorManager();
         
    }
      

    function actionProjecttplIndex()
    {
        $data = [];
        $tmpDir = Yii::app()->basePath."/../assets";
        $this->render($tmpDir.'/vue/projecttpl_index.html', $data);
    }

    function actionProjecttplRunlist()
    {
        $data = [];
        $tmpDir = Yii::app()->basePath."/../assets";
        $this->render($tmpDir.'/vue/projecttpl_runlist.html', $data);
    }
    
}

