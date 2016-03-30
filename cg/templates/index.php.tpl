<?php
/***************************************************************************
 *
 * Copyright (c) 2016 xlegal.com, Inc. All Rights Reserved
 * $Id: index.php,v 1.1 2016/03/28 19:53:47 liangtao Exp $
 * @author liangtao@xmanlegal.com
 *
 **************************************************************************/

require_once(dirname(__FILE__).'/common/env_init.php');
Application::start(defined('IS_DEBUG') ? IS_DEBUG : false);

/* vim: set ts=4 sw=4 sts=4 tw=100 noet: */
?>