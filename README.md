# Request

Helper class for working with information about web requests. Useful for accessing request data in an object-oriented fashion.


## Usage

```php
use cjrasmussen\Request\RequestData;

$requestData = new RequestData($_GET, $_POST, $_COOKIES, $_FILES, $_SERVER, $_ENV);

$isWebRequest = $requestData->isWebRequest();

if ($isWebRequest) {
    $url = $requestData->getRequestedUrl();
}

// access $_GET['id']
$requestData->query->get('id');
```

## Installation

Simply add a dependency on cjrasmussen/request to your composer.json file if you use [Composer](https://getcomposer.org/) to manage the dependencies of your project:

```sh
composer require cjrasmussen/request
```

Although it's recommended to use Composer, you can actually include the file(s) any way you want.


## License

Request is [MIT](http://opensource.org/licenses/MIT) licensed.