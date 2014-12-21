<?php
/**
 * @package bootstrapped_contactform
 * @version 0.1
 */
/*
Plugin Name: Bootstrapped contactform
Plugin URI: https://github.com/humanific/bootstrapped-carousel
Description: Shortcode for displaying bootstrap contact form
Author: Francois Richir
Version: 0.1
Author URI: http://humanific.com
*/

function bootstrapped_contact_form($atts,$content=null){
 global $post;
 ob_start();
 if(isset($atts['subject'])){
   $subjects = explode(';', $atts['subject']);
   if(count($subjects)==1) $subjects = $subjects[0];
 }else{
   $subjects = __('Information request sent from','bootstrapped-contactform')." ".get_permalink( $post->ID );
 }

if($atts['layout'] == 'horizontal' || $atts['class'] == 'form-horizontal' ){
  $class='form-horizontal';
}



$to = isset($atts['to']) ? $atts['to'] : get_bloginfo( 'admin_email' );





if( $_POST && is_email( $_POST['email'] ) && $_POST['f_name']&& $_POST['f_msg'] && wp_verify_nonce( $_POST['contactsecurity'], 'contactform' )) {
    $headers = 'From: '.$_POST['f_name'].' <'.$_POST['email'].'>' . "\r\n";
    $s = is_array($subject) ? $_POST['f_subject'] : $subject ;
    $msg = $s. "\r\nurl : ".$_SERVER['HTTP_REFERER']. "\r\nfrom : ".$_POST['f_name']." ".$_POST['email']. "\r\n\r\n".$_POST['f_msg'];
    
    if(wp_mail( $to,$s , $msg, $headers )){
      echo '<div class="alert alert-success">';
       if($content){
          echo $content;
       }else{
          _e('Thanks, your message has been sent.','bootstrapped-contactform');
       }
      echo '</div>';
      $time = current_time('mysql');
      $data = array(
          'comment_post_ID' => get_the_ID(),
          'comment_author' => $_POST['f_name'],
          'comment_author_email' => $_POST['email'],
          'comment_content' => '<b>'.$s.'</b>'.$_POST['f_msg'],
          'comment_date' => $time
      );
      wp_insert_comment($data);
    }else{
        echo '<div class="alert alert-danger">';
         _e('Sorry, something went wrong.','bootstrapped-contactform');
        echo '</div>';
    }
  }

?>

<script>

  jQuery(document).ready(function($){
     $("#contactform").validate({
      errorClass: "text-danger",
      messages: {        
        f_name: "<?php _e('This field is mandatory','bootstrapped-contactform'); ?>", 
        f_msg: "<?php _e('This field is mandatory','bootstrapped-contactform'); ?>", 
        email: "<?php _e('Please check this email address','bootstrapped-contactform'); ?>"}
      });
  })
</script>
<form method="post" id="contactform"  class="<?php echo $class;?>"  role="form">
      <?php echo wp_nonce_field('contactform','contactsecurity'); ?>
      <?php if(is_array($subjects)):?>
      <div class="form-group">
      <label class="<?php if($class=='form-horizontal') { echo 'col-sm-3';}?> control-label" for="f_subject"><?php _e('Subject','bootstrapped-contactform'); ?></label>
      <div class="<?php if($class=='form-horizontal') { echo 'col-sm-9';}?>"><select name="f_subject" class="form-control ">
      
      <?php foreach($subjects as $k=>$s) :?>
      <option <?php if($_REQUEST['subject']==$k) { echo 'selected' ;} ?> value="<?php echo $k; ?>"><?php echo $s; ?></option>
      <?php endforeach;?>

      </select>
      </div></div>
      <?php endif;?>
      <?php if ( is_user_logged_in() ){ 
          global $current_user;
          get_currentuserinfo();
        }
      ?>
          <div class="form-group">
            <label class="<?php if($class=='form-horizontal') { echo 'col-sm-3';}?> control-label" for="f_name"><?php _e('Your name','bootstrapped-contactform'); ?>*</label>
            <div class="<?php if($class=='form-horizontal') { echo 'col-sm-9';}?>"><input type="text" value="<?php echo $current_user->display_name; ?>" name="f_name" class="form-control required " />
            </div>
          </div>
          <div class="form-group">
            <label class="<?php if($class=='form-horizontal') { echo 'col-sm-3';}?> control-label" for="email"><?php _e('Your email','bootstrapped-contactform'); ?>*</label>
            <div class="<?php if($class=='form-horizontal') { echo 'col-sm-9';}?>"><input type="text" value="<?php echo $current_user->user_email; ?>" name="email"  class="form-control required email " />
            </div>
          </div>
          <div class="form-group">
            <label class="<?php if($class=='form-horizontal') { echo 'col-sm-3';}?> control-label" for="f_msg"><?php _e('Your message','bootstrapped-contactform'); ?>*</label>
            <div class="<?php if($class=='form-horizontal') { echo 'col-sm-9';}?>"><textarea name="f_msg" class="form-control required " style="height:200px"></textarea>
            </div>
          </div>
      <div class="form-group">
        <input type="hidden" value="<?php echo $_GET['origin']?>" name="origin" />
        <div class="<?php if($class=='form-horizontal') { echo 'col-sm-offset-3 col-sm-9';}?>">
          <input type="submit" class="btn btn-default btn-lg" value="<?php _e('Send','bootstrapped-contactform'); ?>" />
        </div>
      </div>
    </form>
    <?php

  return ob_get_clean();
}

add_shortcode( 'contactform', 'bootstrapped_contact_form' );

function bootstrapped_contactform_scripts() {
  wp_enqueue_script( 'jquery.validate', plugins_url( 'jquery.validate.min.js' , __FILE__ ), array( 'jquery') );
}


add_action( 'wp_enqueue_scripts', 'bootstrapped_contactform_scripts' );




function bootstrapped_contactform_load_textdomain() {
  load_plugin_textdomain('bootstrapped-contactform', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/'); 
}
add_action('init', 'bootstrapped_contactform_load_textdomain');


?>