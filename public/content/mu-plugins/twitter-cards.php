<?php

function twitter_metadata($metadata) {
  foreach (array('url', 'title', 'description', 'image') as $attr) {
    if (array_key_exists("og:$attr", $metadata)) {
      $metadata["twitter:$attr"] = $metadata["og:$attr"];
    }
  }
  $metadata['twitter:creator'] = '@willnorris';

  return $metadata;
}
add_filter('opengraph_metadata', 'twitter_metadata');
