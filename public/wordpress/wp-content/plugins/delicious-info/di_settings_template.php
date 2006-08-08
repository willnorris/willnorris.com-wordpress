<div class="wrap">

<?php di_load_settings(); ?>

<h2>Del.icio.us Info Settings</h2>

<a name="form"></a>
<form name="di_settings" id="di_settings" method="post" action="<?php echo di_form_action(); ?>">

  <table class="editform" width="100%" cellspacing="2" cellpadding="5">
    <tr>
      <th scope="row">Del.icio.us username:</th>
      <td><input type="text" name="delicious_username" value="<?php echo $_REQUEST['delicious_username']; ?>" style="width: 95%" /></td>
    </tr>
    <tr>
      <th scope="row">Del.icio.us password:</th>
      <td><input type="password" name="delicious_password" value="<?php echo $_REQUEST['delicious_password']; ?>" style="width: 95%" /></td>
    </tr>
    <tr>
      <th scope="row">Number of recent links:</th>
      <td><input type="text" name="recent" value="<?php echo $_REQUEST['recent']; ?>" style="width: 95%" /></td>
    </tr>
    <tr>
      <th scope="row">Cache timeout (in seconds):</th>
      <td><input type="text" name="timeout" value="<?php echo $_REQUEST['timeout']; ?>" style="width: 95%" /></td>
    </tr>
  </table>

  <p class="submit">
    <input type="hidden" name="di_action" value="set" />
    <input type="submit" name="submit" value="Save Settings &raquo;" />
  </p>

</form>

</div>

<div class="wrap">

<h2>Del.icio.us API URLs</h2>

<p> These URLs may change in future versions of the del.icio.us API. Do not
modify these URLs unless you know that the <a href="http://del.icio.us/help/api/">del.icio.us API</a> has changed. </p>

<form name="di_url_settings" id="di_url_settings" method="post" action="<?php echo di_form_action(); ?>">

  <table class="editform" width="100%" cellspacing="2" cellpadding="5">
    <tr>
      <th scope="row">Del.icio.us tag API URL:</th>
      <td><input type="text" name="delicious_tag_url" value="<?php echo $_REQUEST['delicious_tag_url']; ?>" style="width: 95%" /></td>
    </tr>
    <tr>
      <th scope="row">Del.icio.us recent post API URL:</th>
      <td><input type="text" name="delicious_post_url" value="<?php echo $_REQUEST['delicious_post_url']; ?>" style="width: 95%" /></td>
    </tr>
  </table>

  <p class="submit">
    <input type="hidden" name="di_action" value="setapi" />
    <input type="submit" name="submit" value="Save URLs &raquo;" />
  </p>

</form>

</div>
