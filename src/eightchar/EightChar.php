<?php

namespace com\tyme\eightchar;


use com\tyme\AbstractCulture;
use com\tyme\culture\Duty;
use com\tyme\sixtycycle\EarthBranch;
use com\tyme\sixtycycle\HeavenStem;
use com\tyme\sixtycycle\SixtyCycle;
use com\tyme\solar\SolarTerm;
use com\tyme\solar\SolarTime;

/**
 * 八字
 * @author 6tail
 * @package com\tyme\eightchar
 */
class EightChar extends AbstractCulture
{
    /**
     * @var SixtyCycle 年柱
     */
    protected SixtyCycle $year;

    /**
     * @var SixtyCycle 月柱
     */
    protected SixtyCycle $month;

    /**
     * @var SixtyCycle 日柱
     */
    protected SixtyCycle $day;

    /**
     * @var SixtyCycle 时柱
     */
    protected SixtyCycle $hour;

    function __construct(SixtyCycle|string $year, SixtyCycle|string $month, SixtyCycle|string $day, SixtyCycle|string $hour)
    {
        $this->year = $year instanceof SixtyCycle ? $year : SixtyCycle::fromName($year);
        $this->month = $month instanceof SixtyCycle ? $month : SixtyCycle::fromName($month);
        $this->day = $day instanceof SixtyCycle ? $day : SixtyCycle::fromName($day);
        $this->hour = $hour instanceof SixtyCycle ? $hour : SixtyCycle::fromName($hour);
    }

    /**
     * 年柱
     *
     * @return SixtyCycle 年柱
     */
    function getYear(): SixtyCycle
    {
        return $this->year;
    }

    /**
     * 月柱
     *
     * @return SixtyCycle 月柱
     */
    function getMonth(): SixtyCycle
    {
        return $this->month;
    }

    /**
     * 日柱
     *
     * @return SixtyCycle 日柱
     */
    function getDay(): SixtyCycle
    {
        return $this->day;
    }

    /**
     * 时柱
     *
     * @return SixtyCycle 时柱
     */
    function getHour(): SixtyCycle
    {
        return $this->hour;
    }

    /**
     * 胎元
     *
     * @return SixtyCycle 胎元
     */
    function getFetalOrigin(): SixtyCycle
    {
        return SixtyCycle::fromName(sprintf('%s%s', $this->month->getHeavenStem()->next(1)->getName(), $this->month->getEarthBranch()->next(3)->getName()));
    }

    /**
     * 胎息
     *
     * @return SixtyCycle 胎息
     */
    function getFetalBreath(): SixtyCycle
    {
        return SixtyCycle::fromName(sprintf('%s%s', $this->day->getHeavenStem()->next(5)->getName(), EarthBranch::fromIndex(13 - $this->day->getEarthBranch()->getIndex())->getName()));
    }

    /**
     * 命宫
     *
     * @return SixtyCycle 命宫
     */
    function getOwnSign(): SixtyCycle
    {
        $offset = $this->month->getEarthBranch()->next(-1)->getIndex() + $this->hour->getEarthBranch()->next(-1)->getIndex();
        $offset = ($offset >= 14 ? 26 : 14) - $offset;
        $offset -= 1;
        return SixtyCycle::fromName(sprintf('%s%s', HeavenStem::fromIndex(($this->year->getHeavenStem()->getIndex() + 1) * 2 + $offset)->getName(), EarthBranch::fromIndex(2 + $offset)->getName()));
    }

    /**
     * 身宫
     *
     * @return SixtyCycle 身宫
     */
    function getBodySign(): SixtyCycle
    {
        $offset = $this->month->getEarthBranch()->getIndex() + $this->hour->getEarthBranch()->getIndex();
        $offset %= 12;
        $offset -= 1;
        return SixtyCycle::fromName(sprintf('%s%s', HeavenStem::fromIndex(($this->year->getHeavenStem()->getIndex() + 1) * 2 + $offset)->getName(), EarthBranch::fromIndex(2 + $offset)->getName()));
    }

    /**
     * 建除十二值神
     *
     * @return Duty 建除十二值神
     */
    function getDuty(): Duty
    {
        return Duty::fromIndex($this->day->getEarthBranch()->getIndex() - $this->month->getEarthBranch()->getIndex());
    }

    function getName(): string
    {
        return sprintf('%s %s %s %s', $this->year, $this->month, $this->day, $this->hour);
    }

    /**
     * 阳历时刻列表
     * @param int $startYear 开始年(含)，支持1-9999年
     * @param int $endYear 结束年(含)，支持1-9999年
     * @return SolarTime[] 阳历时刻列表
     */
    function getSolarTimes(int $startYear, int $endYear): array
    {
        $l = array();
        // 月地支距寅月的偏移值
        $m = $this->month->getEarthBranch()->next(-2)->getIndex();
        // 月天干要一致
        if (!HeavenStem::fromIndex(($this->year->getHeavenStem()->getIndex() + 1) * 2 + $m)->equals($this->month->getHeavenStem())) {
            return $l;
        }
        // 1年的立春是辛酉，序号57
        $y = $this->year->next(-57)->getIndex() + 1;
        // 节令偏移值
        $m *= 2;
        // 时辰地支转时刻，子时按零点算
        $h = $this->hour->getEarthBranch()->getIndex() * 2;
        $baseYear = $startYear - 1;
        while ($y <= $endYear) {
            if ($y >= $baseYear) {
                // 立春为寅月的开始
                $term = SolarTerm::fromIndex($y, 3);
                // 节令推移，年干支和月干支就都匹配上了
                if ($m > 0) {
                    $term = $term->next($m);
                }
                $solarTime = $term->getJulianDay()->getSolarTime();
                if ($solarTime->getDay()->getMonth()->getYear()->getYear() >= $startYear) {
                    $mi = 0;
                    $s = 0;
                    // 日干支和节令干支的偏移值
                    $solarDay = $solarTime->getDay();
                    $d = $this->day->next(-$solarDay->getLunarDay()->getSixtyCycle()->getIndex())->getIndex();
                    if ($d > 0) {
                        // 从节令推移天数
                        $solarDay = $solarDay->next($d);
                    } else if ($h == $solarTime->getHour()) {
                        // 如果正好是节令当天，且小时和节令的小时数相等的极端情况，把分钟和秒钟带上
                        $mi = $solarTime->getMinute();
                        $s = $solarTime->getSecond();
                    }
                    $solarMonth = $solarDay->getMonth();
                    $time = SolarTime::fromYmdHms($solarMonth->getYear()->getYear(), $solarMonth->getMonth(), $solarDay->getDay(), $h, $mi, $s);
                    // 验证一下
                    if ($time->getLunarHour()->getEightChar()->equals($this)) {
                        $l[] = $time;
                    }
                }
            }
            $y += 60;
        }
        return $l;
    }

}
