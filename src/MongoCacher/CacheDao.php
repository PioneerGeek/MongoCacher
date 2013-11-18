<?php

namespace MongoCacher;

// Zend classes
use Zend\Stdlib\Hydrator\ObjectProperty;
use PhlyMongo\HydratingMongoCursor;
use MongoCollection;
use MongoCursor;
use MongoId;

// Our classes
use MongoCacher\Cache;

/**
 * Class CacheDao
 *
 * @package MongoCacher
 */
class CacheDao
{
    /**
     * Current DB in use (use db ****)
     *
     * @var \MongoCollection|null
     */
    protected $db = null;

    /**
     * Template class in use (model)
     *
     * @var Cache|null
     */
    protected $templateClass = null;

    /**
     * Constructor
     *
     * @param MongoCollection $db
     * @param Cache $templateClass
     */
    public function __construct(MongoCollection $db, Cache $templateClass)
    {
        $this->db = $db;
        $this->templateClass = $templateClass;

        $this->db->ensureIndex(
            array(
                "status" => 1
            ),
            array(
                "expireAfterSeconds" => 0
            )
        );
    }

    /**
     * Gets Mongo documents
     *
     * @param MongoCursor $result
     *
     * @return array
     */
    protected function getObjects(MongoCursor $result)
    {
        $resultArray = array();

        if ($result->count()) {

            $cursor = new HydratingMongoCursor(
                $result,
                new ObjectProperty(),
                $this->templateClass
            );

            foreach ($cursor as $object) {
                $newObject = clone $object;
                $newObject->removeMongoId();
                $resultArray[] = $newObject;
            }

        }

        return $resultArray;
    }

    public function getObjectListByParams(array $params, $shallow = true)
    {
        $result = $this->db->find($params);

        $objects = $this->getObjects($result);

        return $objects;
    }

    public function getObjectByParams(array $params)
    {
        $objects = $this->getObjectListByParams($params, false);

        if (count($objects)) {
            return $objects[0];
        }

        return null;
    }

    public function getById($id)
    {
        return $this->getObjectByParams(array('_id' => new MongoId($id)));
    }

    public function insert($data)
    {
        if (!$data instanceof AbstractModel) {
            $data = $this->hydrate(
                $data,
                $this->templateClass
            );
        }


        $newlyCreatedId = (string)new MongoId();
        $data->addMongoId($newlyCreatedId);

        //update metadatas
        $data->modelCreated();

        //save the data
        $this->db->save($data);

        return $newlyCreatedId;
    }

    public function save($id, $data)
    {
        if ($data instanceof Cache) {
            $obj = $data;
        } else {
            $obj = $this->hydrate(
                $data,
                $this->templateClass
            );
        }

        $obj->addMongoId($id);

        $oldObject = $this->getById($id);

        if ($oldObject) {
            $obj->metadatas = $oldObject->metadatas;
        }

        // Update metadatas
        $obj->modelUpdated();

        $this->db->save($obj);
    }

    public function getTemplateClass()
    {
        return $this->templateClass;
    }

    public function hydrate(array $data, Cache $templateClassObject)
    {
        $hydrator = new ObjectProperty();

        return $hydrator->hydrate(
            $data,
            $templateClassObject
        );
    }
}
