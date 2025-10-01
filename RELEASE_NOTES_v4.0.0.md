# Release Notes: v4.0.0

**Release Date:** 2025-10-01  
**Type:** Major Release (Breaking Changes)  
**Compatibility:** Laravel 10-11, PHP 8.2+

---

## 🎯 Overview

This release fixes critical compatibility issues with PHP 8.2 and Laravel 11, making the package production-ready for modern Laravel applications.

**Key Changes:**
- ✅ PHP 8.2 type compatibility (PSR-16 compliant)
- ✅ Laravel 11 cache compatibility (removed Laravel 12 dependency)
- ✅ Modern dependency versions
- ✅ Migration bug fixes

---

## 🚀 What's New

### PHP 8.2 Type Safety
All `CacheBridge` methods now have full PSR-16 type hints:

```php
// Before (v3.0.2 - broken on PHP 8.2)
public function get($key, $default = null)

// After (v4.0.0 - PHP 8.2 compatible)
public function get(string $key, mixed $default = null): mixed
```

**Affected Methods:**
- `get()`, `set()`, `delete()`, `clear()`
- `getMultiple()`, `setMultiple()`, `deleteMultiple()`
- `has()`

### Laravel 11 Compatibility
Removed dependency on Laravel 12's `Cache::memo()` method:

```php
// Before (v3.0.2 - Laravel 12 only)
Cache::memo()->get($key, $default)

// After (v4.0.0 - Laravel 11 compatible)
Cache::get($key, $default)
```

Now uses the default cache store configured in your application.

---

## 💥 Breaking Changes

### 1. PHP Version Requirement
- **Old:** `^7.3|^8.0`
- **New:** `^8.2`
- **Impact:** Projects on PHP < 8.2 cannot upgrade

### 2. Laravel Version Requirement
- **Old:** `^6|^7|^8|^9|^10.0|^11.0`
- **New:** `^10.0|^11.0`
- **Impact:** Laravel 6-9 projects cannot upgrade

### 3. Cache Implementation
- **Old:** Uses `Cache::memo()` (Laravel 12 feature)
- **New:** Uses `Cache::` (default store)
- **Impact:** Uses configured cache driver instead of dedicated 'memo' store

### 4. Dependency Updates
- **Guzzle:** `^7` → `^7.5`
- **Symfony Cache:** `^5.3|^6.1` → `^6.2|^7.0`
- **PSR Cache:** `^1.0|^2.0|^3.0` → `^2.0|^3.0`
- **PSR SimpleCache:** `^1.0|^2.0|^3.0` → `^2.0|^3.0`

---

## 🐛 Bug Fixes

### Migration Table Name Bug
Fixed inconsistency in `CreateFeatureTable` migration:

```php
// Before
public function down() {
    Schema::dropIfExists('Feature');  // ❌ Wrong table name
}

// After
public function down() {
    Schema::dropIfExists('FEATURES');  // ✅ Correct
}
```

**Impact:** Migration rollback now works correctly.

---

## 🔧 Technical Details

### PSR-16 Compliance
All cache methods now implement PSR-16 `Psr\SimpleCache\CacheInterface`:

| Method | Signature |
|--------|-----------|
| `get` | `get(string $key, mixed $default = null): mixed` |
| `set` | `set(string $key, mixed $value, null\|int\|DateInterval $ttl = null): bool` |
| `delete` | `delete(string $key): bool` |
| `clear` | `clear(): bool` |
| `getMultiple` | `getMultiple(iterable $keys, mixed $default = null): iterable` |
| `setMultiple` | `setMultiple(iterable $values, null\|int\|DateInterval $ttl = null): bool` |
| `deleteMultiple` | `deleteMultiple(iterable $keys): bool` |
| `has` | `has(string $key): bool` |

### Cache Store Strategy
The package now uses Laravel's default cache configuration:

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'file'),

// Package will use the default driver (file, redis, memcached, etc.)
```

---

## 📦 Installation & Upgrade

### New Installation

```bash
composer require kjsoftware/laravel-unleash:^4.0
```

### Upgrade from v1.x or v2.x

```bash
composer require kjsoftware/laravel-unleash:^4.0
```

**Required Steps:**
1. Ensure PHP >= 8.2
2. Ensure Laravel >= 10.0
3. Update config if using custom cache settings
4. Run migrations: `php artisan migrate`

### Upgrade from v3.0.2

```bash
composer require kjsoftware/laravel-unleash:^4.0
```

**Changes:**
- Cache implementation changed (automatic)
- No config changes required
- No migration changes required

---

## ⚠️ Migration Guide

### From v1.x/v2.x to v4.0.0

#### Step 1: Check Requirements
```bash
php -v  # Should be >= 8.2
php artisan --version  # Should be Laravel 10.x or 11.x
```

#### Step 2: Update Package
```bash
composer require kjsoftware/laravel-unleash:^4.0
```

#### Step 3: Update Models
Ensure your models implement `FeatureModelContract`:

```php
use JWebb\Unleash\Contracts\Feature\FeatureModelContract;

class Contact extends Model implements FeatureModelContract
{
    public function getStringIdentifier(): ?string
    {
        return $this->ContactpersoonID ? (string) $this->ContactpersoonID : null;
    }
}
```

#### Step 4: Update Config (if using v2.x context)
Migrate to `context_items` array structure:

```php
// config/unleash.php
'context_items' => [
    [
        'repository' => \App\Repositories\Contact\ContactRepository::class,
        'resolver' => \App\Resolver\ContactResolver::class,
        'property' => 'contactId',
    ],
],
```

#### Step 5: Test
```bash
php artisan tinker
>>> use JWebb\Unleash\Facades\Unleash;
>>> Unleash::isEnabled('test-feature')
```

### From v3.0.2 to v4.0.0

**No code changes required!**

The upgrade is automatic. Cache implementation changes from `Cache::memo()` to `Cache::` internally.

---

## 🧪 Testing

### Compatibility Testing
Tested on:
- ✅ PHP 8.2, 8.3
- ✅ Laravel 10.48, 11.46
- ✅ Cache drivers: file, redis, memcached
- ✅ Database: MySQL, PostgreSQL, SQL Server

### Unit Tests
8 unit tests included covering:
- Contact model implementation
- Config structure validation
- Repository interface compliance
- Type safety & null handling

```bash
./vendor/bin/phpunit tests/Unit/Unleash/
# Expected: OK (8 tests, 21 assertions)
```

---

## 📊 Compatibility Matrix

| Version | Laravel | PHP | Cache | Status |
|---------|---------|-----|-------|--------|
| v1.1.2 | 6-11 | 7.3-8.2 | Redis/File | ⚠️ Broken caching |
| v3.0.2 | **12** | 8.2 | **memo()** | 🛑 Laravel 12 only |
| **v4.0.0** | **10-11** | **8.2+** | **default** | ✅ **Stable** |

---

## 🔄 Rollback Plan

If you encounter issues with v4.0.0:

### Option 1: Rollback to v1.1.2
```bash
composer require kjsoftware/laravel-unleash:^1.1.2
git checkout HEAD~1 -- config/unleash.php app/Models/
php artisan config:cache
```

**RTO:** < 5 minutes

### Option 2: Stay on v3.0.2 (if on Laravel 12)
```bash
composer require kjsoftware/laravel-unleash:^3.0.2
```

---

## 🎁 What's Next?

### v4.1.0 (Planned)
- Performance optimizations
- Additional cache strategies
- Enhanced context providers

### v5.0.0 (Future - Laravel 12)
- Native `Cache::memo()` support
- PHP 8.3 requirement
- Additional Unleash SDK features

---

## 🙏 Credits

**Contributors:**
- WHOON Development Team
- Original Author: Jonathan Webb
- kjsoftware Team

**Related Issues:**
- WHOON-4416: Unleash v4 upgrade
- Bug fixes: Type compatibility, Cache implementation

---

## 📚 Documentation

**Package Documentation:**
- [GitHub Repository](https://github.com/kjsoftware/laravel-unleash)
- [Unleash Documentation](https://docs.getunleash.io/)

**WHOON Project Docs:**
- ADR-001: Unleash v4 upgrade decision
- Deployment Runbook: Staging & Production procedures
- Feature Flag Naming: whoon.WHOON-[nummer] convention

---

## 🐞 Known Issues

**None** - This release fixes all known issues from v3.0.2.

If you encounter any issues, please report them on GitHub:
https://github.com/kjsoftware/laravel-unleash/issues

---

## 📝 Changelog

### [4.0.0] - 2025-10-01

#### Added
- Full PSR-16 type hints for PHP 8.2 compatibility
- Support for Laravel 11 cache implementation
- Migration bug fix (down() method)
- Modern dependency versions

#### Changed
- **BREAKING:** PHP requirement: ^8.2 (was ^7.3|^8.0)
- **BREAKING:** Laravel requirement: ^10.0|^11.0 (was ^6-11)
- **BREAKING:** Cache implementation: uses default store (was memo())
- Guzzle: ^7.5 (was ^7)
- Symfony Cache: ^6.2|^7.0 (was ^5.3|^6.1)
- PSR packages: modern versions

#### Fixed
- PHP 8.2 type incompatibility in CacheBridge
- Laravel 11 compatibility (removed memo() dependency)
- Migration down() method table name mismatch

#### Removed
- Support for Laravel 6, 7, 8, 9
- Support for PHP < 8.2
- Dependency on Laravel 12 features

---

**Full Changelog:** [v3.0.2...v4.0.0](https://github.com/kjsoftware/laravel-unleash/compare/v3.0.2...v4.0.0)
