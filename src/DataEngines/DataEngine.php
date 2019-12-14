<?php

namespace Awssat\Visits\DataEngines;

Interface DataEngine
{
    public function connect(string $connection): self;
    public function setPrefix(string $prefix): self;

    public function increment(string $key, int $value, ?string $member = null): bool;
    public function decrement(string $key, int $value, ?string $member = null): bool;

    public function delete($key, ?string $member = null): bool;
    public function get(string $key, ?string $member = null);
    public function set(string $key, $value, ?string $member = null): bool;

    public function flatList(string $key, int $limit): array;
    public function addToFlatList(string $key, $value): bool;
    public function search(string $word, bool $noPrefix = true): array;
    public function valueList(string $search, int $limit = -1, bool $orderByAsc = false, bool $withValues = false): array;


    public function exists(string $key): bool;

    /**
     * @return int seconds, will return -1 if no it has no expiration 
     */
    public function timeLeft(string $key): int;

    public function setExpiration(string $key, int $time): bool;
}