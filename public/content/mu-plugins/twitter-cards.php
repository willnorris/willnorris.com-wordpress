<?php

function twitter_metadata($metadata) {
  $metadata['twitter:card'] = 'summary';
  $metadata['twitter:creator'] = '@willnorris';

  foreach (array('url', 'title', 'description', 'image') as $attr) {
    if (array_key_exists("og:$attr", $metadata)) {
      $metadata["twitter:$attr"] = $metadata["og:$attr"];
    }
  }

  return $metadata;
}
add_filter('opengraph_metadata', 'twitter_metadata');
