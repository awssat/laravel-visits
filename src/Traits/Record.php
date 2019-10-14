<?php

namespace Awssat\Visits\Traits;

use Spatie\Referer\Referer;

trait Record
{
    /**
     * @param $inc
     */
    protected function recordCountry($inc)
    {
        $this->connection->increment($this->keys->visits."_countries:{$this->keys->id}", $inc, $this->getVisitorCountry());
    }

    /**
     * @param $inc
     */
    protected function recordRefer($inc)
    {
        $this->connection->increment($this->keys->visits."_referers:{$this->keys->id}", $inc, $this->getVisitorReferer());
    }

    /**
     * @param $inc
     */
    protected function recordOperatingSystem($inc)
    {
        $this->connection->increment($this->keys->visits."_OSes:{$this->keys->id}", $inc, $this->getVisitorOperatingSystem());
    }

    /**
     * @param $inc
     */
    protected function recordLanguage($inc)
    {
        $this->connection->increment($this->keys->visits."_languages:{$this->keys->id}", $inc, $this->getVisitorLanguage());
    }

    /**
     * @param $inc
     */
    protected function recordPeriods($inc)
    {
        foreach ($this->periods as $period) {
            $periodKey = $this->keys->period($period);

            $this->connection->increment($periodKey, $inc, $this->keys->id);
            $this->connection->increment($periodKey.'_total', $inc);
        }
    }

    /**
     *  Gets visitor country code
     * @return mixed|string
     */
    public function getVisitorCountry()
    {
        return strtolower(geoip()->getLocation()->iso_code);
    }

    /**
     *  Gets visitor operating system
     * @return mixed|string
     */
    public function getVisitorOperatingSystem()
    {
        $osArray = [
        '/windows|win32|win16|win95/i' => 'Windows',
        '/iphone/i' => 'iPhone',
        '/ipad/i' => 'iPad',
        '/macintosh|mac os x|mac_powerpc/i' => 'MacOS',
        '/(?=.*mobile)android/i' => 'AndroidMobile',
        '/(?!.*mobile)android/i' => 'AndroidTablet',
        '/android/i' => 'Android',
        '/blackberry/i' => 'BlackBerry',
        '/linux/i' => 'Linux',
        ];

        foreach ($osArray as $regex => $value) {
            if (preg_match($regex, request()->server('HTTP_USER_AGENT') ?? '')) {
                return $value;
            }
        }

        return 'unknown';
    }

    /**
     *  Gets visitor language
     * @return mixed|string
     */
    public function getVisitorLanguage()
    {
        $language = request()->getPreferredLanguage();
        if (false !== $position = strpos($language, '_')) {
            $language = substr($language, 0, $position);
        }
        return $language;
    }

    /**
     *  Gets visitor referer
     * @return mixed|string
     */
    public function getVisitorReferer()
    {
        return app(Referer::class)->get();
    }
}
