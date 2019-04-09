<?php
class ServiceController extends Controller
{

    public function __construct()
    {
        $this->menu = new MenuManager();
		$this->report = new ReportManager();

		$this->objAuth = new AuthManager();
		$this->objRoles = new RolesManager();
		$this->common = new CommonManager();

    }
    public function actionGetMenu()
    {
        $menuList = $this->menu->selectMenu();
        $data = array();
        foreach ($menuList as $key => $value) {
            if (!empty($value['all'])) {
                foreach ($value['all'] as $item => $iVal) {
                    $one = array();
                    $one['menu_id'] = $value['id'];
                    $one['first_menu'] = $value['first_menu'];
                    $one['second_menu'] = $value['second_menu'];
                    $one['buiness'] = 'data平台';
                    if ($iVal['type'] == 1) {

                        $nameArr = $this->report->getReoport($iVal['id']);
                        $one['funname'] = $iVal['id'] . "_" . $nameArr['cn_name'];
                        $one['url'] = '/visual/index/' . $iVal['id'];
                    } else {
                        if (strrpos($iVal['url'], 'works.meiliworks.com/biData/tplShow') !== false) {
                            $urlArr = explode("/", $iVal['url']);
                            $onlyId = explode("?", end($urlArr));
                            $workId = $onlyId[0];
                            //获取work报表名称
                            $worksTable = $this->report->getWorkReport($workId);
                            $one['funname'] = $worksTable['name'];
                            $one['url'] = $worksTable['url'];
                        }
                    }
                    $data[] = $one;
                }
            }
        }
        if (empty($data)) {
            echo json_encode(array('status' => 0, 'msg' => '数据为空'));
        } else {
            //$this->jsonOutPut(,'success',$data);
            echo json_encode(array('status' => 1, 'msg' => 'success', 'data' => $data));
        }
    }

    /**
     * 获取 一级菜单 -- 二级菜单 -- 报表 这样的权限树
     *
     * 报表数据下的 sensitive_level 表示是否是敏感报表：0 普通报表；1 敏感数据报表；
     */
    public function actionGet_role_tree()
    {
        //获取所有报表
        $allReport = $this->report->getReportSingle(array('id', 'cn_name', 'IF (params LIKE \'%s:9:"sensitive";s:1:"1"%\' OR params LIKE \'%sensitive=1%\', "1", "0") AS is_sensitive'));
        $allReportInfos = $this->common->pickup($allReport, array('cn_name', 'is_sensitive'), 'id');
        // TODO 获取属于多个分组的报表ID；根据当前设计，需要过滤掉
        $numArr = $this->objAuth->checkRoleReport();
        $filterReportIds = [];
        foreach ($numArr as $num) {
            if ($num['num'] > 1) {
                $filterReportIds[] = $num['report_id'];
            }
        }

        $tree = array();
        //获取所有一级菜单，并排序
        $firstMenu = $this->menu->selectFirstMenu();
        foreach ($firstMenu as $first) {
            //获取二级菜单
            $secondMenu = $this->menu->getSecondMenu($first['first_menu']);
            if (empty($secondMenu)) {
                continue;
            }

            $secondMenus = array();
            foreach ($secondMenu as $second) {
                $tableArr = json_decode($second['table_id'], true);
                if (empty($tableArr)) {
                    continue;
                }

                $tables = array();
                foreach ($tableArr as $key => $table) {
                    // 核心类的报表，暂时不显示
                    if (array_key_exists($table['id'], $this->objAuth->coreReportWhiteList)) {
                        continue;
                    }

                    $tables[] = array(
                        'id' => $table['id'],
                        'parent_id' => $second['id'],
                        'name' => $table['id'] . "_" . $allReportInfos[$table['id']]["cn_name"],
                        "sensitive_level" => $allReportInfos[$table['id']]["is_sensitive"],
                    );
                }

                if (!empty($tables)) {
                    $secondMenus[] = array(
                        "id" => $second['id'],
                        "parent_id" => $first['id'],
                        'name' => $second['second_menu'],
                        'children' => $tables,
                    );
                }
            }

            if (!empty($secondMenus)) {
                $tree[] = array(
                    "id" => $first['id'],
                    "parent_id" => '0',
                    'name' => $first['first_menu'],
                    'children' => $secondMenus,
                );
            }
        }
        $this->jsonOutPut(0, '', $tree);
    }

	// 设置用户的权限
    public function actionSet_user_roles()
    {
        $userName = $_REQUEST['email'];
        $realName = $_REQUEST['name'];
        $mobile = $_REQUEST['mobile'];
        $group = $_REQUEST['group'];

        if (empty($userName)) {
            return $this->jsonOutPut(400, 'email 为空');
        }
        if (empty($realName)) {
            return $this->jsonOutPut(400, 'name 为空');
        }
        if (!in_array($group, ['', "2"])) {
            return $this->jsonOutPut(400, 'group 只能为 普通用户 或者 开发用户');
        }
        $message = '';
        $user = $this->objRoles->getUserByName($userName);
        if (empty($user)) {
            // 用户不存在的情况
            $ret = $this->objRoles->addUser($userName, $group, $realName, $mobile, $message);
            if (empty($ret)) {
                return $this->jsonOutPut(500, $message);
            }

            $user = $this->objRoles->getUserByName($userName);
        } else {
            // 更新用户数据
            $ret = $this->objRoles->updateUser($user['id'], $userName, $group, $realName, $mobile, $message);
            // MySQL 的特性，如果数据没有更新的话，执行的记录数也是0
            // if (empty($ret)) {
            //     return $this->jsonOutPut(500, $message);
            // }
        }
        $userID = $user['id'];

        $reportIDs = [];
        $addRet = false;
        $message = '';
        foreach ($_REQUEST['report_ids'] ?: [] as $item) {
            $reportIDs[] = explode("_", $item)[0];
        }

        if (!empty($reportIDs)) {
            $roles = $this->objRoles->getGroupsByReoport($reportIDs);
            $roleIDs = $this->common->pickup($roles, 'role_id');
            $addRet = $this->objRoles->addUserRolesMultiple([$userID], $roleIDs, $message, true);
        }

        return $this->jsonOutPut($addRet ? 0 : 500, $message);
    }

	// 清空用户的权限
    public function actionClear_user_roles()
    {
        $userName = $_REQUEST['email'];

        if (empty($userName)) {
            return $this->jsonOutPut(400, 'email 为空');
        }

        $user = $this->objRoles->getUserByName($userName);
        if (empty($user)) {
            // 用户不存在的情况
            return $this->jsonOutPut(400, "用户 {$userName} 不存在");
        }

        $this->objRoles->clearUserRoles($user['id']);
        return $this->jsonOutPut(0, '成功');
    }

}
