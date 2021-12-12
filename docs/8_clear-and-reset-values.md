# Clear and reset values

## Clear an item's visits

```php
visits($post)->reset();
```

## Clear an item's visits of a specific period

```php
visits($post)->period('year')->reset();
```

### Periods options

-   minute
-   hour
-   1hours to 12hours
-   day
-   week
-   month
-   year
-   quarter
-   decade
-   century

You can also make your custom period by adding a carbon marco in `AppServiceProvider`:

```php
Carbon::macro('endOf...', function () {
    //
});
```

## Clear recorded visitors' IPs

```php
//all
visits($post)->reset('ips');
//one
visits($post)->reset('ips','127.0.0.1');
```

## Clear items and its visits of a given model

```php
visits('App\Post')->reset();
```

## Clear all cached top/lowest lists

```php
visits('App\Post')->reset('lists');
```

## Clear visits from all items of the given model of a period

```php
visits('App\Post')->period('year')->reset();
```

## Clear & reset everything!

```php
visits('App\Post')->reset('factory');
```

---

<p align="left">
  Prev:  <a href="7_visits-lists.md">< Visits-lists</a> 
</p>
