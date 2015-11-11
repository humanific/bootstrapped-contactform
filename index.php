<?php
/**
 * @package bootstrapped_contactform
 * @version 0.2
 */
/*
Plugin Name: Bootstrapped contactform
Plugin URI: https://github.com/humanific/bootstrapped-carousel
Description: Shortcode for displaying bootstrap contact form
Author: Francois Richir
Version: 0.2
Author URI: http://humanific.com
*/


function bootstrapped_contact_form($atts,$content=null){
 ob_start();
 global $post;?><a name="contactform"></a> <?php
 if(isset($atts['subject'])){
   $subjects = explode(';', $atts['subject']);
    if(count($subjects)==1) $subjects = $subjects[0];
   }else{
     $subjects = __('Information request sent from','bootstrapped-contactform')." ".get_permalink( $post->ID );
   }
  if(( isset($atts['layout']) ||isset($atts['class']) ) && ($atts['layout'] == 'horizontal' || $atts['class'] == 'form-horizontal') ){
    $class='form-horizontal';
  }
  $to = isset($atts['to']) ? $atts['to'] : get_bloginfo( 'admin_email' );
  global $contactformsuccess;
  if($contactformsuccess===false):?>
    <div class="alert alert-danger">
     <?php _e('Sorry, something went wrong.','bootstrapped-contactform');?>
    </div>
  <?php elseif($contactformsuccess===true):?>
    <div class="alert alert-success">
       <?php if($content){
          echo $content;
       }else{
          _e('Thanks, your message has been sent.','bootstrapped-contactform');
       }?>
      </div>
  <?php endif?>
<script>
  jQuery(document).ready(function($){
     $(".contactform").validate({
      errorClass: "text-danger small",
      highlight: function(element) {
          $(element).closest('.form-group').addClass('has-error');
      },
      unhighlight: function(element) {
          $(element).closest('.form-group').removeClass('has-error');
      },
      errorPlacement: function(error, element) {
          element.closest('div').append(error);
      },
      messages: {
        f_name: "<?php _e('This field is required','bootstrapped-contactform'); ?>",
        f_msg: "<?php _e('This field is required','bootstrapped-contactform'); ?>",
        email: "<?php _e('Please enter a valid email address','bootstrapped-contactform'); ?>"}
      });
  })
</script>

<form method="post" id="contactform"  class="<?php echo $class;?> contactform"  role="form"  enctype="multipart/form-data">
  <?php echo wp_nonce_field('contactform','contactsecurity'); ?>
  <input type="hidden" name="action" value="bootstrapped_contactform_submit" >
  <input type="hidden" name="contacttoken" value="<?php echo bootstrapped_contactform_encrypt($to);?>" >
  <?php if(is_array($subjects)):?>
    <div class="form-group">
    <label class="<?php if($class=='form-horizontal') { echo 'col-sm-3';}?> control-label" for="f_subject"><?php _e('Subject','bootstrapped-contactform'); ?></label>
    <div class="<?php if($class=='form-horizontal') { echo 'col-sm-9';}?>">
      <select name="f_subject" class="form-control ">
      <?php foreach($subjects as $k=>$s) :?>
        <option <?php if($_POST['subject']==$k) { echo 'selected' ;} ?> value="<?php echo $k; ?>"><?php echo $s; ?></option>
      <?php endforeach;?>
    </select>
    </div>
    </div>
  <?php else:?>
  <?php endif;?>
  <?php if ( is_user_logged_in() ){
    global $current_user;
    get_currentuserinfo();
  }?>
  <div class="form-group">
    <label class="<?php if($class=='form-horizontal') { echo 'col-sm-3';}?> control-label" for="full_name"><?php _e('Your name','bootstrapped-contactform'); ?>*</label>
    <div class="<?php if($class=='form-horizontal') { echo 'col-sm-9';}?>"><input type="text" value="<?php echo $current_user->display_name; ?>" name="full_name" class="form-control required " />
    </div>
  </div>
  <div class="form-group">
    <label class="<?php if($class=='form-horizontal') { echo 'col-sm-3';}?> control-label" for="email"><?php _e('Your email','bootstrapped-contactform'); ?>*</label>
    <div class="<?php if($class=='form-horizontal') { echo 'col-sm-9';}?>"><input type="text" value="<?php echo $current_user->user_email; ?>" name="email"  class="form-control required email " />
    </div>
  </div>

  <?php if(isset($atts['attachment'])) :?>
  <div class="form-group">
    <label class="<?php if($class=='form-horizontal') { echo 'col-sm-3';}?> control-label text-right" for="image"><?php echo $atts['attachment']; ?></label>
    <div class="<?php if($class=='form-horizontal') { echo 'col-sm-9';}?>">
    <input type="file" name="attachment" id="attachment">
    </div>
  </div>
  <?php endif; ?>


  <div class="form-group">
    <label class="<?php if($class=='form-horizontal') { echo 'col-sm-3';}?> control-label" for="message"><?php _e('Your message','bootstrapped-contactform'); ?>*</label>
    <div class="<?php if($class=='form-horizontal') { echo 'col-sm-9';}?>"><textarea name="message" class="form-control required " style="height:200px"></textarea>
    </div>
  </div>
  <div class="form-group">
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
function bootstrapped_contactform_submit(){
  if( $_POST['action'] == 'bootstrapped_contactform_submit' && wp_verify_nonce( $_POST['contactsecurity'] ,'contactform') ){
    $to_email = bootstrapped_contactform_decrypt($_POST['contacttoken']);
    if( is_email( $_POST['email'] ) && $_POST['full_name'] && $_POST['message']) {
      global $contactformsuccess;
      $contactformsuccess = false;
      $headers = 'From: '.$_POST['full_name'].' <'.$_POST['email'].'>' . "\r\n";
      $subject = $_POST['f_subject'];
      if(!$subject){
         $subject = __('Information request sent from','bootstrapped-contactform')." ".$_SERVER['HTTP_REFERER'];
      }
      $msg = $subject. "\r\nurl : ".$_SERVER['HTTP_REFERER']. "\r\nfrom : ".$_POST['full_name']." ".$_POST['email']. "\r\n\r\n".$_POST['message'];

        $attachment = $_FILES['attachment']['tmp_name'];
      }
      $contactform_contact = array(
        'post_content'=>$msg,
        'post_title'=> $subject,
        'post_type'=>'contactform_contact',
        'post_author'=> $user_id
      );
      $contactform_contact_id = wp_insert_post($contactform_contact);
      if($_FILES['attachment']){
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );
        $attachment_id = media_handle_upload( 'attachment', $contactform_contact_id );
        if ( is_wp_error( $attachment_id ) ) {

        } else {
          $attachment = get_attached_file( $attachment_id);
        }
      }
      $contactformsuccess = wp_mail( $to_email ,$subject , $msg, $headers, array($attachment)) ;
    }
  }



function bootstrapped_contactform_meta_box ($post){ ?>
<h2><?php echo $post->post_title;?></h2>
<?php echo nl2br($post->post_content);?>
<p><?php echo $post->post_date;?></p>
<?php echo $post->post_author;?>
<?php }


function bootstrapped_contactform_add_meta_boxes (){
  add_meta_box("contact_messge", __('Contact message','finicrowd'), "bootstrapped_contactform_meta_box", "contactform_contact", "normal", "high");
}

add_action( 'add_meta_boxes', 'bootstrapped_contactform_add_meta_boxes' );



add_action( 'init', 'bootstrapped_contactform_submit' );

function bootstrapped_contactform_encrypt($pure_string) {
  $key = get_option('bootstrapped_contactform_key');
  if(!$key) {
    $key = sha1(microtime(true).mt_rand(10000,90000));
    update_option( 'bootstrapped_contactform_key' , $key );
  }
  $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
  $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
  $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
  return urlencode($encrypted_string);
}


function bootstrapped_contactform_decrypt($encrypted_string) {
  $key = get_option('bootstrapped_contactform_key');
  $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
  $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
  $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, urldecode($encrypted_string), MCRYPT_MODE_ECB, $iv);
  return  rtrim($decrypted_string, "\0");
}

add_action('init', 'bootstrapped_contactform_create_post_type');

function bootstrapped_contactform_create_post_type() {
  register_post_type( 'contactform_contact',
    array(
      'labels' => array(
        'name' => __('Contacts','finicrowd'),
        'singular_name' =>__('Contact','finicrowd'),
        'add_new' => __('Create a new contact','finicrowd'),
      ),
      'public' => false,
      'show_in_nav_menus' => false,
      'show_ui' => false,
      'publicly_queryable' => false,
      'exclude_from_search' => true,
      'hierarchical' => false,
      'menu_position' => 14,
      'menu_icon'=> 'dashicons-email',
      'capability_type' => 'post',
      'query_var' => false,
      'supports' => false
    )
  );
}
