<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2019/7/17 0017
 * Time: 14:09
 */
namespace SyIot;

use Constant\ErrorCode;
use SyException\Iot\BaiDuIotException;

abstract class IotBaseBaiDu extends IotBase
{
    const REQ_METHOD_GET = 'GET'; //请求方式-
    const REQ_METHOD_POST = 'POST'; //请求方式-POST
    const REQ_METHOD_PUT = 'PUT'; //请求方式-PUT
    const REQ_METHOD_DELETE = 'DELETE'; //请求方式-DELETE
    const REQ_METHOD_HEAD = 'HEAD'; //请求方式-HEAD

    /**
     * 服务协议
     * @var string
     */
    protected $serviceProtocol = '';
    /**
     * 服务域名
     * @var string
     */
    protected $serviceDomain = '';
    /**
     * 服务uri
     * @var string
     */
    protected $serviceUri = '';
    /**
     * 请求方式
     * @var string
     */
    protected $reqMethod = '';
    /**
     * 请求头
     * @var array
     */
    protected $reqHeader = [];

    public function __construct()
    {
        parent::__construct();
        $this->serviceProtocol = 'https';
        $this->serviceDomain = 'iot.gz.baidubce.com';
        $this->reqHeader = [
            'Host' => $this->serviceDomain,
            'Content-Type' => 'application/json; charset=utf-8',
        ];
    }

    private function __clone()
    {
    }

    /**
     * @param string $serviceProtocol
     * @throws \SyException\Iot\BaiDuIotException
     */
    public function setServiceProtocol(string $serviceProtocol)
    {
        if (in_array($serviceProtocol, ['http', 'https'])) {
            $this->serviceProtocol = $serviceProtocol;
        } else {
            throw new BaiDuIotException('服务协议不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    /**
     * @param string $serviceDomain
     * @throws \SyException\Iot\BaiDuIotException
     */
    public function setServiceDomain(string $serviceDomain)
    {
        if (in_array($serviceDomain, ['iot.bj.baidubce.com', 'iot.gz.baidubce.com'])) {
            $this->serviceDomain = $serviceDomain;
            $this->reqHeader['Host'] = $serviceDomain;
        } else {
            throw new BaiDuIotException('服务域名不合法', ErrorCode::IOT_PARAM_ERROR);
        }
    }

    protected function getContent() : array
    {
        if (!isset($this->reqMethod{0})) {
            throw new BaiDuIotException('请求方式不能为空', ErrorCode::IOT_PARAM_ERROR);
        }
        if (!isset($this->serviceUri{0})) {
            throw new BaiDuIotException('服务uri不能为空', ErrorCode::IOT_PARAM_ERROR);
        } elseif ($this->serviceUri{0} != '/') {
            throw new BaiDuIotException('服务uri不合法', ErrorCode::IOT_PARAM_ERROR);
        }

        $signData = [
            'req_method' => $this->reqMethod,
            'req_uri' => $this->serviceUri,
            'req_params' => [],
            'req_headers' => [
                'host',
            ],
        ];
        $url = $this->serviceProtocol . '://' . $this->serviceDomain . $this->serviceUri;
        if (in_array($this->reqMethod, [self::REQ_METHOD_GET, self::REQ_METHOD_DELETE]) && !empty($this->reqData)) {
            $url .= '?' . http_build_query($this->reqData);
            $signData['req_params'] = $this->reqData;
        }
        $this->reqHeader['Authorization'] = IotUtilBaiDu::createSign($signData);

        $this->curlConfigs[CURLOPT_URL] = $url;
        $this->curlConfigs[CURLOPT_RETURNTRANSFER] = true;
        $this->curlConfigs[CURLOPT_SSL_VERIFYPEER] = false;
        $this->curlConfigs[CURLOPT_SSL_VERIFYHOST] = false;
        $this->curlConfigs[CURLOPT_HEADER] = false;
        if (!isset($this->curlConfigs[CURLOPT_TIMEOUT_MS])) {
            $this->curlConfigs[CURLOPT_TIMEOUT_MS] = 2000;
        }
        $reqHeaderArr = [];
        foreach ($this->reqHeader as $headerKey => $headerVal) {
            $reqHeaderArr[] = $headerKey . ': ' . $headerVal;
        }
        $this->curlConfigs[CURLOPT_HTTPHEADER] = $reqHeaderArr;
        return $this->curlConfigs;
    }
}
