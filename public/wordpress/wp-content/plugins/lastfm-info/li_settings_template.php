<div class="wrap">

<?php li_load_settings(); ?>

<h2>Last.fm Info Settings</h2>

<a name="form"></a>
<form name="li_settings" id="li_settings" method="post" action="<?php echo li_form_action(); ?>">

  <table class="editform" width="100%" cellspacing="2" cellpadding="5">
    <tr>
      <th scope="row">Last.fm username:</th>
      <td><input type="text" name="lastfm_username" value="<?php echo $_REQUEST['lastfm_username']; ?>" style="width: 95%" /></td>
    </tr>
    <tr>
      <th scope="row">Number of tracks to display:</th>
      <td><input type="text" name="tracks" value="<?php echo $_REQUEST['tracks']; ?>" style="width: 95%" /></td>
    </tr>
    <tr>
      <th scope="row">Refresh interval (seconds):</th>
      <td><input type="text" name="timeout" value="<?php echo $_REQUEST['timeout']; ?>" style="width: 95%" /></td>
    </tr>
  </table>

  <p class="submit">
    <input type="hidden" name="li_action" value="set" />
    <input type="submit" name="submit" value="Save Settings &raquo;" />
  </p>

</form>

</div>
