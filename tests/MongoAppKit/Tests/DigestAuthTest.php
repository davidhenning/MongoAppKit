<?php

namespace MongoAppKit\Tests;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

use MongoAppKit\HttpAuthDigest,
    MongoAppKit\Exception\HttpException;

class DigestAuthTest extends \PHPUnit_Framework_TestCase {

    protected function _getDigest() {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new Request();
        $request->server->set('PHP_AUTH_DIGEST', 'Digest username="MadCat", realm="CodrPress", nonce="af0e89caf3051ec4f3ca9c0d3bd47b47c419e6d9", uri="/CodrPress/posts/", response="719530eab88a1a73e399f621eddb0200", opaque="93b469dbde4fe014f1d0ec23dd66db08d790c3aa", qop=auth, nc=00000002, cnonce="43d5fa6e10b7195e"');

        return new HttpAuthDigest($request, 'CodrPress');
    }

    public function testAuthentication() {
        $digest = $this->_getDigest();
        $auth = false;

        try {
            $digest->authenticate('8514c67a500cb6509b7f240d14761364');
            $auth = true;
        } catch(HttpException $e) {
            $auth = false;
        }

        $this->assertTrue($auth);
    }

    public function testWrongAuthentication() {
        $digest = $this->_getDigest();
        $auth = false;

        try {
            $digest->authenticate('fgdfgdfg');
            $auth = true;
        } catch(HttpException $e) {
            $auth = false;
        }

        $this->assertFalse($auth);
    }

    public function testgetUserName() {
        $digest = $this->_getDigest();

        $this->assertEquals('MadCat', $digest->getUserName());
    }

    public function testSendAuthenticationHeader() {
        $digest = $this->_getDigest();
        $response = $digest->sendAuthenticationHeader();

        $this->assertNull($response);
    }

    public function testSendForcedAuthenticationHeader() {
        $digest = $this->_getDigest();
        $response = $digest->sendAuthenticationHeader(true);

        $this->assertTrue($response instanceof Response);
    }
}
