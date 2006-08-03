<div class="wrap">

<?php fp_load_settings(); ?>

<h2>Flickr-post Settings</h2>

<a name="form"></a>
<form name="fp_settings" id="fp_settings" method="post" action="<?php echo fp_form_action(); ?>">

  <table class="editform" width="100%" cellspacing="2" cellpadding="5">
    <tr>
      <th scope="row">Flickr username:</th>
      <td><input type="text" name="flickr_username" value="<?php echo $_REQUEST['flickr_username']; ?>" style="width: 95%" /></td>
    </tr>
    <tr>
      <th scope="row">Cache timeout (in seconds):</th>
      <td><input type="text" name="timeout" value="<?php echo $_REQUEST['timeout']; ?>" style="width: 95%" /></td>
    </tr>
    <tr>
      <th scope="row">Number of recent photos:</th>
      <td><input type="text" name="recent" value="<?php echo $_REQUEST['recent']; ?>" style="width: 95%" /></td>
    </tr>
    <tr>
      <th scope="row">CSS class for images:</th>
      <td><input type="text" name="image_class" value="<?php echo $_REQUEST['image_class']; ?>" style="width: 95%" /></td>
    </tr>
  </table>

  <p class="submit">
    <input type="hidden" name="fp_action" value="set" />
    <input type="submit" name="submit" value="Save Settings &raquo;" />
  </p>

</form>

</div>
