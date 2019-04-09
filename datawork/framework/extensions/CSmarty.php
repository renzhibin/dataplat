<?php
require_once (Yii::getPathOfAlias('framework.extensions.Smarty') . DIRECTORY_SEPARATOR . 'Smarty.class.php');

define('SMARTY_VIEW_DIR', Yii::getPathOfAlias('application.views'));
define('RUNTIME', Yii::getPathOfAlias('application.runtime'));

class CSmarty extends Smarty
{
    const SEP = DIRECTORY_SEPARATOR;
    
    function __construct()
    {
        parent::__construct();
        
        $this -> template_dir = SMARTY_VIEW_DIR;
        $this -> compile_dir = RUNTIME . self::SEP . 'compile';
        $this -> caching = false;
        $this -> cache_dir = RUNTIME . self::SEP . 'cache';
        $this -> left_delimiter = '{/';
        $this -> right_delimiter = '/}';
        $this -> cache_lifetime = 0;
        
        // init general variables
        // $this -> assign('app', 'focus');
    }
    
    function init()
    {
    	// code
    }
}