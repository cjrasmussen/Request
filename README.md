# Request

Simple functions for working with information about web requests.


## Usage

```php
use cjrasmussen\Request\RequestHelpers;

$isWebRequest = RequestHelpers::isWebRequest();

if ($isWebRequest) {
    $url = RequestHelpers::getRequestedUrl();
}
```

## Installation

Simply add a dependency on cjrasmussen/request to your composer.json file if you use [Composer](https://getcomposer.org/) to manage the dependencies of your project:

```sh
composer require cjrasmussen/request
```

Although it's recommended to use Composer, you can actually include the file(s) any way you want.


## License

Request is [MIT](http://opensource.org/licenses/MIT) licensed.