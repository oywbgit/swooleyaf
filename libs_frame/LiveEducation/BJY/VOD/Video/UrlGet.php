<?php
/**
 * 获取转码后视频/音频地址
 * User: 姜伟
 * Date: 2020/4/1 0001
 * Time: 18:54
 */
namespace LiveEducation\BJY\VOD\Video;

use LiveEducation\BaseBJY;
use LiveEducation\UtilBJY;
use SyConstant\ErrorCode;
use SyException\LiveEducation\BJYException;

/**
 * Class UrlGet
 * @package LiveEducation\BJY\VOD\Video
 */
class UrlGet extends BaseBJY
{
    /**
     * 视频ID
     * @var int
     */
    private $video_id = 0;
    /**
     * 视频格式,默认是mp4 可选值有：mp4/m3u8/flv/encrypt
     * @var array
     */
    private $format = '';
    /**
     * 过期时间,单位为秒
     * @var int
     */
    private $expires_in = 0;

    public function __construct(string $partnerId)
    {
        parent::__construct($partnerId);
        $this->serviceUri = '/openapi/video/getUrl';
        $this->reqData['format'] = 'mp4';
        $this->reqData['expires_in'] = 43200;
    }

    private function __clone()
    {
    }

    /**
     * @param int $videoId
     * @throws \SyException\LiveEducation\BJYException
     */
    public function setVideoId(int $videoId)
    {
        if ($videoId > 0) {
            $this->reqData['video_id'] = $videoId;
        } else {
            throw new BJYException('视频ID不合法', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
        }
    }

    /**
     * @param array $format
     * @throws \SyException\LiveEducation\BJYException
     */
    public function setFormat(array $format)
    {
        if (in_array($format, ['mp4', 'm3u8', 'flv', 'encrypt'])) {
            $this->reqData['format'] = $format;
        } else {
            throw new BJYException('视频格式不合法', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
        }
    }

    /**
     * @param int $expiresIn
     * @throws \SyException\LiveEducation\BJYException
     */
    public function setExpiresIn(int $expiresIn)
    {
        if ($expiresIn > 0) {
            $this->reqData['expires_in'] = $expiresIn;
        } else {
            throw new BJYException('过期时间不合法', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
        }
    }

    public function getDetail() : array
    {
        if (!isset($this->reqData['video_id'])) {
            throw new BJYException('视频ID不能为空', ErrorCode::LIVE_EDUCATION_PARAM_ERROR);
        }
        UtilBJY::createSign($this->partnerId, $this->reqData);

        return $this->getContent();
    }
}
