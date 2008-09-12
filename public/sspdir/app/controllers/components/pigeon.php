<?php

class PigeonComponent extends Object {
        var $controller = true;
        var $curl                 = false;
        var $host                 = '';
        var $hole                 = '';

    function startup (&$controller) {
        $this->controller = &$controller;
                $this->curl = in_array('curl', get_loaded_extensions());
    }

        function activate($key, $xfer = false) {

        }

        function news() {

        }

        function version() {

        }

        function quick_start() {

        }

        function __parseTag($tag, $haystack, $all = false) {
                $pattern = '/\<' . $tag . '\>(.+)\<\/' . $tag . '\>/iUs';
                if ($all) {
                        preg_match_all($pattern, $haystack, $matches);
                } else {
                        preg_match($pattern, $haystack, $matches);
                }
                return $matches[1];
        }

        function isLocal() {
                return (preg_match('/^(127\.0\.0\.1|localhost)(:\d+)?$/i', $this->baseDomain())) ? true : false;
        }

        function baseDomain() {
                return preg_replace('/(^www\.|:\d+$)/', '', env('HTTP_HOST'));
        }

        function _ping($action, $post = false) {
                $pinger = "X-director-ping: $action";
                if ($this->curl) {
                        $handle        = curl_init("http://{$this->host}{$this->hole}");
                        curl_setopt($handle, CURLOPT_HTTPHEADER, array($pinger));
                        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
                        curl_setopt($handle, CURLOPT_PORT, 80);
                        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
                        if ($post) {
                                curl_setopt($handle, CURLOPT_POST, true);
                                curl_setopt($handle, CURLOPT_POSTFIELDS, $post);
                        }
                        $response = curl_exec($handle);
                        if (curl_errno($handle)) {
                                $error = 'Could not connect (using cURL): ' . curl_error($handle);
                        }
                        curl_close($handle);
                } else {
                        $headers = ($post ? 'POST' : 'GET') . " {$this->hole} HTTP/1.0\r\n";
                        $headers .= "Host: {$this->host}\r\n";
                        $headers .= "{$pinger}\r\n";
                        if ($post) {
                                $headers .= "Content-type: application/x-www-form-urlencoded\r\n";
                                $headers .= "Content-length: " . strlen($post) . "\r\n";
                        }
                        $headers .= "\r\n";

                        $socket = @fsockopen($this->host, 80, $errno, $errstr, 15);

                        if ($socket) {
                                $towrite = $headers;
                                if ($post) { $towrite .= $post; }
                                fwrite($socket, $towrite);
                                $response = '';
                                while (!feof($socket)) {
                                        $response .= fgets ($socket, 1024);
                                }
                                $response = explode("\r\n\r\n", $response, 2);
                                $response = trim($response[1]);
                        } else {
                                $error = 'Could not connect (using fsockopen): '.$errstr.' ('.$errno.')';
                                $response = 'FAILED';
                        }
                }
                if ($response != 'SUCCESS') {
                        if (isset($error)) {
                                return $error;
                        } else {
                                return $response;
                        }
                } else {
                        return false;
                }
        }
}

?>