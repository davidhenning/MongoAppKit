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

use Silex\Application as SilexApplication;

use Symfony\Component\HttpFoundation\Request;

class View {

    /**
     * Name of the App built with MongoAppKit (used template path)
     * @var string
     */
    
    protected $_appName = '';

    /**
     * Config object
     * @var Config
     */

    protected $_config = null;

    /**
     * Request object
     * @var Config
     */

    protected $_request = null;

    /**
     * Application object
     * @var Config
     */

    protected $_app = null;
 
    /**
     * Name of the template to render
     * @var string
     */

    protected $_templateName = null;

    /**
     * Template data for rendering
     * @var array
     */

    protected $_templateData = array();

    /**
     * Id of the view related document
     * @var string
     */

    protected $_id = null;

    /**
     * Documents per page
     * @var integer
     */

    protected $_documentLimit = null;

    /**
     * Skipped documents
     * @var integer
     */

    protected $_skippedDocuments = 0;

    /**
     * Current page
     * @var integer
     */

    protected $_currentPage = 1;

    /**
     * Base path for generated urls
     * @var string
     */

    protected $_baseUrl = '';

    /**
     * Additional path for generated urls
     * @var string
     */

    protected $_paginationAdditionalUrl = '';

    /**
     * Additional get parameters for generated urls
     * @var array
     */

    protected $_additionalUrlParameters = array();

    /**
     * Total count of documents for pagination
     * @var integer
     */

    protected $_totalDocuments = 0;

    /**
     * Pagination array
     * @var array
     */

    protected $_pagination = null;

    /**
     * Current output format
     * @var string
     */

    protected $_outputFormat = 'html';

    /**
     * Allowed output format
     * @var string
     */

    protected $_allowedOutputFormats = array('html', 'json', 'xml');

    /**
     * Set document id if given
     *
     * @param string $id
     */

    public function __construct(SilexApplication $app, $id = null) {
        $this->_config = $app['config'];
        $this->_request = $app['request'];
        $this->_app = $app;

        if($id !== null) {
            $this->setId($id);
        }
    }

    /**
     * Get app name
     *
     * @return string
     */

    public function getAppName() {
        return $this->_appName;
    }

    /**
     * Set app name
     */

    public function setAppName($appName) {
        $this->_appName = $appName;
    }    

    /**
     * Get document id
     *
     * @return string
     */

    public function getId() {
        return $this->_id;
    }

    /**
     * Set document id
     *
     * @param string $id
     */

    public function setId($id) {
        $this->_id = $id;
    }

    /**
     * Get output method
     *
     * @return string
     */

    public function getOutputFormat() {
        return $this->_outputFormat;
    }

    /**
     * Set output method
     *
     * @param string $outputFormat
     */

    public function setOutputFormat($outputFormat) {
        if(!in_array($outputFormat, $this->_allowedOutputFormats)) {
            throw new \InvalidArgumentException("Output format '{$outputFormat}' is unkown.");
        }

        $this->_outputFormat = $outputFormat;
    }

    /**
     * Get document limit
     *
     * @return integer
     */

    public function getDocumentLimit() {
        return ($this->_documentLimit !== null) ? $this->_documentLimit : 100;
    }

    /**
     * Set count of documents per page
     *
     * @param integer $documentLimit
     */

    public function setDocumentLimit($documentLimit) {
        $this->_documentLimit = $documentLimit;
    }

    /**
     * Get total documents
     *
     * @return integer
     */

    public function getTotalDocuments() {
        return $this->_totalDocuments;
    }

    /**
     * Set count of total documents
     *
     * @param integer $documents
     */

    public function setTotalDocuments($documents) {
        $this->_totalDocuments = $documents;
    }


    /**
     * Get count of skipped documents
     *
     * @return integer
     */

    public function getSkippedDocuments() {
        return $this->_skippedDocuments;
    }

    /**
     * Set count of skipped documents
     *
     * @param integer $skippedDocuments
     */

    public function setSkippedDocuments($skippedDocuments) {
        $this->_skippedDocuments = $skippedDocuments;
    }

    /**
     * Get current page number
     *
     * @return integer
     */

    public function getCurrentPage() {
        return (int)$this->_skippedDocuments / $this->getDocumentLimit() + 1;
    }

    /**
     * Get all additional GET parameters
     *
     * @return array
     */

    public function getAdditionalUrlParameters() {
        return $this->_additionalUrlParameters;
    }

    /**
     * Add a get parameter to generated urls
     *
     * @param string $name
     * @param string $value
     */

    public function addAdditionalUrlParameter($name, $value) {
        $this->_additionalUrlParameters[$name] = $value;
    }

    /**
     * Create and return array with all pagination data
     *
     * @return array
     */

    public function getPagination() {
        if($this->_pagination === null) {
            // compute total pages
            $documentLimit = $this->getDocumentLimit();
            $currentPage = $this->getCurrentPage();
            $pageCount = (int)ceil($this->_totalDocuments / $documentLimit);
            
            if($pageCount > 1) {
                // init array of the pagination
                $pages = array(
                    'pages' => array(),
                    'currentPage' => $currentPage,
                    'documentsPerPage' => $documentLimit,
                    'totalPages' => $pageCount
                );

                // set URL to previous page and first page
                if($this->getCurrentPage() > 1) {
                    $pages['prevPageUrl'] = $this->_createPageUrl($currentPage - 1, $documentLimit);
                    $pages['firstPageUrl'] = $this->_createPageUrl(1, $documentLimit);
                } else {
                    $pages['prevPageUrl'] = false;
                    $pages['firstPageUrl'] = false;
                }

                // set URL to next page and last page
                if($this->getCurrentPage() < $pageCount) {
                    $pages['nextPageUrl'] = $this->_createPageUrl($currentPage + 1, $documentLimit);
                    $pages['lastPageUrl'] = $this->_createPageUrl($pageCount, $documentLimit);
                } else {
                    $pages['nextPageUrl'] = false;
                    $pages['lastPageUrl'] = false;
                }

                $pages['pages'] = $this->_getPages($pageCount, $documentLimit, $currentPage);

                $this->_pagination = $pages;
            }
        }

        return $this->_pagination;
    }

    /**
     * Get pages array with page numbers and urls
     *
     * @param integer $pages
     * @param integer $documentLimit
     * @param integer $currentPage
     * @return array
     */

    protected function _getPages($pages, $documentLimit, $currentPage) {
        $aPages = array();

        if($pages > 0) {
            
            // set pages with number, url and active state
            for($i = 1; $i <= $pages; $i++) {
                $page = array(
                    'nr' => $i,
                    'url' => $this->_createPageUrl($i, $documentLimit),
                    'active' => false
                );

                if($i === $currentPage) {
                    $page['active'] = true;
                } 

                $aPages[] = $page;
            }
        }

        return $aPages;
    }

    /**
     * Get url with given parameters and permanently added parameters
     *
     * @param array $params
     * @param string $baseUrl
     * @return string
     */

    protected function _createUrl($params, $baseUrl = null) {
        $baseUrl = (!empty($baseUrl)) ? $baseUrl : $this->_baseUrl;
        $sUrl = "{$baseUrl}.{$this->_outputFormat}";
        $params = array_merge($this->_additionalUrlParameters, $params);

        if(!empty($params)) {
            $sUrl .= '?'.http_build_query($params);
        }

        return $sUrl;
    }

    /**
     * Get url for given page number and limit
     *
     * @param integer $page
     * @param integer $limit
     * @return string
     */

    protected function _createPageUrl($page, $limit) {
        $params = array(
            'skip' => (($page - 1) * $limit),
            'limit' => $limit
        );

        return $this->_createUrl($params);
    }

    /**
     * Begin rendering the page in selected output format (HTML/TWIG, JSON, XML)
     */

    public function render(SilexApplication $app) {
        if($this->_outputFormat == 'html') {
            return $this->_renderTwig($app);
        } elseif($this->_outputFormat == 'json') {
            return $this->_renderJSON($app);
        } elseif($this->_outputFormat == 'xml') {
            header('Content-type: text/xml');
            return $this->_renderXML();
        } else {
            return $this->_renderTwig($app);
        }
    }

    /**
     * Load Twig Template Engine and render selected template with set data
     */

    protected function _renderTwig(SilexApplication $app) {
        return $app['twig']->render($this->_templateName, $this->_templateData);
    }

    /**
     * Render JSON output
     */

    protected function _renderJSON(SilexApplication $app) {
        return $app->json($this->_templateData);
    }

    /**
     * Render XML output
     */

    protected function _renderXML() {
        $document = new \SimpleXMLElement('<kickipedia></kickipedia>');
        $header = $document->addChild('header');
        $header->addChild('status', 200);

        return $document->asXML();
    }
}