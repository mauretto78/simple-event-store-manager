<?php
/**
 * This file is part of the Simple EventStore Manager package.
 *
 * (c) Mauro Cassani<https://github.com/mauretto78>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleEventStoreManager\Infrastructure\Services;

use Cocur\Slugify\Slugify;
use SimpleEventStoreManager\Domain\Model\EventAggregateId;

class HashGeneratorService
{
    /**
     * SETTINGS
     */
    const HASH_SEPARATOR = '-';
    const LOWERCASE = true;
    const REGEXP_PATTERN = '/([^A-Za-z0-9*]|-)+/';
    const RULESET = 'default';

    /**
     * @var Slugify
     */
    private $slugify;

    /**
     * HashGeneratorService constructor.
     */
    public function __construct()
    {
        $this->slugify = new Slugify(
            [
                'lowercase' => self::LOWERCASE,
                'separator' => self::HASH_SEPARATOR,
                'regexp' => self::REGEXP_PATTERN,
                'ruleset' => self::RULESET,
            ]
        );
    }

    /**
     * @return HashGeneratorService
     */
    private static function build()
    {
        return new self();
    }

    /**
     * @param EventAggregateId $eventAggregateId
     *
     * @return string
     */
    public static function computeAggregateHash(EventAggregateId $eventAggregateId)
    {
        return sprintf('aggregate:%s', (string) $eventAggregateId);
    }

    /**
     * @param $aggregateName
     *
     * @return string
     */
    public static function computeAggregateNameHash($aggregateName)
    {
        return sprintf('aggregate-name:%s', self::computeStringHash($aggregateName));
    }

    /**
     * @param EventAggregateId $eventAggregateId
     * @return string
     */
    public static function computeEventsHash(EventAggregateId $eventAggregateId)
    {
        return sprintf('events:%s', (string) $eventAggregateId);
    }

    /**
     * @param $string
     * @return string
     */
    public static function computeStringHash($string)
    {
        return self::build()->slugify->slugify($string);
    }
}
