<?php

/**
 * Class View
 *
 * Basic view functions
 * 
 * @author David Henning <madcat.me@gmail.com>
 * 
 * @package MongoAppKit
 */

namespace MongoAppKit;

use Silex\Application,
    Silex\Provider\TwigServiceProvider;

use Symfony\Component\HttpFoundation\Request;

class View {

    /**
     * Name of the App built with MongoAppKit (used template path)
     * @var string
     */
    
    protected $_sAppName = '';

    /**
     * Config object
     * @var Config
     */

    protected $_oConfig = null;

    /**
     * Request object
     * @var Config
     */

    protected $_oRequest = null;

    /**
     * Application object
     * @var Config
     */

    protected $_oApp = null;
 
    /**
     * Name of the template to render
     * @var string
     */

    protected $_sTemplateName = null;

    /**
     * Template data for rendering
     * @var array
     */

    protected $_aTemplateData = array();

    /**
     * Id of the view related document
     * @var string
     */

    protected $_sId = null;

    /**
     * Documents per page
     * @var integer
     */

    protected $_iDocumentLimit = null;

    /**
     * Skipped documents
     * @var integer
     */

    protected $_iSkippedDocuments = 0;

    /**
     * Current page
     * @var integer
     */

    protected $_iCurrentPage = 1;

    /**
     * Base path for generated urls
     * @var string
     */

    protected $_sBaseUrl = '';

    /**
     * Additional path for generated urls
     * @var string
     */

    protected $_sPaginationAdditionalUrl = '';

    /**
     * Additional get parameters for generated urls
     * @var array
     */

    protected $_aAdditionalUrlParameters = array();

    /**
     * Total count of documents for pagination
     * @var integer
     */

    protected $_iTotalDocuments = 0;

    /**
     * Pagination array
     * @var array
     */

    protected $_aPagination = null;

    /**
     * Current output format
     * @var string
     */

    protected $_sOutputFormat = 'html';

    /**
     * Allowed output format
     * @var string
     */

    protected $_aAllowedOutputFormats = array('html', 'json', 'xml');

    /**
     * Set document id if given
     *
     * @param string $sId
     */

    public function __construct(Application $oApp, $sId = null) {
        $this->_oConfig = $oApp['config'];
        $this->_oRequest = $oApp['request'];
        $this->_oApp = $oApp;

        if($sId !== null) {
            $this->setId($sId);
        }
    }

    /**
     * Get app name
     *
     * @return string
     */

    public function getAppName() {
        return $this->_sAppName;
    }

    /**
     * Set app name
     */

    protected function _setAppName($sAppName) {
        $this->_sAppName = $sAppName;
    }    

    /**
     * Get document id
     *
     * @return string
     */

    public function getId() {
        return $this->_sId;
    }

    /**
     * Set document id
     *
     * @param string $sId
     */

    public function setId($sId) {
        $this->_sId = $sId;
    }

    /**
     * Get output method
     *
     * @return string
     */

    public function getOutputFormat() {
        return $this->_sOutputFormat;
    }

    /**
     * Set output method
     *
     * @param string $sOutputFormat
     */

    public function setOutputFormat($sOutputFormat) {
        if(!in_array($sOutputFormat, $this->_aAllowedOutputFormats)) {
            throw new \InvalidArgumentException("Output format '{$sOutputFormat}' is unkown.");
        }

        $this->_sOutputFormat = $sOutputFormat;
    }

    /**
     * Get document limit
     *
     * @return integer
     */

    public function getDocumentLimit() {
        return ($this->_iDocumentLimit !== null) ? $this->_iDocumentLimit : 100;
    }

    /**
     * Set count of documents per page
     *
     * @param integer $iDocumentLimit
     */

    public function setDocumentLimit($iDocumentLimit) {
        $this->_iDocumentLimit = $iDocumentLimit;
    }

    /**
     * Get total documents
     *
     * @return integer
     */

    public function getTotalDocuments() {
        return $this->_iTotalDocuments;
    }

    /**
     * Set count of total documents
     *
     * @param integer $iDocuments
     */

    public function setTotalDocuments($iDocuments) {
        $this->_iTotalDocuments = $iDocuments;
    }

    /**
     * Set count of skipped documents
     *
     * @param integer $iSkippedDocuments
     */

    public function setSkippedDocuments($iSkippedDocuments) {
        $this->_iSkippedDocuments = $iSkippedDocuments;
    }

    /**
     * Get current page number
     *
     * @return integer
     */

    public function getCurrentPage() {
        return $this->_iSkippedDocuments / $this->getDocumentLimit() + 1;
    }

    /**
     * Add a get parameter to generated urls
     *
     * @param string $sName
     * @param string $sValue
     */

    public function addAdditionalUrlParameter($sName, $sValue) {
        $this->_aAdditionalUrlParameters[$sName] = $sValue;
    }

    /**
     * Create and return array with all pagination data
     *
     * @return array
     */

    public function getPagination() {
        if($this->_aPagination === null) {    
            // compute total pages
            $iDocumentLimit = $this->getDocumentLimit();
            $iCurrentPage = $this->getCurrentPage();
            $iPages = ceil($this->_iTotalDocuments / $iDocumentLimit);
            
            if($iPages > 1) {
                // init array of the pagination
                $aPages = array(
                    'pages' => array(),
                    'currentPage' => $iCurrentPage,
                    'documentsPerPage' => $iDocumentLimit,
                    'totalPages' => $iPages
                );

                // set URL to previous page and first page
                if($this->getCurrentPage() > 1) {
                    $aPages['prevPageUrl'] = $this->_createPageUrl($iCurrentPage - 1, $iDocumentLimit);
                    $aPages['firstPageUrl'] = $this->_createPageUrl(1, $iDocumentLimit);
                } else {
                    $aPages['prevPageUrl'] = false;
                    $aPages['firstPageUrl'] = false;
                }

                // set URL to next page and last page
                if($this->getCurrentPage() < $iPages) {
                    $aPages['nextPageUrl'] = $this->_createPageUrl($iCurrentPage + 1, $iDocumentLimit);
                    $aPages['lastPageUrl'] = $this->_createPageUrl($iPages, $iDocumentLimit);
                } else {
                    $aPages['nextPageUrl'] = false;
                    $aPages['lastPageUrl'] = false;
                }

                $aPages['pages'] = $this->_getPages($iPages, $iDocumentLimit, $iCurrentPage); 

                $this->_aPagination = $aPages;
            }
        }

        return $this->_aPagination;
    }

    /**
     * Get pages array with page numbers and urls
     *
     * @param integer $iPages
     * @param integer $iDocumentLimit
     * @param integer $iCurrentPage
     * @return array
     */

    protected function _getPages($iPages, $iDocumentLimit, $iCurrentPage) {
        $aPages = array();

        if($iPages > 0) {
            
            // set pages with number, url and active state
            for($i = 1; $i <= $iPages; $i++) {
                $aPage = array(
                    'nr' => $i,
                    'url' => $this->_createPageUrl($i, $iDocumentLimit),
                    'active' => false
                );

                if($i === $iCurrentPage) {
                    $aPage['active'] = true;
                } 

                $aPages[] = $aPage;
            }
        }

        return $aPages;
    }

    /**
     * Get url with given parameters and permanently added parameters
     *
     * @param array $aParams
     * @param string $sBaseUrl
     * @return string
     */

    protected function _createUrl($aParams, $sBaseUrl = null) {
        $sBaseUrl = (!empty($sBaseUrl)) ? $sBaseUrl : $this->_sBaseUrl;
        $sUrl = "{$sBaseUrl}.{$this->_sOutputFormat}";
        $aParams = array_merge($this->_aAdditionalUrlParameters, $aParams);

        if(!empty($aParams)) {
            $sUrl .= '?'.http_build_query($aParams);
        }

        return $sUrl;
    }

    /**
     * Get url for given page number and limit
     *
     * @param integer $iPage
     * @param integer $iLimit
     * @return string
     */

    protected function _createPageUrl($iPage, $iLimit) {
        $aParams = array(
            'skip' => (($iPage - 1) * $iLimit), 
            'limit' => $iLimit
        );

        return $this->_createUrl($aParams);
    }

    /**
     * Begin rendering the page in selected output format (HTML/TWIG, JSON, XML)
     */

    public function render($oApp) {
        if($this->_sOutputFormat == 'html') {
            return $this->_renderTwig($oApp);
        } elseif($this->_sOutputFormat == 'json') {
            return $this->_renderJSON($oApp);
        } elseif($this->_sOutputFormat == 'xml') {
            header('Content-type: text/xml');
            $this->_renderXML();
        } else {
            return $this->_renderTwig($oApp);
        }
    }

    /**
     * Load Twig Template Engine and render selected template with set data
     */

    protected function _renderTwig($oApp) {
        $oApp->register(new TwigServiceProvider(), array(
            'twig.path' => getBasePath() ."/src/".$this->_oConfig->getProperty('AppName')."/Templates",
            'twig.options' => array(
              'cache' => getBasePath() .'/tmp',
              'auto_reload' => $this->_oConfig->getProperty('DebugMode')
            )
        ));

        return $oApp['twig']->render($this->_sTemplateName, $this->_aTemplateData);
    }

    /**
     * Render JSON output
     */

    protected function _renderJSON($oApp) {
        return $oApp->json($this->_aTemplateData);
    }

    /**
     * Render XML output
     */

    protected function _renderXML() {
        $oDocument = new \SimpleXMLElement('<kickipedia></kickipedia>');
        $oHeader = $oDocument->addChild('header');
        $oStatus = $oHeader->addChild('status', 200);

        echo $oDocument->asXML();
    }
}