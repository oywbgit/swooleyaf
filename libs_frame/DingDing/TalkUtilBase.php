<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/1/26 0026
 * Time: 10:47
 */
namespace DingDing;

use Constant\ErrorCode;
use DesignPatterns\Singletons\DingTalkConfigSingleton;
use Exception\DingDing\TalkException;
use Tool\Tool;

abstract class TalkUtilBase {
    const OPTION_TYPE_CROP = 'crop'; //操作类型-企业
    const OPTION_TYPE_PROVIDER = 'provider'; //操作类型-服务商

    /**
     * 发送post请求
     * @param array $curlConfig
     * @return mixed
     * @throws \Exception\DingDing\TalkException
     */
    public static function sendPostReq(array $curlConfig) {
        $curlConfig[CURLOPT_POST] = true;
        $curlConfig[CURLOPT_RETURNTRANSFER] = true;
        if(!isset($curlConfig[CURLOPT_TIMEOUT_MS])){
            $curlConfig[CURLOPT_TIMEOUT_MS] = 3000;
        }
        if(!isset($curlConfig[CURLOPT_HEADER])){
            $curlConfig[CURLOPT_HEADER] = false;
        }
        $sendRes = Tool::sendCurlReq($curlConfig);
        if ($sendRes['res_no'] == 0) {
            return $sendRes['res_content'];
        } else {
            throw new TalkException('curl出错，错误码=' . $sendRes['res_no'], ErrorCode::DING_TALK_POST_ERROR);
        }
    }

    /**
     * 发送get请求
     * @param array $curlConfig
     * @return mixed
     * @throws \Exception\DingDing\TalkException
     */
    public static function sendGetReq(array $curlConfig) {
        $curlConfig[CURLOPT_SSL_VERIFYPEER] = false;
        $curlConfig[CURLOPT_SSL_VERIFYHOST] = false;
        $curlConfig[CURLOPT_HEADER] = false;
        $curlConfig[CURLOPT_RETURNTRANSFER] = true;
        if(!isset($curlConfig[CURLOPT_TIMEOUT_MS])){
            $curlConfig[CURLOPT_TIMEOUT_MS] = 2000;
        }
        $sendRes = Tool::sendCurlReq($curlConfig);
        if ($sendRes['res_no'] == 0) {
            return $sendRes['res_content'];
        } else {
            throw new TalkException('curl出错，错误码=' . $sendRes['res_no'], ErrorCode::DING_TALK_GET_ERROR);
        }
    }

    /**
     * 校验回调签名
     * @param array $data 待校验数据,数据格式如下:
     *   token: string 服务商或企业应用的消息加解密token
     *   timestamp: string|int 时间戳
     *   nonce: string 随机字符串
     *   encrypt: string 加密数据
     * @param string $signature 用于比对的签名
     * @return bool
     */
    public static function checkCallbackSign(array $data,string $signature) : bool {
        $saveArr = [$data['token'], (string)$data['timestamp'], $data['nonce'], $data['encrypt']];
        sort($saveArr, SORT_STRING);
        $needStr = implode('', $saveArr);
        $nowSign = sha1($needStr);
        return $nowSign === $signature;
    }

    /**
     * 解密数据
     * @param string $encryptMsg
     * @param string $optionType 操作类型
     * @param string $corpId 企业ID
     * @param string $agentTag 应用标志
     * @return string
     * @throws \Exception\DingDing\TalkException
     */
    public static function decryptMsg(string $encryptMsg,string $optionType,string $corpId='',string $agentTag='') : string {
        if($optionType == self::OPTION_TYPE_PROVIDER){
            $providerConfig = DingTalkConfigSingleton::getInstance()->getCorpProviderConfig();
            $key = base64_decode($providerConfig->getAesKey() . '=');
            $iv = substr($key, 0, 16);
            $checkKey = $providerConfig->getSuiteKey();
        } else {
            $agentInfo = DingTalkConfigSingleton::getInstance()->getCorpConfig($corpId)->getAgentInfo($agentTag);
            $key = base64_decode($agentInfo['aes_key'] . '=');
            $iv = substr($key, 0, 16);
            $checkKey = $corpId;
        }
        $error = '';
        $msg = '';
        $decryptMsg = openssl_decrypt(base64_decode($encryptMsg), 'aes-256-cbc', substr($key, 0, 32), OPENSSL_ZERO_PADDING, $iv);
        $decodeMsg = Tool::pkcs7Decode($decryptMsg);
        if (strlen($decodeMsg) >= 16) {
            $msgContent = substr($decodeMsg, 16);
            $lengthList = unpack("N", substr($msgContent, 0, 4));
            $msg = substr($msgContent, 4, $lengthList[1]);
            $receiveId = substr($msgContent, ($lengthList[1] + 4));
            if($receiveId != $checkKey){
                $error = $optionType == self::OPTION_TYPE_PROVIDER ? '套件标识不匹配' : '企业ID不匹配';
            }
        } else {
            $error = '解密失败';
        }
        if (strlen($error) > 0) {
            throw new TalkException($error, ErrorCode::DING_TALK_PARAM_ERROR);
        }

        return $msg;
    }

    /**
     * 加密数据
     * @param string $replyMsg
     * @param string $optionType 操作类型
     * @param string $corpId 企业ID
     * @param string $agentTag 应用标志
     * @return string
     */
    public static function encryptMsg(string $replyMsg,string $optionType,string $corpId='',string $agentTag='') : string {
        if($optionType == self::OPTION_TYPE_PROVIDER){
            $providerConfig = DingTalkConfigSingleton::getInstance()->getCorpProviderConfig();
            $key = base64_decode($providerConfig->getAesKey() . '=');
            $iv = substr($key, 0, 16);
            $checkKey = $providerConfig->getSuiteKey();
        } else {
            $agentInfo = DingTalkConfigSingleton::getInstance()->getCorpConfig($corpId)->getAgentInfo($agentTag);
            $key = base64_decode($agentInfo['aes_key'] . '=');
            $iv = substr($key, 0, 16);
            $checkKey = $corpId;
        }

        $content1 = Tool::createNonceStr(16) . pack("N", strlen($replyMsg)) . $replyMsg . $checkKey;
        $content2 = Tool::pkcs7Encode($content1);
        $content3 = openssl_encrypt($content2, 'aes-256-cbc', substr($key, 0, 32), OPENSSL_ZERO_PADDING, $iv);
        return base64_encode($content3);
    }
}