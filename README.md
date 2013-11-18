#MongoCacher

Provides a caching layer for any Zend Framework 2 web app using MongoDB.

### Installation
----------------
Add MongoCacher to your `composer.json`:

~~~json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/Punchkick-Interactive/pki-saas-view-strategy.git"
        }
    ],
    "require": {
        "punchkick-interactive/MongoCacher": "dev-master"
    }
}
~~~

### Usage
---------
Start by importing MongoCacher:

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

You're almost done; just make sure to make MongoCacher available through your ServiceManager by adding the following factory to the same function `getServiceConfig()` within the same file `Module.php`:

~~~php
'MongoCacher' => function (ServiceManager $serviceManager) {
    $cacheDao = $serviceManager->get('CacheDao');
    $mongoCacher = new MongoCacher\MongoCacher($cacheDao);

    return $mongoCacher;
},
~~~

## And That's All!
