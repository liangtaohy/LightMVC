<?php
/***************************************************************************
 *
 * Copyright (c) 2009 Baidu.com, Inc. All Rights Reserved
 * $Id: bdHttpRequestWithUpload.class.php,v 1.3 2010/05/12 08:52:07 zhujt Exp $
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
 * special Request when upload for bdHttp package
 */
require_once 'bdHttpRequestMultipartBody.php';

/**
 * base class
 */
require_once 'bdHttpRequest.class.php';

/**
 * Class representing a HTTP request message
 *
 * @category	Networking
 * @package		bdHttp
 * @author		hanguofeng <hanguofeng@baidu.com>
 * @version		$Revision: 1.0 $
 * @link		http://tools.ietf.org/html/rfc2616#section-6
 */
class bdHttpRequestWithUpload extends bdHttpRequest implements SplSubject
{
    /**
    * uploads files
    * @var  array
    */
    protected $_uploads=array();

    /**
    * Fileinfo magic database resource
    * @var  resource
    * @see  detectMimeType()
    */
    private static $_fileinfoDb;

    /**
     * Returns the request body
     *
     * @overwrite
     * @return   string|resource|bdHttpRequestMultipartBody
     */
    public function getBody()
    {
        if (self::METHOD_POST == $this->method && ( !empty($this->postParams) || !empty($this->uploads))) {
            if ('application/x-www-form-urlencoded' == $this->headers['content-type']) {
                $body = http_build_query($this->postParams, '', '&');
                if (!$this->getConfig('use_brackets')) {
                    $body = preg_replace('/%5B\d+%5D=/', '=', $body);
                }
                // support RFC 3986 by not encoding '~' symbol (request #15368)
                return str_replace('%7E', '~', $body);
            } elseif ('multipart/form-data' == $this->headers['content-type']) {

                return new bdHttpRequestMultipartBody(
                    $this->postParams, $this->uploads, $this->getConfig('use_brackets')
                );
            }
        }
        return $this->body;
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
    							array $config = array(),
                                array $files = array()
                                )
    {
    	$request = new self($url, self::METHOD_POST, $config);
    	$request->addPostParameter($postParams);
        foreach($files as $file_item)
        {
            $file_item['sendFilename'] = $file_item['sendFilename'] ? $file_item['sendFilename'] : null;
            $file_item['contentType'] = $file_item['contentType'] ? $file_item['contentType'] : null;
            $request->addUpload($file_item['fieldName'],$file_item['filename'],$file_item['sendFilename'],$file_item['contentType']);
        }

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
    * Adds a file to form-based file upload
    *
    * Used to emulate file upload via a HTML form. The method also sets
    * Content-Type of HTTP request to 'multipart/form-data'.
    *
    * If you just want to send the contents of a file as the body of HTTP
    * request you should use setBody() method.
    *
    * If you provide file pointers rather than file names, they should support
    * fstat() and rewind() operations.
    *
    * @param    string  name of file-upload field
    * @param    string|resource|array   full name of local file, pointer to
    *               open file or an array of files
    * @param    string  filename to send in the request
    * @param    string  content-type of file being uploaded
    * @return   HTTP_Request2
    * @throws   HTTP_Request2_LogicException
    */
    public function addUpload($fieldName, $filename, $sendFilename = null,
                              $contentType = null)
    {
        if (!is_array($filename)) {
            $fileData = $this->fopenWrapper($filename, empty($contentType));
            $this->uploads[$fieldName] = array(
                'fp'        => $fileData['fp'],
                'filename'  => !empty($sendFilename)? $sendFilename
                                :(is_string($filename)? basename($filename): 'anonymous.blob') ,
                'size'      => $fileData['size'],
                'type'      => empty($contentType)? $fileData['type']: $contentType
            );
        } else {
            $fps = $names = $sizes = $types = array();
            foreach ($filename as $f) {
                if (!is_array($f)) {
                    $f = array($f);
                }
                $fileData = $this->fopenWrapper($f[0], empty($f[2]));
                $fps[]   = $fileData['fp'];
                $names[] = !empty($f[1])? $f[1]
                            :(is_string($f[0])? basename($f[0]): 'anonymous.blob');
                $sizes[] = $fileData['size'];
                $types[] = empty($f[2])? $fileData['type']: $f[2];
            }
            $this->uploads[$fieldName] = array(
                'fp' => $fps, 'filename' => $names, 'size' => $sizes, 'type' => $types
            );
        }
        if (empty($this->headers['content-type']) ||
            'application/x-www-form-urlencoded' == $this->headers['content-type']
        ) {
            $this->setHeader('content-type', 'multipart/form-data');
        }

        return $this;
    }
    /**
    * Wrapper around fopen()/fstat() used by setBody() and addUpload()
    *
    * @param  string|resource file name or pointer to open file
    * @param  bool            whether to try autodetecting MIME type of file,
    *                         will only work if $file is a filename, not pointer
    * @return array array('fp' => file pointer, 'size' => file size, 'type' => MIME type)
    * @throws HTTP_Request2_LogicException
    */
    protected function fopenWrapper($file, $detectType = false)
    {
        if (!is_string($file) && !is_resource($file)) {
            throw new bdHttpException(
                "Filename or file pointer resource expected"
            );
        }
        $fileData = array(
            'fp'   => is_string($file)? null: $file,
            'type' => 'application/octet-stream',
            'size' => 0
        );
        if (is_string($file)) {
            $track = @ini_set('track_errors', 1);
            if (!($fileData['fp'] = @fopen($file, 'rb'))) {
                $e = new bdHttpException(
                    $php_errormsg
                );
            }
            @ini_set('track_errors', $track);
            if (isset($e)) {
                throw $e;
            }
            if ($detectType) {
                $fileData['type'] = self::detectMimeType($file);
            }
        }
        if (!($stat = fstat($fileData['fp']))) {
            throw new bdHttpException(
                "fstat() call failed"
            );
        }
        $fileData['size'] = $stat['size'];

        return $fileData;
    }

   /**
    * Tries to detect MIME type of a file
    *
    * The method will try to use fileinfo extension if it is available,
    * deprecated mime_content_type() function in the other case. If neither
    * works, default 'application/octet-stream' MIME type is returned
    *
    * @param    string  filename
    * @return   string  file MIME type
    */
    protected static function detectMimeType($filename)
    {
        // finfo extension from PECL available
        if (function_exists('finfo_open')) {
            if (!isset(self::$_fileinfoDb)) {
                self::$_fileinfoDb = @finfo_open(FILEINFO_MIME);
            }
            if (self::$_fileinfoDb) {
                $info = finfo_file(self::$_fileinfoDb, $filename);
            }
        }
        // (deprecated) mime_content_type function available
        if (empty($info) && function_exists('mime_content_type')) {
            return mime_content_type($filename);
        }
        return empty($info)? 'application/octet-stream': $info;
    }
}



/* vim: set ts=4 sw=4 sts=4 tw=90 noet: */
?>
