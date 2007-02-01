<?php 
$path = TEMPLATEPATH . '/images/headers/';

function findrandompicture() {
	global $path;
	$picture_dir = @ dir($path);
	if ($picture_dir) {
		while (($file = $picture_dir->read()) !== false) {
			if (!preg_match('|^\.+$|', $file) and !preg_match('|^CVS$|', $file) and !preg_match('|^\.|', $file)) {
				$picture_files[] = $file;
			}
		}
	}
	$size = count($picture_files);
	return ($picture_files[rand(0, $size-1)]);
}

function k2uploadupdate() {
	global $path;

	if ($_POST['unwakeableheadertextcolor'] != "") { update_option('unwakeableheadertextcolor' , $_POST['unwakeableheadertextcolor']); }
 	if ($_POST['unwakeableheadertextalignment'] != "") { update_option('unwakeableheadertextalignment', $_POST['unwakeableheadertextalignment']); }
 	if ($_POST['unwakeableheadertextfontsize'] != "") { update_option('unwakeableheadertextfontsize', $_POST['unwakeableheadertextfontsize']); }
  	if ($_POST['backgroundcolor'] != "") { $backgroundcolor = substr(str_replace("#","", $_POST['backgroundcolor']),0,6); update_option('unwakeableheaderbackgroundcolor', "#".$backgroundcolor); }  
  	if ($_POST['brightcolor'] != "" and ($_POST['brightcolor'] != get_option('unwakeable_headertextcolor_bright'))) { update_option('unwakeableheadertextcolor_bright', $_POST['brightcolor']); }
	if ($_POST['darkcolor'] != "" and ($_POST['darkcolor'] != get_option('unwakeable_headertextcolor_dark'))) { update_option('unwakeableheadertextcolor_dark', $_POST['darkcolor']); }
	if (get_option('unwakeable_imagerandomfeature') == "") { update_option('unwakeableimagerandomfeature', 'n'); }
	if ((($_POST['userandomfeature'] != get_option('unwakeable_imagerandomfeature')) and ($_POST['userandomfeature']))) { update_option('unwakeableimagerandomfeature', $_POST['userandomfeature']); }
	if ($_FILES['picture']['name'] != "" and $_FILES['picture']['size'] > 0) { $target_path =  $path; move_uploaded_file($_FILES['picture']['tmp_name'], $target_path.$_FILES['picture']['name']); }
	if ($_POST['upload_activate'] == "active") { update_option('unwakeableheader_picture', $_FILES['picture']['name']); }
	if (($_POST['unwakeableheader_picture'] != "") and (trim($_POST['unwakeableheader_picture']) != trim(get_option('unwakeable_header_picture'))) and ($_POST['upload_activate'] != "active")) { $k2header_picture = $_POST['unwakeableheader_picture']; update_option('unwakeableheader_picture', $k2header_picture, '',''); }
}

function k2_picupload_admin() {
	add_submenu_page('themes.php', __('Unwakeable Custom Header','k2_domain'), __('Unwakeable Custom Header','k2_domain'), 5, 'options/display/headers.php', 'uploadmenu');
	}

function uploadmenu() {
	global $path;
	$error = FALSE;

	if (!is_dir($path)) { 
		echo "<div class='error'>" . sprintf(__('<strong>ERROR:</strong><p>%s is missing.</p><p>Please add this directory at your earliest convenience.</p><p>Remember to also <strong>chmod 777</strong> the headers directory.</p>','k2_domain'), $path ) . "</div>\n"; 
		$error = TRUE;
	} 

	global $path;
	if (isset($_POST['Submit'])) {
?>

<div class="updated">
	<p><?php _e('Options has been updated.','k2_domain'); ?></p>
</div>

<?php
}
?>

<div class="wrap">
<h2><?php _e('Unwakeable Custom Header Options','k2_domain'); ?></h2>
<form name="dofollow" action="" method="post" enctype="multipart/form-data">
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options','k2_domain') ?> &raquo;" /></p>
  <input type="hidden" name="action" value="<?php k2uploadupdate(); ?>" />
  <table width="700px" cellspacing="2" cellpadding="5" class="editform">
  <tr valign="top">
	<th scope="row"><?php _e('Upload Picture','k2_domain'); ?></th>
	<td>
		<label for="k2upload_picture">
		<input <?php echo ($error ? 'disabled' : ''); ?> type="file" name="picture">
		<p><small><?php _e('Choose the picture you would like to upload to the server','k2_domain'); ?></small><p>
	</td>
  </tr>
  <tr>
	<th valign="top" scope="row"><?php _e('Active on upload','k2_domain'); ?></th>
	<td>
		<label for="k2upload_activate">
		<input type="checkbox" name="upload_activate" value="active">
		<p><small><?php _e('Automatically activate the uploaded picture as the active picture','k2_domain'); ?></small></p>
  <tr valign="top">
	<th scope="row"><?php _e('Select random picture','k2_domain'); ?></th>
	<td>
		<label for="k2upload_picture">
		<?php _e('Yes','k2_domain'); ?><input type="radio" name="userandomfeature" value="yes" <?php echo (get_option('unwakeable_imagerandomfeature') == "yes" ? 'checked' : '') ?> >&nbsp;
		<?php _e('No','k2_domain'); ?><input type="radio" name="userandomfeature" value="no" <?php echo (get_option('unwakeable_imagerandomfeature') == "no" ? 'checked' : '') ?> >
		<p><small><?php _e('Show random header picture','k2_domain'); ?></small><p>
	</td>
  </tr>

  <tr valign="top">
	<th scope="row"><?php _e('Manage Pictures','k2_domain'); ?></th>
	<td>
		<label for="k2manage_pictures">
		<?php
		global $wpdb;
		$picname = get_option('unwakeable_header_picture');
		if ($picname != '')
		{
			$picture_title = $picname;
		}
		else
		{
			$picture_title = __('No Picture','k2_domain');
		}
		?>
		<select name="k2header_picture">
			<option value="<?php echo get_option('unwakeable_header_picture')?>"><?php echo $picture_title; ?></option>
			<option value="">----</option>
			<option value="No Picture"><?php _e('No Picture','k2_domain'); ?></option>
			<?php
			$picture_dir = @ dir($path);
			if ($picture_dir) {
				while(($file = $picture_dir->read()) !== false) {
					if (!preg_match('|^\.+$|', $file) and !preg_match('|^CVS$|', $file) and !preg_match('|^\.|', $file)) {
						$picture_files[] = $file;
					}
				}

				if ($picture_dir or $picture_files) {
					foreach ($picture_files as $picture_file) {
						echo "<option value=\"".$picture_file."\">".$picture_file."</option>";
					}
				}
			}
			?>
		</select>
		<p><small><?php _e('Select the picture you want to show at the frontpage','k2_domain'); ?></small></p>
  </tr>
  
<tr valign="top">
	<th scope="row"><?php _e('Background color','k2_domain'); ?></th>
	<td>
		<input type="text" name="backgroundcolor" value="<?php echo strtoupper(get_option('unwakeable_headerbackgroundcolor')); ?>">
		<p><small><?php _e('Define the background color for the header (default is #3371A3).','k2_domain'); ?></small></p>
</tr>
  
<tr valign="top">
	<th scope="row"><?php _e('Set header text alignment','k2_domain'); ?></th>
	<td>
		<label for="k2upload_align">
    <select name="k2headertextalignment">
      <option <?php echo (get_option('unwakeable_headertextalignment') == 'left' ? 'selected' : ''); ?> value="left"><?php _e('Left','k2_domain'); ?></option>
      <option <?php echo (get_option('unwakeable_headertextalignment') == 'center' ? 'selected' : ''); ?> value="center"><?php _e('Center','k2_domain'); ?></option>
      <option <?php echo (get_option('unwakeable_headertextalignment') == 'right' ? 'selected' : ''); ?> value="right"><?php _e('Right','k2_domain'); ?></option>
    </select>
		<p><small><?php _e('Set the alignment of the text over the header image.','k2_domain'); ?></small><p>
	</td>
</tr>
  
<tr valign="top">
	<th scope="row"><?php _e('Set header text size','k2_domain'); ?></th>
	<td>
		<input type="text" size="2" name="k2headertextfontsize" value="<?php echo get_option('unwakeable_headertextfontsize'); ?>"> px.
		<p><small><?php _e('Set the font size in \'px\' of the text over the header image.','k2_domain'); ?></small><p>
	</td>
</tr>

<tr valign="top">
	<th scope="row"><?php _e('Set header text color','k2_domain'); ?></th>
	<td>
		<label for="k2upload_color">
    <select name="k2headertextcolor">
      <option <?php echo (get_option('unwakeable_headertextcolor_bright') == get_option('unwakeable_headertextcolor') ? 'selected' : ''); ?> value="<?php echo get_option('unwakeable_headertextcolor_bright'); ?>"><?php _e('Bright','k2_domain'); ?> - <?php echo get_option('unwakeable_headertextcolor_bright'); ?></option>
      <option <?php echo (get_option('unwakeable_headertextcolor_dark') == get_option('unwakeable_headertextcolor') ? 'selected' : ''); ?> value="<?php echo get_option('unwakeable_headertextcolor_dark'); ?>"><?php _e('Dark','k2_domain'); ?> - <?php echo get_option('unwakeable_headertextcolor_dark'); ?></option>
    </select>
		<p><small><?php _e('Set the color of the text over the header image.','k2_domain'); ?></small><p>
	</td>
</tr>

<tr valign="top">
    <th scope="row"><?php _e('Define Bright & Dark text color','k2_domain'); ?></th>
    <td valign="top">
       <table border=0 cellspacing=0 cellpadding=2>
          <tr>
            <td><?php _e('Bright Color','k2_domain'); ?></td>
            <td><input type="text" name="brightcolor" value="<?php echo get_option('unwakeable_headertextcolor_bright'); ?>"></td>
          </tr>
          <tr>
            <td><?php _e('Dark Color','k2_domain'); ?></td>
            <td><input type="text" name="darkcolor" value="<?php echo get_option('unwakeable_headertextcolor_dark'); ?>"></td>
          </tr>
       </table>
    </td>
    <tr>
  </table> 
<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options','k2_domain') ?> &raquo;" /></p>

</form>
</div>
<div class="wrap">
 	<p style="text-align: center;"><?php printf(__('Help to be had at the %1$s or in the %2$s.','k2_domain'), '<a href="http://getk2.com/forum/" title="' .__('unwakeable Support Forums','k2_domain') . '">' .__('unwakeable Support Forums','k2_domain') . '</a>', '<a href="http://k2.stikipad.com/" title="' .__('unwakeable Documentation','k2_domain') . '">' .__('unwakeable Documentation','k2_domain') . '</a>' ) ?></p>
</div>

<?php
}

add_action('admin_menu', 'k2_picupload_admin');

function change_hpicture() {
	if (get_option('unwakeable_imagerandomfeature') == "yes") {
		$picture = findrandompicture();
	} else {
		$picture = get_option('unwakeable_header_picture');
	}	

	if ($picture != "") {
		if ($picture != __('No Picture','k2_domain')) {
			$pic = 'background: url('.get_bloginfo('template_url').'/images/headers/'.$picture.') no-repeat center center;';
		}
	echo '
		<style type="text/css">
			#header {
				'.$pic.'
				background-color: '.get_option(k2headerbackgroundcolor).';
				}

			#header h1 {
				text-align:'.get_option('unwakeable_headertextalignment').';
				font-size:'.get_option('unwakeable_headertextfontsize').'px;
				}
      		
			h1, h1 a,h1 a:visited, #header .description {
				color: '.get_option('unwakeable_headertextcolor').';
      			}

			.description {
				display: block !important;
				text-align:'.get_option('unwakeable_headertextalignment').';
				}
		</style>
	';
	}
}
	add_action('wp_head','change_hpicture');
?>
