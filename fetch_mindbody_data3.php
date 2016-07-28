<?php

/* A NOTE TO FUTURE MAINTAINERS:  
 * WHEN YOU SEE THE WORD "class" IN THIS SCRIPT, DON'T BE CONFUSED.  
 * IT MEANS A GROUP OF STUDENTS TAUGHT BY A TEACHER.  NOTHING TO DO WITH PHP CLASSES.  
 * THAT GOES FOR COMMENTS, NAMES OF VARS, METHODS, FILES, ETC. 
 * JUST THOUGHT I'D MENTION THAT.  MSH  
 
 NOTES:
 Need changes in wp and theme:
 Will need to change css for popup in calendar.  Then can increase excerpt length.
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
   as of 11/18/2015 images are not handled at all on update.  to-do:
     Check if image filename in feeed is different from that in post and replace if necessary
     check if ...

 Delete posts that are older than the start of the previous month
  
 *** wp function that will be used eventually, once we get some basis for assigning categories:
 get_cat_ID( $cat_name )
 wp_set_post_categories( $post_ID, $post_categories_arr, $append )
 */


// Some lookup arrays as a patch to assign images to classes, until Casey figures out the Mindbody tagging
$class_tag_lookup_arr = array(
  'Continuing yoga' => array('Yoga', 'Mindfulness', 'Meditation'),
  'CHAIR Yoga' => array('Yoga'),
  'Heart Opening for the Holidays' => array('Yoga'), 
  'Rope Walls Rumpus! ' => array('Yoga'),
  'Stay Aglow for the Holidays' => array('Meditation','Mindfulness'),
  'aCHIeive Weight Loss 4 Life' => array('Weight Loss','Healthy Eating'), 
  'Dance Fitness' => array('Dance'),
  'aCHIeve Class' => array('Weight Loss','Healthy Eating','Yoga'),
  'Therapeutic Yoga' => array('Yoga'),
  'Physicians\' Kitchen: Holiday Fun without the Bun!' => array('Nutrition','Healthy Eating'),
  'All Levels Yoga' => array('Yoga'),
  'Introductory Yoga' => array('Yoga'), 
  'Relax and Breathe: Using Pranayama to Find Quiet' => array('Yoga'),
  'Yoga for High Blood Pressure' => array('Yoga'),
  'Keep Your Holiday Light Steady & Bright' => array('Meditation','Mindfulness'),
  'Meditation in Motion: Flowing with Grace' => array('Yoga'),
  'Gentle Yoga' => array('Yoga'),
  'Restoring and Reflecting' => array('Yoga'),
  'Community Meditation' => array('Meditation','Mindfulness', 'Community Night'),
  'Community Yoga' => array('Yoga', 'Community Night'),
  'Yoga Teacher Training Program' => array('Yoga')
);

$pillar_lookup_arr = array(
  'Community Night' => array('Connect Deeply','Connect Deeply'),
  'Dance' => array('Be Active'),
  'Fitness' => array('Be Active'),
  'Happiness/Positivity' => array('Stress Less'),
  'Healthy Cooking' => array('Eat Well'),
  'Healthy Eating' => array('Eat Well'),
  'Massage Therapy' => array('Stress Less'),
  'Meditation' => array('Stress Less'),
  'Mindfulness' => array('Stress Less','Connect Deeply'),
  'Nutrition' => array('Eat Well'),
  'Reiki' => array('Stress Less','Connect Deeply'),
  'Sleep' => array('Stress Less'),
  'Weight Loss' => array(''),
  'Yoga' => array('Connect Deeply','Be Active'),
);

 // this gets values below
$tag_ids_arr = array(
  'Community Night' => false,
  'Dance' => false,
  'Fitness' => false,
  'Happiness/Positivity' => false,
  'Healthy Cooking' => false,
  'Healthy Eating' => false,
  'Massage Therapy' => false,
  'Meditation' => false,
  'Mindfulness' => false,
  'Nutrition' => false,
  'Reiki' => false,
  'Sleep' => false,
  'Weight Loss' => false,
  'Yoga' => false,
  'Be Active' => false,
  'Eat Well' => false,
  'Stress Less' => false,
  'Connect Deeply' => false    
);

 // this gets values below
$tier1_tag_ids_arr = array(
  'Community Night',
  'Dance',
  'Fitness',
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
  'Yoga'
);


 // this gets values below
$pillar_tag_ids_arr = array(
  'Be Active',
  'Eat Well',
  'Stress Less',
  'Connect Deeply'    
);





$image_filenames_arr = array(    
 
  'Community Night' => 'mb_class_communitynight.jpg',
  'Dance' => 'mb_class_dance.jpg',
  'Fitness' => 'mb_class_fitness.jpg',
  'Happiness/Positivity' => 'mb_class_happinesspositivity.jpg',
  'Healthy Cooking' => 'mb_class_healthycooking.jpg',
  'Healthy Eating' => 'mb_class_healthyeating.jpg',
  'Massage Therapy' => 'mb_class_massagetherapy.jpg',
  'Meditation' => 'mb_class_meditation.jpg',
  'Mindfulness' => 'mb_class_mindfulness.jpg',
  'Nutrition' => 'mb_class_nutrition.jpg',
  'Reiki' => 'mb_class_reiki.jpg',
  'Sleep' => 'mb_class_sleep.jpg',
  'Weight Loss' => 'mb_class_weightloss.jpg',
  'Yoga' => 'mb_class_yoga.jpg'
);

echo '<pre>';
log_it("\n\n********    ". date('Y-m-d H:i:s') .'    ***********');
require_once("includes/classService.php");
// sandbox credentials:
$sourcename = 'AccelerantStudiosLLC';
$password = "oMh1ajTlIwhtxZsHomLprIdxS9Q=";
//$siteID ="-99"; // Mindbody Sandbox
$siteID ="38100"; // Casey's ID, now that our keys work there

$creds = new SourceCredentials($sourcename, $password, array($siteID));
$classService = new MBClassService();
$classService->SetDefaultCredentials($creds); 


// As of 12/7/2015 the plan is to sinc the posts to the feed once a day, presumably at 2 or 3 AM.  That's the cron's business
// Except once a week, we update a full two years ahead

// Start date is Now
$d1=new DateTime('2015-12-01'); // defaults to now

// if it's Sunday Morning, set the second date way ahead.  Otherwise 90 days.
if(date('w')== 0){
  $end_uts = time()+(61516800); // Two years from now
} else {
  //$end_uts = time()+(7776000);  // 90 days from now
  $end_uts = time()+(1296000);  // 
  
}


$d2=new DateTime(date('Y-m-d', $end_uts)); 

$result = $classService->GetClasses(array(), array(), array(), $d1, $d2, null, 1000, 0);
//print_r($result);
//exit;
//The array of class data we want is $result->GetClassesResult->Classes->Class (why 3 lavels deep? dunno. Big sprawling structure.  Not my code.  MSH)

// DEV: look at the structure
$class_count_arr = array();
foreach ($result->GetClassesResult->Classes->Class as $one_class ){
	//$class_count_arr[$one_class->ClassDescription->Name] = $one_class->ClassDescription->Description;
  //print_r( $one_class->ClassDescription);
  
}

// TO DO:  make a table of all instructor names



// load wordpress so we can get the existing event posts
require_once($_SERVER['DOCUMENT_ROOT']."/wp-load.php");
//for image processing
require_once(ABSPATH . "wp-admin" . '/includes/image.php');
require_once(ABSPATH . "wp-admin" . '/includes/file.php');
require_once(ABSPATH . "wp-admin" . '/includes/media.php');
require_once(ABSPATH . "wp-admin" . '/includes/image.php');
require_once(ABSPATH . "wp-admin" . '/includes/taxonomy.php');

// TO DO:  for each instructor in the tale built above, if they have a tribe_organizer post, get the id.  If not, create the post and get the id




// get the category id's for all the categories.  Later on this may be settings
foreach($tag_ids_arr as $cat_name => $cat_id){
  if($id = get_cat_ID($cat_name)) {
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
      }
      if(array_key_exists($cat_name, $pillar_tag_ids_arr)){
    	  $pillar_tag_ids_arr[$cat_name] = $id;
      } 	
    }
  }
}

// get the attachment id's for all the images that correspond to tags.  They are supposed to be there.  If they are issing it breaks
$attachment_ids_arr = array();
foreach($image_filenames_arr as $cat_name => $filename){
	$attachment_ids_arr[$cat_name] = get_free_attachment_id_from_filename($filename);
}
// plus the default
$attachment_ids_arr['default'] = get_free_attachment_id_from_filename('class_default.jpg');

print_r($tag_ids_arr);  



// GET ALL EXISTING EVENT POSTS WITH START DATES AFTER LAST MIDNIGHT(POSSIBLY INCLUDING SOME THAT ARE NOT FROM MINDBODY) 
// AND YES, THOSE NESTED ARRAYS ARE THE WAY get_posts() WANTS IT. 
$args = array('post_type'=>'tribe_events',
              'posts_per_page' => -1, // get them all
              'meta_query' => array(
                                array( 'key'     => '_EventStartDate',
                                       'value'   => date('Y-m-d').' 00:00:00', // starting today at midnight
                                       'compare' => '>'
                                     )
                                   )
              ); 

$event_posts = get_posts($args); // another sprawling structure
$existing_posts_mbids_arr = array();
$attachment_urls_arr = array(); // used in attaching images to new events, since a lot of the feed images are the same

// ADD THE METADATA AND IF THE POST HAS A MINDBODY ID ADD IT TO THE ARR AS A KEY, WITH THE POST ID AS THE VAL
// THAT GIVES US A SIMPLE LOOKUP TO SEE IF A MB EVENT ALREADY HAS A CORRESPONDING POST, AND THE POST ID TO UPDATE IF IT DOES.
foreach ($event_posts as $key => $post){
	$event_posts[$key]->meta = get_metadata ('post', $post->ID);
	if(isset($event_posts[$key]->meta['mind_body_ID'][0])){
		$existing_posts_mbids_arr[$event_posts[$key]->meta['mind_body_ID'][0]] = $post->ID;
	}
}



$fetched_mbid_arr = array();
  // FOREACH EVENT (CLASS) IN MINDBODY FEED, 
foreach($result->GetClassesResult->Classes->Class as $mb_event){
		log_it(__LINE__);
  //  IF THERE IS NO POST WITH THAT MBID, CREATE ONE
	if(!isset($existing_posts_mbids_arr[$mb_event->ID])){
		log_it(__LINE__);
		insert_new_event($mb_event);
	}
	else{
    // else update the post.  See note 1
		log_it(__LINE__);
    $post_id = $existing_posts_mbids_arr[$mb_event->ID];
    update_event($mb_event, $post_id);
	}
	// store fetched mbid (as key so ) so we can see if any posts no longer have MB entries and have to be trashed.
  $fetched_mbid_arr[$mb_event->ID]=true; // store as key for quicker lookup.  Anything useful we can put in the val?  
}


  foreach ($existing_posts_mbids_arr as $mbid => $post_id){
  	// NOTE: THIS ONLY WORKS IF THE FEED AND THE POST SELECTIONS USE THE SAME DATE RANGE.  
    if(!isset($fetched_mbid_arr[$mbid])){
    	log_it("Deleted post $post_id because mbid $mbid is was not in feed");
      wp_trash_post($post_id);  // use trash instead of delete, which will actually delete if post type isn't page or post
    }	
  } 
     
        
function insert_new_event($event_obj){ 
  log_it("inserted event for mbid ".$event_obj->ID);
  $post_data = array(
    'ID'             => null, // not updating an existing post
    'post_content'   => $event_obj->ClassDescription->Description, // The full text 
    'post_name'      => null,  // slug.  wp will generate
    'post_title'     => $event_obj->ClassDescription->Name,  // The title of your post.
    'post_status'    => 'publish',  // Default 'draft'.
    'post_type'      => 'tribe_events', // Default 'post'.
    'post_author'    => '1', // The user ID number of the author. Default is the current user ID.
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
  
  $new_post_id = wp_insert_post($post_data);
  
  if($new_post_id){
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
      
      //'_thumbnail_id' => '2255',                            //GENERATE SEPARATELY
      'Instructor' => $event_obj->Staff->Name,
      '_OrganizerOrganizer' => $event_obj->Staff->Name,
      
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
    	add_post_meta($new_post_id, $key, $val);
    }
    
    $post_obj = get_post($new_post_id);
    set_post_categories($event_obj, $post_obj);  // has to be done before attaching image, since fallback image uses category
    
    // attach an image
    $image_id = attach_image_to_event ($event_obj, $post_obj);
  }
}

function update_event($event_obj, $post_id){  // event_obj is one class from the mind-body feed
  log_it("updated event for mbid ".$event_obj->ID ." post_id is $post_id");

  $post_data = array(
    'ID'             => $post_id, // updating an existing post
    'post_content'   => $event_obj->ClassDescription->Description, 
    'post_title'     => $event_obj->ClassDescription->Name,  
    'post_excerpt'   => truncate_text($event_obj->ClassDescription->Description, 120), 
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
}

function set_post_categories($event_obj, $post_obj){
  global $class_tag_lookup_arr;
  global $pillar_lookup_arr;
  global $tag_ids_arr;
  
  if(is_array($class_tag_lookup_arr[$event_obj->ClassDescription->Name])){
  	
  	
  		$term_id_arr = array();
  		foreach($class_tag_lookup_arr[$event_obj->ClassDescription->Name] as $class_tag){
  			//echo "\n".__LINE__.': '.$class_tag;
  		  $term_id_arr[] = (int)$tag_ids_arr[$class_tag];
  		}
  		// now add the term id's for pillars that this tag belongs to
  		if(is_array($pillar_lookup_arr[$class_tag])){
  		   foreach($pillar_lookup_arr[$class_tag] as $pillar_tag){
  		     $term_id_arr[] = (int)$tag_ids_arr[$pillar_tag];	
  		   }	
  		}
  		
  		
  		echo "\n".$post_obj->ID."\n";
  	  echo "\n".__LINE__.': ';  print_r($term_id_arr);	
      //$foo_arr = wp_set_post_categories($post_obj->ID, $term_id_arr );
      $foo_arr = wp_set_object_terms($post_obj->ID, $term_id_arr, 'tribe_events_cat' );
      echo "\n".__LINE__.': ';  print_r($foo_arr);
  }
}

function attach_image_to_event ($event_obj, $post_obj){
	$post_id = $post_obj->ID;
  // if there is an image url in the feed for this class (not staff) use that as the thumbnail
  if(isset($event_obj->ClassDescription->ImageURL)){
  	$url = $event_obj->ClassDescription->ImageURL;
  	// strip possible query strings after basename
  	preg_match('/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches);
    $image_filename = basename($matches[0]);
  	log_it('image filename from feed: '.$image_filename);
  	
    // if no image with that filename already exists as an attachment, "sideload" the image & create the attachment
    if(!$id = get_attachment_id_from_filename($image_filename)){
    	log_it(__LINE__ . ': filename did not exist');
     	$tmp = download_url( $url );
    	if( is_wp_error( $tmp ) ){
    		// download failed, need to add error handling
    		return false;
    	}
    	$file_array = array();
    
    	// Set variables for storage
    	// fix filename for query strings
    	$file_array['name'] = $image_filename;
    	$file_array['tmp_name'] = $tmp;
    	
    	// If error storing temporarily, unlink
    	if ( is_wp_error( $tmp ) ) {
    		log_it('temp store error');
    		@unlink($file_array['tmp_name']);
    		$file_array['tmp_name'] = '';
    	}
    	
    	// do the validation and storage stuff
    	$id = media_handle_sideload( $file_array, $post_id, $desc);
    	// If error storing permanently, unlink
    	if ( is_wp_error($id) ) {
    		log_it('sideload error');
    		@unlink($file_array['tmp_name']);
    	}  	
    	$attachment_urls_arr[$id]=$url;
    	log_it("returned new id $id");
      
      // create the thumbnails
      $attach_data = wp_generate_attachment_metadata( $id, $file_array['tmp_name'] );
      wp_update_attachment_metadata( $id,  $attach_data );
    } 
  	
    log_it(__LINE__ . ': filename existed: id is '. $id);
  } else {
  	// get the id of the fallback image for this category
  	$id = select_fallback_image($post_obj);
  	
  }
  if($id){
    log_it(__LINE__ . ": updating postid: $post_id with attid $id");
	  update_post_meta($post_id, '_thumbnail_id', $id );
  }
}


function update_image($event_obj, $post_id){   
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
}	
	
function select_fallback_image($event_obj){
	//print_r($event_obj) ; exit;
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
  
  //echo "\n".__LINE__." - cats_arr for {$event_obj->ID}: ";
  //print_r($cats_arr);
  // first see if there are any class tags
  if(is_array($cats_arr)){
    foreach($cats_arr as $cat_name){
      if(in_array($cat_name, $tier1_tag_ids_arr) && array_key_exists($cat_name, $attachment_ids_arr))	{
      	echo "\n".__LINE__." - returning id {$attachment_ids_arr[$cat_name]}";
      	return $attachment_ids_arr[$cat_name];  // first match short-circuits 
      } 
    }
    foreach($cats_arr as $cat_name){ 
      if(in_array($cat_name, $pillar_tag_ids_arr) && array_key_exists($cat_name, $attachment_ids_arr))	{
      	echo "\n".__LINE__." - returning id {$attachment_ids_arr[$cat_name]}";
      	return $attachment_ids_arr[$cat_name];  // first match short-circuits
      }
    }
    if(array_key_exists('default', $attachment_ids_arr))	{
    	echo "\n".__LINE__." - returning id {$attachment_ids_arr['default']}";
    	return $attachment_ids_arr['default']; 
    } 
  }
  return $id;
}

function get_attachment_id_from_filename( $filename = '') {
	//this finds the id of an attachment that is already attached to an event
	global $wpdb;
	if ($filename == '' ) {
		return;
	}
	$query = 'select p.id from wp_postmeta pm
            left join wp_posts p on p.id = pm.meta_value
            left join wp_posts p2 on p2.id = pm.post_id

            where pm.meta_key = "_thumbnail_id"
            and p2.post_type = "tribe_events"
            and p.guid like "%'.$filename.'"';
            
  $results = $wpdb->get_results($query , OBJECT );
  
  if(isset($results[0]->id)){
  	echo "\nattachment found for $filename: " . $results[0]->id;
  	return $results[0]->id;
  }
  echo "\n".__LINE__."no attachment found for $filename";
  echo "\nQuery: ".$query;
  
	return false;
} 

function get_free_attachment_id_from_filename( $filename = '') {
	//this finds the id of an attachment whether or not it is attached to anything
	global $wpdb;
	if ($filename == '' ) {
		return;
	}
	$query = 'select id from wp_posts 
            where post_type = "attachment"
            and guid like "%'.$filename.'"';
            
  $results = $wpdb->get_results($query , OBJECT );
  
  if(isset($results[0]->id)){
  	echo "\nattachment found for $filename: " . $results[0]->id;
  	return $results[0]->id;
  }
  echo "\n".__LINE__."no attachment found for $filename";
  echo "\nQuery: ".$query;
  
	return false;
} 







function truncate_text($text,$length) {
	// truncates at last space before $length.
  $text=strip_tags($text);
  if (strlen($text) > $length) { 
    $text = substr($text, 0, $length); 
    $text = substr($text,0,strrpos($text," ")); 
    $etc = " ...";  
    $text = $text.$etc; 
  }
  return $text; 
}

	
function log_it($text, $log_file='fetch_log'){
  file_put_contents($log_file, "\n".$text, FILE_APPEND);
}



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
                             
*/
