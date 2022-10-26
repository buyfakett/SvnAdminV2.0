<?php
/*
 * @Author: witersen
 * @Date: 2022-04-24 23:37:05
 * @LastEditors: witersen
 * @LastEditTime: 2022-05-07 14:09:40
 * @Description: QQ:1801168257
 */

namespace app\controller;

use app\service\Logs as ServiceLogs;

class Logs extends Base
{
    /**
     * 服务层对象
     *
     * @var object
     */
    private $ServiceLogs;

    function __construct($parm)
    {
        parent::__construct($parm);

        $this->ServiceLogs = new ServiceLogs($parm);
    }

    /**
     * 获取日志列表
     */
    public function GetLogList()
    {
        $result = $this->ServiceLogs->GetLogList();
        json2($result);
    }

    /**
     * 清空日志
     */
    public function ClearLogs()
    {
        $result = $this->ServiceLogs->ClearLogs();
        json2($result);
    }
}
