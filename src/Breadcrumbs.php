<?php

declare(strict_types=1);

namespace Profiler;

use Illuminate\Support\Arr;
use Profiler\Processors\MemoryProcessor;

/**
 * Class Breadcrumbs
 *
 * @package Profiler
 */
final class Breadcrumbs
{
    /**
     * Breadcrumbs of current execution
     *
     * @var array<mixed>
     */
    private $breadcrumbs = [
    ];

    /**
     * @var Breadcrumbs|null
     */
    private static $instance;

    /**
     * @var string
     */
    private $currentBranch = 'main';

    /**
     *
     */
    public function __construct()
    {
        $this->breadcrumbs = [
            'main' => [
                'type' => 'section',
                'title' => 'main',
                'time' => 0,
                'startTime' => $_SERVER["REQUEST_TIME_FLOAT"],
                'memory' => 0,
                'stats' => [
                    'sql' => 0,
                    'http' => 0,
                ],
                'startMemory' => memory_get_usage(),
                'breadcrumbs' => [],
            ],
        ];
    }

    /**
     * Get current instance
     *
     * @return Breadcrumbs
     */
    public static function getInstance(): Breadcrumbs
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Add info in breadcrumbs
     *
     * @param mixed $data
     *
     * @return Breadcrumbs
     */
    public static function add($data): Breadcrumbs
    {
        $insertData = Arr::get(self::getInstance()->breadcrumbs, self::getInstance()->currentBranch);
        $insertData['breadcrumbs'][] = $data;
        if (!isset($insertData['startTime'])) {
            $insertData['startTime'] = microtime(true);
        }
        $insertData['time'] = microtime(true) - $insertData['startTime'];

        if (isset($data['type'])) {
            if ($data['type'] === 'sql') {
                $insertData['stats']['sql']++;
            }
            if ($data['type'] === 'http') {
                $insertData['stats']['http']++;
            }
        }

        Arr::set(self::getInstance()->breadcrumbs, self::getInstance()->currentBranch, $insertData);
        return self::getInstance();
    }

    /**
     * Add info in breadcrumbs
     *
     * @param $title
     *
     * @return Breadcrumbs
     */
    public static function addBranch($title): Breadcrumbs
    {
        $sectionSkeleton = [
            'startTime' => microtime(true),
            'time' => 0,
            'memory' => 0,
            'startMemory' => memory_get_peak_usage(),
            'stats' => [
                'sql' => 0,
                'http' => 0,
            ],
            'type' => 'branch',
            'title' => $title,
            'sub' => $title,
            'breadcrumbs' => [],
        ];
        $insertData = Arr::get(self::getInstance()->breadcrumbs, self::getInstance()->currentBranch);
        $insertData['breadcrumbs'][] = $sectionSkeleton;
        Arr::set(self::getInstance()->breadcrumbs, self::getInstance()->currentBranch, $insertData);
        $index = count($insertData['breadcrumbs']) > 0 ? count($insertData['breadcrumbs']) - 1 : 0;
        self::getInstance()->currentBranch = self::getInstance()->currentBranch . '.breadcrumbs.' . $index;
        return self::getInstance();
    }

    /**
     * @return Breadcrumbs
     */
    public static function closeCurrentBranch(): Breadcrumbs
    {
        $closeSection = Arr::get(self::getInstance()->breadcrumbs, self::getInstance()->currentBranch);
        $closeSection['time'] = microtime(true) - $closeSection['startTime'];

        $closeSection['memory'] = memory_get_peak_usage() - $closeSection['startMemory'];
        $closeSection['memory'] = MemoryProcessor::formatBytes($closeSection['memory']);
        Arr::set(self::getInstance()->breadcrumbs, self::getInstance()->currentBranch, $closeSection);

        $path = explode('.', self::getInstance()->currentBranch);
        array_pop($path);
        array_pop($path);
        self::getInstance()->currentBranch = implode('.', $path);

        $insertData = Arr::get(self::getInstance()->breadcrumbs, self::getInstance()->currentBranch);
        $insertData['stats']['sql'] += $closeSection['stats']['sql'];
        $insertData['stats']['http'] += $closeSection['stats']['http'];
        Arr::set(self::getInstance()->breadcrumbs, self::getInstance()->currentBranch, $insertData);

        return self::getInstance();
    }

    /**
     * Get all breadcrumbs
     *
     * @return array
     */
    public static function getBreadcrumbs(): array
    {
        self::getInstance()->breadcrumbs['main']['time'] = microtime(true) - self::getInstance()->breadcrumbs['main']['startTime'];

        self::getInstance()->breadcrumbs['main']['memory'] = MemoryProcessor::formatBytes(
            memory_get_peak_usage() - self::getInstance()->breadcrumbs['main']['startMemory']
        );
        return self::getInstance()->breadcrumbs;
    }
}
