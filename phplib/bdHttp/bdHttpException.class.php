<?php
/***************************************************************************
 * 
 * Copyright (c) 2009 Baidu.com, Inc. All Rights Reserved
 * $Id: bdHttpException.class.php,v 1.2 2010/05/12 08:52:07 zhujt Exp $ 
 * 
 **************************************************************************/
 
/**
 * Exception classes for bdHttp Package
 * 
 * @category	Networking
 * @package		bdHttp
 * @author		zhujt <zhujianting@baidu.com>
 * @version		$Revision: 1.2 $ 
 */
class bdHttpException extends Exception
{
	
}

class bdHttpURLInvalidException extends bdHttpException
{
	public function __construct($url, $errmsg)
	{
		parent::__construct("The URL $url is not valid: $errmsg");
	}
}

class bdHttpServiceInvalidException extends bdHttpException
{
	public function __construct($url, $errmsg, $errno)
	{
		parent::__construct("Service for URL[$url] is invalid now, errno[$errno] errmsg[$errmsg]");
	}
}

class bdHttpRequestTimeoutException extends bdHttpException
{
	public function __construct($url, $errmsg)
	{
		parent::__construct("Request for $url timeout: $errmsg");
	}
}

class bdHttpResponseTooLargeException extends bdHttpException
{
	public function __construct($url, $content_length, $max_response_size)
	{
		parent::__construct("Response body for $url has at least $content_length bytes, " .
							"which has exceed the max response size limit($max_response_size)");
	}
}

class bdHttpRedirectsTooManyException extends bdHttpException
{
	public function __construct($url)
	{
		parent::__construct("Request for $url caused too many redirections.");
	}
}

class bdHttpErrorPageException extends bdHttpException
{
	public function __construct($url, $http_code)
	{
		parent::__construct("Received HTTP error code $http_code while loading $url");
	}
}

class bdHttpNoResponseException extends bdHttpException
{
	public function __construct($url)
	{
		parent::__construct("The URL $url has no response.");
	}
}

/* vim: set ts=4 sw=4 sts=4 tw=90 noet: */
?>
