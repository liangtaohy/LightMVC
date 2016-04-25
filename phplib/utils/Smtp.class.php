<?php
/***********************************
PHP MIME　SMTP ver 1.0 Powered by Boss_ch， Unigenius soft ware co. Ltd
All rights reserved, Copyright 2000 ;
本类用PHP通过smtp sock操作发送MIME 类型的邮件,可以发送HTML 格式的正文、附件，采用base64编码.
本版本是针对个人的发送,与多人群发版本不同的是，每发送到一个人，就重新进行一次编码,
在接收端的用户看来，只是发送给他一个人的。针对多人群发的情况，只发送一次，通过多个
RCPT TO命令发送到不同的人信箱中。
说明：
请把$hostname设为你有权限的默认smtp服务器或是在new时指定，把$charset改成你的默认字符集
Html 正文中如有图片，请用绝对路径的引用"httP://host/path/image.gif"，并连上网，以保证程序能读取到图片的数据信息
如果是通过表单提交过来的Html正文，请先用StripSlashes($html_body)把正文内容进行预处理，Html中用到的样式表文件，
请不要用<link>之类的引用，直接把样式表定义放在<style></style>标签中

转载请保留此版权信息，　Bugs Report : boss_ch@china.com
 *************************************/
class Smtp
{
    protected $hostname = "";
    protected $port = 25;
    protected $timeout = 500;
    protected $connection = 0;
    protected $debug = false;

    protected $err_str;
    protected $err_no;

    protected $autocode = true;
    protected $charset = 'UTF-8'; //change gb2312 to UTF-8
    protected $subject = '';
    protected $body = '';
    protected $attach = '';
    protected $temp_text_body;
    protected $temp_html_body;
    protected $temp_body_images;

    protected $bound_begin = '';
    protected $bound_end = '';

    public function __construct($server, $port = 25, $time_out = 500)
    {
        $this->hostname = $server;
        $this->port = $port;
        $this->timeout = $time_out;
    }

    public function enableDebug($bEnable = false)
    {
        $this->debug = $bEnable;
    }

    public function outdebug($message)
    {
        echo htmlspecialchars($message)."<br>\n";
    }

    public function errmsg()
    {
        return $this->err_str;
    }

    public function send($from_name, $to_name, $from_mail, $to_mail, $subject, $text_body = false, $html_body = false, $att = false)
    {
        if( empty($from_mail) or empty($to_mail) )
        {
            $this->err_str = '没有指定正确的邮件地址:发送人：'.$from_mail.'接收人：'.$to_mail;
            return false;
        }

        if( !is_array($to_mail) )
        {
            $to_mail = split(',', $to_mail);//如果不是数组，转换成数组，哪怕只有一个发送对象;
        }
        if( !is_array($to_name) )
        {
            $to_name = split(',', $to_name);//如果不是数组，转换成数组，哪怕只有一个发送对象;
        }

        $to_mail = array_map('trim', $to_mail);

        //build邮件正文
        $this->Buildbody($text_body, $html_body, $att);

        //与服务器建立链接
        if( !$this->open() ) return false;
        if( !$this->command("HELO $this->hostname", 3, '250') ) return false;

        //不同的收件人需要有不同的邮件头
        for( $i = 0; $i < count($to_mail); $i++ )
        {
            $this->Buildhead($from_name, $to_name[$i], $from_mail, $to_mail[$i], $subject);
            if( !$this->command('RSET', 3, '250') ) return false;
            if( !$this->command('MAIL FROM:'.$from_mail, 3, '250') ) return false;
            if( !$this->command('RCPT TO:'.$to_mail[$i], 3, '250') ) return false;
            if( !$this->command('DATA', 3, '354') ) return false;
            // 准备发送邮件
            if( $this->debug ) $this->outdebug('sending subject;');
            if( !fputs($this->connection, $this->subject) )
            {
                $this->err_str = '发送邮件头时出错！';
                return false;
            }
            if( $this->debug ) $this->outdebug('sending body;');
            if( !fputs($this->connection, $this->body) )
            {
                $this->err_str = '发送正文时出错！';
                return false;
            }
            if( !fputs($this->connection, ".\r\n") )
            {
                $this->err_str = '发正文时出错！';
                return false;
            }//正文发送完毕，退出；
            if( $this->debug ) $this->outdebug('sending body ok;');
            $resp = fgets($this->connection, 256);
            if( $this->debug ) $this->outdebug($resp);
            if( substr($resp, 0, 1) != '2' )
            {
                $this->err_str = '发送完后，服务器没有响应！！';
                return false;
            }
            if( $this->debug ) $this->outdebug('close ok;');
        }
        if( !$this->command('QUIT', 3, '221') ) return false;
        $this->close();
        return true;
    }

    protected function command($command, $return_lenth = 1, $return_code = '2')
    {
        if( $this->connection === 0 )
        {
            $this->err_str = '没有连接到任何服务器，请检查网络连接';
            return false;
        }
        if( $this->debug ) $this->outdebug(">>> $command");
        if( !fputs($this->connection, "$command \r\n") )
        {
            $this->err_str = '无法发送命令'.$command;
            return false;
        }
        else
        {
            $resp = fgets($this->connection, 256);
            if( $this->debug ) $this->outdebug($resp);
            if( substr($resp, 0, $return_lenth) != $return_code )
            {
                $this->err_str = $command .' 命令服务器返回无效:'.$resp;
                return false;
            }
            else
                return true;
        }
    }


    private function open()
    {
        if( empty($this->hostname) )
        {
            $this->err_str = '无效的主机名!!';
            return false;
        }

        $err_no = 0;
        $err_str = '';
        if( $this->debug ) echo "$this->hostname,$this->port,&$err_no, &$err_str, $this->timeout<BR>";
        if( !$this->connection = fsockopen($this->hostname, $this->port, $err_no, $err_str, $this->timeout) )
        {
            $this->err_str = '连接到　SMTP 服务器失败,错误信息：'.$err_str.'错误号：'.$err_no;
            return false;
        }
        else
        {
            $resp = fgets($this->connection, 256);
            if( $this->debug ) $this->outdebug($resp);
            if( substr($resp, 0, 1) != '2' )
            {
                $this->err_str = '服务器返回无效的信息：'.$resp.'请检查SMTP服务器是否正确';
                return false;
            }
            return true;
        }
    }

    private function Close()
    {
        if( $this->connection != 0 )
        {
            fclose($this->connection);
            $this->connection = 0;
        }
    }

    protected function Buildhead($from_name, $to_name, $from_mail, $to_mail, $subject)
    {
        if( empty($from_name) )
        {
            $from_name = $from_mail;
        }
        if( empty($to_name) )
        {
            $to_name = $to_mail;
        }
        $this->subject = "From: =?$this->charset?B?".base64_encode($from_name)."?=<$from_mail>\r\n";
        $this->subject .= "To: =?$this->charset?B?".base64_encode($to_name)."?=<$to_mail>\r\n";
        $subject = ereg_replace("\n", '', $subject);
        $this->subject .= "Subject: =?$this->charset?B?".base64_encode($subject)."?=\r\n";
        if( $this->debug ) echo nl2br(htmlspecialchars($this->subject));
        return true;
    }

    protected function parse_html_body($html_body)
    {
        $passed = '';
        $image_count = 0;
        $this->temp_body_images = array();
        while( preg_match('/<*img([^>]+)src\s*=\s*([^\s]+)/i', $html_body, $reg) )
        {
            $pos = strpos($html_body, $reg[0]);
            $passed .= substr($html_body, 0, $pos);
            $html_body = substr($html_body, $pos + strlen($reg[0]));
            $image_tag = $reg[2];
            $image_att = $reg[1];
            $tag_len = strlen($image_tag);
            if( $image_tag[0] == "'" || $image_tag[0] == '"' )
            {
                $image_tag = substr($image_tag, 1);
            }
            if( substr($image_tag, strlen($image_tag) - 1, 1) == "'" || substr($image_tag, strlen($image_tag) - 1, 1) == '"' )
            {
                $image_tag = substr($image_tag, 0, strlen($image_tag) - 1);
            }
            //echo $image_tag."<br>";
            $cid = md5(uniqid(rand()));
            $cid = substr($cid, 0, 15).'@unigenius.com';
            $passed .= '<img '.$image_att.'src="cid:'.$cid.'"';
            $end_pos = strpos($html_body, '>');
            $passed .= substr($html_body, 0, $end_pos);
            $html_body = substr($html_body,$end_pos);
            // 把图片数据读出来保存到一个数据；

            $img_file_con = fopen($image_tag, 'r');
            $image_data = '';
            $tem_buffer = addslashes(fread($img_file_con, 16777216));
            while( $tem_buffer )
            {
                $image_data .= $tem_buffer;
                $tem_buffer = addslashes(fread($img_file_con, 16777216));
            }
            fclose($img_file_con);
            $image_exe_name = substr($image_tag, strrpos($image_tag,'.') + 1, 3);
            switch( strtolower($image_exe_name) )
            {
                case 'jpg':
                case 'jpeg':
                    $content_type = 'image/jpeg';
                    break;
                case 'gif':
                    $content_type = 'image/gif';
                    break;
                case 'png':
                    $content_type = 'image/x-png';
                    break;
                case 'tif':
                    $content_type = 'image/tif';
                    break;
                default:
                    $content_type = 'image/';
                    break;
            }

            $this->temp_body_images[$image_count][name] = basename($image_tag);
            $this->temp_body_images[$image_count][type] = $content_type;
            $this->temp_body_images[$image_count][cid] = $cid;
            $this->temp_body_images[$image_count][data] = $image_data;
            $image_count++;
            unset($image_data);
        }
        $this->temp_html_body = $passed.$html_body;
        return true;
    }

    protected function build_content($bound_level, $text_body, $html_body, $hava_att = false)
    {
        if( !empty($html_body) )
        {
            //if( preg_match('/<*img([^>]+)src\s*=\s*([^\s]+)/i', $html_body, $reg) )
            if(false)
            {
                $bound_level++;
                if( $text_body )
                {
                    $this->body .= "Content-Type: multipart/related; type=\"multipart/alternative\";\tboundary=\"";
                    $this->body .= $this->bound_begin.$bound_level.$this->bound_end ."\"\r\n\r\n";
                }
                else
                {
                    $this->body .= "Content-Type: multipart/related;\tboundar'=\"";
                    $this->body .= $this->bound_begin.$bound_level.$this->bound_end ."\"\r\n\r\n";

                }// 对于是否 text 正文 、 html正文 有没有，须有不同的 MIME 头
                if( !$hava_att ) $this->body .= "This is a multi-part message in MIME format.\r\n\r\n";
                // 正文标识，如果是已经有附件的编码，则在正文 中不需要这一句
                $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end ."\r\n";
                $this->parse_html_body($html_body);
                if( !empty($text_body) )
                {
                    $this->body .= "Content-Type: multipart/alternative;\tboundary=\"";
                    $bound_level++;
                    $this->body .= $this->bound_begin.$bound_level.$this->bound_end."\"\r\n\r\n";
                    $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end."\r\n";
                    $this->body .= "Content-Type: text/plain;";
                    $this->body .= " charset=\"$this->charset\"\r\n";
                    $this->body .= "Content-Transfer-Encoding: base64\r\n";
                    $this->body .= "\r\n".chunk_split(base64_encode(StripSlashes($text_body)))."\r\n";
                    $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end."\r\n";
                    $this->body .= "Content-Type: text/html;";
                    $this->body .= " charset=\"$this->charset\"\r\n";
                    $this->body .= "Content-Transfer-Encoding: base64\r\n";
                    $this->body .= "\r\n".chunk_split(base64_encode(StripSlashes($this->temp_html_body)))."\r\n";
                    $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end."--\r\n\r\n";
                    $bound_level--;
                }
                else
                {
                    $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end."\r\n";
                    $this->body .= "Content-Type: text/html;";
                    $this->body .= " charset=\"$this->charset\"\r\n";
                    $this->body .= "Content-Transfer-Encoding: base64\r\n";
                    $this->body .= "\r\n".chunk_split(base64_encode(StripSlashes($this->temp_html_body)))."\r\n";
                }//正文编码，有或没有　text 部分，编成不同的格式。
                for( $i = 0; $i < count($this->temp_body_images); $i++ )
                {
                    $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end."\r\n";
                    $this->body .= "Content-Type:".$this->temp_body_images[$i][type].";name=\"";
                    $this->body .= $this->temp_body_images[$i][name]."\"\r\n";
                    $this->body .= "Content-Transfer-Encoding: base64\r\n";
                    $this->body .= "Content-ID: <".$this->temp_body_images[$i][cid].">\r\n";
                    $this->body .= "\r\n".chunk_split(base64_encode(StripSlashes($this->temp_body_images[$i][data])))."\r\n";
                }
                $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end."--\r\n\r\n";
                $bound_level--;
            }
            else // 有或没有图片，以上是有图片的处理，下面是没有图片的处理
            {
                $this->temp_html_body = $html_body;
                if( !empty($text_body) )
                {
                    $bound_level++;
                    $this->body .= "Content-Type: multipart/alternative;\tboundary=\"";
                    $this->body .= $this->bound_begin.$bound_level.$this->bound_end."\"\r\n\r\n";

                    if( !$hava_att ) $this->body .= "\r\nThis is a multi-part message in MIME format.\r\n\r\n";
                    $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end."\r\n";
                    $this->body .= "Content-Type: text/plain;";
                    $this->body .= " charset=\"$this->charset\"\r\n";
                    $this->body .= "Content-Transfer-Encoding: base64\r\n";
                    $this->body .= "\r\n".chunk_split(base64_encode(StripSlashes($text_body)))."\r\n";
                    $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end."\r\n";
                    $this->body .= "Content-Type: text/html;";
                    $this->body .= " charset=\"$this->charset\"\r\n";
                    $this->body .= "Content-Transfer-Encoding: base64\r\n";
                    $this->body .= "\r\n".chunk_split(base64_encode(StripSlashes($this->temp_html_body)))."\r\n";
                    $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end."--\r\n\r\n";
                    $bound_level--;
                }
                else
                {
                    $this->body .= "Content-Type: text/html;";
                    $this->body .= " charset=\"$this->charset\"\r\n";
                    $this->body .= "Content-Transfer-Encoding: base64\r\n";
                    $this->body .= "\r\n".chunk_split(base64_encode(StripSlashes($this->temp_html_body)))."\r\n";
                }//正文编码，有或没有　text 部分，编成不同的格式。

            }// end else
        }
        else // 如果没有　html 正文，只有　text 正文　
        {
            $this->body .= "Content-Type: text/plain; charset=\"$this->charset\"\r\n";
            $this->body .= "Content-Transfer-Encoding: base64\r\n";
            $this->body .= "\r\n".chunk_split(base64_encode(StripSlashes($text_body)))."\r\n";
        }
    }// end function default

    protected function Buildbody($text_body, $html_body, $att)
    {
        $this->body = "MIME-Version: 1.0\r\n";
        if( !is_array($att) || count($att) === 0 )	//如果没有附件，查看正文的类型
        {
            $encode_level = 0;
            $this->build_content($encode_level, $text_body, $html_body);
        }
        else //如果有附件，
        {
            $bound_level = 0;
            $this->body .= "Content-Type: multipart/mixed;\tboundary=\"";
            $bound_level++;

            $this->body .= $this->bound_begin.$bound_level.$this->bound_end ."\"\r\n\r\n";
            $this->body .= "This is a multi-part message in MIME format.\r\n\r\n";
            $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end ."\r\n";
            $this->build_content($bound_level, $text_body, $html_body, true);// 编入正文部分

            $num = count($att);
            for( $i = 0; $i < $num; $i++ )
            {
                $file_name = $att[$i][name];
                $file_source = $att[$i][source];
                $file_type = $att[$i][type];
                $file_size = $att[$i][size];
                if( file_exists($file_source) )
                {
                    $file_data = addslashes(fread($fp=fopen($file_source,"r"), filesize($file_source)));
                    $file_data = chunk_split(base64_encode(StripSlashes($file_data)));
                    $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end ."\r\n";
                    $this->body .= "Content-Type: $file_type;\r\n\tname=\"$file_name\"\r\nContent-Transfer-Encoding: base64\r\n";
                    $this->body .= "Content-Disposition: attachment;\r\n\tfilename=\"$file_name\"\r\n\r\n";
                    $this->body .= $file_data."\r\n";
                }
            }//end for

            $this->body .= "--".$this->bound_begin.$bound_level.$this->bound_end ."--\r\n\r\n";
        }// end else

        if( $this->debug ) echo nl2br(htmlspecialchars($this->body));

        return true;
    }

}//end class define