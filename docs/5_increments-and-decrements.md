# Increments and Decrements

## Increment

### One

```php
visits($post)->increment();
```

### More than one

```php
visits($post)->increment(10);
```

## Decrement

### One

```php
visits($post)->decrement();
```

### More than one

```php
visits($post)->decrement(10);
```

> **Note:** Using Increment/decrement method will only work once every 15 minutes (default setting). You can use force methods or modifiy the time from settings or using seconds method.

## Increment/decrement once per x seconds

based on visitor's IP

```php
visits($post)->seconds(30)->increment()
```

> **Note:** this will override default config setting (once each 15 minutes per IP).

## Force increment/decrement

```php
visits($post)->forceIncrement();
visits($post)->forceDecrement();
```

-   This will ignore IP limitation and increment/decrement every visit.

## Ignore recording extra information

If you want to stop recoding some of the extra information that the package collected during incrementing the counter such as country and language of visior, then just pass it to the ignore parameter

```php
//any of 'country', 'refer', 'periods', 'operatingSystem', 'language'
visits('App\Post')->increment(1, false, ['country', 'language']);
```

or you can ignore it permanently from config/visits.php

> **warning:** If you choose to ignore `periods` then you won't be able to get the count of visits during specific period of time.

---

<p align="left">
  Prev:  <a href="4_quick-start.md">< Quick start</a> 
</p>

<p align="right">
  Next:  <a href="6_retrieve-visits-and-stats.md">Retrieve visits and Stats ></a> 
</p>
