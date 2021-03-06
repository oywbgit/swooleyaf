<?php

/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 18-5-27
 * Time: 上午10:10
 */

namespace SyTrait\Server;

use Swoole\Server;
use SyConstant\Project;
use Response\Result;
use SyTool\Tool;

trait ProjectHttpTrait
{
    private function checkServerHttpTrait()
    {
    }

    private function initTableHttpTrait()
    {
    }

    private function addTaskHttpTrait(Server $server)
    {
    }

    private function getTokenExpireTime(): int
    {
        $decryptStr = Tool::decrypt(SY_TOKEN_SECRET, 'f68a600f6c1b1163');
        if (is_bool($decryptStr)) {
            return 0;
        } elseif (substr($decryptStr, 0, 8) != SY_TOKEN) {
            return 0;
        }

        return (int) substr($decryptStr, 8);
    }

    /**
     * @param \Swoole\Server $server
     * @param int $taskId
     * @param int $fromId
     * @param array $data
     * @return string 空字符串:执行成功 非空:执行失败
     */
    private function handleTaskHttpTrait(Server $server, int $taskId, int $fromId, array &$data): string
    {
        $taskCommand = Tool::getArrayVal($data['params'], 'task_command', '');
        switch ($taskCommand) {
            case Project::TASK_TYPE_REFRESH_TOKEN_EXPIRE:
                self::$_syServer->set(self::$_serverToken, [
                    'token_etime' => $this->getTokenExpireTime(),
                ]);
                break;
            default:
                $result = new Result();
                $result->setData([
                    'result' => 'fail',
                ]);
                return $result->getJson();
        }

        return '';
    }

    private function handleReqExceptionByProject(\Throwable $e): Result
    {
    }

    /**
     * 初始化语言类型
     */
    private function initLanguageType()
    {
        if (isset($_COOKIE[Project::DATA_KEY_LANGUAGE_TAG])) {
            $langType = $_COOKIE[Project::DATA_KEY_LANGUAGE_TAG];
        } elseif ($_GET[Project::DATA_KEY_LANGUAGE_TAG]) {
            $langType = $_GET[Project::DATA_KEY_LANGUAGE_TAG];
        } else {
            $langType = $_POST[Project::DATA_KEY_LANGUAGE_TAG] ?? '';
        }
        if (isset(Project::$totalLangType[$langType])) {
            $_POST[Project::DATA_KEY_LANGUAGE_TAG] = $langType;
        } else {
            $_POST[Project::DATA_KEY_LANGUAGE_TAG] = Project::LANG_TYPE_DEFAULT;
        }
    }
}
