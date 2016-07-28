<?php
echo "<pre>";
echo "\n".__LINE__;
require_once($_SERVER['DOCUMENT_ROOT']."/wp-load.php");
//for image processing
require_once(ABSPATH . "wp-admin" . '/includes/image.php');
require_once(ABSPATH . "wp-admin" . '/includes/file.php');
require_once(ABSPATH . "wp-admin" . '/includes/media.php');
require_once(ABSPATH . "wp-admin" . '/includes/image.php');
require_once(ABSPATH . "wp-admin" . '/includes/taxonomy.php');
 $terms_arr = array('bar','baz');
 $foo_arr = wp_set_object_terms(2476, $terms_arr, 'tribe_events_cat' );
  echo "\n".__LINE__.': ';  print_r($foo_arr);


?>