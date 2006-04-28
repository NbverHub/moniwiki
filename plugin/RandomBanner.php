<?php
// Copyright 2003-2006 Won-Kyu Park <wkpark at kldp.org>
// All rights reserved. Distributable under GPL see COPYING
// a RandomBanner macro plugin for the MoniWiki
//
// Usage: [[RandomBanner(PageName,number)]]
//
// $Id$
// vim:et:ts=2:

function macro_RandomBanner($formatter,$value="") {
  global $DBInfo;

  $test=preg_match("/^([^ ,0-9]*)\s*,?\s*(\d+)?$/",$value,$match);
  if ($test) 
    $pagename=$match[1];$number=$match[2];

  if (!$pagename) $pagename='RandomBanner';
  if (!$number) $number=3;
  #print $pagename.";".$number;
  if ($DBInfo->hasPage($pagename)) {
    $page=$DBInfo->getPage($pagename);
    $body=$page->_get_raw_body();
  } else
    return "[[RandomBanner($value)]]";

  $banner=array();
  $lines=explode("\n",$body);
  foreach ($lines as $line) {
    if (substr($line,0,10)!= ' * http://') continue;
    $dummy=explode(" ",substr($line,3),3);
    $text=$dummy[1];
    $title=$dummy[2];
    if (preg_match(",^(http|ftp|attachment):.*\.(gif|png|jpg|jpeg)$,",$text,$match)) {
      if ($match[1]=='attachment') {
        $fname=$pagename.'/'.substr($text,11);
        $ntext=$formatter->macro_repl('Attachment',$fname,1);
        if (!file_exists($ntext))
          $text=$formatter->macro_repl('Attachment',$fname);
        else {
          $text=qualifiedUrl($DBInfo->url_prefix.'/'.$ntext);
          $text= "<img border='0' alt='$text' src='$text' title='$title' />";
        }
      } else {
        $text= "<img border='0' alt='$text' src='$text' title='$title' />";
      }
      $banner[]=
        "<a href='$dummy[0]'>$text</a>";
    }
  }

  $count=count($banner)-1;
  $number=min($number,$count);
  $selected=array_rand($banner,$number);
  if ($number==1) $selected=array($selected);
  $out=array();
  foreach ($selected as $idx)
    $out[]=$banner[$idx];

  $banners=implode(' ',$out);

  return $banners;
}

?>
