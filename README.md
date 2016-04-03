# Asd(name?) PHP7 API Framework

PHP7 Framework for API applications.

## Installation

None, it is not even close to ready for release.

##Some Key Concepts

* PHP 7 only
* No dependencies(expect PSR-7 interfaces)
* PSR: 1,2,4,7
* No statics, no globals
* No forced strict conventions in naming/routing, folder structure etc when using
* No settings or configs
* Transparency, no unncessary abstraction/wrapping
* Use PHP 7 strict types, (except PSR-7 classes, cuz they're not PHP 7)

## Intended usage

Presumptions: htaccess configured and PSR-4 autoloading

Define routes with HTTP-method, path and controllerclass + method

```
//index.php

require_once('vendor/autoload.php');

use Asd\Asd;
use Asd\Router\Route;

$app = new Asd();

$app->addRoute(new Route('GET', '/', 'MyApp\Controller\Welcome@start'));
$app->addRoute(new Route('GET', 'about', 'MyApp\Controller\Welcome@about'));

$app->run();
```

```
//MyApp/Controller/Welcome.php

namespace MyApp\controller;

use Asd\Controller;

class Welcome extends Controller
{
  public function start()
  {
    $this->response->withJson('Hello!');
  }

  public function about()
  {
    $this->response->withJson(['name' => 'Steve', 'email' => 'steve@email.com']);
  }
}
```

## Setup Dev

*Requires PHP7*

```
git clone https://github.com/afridlund85/php7-api-framework.git
cd php7-api-framework
composer install
```

### Tests

Three levels of testing, unit, integration and system.

#### Runing tests

**Run all test suites**
```
composer test
```

**Run Unit test suite**
```
composer unit
```

**Run Integration test suite**
```
composer integration
```

**Run System test suite**
```
composer system
```

### PSR-2 linting

**Check for PSR-2 errors**

```
composer sniff
```

**(Try to)Auto-fix PSR-2 issues**

```
composer fixer
```

### Code coverage

**Generate code coverage**

```
composer coverage
```