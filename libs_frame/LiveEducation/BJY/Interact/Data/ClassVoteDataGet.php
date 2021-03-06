<?php
/**
 * 获取班级投票数据
 * User: 姜伟
 * Date: 2020/3/31 0031
 * Time: 15:42
 */
namespace LiveEducation\BJY\Interact\Data;

use LiveEducation\BaseBJY;
use LiveEducation\UtilBJY;
use SyConstant\ErrorCode;
use SyException\LiveEducation\BJYException;

/**
 * Class ClassVoteDataGet
 * @package LiveEducation\BJY\Interact\Data
 */
class ClassVoteDataGet extends BaseBJY
{
    /**
     * 房间ID
     * @var int
     */
    private $room_id = 0;
    /**
     * 题型 不传:所有 0:投票 1:抢答
     * @var int
     */
    private $type = 0;
    /**
     * 题目类型 0:选择题 1:判断题 2:问答题
     * @var int
     */
    private $sub_type = 0;
    /**
     * 日期
     * @var string
     */
    private $date = '';
    /**
     * 页数
     * @var int
     */
    private $page = 0;
    /**
     * 每页条数
     * @var int
     */
    private $page_size = 0;

    public function __construct(string $partnerId)
    {
        parent::__construct($partnerId);
        $this->serviceDomain = 'http://hudong.baijiayun.com';
        $this->serviceUri = '/openapi/interact_data/getClassVoteData';
        $this->reqData['page'] = 1;
        $this->reqData['page_size'] = 20;
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
     * @param int $type
     * @throws \SyException\LiveEducation\BJYException
     */
    public function setType(int $type)
    {
        if (in_array($type, [0, 1])) {
            $this->reqData['type'] = $type;
        } else {
            throw new BJYException('题型不合法', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
        }
    }

    /**
     * @param int $subType
     * @throws \SyException\LiveEducation\BJYException
     */
    public function setSubType(int $subType)
    {
        if (in_array($subType, [0, 1, 2])) {
            $this->reqData['sub_type'] = $subType;
        } else {
            throw new BJYException('题目类型不合法', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
        }
    }

    /**
     * @param int $dateTime
     * @throws \SyException\LiveEducation\BJYException
     */
    public function setDate(int $dateTime)
    {
        if ($dateTime > 1262275200) {
            $this->reqData['date'] = date('Y-m-d', $dateTime);
        } else {
            throw new BJYException('日期不合法', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
        }
    }

    /**
     * @param int $page
     * @throws \SyException\LiveEducation\BJYException
     */
    public function setPage(int $page)
    {
        if ($page > 0) {
            $this->reqData['page'] = $page;
        } else {
            throw new BJYException('页数不合法', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
        }
    }

    /**
     * @param int $pageSize
     * @throws \SyException\LiveEducation\BJYException
     */
    public function setPageSize(int $pageSize)
    {
        if ($pageSize > 0) {
            $this->reqData['page_size'] = $pageSize;
        } else {
            throw new BJYException('每页条数不合法', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
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
