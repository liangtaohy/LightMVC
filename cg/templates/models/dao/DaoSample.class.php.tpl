<?php
class DaoSample extends DaoBase
{
    const TABLE_NAME = 'sample';
    /**
     * 表前缀
     * @var string
     */
    protected $_table_prefix = 'xlegal_';

    protected $_table_name = self::TABLE_NAME; // so, the full table name is xlegal_sample

    private static $inst;

    private $db;

    protected $_table_fields = array(
        self::TABLE_NAME  => array(
        )
    );
}
