<?php

namespace Awssat\Visits\Commands;

use Awssat\Visits\DataEngines\RedisEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class VisitsArchiveCommand extends Command
{
    protected $signature = 'visits:archive';
    protected $description = '(Laravel-Visits) Archive daily visits from Redis to the database.';

    public function handle()
    {
        if (! config('visits.archive_daily_visits')) {
            $this->error('Daily visits archiving is disabled. Please enable it in config/visits.php');
            return;
        }

        $this->info('Archiving daily visits...');

        $redis = app(RedisEngine::class)
            ->connect(config('visits.connection'))
            ->setPrefix(config('visits.keys_prefix'));
        $prefix = config('visits.keys_prefix');

        $keys = $redis->search('*_day_daily_*', false);

        foreach ($keys as $key) {
            if (\Illuminate\Support\Str::endsWith($key, '_total')) {
                continue;
            }

            $keyWithoutPrefix = substr($key, strlen($prefix) + 1);

            $keyParts = explode(':', $key);
            $key_parts_without_prefix = explode(':', $keyWithoutPrefix);
            $keyParts = explode(':', $key);
            $visitable_type = $keyParts[1];
            $keyWithoutPrefix = implode(':', array_slice($keyParts, 1));

            $parts = explode('_', $keyWithoutPrefix);
            $date = array_pop($parts);
            array_pop($parts); // remove daily
            array_pop($parts); // remove day
            $tag = array_pop($parts);

            $visits = $redis->valueList($keyWithoutPrefix, -1, true, true);

            foreach ($visits as $visitable_id => $count) {
                DB::table('visits_archive')->insert([
                    'visitable_type' => $visitable_type,
                    'visitable_id' => $visitable_id,
                    'tag' => $tag,
                    'date' => $date,
                    'count' => $count,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $redis->delete($key);
            $redis->delete($key . '_total');
        }

        $this->info('Done.');
    }
}
