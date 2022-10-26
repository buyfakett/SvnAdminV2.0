<?php
/*
 * @Author: witersen
 * @Date: 2022-04-24 23:37:05
 * @LastEditors: witersen
 * @LastEditTime: 2022-05-06 21:37:57
 * @Description: QQ:1801168257
 */

namespace app\service;

class Crond extends Base
{
    function __construct($parm = [])
    {
        parent::__construct($parm);
    }

    /**
     * 获取特殊结构的下拉列表
     *
     * @return void
     */
    public function GetRepList()
    {
        $list = $this->database->select('svn_reps', [
            'rep_name(rep_key)',
            'rep_name',
        ]);

        $list = array_merge([[
            'rep_key' => '-1',
            'rep_name' => '所有仓库'
        ]], $list);

        return message(200, 1, '成功', $list);
    }

    /**
     * 获取任务计划列表
     *
     * @return array
     */
    public function GetCrondList()
    {
        $pageSize = $this->payload['pageSize'];
        $currentPage = $this->payload['currentPage'];
        $searchKeyword = trim($this->payload['searchKeyword']);

        //分页
        $begin = $pageSize * ($currentPage - 1);

        $list = $this->database->select('crond', [
            'crond_id',
            'sign',
            'task_type [Int]',
            'task_name',
            'cycle_type',
            'cycle_desc',
            'status',
            'save_count [Int]',
            'rep_name',
            'week [Int]',
            'day [Int]',
            'hour [Int]',
            'minute [Int]',
            'notice',
            'shell',
            'last_exec_time',
            'create_time',
        ], [
            'AND' => [
                'OR' => [
                    'task_name[~]' => $searchKeyword,
                    'cycle_desc[~]' => $searchKeyword,
                ],
            ],
            'LIMIT' => [$begin, $pageSize],
            'ORDER' => [
                $this->payload['sortName']  => strtoupper($this->payload['sortType'])
            ]
        ]);

        $total = $this->database->count('crond', [
            'crond_id'
        ], [
            'AND' => [
                'OR' => [
                    'task_name[~]' => $searchKeyword,
                    'cycle_desc[~]' => $searchKeyword,
                ],
            ],
        ]);

        foreach ($list as $key => $value) {
            // 5 6 类型不需要 count 字段
            if (in_array($value['task_type'], [5, 6])) {
                $list[$key]['save_count'] = '-';
            }

            //数字到布尔值
            $list[$key]['status'] = $value['status'] == 1 ? true : false;

            //数字到数组
            if ($value['notice'] == 0) {
                $list[$key]['notice'] = [];
            } else if ($value['notice'] == 1) {
                $list[$key]['notice'] = ['success'];
            } else if ($value['notice'] == 2) {
                $list[$key]['notice'] = ['fail'];
            } else if ($value['notice'] == 3) {
                $list[$key]['notice'] = ['success', 'fail'];
            } else {
                $list[$key]['notice'] = [];
            }

            //仓库
            $list[$key]['rep_key'] = json_decode($value['rep_name'])[0];
            unset($list[$key]['rep_name']);
        }

        return message(200, 1, '成功', [
            'data' => $list,
            'total' => $total
        ]);
    }

    /**
     * 设置任务计划
     *
     * @return array
     */
    public function SetCrond()
    {
        //todo 检查crond服务有无开启

        if (!isset($this->payload['cycle'])) {
            return message(200, 0, '参数[cycle]不存在');
        }
        $cycle = $this->payload['cycle'];

        //sign 处理
        $sign = md5(time());

        //notice 处理
        if (in_array('success', (array)$cycle['notice']) && in_array('fail', (array)$cycle['notice'])) {
            $cycle['notice'] = 3;
        } else if (in_array('fail', (array)$cycle['notice'])) {
            $cycle['notice'] = 2;
        } else if (in_array('success', (array)$cycle['notice'])) {
            $cycle['notice'] = 1;
        } else {
            $cycle['notice'] = 0;
        }

        //cycle_desc 和 code 处理
        $code = '';
        $cycle['cycle_desc'] = '';
        switch ($cycle['cycle_type']) {
            case 'minute': //每分钟
                $code = '* * * * *';
                $cycle['cycle_desc'] = "每分钟执行一次";
                break;
            case 'minute_n': //每隔N分钟
                $code = sprintf("*/%s * * * *", $cycle['minute']);
                $cycle['cycle_desc'] = sprintf("每隔%s分钟执行一次", $cycle['minute']);
                break;
            case 'hour': //每小时
                $code = sprintf("%s * * * *", $cycle['minute']);
                $cycle['cycle_desc'] = sprintf("每小时-第%s分钟执行一次", $cycle['minute']);
                break;
            case 'hour_n': //每隔N小时
                $code = sprintf("%s */%s * * *", $cycle['minute'], $cycle['hour']);
                $cycle['cycle_desc'] = sprintf("每隔%s小时-第%s分钟执行一次", $cycle['hour'], $cycle['minute']);
                break;
            case 'day': //每天
                $code = sprintf("%s %s * * *", $cycle['minute'], $cycle['hour']);
                $cycle['cycle_desc'] = sprintf("每天-%s点%s分执行一次", $cycle['hour'], $cycle['minute']);
                break;
            case 'day_n': //每隔N天
                $code = sprintf("%s %s */%s * *", $cycle['minute'], $cycle['hour'], $cycle['day']);
                $cycle['cycle_desc'] = sprintf("每隔%s天-%s点%s分执行一次", $cycle['day'], $cycle['hour'], $cycle['minute']);
                break;
            case 'week': //每周
                $code = sprintf("%s %s * * %s", $cycle['minute'], $cycle['hour'], $cycle['week']);
                $cycle['cycle_desc'] = sprintf("每周%s-%s点%s分执行一次", $cycle['week'], $cycle['hour'], $cycle['minute']);
                break;
            case 'month': //每月
                $code = sprintf("%s %s %s * *", $cycle['minute'], $cycle['hour'], $cycle['day']);
                $cycle['cycle_desc'] = sprintf("每月%s日-%s点%s分执行一次", $cycle['day'], $cycle['hour'], $cycle['minute']);
                break;
            default:
                break;
        }

        //写入 /home/svnadmin/crond/xxx
        if (!is_dir($this->config_svn['crond_base_path'])) {
            funShellExec(sprintf("mkdir -p '%s' && chmod 777 -R '%s'", $this->config_svn['crond_base_path'], $this->config_svn['crond_base_path']));
        }
        $nameCrond = $this->config_svn['crond_base_path'] . $sign;
        $nameCrondLog = $nameCrond . '.log';

        $conCrond = sprintf(
            "#!/bin/bash
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin:~/bin
export PATH
startDate=`date +%s`
echo ----------starTime:[\$startDate]--------------------------------------------
php %s %s %s
endDate=`date +%s`
echo ----------endTime:[\$endDate]--------------------------------------------",
            "\"%Y-%m-%d %H:%M:%S\"",
            BASE_PATH . '/server/command.php',
            $cycle['task_type'],
            $sign,
            "\"%Y-%m-%d %H:%M:%S\""
        );

        file_put_contents($nameCrond, $conCrond);
        // funShellExec(sprintf("chmod 777 '%s' && chmod 777 '%s'", $nameCrond, $nameCrondLog));
        funShellExec(sprintf("chmod 777 '%s'", $nameCrond));

        //crontab -l 获取原有的任务计划列表
        $result = funShellExec('crontab -l');
        $crontabs = trim($result['result']);

        //crontab file 写入新的任务计划列表
        $tempFile = tempnam('/tmp', 'svnadmin_crond_');
        file_put_contents($tempFile, (empty($crontabs) ? '' : $crontabs . "\n") . sprintf("%s %s >> %s 2>&1\n", $code, $nameCrond, $nameCrondLog));
        $result = funShellExec(sprintf("crontab %s", $tempFile));
        @unlink($tempFile);
        if ($result['code'] != 0) {
            @unlink($nameCrond);
            return message(200, 0, $result['error']);
        }

        $this->database->insert('crond', [
            'sign' => $sign,
            'task_type' => $cycle['task_type'],
            'task_name' => $cycle['task_name'], //有机会为空 不可为空
            'cycle_type' => $cycle['cycle_type'],
            'cycle_desc' => $cycle['cycle_desc'], //需要自己根据周期生成语义化描述
            'status' => 1, //启用状态 默认启用
            'save_count' => $cycle['save_count'],
            'rep_name' => json_encode([$cycle['rep_key']]),
            'week' => $cycle['week'],
            'day' => $cycle['day'],
            'hour' => $cycle['hour'],
            'minute' => $cycle['minute'],
            'notice' => $cycle['notice'],
            'code' => $code,
            'shell' => $cycle['shell'],
            'last_exec_time' => '-',
            'create_time' => date('Y-m-d H:i:s'),
        ]);

        return message(200, 1, '成功');
    }

    /**
     * 更新任务计划
     *
     * @return array
     */
    public function UpdCrond()
    {
        if (!isset($this->payload['cycle'])) {
            return message(200, 0, '参数[cycle]不存在');
        }
        $cycle = $this->payload['cycle'];

        //sign 处理
        $sign = $cycle['sign'];

        //notice 处理
        if (in_array('success', (array)$cycle['notice']) && in_array('fail', (array)$cycle['notice'])) {
            $cycle['notice'] = 3;
        } else if (in_array('fail', (array)$cycle['notice'])) {
            $cycle['notice'] = 2;
        } else if (in_array('success', (array)$cycle['notice'])) {
            $cycle['notice'] = 1;
        } else {
            $cycle['notice'] = 0;
        }

        //cycle_desc 和 code 处理
        $code = '';
        $cycle['cycle_desc'] = '';
        switch ($cycle['cycle_type']) {
            case 'minute': //每分钟
                $code = '* * * * *';
                $cycle['cycle_desc'] = "每分钟执行一次";
                break;
            case 'minute_n': //每隔N分钟
                $code = sprintf("*/%s * * * *", $cycle['minute']);
                $cycle['cycle_desc'] = sprintf("每隔%s分钟执行一次", $cycle['minute']);
                break;
            case 'hour': //每小时
                $code = sprintf("%s * * * *", $cycle['minute']);
                $cycle['cycle_desc'] = sprintf("每小时-第%s分钟执行一次", $cycle['minute']);
                break;
            case 'hour_n': //每隔N小时
                $code = sprintf("%s */%s * * *", $cycle['minute'], $cycle['hour']);
                $cycle['cycle_desc'] = sprintf("每隔%s小时-第%s分钟执行一次", $cycle['hour'], $cycle['minute']);
                break;
            case 'day': //每天
                $code = sprintf("%s %s * * *", $cycle['minute'], $cycle['hour']);
                $cycle['cycle_desc'] = sprintf("每天-%s点%s分执行一次", $cycle['hour'], $cycle['minute']);
                break;
            case 'day_n': //每隔N天
                $code = sprintf("%s %s */%s * *", $cycle['minute'], $cycle['hour'], $cycle['day']);
                $cycle['cycle_desc'] = sprintf("每隔%s天-%s点%s分执行一次", $cycle['day'], $cycle['hour'], $cycle['minute']);
                break;
            case 'week': //每周
                $code = sprintf("%s %s * * %s", $cycle['minute'], $cycle['hour'], $cycle['week']);
                $cycle['cycle_desc'] = sprintf("每周%s-%s点%s分执行一次", $cycle['week'], $cycle['hour'], $cycle['minute']);
                break;
            case 'month': //每月
                $code = sprintf("%s %s %s * *", $cycle['minute'], $cycle['hour'], $cycle['day']);
                $cycle['cycle_desc'] = sprintf("每月%s日-%s点%s分执行一次", $cycle['day'], $cycle['hour'], $cycle['minute']);
                break;
            default:
                break;
        }

        $this->database->update('crond', [
            'task_type' => $cycle['task_type'],
            'task_name' => $cycle['task_name'], //有机会为空 不可为空
            'cycle_type' => $cycle['cycle_type'],
            'cycle_desc' => $cycle['cycle_desc'], //需要自己根据周期生成语义化描述
            'save_count' => $cycle['save_count'],
            'rep_name' => json_encode([$cycle['rep_key']]),
            'week' => $cycle['week'],
            'day' => $cycle['day'],
            'hour' => $cycle['hour'],
            'minute' => $cycle['minute'],
            'notice' => $cycle['notice'],
            'code' => $code,
            'shell' => $cycle['shell'],
        ], [
            'sign' => $sign
        ]);

        return message(200, 1, '成功');
    }

    /**
     * 修改任务计划状态
     *
     * @return void
     */
    public function UpdCrondStatus()
    {
        if (!isset($this->payload['crond_id'])) {
            return message(200, 0, '参数不完整');
        }

        $result = $this->database->get('crond', '*', [
            'crond_id' => $this->payload['crond_id']
        ]);
        if (empty($result)) {
            return message(200, 0, '任务计划不存在');
        }

        $sign = $result['sign'];
        $code = $result['code'];

        //crontab -l 获取原有的任务计划列表
        $result = funShellExec('crontab -l');
        $crontabs = trim($result['result']);

        if ($this->payload['status']) {
            $nameCrond = $this->config_svn['crond_base_path'] . $sign;
            $nameCrondLog = $nameCrond . '.log';
            $crontabs = (empty($crontabs) ? '' : $crontabs . "\n") . sprintf("%s %s >> %s 2>&1", $code, $nameCrond, $nameCrondLog);
        } else {
            //查询标识并删除标识所在行
            $contabArray = explode("\n", $crontabs);
            foreach ($contabArray as $key => $value) {
                if (strstr($value, ' ' . $sign . '.log') || strstr($value, $sign . '.log')) {
                    unset($contabArray[$key]);
                }
            }
            $crontabs = trim(implode("\n", $contabArray));
        }

        if (empty($crontabs)) {
            funShellExec('crontab -r');
        } else {
            $tempFile = tempnam('/tmp', 'svnadmin_crond_');
            file_put_contents($tempFile, $crontabs . "\n");
            $result = funShellExec(sprintf("crontab %s", $tempFile));
            @unlink($tempFile);
            if ($result['code'] != 0) {
                return message(200, 0, $result['error']);
            }
        }

        //从数据库修改
        $this->database->update('crond', [
            'status' => $this->payload['status'] ? 1 : 0
        ], [
            'crond_id' => $this->payload['crond_id']
        ]);

        return message();
    }

    /**
     * 删除任务计划
     *
     * @return array
     */
    public function DelCrond()
    {
        if (!isset($this->payload['crond_id'])) {
            return message(200, 0, '参数不完整');
        }

        $result = $this->database->get('crond', '*', [
            'crond_id' => $this->payload['crond_id']
        ]);
        if (empty($result)) {
            return message(200, 0, '任务计划不存在');
        }
        $sign = $result['sign'];

        //crontab -l 获取原有的任务计划列表
        $result = funShellExec('crontab -l');
        $crontabs = trim($result['result']);

        //查询标识并删除标识所在行
        $contabArray = explode("\n", $crontabs);
        foreach ($contabArray as $key => $value) {
            if (strstr($value, $sign . '.log')) {
                unset($contabArray[$key]);
                break;
            }
        }
        if ($contabArray == explode("\n", $crontabs)) {
            //无改动 删除的为已暂停的记录
        } else {
            $crontabs = trim(implode("\n", $contabArray));
            //crontab file 写入新的任务计划列表
            if (empty($crontabs)) {
                funShellExec('crontab -r');
            } else {
                $tempFile = tempnam('/tmp', 'svnadmin_crond_');
                file_put_contents($tempFile, $crontabs . "\n");
                $result = funShellExec(sprintf("crontab %s", $tempFile));
                @unlink($tempFile);
                if ($result['code'] != 0) {
                    return message(200, 0, $result['error']);
                }
            }
        }

        //从文件删除
        @unlink($this->config_svn['crond_base_path'] . $sign);

        //删除日志
        @unlink($this->config_svn['crond_base_path'] . $sign . '.log');

        //从数据库删除
        $this->database->delete('crond', [
            'crond_id' => $this->payload['crond_id']
        ]);

        return message();
    }

    /**
     * 现在执行任务计划
     *
     * @return void
     */
    public function CrondNow()
    {
        if (!isset($this->payload['crond_id'])) {
            return message(200, 0, '参数不完整');
        }

        $result = $this->database->get('crond', '*', [
            'crond_id' => $this->payload['crond_id']
        ]);
        if (empty($result)) {
            return message(200, 0, '任务计划不存在');
        }
        $sign = $result['sign'];

        $nameCrond = $this->config_svn['crond_base_path'] . $sign;
        $nameCrondLog = $nameCrond . '.log';

        $tempFile = tempnam('/tmp', 'svnadmin_crond_');

        file_put_contents($tempFile, sprintf("%s >> %s 2>&1\n", $nameCrond, $nameCrondLog));

        $result = funShellExec(sprintf("at -f '%s' now", $tempFile));

        @unlink($tempFile);

        if ($result['code'] != 0) {
            return message(200, 0, $result['error']);
        }

        return message();
    }

    /**
     * 获取日志信息
     *
     * @return void
     */
    public function GetCrondLog()
    {
        if (!isset($this->payload['crond_id'])) {
            return message(200, 0, '参数不完整');
        }

        $result = $this->database->get('crond', '*', [
            'crond_id' => $this->payload['crond_id']
        ]);
        if (empty($result)) {
            return message(200, 0, '任务计划不存在');
        }
        $sign = $result['sign'];

        clearstatcache();
        if (file_exists($this->config_svn['crond_base_path'] . $sign . '.log')) {
            return message(200, 1, '成功', [
                'log_path' => $this->config_svn['crond_base_path'] . $sign . '.log',
                'log_con' => file_get_contents($this->config_svn['crond_base_path'] . $sign . '.log')
            ]);
        } else {
            return message(200, 1, '成功', [
                'log_path' => '未生成',
                'log_con' => ''
            ]);
        }
    }

    /**
     * 检查 crontab at 是否安装和启动
     * 
     * 使用此方式检测守护进程是否存活并不准确 如果使用 pid 判断会更好
     *
     * @return void
     */
    public function GetCronStatus()
    {
        $software = [
            [
                'shell' => 'crontab -c',
                'desc' => 'crontab 服务未安装'
            ],
            [
                'shell' => 'at -l',
                'desc' => 'at 服务未安装'
            ],
        ];

        $serviceStart = [
            [
                'shell' => 'crond',
            ],
            [
                'shell' => 'atd',
            ],
        ];

        $service = [
            [
                'shell' => 'ps aux | grep -v grep | grep crond',
                'desc' => 'crond 服务未启动'
            ],
            [
                'shell' => 'ps aux | grep -v grep | grep atd',
                'desc' => 'atd 服务未启动'
            ],
        ];

        foreach ($software as $value) {
            $result = funShellExec($value['shell']);
            if ($result['code'] != 0) {
                return message(200, 0, $value['desc'] . ': ' . $result['error']);
            }
        }

        foreach ($serviceStart as $value) {
            $result = funShellExec($value['shell']);
        }

        foreach ($service as $value) {
            $result = funShellExec($value['shell']);
            if (empty($result['result'])) {
                return message(200, 0, $value['desc']);
            }
        }

        return message();
    }
}
