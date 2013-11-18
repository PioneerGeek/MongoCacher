#MongoCacher

Provides a caching layer for any Zend Framework 2 web app using MongoDB.

### Installation
----------------
Add `MongoCacher` to your `composer.json`:

~~~json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Punchkick-Interactive/MongoCacher.git"
        }
    ],
    "require": {
        "punchkick-interactive/MongoCacher": "dev-master"
    }
}
~~~

### Usage
---------
Start by importing `MongoCacher`:

~~~
use MongoCacher;
~~~

And adding a factory for the DAO (data access object) to your `Module.php` within the function `getServiceConfig()`:

~~~php
public function getServiceConfig()
{
    return array(
        'factories' => array(
        	'CacheDao' => function ($sm) {
                $factory = new \PhlyMongo\MongoDbFactory(
                    $sm->get(
                        'MongoCacher'
                    ),
                    // Your preferred DB connection (usually defined within your global.php or local.php)
                    'Application\Mongo\Connection'
                );

                $mongoDB = $factory->createService($sm);

                return new MongoCacher\CacheDao(
                    $mongoDB->ggpData,
                    new MongoCacher\Cache()
                );
            },
        )
    );
}
~~~

You're almost done; just make sure to make `MongoCacher` available through your `ServiceManager` by adding the following factory to the same function `getServiceConfig()` within the same file `Module.php`:

~~~php
'MongoCacher' => function (ServiceManager $serviceManager) {
    $cacheDao = $serviceManager->get('CacheDao');
    $mongoCacher = new MongoCacher\MongoCacher($cacheDao);

    return $mongoCacher;
},
~~~

You're set now! In your controller, fetch a new instance of `MongoCacher` using the `ServiceManager` and start using it:

~~~php
$mongoCacher = $this->getServiceLocator()->get('MongoCacher');

$key = "myFistMongoCacherKey"; // String
$value = "myFirstMongoCacherData"; // Could be any data type (object, string, integer, etc.)
$ttl = 1800; // In seconds

// Caches a new entry
$this->mongoCacher->set($key, $value, $ttl);

// Retrieves an entry
$cachedData = $this->mongoCacher->get($key);
~~~

## And That's All!
