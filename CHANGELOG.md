# Changelog

## [1.1.9](https://github.com/Okipa/laravel-base-repository/releases/tag/1.1.9)
2018-11-12
- Changed `getAll()` return type from `Illuminate\Database\Eloquent\Collection` to `Illuminate\Support\Collection`.

## [1.1.8](https://github.com/Okipa/laravel-base-repository/releases/tag/1.1.8)
2018-08-27
- Changed `findMultipleFromIds()` method to `findMultipleFromPrimaries()` to improve code coherence.

## [1.1.7](https://github.com/Okipa/laravel-base-repository/releases/tag/1.1.7)
2018-08-27
- Changed all `$missingFillableAttributesToNull` parameter to `$saveMissingModelFillableAttributesToNull` for better comprehension.
