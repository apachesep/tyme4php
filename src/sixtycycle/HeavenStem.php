<?php

namespace com\tyme\sixtycycle;


use com\tyme\culture\Direction;
use com\tyme\culture\Element;
use com\tyme\culture\pengzu\PengZuHeavenStem;
use com\tyme\culture\star\ten\TenStar;
use com\tyme\culture\Terrain;
use com\tyme\enums\YinYang;
use com\tyme\LoopTyme;

/**
 * 天干
 * @author 6tail
 * @package com\tyme\sixtycycle
 */
class HeavenStem extends LoopTyme
{
    static array $NAMES = ['甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'];

    protected function __construct(int $index = null, string $name = null)
    {
        if ($index !== null) {
            parent::__construct(self::$NAMES, $index);
        } else if ($name !== null) {
            parent::__construct(self::$NAMES, null, $name);
        }
    }

    static function fromIndex(int $index): static
    {
        return new static($index);
    }

    static function fromName(string $name): static
    {
        return new static(null, $name);
    }

    function next(int $n): static
    {
        return self::fromIndex($this->nextIndex($n));
    }

    /**
     * 五行
     *
     * @return Element 五行
     */
    function getElement(): Element
    {
        return Element::fromIndex(intdiv($this->index, 2));
    }

    /**
     * 阴阳
     *
     * @return YinYang 阴阳
     */
    function getYinYang(): YinYang
    {
        return $this->index % 2 == 0 ? YinYang::YANG : YinYang::YIN;
    }

    /**
     * 十神（生我者，正印偏印。我生者，伤官食神。克我者，正官七杀。我克者，正财偏财。同我者，劫财比肩。）
     *
     * @param HeavenStem $target 天干
     * @return TenStar 十神
     */
    function getTenStar(HeavenStem $target): TenStar
    {
        $hostElement = $this->getElement();
        $guestElement = $target->getElement();
        $index = 0;
        $sameYinYang = $this->getYinYang()->equals($target->getYinYang());
        if ($hostElement->getReinforce()->equals($guestElement)) {
            $index = 1;
        } else if ($hostElement->getRestrain()->equals($guestElement)) {
            $index = 2;
        } else if ($guestElement->getRestrain()->equals($hostElement)) {
            $index = 3;
        } else if ($guestElement->getReinforce()->equals($hostElement)) {
            $index = 4;
        }
        return TenStar::fromIndex($index * 2 + ($sameYinYang ? 0 : 1));
    }

    /**
     * 方位
     *
     * @return Direction 方位
     */
    function getDirection(): Direction
    {
        return Direction::fromIndex([2, 8, 4, 6, 0][intdiv($this->index, 2)]);
    }

    /**
     * 喜神方位（《喜神方位歌》甲己在艮乙庚乾，丙辛坤位喜神安。丁壬只在离宫坐，戊癸原在在巽间。）
     *
     * @return Direction 方位
     */
    function getJoyDirection(): Direction
    {
        return Direction::fromIndex([7, 5, 1, 8, 3][$this->index % 5]);
    }

    /**
     * 阳贵神方位（《阳贵神歌》甲戊坤艮位，乙己是坤坎，庚辛居离艮，丙丁兑与乾，震巽属何日，壬癸贵神安。）
     *
     * @return Direction 方位
     */
    function getYangDirection(): Direction
    {
        return Direction::fromIndex([1, 1, 6, 5, 7, 0, 8, 7, 2, 3][$this->index]);
    }

    /**
     * 阴贵神方位（《阴贵神歌》甲戊见牛羊，乙己鼠猴乡，丙丁猪鸡位，壬癸蛇兔藏，庚辛逢虎马，此是贵神方。）
     *
     * @return Direction 方位
     */
    function getYinDirection(): Direction
    {
        return Direction::fromIndex([7, 0, 5, 6, 1, 1, 7, 8, 3, 2][$this->index]);
    }

    /**
     * 财神方位（《财神方位歌》甲乙东北是财神，丙丁向在西南寻，戊己正北坐方位，庚辛正东去安身，壬癸原来正南坐，便是财神方位真。）
     *
     * @return Direction 方位
     */
    function getWealthDirection(): Direction
    {
        return Direction::fromIndex([7, 1, 0, 2, 8][intdiv($this->index, 2)]);
    }

    /**
     * 福神方位（《福神方位歌》甲乙东南是福神，丙丁正东是堪宜，戊北己南庚辛坤，壬在乾方癸在西。）
     *
     * @return Direction 方位
     */
    function getMascotDirection(): Direction
    {
        return Direction::fromIndex([3, 3, 2, 2, 0, 8, 1, 1, 5, 6][$this->index]);
    }

    /**
     * 天干彭祖百忌
     *
     * @return PengZuHeavenStem 天干彭祖百忌
     */
    function getPengZuHeavenStem(): PengZuHeavenStem
    {
        return PengZuHeavenStem::fromIndex($this->index);
    }

    /**
     * 地势(长生十二神)
     *
     * @param EarthBranch $earthBranch 地支
     * @return Terrain 地势(长生十二神)
     */
    function getTerrain(EarthBranch $earthBranch): Terrain
    {
        $earthBranchIndex = $earthBranch->getIndex();
        return Terrain::fromIndex([1, 6, 10, 9, 10, 9, 7, 0, 4, 3][$this->index] + (YinYang::YANG == $this->getYinYang() ? $earthBranchIndex : -$earthBranchIndex));
    }
}
