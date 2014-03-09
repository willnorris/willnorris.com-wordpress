<?php

// add metadata for twitter cards.
add_filter( 'opengraph_metadata', function( $metadata ) {
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
});
