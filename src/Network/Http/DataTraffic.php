<?php
/**
 * Created by PhpStorm.
 * User: chenfan
 * Date: 16/3/8
 * Time: 下午8:02
 */

namespace Zan\Framework\Network\Http;


class DataTraffic
{
    /**
     * @var session
     */
    public $kdtId = 0;
    public $userId = 0;
    public $buyerId = 0;
    public $buyerPhone = '';
    public $nobody = '';
    public $fansId = 0;
    public $isFans = 2;
    public $fansNickname = '';
    public $fansType = 0;
    public $fansToken = '';
    public $fansPicture = '';
    public $youzanFansNickname = '';
    public $youzanFansPicture = '';
    public $youzanUserId = '';
    public $noUserLogin = true;
    public $mpId = 0;
    /**
     * @var config
     */
    public $runMode = '';
    public $debug = false;
    public $onlineDebug = false;
    public $jsCompress = false;
    public $cssCompress = false;
    public $useJsCdn = false;
    public $useCssCdn = false;
    public $checkBrowser = false;
    public $messageReport = false;
    public $hideWxNav = false;
    public $qnPublic = '';
    public $qnPrivate = '';
    public $pageSize = 0;
    public $screenDemo = false;
    public $urls = [];
    /**
     * @var env
     */
    public $project = '';
    public $platform = '';
    public $isMobile = false;
    public $authorize = '';
    public $platformVersion = '';
    public $mobileSystem = '';
    public $shareTitle = '';
    public $shareDesc = '';
    public $shareCover = '';
    /**
     * @var query
     */
    public $queryPath = '';
    public $realQueryPath = '';
    public $module = '';
    public $controller = '';
    public $action = '';
    public $fullAction = '';
    public $method = '';
    public $isShopDomain = false;
    /**
     * @var other
     */
    public $source = '';
    public $track = '';
    public $spmLogType = '';
    public $spmLogId = '';


    public function setKdtId($kdtId)
    {
        $this->kdtId = (int)$kdtId;
    }

    public function setRunMode($runMode)
    {
        $this->runMode = trim($runMode);
    }

    public function setPlatform($platform)
    {
        $this->platform = trim($platform);
    }

    public function setQueryPath($queryPath)
    {
        $this->queryPath = trim($queryPath);
    }

    public function setUrls(array $urls)
    {
        $this->urls = array_filter($urls);
    }
}