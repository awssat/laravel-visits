# Retrieve visits and stats

## An item visits

#### All visits of an item

```php
visits($post)->count();
```

> **Note:** $post is a row of a model, i.e. $post = Post::find(22);

#### Item's visits by a period

```php
visits($post)->period('day')->count();
```

## A model class visits

#### All visits of a model type

```php
visits('App\Post')->count();
```

#### Visits of a model type in period

```php
visits('App\Post')->period('day')->count();
```

## Countries of visitors

```php
visits($post)->countries();
```

## Referers of visitors

```php
visits($post)->refs();
```

## Operating Systems of visitors

```php
visits($post)->operatingSystems();
```

## Languages of visitors

```php
visits($post)->languages();
```

---

<p align="left">
  Prev:  <a href="5_increments-and-decrements.md">< Increments and decrements</a> 
</p>

<p align="right">
  Next:  <a href="7_visits-lists.md">Visits lists ></a> 
</p>
