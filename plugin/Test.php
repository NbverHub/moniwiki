<?php
// Copyright 2003 by Won-Kyu Park <wkpark at kldp.org>
// All rights reserved. Distributable under GPL see COPYING
// a sample plugin for the MoniWiki
//
// Usage: [[Test]]
//
// $Id$
// vim:et:ts=2:

function macro_Test($formatter,$value) {
  return "HelloWorld !\n";

}

function do_test($formatter,$options) {
  $formatter->send_header();
  $formatter->send_title();
  $ret= macro_Test($formatter,$options[value]);
  $formatter->send_page($ret);
  $formatter->send_footer("",$options);
  return;
}

?>
