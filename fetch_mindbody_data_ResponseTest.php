<?php

/* A NOTE TO FUTURE MAINTAINERS:  
 * WHEN YOU SEE THE WORD "class" IN THIS SCRIPT, DON'T BE CONFUSED.  
 * IT MEANS A GROUP OF STUDENTS LED BY A TEACHER.  
 * NOTHING TO DO WITH PHP CLASSES.  
 * THAT GOES FOR COMMENTS, NAMES OF VARS, METHODS, FILES, ETC. 
 * JUST THOUGHT I'D MENTION THAT.  MSH  
 
 NOTES:
 
 API Keys should be stored outside /var/www
 
 12/21/2015 To Do
 Call nick:  
	- Figure out how to edit class types on MB
 	- get mappings of class names to class types
 	- tag the classes in MB
 	- write the code to use the new tags in the feed, instead of lookups
 	- rename images and add to media libraries
 	- mod code to use new image names, mappings.
 
	- test image attachment on scratch server with new images
 	- Add code to feed script to create organizer posts 
 
 Modify feed script to create organizer posts from staff listings in feed (ifNotExists)
 Modify calendar bar to add drop-down to sort by instructor (organizer)
 
 Modify calendar bar to add drop-down of categories, with js to redirect.
 
 duplicate everything on sandbox
   
 TO-DO HERE:  
 Handling images on update:
   as of 11/18/2015 images are not handled at all on update.  
   to-do:
     Check if image filename in feeed is different from that in post and replace if necessary
     check if ...

 Delete posts that are older than the start of the previous month

 */


// custom logging
require_once("includes/accelerant_custom_logging.php");
//acc_write_log('DEBUG',"\ncustom logging: ".dirname(__FILE__)."\n\n ");
echo "\n".__FILE__." : Line: ".__LINE__. " -- "."\n\n ";


echo '<pre>'."\n\n********    ". date('Y-m-d H:i:s') .'    ***********';
//log_it("\n\n********    ". date('Y-m-d H:i:s') .'    ***********');

require_once("includes/classService.php");

// sandbox credentials now work for real MB:
$sourcename = 'AccelerantStudiosLLC';
$password = "oMh1ajTlIwhtxZsHomLprIdxS9Q=";
//$siteID ="-99"; // Mindbody Sandbox
$siteID ="38100"; // Casey's ID, now that our keys work there

$creds = new SourceCredentials($sourcename, $password, array($siteID));
$classService = new MBClassService();
$classService->SetDefaultCredentials($creds); 




// As of 12/7/2015 the plan is to sinc the posts to the feed once a day, presumably at 2 or 3 AM.  That's the cron's business
// Except once a week, we update a full two years ahead
// Start date will be Now once dev is done
$d1 = new DateTime(); // defaults to now

// if it's Sunday (plan is for cron to run at 2 am) set the second date way ahead.  Otherwise 90 days.
if( date('w')== 0 ) {
  //$end_uts = time()+(61516800); // Two years from now
  $end_uts = time()+strtotime("+2 years"); // Two years from now
} else {
  //$end_uts = time()+(7776000);  // 90 days from now
  //$end_uts = time()+(1296000);  // 90 days from now
  $end_uts = time()+strtotime("+90 days");  // 90 days from now
  
} // else

$d2 = new DateTime(date('Y-m-d', $end_uts)); 

// get the feed, using MindBody's supplied php library d1>startdate d2>enddate number to get=>1000
$result = $classService->GetClasses(array(), array(), array(), $d1, $d2, null, 1000, 0);

echo "\n :: updating and creating ".count($result->GetClassesResult->Classes->Class)." event/class records :: \n";

echo "<br /><br /><pre>";
//var_dump($result->GetClassesResult->Classes->Class[0]);
print_r($result->GetClassesResult->Classes->Class[0]);

echo "</pre>";

