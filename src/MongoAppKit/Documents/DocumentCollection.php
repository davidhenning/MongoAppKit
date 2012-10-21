<?php

/**
 * Class DocumentList
 *
 * Collects a list of documents
 * 
 * @author David Henning <madcat.me@gmail.com>
 * 
 * @package MongoAppKit
 */

namespace MongoAppKit\Documents;

use MongoAppKit\Config,
    MongoAppKit\Lists\IterateableList;

use Silex\Application;

class DocumentCollection extends IterateableList {

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

    protected $_customSortField = null;

    /**
     * Direction of custom sorting (asc or desc)
     * @var string
     */

    protected $_customSortOrder = null;

    /**
     * Set MongoDB object
     *
     * @param MongoDB $oDatabase
     */

    public function __construct(Document $defaultDocument) {
        $this->_defaultDocument = $defaultDocument;
    }

    public function setCustomSorting($field, $direction = 'asc') {
        if(!$this->_defaultDocument instanceof Document) {
            throw new \Exception("No document base object set");
        }

        if(!$this->_defaultDocument->fieldExists($field)) {
            throw new \Exception("Field {$field} does not exist.");
        }

        if($direction === null) {
            $direction = 'asc';
        }

        if(!in_array($direction, array('asc', 'desc'))) {
            throw new \Exception("Direction {$direction} is not supported.");
        }

        $this->_customSortField = $field;
        $this->_customSortOrder = $direction;
    }

    /**
     * Load all documents of selected MongoDB collection
     */

    public function findAll() {       
        $cursor = $this->_getDefaultCursor();
        $this->_setDocumentsFromCursor($cursor);

        return $this;
    }

    /**
     * Load documents of selected MongoDB collection by given page
     *
     * @param integer $iPage
     * @param integer $iPerPage
     * @param MongoCursor $cursorOverride
     */

    public function find($limit = 100, $skip = 0, $cursorOverride = null) {
        // set default cursor if no override is available
        $cursor = ($cursorOverride !== null && $cursorOverride instanceof \MongoCursor) ? $cursorOverride : $this->_getDefaultCursor();
        // set limit for page
        $cursor->limit($limit);

        if($skip > 0) {
            $cursor->skip($skip);
        }

        $this->_setDocumentsFromCursor($cursor);

        return $this;
    }

    /**
     * Get total count of documents of selected MongoDB collection
     *
     * @return integer
     */

    public function getTotalDocuments() {
        return $this->_totalDocuments;
    }

    /**
     * Get count of documents of selected MongoDB collection
     *
     * @return integer
     */

    public function getFoundDocuments() {
        return $this->_foundDocuments;
    }

    /**
     * Get MongoCursor object with given fields for given where clause
     *
     * @param array $where
     * @param arary $fields
     * @return MongoCursor
     */

    protected function _getDefaultCursor($where = null, $fields = null) {

        // check for valid base document object
        if(!$this->_defaultDocument instanceof Document) {
            throw new \Exception("No document base object set");
        }

        // no where clause if none given
        if($where === null) {
            $where = array();
        }

        // select all fields if none given
        if($fields === null) {
            $fields = array();
        }

        // get documents
        $cursor = $this->_defaultDocument->getCollection()->find($where, $fields);

        // sort
        $aSorting = $this->_getSorting();
        $cursor->sort($aSorting);

        return $cursor;
    }

    protected function _getSorting() {
        $sorting = array();

        if($this->_customSortField !== null) {
            $sortOrder = 1;

            // set sorting direction
            if($this->_customSortOrder !== null) {
                if($this->_customSortOrder === 'asc') {
                    $sortOrder = 1;
                } elseif($this->_customSortOrder === 'desc') {
                    $sortOrder = -1;
                } else {
                    $sortOrder = 1;
                }
            }   

            // order documents by custom sorting field
            $sorting = array($this->_customSortField => $sortOrder);
        } else {
            // default sorting by creation date
            $sorting = array('createdOn' => -1);
        }

        return $sorting;
    }

    /**
     * Clone instances of the given document object for each document in the given MongoCursor object 
     *
     * @param MongoCursor $cursor
     */

    protected function _setDocumentsFromCursor(\MongoCursor $cursor) {
        $data = array();
        
        // check for valid cursor
        if($cursor === null) {
            throw new \Exception("No cursor object set");
        }

        // check for valid base document object
        if(!$this->_defaultDocument instanceof Document) {
            throw new \Exception("No document base object set");
        }

        // iterate cursor
        foreach($cursor as $line) {
            // clone base object and fill with data from current cursor iteration
            $document = clone $this->_defaultDocument;
            $document->updateProperties($line);
            $data[] = $document;
        }

        $this->_foundDocuments = $cursor->count(true);
        $this->_totalDocuments = $cursor->count();
        $this->_properties = $data;
    }
}