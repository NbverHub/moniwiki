<?php
// Copyright 2003 by Won-Kyu Park <wkpark at kldp.org>
// All rights reserved. Distributable under GPL see COPYING
// a vim colorizer plugin for the MoniWiki
//
// Usage: {{{#!vim sh|c|sh|.. [number]
// some codes
// }}}
// $Id$

function processor_vim($formatter,$value,$options) {
  global $DBInfo;
  $cache_dir=$DBInfo->upload_dir."/VimProcessor";

  $syntax=array("php","c","python","jsp","sh","cpp",
          "java","ruby","forth","fortran","perl",
          "haskell","lisp","st","objc","tcl","lua",
          "asm","masm","tasm","make",
          "awk","docbk","diff","html","tex","vim",
          "xml","dtd","sql","conf","config","nosyntax","apache");

  #$opts=array("number");

  if ($value[0]=='#' and $value[1]=='!')
    list($line,$value)=explode("\n",$value,2);
  # get parameters
  if ($line)
    list($tag,$type,$extra)=explode(" ",$line,3);
  $src=$value;
  if (!$type) $type='nosyntax';

  if ($extra == "number") 
    $option='+"set number" ';
  if ($DBInfo->vim_options)
    $option.=$DBInfo->vim_options.' ';

  $uniq=md5($option.$src);
  if (!file_exists($cache_dir)) {
    umask(000);
    mkdir($cache_dir,0777);
    umask(022);
  }

  if (file_exists($cache_dir."/$uniq".".html") && !$formatter->refresh) {
    $out = "";
    $fp=fopen($cache_dir."/$uniq".".html","r");
    while (!feof($fp)) $out .= fread($fp, 1024);
    return $out;
    #return join('',file($cache_dir."/$uniq".".html"));
  }

  # comment out the following two lines to freely use any syntaxes.
  if (!in_array($type,$syntax)) 
    return "<pre class='code'>\n$line\n$src\n</pre>\n";

  if(getenv("OS")=="Windows_NT") {
    $tohtml='\%VIMRUNTIME\%\\syntax\\2html.vim';
    $vim="gvim"; # Win32
    $fout=tempnam("/tmp","OUT");
  } else {
    $tohtml='\$VIMRUNTIME/syntax/2html.vim';
    $vim="vim";
    $fout="/dev/stdout";
  }  

# simple sample
#$type='c';
#$src='
#void main() {
#printf("Hello World!");
#
#}
#';

  $tmpf=tempnam("/tmp","FOO");
  $fp= fopen($tmpf, "w");
  fwrite($fp, $src);
  fclose($fp);

  $cmd= "$vim -T xterm -e -s $tmpf ".
        ' +"syntax on " +"set syntax='.$type.'" '.$option.
        ' +"so '.$tohtml.'" +"wq! '.$fout.'" +q';

  if(getenv("OS")=="Windows_NT") {
    system($cmd);
    $out=join(file($fout),"");
    unlink($fout);
  } else {
    $fp=popen($cmd,"r");
    while($s = fgets($fp, 1024)) $out.= $s;
    pclose($fp);
  }
  unlink($tmpf);

  $out=preg_replace("/<title>.*title>|<\/?head>|<\/?html>|<meta.*>|<\/?body.*>/","", $out);
  $out=preg_replace("/<pre>/","<pre class='wikiSyntax' style='font-family:FixedSys,monospace;color:#c0c0c0;background-color:black'>", $out);
#  $out=preg_replace("/<\/pre>/","</span></pre>", $out);
  $fp=fopen($cache_dir."/$uniq".".html","w");
  fwrite($fp,$out);
  fclose($fp);

  return $out;
}

// vim:et:sts=2:
?>
