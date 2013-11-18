<?php

namespace MongoCacher;

// Zend classes
use \MongoId;
use \MongoDate;

/**
 * Class Cache
 *
 * @package MongoCacher
 */
class Cache
{
    /**
     * Mongo ID
     *
     * @var
     */
    public $id;

    /**
     * Metadatas property that includes 'dateCreated' and 'dateModified'
     *
     * @var null
     */
    public $metadatas = null;

    /**
     * Adds a mongo id (_id) to the object and removes the id field
     *
     * @param $id
     *
     * @return void
     */
    public function addMongoId($id)
    {
        $this->_id = new MongoId($id);
        unset($this->id);
    }

    /**
     * Removes the _id field and add the id field by converting _id to string
     *
     * @return void
     */
    public function removeMongoId()
    {
        $this->id = (string) $this->_id;
        unset($this->_id);
    }

    /**
     * Modifies the datemodified part of the metadatas
     *
     * @return void
     */
    public function modelUpdated()
    {
        if (!$this->metadatas) {
            $this->metadatas = array();
        }

        $this->metadatas['dateModified'] = $this->generateDate();
    }

    /**
     * Creates the metadatas object and sets the 'datecreated' to now
     */
    public function modelCreated()
    {
        $this->metadatas = array();
        $this->metadatas['dateCreated'] = $this->generateDate();
    }

    /**
     * Generates a date for now
     *
     * @return array
     */
    public function generateDate()
    {
        return array(
            'string' => date('Y-m-d h:i:s'),
            'timestamp' => new MongoDate()
        );
    }

    /**
     * Filter out fields that are in the $fields array
     *
     * @param array $fields
     */
    public function filterOutFields(array $fields)
    {
        $vars = get_object_vars($this);

        foreach ($vars as $key => $value) {

            if (!in_array($key, $fields)) {
                unset($this->{$key});
            }
        }
    }
}
