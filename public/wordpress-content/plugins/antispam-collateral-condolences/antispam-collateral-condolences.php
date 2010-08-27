<?php
/*
Plugin Name: Antispam Collateral Condolences
Version: 0.3
Plugin URI: http://txfx.net/wordpress-plugins/antispam-collateral-condolences/
Description: Notifies people when their comment is moderated or caught as spam, so they aren't left wondering.
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

function cws_acc_die_if_spam( $original_redirect, $comment ) {
	if ( $comment && cws_acc_is_catchable_type( $comment->comment_approved ) ) {
		$original_redirect = preg_replace('|#.*?$|', '', $original_redirect); // strip the #anchor
		$caught_as = ( '0' == $comment->comment_approved ) ? 'moderation' : 'spam';
		return add_query_arg( 'caught_as', $caught_as, add_query_arg( 'cws_acc', time(), $original_redirect ) ) . '#comment-caught';
	}
	return remove_query_arg( 'caught_as', remove_query_arg( 'cws_acc', $original_redirect ) );
}

function cws_acc_is_catchable_type( $type ) {
	switch ( get_option( 'cws_acc_comment_status' ) ) {
		case 'moderated' :
			if ( $type == '0' )
				return true;
			break;
		case 'spam' :
			if ( $type == 'spam' )
				return true;
			break;
		case 'both' :
		default :
			if ( $type == '0' || $type == 'spam' )
				return true;
			break;
	}
	return false;
}

function cws_acc_comment_was_caught() {
	return isset( $_GET['caught_as'] );
}

function cws_acc_get_caught_message() {
	if ( !cws_acc_comment_was_caught() )
		return false;
	elseif ( 'moderation' == $_GET['caught_as'] )
		return __( 'Your comment was placed in the moderation queue and the site administrator has been notified.  Your comment will not appear on the site until the administrator has approved it.', 'antispam-collateral-condolences' );
	elseif ( 'spam' == $_GET['caught_as'] )
		return __( "Your comment was caught by this site's anti-spam defenses.  Please notify the site administrator so your comment can be rescued.", 'antispam-collateral-condolences' );
	else
		return false;
}

function cws_acc_alert_user_js() {
	if ( !cws_acc_comment_was_caught() )
		return;
	$caught_message = cws_acc_get_caught_message();
	echo "<script type='text/javascript'><!--\nalert('" . esc_js( $caught_message ) . "');\n--></script>";
}

function cws_acc_alert_user_comment_form() {
	if ( !cws_acc_comment_was_caught() )
		return;
	$caught_message = cws_acc_get_caught_message();
	echo '<p id="cws-acc-comment-caught">' . esc_html( $caught_message ) . '</p>';
}

function cws_acc_options_page() {
?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>Antispam Collateral Condolences</h2>
		<form method="post">
			<?php wp_nonce_field( 'cws-acc-update-options' ); ?>
			<table class="form-table">
			<tr valign="top">
			<th scope="row"><?php esc_html_e( 'Comment status', 'antispam-collateral-condolences' ); ?></th>
			<td><fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Comment status setting', 'antispam-collateral-condolences' ) ?></span></legend>
				<p>Which comment statuses should receive notification?</p>
				<label><input type='radio' name='cws_acc_comment_status' value='both' <?php checked( 'both', get_option( 'cws_acc_comment_status' ) ); ?> /> Spam and moderated</label><br />
				<label><input type='radio' name='cws_acc_comment_status' value='spam' <?php checked( 'spam', get_option( 'cws_acc_comment_status' ) ); ?> /> Spam only</label><br />
				<label><input type='radio' name='cws_acc_comment_status' value='moderated' <?php checked( 'moderated', get_option( 'cws_acc_comment_status' ) ); ?> /> Moderated only</label><br /></fieldset></td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Notification style', 'antispam-collateral-condolences' ); ?></th>
				<td><fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Notification style setting', 'antispam-collateral-condolences' ); ?></span></legend>
				<p>What notification style should be used?</p>
				<label><input type='radio' name='cws_acc_notification_style' value='js' <?php checked( 'js', get_option( 'cws_acc_notification_style' ) ); ?> /> Javascript popup</label><br />
				<label><input type='radio' name='cws_acc_notification_style' value='html' <?php checked( 'html', get_option( 'cws_acc_notification_style' ) ); ?> /> HTML</label><br />
				</fieldset></td>
			</tr>
			</table>
			<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes', 'antispam-collateral-condolences' ); ?>" />
		</form>
	</div>
<?php
}

function cws_acc_init() {
	global $pagenow;
	add_action( 'load-' . get_plugin_page_hook( 'cws-acc-options', 'options-general.php' ), 'cws_acc_save_options' );
}

function cws_acc_save_options() {
	if ( !$_POST )
		return;
	check_admin_referer( 'cws-acc-update-options' ); // Thwart CSRF
	foreach ( array( 'notification_style', 'comment_status' ) as $setting_fragment ) {
		$setting = 'cws_acc_' . $setting_fragment;
		$value = stripslashes( $_POST[$setting] );
		$value = cws_acc_clean_option( $value, $setting );
		update_option( $setting, stripslashes( $_POST[$setting] ) );
	}
}

function cws_acc_clean_option( $value, $option_name ) {
	switch ( $option_name ) {
		default :
			return preg_replace( '#[^a-z]#', '', $value );
			break;
	}
}

add_option( 'cws_acc_comment_status', 'both' );
add_option( 'cws_acc_notification_style', 'js' );

if ( 'html' == get_option( 'cws_acc_notification_style' ) )
	add_action( 'comment_form', 'cws_acc_alert_user_comment_form' );
else
	add_action( 'wp_head', 'cws_acc_alert_user_js' );

add_filter( 'comment_post_redirect', 'cws_acc_die_if_spam', 999, 2 );
add_action( 'admin_menu', create_function( '', "add_options_page( 'Antispam Collateral Condolences', 'Antispam Collateral Condolences', 'manage_options', 'cws-acc-options', 'cws_acc_options_page' );" ) );
add_action( 'admin_init', 'cws_acc_init' );
