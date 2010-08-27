<?php
/*
Plugin Name: Portable Contacts
Plugin URI: http://notizBlog.org
Description: Some kind of <em>Portable Contacts Delegation</em> based on the awesome <a href="http://portablecontactsdemo.janrain.com/">OpenID/Portable Contacts Demo by JanRain</a>.
Version: 0.3
Author: Matthias Pfefferle
Author URI: http://pfefferle.org/
*/

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
    define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
    define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
    define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

add_action( 'admin_menu', array('PortableContacts', 'adminMenu'));
add_filter( 'xrds_simple', array('PortableContacts', 'addXrdsSimple' ));

if (is_plugin_page()) {
  PortableContacts::optionsPage();
} else {

class PortableContacts {
  function showCommunity() {
    echo stripslashes(get_option('livecommunity_code'));
  }

  function adminMenu() {
    add_options_page(
      __('Portable Contacts Delegation'),
      __('Portable Contacts'), 5, __FILE__);
  }

  /**
   * Contribute the Portable Contact Service to XRDS-Simple.
   *
   * @param array $xrds current XRDS-Simple array
   * @return array updated XRDS-Simple array
   */
  function addXrdsSimple($xrds) {
    if (function_exists(xrds_add_service) && get_option('porc_xrds')) {
      $xrds = xrds_add_service($xrds, 'main', 'Portable Contacts Delegation',
        array(
          'Type' => array( array('content' => 'http://portablecontacts.net/spec/1.0') ),
          'URI' => array( array('content' => get_option('porc_xrds') ) ),
        )
      );
    }

    return $xrds;
  }

  function optionsPage() {
    wp_enqueue_script( 'jquery' );
    if (isset($_POST['Submit'])) {
      update_option('porc_xrds', $_POST['porc_xrds']);
?>
<div class="updated">
  <p><strong><?php _e('Options saved.') ?></strong></p>
</div>
<?php
    }
?>

<div class="wrap">
  <h2><?php _e('Add a Portable Contacts Provider'); ?></h2>

<?php if (function_exists(xrds_add_service)) { ?>
  <p><?php echo __("Select a provider, or enter your provider's endpoint URL in the box."); ?></p>

  <p>
    <a href="#" onclick="jQuery('#porc_xrds').val('http://pulse.plaxo.com/pulse/pdata/contacts')" title="<?php _e("Add Plaxo as Portable Contacts Provider"); ?>">
      <img src="<?php echo WP_PLUGIN_URL ?>/portable-contacts/img/plaxo_logo.gif" alt="<?php _e("Plaxo Logo"); ?>" id="plaxo" class="plaxo" />
    </a>
  </p>

  <form name="livecommunity" method="post" action="">
    <input type="hidden" name="action" value="update" />

    <input name="porc_xrds" id="porc_xrds" value="<?php echo stripslashes(get_option('porc_xrds')); ?>" size="50" />

    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" />
    </p>

    <p><?php _e('The Idea is based on the awesome <a href="http://portablecontactsdemo.janrain.com/">JanRain OpenID/Portable Contacts Demo</a>.') ?>
  </form>
<?php } else { ?>
  <p><?php _e('You have to install the <a href="http://wordpress.org/extend/plugins/xrds-simple/">DiSo XRDS-Simple Plugin</a> first to use the Portable Contacts Delegation.'); ?></p>
<?php } ?>
</div>
<?php
    }
  }
}
?>
