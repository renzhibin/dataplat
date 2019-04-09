<?php
//site controller
class SiteController extends Controller
{

    function __construct()
    {
        $this->roleManger = new RolesManager();
    }
    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex($id = '')
    {

        $request = $_REQUEST;

        $useInnerLogin = env('INNER_LOGIN_INTERFACE', false);


        $useInnerLogin ?
            $this->render('site/login.tpl')
            :
            $this->render('visual/index.tpl');
    }

    public function actionUser()
    {
        echo Yii::app()->user->id;
        echo Yii::app()->user->username;
        echo Yii::app()->user->role;
        //var_dump(Yii::app()->user->admin);
    }

    /**
     * Displays the login page
     * auth登录
     * auth 回调此接口
     */
    public function actionLogin()
    {

        $useInnerLogin = env('INNER_LOGIN_INTERFACE', false);
        if ($useInnerLogin) {
            $model = new LoginForm;
            // if it is ajax validation request
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
                echo CActiveForm::validate($model);
                Yii::app()->end();
            }
            // collect user input data
            if (isset($_POST['LoginForm'])) {
                $model->attributes = $_POST['LoginForm'];

                // validate user input and redirect to the previous page if valid
                if ($model->validate() && $model->login()) {

                    $expires_time = time() + 60*60*24*30;
                    $uid = $model->uid;
                    $realname = $model->realname;
                    setcookie('username_token', $model->username . '#' . $uid . '#' .$realname, $expires_time, '/');
                    $returnUrl = isset($_REQUEST['lasturl']) ? $_REQUEST['lasturl'] : '/visual/index';
                    $status = $this->common->checkDevice();
//                if ($status) {
//                    $returnUrl = '/wap/index';
//                }
                    if($model->changePwd==1){
                        setcookie('username_token','',0,'/');
                        $this->render('site/reset_pwd.tpl');
                        exit();
                    }else{
                            Yii::app()->request->redirect($returnUrl);
                    }
//                Yii::app()->request->redirect($returnUrl);
                }
            }
            // display the login form
            // var_dump($model->msg);
            $this->assign('loginError', $model->msg);
            $this->assign('model', $model);
            $this->render('site/login.tpl');
        } else {
            //SSO登录情况
            $action   = $_REQUEST['action'];
            $token    = $_REQUEST['token'];
            $callback = $_REQUEST['callback'];
            switch ($action) {
                case 'login':
                    $checkToken = SSO_CHECK_TOKEN . '?token=' . $token;
                    $checkResult = file_get_contents($checkToken);
                    $content     = json_decode($checkResult, true);
                    switch ($content['code']) {
                        //登陆成功
                        case '0':
                            $userInfo = $content['data'];
                            $iphone   = $userInfo['mobile'];

                            $loginUserInfo = AuthManager::getAdminInfo($iphone);
                            $loginUserInfo['account'] = $userInfo['account'];
                            $loginUserInfo['token'] = $token;
                            AuthService::login($loginUserInfo);

                            break;
                        //参数为空
                        case '-1':
                            Yii::log('token参数为空！');
                            break;
                        //参数无效
                        case '-2':
                            Yii::log('token无效！');
                            break;
                        default:
                            //失败失败处理，写log
                            Yii::log('SSO登陆失败-->token验证未定义错误！');
                    }
                    break;
                case 'logout':
                    AuthService::logout();
                    exit;
            }
            $entry = env('PROJECT_KEY');
            header ('p3p:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
            echo $callback."('" . $entry . "')";
        }
    }


    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionLogout()
    {
        //清除登录cookie
        setcookie('username_token','',0,'/');
        $logOutUrl = AuthService::SsoLogout();
//        $this->redirect('/site/index');
        $this->redirect($logOutUrl);

    }

    /**
     * Logs out the current user and redirect to homepage.
     */
    public function actionError()
    {
        $this->render('error/404.tpl');
    }


    public function actionPwdPage()
    {
        $this->render('site/reset_pwd.tpl');
    }

    public function actionResetPwd()
    {
        //清除登录cookie
//        setcookie('username_token','',0,'/');
        $username=$_REQUEST['username'];
        $pwd=$_REQUEST['pwd'];
        $newpwd=$_REQUEST['newpwd'];
//        $message='修改密码成功';
        $status=$this->roleManger->resetPwd($username,$pwd,$newpwd,$message);
        if($status){
            setcookie('username_token','',0,'/');
        }
        $this->jsonOutPut($status?0:1, $message);
//        $this->redirect('/site/index');
    }

}
