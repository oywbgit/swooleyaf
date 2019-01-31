<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 19-1-28
 * Time: 上午11:15
 */
namespace DingDing\Corp\Sns;

use Constant\ErrorCode;
use DesignPatterns\Singletons\DingTalkConfigSingleton;
use DingDing\TalkBaseCorp;
use DingDing\TalkTraitCorp;
use DingDing\TalkUtilBase;
use Exception\DingDing\TalkException;
use Tool\Tool;

/**
 * 通过临时授权码获取授权用户的个人信息
 * @package DingDing\Corp\Sns
 */
class UserInfoGetByCode extends TalkBaseCorp {
    use TalkTraitCorp;

    /**
     * 临时授权码
     * @var string
     */
    private $tmp_auth_code = '';

    public function __construct(string $corpId,string $agentTag){
        parent::__construct();
        $this->_corpId = $corpId;
        $this->_agentTag = $agentTag;
    }

    private function __clone(){
    }

    /**
     * @param string $tmpAuthCode
     * @throws \Exception\DingDing\TalkException
     */
    public function setTmpAuthCode(string $tmpAuthCode){
        if(ctype_alnum($tmpAuthCode)){
            $this->reqData['tmp_auth_code'] = $tmpAuthCode;
        } else {
            throw new TalkException('临时授权码不合法', ErrorCode::DING_TALK_PARAM_ERROR);
        }
    }

    public function getDetail() : array {
        if(!isset($this->reqData['tmp_auth_code'])){
            throw new TalkException('临时授权码不能为空', ErrorCode::DING_TALK_PARAM_ERROR);
        }

        $timestamp = (string)Tool::getNowTime();
        if ($this->_tokenType == TalkBaseCorp::ACCESS_TOKEN_TYPE_CORP) {
            $corpConfig = DingTalkConfigSingleton::getInstance()->getCorpConfig($this->_corpId);
            $accessKey = $corpConfig->getLoginAppId();
            $signSecret = $corpConfig->getLoginAppSecret();
        } else {
            $providerConfig = DingTalkConfigSingleton::getInstance()->getCorpProviderConfig();
            $accessKey = $providerConfig->getLoginAppId();
            $signSecret = $providerConfig->getLoginAppSecret();
        }

        $resArr = [
            'code' => 0,
        ];

        $this->curlConfigs[CURLOPT_URL] = $this->serviceDomain . '/sns/getuserinfo_bycode?' . http_build_query([
            'signature' => TalkUtilBase::createApiSign($timestamp, $signSecret),
            'timestamp' => $timestamp,
            'accessKey' => $accessKey,
        ]);
        $this->curlConfigs[CURLOPT_POSTFIELDS] = Tool::jsonEncode($this->reqData, JSON_UNESCAPED_UNICODE);
        $sendRes = TalkUtilBase::sendPostReq($this->curlConfigs);
        $sendData = Tool::jsonDecode($sendRes);
        if($sendData['errcode'] == 0){
            $resArr['data'] = $sendData;
        } else {
            $resArr['code'] = ErrorCode::DING_TALK_POST_ERROR;
            $resArr['message'] = $sendData['errmsg'];
        }

        return $resArr;
    }
}