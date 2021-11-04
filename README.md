### PclZip Refactor
A sort of refactor of the monolithic but excellent pclzip library. It adds no new features or fixes no known bugs (yet). But this is an attempt to keep it usable in the modern PHP era. The code has been changed however, thus not compatible with existing projects where PclZip is being used. However, these changes are very minimal, and should be easy to migrate.

### Refactor RoadMap
- [x] Change the code to meet PSR standards.
- [x] Remove anything related to ``magic_quotes``.
- [x] Swapp error logging with exceptions while keeping their codes and messages intact
- [x] Change global constants to internal class constants
- [x] Move global functions to a separate Helper class
- [ ] Write docblock comments
- [ ] Make use of strict types (highly unlikely)

### Change reflected in usage
Using the library would be almost the same except for the constants, as they have been moved to the class itself. So if you were using it like this:
```php
$pclZip = new PclZip('file.zip');
$pclZip->extract(PCLZIP_OPT_PATH, '/path/to/', PCLZIP_OPT_REPLACE_NEWER);
```
The new  usage would be something like this:

```php
use MirazMac\PclZip\PclZip;

try {
    $pclZip = new PclZip('file.zip');
    $pclZip->extract(PclZip::PCLZIP_OPT_PATH, '/path/to/', PclZip::PCLZIP_OPT_REPLACE_NEWER);
} catch (\Exception $e) {
    echo $e->getMessage();
}
```

So the key difference as of now is the constants should be accessed via the class and you should be ready to catch the exception whenever something goes wrong.

And these following constants ``PCLZIP_READ_BLOCK_SIZE``, ``PCLZIP_SEPARATOR``, ``PCLZIP_TEMPORARY_DIR``, ``PCLZIP_TEMPORARY_FILE_RATIO``. has been changed to ``public`` static variables, as these were meant to be customizable, by defining them on your own. Now to change this just override the values before creating an instance, like this:
```php
use MirazMac\PclZip\PclZip;

PclZip::$PCLZIP_TEMPORARY_DIR = '/temp';
```
