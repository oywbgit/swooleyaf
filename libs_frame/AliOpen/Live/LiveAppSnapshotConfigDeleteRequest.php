<?php
namespace AliOpen\Live;

use AliOpen\Core\RpcAcsRequest;

class LiveAppSnapshotConfigDeleteRequest extends RpcAcsRequest
{
    private $appName;
    private $securityToken;
    private $domainName;
    private $ownerId;

    public function __construct()
    {
        parent::__construct('live', '2016-11-01', 'DeleteLiveAppSnapshotConfig', 'live', 'openAPI');
        $this->setMethod('POST');
    }

    public function getAppName()
    {
        return $this->appName;
    }

    public function setAppName($appName)
    {
        $this->appName = $appName;
        $this->queryParameters['AppName'] = $appName;
    }

    public function getSecurityToken()
    {
        return $this->securityToken;
    }

    public function setSecurityToken($securityToken)
    {
        $this->securityToken = $securityToken;
        $this->queryParameters['SecurityToken'] = $securityToken;
    }

    public function getDomainName()
    {
        return $this->domainName;
    }

    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
        $this->queryParameters['DomainName'] = $domainName;
    }

    public function getOwnerId()
    {
        return $this->ownerId;
    }

    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
        $this->queryParameters['OwnerId'] = $ownerId;
    }
}
