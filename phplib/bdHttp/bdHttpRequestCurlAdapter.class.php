<?php
/***************************************************************************
 *
 * Copyright (c) 2009 Baidu.com, Inc. All Rights Reserved
 * $Id: bdHttpRequestCurlAdapter.class.php,v 1.4 2010/05/12 08:52:07 zhujt Exp $
 *
 **************************************************************************/

/**
 * This software is derived from HTTP_Request2 package(http://pear.php.net/package/HTTP_Request2),
 * following is the retained LICENSE for HTTP_Rquest2:
 *
 * LICENSE:
 *
 * Copyright (c) 2008, 2009, Alexey Borzov <avb@php.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTTP
 * @package    HTTP_Request2
 * @author     Alexey Borzov <avb@php.net>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: bdHttpRequestCurlAdapter.class.php,v 1.4 2010/05/12 08:52:07 zhujt Exp $
 * @link       http://pear.php.net/package/HTTP_Request2
 */


require_once 'bdHttpResponse.class.php';

/**
 * Base class for bdHttpRequest adapters
 */
require_once 'bdHttpRequestAdapter.class.php';

/**
 * Exception class for bdHttp package
 */
require_once 'bdHttpException.class.php';

/**
 * Adapter for bdHttpRequest wrapping around cURL extension
 *
 * @category	Networking
 * @package		bdHttp
 * @author		zhujt <zhujianting@baidu.com>
 * @version		$Revision: 1.4 $
 */
class bdHttpRequestCurlAdapter extends bdHttpRequestAdapter
{
	/**
	 * Mapping of header names to cURL options
	 * @var array
	 */
    protected static $headerMap = array(
        'accept-encoding' => CURLOPT_ENCODING,
        'cookie'          => CURLOPT_COOKIE,
        'referer'         => CURLOPT_REFERER,
        'user-agent'      => CURLOPT_USERAGENT
    );

    /**
     * Mapping of SSL context options to cURL options
     * @var  array
     */
    protected static $sslContextMap = array(
        'ssl_verify_peer' => CURLOPT_SSL_VERIFYPEER,
        'ssl_cafile'      => CURLOPT_CAINFO,
        'ssl_capath'      => CURLOPT_CAPATH,
        'ssl_local_cert'  => CURLOPT_SSLCERT,
        'ssl_passphrase'  => CURLOPT_SSLCERTPASSWD
   );

   /**
    * Response being received
    * @var  bdHttpResponse
    */
    protected $response;

    /**
     * Whether 'sentHeaders' event was sent to observers
     * @var  boolean
     */
    protected $eventSentHeaders = false;

    /**
     * Whether 'receivedHeaders' event was sent to observers
     * @var boolean
     */
    protected $eventReceivedHeaders = false;

    /**
     * Position within request body
     * @var integer
     * @see onReadBody()
     */
    protected $position = 0;

    /**
     * Information about last transfer, as returned by curl_getinfo()
     * @var array
     */
    protected $lastInfo;

    /**
     * Whether response body length is larger than $this->request->getConfig('max_response_size')
     * @var boolean
     */
    protected $exceedMaxSizeLimit = false;

    /**
     * The minimum length of the response body
     * @var integer
     */
    protected $miniContentLength = 0;

    /**
     * The max response size limit
     * @var integer
     */
    protected $maxResponseSize;

    /**
     * The requested url
     * @var string
     */
    protected $requestUrl;

    /**
     * Sends request to the remote server and returns its response
     *
     * @param bdHttpRequest $request
     * @return bdHttpResponse
     * @throws bdHttpException
     */
    public function sendRequest(bdHttpRequest $request)
    {
        if (!extension_loaded('curl')) {
            throw new bdHttpException('cURL extension not available');
        }

        $this->request = $request;
        $this->response = null;
        $this->position = 0;
        $this->eventSentHeaders = false;
        $this->eventReceivedHeaders = false;
        $this->exceedMaxSizeLimit = false;
        $this->miniContentLength = 0;
        $this->maxResponseSize = $request->getConfig('max_response_size');
        $this->requestUrl = $request->getUrl()->getURL();

        try {
        	$ch = $this->createCurlHandle();
        	curl_exec($ch);
        	/*
            if (false === curl_exec($ch)) {
                $errorMessage = 'Error sending request: #' . curl_errno($ch) .
                                                       ' ' . curl_error($ch);
            }*/
        } catch (Exception $e) {

        }
        $errno = curl_errno($ch);
        $errmsg = curl_error($ch);
        $this->lastInfo = curl_getinfo($ch);
        curl_close($ch);
        $response = $this->response;
        unset($this->request, $this->requestBody, $this->response);
        if (is_object($response) && $response instanceof bdHttpResponse) {
            $response->setCurlInfo($this->lastInfo);
        }
        if (!empty($e)) {
            throw $e;
        } else {
        	$this->resolveHttpResponse($this->requestUrl, $errno, $errmsg);
        }

        if (0 < $this->lastInfo['size_download']) {
            $request->setLastEvent('receivedBody', $response);
        }
        return $response;
    }

    /**
     * Returns information about last transfer
     *
     * @return array	associative array as returned by curl_getinfo()
     */
    public function getInfo()
    {
        return $this->lastInfo;
    }

    protected function resolveHttpResponse($url, $errno, $errmsg)
    {
    	$url = htmlspecialchars($url, ENT_QUOTES);
		$http_code = $this->lastInfo['http_code'];

		if ($errno == CURLE_URL_MALFORMAT ||
			$errno == CURLE_COULDNT_RESOLVE_HOST) {
			throw new bdHttpURLInvalidException($url, $errmsg);
		} elseif ($errno == CURLE_COULDNT_CONNECT) {
			throw new bdHttpServiceInvalidException($url, $errmsg, $errno);
		} elseif ($errno == 28/*CURLE_OPERATION_TIMEDOUT*/) {
			throw new bdHttpRequestTimeoutException($url, $errmsg);
		} elseif ($errno == CURLE_TOO_MANY_REDIRECTS ||
				$http_code == 301 || $http_code == 302 || $http_code == 307) {
			//$errno == CURLE_OK can only indicate that the response is received, but it may
			//also be an error page or empty page, so we also need more checking when $errno == CURLE_OK
			throw new bdHttpRedirectsTooManyException($url);
		} elseif ($http_code >= 400) {
			throw new bdHttpErrorPageException($url, $http_code);
		} elseif ($this->miniContentLength > $this->maxResponseSize) {
			throw new bdHttpResponseTooLargeException($url, $this->miniContentLength, $this->maxResponseSize);
		} elseif ($errno != CURLE_OK) {
			if ($this->miniContentLength == 0 && !$http_code) {
				throw new bdHttpNoResponseException($url);
			} else {
				throw new bdHttpException($errmsg, $errno);
			}
		}
    }

    /**
     * Creates a new cURL handle and populates it with data from the request
     *
     * @return resouce	a cURL handle, as created by curl_init()
     * @throws bdHttpException
     */
    protected function createCurlHandle()
    {
        $ch = curl_init();

        $bdurl = $this->request->getUrl();
        $config = $this->request->getConfig();

      	$curl_opts = array(
      		// request url
      		CURLOPT_URL				=> $this->requestUrl,
      		// setup write callbacks
      		CURLOPT_HEADERFUNCTION	=> array($this, 'onWriteHeader'),
      		CURLOPT_WRITEFUNCTION	=> array($this, 'onWriteBody'),
      		// buffer size
      		CURLOPT_BUFFERSIZE		=> $config['buffer_size'],
      		// save full outgoing headers, in case someone is interested
            CURLINFO_HEADER_OUT		=> true,
            CURLOPT_NOSIGNAL => 1,
            );

        // setup connection timeout
		if (defined('CURLOPT_CONNECTTIMEOUT_MS')) {
			$curl_opts[CURLOPT_CONNECTTIMEOUT_MS] = $config['connect_timeout'];
		} else {
			$curl_opts[CURLOPT_CONNECTTIMEOUT] = ceil($config['connect_timeout'] / 1000);
		}

        // setup request timeout
		if (defined('CURLOPT_TIMEOUT_MS')) {
			$curl_opts[CURLOPT_TIMEOUT_MS] = $config['timeout'];
		} else {
			$curl_opts[CURLOPT_TIMEOUT] = ceil($config['timeout'] / 1000);
		}

        // setup redirects
        if ($config['follow_redirects']) {
        	$curl_opts[CURLOPT_FOLLOWLOCATION] = true;
            $curl_opts[CURLOPT_MAXREDIRS] = $config['max_redirects'];
            // limit redirects to http(s), works in 5.2.10+
            if (defined('CURLOPT_REDIR_PROTOCOLS')) {
                $curl_opts[CURLOPT_REDIR_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
            }
            // works sometime after 5.3.0, http://bugs.php.net/bug.php?id=49571
            if ($config['strict_redirects'] && defined('CURLOPT_POSTREDIR ')) {
                $curl_opts[CURLOPT_POSTREDIR] = 3;
            }
        } else {
            $curl_opts[CURLOPT_FOLLOWLOCATION] = false;
        }

        // set HTTP version
        switch ($config['protocol_version']) {
            case '1.0':
                $curl_opts[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_0;
                break;
            case '1.1':
                $curl_opts[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
        }

        // set request method
        switch ($this->request->getMethod()) {
            case bdHttpRequest::METHOD_GET:
                $curl_opts[CURLOPT_HTTPGET] = true;
                break;
            case bdHttpRequest::METHOD_POST:
                $curl_opts[CURLOPT_POST] = true;
                break;
            case bdHttpRequest::METHOD_HEAD:
                $curl_opts[CURLOPT_NOBODY] = true;
                break;
            default:
                $curl_opts[CURLOPT_CUSTOMREQUEST] = $this->request->getMethod();
        }

        // set proxy, if needed
        if ($config['proxy_host']) {
            if (!$config['proxy_port']) {
                throw new bdHttpException('Proxy port not provided');
            }
            $curl_opts[CURLOPT_PROXY] = $config['proxy_host'] . ':' . $config['proxy_port'];
            if ($config['proxy_user']) {
                $curl_opts[CURLOPT_PROXYUSERPWD] = $config['proxy_user'] . ':' . $config['proxy_password'];
                switch ($config['proxy_auth_scheme']) {
                    case bdHttpRequest::AUTH_BASIC:
                        $curl_opts[CURLOPT_PROXYAUTH] = CURLAUTH_BASIC;
                        break;
                    case bdHttpRequest::AUTH_DIGEST:
                        $curl_opts[CURLOPT_PROXYAUTH] = CURLAUTH_DIGEST;
                }
            }
        }

        // set authentication data
        $auth = $this->request->getAuth();
        if ($auth) {
            $curl_opts[CURLOPT_USERPWD] = $auth['user'] . ':' . $auth['password'];
            switch ($auth['scheme']) {
                case bdHttpRequest::AUTH_BASIC:
                    $curl_opts[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
                    break;
                case bdHttpRequest::AUTH_DIGEST:
                    $curl_opts[CURLOPT_HTTPAUTH] = CURLAUTH_DIGEST;
            }
        }

        // set SSL options
        if (0 == strcasecmp($bdurl->getScheme(), 'https')) {
        	if (isset($config['ssl_verify_host'])) {
        		$curl_opts[CURLOPT_SSL_VERIFYHOST] = $config['ssl_verify_host'] ? 2 : 0;
        	}
        	foreach (self::$sslContextMap as $name => $option) {
        		if (isset($config[$name])) {
        			$curl_opts[$option] = $config[$name];
        		}
        	}
        }

        $headers = $this->request->getHeaders();
        // make cURL automagically send proper header
        if (!isset($headers['accept-encoding'])) {
            $headers['accept-encoding'] = '';
        }

        // set headers having special cURL keys
        foreach (self::$headerMap as $name => $option) {
            if (isset($headers[$name])) {
                $curl_opts[$option] = $headers[$name];
                unset($headers[$name]);
            }
        }

        $this->calculateRequestLength($headers);
        if (isset($headers['content-length'])) {
            $this->workaroundPhpBug47204($curl_opts, $headers);
        }

        // set headers not having special keys
        $headersFmt = array();
        foreach ($headers as $name => $value) {
            $canonicalName = implode('-', array_map('ucfirst', explode('-', $name)));
            $headersFmt[]  = $canonicalName . ': ' . $value;
        }
        $curl_opts[CURLOPT_HTTPHEADER] = $headersFmt;

        curl_setopt_array($ch, $curl_opts);

        return $ch;
    }

    /**
     * Workaround for PHP bug #47204 that prevents rewinding request body
     *
     * The workaround consists of reading the entire request body into memory
     * and setting it as CURLOPT_POSTFIELDS, so it isn't recommended for large
     * file uploads, use Socket adapter instead.
     *
     * @param array $curl_opts	cURL options
     * @param array $headers	Request header
     */
    protected function workaroundPhpBug47204(&$curl_opts, &$headers)
    {
    	$auth = $this->request->getAuth();

        // no redirects, no digest auth -> probably no rewind needed
        if (!$this->request->getConfig('follow_redirects') &&
        	(!$auth || bdHttpRequest::AUTH_DIGEST != $auth['scheme'])) {
            $curl_opts[CURLOPT_READFUNCTION] = array($this, 'onReadBody');

        // rewind may be needed, read the whole body into memory
        } else {

            if ($this->requestBody instanceof bdHttpRequestMultipartBody) {
                $this->requestBody = $this->requestBody->__toString();
            } elseif (is_resource($this->requestBody)) {
                $fp = $this->requestBody;
                $this->requestBody = '';
                while (!feof($fp)) {
                    $this->requestBody .= fread($fp, 16384);
                }
            }

            // curl hangs up if content-length is present
            unset($headers['content-length']);
            // curl failed to process an empty postfields,
            // so we send * when empty postfields specified
            if (empty($this->requestBody)) {
            	$curl_opts[CURLOPT_POSTFIELDS] = '*';
            } else {
            	$curl_opts[CURLOPT_POSTFIELDS] = $this->requestBody;
            }
        }
    }

    /**
     * Callback function called by cURL for reading the request body
     *
     * @param resource $ch	cURL handle
     * @param resource $fd	file descriptor (not used)
     * @param integer $length	maximum length of data to return
     * @return string	part of the request body, up to $length bytes
     */
    protected function onReadBody($ch, $fd, $length)
    {
        if (!$this->eventSentHeaders) {
            $this->request->setLastEvent('sentHeaders', curl_getinfo($ch, CURLINFO_HEADER_OUT));
            $this->eventSentHeaders = true;
        }
        if (in_array($this->request->getMethod(), self::$bodyDisallowed) ||
        	0 == $this->contentLength ||
        	$this->position >= $this->contentLength) {
            return '';
        }
        if (is_string($this->requestBody)) {
            $string = substr($this->requestBody, $this->position, $length);
        } elseif (is_resource($this->requestBody)) {
            $string = fread($this->requestBody, $length);
        } else {
            $string = $this->requestBody->read($length);
        }
        $this->request->setLastEvent('sentBodyPart', strlen($string));
        $this->position += strlen($string);
        return $string;
    }

    /**
     * Callback function called by cURL for saving the response headers
     *
     * @param	resource $ch	cURL handle
     * @param	string $header	response header (with trailing CRLF)
     * @return	integer			number of bytes saved
     * @see		bdHttpResponse::parseHeaderLine()
     */
    protected function onWriteHeader($ch, $header)
    {
    	$config = $this->request->getConfig();

        // we may receive a second set of headers if doing e.g. digest auth
        if ($this->eventReceivedHeaders || !$this->eventSentHeaders) {
            // don't bother with 100-Continue responses (bug #15785)
            if (!$this->eventSentHeaders || $this->response->getStatus() >= 200) {
                $this->request->setLastEvent('sentHeaders', curl_getinfo($ch, CURLINFO_HEADER_OUT));
            }
            $upload = curl_getinfo($ch, CURLINFO_SIZE_UPLOAD);
            // if body wasn't read by a callback, send event with total body size
            if ($upload > $this->position) {
                $this->request->setLastEvent('sentBodyPart', $upload - $this->position);
                $this->position = $upload;
            }
            $this->eventSentHeaders = true;
            // we'll need a new response object
            if ($this->eventReceivedHeaders) {
                $this->eventReceivedHeaders = false;
                $this->response = null;
            }
        }
        if (empty($this->response)) {
            $this->response = new bdHttpResponse($header, false);
        } else {
            $this->response->parseHeaderLine($header);
            if ('' == trim($header)) {
                // don't bother with 100-Continue responses (bug #15785)
                if (200 <= $this->response->getStatus()) {
                    $this->request->setLastEvent('receivedHeaders', $this->response);
                }
                // for versions lower than 5.2.10, check the redirection URL protocol
                if ($config['follow_redirects'] && !defined('CURLOPT_REDIR_PROTOCOLS') &&
                	$this->response->isRedirect()) {
                    $redirectUrl = new bdURL($this->response->getHeader('location'));
                    if ($redirectUrl->isAbsolute() &&
                    	!in_array($redirectUrl->getScheme(), array('http', 'https'))) {
                        return -1;
                    }
                }
                // check the max response size limit
                $contentLength = intval($this->response->getHeader('Content-Length'));
                if ($contentLength > $config['max_response_size']) {
                	$this->miniContentLength = $contentLength;
                	$this->exceedMaxSizeLimit = true;
                	return -1;
                }
                $this->eventReceivedHeaders = true;
            }
        }
        return strlen($header);
    }

    /**
     * Callback function called by cURL for saving the response body
     *
     * @param resource	$ch		cURL handle (not used)
     * @param string	$body	part of the response body
     * @return	integer	number of bytes saved
     * @throws	bdHttpException if response doesn't start with proper HTTP status line
     * @see	bdHttpResponse::appendBody
     */
    protected function onWriteBody($ch, $body)
    {
        // cURL calls WRITEFUNCTION callback without calling HEADERFUNCTION if
        // response doesn't start with proper HTTP status line (see bug #15716)
        if (empty($this->response)) {
            throw new bdHttpException("Malformed response: {$body}");
        }

        $this->response->appendBody($body);
        $this->request->setLastEvent('receivedBodyPart', $body);

        $chunckSize = strlen($body);
        $this->miniContentLength += $chunckSize;
        if ($this->miniContentLength <= $this->request->getConfig('max_response_size')) {
        	return $chunckSize;
        } else {
        	$this->exceedMaxSizeLimit = true;
        	return 0;
        }
    }
}


/* vim: set ts=4 sw=4 sts=4 tw=90 noet: */
?>
