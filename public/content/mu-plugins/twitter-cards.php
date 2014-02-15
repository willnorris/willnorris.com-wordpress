<?php

function twitter_metadata($metadata) {
  $metadata['twitter:card'] = 'summary';
  $metadata['twitter:creator'] = '@willnorris';

  foreach (array('url', 'title', 'description', 'image') as $attr) {
    if ( array_key_exists("og:$attr", $metadata) && $metadata["og:$attr"] ) {
      $metadata["twitter:$attr"] = $metadata["og:$attr"];
    }
  }

  // only return first image for twitter
  if ( array_key_exists('twitter:image', $metadata) && is_array($metadata['twitter:image']) ) {
    $metadata['twitter:image'] = $metadata['twitter:image'][0];
  }

  return $metadata;
}
add_filter('opengraph_metadata', 'twitter_metadata');
