<?php

declare(strict_types=1);

namespace Profiler\Processors;

/**
 * Class MemoryProcessor
 *
 * @package Profiler\Processors
 */
class MemoryProcessor
{
    /**
     * @return string
     */
    public static function getMemoryPeakUsage(): string
    {
        $usage = memory_get_peak_usage(true);
        return self::formatBytes($usage);
    }
    
    /**
     * @param int $bytes
     *
     * @return string
     */
    public static function formatBytes(int $bytes): string
    {
        if ($bytes === 0){
            return '0 b';
        }
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . ' ' . $unit[$i];
    }
}
