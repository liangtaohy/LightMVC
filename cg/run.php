<?php
/**
 * @name conf.php
 * @desc xdb cg的主程序
 * @author liangtaohy@163.com
 */
require_once dirname(__FILE__).'/config.php';

define('INPUT_DIR', dirname(__FILE__).'/templates');
define('OUTPUT_DIR', dirname(__FILE__).'/out');

$strOutputRoot = OUTPUT_DIR.'/'.$conf['APP_NAME'];
if(file_exists($strOutputRoot))
{
    rename($strOutputRoot, $strOutputRoot.date('Ymd-His', time()));
}

$arrTpls = getAllTpls();
foreach($arrTpls as $strFultTplPath)
{

    $strRelativeTplPath = substr($strFultTplPath, strlen(INPUT_DIR)+1);
    $strOutputRelativePath = convertPath($strRelativeTplPath);
    $strOutputPath = $strOutputRoot.'/'.$strOutputRelativePath;
    $strOutputDir = is_dir($strFultTplPath)? $strOutputPath : dirname($strOutputPath);
    if(!file_exists($strOutputDir))
    {
        mkdir($strOutputDir, 0777, true);
    }
    if(!is_dir($strFultTplPath))
    {
        $strContent = processTemplates($strFultTplPath);
        file_put_contents($strOutputPath, $strContent);
    }
}
echo "DONE\n";

//获取所有的代码模板文件
function getAllTpls()
{
    $intFirst = 0;
    $intLast = 1;
    $arrQueue = array(INPUT_DIR);
    $arrFiles = array ();
    while ($intFirst < $intLast)
    {
        $strPath = $arrQueue[$intFirst++];
        if (!is_dir($strPath))
        {
            if (file_exists($strPath))
            {
                $arrSep = explode('.', $strPath);
                //只取.tpl文件
                if($arrSep[count($arrSep) - 1] == 'tpl')
                {
                    array_push($arrFiles, $strPath);
                }
            }
        }
        else
        {
            $arrPaths = scandir($strPath);
            $intSubCount = 0;//下级目录或者文件个数
            foreach ($arrPaths as $strSubPath)
            {
                //过滤以'.'开头的文件,包括'.', '..', '.svn'
                if (substr($strSubPath, 0, 1) == '.')
                {
                    continue;
                }
                $strCurPath = $strPath.'/'.$strSubPath;
                $arrQueue[$intLast++] = $strCurPath;
                $intSubCount++;
            }
            //如果当前是没有tpl文件的目录，直接放到返回结果中
            if ($intSubCount === 0)
            {
                array_push($arrFiles, $strPath);
                continue;
            }

        }
    }
    return $arrFiles;
}

//将模板文件名转换成输出的文件名
function convertPath($strPath)
{
    global $conf;
    //去掉模板后缀
    $strPath = str_replace('.tpl', '', $strPath);
    //替换文件名中的模板变量
    $strToFind = '$APP_NAME$';
    $strToRelace = $conf['APP_NAME'];
    $strPath = str_replace($strToFind, $strToRelace, $strPath);
    return $strPath;
}

//以后如果需要，可以用smarty等复杂的模板引擎来处理
function processTemplates($strTpl)
{
    $strContent = file_get_contents($strTpl);
    $arrSearch = array();
    $arrReplace = array();
    global $conf;
    foreach($conf as $strKey => $strValue)
    {
        $arrSearch[] = '{{'.$strKey.'}}';
        $arrReplace[] = $strValue;
    }

    $strResult = str_replace($arrSearch, $arrReplace, $strContent);
    return $strResult;
}
