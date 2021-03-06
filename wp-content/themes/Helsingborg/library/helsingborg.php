<?php
function my_css_attributes_filter($var) {
  return is_array($var) ? array_intersect($var, array('current-menu-item')) : '';
}

add_action( 'show_user_profile', 'helsingborg_happy_user_id_field' );
add_action( 'edit_user_profile', 'helsingborg_happy_user_id_field' );
function helsingborg_happy_user_id_field( $user ) {
?>
  <h3><?php _e("Evenemangshantering", "blank"); ?></h3>
  <table class="form-table">
    <tr>
      <th><label for="happy_user_id"><?php _e("Evenemangs ID"); ?></label></th>
      <td>
        <input type="text" name="happy_user_id" id="happy_user_id" class="regular-text"
            value="<?php echo esc_attr( get_the_author_meta( 'happy_user_id', $user->ID ) ); ?>" /><br />
        <span class="description"><?php _e("Skriv in id som används för autentiering för evenemangshantering."); ?></span>
    </td>
    </tr>
  </table>
<?php
}

add_action( 'personal_options_update', 'helsingborg_save_happy_user_id_field' );
add_action( 'edit_user_profile_update', 'helsingborg_save_happy_user_id_field' );
function helsingborg_save_happy_user_id_field( $user_id ) {
  $saved = false;
  if ( current_user_can( 'edit_user', $user_id ) ) {
    update_user_meta( $user_id, 'happy_user_id', $_POST['happy_user_id'] );
    $saved = true;
  }
  return true;
}

// Change to pre_submission !
add_action('gform_after_submission', 'event_add_entry_to_db', 10, 2);
function event_add_entry_to_db($entry, $form) {

  if (strcmp($entry['form_id'], '3') === 0) {
    // Event
    $name         = $entry[1];
    $description  = $entry[15];
    $approved     = 0;
    $organizer    = $entry[11];
    $location     = $entry[7];
    $external_id  = null;

    // Event time
    $type_of_date  = $entry[4];
    $single_date   = $entry[5];
    $time          = $entry[6];
    $start_date    = $entry[8];
    $end_date      = $entry[9];

    // Selected days
    for($e=1;$e<=7;$e++) {
      if (strlen($entry["10.$e"])>0) {
        $days_array[] = $e;
      }
    }

    // Create event
    $event        = 	array ( 'Name'            => $name,
                              'Description'     => $description,
                              'Approved'        => $approved,
                              'OrganizerID'     => $organizer,
                              'Location'        => $location,
                              'ExternalEventID' => $external_id );

    // Administration units
    $administrations = explode(',', $entry[3]);
    foreach($administrations as $unit) {
      $administration_units[] = HelsingborgEventModel::get_administration_id_from_name($unit)->AdministrationUnitID;
    }

    // Event types
    $event_types  = explode(',', $entry[2]);

    // Create time/times
    $event_times = array();
    if ($single_date) { // Single occurence
      $event_time = array('Date'  => $single_date,
                          'Time'  => $time,
                          'Price' => 0);
      array_push($event_times, $event_time);
    } else { // Must be start and end then
      $dates_array = create_date_range_array($start_date, $end_date);
      $filtered_days = filter_date_array_by_days($dates_array, $days_array);

      foreach($filtered_days as $date) {
        $event_time = array('Date'  => $date,
                            'Time'  => $time,
                            'Price' => 0);
        array_push($event_times, $event_time);
      }
    }

    // Image
    if ($entry[16])
      $image = handle_gf_image($entry[16], $entry[13]);

    // Now create the Event in DB
    HelsingborgEventModel::create_event($event, $event_types, $administration_units, $image, $event_times);

    // Now remove the entry from GF !
    delete_form_entry($entry);
  }
}


function handle_gf_image($path, $author) {
  $image_path   = $path;
  $file_name    = basename($image_path);
  $uploads      = wp_upload_dir();
  $new_path     = $uploads['basedir'].'/eventimages/'.$file_name;
  $save_path     = $uploads['baseurl'].'/eventimages/'.$file_name;

  if (!file_exists($uploads['basedir'].'/eventimages/')) {
    mkdir($uploads['basedir'].'/eventimages/', 0777, true);
  }

  if (!copy($image_path, $new_path))
    return false;

  return array( 'ImagePath' => $save_path, 'Author' => $auther);
}

function delete_form_entry( $entry ) {
  $delete = GFAPI::delete_entry( $entry['id'] );
  $result = ( $delete ) ? "entry {$entry['id']} successfully deleted." : $delete;
  GFCommon::log_debug( "GFAPI::delete_entry() - form #{$form['id']}: " . print_r( $result, true ) );
}

/**
 * Filter the days so only those in $days_array is returned.
 * @param string $dates_array Array with date strings
 * @param string $days_array Array with numbers representing days. (1 Monday - 7 Sunday)
 * @return array with dates
 */
function filter_date_array_by_days($dates_array, $days_array) {
  $return_array=array();
  foreach($dates_array as $date_string) {
    if (in_array(date('N', strtotime($date_string)), $days_array)) {
      array_push($return_array, $date_string);
    }
  }
  return $return_array;
}

/**
 * Creates an Array with strings with all dates between the from and to dates inserted.
 * @param string $strDateFrom Date string from
 * @param string $strDateTo Date string to
 */
function create_date_range_array($strDateFrom,$strDateTo)
{
    $aryRange=array();
    $iDateFrom=mktime(1,0,0,substr($strDateFrom,5,2),substr($strDateFrom,8,2),substr($strDateFrom,0,4));
    $iDateTo=mktime(1,0,0,substr($strDateTo,5,2),substr($strDateTo,8,2),substr($strDateTo,0,4));
    if ($iDateTo>=$iDateFrom)
    {
        array_push($aryRange,date('Y-m-d',$iDateFrom)); // first entry
        while ($iDateFrom<$iDateTo)
        {
            $iDateFrom+=86400; // add 24 hours
            array_push($aryRange,date('Y-m-d',$iDateFrom));
        }
    }
    return $aryRange;
}

/**
 * trims text to a space then adds ellipses if desired
 * @param string $input text to trim
 * @param int $length in characters to trim to
 * @param bool $ellipses if ellipses (...) are to be added
 * @param bool $strip_html if html tags are to be stripped
 * @param bool $strip_style if css style are to be stripped
 * @return string
 */
function trim_text($input, $length, $ellipses = true, $strip_tag = true,$strip_style = true) {
    //strip tags, if desired
    if ($strip_tag) { $input = strip_tags($input); }

    //strip tags, if desired
    if ($strip_style) { $input = preg_replace('/(<[^>]+) style=".*?"/i', '$1',$input); }

    if($length=='full') { $trimmed_text=$input; }
    else
    {
        //no need to trim, already shorter than trim length
        if (strlen($input) <= $length) { return $input; }

        //find last space within length
        $last_space = strrpos(substr($input, 0, $length), ' ');
        $trimmed_text = substr($input, 0, $last_space);

        //add ellipses (...)
        if ($ellipses) { $trimmed_text .= '...'; }
    }
    return $trimmed_text;
}

function the_breadcrumb() {
    global $post;
    $title = get_the_title();
    echo '<ul class="breadcrumbs">';
    if (!is_front_page()) {
        if (is_category() || is_single()) {
            echo '<li>';
            the_category(' <li> ');
            if (is_single()) {
                echo '<li>';
                the_title();
                echo '</li>';
            }
        } elseif (is_page()) {
            if($post->post_parent){
                $anc = get_post_ancestors( $post->ID );
                $title = get_the_title();
                foreach ( $anc as $ancestor ) {
                    $output = '<li><a href="'.get_permalink($ancestor).'" title="'.get_the_title($ancestor).'">'.get_the_title($ancestor).'</a></li></li>' . $output;
                }
                echo $output;
                echo '<strong title="'.$title.'"> '.$title.'</strong>';
            } else {
                echo '<li><strong> '.get_the_title().'</strong></li>';
            }
        }
    }
    echo '</ul>';
}

// Add AJAX functions for admin. So Event may be changed by users
// Note: wp_ajax_nopriv_X is not used, since events cannot be changed by other than logged in users
add_action( 'wp_ajax_approve_event', 'approve_event_callback' );
add_action( 'wp_ajax_deny_event',    'deny_event_callback' );
add_action( 'wp_ajax_save_event',    'save_event_callback' );

// Function for approving events, returns true if success.
function approve_event_callback() {
  global $wpdb;
  $id     = $_POST['id'];
  $result = HelsingborgEventModel::approve_event($id);
  die();
}

// Function for denying events, returns true if success.
function deny_event_callback() {
  global $wpdb;
  $id     = $_POST['id'];
  $result = HelsingborgEventModel::deny_event($id);
  
  die();
}

// Function for saving events, returns true if success.
function save_event_callback() {
	global $wpdb;

  $id          = $_POST['id'];
  $type        = $_POST['type'];
  $name        = $_POST['name'];
  $description = $_POST['description'];
  $days        = $_POST['days'];
  $start_date  = $_POST['startDate'];
  $end_date    = $_POST['endDate'];
  $time        = $_POST['time'];
  $units       = $_POST['units'];
  $types       = $_POST['types'];
  $organizer   = $_POST['organizer'];
  $location    = $_POST['location'];
  $imageUrl    = $_POST['imageUrl'];
  $author      = $_POST['author'];

  // Create event
  $event        = 	array ( 'EventID'         => $id,
                            'Name'            => $name,
                            'Description'     => $description,
                            'Approved'        => $approved,
                            'OrganizerID'     => $organizer,
                            'Location'        => $location,
                            'ExternalEventID' => $external_id );

  // Event types
  $event_types  = explode(',', $types);

  // Administration units
  if ($units && !empty($units)){
    $administrations = explode(',', $units);
    foreach($administrations as $unit) {
      $administration_units[] = HelsingborgEventModel::get_administration_id_from_name($unit)->AdministrationUnitID;
    }
  }

  // Image
  if ($imageUrl)
    $image = array( 'ImagePath' => $imageUrl, 'Author' => $author);

  // Create time/times
  $event_times = array();
  if ($single_date) { // Single occurence
    $event_time = array('Date'  => $single_date,
                        'Time'  => $time,
                        'Price' => 0);
    array_push($event_times, $event_time);
  } else { // Must be start and end then
    $dates_array = create_date_range_array($start_date, $end_date);
    $filtered_days = filter_date_array_by_days($dates_array, $days_array);

    foreach($filtered_days as $date) {
      $event_time = array('Date'  => $date,
                          'Time'  => $time,
                          'Price' => 0);
      array_push($event_times, $event_time);
    }
  }

  $result = HelsingborgEventModel::update_event($event, $event_types, $administration_units, $image, $times);

  echo $result;

	die();
}


add_filter('nav_menu_css_class', 'my_css_attributes_filter', 100, 1);
add_filter('nav_menu_item_id', 'my_css_attributes_filter', 100, 1);
add_filter('page_css_class', 'my_css_attributes_filter', 100, 1);
?>
