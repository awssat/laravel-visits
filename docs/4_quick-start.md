# Quick Start

## Start using it

It's simple.

Using `visits` helper as:

```php
visits($model)->{method}()
```

Where:

-   **$model**: is any Eloquent model from your project.
-   **{method}**: any method that is supported by this library, and they are documented below.

## Tags

-   You can track multiple kinds of visits to a single model using the tags as

```php
visits($model,'tag1')->increment();
```

## Integration with any model

You can add a `visits` method to your model class:

```php
class Post extends Model
{

    //....

    public function vzt()
    {
        return visits($this);
    }
}
```

Then you can use it as:

```php
$post = Post::find(1);
$post->vzt()->increment();
$post->vzt()->count();
```

## Relationship with models (only for Eloquent engine)

If you are using visits with eloquent as engine (from config/visits.php; engine => 'eloquent') then you can add a relationship method to your models.

```php
class Post extends Model
{

    //....

    public function visits()
    {
        return visits($this)->relation();
    }
}

//then:

Post::with('visits')->get();
```

---

<p align="left">
  Prev:  <a href="3_installation.md">< Installation</a> 
</p>

<p align="right">
  Next:  <a href="5_increments-and-decrementst.md">Increments and decrements ></a> 
</p>
