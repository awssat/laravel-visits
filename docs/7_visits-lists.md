# Visits Lists

Top or Lowest list per model type

## Top/Lowest visited items per model

```php
visits('App\Post')->top(10);
```

```php
visits('App\Post')->low(10);
```

### Filter by model attributes

You can get only some of the top/low models by query where clause. For example if Post model has `shares` & `likes` attributes you can filter the models like this:

```php
visits('App\Post')->top(10, [['likes', '>', 30], ['shares', '<', 20]]);
```

or just ...

```php
visits('App\Post')->top(10, ['likes' => 20]);
```

## Uncached list

```php
visits('App\Post')->fresh()->top(10);
```

> **Note:** you can always get uncached list by enabling `alwaysFresh` from config/visits.php file.

## By a period of time

```php
visits('App\Post')->period('month')->top(10);
```

> **Note** supported periods can be found in [periods-options](8_clear-and-reset-values.md#periods-options)

---

<p align="left">
  Prev:  <a href="6_retrieve-visits-and-stats.md">< Retrieve visits and stats</a> 
</p>

<p align="right">
  Next:  <a href="8_clear-and-reset-values.md">Clear and reset values ></a> 
</p>
