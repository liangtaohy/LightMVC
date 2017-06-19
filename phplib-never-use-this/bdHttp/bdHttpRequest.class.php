<?php
/***************************************************************************
 * 
 * Copyright (c) 2009 Baidu.com, Inc. All Rights Reserved
 * $Id: bdHttpRequest.class.php,v 1.3 2010/05/12 08:52:07 zhujt Exp $ 
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
 * @version    SVN: $Id: bdHttpRequest.class.php,v 1.3 2010/05/12 08:52:07 zhujt Exp $
 * @link       http://pear.php.net/package/HTTP_Request2
 */

/**
 * A class representing an URL as per RFC 3986.
 */
require_once 'bdURL.class.php';

/**
 * Exception class for bdHttp package
 */
require_once 'bdHttpException.class.php';

/**
 * Class representing a HTTP request message
 * 
 * @category	Networking
 * @package		bdHttp
 * @author		zhut <zhujianting@baidu.com>
 * @version		$Revision: 1.3 $
 * @link		http://tools.ietf.org/html/rfc2616#section-6
 */
class bdHttpRequest implements SplSubject
{
	/**#@+
	 * Constants for HTTP request methods
	 * 
	 * @link http://tools.ietf.org/html/rfc2616#section-5.1.1
	 */
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET     = 'GET';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_TRACE   = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';
    /**#@-*/
    
    /**#@+
     * Constants for HTTP authentication schemes
     * 
     * @link http://tools.ietf.org/html/rfc2617
     */
    const AUTH_BASIC  = 'basic';
    const AUTH_DIGEST = 'digest';
    /**#@-*/
    
    /**
     * Regular expression used to check for invalid symbols in RFC 2616 tokens
     * @link http://pear.php.net/bugs/bug.php?id=15630
     */
    const REGEXP_INVALID_TOKEN = '![\x00-\x1f\x7f-\xff()<>@,;:\\\\"/\[\]?={}\s]!';
    
    /**
     * Regular expression used to check for invalid symbols in cookie strings
     * @link http://pear.php.net/bugs/bug.php?id=15630
     */
    const REGEXP_INVALID_COOKIE = '/[\s,;]/';
    
    /**
     * Configuration parameters
     * @var  array
     * @see  setConfig()
     */
    protected $config = array(
    	'adapter'			=> 'curl',
    	'connect_retry'		=> 3,		
    	'connect_timeout'   => 1000,	
    	'timeout'           => 3000,
    	'follow_redirects'	=> true,
    	'max_redirects'		=> 3,
    	'max_response_size'	=> 512000,
    
    	'protocol_version'  => '1.1',
        'buffer_size'       => 16384,

        'proxy_host'        => '',
        'proxy_port'        => '',
        'proxy_user'        => '',
        'proxy_password'    => '',
        'proxy_auth_scheme' => self::AUTH_BASIC,

        'ssl_verify_peer'   => false,
        'ssl_verify_host'   => false,
        'ssl_cafile'        => null,
        'ssl_capath'        => null,
        'ssl_local_cert'    => null,
        'ssl_passphrase'    => null,
    
    	'use_brackets'		=> true,
    	'strict_redirects'	=> false,
    );
    
    /**
     * Request URL
     * @var  bdURL
     */
    protected $url;
    
    /**
     * Request method
     * @var  string
     */
    protected $method = self::METHOD_GET;
    
    /**
     * Authentication data
     * @var  array
     * @see  getAuth()
     */
    protected $auth;
    
    /**
     * Request headers
     * @var  array
     */
    protected $headers = array();
    
    /**
     * Request body
     * @var  string
     */
    protected $body = '';
    
    /**
     * Array of POST parameters
     * @var  array
     */
    protected $postParams = array();
    
    /**
     * Array of bdHttpRequest Observers
     * @var array
     */
    protected $observers = array();
    
	/**
	 * Constructor. Can set request URL, method and configuration array.
	 * 
	 * Also sets a default value for User-Agent & Referer header.
	 * 
	 * @param string|bdURL	Request URL
	 * @param string			Request method
	 * @param array			Configuration for this Request instance
	 */
    public function __construct($url = null, $method = self::METHOD_GET, array $config = array())
    {
        $this->setConfig($config);
        
        if (!empty($url)) {
            $this->setUrl($url);
        }
        if (!empty($method)) {
        	$this->setMethod($method);
        }
        
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'bdHttpRequest/1.0.0'; 
		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : bdURL::getRequestedURL();
		
		$this->setHeaders(array('user-agent' => $user_agent, 'referer' => $referer));
    }
    
	/**
	 * Sets the URL for this request
	 * 
	 * If the URL has userinfo part (username & password) these will be removed
	 * and converted to auth data. If the URL does not have a path component,
	 * that will be set to '/'.
	 * 
	 * @param    string|bdURL Request URL
	 * @return   bdHttpRequest	self instance
	 * @throws   bdHttpException
	 */
    public function setUrl($url)
    {
        if (is_string($url)) {
            $url = new bdURL($url, array(bdURL::OPTION_USE_BRACKETS => $this->config['use_brackets']));
        }
        if (!$url instanceof bdURL) {
            throw new bdHttpException('Parameter is not a valid HTTP URL');
        }
        
        // URL contains username / password?
        $user = $url->getUser();
        $pass = $url->getPassword();
        if ($user || $pass) {
            $this->setAuth(rawurldecode($user), rawurldecode($pass));
            $url->setUserinfo('');
        }
        if ('' == $url->getPath()) {
            $url->setPath('/');
        }
        $this->url = $url;

        return $this;
    }

	/**
	 * Returns the request URL
	 * 
	 * @return bdURL
	 */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Sets the request method
     * 
     * @param    string $method
     * @return   bdHttpRequest	self instance
     * @throws   bdHttpException if the method name is invalid
     */
    public function setMethod($method)
    {
        // Method name should be a token: http://tools.ietf.org/html/rfc2616#section-5.1.1
        if (preg_match(self::REGEXP_INVALID_TOKEN, $method)) {
            throw new bdHttpException("Invalid request method '{$method}'");
        }
        $this->method = $method;

        return $this;
    }
    
    /**
     * Returns the request method
     * 
     * @return   string
     */
    public function getMethod()
    {
        return $this->method;
    }
    
	/**
	 * Returns the request headers
	 * 
	 * The array is of the form ('header name' => 'header value'), header names
	 * are lowercased
	 * 
	 * @return   array
	 */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Set request headers
     * Header with invalid token will not be set
     * 
     * @param array	$headers	associated array specify request headers and their values
     * @return bdHttpRequest	self instance
     * @throws bdHttpException if any header name invalid
     */
    public function setHeaders(array $headers)
    {
    	foreach ($headers as $name => $value) {
    		//Header name should be a token: http://tools.ietf.org/html/rfc2616#section-4.2
    		if (preg_match(self::REGEXP_INVALID_TOKEN, $name)) {
    			throw new bdHttpException("Invalid header name '{$name}'");
    		}
    		$name = strtolower($name);
    		if (null === $value) {
    			unset($this->headers[$name]);
    		} else {
    			$this->headers[$name] = $value;
    		}
    	}
    	
    	return $this;
    }
    
    /**
     * Set named request header to specified value
     * 
     * @param string		$name	header name or a request header line, ex. user-aget: firefox/3.5.0
     * @param string|null	$value	request header value
     * @return bdHttpRequest	self instance
     * @throws bdHttpException if header name invalid
     */
 	public function setHeader($name, $value = null)
    {
    	if (null === $value && strpos($name, ':')) {
    		list($name, $value) = array_map('trim', explode(':', $name, 2));
    	}
    	// Header name should be a token: http://tools.ietf.org/html/rfc2616#section-4.2
    	if (preg_match(self::REGEXP_INVALID_TOKEN, $name)) {
    		//return $this;
    		throw new bdHttpException("Invalid header name '{$name}'");
    	}
    	// Header names are case insensitive anyway
    	$name = strtolower($name);
    	if (null === $value) {
    		unset($this->headers[$name]);
    	} else {
    		$this->headers[$name] = $value;
    	}

        return $this;
    }
    
    /**
     * Sets the autentification data
     * 
     * @param	string  user name
     * @param    string  password
     * @param    string  authentication scheme
     * @return   bdHttpRequest	self instance
     */
    public function setAuth($user, $password = '', $scheme = self::AUTH_BASIC)
    {
        if (empty($user)) {
            $this->auth = null;
        } else {
            $this->auth = array(
                'user'     => (string)$user,
                'password' => (string)$password,
                'scheme'   => $scheme
            );
        }

        return $this;
    }
    
    /**
     * Returns the authentication data
     * 
     * The array has the keys 'user', 'password' and 'scheme', where 'scheme'
     * is one of the bdHttpRequest::AUTH_* constants.
     * 
     * @return   array
     */
    public function getAuth()
    {
        return $this->auth;
    }
    
    /**
     * Appends a cookie to "Cookie:" header
     * 
     * @param    string  cookie name
     * @param    string  cookie value
     * @return   bdHttpRequest	self instance
     * @throws   bdHttpException if the cookie name is invalid
     */
    public function addCookie($name, $value)
    {
        $cookie = $name . '=' . $value;
        if (preg_match(self::REGEXP_INVALID_COOKIE, $cookie)) {
            throw new bdHttpException("Invalid cookie: '{$cookie}'");
        }
        $cookies = empty($this->headers['cookie'])? '': $this->headers['cookie'] . '; ';
        $this->setHeader('cookie', $cookies . $cookie);

        return $this;
    }

	/**
	 * Adds POST parameter(s) to the request.
	 * 
	 * @param    string|array    parameter name or array ('name' => 'value')
	 * @param    mixed           parameter value (can be an array)
	 * @return   bdHttpRequest	self instance
	 */
    public function addPostParameter($name, $value = null)
    {
        if (!is_array($name)) {
            $this->postParams[$name] = $value;
        } else {
            foreach ($name as $k => $v) {
            	$this->postParams[$k] = $v;
            }
        }
        if (empty($this->headers['content-type'])) {
            $this->setHeader('content-type', 'application/x-www-form-urlencoded');
        }

        return $this;
    }
    
    /**
     * Sets the request body
     * Currently we do not support file upload.
     * 
     * @param    string  Either a string with the body or filename containing body
     * @param    bool    Whether first parameter is a filename
     * @return   bdHttpRequest	self instance
     */
    public function setBody($body)
    {
    	$this->body = $body;
    	$this->postParams = array();
        
        return $this;
    }
    
    /**
     * Returns the request body
     * 
     * @return   string|resource|bdHttpRequestMultipartBody
     */
    public function getBody()
    {
        if (self::METHOD_POST == $this->method && !empty($this->postParams)) {
            //if ('application/x-www-form-urlencoded' == $this->headers['content-type']) {
                $body = http_build_query($this->postParams, '', '&');
                if (!$this->getConfig('use_brackets')) {
                    $body = preg_replace('/%5B\d+%5D=/', '=', $body);
                }
                // support RFC 3986 by not encoding '~' symbol (request #15368)
                return str_replace('%7E', '~', $body);
            /*} elseif ('multipart/form-data' == $this->headers['content-type']) {
                require_once 'HTTP/Request2/MultipartBody.php';
                return new HTTP_Request2_MultipartBody(
                    $this->postParams, $this->uploads, $this->getConfig('use_brackets')
                );
            }*/
        }
        return $this->body;
    }
    
	/**
	 * Sets the configuration parameter(s)
	 * 
	 * The following parameters are available:
	 * <ul>
	 *  <li> 'adapter'           - adapter to use (string)</li>
	 *  <li> 'connect_retry'	 - Retry times for failed connection (integer)</li>
	 *  <li> 'connect_timeout'   - Connection timeout in milliseconds (integer)</li>
	 *  <li> 'timeout'           - Total number of milliseconds a request can take.
	 *                             Use 0 for no limit, should be greater than
	 *                             'connect_timeout' if set (integer)</li>
	 *	<li> 'follow_redirects'  - Whether to automatically follow HTTP Redirects (boolean)</li>
	 *	<li> 'max_redirects'     - Maximum number of redirects to follow (integer)</li>
	 *	<li> 'max_response_size' - Maxinum size of response body, default is 512k (integer)</li>
	 *  <li> 'protocol_version'  - HTTP Version to use, '1.0' or '1.1' (string)</li>
	 *  <li> 'buffer_size'       - Buffer size to use for reading and writing (int)</li>
	 *  <li> 'proxy_host'        - Proxy server host (string)</li>
	 *  <li> 'proxy_port'        - Proxy server port (integer)</li>
	 *  <li> 'proxy_user'        - Proxy auth username (string)</li>
	 *  <li> 'proxy_password'    - Proxy auth password (string)</li>
	 *  <li> 'proxy_auth_scheme' - Proxy auth scheme, one of HTTP_Request2::AUTH_* constants (string)</li>
	 *  <li> 'ssl_verify_peer'   - Whether to verify peer's SSL certificate (bool)</li>
	 *  <li> 'ssl_verify_host'   - Whether to check that Common Name in SSL
	 *  						   certificate matches host name (bool)</li>
	 *  <li> 'ssl_cafile'        - Cerificate Authority file to verify the peer
	 *                             with (use with 'ssl_verify_peer') (string)</li>
	 *	<li> 'ssl_capath'        - Directory holding multiple Certificate
	 *                             Authority files (string)</li>
	 *	<li> 'ssl_local_cert'    - Name of a file containing local cerificate (string)</li>
	 *	<li> 'ssl_passphrase'    - Passphrase with which local certificate
	 *                             was encoded (string)</li>
	 *	<li> 'use_brackets'		 - Whether to append [] to array variable names (bool)</li>
	 * </ul>
	 * 
	 * @param    string|array    configuration parameter name or array
	 *                           ('parameter name' => 'parameter value')
	 * @param    mixed           parameter value if $nameOrConfig is not an array
	 * @return   bdHttpRequest
	 * @throws   bdHttpException If the parameter is unknown
	 */
    public function setConfig($nameOrConfig, $value = null)
    {
        if (is_array($nameOrConfig)) {
            foreach ($nameOrConfig as $name => $value) {
            	if (!array_key_exists($name, $this->config)) {
            		throw new bdHttpException("Unknown configuration parameter '{$name}'");
            	}
                $this->config[$name] = $value;
            }
        } else {
        	if (!array_key_exists($nameOrConfig, $this->config)) {
                throw new bdHttpException("Unknown configuration parameter '{$nameOrConfig}'");
            }
            $this->config[$nameOrConfig] = $value;
        }

        return $this;
    }
    
    /**
     * Returns the value(s) of the configuration parameter(s)
     * 
     * @param    string  parameter name
     * @return   mixed   value of $name parameter, array of all configuration
     *                   parameters if $name is not given
     * @throws   bdHttpException If the parameter is unknown
     */
    public function getConfig($name = null)
    {
        if (null === $name) {
            return $this->config;
        } elseif (!array_key_exists($name, $this->config)) {
            throw new bdHttpException("Unknown configuration parameter '{$name}'");
        }
        return $this->config[$name];
    }
    
	/**
     * Add a new observer
     * 
     * @param SplObserver $observer
     */
    public function attach(SplObserver $observer)
    {
        foreach ($this->observers as $attached) {
            if ($attached === $observer) {
                return;
            }
        }
        $this->observers[] = $observer;
    }

    /**
     * Remove an existing observer
     * 
     * @param SplObserver $observer
     */
    public function detach(SplObserver $observer)
    {
        foreach ($this->observers as $key => $attached) {
            if ($attached === $observer) {
                unset($this->observers[$key]);
                return;
            }
        }
    }
    
    /**
     * Notifies all observers
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
    
    /**
     * Sets the last event
     * 
     * Adapters should use this method to set the current state of the request
     * and notify the observers.
     * 
     * @param    string  event name
     * @param    mixed   event data
     */
    public function setLastEvent($name, $data = null)
    {
        $this->lastEvent = array(
            'name' => $name,
            'data' => $data
        );
        $this->notify();
    }
    
    /**
     * Returns the last event
     * 
     * Observers should use this method to access the last change in request.
     * The following event names are possible:
     * <ul>
     * 	<li>'connect'                 - after connection to remote server,
     *                                  data is the destination (string)</li>
     *	<li>'disconnect'              - after disconnection from server</li>
     *	<li>'sentHeaders'             - after sending the request headers,
     *                                  data is the headers sent (string)</li>
     *	<li>'sentBodyPart'            - after sending a part of the request body,
     *                                  data is the length of that part (int)</li>
     *	<li>'receivedHeaders'         - after receiving the response headers,
     *                                  data is bdHttpResponse object</li>
     *	<li>'receivedBodyPart'        - after receiving a part of the response
     *                                  body, data is that part (string)</li>
     *	<li>'receivedEncodedBodyPart' - as 'receivedBodyPart', but data is still
     *                                  encoded by Content-Encoding</li>
     *	<li>'receivedBody'            - after receiving the complete response
     *                                  body, data is bdHttpResponse object</li>
     * </ul>
     * Different adapters may not send all the event types. Mock adapter does
     * not send any events to the observers.
     * 
     * @return   array   The array has two keys: 'name' and 'data'
     */
    public function getLastEvent()
    {
        return $this->lastEvent;
    }
    
    /**
     * Sets the adapter used to actually perform the request
     * 
     * You can pass either an instance of a class implementing bdHttpRequestAdapter
     * or a class name. The method will only try to include a file if the class
     * name is bdHttpRequestXxxAdapter format, example:
     * <code>
     * $request->setAdapter('curl');
     * </code>
     * will try to include bdHttpRequestCurlAdapter.class.php, and create a
     * bdHttpRequestCurlAdapter instance and set it as an adapter.
     * <code>
     * $adapter = new bdHttpRequestCurlAdapter();
     * $request->setAdapter($adapter);
     * </code>
     * will also work.
     * 
     * @param    string|bdHttpRequestAdapter
     * @return   bdHttpRequest	self instance
     * @throws   bdHttpException
     */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
        	if (ctype_alnum($adapter)) {
            	$adapter = 'bdHttpRequest' . ucfirst($adapter) . 'Adapter';
            }
            if (!class_exists($adapter, false)) {
            	include_once($adapter . '.class.php');
                if (!class_exists($adapter, false)) {
                    throw new bdHttpException("Class {$adapter} not found");
                }
            }
            $adapter = new $adapter;
        }
        if (!$adapter instanceof bdHttpRequestAdapter) {
            throw new bdHttpException('Parameter is not a HTTP request adapter');
        }
        $this->adapter = $adapter;

        return $this;
    }
    
    /**
     * Sends the request and returns the response
     * 
     * @return   bdHttpResponse
     * @throws   bdHttpException
     */
    public function send()
    {
        // Sanity check for URL
        if (!$this->url instanceof bdURL) {
            throw new bdHttpException('No URL given');
        } elseif (!$this->url->isAbsolute()) {
            throw new bdHttpException('Absolute URL required');
        } elseif (!in_array(strtolower($this->url->getScheme()), array('https', 'http'))) {
            throw new bdHttpException('Not a HTTP URL');
        }
        if (empty($this->adapter)) {
            $this->setAdapter($this->getConfig('adapter'));
        }
        // magic_quotes_runtime may break file uploads and chunked response processing
        $magicQuotes = get_magic_quotes_runtime();
        if ($magicQuotes) {
            set_magic_quotes_runtime(false);
        }
        // force using single byte encoding if mbstring extension overloads
        // strlen() and substr()
        if (extension_loaded('mbstring') && (2 & ini_get('mbstring.func_overload'))) {
            $oldEncoding = mb_internal_encoding();
            mb_internal_encoding('iso-8859-1');
        }

        try {
            $response = $this->adapter->sendRequest($this);
        } catch (Exception $e) {

        }
        // cleanup in either case (poor man's "finally" clause)
        if ($magicQuotes) {
            set_magic_quotes_runtime(true);
        }
        if (!empty($oldEncoding)) {
            mb_internal_encoding($oldEncoding);
        }
        // rethrow the exception
        if (!empty($e)) {
            throw $e;
        }
        return $response;
    }
    
    /**
     * Sends a GET request and returns the response
     * 
     * @param string $url			Request URL
     * @param array $queryParams	Params to be append in query string, e.g. array('uid'=>'xxx',...)
     * @param array $cookies		Cookies to be send, e.g. array('BDUSS'=>'xxxx', ...)
     * @param array $extra_headers	Extra http headers to be send, e.g. array('Accept-Encoding' => '*', ...)
     * @param array $config			Configures for a http request, e.g. array('connect_timeout' => 2000, ...)
     * @return bdHttpResponse
     * @throws bdHttpException
     */
    public static function get($url, array $queryParams = array(),
    						   array $cookies = array(),
    						   array $extra_headers = array(),
    						   array $config = array())
    {
    	$request = new bdHttpRequest($url, self::METHOD_GET, $config);
		$request->getUrl()->setQueryVariables($queryParams);
    	
    	$strCookie = '';
    	foreach ($cookies as $name => $value) {
    		$cookie = $name . '=' . $value;
    		if (preg_match(self::REGEXP_INVALID_COOKIE, $cookie)) {
    			throw new bdHttpException("Invalid cookie: '{$cookie}'");
    		};
    		$strCookie .= $cookie;
    	}
    	
    	if (!empty($strCookie)) {
	    	$strCookie = empty($extra_headers['cookie']) 
	    					? $strCookie
	    					: $extra_headers['cookie'] . '; ' . $strCookie;
	    	$extra_headers['cookie'] = $strCookie;
    	}
    	
        $request->setHeaders($extra_headers);
        
        return $request->send();
    }
    
    /**
     * Sends a POST request and returns the response
     * 
     * @param string $url			Request URL
     * @param array $postParams		Post params in associated array format, e.g. array('uid'=>'xxx',...)
     * @param array $cookies		Cookies to be send, e.g. array('BDUSS'=>'xxxx', ...)
     * @param array $extra_headers	Extra http headers to be send, e.g. array('Accept-Encoding' => '*', ...)
     * @param array $config			Configures for a http request, e.g. array('connect_timeout' => 2000, ...)
     * @return bdHttpResponse
     * @throws bdHttpException
     */
    public static function post($url, array $postParams = array(),
    							array $cookies = array(),
    							array $extra_headers = array(),
    							array $config = array())
    {
    	$request = new bdHttpRequest($url, self::METHOD_POST, $config);
    	$request->addPostParameter($postParams);
    	
    	$strCookie = '';
    	foreach ($cookies as $name => $value) {
    		$cookie = $name . '=' . $value;
    		if (preg_match(self::REGEXP_INVALID_COOKIE, $cookie)) {
    			throw new bdHttpException("Invalid cookie: '{$cookie}'");
    		};
    		$strCookie .= $cookie;
    	}
    	
    	if (!empty($strCookie)) {
	    	$strCookie = empty($extra_headers['cookie']) 
	    					? $strCookie
	    					: $extra_headers['cookie'] . '; ' . $strCookie;
	    	$extra_headers['cookie'] = $strCookie;
    	}
    	
        $request->setHeaders($extra_headers);
        
        return $request->send();
    }
}



/* vim: set ts=4 sw=4 sts=4 tw=90 noet: */
?>
