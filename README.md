# EXTENSION LOADER

Easy extension loader


## Composer

```json
{
    "arrayiterator/extension" : "dev-master"
}
```

## REQUIREMENTS

```yaml
php : >= 7.0
```

## Example

```php
<?php
namespace Application;

use ArrayIterator\Extension\Loader;

// require composer autoload
require __DIR__ . '/vendor/autoload.php';

// extensions path to scan
$extensionPath = '/path/to/extensions/directory/';
$loader = new Loader($extensionPath);

// doing parse
$loader->start();

/**
 * Get available extension
 * @var array $availableExtensions
 */
$availableExtensions = $loader->getAllAvailableExtensions();
```

## LICENSE

[MIT LICENSE](LICENSE)
