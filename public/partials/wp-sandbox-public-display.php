<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/public/partials
 */
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<!DOCTYPE html>
<html>
  <head>
    <link type="text/css" rel="stylesheet" href="<?php echo plugins_url() .'/wp-sandbox/public/css/wp-sandbox-public.css'; ?>"/>
    <style>
      div#wp-sandbox-coming-soon-template{
        /* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#5cccf0+0,3884e8+100 */
       background: <?php echo $settings[0]['background_color_1']; ?>; /* Old browsers */
       background: -moz-linear-gradient(45deg, <?php echo $settings[0]['background_color_1']; ?> 0%, <?php echo $settings[0]['background_color_2']; ?> 100%); /* FF3.6-15 */
       background: -webkit-linear-gradient(45deg, <?php echo $settings[0]['background_color_1']; ?> 0%,<?php echo $settings[0]['background_color_2']; ?> 100%); /* Chrome10-25,Safari5.1-6 */
       background: linear-gradient(45deg, <?php echo $settings[0]['background_color_1']; ?> 0%,<?php echo $settings[0]['background_color_2']; ?> 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */
       filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='<?php echo $settings[0]['background_color_1']; ?>', endColorstr='<?php echo $settings[0]['background_color_2']; ?>',GradientType=1 ); /* IE6-9 fallback on horizontal gradient */
      }
    </style>
  </head>
  <body>
    <div id="wp-sandbox-coming-soon-template">
      <div id="wp-sandbox-coming-soon-template-container">
        <?php if( $settings[0]['logo'] != '' ){ ?>
          <img id="wp-sandbox-coming-soon-template-logo" src="<?php echo $settings[0]['logo']; ?>"/>
        <?php } ?>

        <h2 id="wp-sandbox-coming-soon-template-header"><?php echo trim( $settings[0]['main_title'] ) != '' ? $settings[0]['main_title'] : 'This awesome site is coming soon!'; ?></h2>
        <h3 id="wp-sandbox-coming-soon-template-sub-header"><?php echo trim( $settings[0]['sub_title'] ) != '' ? $settings[0]['sub_title'] : 'Please excuse the dust, we will be launching soon.'; ?></h3>

        <div id="wp-sandbox-social-icon-container">
          <?php if( trim( $settings[0]['twitter_url'] ) != '' ){ ?>
            <a href="<?php echo $settings[0]['twitter_url']; ?>" target="_blank">
              <img id="twitter" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/twitter.svg"/>
            </a>
          <?php } ?>
          <?php if( trim( $settings[0]['instagram_url'] ) != '' ){ ?>
            <a href="<?php echo $settings[0]['instagram_url']; ?>" target="_blank">
              <img id="instagram" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/instagram.svg"/>
            </a>
          <?php } ?>
          <?php if( trim( $settings[0]['google_plus_url'] ) != '' ){ ?>
            <a href="<?php echo $settings[0]['google_plus_url']; ?>" target="_blank">
              <img id="google-plus" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/google-plus.svg"/>
            </a>
          <?php } ?>
          <?php if( trim( $settings[0]['dribbble_url'] ) != '' ){ ?>
            <a href="<?php echo $settings[0]['dribbble_url']; ?>" target="_blank">
              <img id="dribbble" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/dribbble.svg"/>
            </a>
          <?php } ?>
          <?php if( trim( $settings[0]['vimeo_url'] ) != '' ){ ?>
            <a href="<?php echo $settings[0]['vimeo_url']; ?>" target="_blank">
              <img id="vimeo" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/vimeo.svg"/>
            </a>
          <?php } ?>
          <?php if( trim( $settings[0]['youtube_url'] ) != '' ){ ?>
            <a href="<?php echo $settings[0]['youtube_url']; ?>" target="_blank">
              <img id="youtube" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/youtube.svg"/>
            </a>
          <?php } ?>
          <?php if( trim( $settings[0]['facebook_url'] ) != '' ){ ?>
            <a href="<?php echo $settings[0]['facebook_url']; ?>" target="_blank">
              <img id="facebook" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/facebook.svg"/>
            </a>
          <?php } ?>
        </div>

        <?php if( $settings[0]['show_login_link'] == '1' ){ ?>
          <span id="wp-sandbox-coming-soon-template-login">Do you have exclusive access? <a href="/wp-admin">Login here.</a></span>
        <?php } ?>
      </div>
    </div>
  </body>
</html>
