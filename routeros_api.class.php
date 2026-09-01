<?php

class RouterosAPI
{
    public $debug = false;
    public $connected = false;

    private $socket = null;
    private $error_no = null;
    private $error_str = null;

    public function connect($ip, $username, $password, $port = 8728, $timeout = 3)
    {
        $this->connected = false;

        $this->socket = @fsockopen($ip, $port, $this->error_no, $this->error_str, $timeout);

        if (!$this->socket) {
            if ($this->debug) {
                echo "Connection failed: {$this->error_str} ({$this->error_no})";
            }
            return false;
        }

        stream_set_timeout($this->socket, $timeout);

        $this->write('/login', false);
        $this->write('=name=' . $username, false);
        $this->write('=password=' . $password);

        $response = $this->read();

        if (!empty($response)) {
            $last = end($response);
            if (is_array($last) && isset($last['!done'])) {
                $this->connected = true;
                return true;
            }
        }

        $this->disconnect();
        return false;
    }

    public function disconnect()
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }

        $this->socket = null;
        $this->connected = false;
    }

    public function write($command, $last = true)
    {
        if (!$this->socket || !is_resource($this->socket)) {
            throw new \Exception("RouterOS socket is not connected. Cannot write command.");
        }

        $this->writeLength(strlen($command));
        fwrite($this->socket, $command);

        if ($last) {
            $this->writeLength(0);
        }

        return true;
    }

    public function read($parse = true)
    {
        if (!$this->socket || !is_resource($this->socket)) {
            throw new \Exception("RouterOS socket is not connected. Cannot read response.");
        }

        $response = [];
        $current = [];

        while (true) {
            $word = $this->readWord();

            if ($word === false) {
                break;
            }

            if ($word === '') {
                if (!empty($current)) {
                    $response[] = $parse ? $this->parseResponseItem($current) : $current;
                    $current = [];
                }

                $last = end($response);
                if ($parse && is_array($last) && (isset($last['!done']) || isset($last['!fatal']))) {
                    break;
                }
                continue;
            }

            $current[] = $word;
        }

        return $response;
    }

    private function parseResponseItem(array $items)
    {
        $result = [];

        foreach ($items as $item) {
            if (in_array($item, ['!re', '!done', '!trap', '!fatal'], true)) {
                $result[$item] = true;
                continue;
            }

            if (strpos($item, '=') === 0) {
                $parts = explode('=', substr($item, 1), 2);
                $key = $parts[0] ?? '';
                $value = $parts[1] ?? '';
                $result[$key] = $value;
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    private function readWord()
    {
        if (!$this->socket || !is_resource($this->socket)) {
            throw new \Exception("RouterOS socket is not connected. Cannot read word.");
        }

        $len = $this->readLength();

        if ($len === false) {
            return false;
        }

        if ($len === 0) {
            return '';
        }

        $word = '';
        $remaining = $len;

        while ($remaining > 0) {
            $chunk = fread($this->socket, $remaining);
            if ($chunk === false || $chunk === '') {
                return false;
            }
            $word .= $chunk;
            $remaining -= strlen($chunk);
        }

        return $word;
    }

    private function writeLength($length)
    {
        if (!$this->socket || !is_resource($this->socket)) {
            throw new \Exception("RouterOS socket is not connected. Cannot write length.");
        }

        if ($length < 0x80) {
            fwrite($this->socket, chr($length));
        } elseif ($length < 0x4000) {
            $length |= 0x8000;
            fwrite($this->socket, chr(($length >> 8) & 0xFF) . chr($length & 0xFF));
        } elseif ($length < 0x200000) {
            $length |= 0xC00000;
            fwrite(
                $this->socket,
                chr(($length >> 16) & 0xFF) .
                chr(($length >> 8) & 0xFF) .
                chr($length & 0xFF)
            );
        } elseif ($length < 0x10000000) {
            $length |= 0xE0000000;
            fwrite(
                $this->socket,
                chr(($length >> 24) & 0xFF) .
                chr(($length >> 16) & 0xFF) .
                chr(($length >> 8) & 0xFF) .
                chr($length & 0xFF)
            );
        } else {
            fwrite(
                $this->socket,
                chr(0xF0) .
                chr(($length >> 24) & 0xFF) .
                chr(($length >> 16) & 0xFF) .
                chr(($length >> 8) & 0xFF) .
                chr($length & 0xFF)
            );
        }
    }

    private function readLength()
    {
        if (!$this->socket || !is_resource($this->socket)) {
            throw new \Exception("RouterOS socket is not connected. Cannot read length.");
        }

        $byte = fread($this->socket, 1);
        if ($byte === false || $byte === '') {
            return false;
        }

        $c = ord($byte);

        if ($c < 0x80) {
            return $c;
        } elseif (($c & 0xC0) === 0x80) {
            $c &= ~0xC0;
            $next = fread($this->socket, 1);
            if ($next === false || $next === '') return false;
            return ($c << 8) + ord($next);
        } elseif (($c & 0xE0) === 0xC0) {
            $c &= ~0xE0;
            $b1 = fread($this->socket, 1);
            $b2 = fread($this->socket, 1);
            if ($b1 === false || $b2 === false) return false;
            return ($c << 16) + (ord($b1) << 8) + ord($b2);
        } elseif (($c & 0xF0) === 0xE0) {
            $c &= ~0xF0;
            $b1 = fread($this->socket, 1);
            $b2 = fread($this->socket, 1);
            $b3 = fread($this->socket, 1);
            if ($b1 === false || $b2 === false || $b3 === false) return false;
            return ($c << 24) + (ord($b1) << 16) + (ord($b2) << 8) + ord($b3);
        } elseif (($c & 0xF8) === 0xF0) {
            $b1 = fread($this->socket, 1);
            $b2 = fread($this->socket, 1);
            $b3 = fread($this->socket, 1);
            $b4 = fread($this->socket, 1);
            if ($b1 === false || $b2 === false || $b3 === false || $b4 === false) return false;
            return (ord($b1) << 24) + (ord($b2) << 16) + (ord($b3) << 8) + ord($b4);
        }

        return false;
    }
}