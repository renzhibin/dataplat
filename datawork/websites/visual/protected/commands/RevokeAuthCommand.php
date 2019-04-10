<?php

Class RevokeAuthCommand extends Command
{
    function __construct($name, $runner)
    {
        $this->objBehavior = new BehaviorManager();
        $this->objRoles    = new RolesManager();
        $this->objMail     = new TimeMailManager();
        $this->objComm     = new CommonManager();

        parent::__construct($name, $runner);
    }

    function main()
    {
        $this->objMail->mail_log('提示', "  删除权限脚本开始运行");

        $days    = $this->args['days'] ?: 45;
        $endAt   = date("Y-m-d");
        $startAt = date("Y-m-d", strtotime("-{$days} day"));

        // 获取用户相关信息 不包括group为2，3的用户，只剩下普通用户
        $users = $this->objRoles->getUserList(['group' => 'normal']);

        $filterUser = [

        ];
        foreach ($users['rows'] as $user) {
            $userId   = $user['id'];
            $userName = $user['user_name'];
            $realName = $user['realname'];

            if (in_array($userName, $filterUser)) {
                $this->objMail->mail_log('特殊', "  用户 {$realName} ({$userId}) 权限无需清理");
                continue;
            }

            // 获取单个用户的权限信息 role_id、report_id、first_menu、first_menu
            $userInfo = $this->objRoles->getUserRoleList([['users.id', '=', $userId]]);
            // 获取单个用户的日志信息
            $userLogs    = $this->objBehavior->getUserVisitV2($startAt, $endAt, $userName);
            $visitReport = [];
            foreach ($userLogs as $userLog) {
                $paramConfig = json_decode($userLog['param'], TRUE);
                if (isset($paramConfig['table_id'])) {
                    $visitReport[$paramConfig['table_id']] = $paramConfig['table_id'];
                }
            }
            $monthlyReportId = $this->objBehavior->getMonthlyReportId();
            $monthlyReportIds = [];
            foreach ($monthlyReportId as $row) {
                array_push($monthlyReportIds, $row['id']);
            }
            // 取出未访问的相关报表
            $noVisitReport = [];
            foreach ($userInfo as $row) {
                if (in_array($row['report_id'], $monthlyReportIds)) {
                    continue;
                }
                if (!isset($visitReport[$row['report_id']]) && $row['updated_at'] < $startAt . '00:00:00') {
                    $noVisitReport[$row['report_id']] = [
                        'role_id'     => $row['role_id'],
                        'report_id'   => $row['report_id'],
                        'cn_name'     => $row['cn_name'],
                        'menu_id'     => $row['menu_id'],
                        'first_menu'  => $row['first_menu'],
                        'second_menu' => $row['second_menu'],
                        'del_date'    => $endAt,
                        'user_id'     => $userId,
                    ];
                }
            }

            if (empty($noVisitReport)) {
                $this->objMail->mail_log('注意', "  用户 {$realName} ({$userId}) 权限无需清理");
                continue;
            } else {
                // 删除备份
                $this->objRoles->addDelRole($noVisitReport);
                // 删除权限
                $this->objRoles->delUserRole($userId, array_column($noVisitReport, 'role_id'), $message = '');
                // 发邮件
                list($mailInfo, $mailHtml) = $this->genMailData($noVisitReport, $user, $days);
                $this->objComm->sendMail($mailInfo, $mailHtml, '【重要】数据平台报表权限回收');
                $this->objMail->mail_log('警告', "  用户 {$realName} ({$userId}) 权限清理完成");
            }
        }

        $this->objMail->mail_log('提示', "  删除权限脚本结束运行");
    }

    private function genMailData($noVisitReport, $user, $days)
    {
        $reportHtml = '';
        $reportHtml .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0;\">";
        $reportHtml .= "<td class=\"content-wrap\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0px 32px 0px 32px;\">";
        $reportHtml .= "<table cellpadding=\"0\" cellspacing=\"0\" style=\"white-space:nowrap;font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; margin: 10px 0; border-spacing: 0; border-collapse: collapse; border: 1px solid #f4f4f4; color: #333; width: 100%;\">";
        $reportHtml .= "<tbody>";
        $reportHtml .= "<tr style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; padding: 0; font-size: 12px;\">";
        $reportHtml .= "<th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;\">
                             一级菜单</th>";
        $reportHtml .= "<th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;\">
                             二级菜单</th>";
        $reportHtml .= "<th class=\"txt-l\" style=\"font-family: 'Microsoft YaHei', Arial, Helvetica, '宋体', sans-serif; margin: 0; white-space: nowrap; padding: 6px; line-height: 1.42857143; background-color: #348eda; color: #fff; border: 1px solid #f4f4f4; border-top: none; text-align: left;\">
                             报表名称</th>";
        $reportHtml .= "</tr>";

        $bodyHtml = '';
        foreach ($noVisitReport as $id => $item) {
            $bodyHtml .= '<tr style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 0; font-size: 12px;">';

            $bodyHtml .= '<td class="txt-l"
                                            style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;">' . $item['first_menu'] . '
                                    </td>';
            $bodyHtml .= '<td class="txt-l"
                                            style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;">' . $item['second_menu'] . '
                                    </td>';
            $bodyHtml .= '<td class="txt-l"
                                            style="font-family: \'Microsoft YaHei\', Arial, Helvetica, \'宋体\', sans-serif; margin: 0; padding: 6px; line-height: 1.42857143; border: 1px solid #f4f4f4; vertical-align: top; text-align: left; white-space: nowrap;">' . $item['cn_name'] . '</td>';
            $bodyHtml .= "</tr>";
        }
        $reportHtml .= $bodyHtml . "</table>";
        $mailHtml   = "尊敬的" . $user['realname'] . "：<br>您好！您开通的报表:<br>{$reportHtml}已经超过{$days}天没有访问了！<br>平台已经收回权限，如需要重新开通，请提交直属部门VP审批，并详细说明未访问原因，谢谢!";

        $mailInfo = [
            'di-inf@xiaozhu.com',
            str_ireplace('@xiaozhu.com', '@xiaozhu.com', $user['user_name']),
        ];

        return [$mailInfo, $mailHtml]; 
    }
}
