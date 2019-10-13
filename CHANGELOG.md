# Changelog

All Notable changes to `laravel-visits` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## 2.1.0

- Rewrites huge part of the package to support multiple data engines.
- Adds database's data engine support (Eloquent).


## 2.0.0

- Global ignore feature (can be enabled from [config/visits.php](https://github.com/awssat/laravel-visits/blob/master/src/config/visits.php#L70))
- Parameter signature of methods increment/decrement/forceIncrement/forceDecrement has changed.

```
//old
increment($inc = 1, $force = false, $periods = true, $country = true, $refer = true)
//new
increment($inc = 1, $force = false, $ignore = [])
//old 
forceIncrement($inc = 1, $periods = true)
//new
forceIncrement($inc = 1, $ignore = [])
```

- Now you can get visitors OSes and browser's languages
- Replace Laravel array/string helpers with clasess as they were deperecated in recent versions.
