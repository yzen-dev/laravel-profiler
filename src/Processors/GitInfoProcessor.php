<?php

declare(strict_types=1);

namespace Profiler\Processors;

/**
 * Class GitInfoProcessor
 *
 * @package Profiler\Processors
 */
class GitInfoProcessor
{
    /**
     * Add in extra git info
     *
     * @param array<mixed> $record
     *
     * @return array<mixed>
     */
    public static function get()
    {
        $branches = shell_exec('git branch -v --no-abbrev');
        $result = [];
        if ($branches !== null && preg_match('{^\* (.+?)\s+([a-f0-9]{40})(?:\s|$)(.*)}m', $branches, $matches)) {
            $result = [
                'branch' => $matches[1],
                'hash' => $matches[2],
                'name' => $matches[3],
            ];
        }

        return $result;
    }
}
