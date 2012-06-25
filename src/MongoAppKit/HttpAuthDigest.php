<?php

/**
 * Class HttpAuthDigest
 *
 * Authentication via HTTP Digest
 * 
 * @author David Henning <madcat.me@gmail.com>
 * 
 * @package MongoAppKit
 */


namespace MongoAppKit;

use MongoAppKit\Exceptions\HttpException;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class HttpAuthDigest {

    /**
     * Digest string from client
     * @var string
     */

    protected $_sDigest = null;

    /**
     * Parsed digest array
     * @var array
     */

    protected $_aDigest = null;

    /**
     * Realm name
     * @var string
     */

    protected $_sRealm = null;

    /**
     * Nonce
     * @var string
     */

    protected $_sNonce = null;

    /**
     * Opaque
     * @var string
     */

    protected $_sOpaque = null;

    public function __construct(Request $oRequest, $sRealm) {
        $sDigest = $oRequest->server->get('PHP_AUTH_DIGEST');
        $sHttpAuth = $oRequest->server->get('REDIRECT_HTTP_AUTHORIZATION');

        if(empty($sDigest) && !empty($sHttpAuth)) {
            $sDigest = $sHttpAuth;
        }
       
        $this->_sDigest = $sDigest;
        $this->_sRealm = $sRealm;

        $sIp = $oRequest->getClientIp();
        $sOpaque = sha1($sRealm.$oRequest->server->get('HTTP_USER_AGENT').$sIp);

        $this->_sNonce = sha1(uniqid($sIp));
        $this->_sOpaque = $sOpaque;
    }

    protected function _parseDigest() {
        if(empty($this->_sDigest)) {
            throw new HttpException('Unauthorized', 401);
        }

        $aNecessaryParts = array(
            "nonce"     => 1,
            "nc"        => 1,
            "cnonce"    => 1,
            "qop"       => 1,
            "username"  => 1,
            "uri"       => 1,
            "response"  => 1
        );

        $sNecessaryParts = implode("|", array_keys($aNecessaryParts));
        $aDigest = array();

        preg_match_all('@(' . $sNecessaryParts . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $this->_sDigest, $aMatches, PREG_SET_ORDER);

        foreach($aMatches as $aMatch) {
            $aDigest[$aMatch[1]] = $aMatch[3] ? $aMatch[3] : $aMatch[4];
            unset($aNecessaryParts[$aMatch[1]]);
        }

        if(!empty($aNecessaryParts)) {
            throw new HttpException('Bad Request', 400);
        }

        $this->_aDigest = $aDigest;
    }

    public function sendAuthenticationHeader($bForce = false) {
        if(empty($this->_sDigest) || $bForce === true) {
            $header = array(
                'WWW-Authenticate' => 'Digest realm="' . $this->_sRealm . '",nonce="' . $this->_sNonce . '",qop="auth",opaque="' . $this->_sOpaque . '"'
            );

            return new Response('Please authenticate', 401, $header);          
        }

        return null;
    }

    public function getUserName() {
        $this->_parseDigest();
        return $this->_aDigest['username'];
    }

    public function authenticate($sToken) {
        $this->_parseDigest();
        $a1 = $sToken; // md5("{$username}:{$realm}:{$password}")
        $a2 = md5("{$_SERVER['REQUEST_METHOD']}:{$this->_aDigest['uri']}");

        $aValidRepsonse = array(
            $a1,
            $this->_aDigest["nonce"],
            $this->_aDigest["nc"],
            $this->_aDigest["cnonce"],
            $this->_aDigest["qop"],
            $a2
        );

        $sValidRepsonse = md5(implode(':', $aValidRepsonse));

        if(($sValidRepsonse === $this->_aDigest["response"]) === false) {
            $e = new HttpException('Unauthorized', 401);
            $e->setCallingObject($this);
            throw $e;
        }
    }
}