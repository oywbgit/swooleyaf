<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/7/2 0002
 * Time: 10:47
 */
namespace ProjectCache;

use SyConstant\ErrorCode;
use SyConstant\Project;
use DesignPatterns\Factories\CacheSimpleFactory;
use SyException\Common\CheckException;
use Factories\SyTaskMysqlFactory;
use SyTool\Tool;
use SyTrait\SimpleTrait;

class WebHook
{
    use SimpleTrait;

    public static function getCacheQueueKey()
    {
        return Project::REDIS_PREFIX_CODE_WEBHOOK_QUEUE . 'list';
    }

    public static function getCacheStatusKey(string $tag)
    {
        return Project::REDIS_PREFIX_CODE_WEBHOOK_STATUS . $tag;
    }

    private static function getCacheCommandKey(string $tag)
    {
        return Project::REDIS_PREFIX_CODE_WEBHOOK_COMMAND . $tag;
    }

    public static function getCacheInfoKey(string $tag)
    {
        return Project::REDIS_PREFIX_CODE_WEBHOOK_INFO . $tag;
    }

    public static function getCommandList(string $tag, string $event, string $msgPrefix)
    {
        $cacheKey = self::getCacheCommandKey($tag);
        $cacheData = CacheSimpleFactory::getRedisInstance()->hGetAll($cacheKey);
        if (empty($cacheData)) {
            $webHook = SyTaskMysqlFactory::WebhookEntity();
            $ormResult1 = $webHook->getContainer()->getModel()->getOrmDbTable();
            $ormResult1->where('`tag`=?', [$tag]);
            $webHookInfo = $webHook->getContainer()->getModel()->findOne($ormResult1);
            $commands = empty($webHookInfo['exec_commands']) ? [] : Tool::jsonDecode($webHookInfo['exec_commands']);
            foreach ($commands as $key => $eCommandData) {
                $cacheData[$key] = Tool::jsonEncode($eCommandData, JSON_UNESCAPED_UNICODE);
            }
            $cacheData['unique_key'] = $cacheKey;
            CacheSimpleFactory::getRedisInstance()->hMset($cacheKey, $cacheData);
            CacheSimpleFactory::getRedisInstance()->expire($cacheKey, 86400);
        }

        if (isset($cacheData['unique_key']) && ($cacheData['unique_key'] == $cacheKey)) {
            if (isset($cacheData[$event])) {
                $commandData = Tool::jsonDecode($cacheData[$event]);
                $commandTag = isset($commandData['list'][$msgPrefix]) ? $msgPrefix : $commandData['default'];
                return $commandData['list'][$commandTag] ?? [];
            } else {
                return [];
            }
        } else {
            throw new CheckException('获取命令列表缓存出错', ErrorCode::COMMON_SERVER_ERROR);
        }
    }

    public static function clearCommandList(string $tag)
    {
        $cacheKey = self::getCacheCommandKey($tag);
        return CacheSimpleFactory::getRedisInstance()->del($cacheKey);
    }

    public static function getHookInfo(string $tag)
    {
        $cacheKey = self::getCacheInfoKey($tag);
        $cacheData = CacheSimpleFactory::getRedisInstance()->hGetAll($cacheKey);
        if (empty($cacheData)) {
            $cacheData['unique_key'] = $cacheKey;
            $webHook = SyTaskMysqlFactory::WebhookEntity();
            $ormResult1 = $webHook->getContainer()->getModel()->getOrmDbTable();
            $ormResult1->where('`tag`=?', [$tag]);
            $webHookInfo = $webHook->getContainer()->getModel()->findOne($ormResult1);
            if (!empty($webHookInfo)) {
                $cacheData['token'] = trim($webHookInfo['token']);
                $cacheData['code_url'] = trim($webHookInfo['code_url']);
                $cacheData['code_ref'] = trim($webHookInfo['code_ref']);
            }
            CacheSimpleFactory::getRedisInstance()->hMset($cacheKey, $cacheData);
            CacheSimpleFactory::getRedisInstance()->expire($cacheKey, 86400);
        }

        if (isset($cacheData['unique_key']) && ($cacheData['unique_key'] == $cacheKey)) {
            unset($cacheData['unique_key']);
            return $cacheData;
        } else {
            throw new CheckException('获取信息缓存出错', ErrorCode::COMMON_SERVER_ERROR);
        }
    }

    public static function clearHookInfo(string $tag)
    {
        $cacheKey = self::getCacheInfoKey($tag);
        return CacheSimpleFactory::getRedisInstance()->del($cacheKey);
    }
}
