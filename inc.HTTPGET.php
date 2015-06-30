<?php

function HTTP_GET($link,$buf=128){
    if(!is_numeric($buf)){
        return(0);
    }
    $useragent="Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2b5) Gecko/20091204 Firefox/3.6b5";

    if(strtolower(substr($link,0,7))=="http://"){
        $link=substr($link,8);
    }
    //if(substr($link,-1)!=="/"){$link=$link."/";}
    $host=substr($link,0,strpos($link,"/"));
    $path=substr($link,strpos($link,"/"));


    $fp = fsockopen($host, 80, $errno, $errstr, 5);
    if($fp){
        $data="";
        $out = "GET ".$path." HTTP/1.1\r\n";
        $out .= "Host: ".$host."\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $out .= "User-Agent: ".$useragent."\r\n";
        $out.= "Accept-Charset: ISO-8859-1,UTF-8;q=0.7,*;q=0.7\r\n";
        //$out.= "Accept-Encoding:\r\n";
        $out.= "Cache-Control: no-cache\r\n";
        $out.= "Accept-Language: de,en;q=0.7,en-us;q=0.3\r\n";
        fwrite($fp, $out);
        while((!feof($fp)) and strlen($data)!==($buf*1024)){$data.=fgets($fp, 1024);}
        fclose($fp);
        $data=explode("\r\n\r\n",$data,2);
        //echo($data[0]);
        if(substr($data[1],0,2)==hex2bin("fffe")){
            $data[1]=mb_convert_encoding($data[1],"UTF-8","UTF-16");
        }
        if(is_encoded($data[0])){
            die($data[0]);
        }
        if(is_chunked($data[0])){
            $data[1]=dechunk($data[1]);
        }
        
        return($data[1]);
    }
}

function is_chunked($headers){
    if(strpos(strtolower($headers),"transfer-encoding: chunked")!==false){
        return(true);
    }else{
        return(false);
    }
}

function is_encoded($headers){
    if(strpos(strtolower($headers),"content-encoding: gzip")!==false){
        return(true);
    }else{
        return(false);
    }
}

function dechunk($data){
    $buf="";
    $t=-1;
    while($t!=0){
        $t = strpos($data, "\r\n");
        $chunksize = hexdec(substr($data, 0, $t));
        $buf .= substr($data, $t + 2, $chunksize);
        $data = substr($data, $t + 4 + $chunksize);
    }
    return($buf);
}

?>
