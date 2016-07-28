<?php

/* custom logging script for php */
/* steven gallagher - 2010 */
function acc_write_log($lvl, $msg)
{

  $log = fopen( dirname(__FILE__) . "/tmp/accelerant-dev.log","a");
  if (!$log)
  {
    echo "unable to open log file /tmp/accelerant-dev.log";
    exit;
  }
  $newDateStr = date("y-m-d:H:i:s");
  fputs($log, "$newDateStr $lvl $msg\n");
  fclose($log);
}


/* ************* usages ****
place this file anywhere in the server path.  
add a web writeable file in tmp directory. see above.
from cmdline: tail -f [the log file]

add include of this script file in other code.

in php files call
basics with varialbe:

acc_write_log('DEBUG',"theme_path: ".$theme_path);

list function and variables

acc_write_log('DEBUG',"\n\n".__FUNCTION__.'---render page: collectionID: '.$collectionID.' + roomID: '.$roomID.' + speciesID: '.$speciesID.' + tabID: '.$tabID.' + accessTabID: '.$accessTabID.' + tabID: '.$tabID);  
*/
?>