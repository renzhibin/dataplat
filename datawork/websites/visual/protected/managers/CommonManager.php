<?php

class CommonManager extends Manager
{
    private $strList = array();


    function addSinglequote($arr)
    {
        foreach ($arr as $tmp) {
            $retu[] = '\'' . $tmp . '\'';
        }

        return $retu;

    }

    const PICKUP_KK_AUTOINCR = NULL;
    const PICKUP_KK_HOLD = TRUE;
    const PICKUP_VK_ENTIRE = NULL;
    const PICKUP_VD_SKIP = FALSE;
    const PICKUP_VD_OVERWRITE = TRUE;
    const PICKUP_VD_MERGE = NULL;

    /**
     * pickup
     * 从二维数组中提取信息
     * @param array $array 目标二维数组
     * @param mixed $valueKey
     *      DataType_Array::PICKUP_VK_ENTIRE: 使用整个子数组作为提取后的值
     *      array('field1', 'field2', ...): 使用子数组中指定的几列组成新数组作为提取后的值
     *      string: 使用指定子数组中一列值作为提取后的值
     * @param mixed $keyKey
     *      DataType_Array::PICKUP_KK_HOLD: 保留原来的Key
     *      DataType_Array::PICKUP_KK_AUTOINCR: 使用自增长Key
     *      string: 使用子数组中一列值作为提取后的key
     * @param mixed $onDup
     *      DataType_Array::PICKUP_VD_MERGE: 合并重复key对应的值
     *      DataType_Array::PICKUP_VD_OVERWRITE: 重复key的值, 后面覆盖前面
     *      DataType_Array::PICKUP_VD_SKIP: 重复key的值, 保留前面, 跳过后面
     * @static
     * @access public
     * @return array 提取到的信息
     */
    public static function pickup($array, $valueKey, $keyKey = self::PICKUP_KK_HOLD, $onDup = self::PICKUP_VD_SKIP)
    {
        $target = array();
        if (is_array($array)) {
            $index = -1;
            foreach ($array as $k => $v) {
                $key = $keyKey === self::PICKUP_KK_AUTOINCR
                    ? (++$index)
                    : ($keyKey === self::PICKUP_KK_HOLD ? $k : @$v[$keyKey]);

                if (is_string($valueKey)) {
                    $value = @$v[$valueKey];
                } else if (is_array($valueKey)) {
                    $value = array();
                    foreach ($valueKey as $vk) {
                        $value[$vk] = @$v[$vk];
                    }
                } else {
                    $value = $v;
                }

                if ($onDup === self::PICKUP_VD_MERGE) {
                    $target[$key][] = $value;
                } else if ($onDup === self::PICKUP_VD_OVERWRITE) {
                    $target[$key] = $value;
                } else if (!array_key_exists($key, $target)) {
                    $target[$key] = $value;
                }
            }
        }

        return $target;
    }

    //判断网站访问类型
    function checkDevice()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia',
                'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-',
                'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu',
                'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi',
                'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function arrayUnique($arr, $key)
    {
        $rAr = array();
        for ($i = 0; $i < count($arr); $i++) {
            if (!isset($rAr[$arr[$i][$key]])) {
                $rAr[$arr[$i][$key]] = $arr[$i];
            }
        }
        $arr = array_values($rAr);

        return $arr;
    }

    /*
     @date 2015-06-25
     @method 数据千分隔 处理
    */
    public function thousandPoints($data)
    {
        return preg_replace('/(?<=[0-9])(?=(?:[0-9]{3})+(?![0-9]))/', ',', $data);
    }

    //处理图表数据
    public function chartTable($chartData)
    {
        $keyList = array();
        $abMERGE = array();
        if (empty($chartData)) {
            return array();
        }
        foreach ($chartData as $index => $dataArray) {
            $$dataArray['name'] = str_replace("\n", "", $dataArray['name']);
            $dataArray['name'] = trim($dataArray['name']);
            foreach ($dataArray['data'] as $values) {
                //list($dt, $value) = $values;
                if (!array_key_exists($values['name'], $abMERGE)) {
                    $abMERGE[$values['name']] = array();
                }
                if (!isset($keyList[$dataArray['name']])) {
                    $keyList[$dataArray['name']] = $this->generate_Str(5);
                }
                $abMERGE[$values['name']][$keyList[$dataArray['name']]] = $values['value'];
            }
        }
        //排序数据
        $tmpArr = array();
        $tmpNum = 0;
        $list = array_keys($abMERGE);
        sort($list, SORT_NUMERIC);
        $newMerge = array();
        foreach ($list as $sort) {
            $newMerge[$sort] = $abMERGE[$sort];
        }
        // echo "<pre>";
        // print_r($newMerge);exit;
        // $newMerge = array_reverse($newMerge);
        $keyList = array_flip($keyList);
        $returnArr = array();
        $returnArr['header'] = $keyList;
        $returnArr['data'] = $newMerge;

        return $returnArr;
    }

    //二维数组排序

    /**
     * $arr 数组
     * $key 要排序的列
     * $sort  asc  desc
     */
    function arrSort($arr, $key, $sort)
    {
        if (!empty($arr)) {
            foreach ($arr as $user) {
                $ages[] = $user[$key];
            }
            if ($sort == 'asc') {
                array_multisort($ages, SORT_ASC, $arr);
            } else {
                array_multisort($ages, SORT_DESC, $arr);
            }

        }

        return $arr;
    }

    public function DataToArray($dbData, $keyword)
    {
        $retArray = array();
        if (is_array($dbData) == false or empty ($dbData)) {
            return $retArray;
        }
        foreach ($dbData as $oneData) {
            if (isset ($oneData [$keyword]) and empty ($oneData [$keyword]) == false) {
                $retArray [] = $oneData [$keyword];
            } else {
                if (isset($oneData [$keyword]) and intval($oneData[$keyword]) === 0) {
                    $retArray [] = $oneData [$keyword];
                } else {
                    $retArray [] = '-';
                }
//                 $retArray [] = '-';
            }
        }

        return $retArray;
    }

    public function DataToArrayAll($dbData, $keyword, $items)
    {
        $retArray = array();
        if (is_array($dbData) == false or empty ($dbData)) {
            return $retArray;
        }

        if (is_array($items) == false or empty ($items)) {
            return $retArray;
        }

        foreach ($items as $item) {
            $retArray[$item] = '-';
        }

        foreach ($dbData as $oneData) {
            if (isset($dbData[0]) && isset($dbData[0]['name'])) {
                if (isset ($oneData [$keyword]) and empty ($oneData [$keyword]) == false) {
                    $retArray [$oneData['name']] = $oneData[$keyword];
                } else {
                    if (isset($oneData [$keyword]) and intval($oneData[$keyword]) === 0) {
                        $retArray [$oneData['name']] = $oneData[$keyword];
                    } else {
                        $retArray [$oneData['name']] = '-';
                    }
                }
            } else {
                if (isset ($oneData [$keyword]) and empty ($oneData [$keyword]) == false) {
                    $retArray [] = $oneData [$keyword];
                } else {
                    if (isset($oneData [$keyword]) and intval($oneData[$keyword]) === 0) {
                        $retArray [] = $oneData [$keyword];
                    } else {
                        $retArray [] = '-';
                    }
                }
            }
        }

        return array_values($retArray);
    }

    public function getDateRangeArray($date_from, $date_to, $date_type)
    {
        $date_from = trim($date_from);
        $date_to = trim($date_to);
        $dates = array();
        if ($date_from == $date_to) {
            $dates[] = $date_to;

            return $dates;
        }
        if ($date_to === "") {
            $date_to = date("Y-m-d", strtotime("-1 day"));
        }
        $time_from = strtotime($date_from);
        $time_to = strtotime($date_to);
        //时间处理 月天小时
        $date_type = $date_type ? $date_type : 'day';
        switch ($date_type) {
            case 'hour':
                while ($time_from <= $time_to) {
                    $dates[] = date("Y-m-d H:00", $time_from);
                    $time_from += 60 * 60;
                }
                break;
            case 'day':
                while ($time_from <= $time_to) {
                    $dates[] = date("Y-m-d", $time_from);
                    $time_from += 24 * 60 * 60;
                }
                break;
            case 'month':
                while ($time_from <= $time_to) {
                    $dates[] = date("Y-m", $time_from);
                    $arr = getdate($time_from);
                    if ($arr['mon'] == '12') {
                        $year = $arr['year'] + 1;
                        $month = $arr['mon'] - 11;
                    } else {
                        $year = $arr['year'];
                        $month = $arr['mon'] + 1;
                    }
                    $date_from = $year . '-' . $month;
                    $time_from = strtotime($date_from);
                }
                break;
            default:
                while ($time_from <= $time_to) {
                    $dates[] = date("Y-m-d", $time_from);
                    $time_from += 24 * 60 * 60;
                }
                break;
        }

        return $dates;
    }

    function array_orderby()
    {
        $args = func_get_args();
        $data = array_shift($args);
        foreach ($args as $n => $field) {
            if (is_string($field)) {
                $tmp = array();
                foreach ($data as $key => $row)
                    $tmp[$key] = $row[$field];
                $args[$n] = $tmp;
            }
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);

        return array_pop($args);
    }

    function array_column($array, $column)
    {
        if (function_exists("array_column")) {
            return array_column($array, $column);
        } else {
            if (!is_array($array) || count($array) === 0) {
                return array();
            }
            $ret = array();
            foreach ($array as $row) {
                $ret[] = $row[$column];
            }

            return $ret;
        }
    }

    function exportExcel($titles, $columns, $rows, $filename)
    {
        // export to excel
        if (empty($filename)) $filename = date('Ymd') . '.xls';
        $excel = new CPHPExcel();
        $sheet = $excel->getActiveSheet();
        $excel->getProperties()->setCreator("focus");

        $data = array();
        $data[] = $titles;

        // data transform
        foreach ($rows as $row) {
            $tmp = array();
            foreach ($columns as $check) {
                $tmp[$check] = $row[$check];
            }
            $data[] = $tmp;
        }

        $sheet->fromArray($data, null, "A1");
        $excel->dumpToClient($filename);
    }

    public function addUserRequestToLog($requestData)
    {
        $jsonUser = Yii::app()->session['data_analysis_login_user'];
        $userInfo = json_decode($jsonUser, true);

        $userRealName = isset($userInfo['realname']) ? $userInfo['realname'] : '';
        $userEmail = isset($userInfo['user_name']) ? $userInfo['user_name'] : '';
        $requestTime = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
        $userIP = $_SERVER['REMOTE_ADDR'];
        $searchUrl = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $getParameter = str_replace('\\', '', json_encode($_GET, JSON_UNESCAPED_UNICODE));
        $postParameter = str_replace('\\', '', json_encode($_POST, JSON_UNESCAPED_UNICODE));
        if (count($requestData) >= 10000) { // 数组过大时不记录data日志
            $requestData = [];
        }
        $requestData = json_encode($requestData, JSON_UNESCAPED_UNICODE);

        $sql = "insert into t_user_request_log (`time`, `ip`, `url`, `get`, `post`, `data`, `user_name`, `user_email`) values (:time, :ip, :url, :get, :post, :data, :user_name, :user_email)";
        $parament = array(':time' => $requestTime, ':ip' => $userIP, ':url' => $searchUrl, ':get' => $getParameter, ':post' => $postParameter, ':data' => $requestData, ':user_name' => $userRealName, ':user_email' => $userEmail);
        Yii::app()->db_metric_meta->createCommand($sql)->execute($parament);
    }

    public function insertMailLog($row)
    {
        $sql = "insert into t_visual_mail_log (`mail_id`, `send_date`, `start_at`, `end_at`, `send_status`, `send_type`) values (:mail_id, :send_date, :start_at, :end_at, :send_status, :send_type)";
        $params = [
            ':mail_id'     => $row['mail_id'],
            ':send_date'   => $row['send_date'],
            ':start_at'    => $row['start_at'],
            ':end_at'      => $row['end_at'],
            ':send_status' => $row['send_status'],
            ':send_type'   => $row['send_type'],
        ];

        Yii::app()->db_metric_meta->createCommand($sql)->execute($params);
    }

    function exportHtml($titles, $columns, $rows, $filename = '')
    {

        if (count($rows) > 1000) {
            $this->addUserRequestToLog(array_slice($rows, 0, 1000));
        } else {
            $this->addUserRequestToLog($rows);
        }

        if (empty($filename)) $filename = date('Ymd') . '.xls';
        Header("Content-type:   application/octet-stream ");
        Header("Accept-Ranges:   bytes ");
        Header("Content-type:application/vnd.ms-excel;charset=utf-8");
        Header("Content-Disposition:attachment;filename=" . $filename);
        header('content-Type:application/vnd.ms-excel;charset=utf-8');

        $html = "";
        $html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $html .= '<table border=1><thead><tr>';

        foreach ($titles as $key => $val) {
            $html .= '<td style="background:cornsilk">' . $val . '</td>';
        }
        $html .= '</tr></thead>';
        $html .= '<tbody>';
        foreach ($rows as $key => $val) {
            $html .= '<tr>';
            //数据字段
            foreach ($columns as $k => $v) {
                if (isset($val[$v])) {
                    //字符替换
                    $valStr = $val[$v];
                    $valStr = str_replace('<', '&lt;', $valStr);
                    $valStr = str_replace('>', '&gt;', $valStr);
                    if (is_numeric($valStr)) {
                        // mso-number-format:\@; 为将数字处理为文本
                        if (strlen($valStr) >= 12) {
                            $html .= '<td style="mso-number-format:\@;">' . $valStr . '</td>';
                        } else {
                            $html .= '<td style="mso-number-format:\@;">' . $valStr . '</td>';
                            // $html .='<td>'.$valStr.'</td>';
                        }
                    } else {
                        $html .= '<td>' . $valStr . '</td>';
                    }
                } else {
                    $html .= '<td></td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        echo $html;
    }

    function exportAllHtml($data)
    {
        $html = "";
        if (empty($data['filename'])) $data['filename'] = date('Ymd') . '.xls';

        Header("Content-type:   application/octet-stream ");
        Header("Accept-Ranges:   bytes ");
        Header("Content-type:application/vnd.ms-excel;charset=utf-8");
        Header("Content-Disposition:attachment;filename=" . $data['filename']);
        header('content-Type:application/vnd.ms-excel;charset=utf-8');
        $html .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        foreach ($data as $items) {
            if (!empty($items['data']) && is_array($items['data'])) {
                if (count($items['data']) > 1000) {
                    $this->addUserRequestToLog(array_slice($items['data'], 0, 1000));
                } else {
                    $this->addUserRequestToLog($items['data']);
                }

                if (isset($items['title']) && !empty($items['title'])) {
                    $html .= '<span>' . $items['title'] . '</span>';
                }
                $html .= '<table border=1><thead><tr>';

                foreach ($items['name'] as $key => $val) {
                    $html .= '<td style="background:cornsilk">' . $val . '</td>';
                }
                $html .= '</tr></thead>';
                $html .= '<tbody>';
                foreach ($items['data'] as $key => $val) {
                    $html .= '<tr>';
                    //数据字段
                    foreach ($items['key'] as $k => $v) {
                        if (isset($val[$v])) {
                            //字符替换
                            $valStr = $val[$v];
                            $valStr = str_replace('<', '&lt;', $valStr);
                            $valStr = str_replace('>', '&gt;', $valStr);
                            if (is_numeric($valStr)) {
                                // mso-number-format:\@; 为将数字处理为文本
                                if (strlen($valStr) >= 12) {
                                    $html .= '<td style="mso-number-format:\@;">' . $valStr . '</td>';
                                } else {
                                    $html .= '<td style="mso-number-format:\@;">' . $valStr . '</td>';
                                    // $html .='<td>'.$valStr.'</td>';
                                }
                            } else {
                                $html .= '<td>' . $valStr . '</td>';
                            }
                        } else {
                            $html .= '<td></td>';
                        }
                    }
                    $html .= '</tr>';
                }
                $html .= '</tbody></table>';
            }
        }
        echo $html;
    }

    // gearmand 发起任务请求
    public function sendTask($p)
    {
        $client = new GearmanClient();
        $client->addServer('127.0.0.1', 4730);
        echo $client->doBackground('SendStatDataByEmail', serialize($p)), "\n";

    }

    function generate_Str($length)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        $password .= "_key";
        if (in_array($password, $this->strList)) {
            $this->generate_Str($length);
        } else {
            array_push($this->strList, $password);

            return $password;
        }
    }


    //发邮件
    /*
        mail  邮件前缀
        body  内容（ 是html文档）
        subject  标题
        from   发件人
        header  以啥格式发版
        flag  是否后缀
        $Cc 抄送人

    */
    function sendMail($receiver, $body, $subject = '', $from = 'data-dt@xiaozhu.com', $header = '', $flag = true, $Cc = '')
    {

        require_once(dirname(__FILE__) . '/../../../../framework/extensions/PHPMailer/PHPMailerAutoload.php');
        $mail = new PHPMailer();
        $mail->CharSet = "utf-8";
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = env('MAIL_HOST');
        $mail->Port = env('MAIL_PORT');
        $mail->Username = env('MAIL_USERNAME');
        $mail->Password = env('MAIL_PASSWORD');
        $mail->Encoding = "base64";
        $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        # 关闭发送邮件的debug，如需打开请参照源码注释
        $mail->SMTPDebug = 0;

        if (strpos($receiver, ';')) {
            $receiver = explode(';', $receiver);
        }

        if (is_array($receiver)) {
            if ($flag == true) {
                foreach ($receiver as $strMail) {
                    if (strpos($strMail, '@') === false) {
                        $strMail = $strMail . '@xiaozhu.com';
                    }
                    // $strMergeMail.=$strMail . ',';

                    $mail->addAddress($strMail);
                }

            } else {
                foreach ($receiver as $strMail) {
                    if (strpos($strMail, '@') === false) {
                        $strMail = $strMail . '@xiaozhu.com';
                    }
                    $mail->addAddress($strMail);
                }
            }
        } else {
            $strMail = $receiver;
            if (strpos($strMail, '@') === false) {
                $strMail = $strMail . '@xiaozhu.com';
            }
            $mail->addAddress($strMail);
        }

        if ($Cc != '') {
            $mail->addCC($Cc);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        if (!$mail->send()) {
            # echo 'Message could not be sent.';
            # echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        } else {
            # echo 'Message has been sent';
            return true;
        }
    }
}
