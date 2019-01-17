## Falsum
Pretty error handling for Fat-Free Framework

[![Downloads](https://img.shields.io/packagist/dm/ikkez/f3-falsum.svg?style=flat-square)](https://packagist.org/packages/ikkez/f3-falsum)
[![Version](http://img.shields.io/packagist/v/ikkez/f3-falsum.svg?style=flat-square)](https://packagist.org/packages/ikkez/f3-falsum)

![Preview](http://i.imgur.com/Wz5gJKy.jpg)

### Instalation

To install it on you web app, just run the following command in you Terminal at your root directory:

```
composer require ikkez/f3-falsum
```

Next, on your bootstrap file (usually `index.php`) add the following line:

```php
Falsum\Run::handler();
```

By default Falsum will only run when the `DEBUG` variable from the hive is set to 3. If you want to override this to show in any `DEBUG` status, send true as parameter on `handler()`.

```php
Falsum\Run::handler(true);
```
