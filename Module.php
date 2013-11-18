<?php

namespace MongoCacher;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use MongoCacher;

/**
 * Class Module
 *
 * @package MongoCacher
 */
class Module implements ConfigProviderInterface, AutoloaderProviderInterface
{
    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Return an array for passing to Zend\Loader\AutoloaderFactory.
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return array(
            'factory' => array(
                // Cacher class
                'MongoCacher' => function (ServiceManager $serviceManager) {
                    $cacheDao = $serviceManager->get('CacheDao');
                    $mongoCacher = new MongoCacher\MongoCacher($cacheDao);

                    return $mongoCacher;
                },
                // CacheDao
                'CacheDao' => function ($sm) {
                    $factory = new \PhlyMongo\MongoDbFactory(
                        $sm->get(
                            'DatabaseString'
                        ),
                        'Application\Mongo\Connection'
                    );

                    $mongoDB = $factory->createService($sm);

                    return new CacheDao(
                        $mongoDB->cachingDao,
                        new Cache()
                    );
                },
            ),
        );
    }

    /**
     * On bootstrap attach our exception strategy
     *
     * @param \Zend\Mvc\MvcEvent
     *
     * @return void
     */
    public function onBootstrap(MvcEvent $event)
    {
        $application = $event->getApplication();
    }
}
