<?php
/**
 * 教室下课
 * User: 姜伟
 * Date: 2020/3/30 0030
 * Time: 10:44
 */
namespace LiveEducation\BJY\Live\LargeClass\Live;

use LiveEducation\BaseBJY;
use LiveEducation\UtilBJY;
use SyConstant\ErrorCode;
use SyException\LiveEducation\BJYException;

/**
 * Class ClassStop
 * @package LiveEducation\BJY\Live\LargeClass\Live
 */
class ClassStop extends BaseBJY
{
    /**
     * 房间ID
     * @var int
     */
    private $room_id = 0;
    /**
     * 踢人标识 0:不踢人 1:踢人
     * @var int
     */
    private $is_kick_out_all = 0;

    public function __construct(string $partnerId)
    {
        parent::__construct($partnerId);
        $this->serviceUri = '/openapi/live/stopClass';
        $this->reqData['is_kick_out_all'] = 0;
    }

    private function __clone()
    {
    }

    /**
     * @param int $roomId
     * @throws \SyException\LiveEducation\BJYException
     */
    public function setRoomId(int $roomId)
    {
        if ($roomId > 0) {
            $this->reqData['room_id'] = $roomId;
        } else {
            throw new BJYException('房间ID不合法', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
        }
    }

    /**
     * @param int $kickOut
     * @throws \SyException\LiveEducation\BJYException
     */
    public function SetKickOut(int $kickOut)
    {
        if (in_array($kickOut, [0, 1])) {
            $this->reqData['is_kick_out_all'] = $kickOut;
        } else {
            throw new BJYException('踢人标识不合法', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
        }
    }

    public function getDetail() : array
    {
        if (!isset($this->reqData['room_id'])) {
            throw new BJYException('房间ID不能为空', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
        }
        UtilBJY::createSign($this->partnerId, $this->reqData);

        return $this->getContent();
    }
}
