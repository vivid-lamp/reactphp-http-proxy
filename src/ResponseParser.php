<?php

namespace Clue\React\HttpProxy;

/**
 * A simple HTTP response parser.
 */
class ResponseParser
{
    protected $message;

    /**
     * Parses an HTTP message into an associative array.
     * @param string $message
     */
    public function __construct($message)
    {
        if (!$message) {
            throw new \InvalidArgumentException('Invalid message');
        }
        $this->message = $message;
    }

    /**
     * Parses an HTTP message into an associative array.
     * @return array
     */
    protected function parseMessage()
    {
        $lines  = preg_split('/(\\r?\\n)/', $this->message, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = array('start-line' => array_shift($lines), 'headers' => array(), 'body' => '');
        array_shift($lines);

        for ($i = 0, $totalLines = count($lines); $i < $totalLines; $i += 2) {
            $line = $lines[$i];
            if (empty($line)) {
                if ($i < $totalLines - 1) {
                    $result['body'] = implode('', array_slice($lines, $i + 2));
                }
                break;
            }
            if (strpos($line, ':')) {
                $parts = explode(':', $line, 2);
                $key   = trim($parts[0]);
                $value = isset($parts[1]) ? trim($parts[1]) : '';

                $result['headers'][$key][] = $value;
            }
        }

        return $result;
    }

    /**
     * Parses a response message string into an associative array.
     * @return array
     */
    public function getResponse()
    {
        $data = $this->parseMessage();

        if (!preg_match('/^HTTP\/.* [0-9]{3}( .*|$)/', $data['start-line'])) {
            throw new \InvalidArgumentException('Invalid response string');
        }
        $parts    = explode(' ', $data['start-line'], 3);
        $subParts = explode('/', $parts[0]);

        return array(
            'status'       => intval($parts[1]),
            'headers'      => $data['headers'],
            'body'         => $data['body'],
            'version'      => $subParts[1],
            'reason_phrase' => isset($parts[2]) ? $parts[2] : null
        );
    }
}
