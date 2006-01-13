<?php
// Copyright 2003-2006 Won-Kyu Park <wkpark at kldp.org>
//                     JoungKyun Kim <http://www.oops.org>
// All rights reserved. Distributable under GPL see COPYING
// a mimetex processor plugin for the MoniWiki
//
// Support command line mode by JoungKyun Kim 2006/01/13
//
// $Id$

function processor_mimetex($formatter,$value) {
  global $DBInfo;

  $value = escapeshellarg ($value);
  preg_match ('/\s*\$+([^\$]*)\$+\s*/', $value, $match);
  $tex = $match[1];

  $mimetex= $DBInfo->mimetex_path ? $DBInfo->mimetex_path:
    $DBInfo->url_prefix.'/mimetex.cgi';

  $debug = 0;
  # debuggin'
  if ( $debug ) {
    echo "<pre>\n" .
         "######################\n" .
         "$formatter\n" .
         "$value\n" .
         "{$match[0]}\n" .
         "{$match[1]}\n" .
         "######################\n" .
         "</pre>\n";
  }

  $alt = str_replace ('\'','&#039;',$tex);
  if ( ! strncmp ('shell:', $mimetex, 6) ) {
    if ( ! $tex ) return;

    $vartmp_dir=&$DBInfo->vartmp_dir;
    $cache_dir=$DBInfo->upload_dir."/MimeTeX";
    $mimetex = str_replace ('shell:', '', $mimetex);

    $uniq = md5($tex);

    if ( ! file_exists ($cache_dir) ) {
      umask (000);
      mkdir ($cache_dir, 0777);
    }

    if ( $formatter->preview || $formatter->refresh || ! file_exists ("$cache_dir/$uniq.png")) {
      $cmd = "$mimetex -e $cache_dir/$uniq.gif \"$tex\"";
      $fp = @popen ($cmd, 'r');
      if ( ! is_resource ($fp) ) return $tex;
      pclose ($fp);
    }

    return "<img class='tex' src='$DBInfo->url_prefix/$cache_dir/$uniq.png' alt='$alt' ".
           "title=\"$alt\" />";
  } else {
    return '<img src=\''.$mimetex.'?'.$tex.'\' alt=\''. $alt .'\' title=\''.$alt.'\' />';
  }
}
// vim:et:sts=2:sw=2:
?>
