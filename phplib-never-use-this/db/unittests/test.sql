CREATE TABLE IF NOT EXISTS `articles` (
  `id` bigint(20) NOT NULL auto_increment COMMENT '主键',
  `a_title` varchar(64) NOT NULL COMMENT '文章标题',
  `a_desc` varchar(128) NOT NULL COMMENT '简介',
  `a_content` mediumtext NOT NULL COMMENT '正文',
  `a_from` varchar(1024) NOT NULL COMMENT '来源',
  `a_tags` varchar(128) NOT NULL COMMENT '标签',
  `a_author_id` bigint(20) NOT NULL default '0' COMMENT '原创作者uid',
  `a_author_name` varchar(64) NOT NULL default '0' COMMENT '作者名',
  `a_created_time` int(11) NOT NULL COMMENT '发布时间',
  `a_modified_time` int(11) NOT NULL COMMENT '修改时间',
  `a_status` tinyint(4) NOT NULL default '0' COMMENT '状态：未发布，已发布，删除/停用，审核未通过，审核通过（审核通过即自动发布）',
  `a_reason` varchar(512) NOT NULL default '''''' COMMENT '审核未通过的理由或停用理由',
  `a_auditor` bigint(20) NOT NULL default '0' COMMENT '审核人员uid',
  `a_head_img` varchar(128) NOT NULL COMMENT '头图的名称',
  `a_type` tinyint(4) NOT NULL default '0' COMMENT '文章类型：推广，推荐',
  PRIMARY KEY  (`id`),
  KEY `a_title` (`a_title`,`a_author_id`,`a_created_time`,`a_modified_time`,`a_status`,`a_auditor`),
  KEY `a_type` (`a_type`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='文章表' AUTO_INCREMENT=30 ;