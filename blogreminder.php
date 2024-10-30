<?php
/*
Plugin Name: Blog Update Reminder
Plugin URI: http://samgruskin.com/programming/blog-update-reminder/
Description: Keep track of multiple users' blog posting frequency and email-notifies users daily if they haven't posted within a set timeframe.
Version: 2.0.3
Author: Samantha Gruskin
Author URI: http://samgruskin.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=H8WQ2JLE6GJSQ
License: GPL2
*/

/*  Copyright (C) 2011 Samantha Gruskin  (email : sam@samgruskin.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_filter('plugin_row_meta', 'bur_plugin_links', 10, 2);
function bur_plugin_links($links, $file) {
	if ( $file == trailingslashit(plugin_basename(dirname(__FILE__))).'blogreminder.php' ) {
		$links[] = "<a href='options-general.php?page=blogreminder'>" . __('Settings', 'blogreminder') . "</a>";
		$links[] = "<a href='https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=H8WQ2JLE6GJSQ' target='_blank'><strong>" . __('Donate', 'blogreminder') . "</strong></a>";
	}
	return $links;
}

// Declare custom time intervals for email reminders
add_filter( 'cron_schedules', 'cron_add_intervals' );
function cron_add_intervals($schedules) {
    $schedules['twodays'] = array(
 		'interval' => 172800,
 		'display' => __('Once Every Two Days')
 	);
 	$schedules['threedays'] = array(
 		'interval' => 259200,
 		'display' => __('Once Every Three Days')
 	);
    $schedules['weekly'] = array(
 		'interval' => 604800,
 		'display' => __('Once Weekly')
 	);
 	$schedules['biweekly'] = array(
 	    'interval' => 1209600,
 	    'display' => __('Once Biweekly')
 	);
 	$schedules['monthly'] = array(
 	    'interval' => 2419200,
 	    'display' => __('Once Monthly')
 	);
    return $schedules;
}

// Schedule daily check upon plugin activation
register_activation_hook(__FILE__, 'bur_activation');
add_action('bur_hook', 'check_days');
function bur_activation() {
    $set_time = 1304431200; // 9:00AM EST
    $file = ABSPATH . "/wp-content/plugins/blog-update-reminder/blogreminder.data";
	if (file_exists($file)) {
	    $bd = read_data();
	    wp_schedule_event($set_time, $bd['schedule'], 'bur_hook');
	} else {
	    wp_schedule_event($set_time, 'daily', 'bur_hook');
	}
}

// Remove scheduled daily checks upon plugin deactivation
register_deactivation_hook(__FILE__, 'bur_deactivation');
function bur_deactivation() {
	wp_clear_scheduled_hook('bur_hook');
}

// Update email frequency schedule
function update_schedule($interval) {
    $set_time = 1304431200; // 9:00AM EST
    wp_clear_scheduled_hook('bur_hook');
    wp_schedule_event($set_time, $interval, 'bur_hook');
}

// Check if blog user is due to make a blog post
function check_days() {
	$blogusers = get_blogusers();
	$blogdata = read_data();
	foreach ($blogusers as $buser) {
		if ($blogdata[$buser->ID] != 0) {
			$lastpost = strtotime($buser->post_date);
			$timeinterval = ($blogdata[$buser->ID])*86400;
			$timesincelast = time() - $lastpost;
			if ($timesincelast >= $timeinterval) {
				send_reminder($buser, floor($timesincelast/86400), $blogdata[$buser->ID]);	
			}
		}
	}
}

// Prepare reminder email and send it
function send_reminder($u, $days, $commitment) {
	$bur_header  = 'MIME-Version: 1.0' . "\r\n";
	$bur_header .= 'Content-type:text/html;charset=iso-8859-1' . "\r\n";
	$bur_header .= "From: Administrator <" . get_bloginfo('admin_email') . ">\r\n";
	$bur_to = $u->user_email;
	$bur_subject = "[" . get_bloginfo('name') . "] Blog Update Reminder";
	$bur_subject = html_entity_decode($bur_subject, ENT_QUOTES);
	$bur_message = "Hi " . $u->display_name . ",<br/><br/>";
	$bur_message .= "Our records indicate that you have not made a new blog post in <a href='" . site_url()  . "'>" . get_bloginfo('name') . "</a> in over " . $days . " day(s). Your commitment is currently set to one blog post every " . $commitment . " day(s).<br/><br/>";
	$bur_message .= "Please make a new blog post to stop receiving these notifications, or contact your blog administrator to update your preferences.<br/><br/>";
	$bur_message .= "Thank you!<br/><br/><strong>Blog Update Reminder</strong><br/>Wordpress plugin";
	$bur_email = mail($bur_to, $bur_subject, $bur_message, $bur_header);
}

// Get list of blog users with timestamps from wordpress database
function get_blogusers() {
	global $wpdb;
	$blogusers = $wpdb->get_results("SELECT u.ID, u.user_nicename, u.display_name, u.user_email, p.post_date FROM $wpdb->users u INNER JOIN (SELECT post_author, MAX(post_date) post_date FROM $wpdb->posts WHERE post_type = 'post' AND post_title != 'Auto Draft' AND post_title != '' GROUP BY post_author) p WHERE u.ID = p.post_author ORDER BY u.display_name");
	return $blogusers;
}

// Initialize each user at 0 days if blogreminder.data does not exist
function initialize_data($blogusers) {
	foreach ($blogusers as $buser) {
		$bdarr[$buser->ID] = 0;
	}
	$bdarr['schedule'] = "daily";
	save_data($bdarr);
}

// Overwrite and save new blogreminder data
function save_data($bdarr) {
	$file = ABSPATH . "/wp-content/plugins/blog-update-reminder/blogreminder.data";
	$fh = fopen($file, "w") or die("Cannot open data file for writing.");
	$bdata = urlencode(serialize($bdarr));
	fwrite($fh, $bdata);
	fclose($fh);
}

// Reads stored array from blogreminder.data file, returns parsable array
function read_data() {
	clearstatcache(); // Clears stat cache on linux-based systems (which stores previous file size)
	$file = ABSPATH . "/wp-content/plugins/blog-update-reminder/blogreminder.data";
	$fh = fopen($file, "r") or die("Cannot open data file for reading.");
	$blogdata = fread($fh, filesize($file)+100);
	$bdata = unserialize(urldecode($blogdata));
	fclose($fh);
	return $bdata;
}

// Updates blogreminder.data array with new set of data
function update_data($blogdata) {
	for ($i=0; $i<sizeof($_POST['id']); $i++) {
		// Validation of user input
		if (!is_numeric($_POST['days'][$i]) || is_null($_POST['days'][$i]) || $_POST['days'][$i] < 0) {
			return '<span style="color:#F00;">Invalid input, please use positive integers only.</span>';
		}
		$newdata[$_POST['id'][$i]] = $_POST['days'][$i];
	}
	$newdata['schedule'] = $_POST['schedule'];
	// Save new array to file
	if ($blogdata != $newdata) {
	    if ($blogdata['schedule'] != $newdata['schedule']) {
	        update_schedule($newdata['schedule']);
	    }
		save_data($newdata);
	}
	return '<span style="color:#090;">Changes saved successfully.</span>';
}

// Adds new user ID to blogreminder.data array (new user will not show up until a blog post is made by this user)
function add_user_data($blogdata, $blogusers) {
	foreach ($blogusers as $buser) {
		if (!array_key_exists($buser->ID, $blogdata)) {
			$blogdata[$buser->ID] = 0;
		}
	}
	// Save new array to file
	save_data($blogdata);
}

// Blog Update Reminder plugin options available only to Administrator users
if (is_admin()) {
	add_action('admin_menu', 'bur_admin_menu');
	function bur_admin_menu() {
		add_options_page('Blog Update Reminder', 'Blog Update Reminder', 'administrator', 'blogreminder', 'bur_plugin_page');
	}
}

// Display plugin options page
function bur_plugin_page() {
	// Communicate with wordpress database
	$blogusers = get_blogusers();

	// Create new blogreminder.data file if it does not already exist
	$file = ABSPATH . "/wp-content/plugins/blog-update-reminder/blogreminder.data";
	if (!file_exists($file)) {
		initialize_data($blogusers);
	}
	
	// Read blogreminder.data array
	$blogdata = read_data();
	
	// Checks if new users that have made posts exist
	if (sizeof($blogusers) != sizeof($blogdata)) {
		add_user_data($blogdata, $blogusers);
		$blogdata = read_data();
	}

	$page_message = "";
	// Update blogreminder.data array after submitting changes
	if ( $_POST['bur-submit'] ) {
		$page_message = update_data($blogdata);
		$blogdata = read_data();
	}
	
	// Grab email schedule variable
	$emailsched = $blogdata["schedule"];
	unset($blogdata["schedule"]);
	
	// Begin HTML output for plugin options page
?>

	<div id="blogreminder" style="width:600px;"><div class="wrap">	
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>Blog Update Reminder Plugin Settings</h2>
		<p>Define a minimum interval of days each author should publish a blog post. Each author will be sent a reminder email if the interval since the last blog post exceeds the number of days set with this plugin. Disable this plugin for certain users by setting the interval to '0'.</p>
		<p><em>Authors that have not yet made a blog post will not appear in this list.</em></p>
		<br/>
		<form method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<table class="widefat">
			<thead>
				<tr>
					<th style="width:200px;text-align:left;">Name</th>
					<th style="width:150px;text-align:left;">Username</th>
					<th style="width:150px;text-align:left;">Last Post Date</th>
					<th style="width:100px;text-align:left;">Interval (Days)</th>
				</tr>
			</thead>
			<tbody>
			<?php
				// Display each active user's information and number of days setting
				foreach ($blogusers as $buser) {
					echo '<tr>';
					echo '<td>' . $buser->display_name . '</td>';
					echo '<td>' . $buser->user_nicename . '</td>';
					echo '<td>' . date('M d, Y', strtotime($buser->post_date)) . '</td>';
					echo '<td><input type="text" name="days[]" value="'. $blogdata[$buser->ID] .'"/><input type="hidden" name="id[]" value="'. $buser->ID .'"/></td>';
					echo '</tr>';
				}
			?>
			</tbody>
		</table>
		<br/><br/>
		<p>Set how often email reminders will be sent to all users. By default, this is set to 'Daily'.</p>
		<ul><li>
		<label for="schedule"><strong>Email Reminder Frequency: </strong></label>
		<select name="schedule">
		<?php
		    foreach(wp_get_schedules() as $value => $interval) {
		        echo "<option";
		        if ($emailsched == $value) { echo " selected"; }
		        echo " value='" . $value . "'>";
		        echo $interval["display"];
		        echo "</option>";
		    }
		?>
		</select>
		</li></ul>
		<p class="submit" align="right"><input type="submit" class="button-primary" name="bur-submit" value="<?php _e('Save Changes') ?>"/></p>
		<div class="page-message" style="text-align:right;font-style:italic;"><?php echo $page_message; ?></div>
		</form>
	</div></div>
<?php 
	}	// End HTML output
?>
