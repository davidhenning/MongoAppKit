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

use MongoAppKit\Lists\IterateableList;

class DocumentList extends IterateableList {

    /**
     * MongoDB object
     * @var MongoDB
     */

    protected $_oDatabase = null;

    /**
     * Collection name
     * @var string
     */

    protected $_sCollectionName = null;

    /**
     * MongoCollection object
     * @var MongoCollection
     */

    protected $_oCollection = null;

    /**
     * Document object
     * @var Document
     */

    protected $_oDocumentBaseObject = null;

    /**
     * Count of selected documents
     * @var integer
     */

    protected $_iFoundDocuments = 0;

    /**
     * Total count of documents of the selected MongoDB collection
     * @var integer
     */

    protected $_iTotalDocuments = 0;

    /**
     * Document field for custom sorting
     * @var string
     */

    protected $_sCustomSortField = null;

    /**
     * Direction of custom sorting (asc or desc)
     * @var string
     */

    protected $_sCustomSortOrder = null;

    /**
     * Get MongoDB object and selects MongoDB collection
     */

    public function __construct() {
        $this->_oDatabase = $this->getStorage()->getDatabase();
        
        if($this->_sCollectionName !== null) {
            $this->_oCollection = $this->_oDatabase->selectCollection($this->_sCollectionName);
        }
    }

    /**
     * Set custom sorting
     *
     * @param string $sField
     * @param string $sDirection
     * @throws Exception
     */

    public function setCustomSorting($sField, $sDirection = 'asc') {
        if(!$this->_oDocumentBaseObject instanceof Document) {
            throw new \Exception("No document base object set");
        }

        if(!$this->_oDocumentBaseObject->fieldExists($sField)) {
            throw new \Exception("Field {$sField} does not exist.");
        }

        if($sDirection === null) {
            $sDirection = 'asc';
        }

        if(!in_array($sDirection, array('asc', 'desc'))) {
            throw new \Exception("Direction {$sDirection} is not supported.");
        }

        $this->_sCustomSortField = $sField;
        $this->_sCustomSortOrder = $sDirection;
    }

    /**
     * Set document base object for list
     *
     * @param Document $oDocumentObject
     */

    public function setDocumentBaseObject(Document $oDocumentObject) {       
        // check for valid document object
        if(!$oDocumentObject instanceof Document) {
            throw new \InvalidArgumentException("Expecting instance of Document");
        }

        $this->_oDocumentBaseObject = $oDocumentObject;
    }

    /**
     * Load all documents of selected MongoDB collection
     */

    public function findAll() {       
        $oCursor = $this->_getDefaultCursor();
        $this->_setPropertiesFromCursor($oCursor);
    }

    /**
     * Load documents of selected MongoDB collection by given page
     *
     * @param integer $iPage
     * @param integer $iPerPage
     * @param MongoCursor $oCursorOverride
     */

    public function find($iLimit = 100, $iSkip = 0, $oCursorOverride = null) {
        // set default cursor if no override is available
        $oCursor = ($oCursorOverride !== null && $oCursorOverride instanceof \MongoCursor) ? $oCursorOverride : $this->_getDefaultCursor();
        // set limit for page
        $oCursor->limit($iLimit);

        if($iSkip > 0) {
            $oCursor->skip($iSkip);
        }

        $this->_setDocumentsFromCursor($oCursor);       
    }

    /**
     * Get total count of documents of selected MongoDB collection
     *
     * @return integer
     */

    public function getTotalDocuments() {
        return $this->_iTotalDocuments;
    }

    /**
     * Get count of documents of selected MongoDB collection
     *
     * @return integer
     */

    public function getFoundDocuments() {
        return $this->_iFoundDocuments;
    }

    /**
     * Get MongoCursor object with given fields for given where clause
     *
     * @param array $aWhere
     * @param arary $aFields
     * @return MongoCursor
     */

    protected function _getDefaultCursor($aWhere = null, $aFields = null) {
        // no where clause if none given
        if($aWhere === null) {
            $aWhere = array();
        }

        // select all fields if none given
        if($aFields === null) {
            $aFields = array();
        }

        // get documents
        $oCursor = $this->_oCollection->find($aWhere, $aFields);

        // sort
        $aSorting = $this->_getSorting();
        $oCursor->sort($aSorting);

        return $oCursor;     
    }

    protected function _getSorting() {
        $aSorting = array();

        if($this->_sCustomSortField !== null) {
            $iSortOrder = 1;

            // set sorting direction
            if($this->_sCustomSortOrder !== null) {
                if($this->_sCustomSortOrder === 'asc') {
                    $iSortOrder = 1;
                } elseif($this->_sCustomSortOrder === 'desc') {
                    $iSortOrder = -1;
                } else {
                    $iSortOrder = 1;
                }
            }   

            // order documents by custom sorting field
            $aSorting = array($this->_sCustomSortField => $iSortOrder);
        } else {
            // default sorting by creation date
            $aSorting = array('createdOn' => -1);
        }

        return $aSorting;
    }

    /**
     * Clone instances of the given document object for each document in the given MongoCursor object 
     *
     * @param MongoCursor $oCursor
     */

    protected function _setDocumentsFromCursor(\MongoCursor $oCursor) {
        $aData = array();
        
        // check for valid cursor
        if($oCursor === null) {
            throw new \Exception("No cursor object set");
        }

        // check for valid base document object
        if(!$this->_oDocumentBaseObject instanceof Document) {
            throw new \Exception("No document base object set");
        }

        // iterate cursor
        foreach($oCursor as $oLine) {
            // clone base object and fill with data from current cursor iteration
            $oDocument = clone $this->_oDocumentBaseObject;
            $oDocument->updateProperties($oLine);
            $aData[] = $oDocument;
        }

        $this->_iFoundDocuments = $oCursor->count(true);
        $this->_iTotalDocuments = $oCursor->count();
        $this->_aProperties = $aData;
    }
}