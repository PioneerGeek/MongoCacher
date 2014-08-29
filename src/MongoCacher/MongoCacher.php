<?php

namespace MongoCacher;

// Zend classes
use MongoDate;

use MongoCacher\CacheDao;

/**
 * Class MongoCacher
 *
 * @package MongoCacher
 */
class MongoCacher
{
    /**
     * Cache collection data access object
     *
     * @var null
     */
    private $currentCachingDao = null;

    /**
     * Time to live for the cached object before it gets expired and deleted
     *
     * @var int|null
     */
    private $ttl = null;

    /**
     * Constructor
     *
     * @param CacheDao $currentCachingDao
     *
     * @param int $ttl
     */
    public function __construct(CacheDao $currentCachingDao, $ttl = 21600)
    {
        $this->currentCachingDao = $currentCachingDao;
        $this->ttl = $ttl;
    }

    /**
     * Getter function for fetching cached data
     *
     * @param $key
     *
     * @return null
     */
    public function get($key)
    {
        // Fetches a cached object based on the provided key
        $currentObject = $this->currentCachingDao->getObjectByParams(
            array(
                "key" => $key
            )
        );

        // If missing status key then something iw wrong, return null
        if (isset($currentObject->status->sec) === false) {
            return null;
        }
        $currentMongoDate = new MongoDate();
        // Safe fail, if mongo did not delete entry return null so data can be reset
        if ($currentMongoDate->sec > $currentObject->status->sec) {
            return null;
        }

        // Makes sure that a key was fetched and has the property 'value'
        if ($currentObject != null && property_exists($currentObject, "value")) {
            $value = unserialize(base64_decode($currentObject->value));
            if (empty($value) === false) {
                // If data is instance of JsonModel then inject dataTime property
                if ($value instanceof \Zend\View\Model\JsonModel) {
                    $value->dataTime = $currentMongoDate->sec;
                }
                return $value;
            }
        }
        return null;

    }

    /**
     * Setter function for caching data
     *
     * @param $key
     * @param $value
     * @param null $ttl
     *
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        // Makes sure that the key for the cached data is not empty
        if (empty($key)) {
            return false;
        }

        // Sets the TTL to the default value in case it was not provided
        if ($ttl === null) {
            $ttl = $this->ttl;
        }

        // Sets the TTL to never expire if the provided TTL was 0
        if ($ttl == 0) {
            $time = null;
        } else {
            // Increases the current timestamp by the TTL value provided
            $time = new MongoDate();
            $time->sec = $time->sec + $ttl;
        }

        // Inserts the document into the collection (caches it)
        $currentObject = $this->currentCachingDao->getObjectByParams(
            array(
                "key" => $key,
            )
        );

        // Prepares the document to be cached
        $documentToBeInserted = array(
            "status" => $time,
            "key" => $key,
            "value" => ""
        );

        if ($currentObject != null) {
            if (property_exists($currentObject, "id")) {
                $documentToBeInserted["value"] = base64_encode(serialize($value));
                $this->currentCachingDao->save($currentObject->id, $documentToBeInserted);

                return true;
            }
        } else {
            // Prepares the document to be cached
            $documentToBeInserted["value"] = base64_encode(serialize($value));
            // Inserts the document into the collection (caches it)
            $result = $this->currentCachingDao->insert($documentToBeInserted);
        }

        if ($result) {
            return true;
        }

        return false;
    }
}
