<?php
/**
 * Created by PhpStorm.
 * User: 姜伟
 * Date: 2020/3/28 0028
 * Time: 13:19
 */
namespace LiveEducation\BJY\Live\SmallClass;

use LiveEducation\BaseBJY;

/**
 * Class Demo
 * @package LiveEducation\BJY\Live\SmallClass
 */
class Demo extends BaseBJY
{
    public function __construct(string $partnerId)
    {
        parent::__construct($partnerId);
        $this->serviceUri = '';
    }

    private function __clone()
    {
    }

    public function getDetail() : array
    {
        return $this->getContent();
    }
}
