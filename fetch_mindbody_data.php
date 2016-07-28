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
 Call nick:  Figure out how to edit class types on MB
             get mappings of class names to class types
 tag the classes in MB
 write the code to use the new tags in the feed, instead of lookups
 rename images and add to media libraries
 mod code to use new image names, mappings.
 
 test image attachment on scratch server with new images
 Add code to feed script to create organizer posts 
 
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
//echo "\n".__FILE__." : Line: ".__LINE__. " -- "."\n\n ";

$tag_ids_arr = array(
	'Move More' 		=> null,
	'Eat Well' 			=> null,
	'Stress Less' 		=> null,
	'Connect Deeply' 	=> null, 
);

$pillar_lookup_arr = array(

	'aCHIeve' 				=> array('Eat Well','Connect Deeply'),
	'Community Night' 		=> array('Connect Deeply'),
	'Dance' 				=> array('Move More'), // 20160713: called dance fitness
	'Fitness' 				=> array('Move More'),
	'Dance Fitness' 		=> array('Move More'),
	'Happiness/Positivity' 	=> array('Stress Less'),
	'Healthy Cooking' 		=> array('Eat Well'),
	'Healthy Eating' 		=> array('Eat Well'),
	'Massage Therapy' 		=> array('Stress Less'),
	'Meditation' 			=> array('Stress Less'),
	'Mindfulness' 			=> array('Stress Less','Connect Deeply'),
	'Nutrition' 			=> array('Eat Well'),
	'Reiki' 				=> array('Stress Less','Connect Deeply'),
	'Sleep' 				=> array('Stress Less'),
	'Weight Loss' 			=> array('Eat Well', 'Move More'),
	'Yoga' 					=> array('Connect Deeply','Move More'),
	'Yoga Short Course' 	=> array('Connect Deeply','Move More'),
	'Class Cancelled'		=> array(''),
	'Daily Classes'			=> array(''),
);

// 20160713: yoga teacher training, specialty classes

 // this gets values below
$tier1_tag_ids_arr = array(

	'aCHIeve',
	'Community Night',
	'Dance',
	'Fitness',
	'Dance Fitness',
	'Happiness/Positivity',
	'Healthy Cooking',
	'Healthy Eating',
	'Massage Therapy',
	'Meditation',
	'Mindfulness',
	'Nutrition',
	'Reiki',
	'Sleep',
	'Weight Loss',
	'Yoga',
	'Yoga Short Course',
	'Class Cancelled',
	'Daily Classes',
);

 // this gets values below
$pillar_tag_ids_arr = array(
	'Move More',
	'Eat Well',
	'Stress Less',
	'Connect Deeply',    
);

$image_filenames_arr = array(    
	
	'aCHIeve'				=> 'casey-calendar-weight-loss.png',
	'Community Night' 		=> 'casey-calendar-community-night.png',
	'Dance' 				=> 'casey-calendar-dance.png',
	'Fitness' 				=> 'casey-calendar-fitness.png',
	'Dance Fitness' 		=> 'casey-calendar-dance.png', // new pic needed
	'Happiness/Positivity' 	=> 'casey-calendar-happiness-positivity.png',
	'Healthy Cooking' 		=> 'casey-calendar-healthy-cooking.png',
	'Healthy Eating' 		=> 'casey-calendar-healthy-eating.png',
	'Massage Therapy' 		=> 'casey-calendar-massage-therapy.png',
	'Meditation' 			=> 'casey-calendar-meditation.png',
	'Mindfulness' 			=> 'casey-calendar-mindfullness.png',
	'Nutrition' 			=> 'casey-calendar-nutrition.png',
	'Reiki' 				=> 'casey-calendar-reiki.png',
	'Sleep' 				=> 'casey-calendar-sleep.png',
	'Weight Loss' 			=> 'casey-calendar-weight-loss.png',
	'Yoga' 					=> 'casey-calendar-yoga.png',
	'Yoga Short Course' 	=> 'casey-calendar-yoga.png',
	'Class Cancelled' 		=> 'casey-calendar-class-default.png',
	'Daily Classes'			=> 'casey-calendar-class-default.png',
);


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

foreach( $result->GetClassesResult->Classes->Class as $one_class ) {
	// will populate with tag id below
	$tag_ids_arr[$one_class->ClassDescription->SessionType->Name] = null;
} //foreach

//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." MBAPI result:: count(".count($result->GetClassesResult->Classes->Class).")\n";
//var_dump($result->GetClassesResult->Classes->Class[0]);


//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." ::tag_ids_arr:: \n";
//print_r($tag_ids_arr);
//exit;

//The array of class data we want is $result->GetClassesResult->Classes->Class (why 3 lavels deep? dunno. Big sprawling structure.  Not my code.  MSH)
$class_count_arr = array();
$staff_arr = array();

// load wordpress so we can get the existing event posts
require_once($_SERVER['DOCUMENT_ROOT']."/wp-load.php");
//for image processing
require_once(ABSPATH . "wp-admin" . '/includes/image.php');
require_once(ABSPATH . "wp-admin" . '/includes/file.php');
require_once(ABSPATH . "wp-admin" . '/includes/media.php');
require_once(ABSPATH . "wp-admin" . '/includes/image.php');
require_once(ABSPATH . "wp-admin" . '/includes/taxonomy.php');

/********************************  CHECK INSTRUCTOR ***************************/
/* for each instructor in the feed:
	 If they have a tribe_organizer post, get the id.  
	 If not, create the instructor post and get the id.
*/	
$x=0; 
foreach ($result->GetClassesResult->Classes->Class as $key => $one_class ) {
//didn't we just do this exact forloop on line 152?

  if( is_string($one_class->Staff->Name) ) { 
  //if the event has an instructor 
  
    if( $org_obj = get_page_by_title( $one_class->Staff->Name, OBJECT, 'tribe_organizer' ) ) {
	// if the instructor already has a page/ID
	
//echo "\n".__FILE__." : Line: ".__LINE__."\n\t\t update instructor  :: count :: ".$x." ::org_obj->ID:: ".$org_obj->ID;
    	
    	// set the classes instructor in the feed to the existing instructor ID
    	$result->GetClassesResult->Classes->Class[$key]->wp_organizer_ID = 
    		$org_obj->ID;   // UNFINISHED . . . 
    						// What's unfinished here? SG

    } else {
	// or insert a new instructor into the DB and then set the instructor ID

//echo "\n".__FILE__." : Line: ".__LINE__."\n\t\t insert instructor :: count :: ".$x." ::org_obj->ID:: ".$org_obj->ID;
    
    // insert a tribe_organizer post
   	$post_data = array(
        'post_title'     => $one_class->Staff->Name,
    	'post_content'   => $one_class->Staff->Bio,
        'post_status'    => 'publish',
        'post_type'      => 'tribe_organizer',
        'post_author'    => '8',
        'ping_status'    => 'closed',
        'post_date'      => date('Y-m-d H:i:s'),
        'post_date_gmt'  => gmdate('Y-m-d H:i:s'),
        'comment_status' => 'closed',
    );  //array
    
		$new_org_id = $new_post_id = wp_insert_post($post_data, true);
		
//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." :: count :: ".$x." :: inserted :: new_org_id:: \n"; 
//print_r($new_org_id);
      	
		$result->GetClassesResult->Classes->Class[$key]->wp_organizer_ID = 
			$new_org_id;
    } //esle
  } //if
$x++; 
} //foreach


// get the category id's for all the categories. 
foreach($tag_ids_arr as $cat_name => $cat_id){

//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." :: get cat ids \n"; 

	$cat = get_term_by( 'name', $cat_name, 'tribe_events_cat' );
	if ( $cat ){
	  $id = $cat->term_id;
	} else {
		$id = false;
	}
	
  if($id) {
    $tag_ids_arr[$cat_name] = $id;
    if(array_key_exists($cat_name, $tier1_tag_ids_arr)){
    	$tier1_tag_ids_arr[$cat_name] = $id;
    }
    if(array_key_exists($cat_name, $pillar_tag_ids_arr)){
    	$pillar_tag_ids_arr[$cat_name] = $id;
    }
  }	else {
  	$cat_info = array( 'cat_name' => $cat_name,
                       'category_nicename' => sanitize_title($cat_name),
                       'taxonomy' => 'tribe_events_cat' );
    if($id = wp_insert_category($cat_info)){
      $tag_ids_arr[$cat_name] = $id; 
      if(array_key_exists($cat_name, $tier1_tag_ids_arr)){
    	  $tier1_tag_ids_arr[$cat_name] = $id;
      } //if
      if(array_key_exists($cat_name, $pillar_tag_ids_arr)){
    	  $pillar_tag_ids_arr[$cat_name] = $id;
      } //if 	
    } //if
  } //else
} //foreach



// get the attachment id's for all the images that correspond to tags.  They are supposed to be there.  If they are missing it breaks
$attachment_ids_arr = array();
foreach($image_filenames_arr as $cat_name => $filename){

//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." :: get attachment ids \n"; 

	$attachment_ids_arr[$cat_name] = 
			get_free_attachment_id_from_filename($filename);
} // foreach

// plus the default
$attachment_ids_arr['default'] = 
			get_free_attachment_id_from_filename('casey-calendar-class-default.png');

//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." :: attachment_ids_arr:: \n";
//print_r($attachment_ids_arr);  

// GET ALL EXISTING EVENT POSTS WITH START DATES AFTER LAST MIDNIGHT(POSSIBLY INCLUDING SOME THAT ARE NOT FROM MINDBODY) 
// AND YES, THOSE NESTED ARRAYS ARE THE WAY get_posts() WANTS IT. 
$args = array('post_type'=>'tribe_events',
              'posts_per_page' => -1, // get them all
              'meta_query' => array(
					array( 'key'     => '_EventStartDate',
						   'value'   => date('Y-m-d').' 00:00:00', 
						   	// starting today at midnight
						   'compare' => '>'
					)
				)	
); //args array

$event_posts = get_posts($args); // another sprawling structure
$existing_posts_mbids_arr = array();
$attachment_urls_arr = array(); // used in attaching images to new events, since a lot of the feed images are the same


// THAT GIVES US A SIMPLE LOOKUP TO SEE IF A MB EVENT ALREADY HAS A CORRESPONDING POST, AND THE POST ID TO UPDATE IF IT DOES.
foreach ($event_posts as $key => $post) {
	$event_posts[$key]->meta = get_metadata ('post', $post->ID);
	if(isset($event_posts[$key]->meta['mind_body_ID'][0])) {
		$existing_posts_mbids_arr[$event_posts[$key]->meta['mind_body_ID'][0]] = $post->ID;
	} //if
} //foreach event_posts


/********************************  CHECK EVENT/CLASS **************************/
$fetched_mbid_arr = array();
$y=0;
// FOREACH EVENT (CLASS) IN MINDBODY FEED, 
foreach($result->GetClassesResult->Classes->Class as $mb_event) {
// we're doing this again?

//log_it(__LINE__);
//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- ".$mb_event->ID);
//echo "\n\n********************************\n\n".__FILE__." : ".__function__. " : Line: ".__LINE__. " :: count :: ".$y." -- mb_event->ID:: ".$mb_event->ID;
  	
	if(!isset($existing_posts_mbids_arr[$mb_event->ID])) {
//  IF THERE IS NO event/class POST WITH THAT MBID, CREATE ONE

//log_it(__LINE__);
//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- ".$mb_event->ID);		
//echo "\n ::INSERT >> mb_event->ID:: ".$mb_event->ID;

		insert_new_event($mb_event);
	} //if
	
	else {
// else update the event/class post.  See note 1

//log_it(__LINE__);
//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- ".$mb_event->ID);		
//echo "\n UPDATE >> mb_event->ID:: ".$mb_event->ID;

    	$post_id = $existing_posts_mbids_arr[$mb_event->ID];
    	update_event($mb_event, $post_id);
	} //else
	
// store fetched mbid (as key so ) so we can see if any posts no longer have MB entries and have to be trashed.
  $fetched_mbid_arr[$mb_event->ID]=true; 
// store as key for quicker lookup.  Anything useful we can put in the val?  
$y++;
} //foreach


foreach ($existing_posts_mbids_arr as $mbid => $post_id){
// NOTE: THIS ONLY WORKS IF THE FEED AND THE POST SELECTIONS USE THE SAME DATE RANGE.  

	// TURNED OFF FOR NOW
	if(!isset($fetched_mbid_arr[$mbid])){ 
//echo("\nDeleted post $post_id because mbid $mbid is was not in feed");
	  //wp_trash_post($post_id);  // use trash instead of delete, which will actually delete if post type isn't page or post
	} //if
} // foreach >> doing nothing?


echo "update complete";
echo "</pre>";
     
        
function insert_new_event($event_obj){ 

//log_it("inserted event for mbid ".$event_obj->ID);  
//acc_write_log('DEBUG',"\n\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- ".$event_obj->ID);
//echo "\n".__FILE__." : Line: ".__LINE__." ::event_obj->ID:: ".$event_obj->ID;
//echo "\n ::event_obj->ClassDescription->Name:: ".$event_obj->ClassDescription->Name;
    
  $post_data = array(
    'post_content'   => $event_obj->ClassDescription->Description,
    'post_title'     => $event_obj->ClassDescription->Name,
    'post_status'    => 'publish',
    'post_type'      => 'tribe_events',
    'post_author'    => '8',
    'ping_status'    => 'closed',
    'post_excerpt'   => truncate_text($event_obj->ClassDescription->Description, 120),
    'post_date'      => date('Y-m-d H:i:s'),
    'post_date_gmt'  => gmdate('Y-m-d H:i:s'),
    'comment_status' => 'closed'
  );  
  
	// insert post data into WP > returns new post ID  
	$new_post_id = wp_insert_post($post_data, true);
	
//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- new_post_id:: ".$new_post_id);	
//echo "\n".__FILE__." : Line: ".__LINE__." ::new_post_id:: ".$new_post_id;
  
  if($new_post_id) {
    $start_date = substr($event_obj->StartDateTime, 0 ,10 ).' '.substr($event_obj->StartDateTime, 11,8 ); // more efficient than str_replace!
    $end_date = substr($event_obj->EndDateTime, 0 ,10 ).' '.substr($event_obj->EndDateTime, 11,8 ); 
    $duration =  strtotime ($end_date) - strtotime ($start_date);
    $meta_arr =  array (
      '_EventStartDate' => $start_date,                     //GENERATE
      '_EventEndDate' => $end_date ,                        //GENERATE
      '_EventStartDateUTC' => $start_date,                  //GENERATE
      '_EventEndDateUTC' => $end_date,                      //GENERATE
      '_EventDuration' => $duration,                        //GENERATE
      'mind_body_ID' => $event_obj->ID,                     //COPY
      
      //'_thumbnail_id' => '2255',                         //GENERATE SEPARATELY
      'Instructor' => $event_obj->Staff->Name,
      '_EventOrganizerID' => $event_obj->wp_organizer_ID,
      
      // ALL THE REST ARE DEFAULTS FOUND IN EVENT POSTS GENERATED THROUGH UI.  MOST OF THEM PROBABLY ARE USELESS, BUT I READ SOMEWHERE THAT AVADA USES ALL THE pyre_* META'S FOR SOMETHING
      '_EventOrigin' => 'events-calendar',
      '_EventVenueID' => '0',
      '_EventShowMapLink' => '1',
      '_EventShowMap' => '',
      '_EventCost' => '',
      'slide_template' => 'default',
      'sbg_selected_sidebar' => 'a:1:{i:0;s:1:"0";}',
      'sbg_selected_sidebar_replacement' => 'a:1:{i:0;s:0:"";}',
      'sbg_selected_sidebar_2' => 'a:1:{i:0;s:1:"0";}',
      'sbg_selected_sidebar_2_replacement' => 'a:1:{i:0;s:0:"";}',
      'pyre_main_top_padding' => '',
      'pyre_main_bottom_padding' => '',
      'pyre_hundredp_padding' => '',
      'pyre_slider_position' => 'default',
      'pyre_slider_type' => 'no',
      'pyre_slider' => '0',
      'pyre_wooslider' => '0',
      'pyre_revslider' => '0',
      'pyre_elasticslider' => '0',
      'pyre_fallback' => '',
      'pyre_avada_rev_styles' => 'default',
      'pyre_display_header' => 'yes',
      'pyre_header_100_width' => 'default',
      'pyre_header_bg' => '',
      'pyre_header_bg_color' => '',
      'pyre_header_bg_opacity' => '',
      'pyre_header_bg_full' => 'no',
      'pyre_header_bg_repeat' => 'repeat',
      'pyre_displayed_menu' => 'default',
      'pyre_display_footer' => 'default',
      'pyre_display_copyright' => 'default',
      'pyre_footer_100_width' => 'default',
      'pyre_sidebar_position' => 'default',
      'pyre_sidebar_bg_color' => '',
      'pyre_page_bg_layout' => 'default',
      'pyre_page_bg' => '',
      'pyre_page_bg_color' => '',
      'pyre_page_bg_full' => 'no',
      'pyre_page_bg_repeat' => 'repeat',
      'pyre_wide_page_bg' => '',
      'pyre_wide_page_bg_color' => '',
      'pyre_wide_page_bg_full' => 'no',
      'pyre_wide_page_bg_repeat' => 'repeat',
      'pyre_page_title' => 'default',
      'pyre_page_title_text' => 'default',
      'pyre_page_title_text_alignment' => 'default',
      'pyre_page_title_100_width' => 'default',
      'pyre_page_title_custom_text' => '',
      'pyre_page_title_text_size' => '',
      'pyre_page_title_custom_subheader' => '',
      'pyre_page_title_custom_subheader_text_size' => '',
      'pyre_page_title_font_color' => '',
      'pyre_page_title_height' => '',
      'pyre_page_title_mobile_height' => '',
      'pyre_page_title_bar_bg' => '',
      'pyre_page_title_bar_bg_retina' => '',
      'pyre_page_title_bar_bg_color' => '',
      'pyre_page_title_bar_borders_color' => '',
      'pyre_page_title_bar_bg_full' => 'default',
      'pyre_page_title_bg_parallax' => 'default',
      'pyre_page_title_breadcrumbs_search_bar' => 'default',
    );
    
    foreach($meta_arr as $key => $val){
    	$new_meta_id = add_post_meta($new_post_id, $key, $val);
    	
//echo "\n".__FILE__." : Line: ".__LINE__." ::new_meta_id:: ".$new_meta_id;

    } //foreach
       
    $post_obj = get_post($new_post_id);
    set_post_categories($event_obj, $post_obj);  
    // has to be done before attaching image, since fallback image uses category

//echo "\n".__FILE__." : Line: ".__LINE__." ::backfrom set_post_categories:: ";
//print_r($event_obj);	

    set_post_instructor_tags($event_obj, $post_obj);

//echo "\n".__FILE__." : Line: ".__LINE__." ::backfrom set_post_instructor_tags";
    
    // attach an image
    $image_id = attach_image_to_event ($event_obj, $post_obj);

//echo "\n".__FILE__." : Line: ".__LINE__." ::image_id:: ".$image_id;

  } // if 
} // insert_new_event()


function update_event($event_obj, $post_id) {  
// event_obj is one class from the mind-body feed

//log_it("updated event for mbid ".$event_obj->ID ." post_id is $post_id");
//acc_write_log('DEBUG',"\n\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- ".$event_obj->ID."\n\n ");
//echo "\n".__FILE__." : Line: ".__LINE__." ::event_obj:: ".$event_obj->ID;

  $post_data = array(
    'ID'             => $post_id, // updating an existing post
    'post_content'   => $event_obj->ClassDescription->Description, 
    'post_title'     => $event_obj->ClassDescription->Name,  
    'post_excerpt'   => truncate_text($event_obj->ClassDescription->Description, 120),
    'post_author'    => '8'
  );  
  
  wp_update_post( $post_data );

  $start_date = substr($event_obj->StartDateTime, 0 ,10 ).' '.substr($event_obj->StartDateTime, 11,8 ); // more efficient than str_replace!
  $end_date = substr($event_obj->EndDateTime, 0 ,10 ).' '.substr($event_obj->EndDateTime, 11,8 ); 
  $duration =  strtotime ($end_date) - strtotime ($start_date);
  
  
  $meta_arr =  array (
    '_EventStartDate' => $start_date,                     //GENERATE
    '_EventEndDate' => $end_date ,                        //GENERATE
    '_EventStartDateUTC' => $start_date,                  //GENERATE
    '_EventEndDateUTC' => $end_date,                      //GENERATE
    '_EventDuration' => $duration,                        //GENERATE
   // '_thumbnail_id' => '2255',                          //GENERATE SEPARATELY

  );
  
  foreach($meta_arr as $key => $val){
  	update_post_meta($post_id, $key, $val);
  }
} //update_event


function set_post_categories($event_obj, $post_obj){
  global $class_tag_lookup_arr;
  global $pillar_lookup_arr;
  global $tag_ids_arr;
  
  
//acc_write_log('DEBUG',"\n\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- ");
//echo "\n\n".__FILE__." : Line: ".__LINE__;

  $term_id_arr = array();
  if( isset($event_obj->ClassDescription->SessionType->Name) ) {
  	$term_id_arr[] = 
  		(int)$tag_ids_arr[$event_obj->ClassDescription->SessionType->Name]; 
  	// cast as int because wp_set_object_terms is said to be picky

  // now add the term id's for pillars that this tag belongs to
   	if( 
   	is_array($pillar_lookup_arr[$event_obj->ClassDescription->SessionType->Name]) 
   	) {
      		
     foreach( 
     	$pillar_lookup_arr[$event_obj->ClassDescription->SessionType->Name] as 
     	$pillar_tag ) 
     {
        $term_id_arr[] = (int)$tag_ids_arr[$pillar_tag];	
      }	//foreach
    } //if

$foo_arr = wp_set_object_terms($post_obj->ID, $term_id_arr, 'tribe_events_cat');

//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." ::foo_arr:: \n";
//print_r($foo_arr);

  } //if
} //set_post_categories


//set_post_instructor_tags
function set_post_instructor_tags($event_obj, $post_obj){
  
//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- "."\n\n ");	
//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." ::event_obj->Staff->Name:: ".$event_obj->Staff->Name;
	
  if(isset($event_obj->Staff->Name)) {
  	$tag_id = null;

	if($tag_obj = get_term_by( 'name', $event_obj->Staff->Name, 'class_instructors' )) {
	  	$tag_id = $tag_obj->term_id;
	  	
//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." ::tag_id:: ".$tag_id;
	} else {
	// need to test that 'class_instructors' exist >> if not create
	
		$new_term_arr = wp_insert_term($event_obj->Staff->Name, 'class_instructors');	
		
//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." ::gettype:: ";
//print_r($new_term_arr);
		
	    if( ( gettype($new_term_arr) != WP_Error ) &&
	    	( isset($new_term_arr['term_id']) )
	    ) {
	    	$tag_id = $new_term_arr['term_id'];
	    } //if
	} //else
    $bar_arr = wp_set_object_terms($post_obj->ID, array($tag_id), 'class_instructors' );

    // apparently can't do this
  	$tag_id = null;
	if($tag_obj = get_term_by( 'name', $event_obj->Staff->Name, 'post_tag' )) {
		$tag_id = $tag_obj->term_id;
	} else {
		$new_term_arr = wp_insert_term( $event_obj->Staff->Name, 'post_tag');	
	    if(is_array($new_term_arr) && isset($new_term_arr['term_id'])) {
	    	$tag_id = $new_term_arr['term_id'];
		} //if
	    else {
//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." ::new_term_arr:: \n";
//print_r($new_term_arr);	

	    } //else
	} //else
    $bar_arr = wp_set_object_terms($post_obj->ID, array($tag_id), 'post_tag' );    
  } //if
  else {

//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." ::$event_obj->Staff->Name:: \n";
//print_r($event_obj->Staff->Name);
  	
  } //else
} //set_post_instructor_tags



function attach_image_to_event ($event_obj, $post_obj){
  
//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- "."\n\n ");
//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." ::event_obj->ID:: ".$event_obj->ID." ::post_obj->ID:: ".$post_obj->ID;
	
	$post_id = $post_obj->ID;
  
// if there is an image url in the feed for this class (not staff) 
// use that as the thumbnail
  if(isset($event_obj->ClassDescription->ImageURL)){
  	$url = $event_obj->ClassDescription->ImageURL;
  	
  	// strip possible query strings after basename
  	preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches);
    $image_filename = basename($matches[0]);
    
//log_it('image filename from feed: '.$image_filename);
//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__.'image filename from feed: '.$image_filename."\n\n ");
//echo "\n".__FILE__." : Line: ".__LINE__.'image filename from feed: '.$image_filename."\n\n";  	

    // if no image with that filename already exists as an attachment,
    // "sideload" the image & create the attachment
    if(!$id = get_attachment_id_from_filename($image_filename)){
//log_it(__LINE__ . ': filename did not exist');
//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__.': filename did not exist'."\n\n ");
//echo "\n".__FILE__." : Line: ".__LINE__.': filename did not exist'."\n\n";  	

     	$tmp = download_url( $url );
     	
    	if( is_wp_error( $tmp ) ){
    		// download failed, need to add error handling
    		return false;
    	} //if
    	$file_array = array();
    
    	// Set variables for storage
    	// fix filename for query strings
    	$file_array['name'] = $image_filename;
    	$file_array['tmp_name'] = $tmp;
    	
    	// If error storing temporarily, unlink
    	if ( is_wp_error( $tmp ) ) {
    		//log_it('temp store error');
    		@unlink($file_array['tmp_name']);
    		$file_array['tmp_name'] = '';
    	} //if
    	
    	// do the validation and storage stuff
    	$id = media_handle_sideload( $file_array, $post_id, $desc);
    	// If error storing permanently, unlink
    	if ( is_wp_error($id) ) {
    		//log_it('sideload error');
    		@unlink($file_array['tmp_name']);
    	} //if
    	
    	$attachment_urls_arr[$id]=$url;
    	//log_it("returned new id $id");
      
      // create the thumbnails
      $attach_data = wp_generate_attachment_metadata( $id, $file_array['tmp_name'] );
      wp_update_attachment_metadata( $id,  $attach_data );
    } //if
  	
    //log_it(__LINE__ . ': filename existed: id is '. $id);
  } //if 
  else {
  	// get the id of the fallback image for this category
  	$id = select_fallback_image($post_obj);
  	
  } //else
  if($id){
    
//log_it(__LINE__ . ": updating postid: $post_id with attid $id");
//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. ": updating postid: $post_id with attid $id"."\n\n ");
//echo "\n".__FILE__." : Line: ".__LINE__. ": updating postid: $post_id with attid $id"."\n\n";  
	
	update_post_meta($post_id, '_thumbnail_id', $id );
  } //if
} //attach_image_to_event



function update_image($event_obj, $post_id){   
  
//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- "."\n\n ");
//echo "\n".__FILE__." : Line: ".__LINE__. " -- "."\n\n ";

// most of the time the image won't change, but check if feed and post match and update accordingly  
	
	/*
	if (there is an image in the feed object and not in the post){
	  if (an image with that filename is already an attachment){
	     get the attachment\'s id
	  } else {
	     sideload the image as an attachment and get the new id
	  }
	  make that id the meta _Thumbnail`
	  return
	}
	
	if(there is an image in the post but none in the feed object){
	  get fallback image filename for this class
	  get id of attachment with that filename
	  make that id the meta _Thumbnail
	  return
  }
	
	if(there is an image in the feed object and in the post){
	  look up the filename of the attached image
	  if (it\'s the same as  the filename in the feed oject) {
	    do nothing
	    return
	  } else {
	     if (an image with that filename is already an attachment){
	       get that attachment's id
	  } else {
	     sideload the image and get the id 
	  }
	  make that id the meta _Thumbnail
	  return
	  
	  }
  }
  */
}	//update_image
	
	
function select_fallback_image($event_obj){
  
	//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- "."\n\n ");
	//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." ::event_obj:: \n";
	//print_r($event_obj) ; 
	//exit;
/*
 * Looks at a post object and returns the id of an attachment, depending on the categories
 * As of 12/14/2015 the protocol is:  
 * if there was an image url in the mindbody feed, we never got here
 * if there is a class tag, attach the image that corresponds to that
 * else, if there is a pillar tag, attach the image for that pillar
 * as a final fallback, attach the default image.
 */
  global $class_tag_lookup_arr, // tells you which categories a class belongs to
         $pillar_lookup_arr,  // tells you which pillars a category belongs to
         $attachment_ids_arr, // generated in run, has attachment id's for all the stock images for categories and pillars
         $pillar_tag_ids_arr, // a flat lookup array
         $tier1_tag_ids_arr;  // another flat lookup array
  $id = false;
  $cats_arr = wp_get_object_terms($event_obj->ID, 'tribe_events_cat', array('fields'=>'names') );
  
  //echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." - cats_arr for {$event_obj->ID}: ";
  //print_r($cats_arr);
  // first see if there are any class tags
  if(is_array($cats_arr)){
    foreach($cats_arr as $cat_name){
      if(in_array($cat_name, $tier1_tag_ids_arr) && array_key_exists($cat_name, $attachment_ids_arr))	{
      	//echo "\n".__LINE__." - returning id {$attachment_ids_arr[$cat_name]}";
      	return $attachment_ids_arr[$cat_name];  // first match short-circuits 
      } 
    }
    foreach($cats_arr as $cat_name){ 
      if(in_array($cat_name, $pillar_tag_ids_arr) && array_key_exists($cat_name, $attachment_ids_arr))	{
      	//echo "\n".__LINE__." - returning id {$attachment_ids_arr[$cat_name]}";
      	return $attachment_ids_arr[$cat_name];  // first match short-circuits
      }
    }
    if(array_key_exists('default', $attachment_ids_arr))	{
    	//echo "\n".__LINE__." - returning id {$attachment_ids_arr['default']}";
    	return $attachment_ids_arr['default']; 
    } 
  }
  return $id;
} //select_fallback_image


function get_attachment_id_from_filename( $filename = '') {
  
//acc_write_log('DEBUG',"\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__. " -- "."\n\n ");
//echo "\n".__FILE__." : Line: ".__LINE__. " -- "."\n\n ";

	//this finds the id of an attachment that is already attached to an event
	global $wpdb;
	if ($filename == '' ) {
		return;
	} //if
	
	$query = 'select p.id from wp_postmeta pm
			left join wp_posts p on p.id = pm.meta_value
			left join wp_posts p2 on p2.id = pm.post_id

			where pm.meta_key = "_thumbnail_id"
			and p2.post_type = "tribe_events"
			and p.guid like "%'.$filename.'"';
		
	$results = $wpdb->get_results($query , OBJECT );

	if(isset($results[0]->id)){
		//echo "\nattachment found for $filename: " . $results[0]->id;
		return $results[0]->id;
	}
	//echo "\n".__LINE__."no attachment found for $filename";
	//echo "\nQuery: ".$query;

	return false;
} //get_attachment_id_from_filename


function get_free_attachment_id_from_filename( $filename = '') {
  
//acc_write_log('DEBUG',"\n\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__);
//echo "\n\n".__FILE__." : Line: ".__LINE__;

//this finds the id of an attachment whether or not it is attached to anything
	global $wpdb;
	if ($filename == '' ) {
		return;
	} //if

	$query = 'select id from wp_posts 
			where post_type = "attachment"
			and guid like "%'.$filename.'"';
//	//echo "\n ".__LINE__."  $query  \n";
	
	$results = $wpdb->get_results($query , OBJECT );

//echo "\n".__FILE__." : ".__function__. " : Line: ".__LINE__." ::results:: \n";
//print_r($results);

	if(isset($results[0]->id)){
		//echo "\nattachment found for $filename: " . $results[0]->id;
		return $results[0]->id;
	} //if
		//echo "\n".__LINE__."no attachment found for $filename";
		//echo "\nQuery: ".$query;

	return false;
} //get_free_attachment_id_from_filename







function truncate_text($text,$length) {
// truncates at last space before $length.
  
//acc_write_log('DEBUG',"\n\n: ".__FILE__." : ".__function__. " : Line: ".__LINE__);
//echo "\n\n".__FILE__." : Line: ".__LINE__;

	$text=strip_tags($text);

	if (strlen($text) > $length) { 
		$text = substr($text, 0, $length); 
		$text = substr($text,0,strrpos($text," ")); 
		$etc = " ...";  
		$text = $text.$etc; 
	} //if 

	return $text; 
} //truncate_text

	
function log_it($text, $log_file='fetch_log'){
  file_put_contents($log_file, "\n".$text, FILE_APPEND);
} //log_it



/* Note 1:  Re-writing everything is lazy and will result in a lot of db traffic, 
 * but the last_updated value in the MB feed seems to apply to the class description, not to the session itself.  
 * We could compare a bunch of values (start-times, etc) but that is hard to maintain if we end up using more values, 
 * so go ahead and do all those writes.  It's wasteful but it's bulletproof.
 * Not as wasteful as you might think, actually.  wp-update_post and update_post_meta read first and don't do a write if data is unchanged. 
 * Casey's MB feed only goes about 11 months ahead, so it could be about 600 updates of 11 reads and maybe no writes at all.  
 * A themed page view might involve 50 or more reads, so each run is like 10-20 page views.  Not too onerous.

 * Here's the method sig for GetClasses():
 * public function GetClasses(array $classDescriptionIDs, 
                             array $classIDs, 
                             array $staffIDs, 
                             $startDate, //wants a DateTime object
                             $endDate,  //wants a DateTime object
                             $clientID = null, 
                             $PageSize = null, 
                             $CurrentPage = null, 
                             $XMLDetail = XMLDetail::Full, 
                             $Fields = NULL, 
                             SourceCredentials $credentials = null)
                             
$post_data = array(
    'ID'             => null, 
    					// not updating an existing post
    'post_content'   => $event_obj->ClassDescription->Description, // The full text 
    'post_name'      => null,  // slug.  wp will generate
    'post_title'     => $event_obj->ClassDescription->Name,  // The title of your post.
    'post_status'    => 'publish',  // Default 'draft'.
    'post_type'      => 'tribe_events', // Default 'post'.
    'post_author'    => '8', // The user ID number of the author. Default is the current user ID.
    'ping_status'    => 'closed',  // Pingbacks or trackbacks allowed. Default is the option 'default_ping_status'.
    'post_parent'    => null,  // Sets the parent of the new post, if any. Default 0.
    'menu_order'     => null,  // If new post is a page, sets the order in which it should appear in supported menus. Default 0.
    'to_ping'        => null,  // Space or carriage return-separated list of URLs to ping. Default empty string.
    'pinged'         => null,  // Space or carriage return-separated list of URLs that have been pinged. Default empty string.
    'post_password'  => null,  // Password for post, if any. Default empty string.
    'guid'           => null, // Skip this and let Wordpress handle it, usually.
    'post_content_filtered' => null,  // Skip this and let Wordpress handle it, usually.
    'post_excerpt'   => truncate_text($event_obj->ClassDescription->Description, 120), // For all your post excerpt needs.
    'post_date'      => date('Y-m-d H:i:s'), // The time post was made.
    'post_date_gmt'  => gmdate('Y-m-d H:i:s'),// The time post was made, in GMT.
    'comment_status' => 'closed', // Default is the option 'default_comment_status', or 'closed'.
    'post_category'  => null, // Default empty.
    'tags_input'     => null, // Default empty.
    'tax_input'      => null, // For custom taxonomies. Default empty.
    'page_template'  => null // Requires name of template file, eg template.php. Default empty.
  );  					 
                             
*/
