<?php

namespace Clue\Tests\React\HttpProxy;

use Clue\React\HttpProxy\ResponseParser;

class ResponseParserTest extends AbstractTestCase
{

    public function testValidResponse()
    {
        $message  = "HTTP/1.1 200 OK\r\nContent-Type: text/html\r\n\r\n<html lang=\"\"><body>Hello, World!</body></html>";
        $parser   = new ResponseParser($message);
        $response = $parser->getResponse();

        $this->assertEquals(200, $response['status']);
        $this->assertEquals('1.1', $response['version']);
        $this->assertEquals('OK', $response['reason_phrase']);
        $this->assertEquals(array('Content-Type' => array('text/html')), $response['headers']);
        $this->assertEquals("<html lang=\"\"><body>Hello, World!</body></html>", $response['body']);
    }

    public function testInvalidResponse()
    {
        $this->expectException('InvalidArgumentException');

        $message = "Invalid Response";
        $parser  = new ResponseParser($message);
        $parser->getResponse();
    }

}