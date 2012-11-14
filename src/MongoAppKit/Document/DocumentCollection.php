<?php

namespace MongoAppKit\Document;

use MongoAppKit\Collection\MutableList;

use Silex\Application;

class DocumentCollection extends MutableList
{

    /**
     * Document object
     * @var Document
     */

    protected $_defaultDocument = null;

    /**
     * Count of selected documents
     * @var integer
     */

    protected $_foundDocuments = 0;

    /**
     * Total count of documents of the selected MongoDB collection
     * @var integer
     */

    protected $_totalDocuments = 0;

    /**
     * Document field for custom sorting
     * @var string
     */

    protected $_sort = null;

    /**
     * Direction of custom sorting (asc or desc)
     * @var string
     */

    protected $_sortOrder = null;

    /**
     * Set MongoDB object
     *
     * @param Document $defaultDocument
     */

    public function __construct(Document $defaultDocument)
    {
        $this->_defaultDocument = $defaultDocument;
    }

    public function sortBy($field, $direction = 'asc')
    {
        if (!$this->_defaultDocument->fieldExists($field)) {
            throw new \InvalidArgumentException("Field {$field} does not exist.");
        }

        if ($direction === null) {
            $direction = 'asc';
        }

        if (!in_array($direction, array('asc', 'desc'))) {
            throw new \InvalidArgumentException("Direction {$direction} is not supported.");
        }

        $this->_sort = $field;
        $this->_sortOrder = $direction;
    }

    /**
     * Finds documents of selected MongoDB collection
     *
     * @param array $where
     * @param integer $limit
     * @param integer $skip
     */

    public function find($where = null, $limit = null, $skip = null)
    {
        // set default cursor if no override is available
        $cursor = ($where !== null && is_array($where) && !empty($where)) ? $this->_getCursor($where) : $this->_getCursor();

        // set limit for page
        if ($limit !== null && (int)$limit > 0) {
            $cursor->limit($limit);
        }

        if ($skip !== null && (int)$skip > 0) {
            $cursor->skip($skip);
        }

        $this->_setDocuments($cursor);

        return $this;
    }

    /**
     * Get total count of documents of selected MongoDB collection
     *
     * @return integer
     */

    public function getTotalDocuments()
    {
        return $this->_totalDocuments;
    }

    /**
     * Get count of documents of selected MongoDB collection
     *
     * @return integer
     */

    public function getFoundDocuments()
    {
        return $this->_foundDocuments;
    }

    /**
     * Get MongoCursor object with given fields for given where clause
     *
     * @param array $where
     * @param array $fields
     * @return \MongoCursor
     */

    protected function _getCursor($where = null, $fields = null)
    {
        // no where clause if none given
        if ($where === null) {
            $where = array();
        }

        // select all fields if none given
        if ($fields === null) {
            $fields = array();
        }

        // get documents
        $cursor = $this->_defaultDocument->getCollection()->find($where, $fields);

        // sort
        $sort = $this->_getSorting();
        $cursor->sort($sort);

        return $cursor;
    }

    protected function _getSorting()
    {
        $sorting = array();

        if ($this->_sort !== null) {
            $sortOrder = 1;

            // set sorting direction
            if ($this->_sortOrder !== null) {
                if ($this->_sortOrder === 'asc') {
                    $sortOrder = 1;
                } elseif ($this->_sortOrder === 'desc') {
                    $sortOrder = -1;
                } else {
                    $sortOrder = 1;
                }
            }

            // order documents by custom sorting field
            $sorting = array($this->_sort => $sortOrder);
        } else {
            // default sorting by creation date
            $sorting = array('createdOn' => -1);
        }

        return $sorting;
    }

    /**
     * Clone instances of the given document object for each document in the given MongoCursor object
     *
     * @param \MongoCursor $cursor
     */

    protected function _setDocuments(\MongoCursor $cursor)
    {
        $data = array();

        // iterate cursor
        foreach ($cursor as $line) {
            // clone base object and fill with data from current cursor iteration
            $document = clone $this->_defaultDocument;
            $document->updateProperties($line);
            $data[] = $document;
        }

        $this->_foundDocuments = $cursor->count(true);
        $this->_totalDocuments = $cursor->count();
        $this->_properties = $data;
    }

    /*
     * Remove found documents from collection object and MongoDB collection
     *
     * @return DocumentCollection
     */

    public function remove()
    {
        if ($this->length > 0) {
            $properties = $this->filter(function ($property) {
                return $property instanceof Document;
            });

            foreach ($properties as $property => $document) {
                $document->remove();
                $this->removeProperty($property);
            }
        }

        return $this;
    }
}