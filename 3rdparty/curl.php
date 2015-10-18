<?php
/**
 * This class is a handy wrapper for using curl
 * @author original author http://php.net/manual/fr/book.curl.php#90821
 * modified bye
 * @author Jérémy Munsch <jeremy.munsch@gmail.com>
 * @copyright https://creativecommons.org/publicdomain/mark/1.0/
 */
class Curl
{
        protected $_useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1';
        protected $_url;
        protected $_followlocation;
        protected $_timeout;
        protected $_maxRedirects;
        protected $_cookieFileLocation = null;
        protected $_post;
        protected $_postFields;
        protected $_referer = "http://www.google.com";
        protected $_debug = false;

        protected $_session;
        protected $_webpage;
        protected $_includeHeader;
        protected $_noBody;
        protected $_status;
        protected $_contentType;
        protected $_infos;
        protected $_binaryTransfer;
        public $authentication = 0;
        public $auth_name      = '';
        public $auth_pass      = '';

        public function useAuth($use)
        {
            $this->authentication = 0;
            if ($use == true) {
                $this->authentication = 1;
            }
        }

        public function setName($name)
        {
            $this->auth_name = $name;
        }

        public function setPass($pass)
        {
            $this->auth_pass = $pass;
        }

        public function __construct($url, $followlocation = true, $timeOut = 30, $maxRedirecs = 4, $binaryTransfer = false, $includeHeader = false, $noBody = false)
        {
            $this->_url = $url;
            $this->_followlocation = $followlocation;
            $this->_timeout = $timeOut;
            $this->_maxRedirects = $maxRedirecs;
            $this->_noBody = $noBody;
            $this->_includeHeader = $includeHeader;
            $this->_binaryTransfer = $binaryTransfer;
        }

        public function setDebug($debug)
        {
            $this->_debug = $debug;
        }

        public function setReferer($referer)
        {
            $this->_referer = $referer;
        }

        public function setCookieFileLocation($path)
        {
            $this->_cookieFileLocation = $path;
        }

        public function setPost ($postFields)
        {
            $this->_post = true;
            $this->_postFields = $postFields;
        }

        public function setUserAgent($userAgent)
        {
            $this->_useragent = $userAgent;
        }

        public function createCurl($url = null)
        {
            if ($url) {
                $this->_url = $url;
            }

            $s = curl_init();

            curl_setopt($s, CURLOPT_URL, $this->_url);
            curl_setopt($s, CURLOPT_HTTPHEADER, array('Expect:'));
            curl_setopt($s, CURLOPT_TIMEOUT, $this->_timeout);
            curl_setopt($s, CURLOPT_MAXREDIRS, $this->_maxRedirects);
            curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($s, CURLOPT_FOLLOWLOCATION, $this->_followlocation);

            if ($this->_debug) {
                curl_setopt($s, CURLOPT_VERBOSE, true);
            }

            if ($this->_cookieFileLocation) {
                curl_setopt($s, CURLOPT_COOKIEJAR, $this->_cookieFileLocation);
                curl_setopt($s, CURLOPT_COOKIEFILE, $this->_cookieFileLocation);
            }

            if ($this->authentication == 1) {
                curl_setopt($s, CURLOPT_USERPWD, $this->auth_name.':'.$this->auth_pass);
            }
            if ($this->_post) {
                curl_setopt($s, CURLOPT_POST, true);
                curl_setopt($s, CURLOPT_POSTFIELDS, $this->_postFields);

            }

            if ($this->_includeHeader) {
                curl_setopt($s, CURLOPT_HEADER, true);
            }

            if ($this->_noBody) {
                curl_setopt($s, CURLOPT_NOBODY, true);
            }
            if($this->_binaryTransfer) {
             curl_setopt($s,CURLOPT_BINARYTRANSFER,true);
            }
            curl_setopt($s, CURLOPT_USERAGENT, $this->_useragent);
            curl_setopt($s, CURLOPT_REFERER, $this->_referer);

            $this->_webpage = curl_exec($s);
            $this->_status = curl_getinfo($s, CURLINFO_HTTP_CODE);
            $this->_contentType = curl_getinfo($s, CURLINFO_CONTENT_TYPE);
            $this->_infos = curl_getinfo($s);

            curl_close($s);

        }

        public function getHttpStatus()
        {
            return $this->_status;
        }

        public function getContentType()
        {
            return $this->_contentType;
        }

        public function getInfos()
        {
            return $this->_infos;
        }

        public function getDebug()
        {
            return $this->_infos;
        }

        public function __tostring()
        {
            return $this->_webpage;
        }
}
