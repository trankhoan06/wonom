<?php

add_action( 'wp_ajax_policysubmit', 'ajax_policysubmit' );
//for none logged-in users
add_action('wp_ajax_nopriv_policysubmit', 'ajax_policysubmit');

function ajax_policysubmit(){
  $result= json_encode(['status' => 0]);
  $mobile = isset($_POST['mobile'])?$_POST['mobile']:"";

  if(!empty($mobile)){
    $args = array(
      'post_type' => 'policysubmit',
      's'         => $mobile
    );
    $the_query = new WP_Query( $args );
    if ( $the_query->have_posts() ) {
      $request_id='';
      while ( $the_query->have_posts() ) {
          $the_query->the_post();
          $request_id = tr_posts_field('request_id', get_the_ID());
      }
      $result= json_encode(['status' => 1,'request_id'=>$request_id]);
    }
    else{
      //Save DB
      $data = array(
        'post_title'    => $mobile,
        'post_status'   => 'draft',
        'post_type'   => 'policysubmit',
      );
      // Insert the post into the database
      $post_id = wp_insert_post( $data );
      $request_id= uniqid();
      add_post_meta( $post_id, 'request_id', $request_id );
      $result=  json_encode(['status' => 1,'request_id'=>$request_id]);
    }


  }
  echo $result;
  exit();
}

add_action( 'wp_ajax_policyupdate', 'ajax_policyupdate' );
//for none logged-in users
add_action('wp_ajax_nopriv_policyupdate', 'ajax_policyupdate');

function ajax_policyupdate(){
  $result= json_encode(['status' => 0]);
  $request_id = isset($_POST['request_id'])?$_POST['request_id']:"";

  if(!empty($request_id)){
    $args = array(
      'post_type' => 'policysubmit',
      'posts_per_page'   => 1,
      'post_status' => 'draft',
      'meta_query' => array(
           array(
               'key' => 'request_id',
               'value' => $request_id,
               'compare' => '=',
           )
       )
    );
    $posts = get_posts( $args );
    $result= json_encode(['status' => 0,'posts'=>$posts]);
    if(!empty($posts )){
      $update_post = array(
          'ID' => $posts[0]->ID,
          'post_status' => 'publish'
      );
      wp_update_post($update_post);
      $result=  json_encode(['status' => 1]);
    }
  }
  echo $result;
  exit();
}

add_action( 'wp_ajax_newsletter', 'ajax_newsletter' );
//for none logged-in users
add_action('wp_ajax_nopriv_newsletter', 'ajax_newsletter');

function ajax_newsletter(){
  $result= json_encode(['status' => 0]);
  $newsletter_email = isset($_POST['newsletter_email'])?$_POST['newsletter_email']:"";
 
  if(!empty($newsletter_email)){
    $args = array(
      'post_type' => 'newsletter',
      's'         => $newsletter_email
    );
    $the_query = new WP_Query( $args );
    if ( $the_query->have_posts() ) {
      $result= json_encode(['status' => 2]);
      echo $result; die();
    }

    //Save DB
    $newsletter_data = array(
      'post_title'    => $newsletter_email,
      'post_status'   => 'publish',
      'post_type'   => 'newsletter',
    );
    // Insert the post into the database
    $post_id = wp_insert_post( $newsletter_data );
    $result=  json_encode(['status' => 1]);
  }
  echo $result;
  exit();
}

add_action( 'wp_ajax_contactform', 'ajax_contactform' );
//for none logged-in users
add_action('wp_ajax_nopriv_contactform', 'ajax_contactform');

function ajax_contactform(){
  $result= json_encode(['status' => 0]);
  $contact_name = isset($_POST['fullname'])?$_POST['fullname']:"";
  $contact_mobile = isset($_POST['mobile'])?$_POST['mobile']:"";
  $contact_email = isset($_POST['email'])?$_POST['email']:"";
  $contact_address = isset($_POST['address'])?$_POST['address']:"";
  $contact_interest = isset($_POST['interest'])?$_POST['interest']:"";
  
  $content = isset($_POST['content'])?$_POST['content']:"";

  /*$args = array(
    'post_type' => 'register',
    'meta_query' => array(
      'relation' => 'OR',
      array(
        'key' => 'mobile',
        'value' => $contact_mobile
      ),
      array(
        'key' => 'email',
        'value' => $contact_email
      ),
    )
  );
   
  $the_query = new WP_Query( $args );
  if ( $the_query->have_posts() ) {
    $result= json_encode(['status' => 2]);
    echo $result; die();
  }*/

  $body  = "Xin chào,<br>";
  $body .= "<p>Bạn nhận được thông tin đăng ký của khách hàng.</p>";
  $body .="<strong>Há» vÃ  tÃªn:</strong> ".$contact_name."<br>";
  $body .= "<strong>Điện thoại di động:</strong> " . $contact_mobile . "<br>";
  $body .="<strong>Email:</strong> ".$contact_email."<br>";
  //$body .="<strong>Äá»‹a chá»‰:</strong> ".$contact_address."<br>";
  $body .= "<strong>Sản phẩm quan tâm:</strong> " . $contact_interest . "<br>";
  $body .= "<strong>Nội dung:</strong> " . $content . "<br>";

  $headers = array('Content-Type: text/html; charset=UTF-8');

   //Save DB
  $register_data = array(
    'post_title'    => wp_strip_all_tags( $contact_name ),
    'post_status'   => 'publish',
    'post_type'   => 'register',
  );
  // Insert the post into the database
  $post_id = wp_insert_post( $register_data );
  add_post_meta( $post_id, 'mobile', $contact_mobile );
  add_post_meta( $post_id, 'email', $contact_email );
  //add_post_meta( $post_id, 'address', $contact_address );
  add_post_meta( $post_id, 'interest', $contact_interest );
  add_post_meta( $post_id, 'content', $content );

  $device ="Desktop";
  if(isMobileDevice()){
      $device ="Mobile";
  }
  $user_agent =  $_SERVER['HTTP_USER_AGENT'];
  $source = isset($_POST['source'])?urldecode($_POST['source']):""; 

  add_post_meta( $post_id, 'device', $device );
  add_post_meta( $post_id, 'source', $source );
  add_post_meta( $post_id, 'user_agent', $user_agent );


  $check = wp_mail( tr_options_field('tr_theme_options.receive_email'), "Thông tin đăng ký từ website TBS Group", $body , $headers );

  //Mail To Customer

  if($check || 1==1){
    $result=  json_encode(['status' => 1]);
  }

  echo $result;
  die();
}


add_action( 'wp_ajax_nopriv_pagination_data', 'ajax_pagination_data' );
add_action( 'wp_ajax_pagination_data', 'ajax_pagination_data' );
function ajax_pagination_data() {
    $paged = !empty($_POST['page'])?$_POST['page']:1;
    $query_vars = [];
    $query_vars['post_type'] = "post";
    $query_vars['post_status'] = "publish"; 
    $query_vars['paged'] = $paged;
    $query_vars['cat'] = !empty($_POST['cat']) ? $_POST['cat'] : '';
    $query_vars['posts_per_page'] = !empty($_POST['posts_per_page'])?$_POST['posts_per_page']:4;

    $posts = new WP_Query( $query_vars );
    $GLOBALS['wp_query'] = $posts;
 
 
    if( $posts->have_posts() ) { 
        echo '<div class="items post-list row gutter-0 animated fadeInUpShort delay-500">';
        while ( $posts->have_posts() ) { 
            $posts->the_post();
            echo '<div class="col-12 col-sm-6 col-lg-3">';
            get_template_part( "partials/content", "news");
            echo '</div>';
        }
        echo '</div>';
        if (function_exists("custom_ajax_pagination")) {
          custom_ajax_pagination($posts->max_num_pages,"",$paged);
        }
    } 
 
    die();      
 
}


add_action( 'wp_ajax_nopriv_checkpopup', 'ajax_checkpopup' );
add_action( 'wp_ajax_checkpopup', 'ajax_checkpopup' );
function ajax_checkpopup() {
    $time = time();
    if(isset($_SESSION['visit_time'])) {
        if(($time - $_SESSION['visit_time']) >= 89){
          $_SESSION['visit_time'] = strtotime('tomorrow');
          echo  json_encode(['status' => 1]); die();
        }
    } else {
        $_SESSION['visit_time'] = $time;
    } 
    echo  json_encode(['status' => 0]); die();  
 
}


add_action( 'wp_ajax_get_news', 'ajax_get_news' );
//for none logged-in users
add_action('wp_ajax_nopriv_get_news', 'ajax_get_news');

function ajax_get_news(){

    $id = isset($_POST['id'])?$_POST['id']:"";
    $post= get_post($id);

    if(!empty($post)){
      echo json_encode(['status' => 1,
        'post'=> [
          "id"=>$post->ID,
          //"href"=>get_permalink($post->ID),
          "title"=>$post->post_title,
          "date"=>date('d/m/Y', strtotime($post->post_date)),
          "content"=> apply_filters('the_content', $post->post_content)
        ]
      ]); 
    }
    else{
      echo json_encode(['status' => 0]);
    }
    die();
}

add_action('wp_ajax_updatepost', 'ajax_updatepost');

function ajax_updatepost(){
    $post_id = isset($_POST['id'])?$_POST['id']:"";
    $meta_key = isset($_POST['field'])?$_POST['field']:"";
    $meta_value = isset($_POST['value'])?$_POST['value']:"";
    try {
      update_post_meta( $post_id, $meta_key, $meta_value );
      echo json_encode(['status' => 1]);
    } catch (\Exception $e) {
      echo json_encode(['status' => 0]);
    }


    //Don't forget to always exit in the ajax function.
    exit();

}


add_action( 'wp_ajax_get_prod', 'ajax_get_prod' );
//for none logged-in users
add_action('wp_ajax_nopriv_get_prod', 'ajax_get_prod');

function ajax_get_prod(){

    $id = isset($_POST['id'])?$_POST['id']:"";
    $post= get_post($id);

    if(!empty($post)){
      $thap = isset($_POST['thap'])?$_POST['thap']:"";
      $tang = isset($_POST['tang'])?$_POST['tang']:"";

      $content = nmc_get_template_part( 'partials/modal-product-detail', [ 'return' => true,'post'=>$post,'thap'=>$thap,'tang'=>$tang] );

      echo json_encode(['status' => 1,'content'=> $content]);
    }
    else{
      echo json_encode(['status' => 0]);
    }
    die();
}

function wonom_email_template($section_title, $content) {
    $logo_url = get_theme_file_uri('/asset/img/logo.webp');

    $html = '
    <div style="background-color: #f5f5f5; padding: 40px 10px; font-family: Arial, sans-serif; color: #333333; line-height: 1.6;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-collapse: collapse; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <tr>
                <td style="background-color: #111111; padding: 20px 30px; text-align: left; vertical-align: middle;">
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td style="vertical-align: middle;">
                                <img src="' . $logo_url . '" alt="Wonom Logo" height="24" style="height: 24px; vertical-align: middle; border: none; display: inline-block;" />
                                <span style="color: #666666; margin: 0 10px; font-size: 18px;">|</span>
                                <span style="color: #ffffff; font-size: 13px; font-weight: normal; text-transform: uppercase; letter-spacing: 1px;">'.$section_title.'</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding: 0; background-color: #ffffff;">
                    <div style="padding: 40px 30px;">
                        '.$content.'
                    </div>
                </td>
            </tr>
        </table>
    </div>
    ';
    return $html;
}

function wonom_verify_recaptcha($token) {
    if (empty($token)) return false;
    $secret = tr_options_field('tr_theme_options.recaptcha_secret_key');
    $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
        'body' => [
            'secret' => $secret,
            'response' => $token
        ]
    ]);
    if (is_wp_error($response)) return false;
    $body = wp_remote_retrieve_body($response);
    $result = json_decode($body);
    return (!empty($result->success) && (!isset($result->score) || $result->score >= 0.5)) ? true : false;
}

add_action('wp_ajax_submit_career_application', 'ajax_submit_career_application');
add_action('wp_ajax_nopriv_submit_career_application', 'ajax_submit_career_application');

function ajax_submit_career_application() {
    $result = ['status' => 0];
    
    if (!wonom_check_rate_limit_ip()) {
        $result['message'] = 'Bạn gửi quá nhiều yêu cầu. Vui lòng đợi 1 phút rồi thử lại.';
        echo json_encode($result);
        die();
    }
    
    $recaptcha_token = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    if (!wonom_verify_recaptcha($recaptcha_token)) {
        $result['message'] = 'Xác thực bảo mật thất bại. Vui lòng thử lại.';
        echo json_encode($result);
        die();
    }
    
    $name = isset($_POST['career_name']) ? sanitize_text_field($_POST['career_name']) : '';
    $email = isset($_POST['career_email']) ? sanitize_email($_POST['career_email']) : '';
    $phone = isset($_POST['career_phone']) ? sanitize_text_field($_POST['career_phone']) : '';
    $portfolio = isset($_POST['career_portfolio']) ? sanitize_text_field($_POST['career_portfolio']) : '';
    $intro = isset($_POST['career_intro']) ? sanitize_textarea_field($_POST['career_intro']) : '';
    $job_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
    
    $job_title = $job_id ? get_the_title($job_id) : 'Vị trí Ứng tuyển';

    $to_email = tr_options_field('tr_theme_options.receive_email'); 
    if (empty($to_email)) {
        $to_email = get_option('admin_email');
    }

    $subject = "Ứng tuyển mới: " . $job_title . " - " . $name;
    
    $content = '<h2 style="margin-top:0; font-size:18px; color:#111; margin-bottom:20px;">Ứng viên ' . $name . ' vừa ứng tuyển</h2>';
    $content .= '<table width="100%" cellpadding="8" cellspacing="0" border="0" style="font-size: 14px;">';
    $content .= '<tr><td width="35%" style="font-weight:bold; color:#555;">Họ và tên:</td><td>' . $name . '</td></tr>';
    $content .= '<tr><td style="font-weight:bold; color:#555;">Vị trí ứng tuyển:</td><td>' . $job_title . '</td></tr>';
    $content .= '<tr><td style="font-weight:bold; color:#555;">Email:</td><td>' . $email . '</td></tr>';
    $content .= '<tr><td style="font-weight:bold; color:#555;">Số điện thoại:</td><td>' . $phone . '</td></tr>';
    $content .= '<tr><td style="font-weight:bold; color:#555;">Xem CV:</td><td>File đính kèm</td></tr>';
    if(!empty($portfolio)) $content .= '<tr><td style="font-weight:bold; color:#555;">Link Portfolio:</td><td><a href="'.$portfolio.'" style="color:#EB4250;">'.$portfolio.'</a></td></tr>';
    if(!empty($intro)) $content .= '<tr><td style="font-weight:bold; color:#555; vertical-align:top;">Giới thiệu:</td><td>' . nl2br($intro) . '</td></tr>';
    $content .= '</table>';
    
    $body = wonom_email_template('TUYỂN DỤNG', $content);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    // Handle File Upload
    $attachments = array();
    if (!empty($_FILES['career_cv']['name'])) {
        $uploaded_file = $_FILES['career_cv'];
        
        $allowed_exts = ['pdf', 'doc', 'docx'];
        $file_info = pathinfo($uploaded_file['name']);
        $extension = isset($file_info['extension']) ? strtolower($file_info['extension']) : '';
        
        if (in_array($extension, $allowed_exts) && $uploaded_file['error'] == 0 && $uploaded_file['size'] <= 5242880) { // 5MB limit
            $upload_dir = wp_upload_dir();
            $file_name = sanitize_file_name($uploaded_file['name']);
            $target_file = $upload_dir['path'] . '/' . wp_unique_filename($upload_dir['path'], $file_name);
            
            if (move_uploaded_file($uploaded_file['tmp_name'], $target_file)) {
                $attachments[] = $target_file;
            }
        }
    }
    
    $check = wp_mail($to_email, $subject, $body, $headers, $attachments);

    if ($check) {
        $result['status'] = 1;
        
        // Gửi mail cảm ơn cho ứng viên
        $thank_you_subject = 'Cảm ơn bạn đã ứng tuyển vị trí ' . $job_title;
        $thank_you_content = '<h2 style="margin-top:0; font-size:18px; color:#111; margin-bottom:20px;">Xin chào ứng viên, ' . $name . '</h2>';
        $thank_you_content .= '<p style="margin-bottom:15px;">Cảm ơn bạn đã quan tâm và gửi hồ sơ ứng tuyển vị trí <strong>' . $job_title . '</strong>.</p>';
        $thank_you_content .= '<p style="margin-bottom:15px;">Chúng tôi đã nhận được thông tin và CV của bạn. Bộ phận Tuyển dụng sẽ xem xét hồ sơ và liên hệ với bạn trong thời gian sớm nhất nếu hồ sơ phù hợp.</p>';
        $thank_you_content .= '<p style="margin-bottom:25px;">Chúc bạn một ngày tốt lành!</p>';
        $thank_you_content .= '<p style="margin-bottom:0; color:#555;">Trân trọng,<br><strong style="color:#111;">Phòng Nhân sự Wonom</strong></p>';
        
        $thank_you_body = wonom_email_template('TUYỂN DỤNG', $thank_you_content);
        wp_mail($email, $thank_you_subject, $thank_you_body, $headers);
    }
    
    if (!empty($attachments)) {
        foreach ($attachments as $att) {
            @unlink($att);
        }
    }
    
    echo json_encode($result);
    die();
}

add_action('wp_ajax_nopriv_submit_contact_form', 'ajax_submit_contact_form');
add_action('wp_ajax_submit_contact_form', 'ajax_submit_contact_form');

function ajax_submit_contact_form() {
    $result = ['status' => 0];
    
    if (!wonom_check_rate_limit_ip()) {
        $result['message'] = 'Bạn gửi quá nhiều yêu cầu. Vui lòng đợi 1 phút rồi thử lại.';
        echo json_encode($result);
        die();
    }
    
    $recaptcha_token = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    if (!wonom_verify_recaptcha($recaptcha_token)) {
        $result['message'] = 'Xác thực bảo mật thất bại. Vui lòng thử lại.';
        echo json_encode($result);
        die();
    }
    
    $name = isset($_POST['contact_name']) ? sanitize_text_field($_POST['contact_name']) : '';
    $email = isset($_POST['contact_email']) ? sanitize_email($_POST['contact_email']) : '';
    $phone = isset($_POST['contact_phone']) ? sanitize_text_field($_POST['contact_phone']) : '';
    $company = isset($_POST['contact_company']) ? sanitize_text_field($_POST['contact_company']) : '';
    $advice = isset($_POST['contact_advice']) ? sanitize_textarea_field($_POST['contact_advice']) : '';
    
    if (empty($name) || empty($email) || empty($phone)) {
        echo json_encode($result);
        die();
    }
    
    $to_email = tr_options_field('tr_theme_options.receive_email') ?: get_option('admin_email');
    $site_name = get_bloginfo('name');
    
    $subject = "[$site_name] Liên hệ mới từ $name";
    
    $content = '<h2 style="margin-top:0; font-size:18px; color:#111; margin-bottom:20px;">Có người vừa liên hệ qua trang Contact</h2>';
    $content .= '<table width="100%" cellpadding="8" cellspacing="0" border="0" style="font-size: 14px;">';
    $content .= '<tr><td width="35%" style="font-weight:bold; color:#555;">Họ và tên:</td><td>' . $name . '</td></tr>';
    $content .= '<tr><td style="font-weight:bold; color:#555;">Email:</td><td>' . $email . '</td></tr>';
    $content .= '<tr><td style="font-weight:bold; color:#555;">Số điện thoại:</td><td>' . $phone . '</td></tr>';
    if ($company) $content .= '<tr><td style="font-weight:bold; color:#555;">Công ty:</td><td>' . $company . '</td></tr>';
    if ($advice) $content .= '<tr><td style="font-weight:bold; color:#555; vertical-align:top;">Nội dung tư vấn:</td><td>' . nl2br($advice) . '</td></tr>';
    $content .= '</table>';
    
    $body = wonom_email_template('LIÊN HỆ', $content);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    // Save to DB
    $register_data = array(
        'post_title'    => wp_strip_all_tags( $name ),
        'post_status'   => 'publish',
        'post_type'     => 'register',
    );
    $post_id = wp_insert_post( $register_data );
    
    add_post_meta( $post_id, 'mobile', $phone );
    add_post_meta( $post_id, 'email', $email );
    
    $content_save = '';
    if (!empty($company)) {
        $content_save .= "Công ty: " . $company . "\n";
    }
    if (!empty($advice)) {
        $content_save .= "Tư vấn: " . $advice;
    }
    add_post_meta( $post_id, 'content', $content_save );
    
    $device = "Desktop";
    if(function_exists('isMobileDevice') && isMobileDevice()) {
        $device = "Mobile";
    } elseif(wp_is_mobile()) {
        $device = "Mobile";
    }
    
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $source = isset($_POST['source']) ? urldecode($_POST['source']) : '';
    
    add_post_meta( $post_id, 'device', $device );
    add_post_meta( $post_id, 'source', $source );
    add_post_meta( $post_id, 'user_agent', $user_agent );

    // Send email to system
    $check = wp_mail($to_email, $subject, $body, $headers);

    if ($check) {
        $result['status'] = 1;
        
        // Send thank you email to customer
        $thank_you_subject = "Cảm ơn bạn đã liên hệ " . $site_name;
        $thank_you_content = '<h2 style="margin-top:0; font-size:18px; color:#111; margin-bottom:20px;">Xin chào ' . $name . ',</h2>';
        $thank_you_content .= '<p style="margin-bottom:15px;">Cảm ơn bạn đã quan tâm và liên hệ với <strong>' . $site_name . '</strong>.</p>';
        $thank_you_content .= '<p style="margin-bottom:15px;">Chúng tôi đã nhận được thông tin yêu cầu tư vấn của bạn. Đội ngũ chuyên viên của chúng tôi sẽ xem xét và liên hệ lại với bạn trong thời gian sớm nhất.</p>';
        $thank_you_content .= '<p style="margin-bottom:25px;">Chúc bạn một ngày tốt lành!</p>';
        $thank_you_content .= '<p style="margin-bottom:0; color:#555;">Trân trọng,<br><strong style="color:#111;">Wonom</strong></p>';
        
        $thank_you_body = wonom_email_template('LIÊN HỆ', $thank_you_content);
        wp_mail($email, $thank_you_subject, $thank_you_body, $headers);
    }
    
    echo json_encode($result);
    die();
}

add_action('wp_ajax_nopriv_submit_footer_form', 'ajax_submit_footer_form');
add_action('wp_ajax_submit_footer_form', 'ajax_submit_footer_form');

function ajax_submit_footer_form() {
    $result = ['status' => 0];
    
    if (!wonom_check_rate_limit_ip()) {
        $result['message'] = 'Bạn gửi quá nhiều yêu cầu. Vui lòng đợi 1 phút rồi thử lại.';
        echo json_encode($result);
        die();
    }
    
    $recaptcha_token = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    if (!wonom_verify_recaptcha($recaptcha_token)) {
        $result['message'] = 'Xác thực bảo mật thất bại. Vui lòng thử lại.';
        echo json_encode($result);
        die();
    }
    
    $name = isset($_POST['footer_name']) ? sanitize_text_field($_POST['footer_name']) : '';
    $phone = isset($_POST['footer_phone']) ? sanitize_text_field($_POST['footer_phone']) : '';
    $message = isset($_POST['footer_message']) ? sanitize_textarea_field($_POST['footer_message']) : '';
    
    if (empty($name) || empty($phone)) {
        echo json_encode($result);
        die();
    }
    
    $to_email = tr_options_field('tr_theme_options.receive_email') ?: get_option('admin_email');
    $site_name = get_bloginfo('name');
    
    $subject = "[$site_name] Liên hệ từ Footer - $name";
    
    $content = '<h2 style="margin-top:0; font-size:18px; color:#111; margin-bottom:20px;">Có người vừa liên hệ qua form ở Footer</h2>';
    $content .= '<table width="100%" cellpadding="8" cellspacing="0" border="0" style="font-size: 14px;">';
    $content .= '<tr><td width="35%" style="font-weight:bold; color:#555;">Họ và tên:</td><td>' . $name . '</td></tr>';
    $content .= '<tr><td style="font-weight:bold; color:#555;">Số điện thoại:</td><td>' . $phone . '</td></tr>';
    if ($message) $content .= '<tr><td style="font-weight:bold; color:#555; vertical-align:top;">Lời nhắn:</td><td>' . nl2br($message) . '</td></tr>';
    $content .= '</table>';
    
    $body = wonom_email_template('LIÊN HỆ', $content);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    // Save to DB
    $register_data = array(
        'post_title'    => wp_strip_all_tags( $name ),
        'post_status'   => 'publish',
        'post_type'     => 'register',
    );
    $post_id = wp_insert_post( $register_data );
    
    add_post_meta( $post_id, 'mobile', $phone );
    add_post_meta( $post_id, 'content', $message );
    
    $device = "Desktop";
    if(function_exists('isMobileDevice') && isMobileDevice()) {
        $device = "Mobile";
    } elseif(wp_is_mobile()) {
        $device = "Mobile";
    }
    
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $source = isset($_POST['source']) ? urldecode($_POST['source']) : 'Footer Form';
    
    add_post_meta( $post_id, 'device', $device );
    add_post_meta( $post_id, 'source', $source );
    add_post_meta( $post_id, 'user_agent', $user_agent );

    // Send email to system
    $check = wp_mail($to_email, $subject, $body, $headers);

    if ($check) {
        $result['status'] = 1;
        
        // No thank you email for footer form as requested (only system email)
    }
    
    echo json_encode($result);
    die();
}

function wonom_check_rate_limit_ip() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    
    $transient_name = 'rate_limit_' . md5($ip);
    $submit_count = get_transient($transient_name);
    
    if ($submit_count === false) {
        set_transient($transient_name, 1, 60);
        return true;
    }
    
    if ($submit_count >= 2) {
        return false;
    }
    
    set_transient($transient_name, $submit_count + 1, 60);
    return true;
}

add_action('wp_ajax_submit_career_popup_application', 'ajax_submit_career_popup_application');
add_action('wp_ajax_nopriv_submit_career_popup_application', 'ajax_submit_career_popup_application');

function ajax_submit_career_popup_application() {
    $result = ['status' => 0];
    
    if (!wonom_check_rate_limit_ip()) {
        $result['message'] = 'Bạn gửi quá nhiều yêu cầu. Vui lòng đợi 1 phút rồi thử lại.';
        echo json_encode($result);
        die();
    }
    
    $recaptcha_token = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
    if (!empty($recaptcha_token) && !wonom_verify_recaptcha($recaptcha_token)) {
        $result['message'] = 'Xác thực bảo mật thất bại. Vui lòng thử lại.';
        echo json_encode($result);
        die();
    }
    
    $name = isset($_POST['your_name']) ? sanitize_text_field($_POST['your_name']) : '';
    $email = isset($_POST['email_address']) ? sanitize_email($_POST['email_address']) : '';
    $phone = isset($_POST['phone_number']) ? sanitize_text_field($_POST['phone_number']) : '';
    $portfolio = isset($_POST['link_portfolio']) ? sanitize_text_field($_POST['link_portfolio']) : '';
    $intro = isset($_POST['introduction']) ? sanitize_textarea_field($_POST['introduction']) : '';
    $job_title = isset($_POST['title_job']) ? sanitize_text_field($_POST['title_job']) : 'Vị trí Ứng tuyển';

    $to_email = tr_options_field('tr_theme_options.receive_email'); 
    if (empty($to_email)) {
        $to_email = get_option('admin_email');
    }

    $subject = "Ứng tuyển mới: " . $job_title . " - " . $name;
    
    $content = '<h2 style="margin-top:0; font-size:18px; color:#111; margin-bottom:20px;">Ứng viên ' . $name . ' vừa ứng tuyển</h2>';
    $content .= '<table width="100%" cellpadding="8" cellspacing="0" border="0" style="font-size: 14px;">';
    $content .= '<tr><td width="35%" style="font-weight:bold; color:#555;">Họ và tên:</td><td>' . $name . '</td></tr>';
    $content .= '<tr><td style="font-weight:bold; color:#555;">Vị trí ứng tuyển:</td><td>' . $job_title . '</td></tr>';
    $content .= '<tr><td style="font-weight:bold; color:#555;">Email:</td><td>' . $email . '</td></tr>';
    $content .= '<tr><td style="font-weight:bold; color:#555;">Số điện thoại:</td><td>' . $phone . '</td></tr>';
    $content .= '<tr><td style="font-weight:bold; color:#555;">Xem CV:</td><td>File đính kèm</td></tr>';
    if(!empty($portfolio)) $content .= '<tr><td style="font-weight:bold; color:#555;">Link Portfolio:</td><td><a href="'.$portfolio.'" style="color:#EB4250;">'.$portfolio.'</a></td></tr>';
    if(!empty($intro)) $content .= '<tr><td style="font-weight:bold; color:#555; vertical-align:top;">Giới thiệu:</td><td>' . nl2br($intro) . '</td></tr>';
    $content .= '</table>';
    
    $body = wonom_email_template('TUYỂN DỤNG', $content);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    
    // Handle File Upload
    $attachments = array();
    if (!empty($_FILES['upload_cv']['name'])) {
        $uploaded_file = $_FILES['upload_cv'];
        
        $allowed_exts = ['pdf', 'ppt', 'pptx', 'doc', 'docx', 'jpg', 'png'];
        $file_info = pathinfo($uploaded_file['name']);
        $extension = isset($file_info['extension']) ? strtolower($file_info['extension']) : '';
        
        if (in_array($extension, $allowed_exts) && $uploaded_file['error'] == 0 && $uploaded_file['size'] <= 5242880) { // 5MB limit
            $upload_dir = wp_upload_dir();
            $file_name = sanitize_file_name($uploaded_file['name']);
            $target_file = $upload_dir['path'] . '/' . wp_unique_filename($upload_dir['path'], $file_name);
            
            if (move_uploaded_file($uploaded_file['tmp_name'], $target_file)) {
                $attachments[] = $target_file;
            }
        } else {
            $result['message'] = 'Lỗi file CV (định dạng không hỗ trợ hoặc vượt quá 5MB).';
            echo json_encode($result);
            die();
        }
    }
    
    $check = wp_mail($to_email, $subject, $body, $headers, $attachments);

    if ($check) {
        $result['status'] = 1;
        
        // Gửi mail cảm ơn cho ứng viên
        $thank_you_subject = 'Cảm ơn bạn đã ứng tuyển vị trí ' . $job_title;
        $thank_you_content = '<h2 style="margin-top:0; font-size:18px; color:#111; margin-bottom:20px;">Xin chào ứng viên, ' . $name . '</h2>';
        $thank_you_content .= '<p style="margin-bottom:15px;">Cảm ơn bạn đã quan tâm và gửi hồ sơ ứng tuyển vị trí <strong>' . $job_title . '</strong>.</p>';
        $thank_you_content .= '<p style="margin-bottom:15px;">Chúng tôi đã nhận được thông tin và CV của bạn. Bộ phận Tuyển dụng sẽ xem xét hồ sơ và liên hệ với bạn trong thời gian sớm nhất nếu hồ sơ phù hợp.</p>';
        $thank_you_content .= '<p style="margin-bottom:25px;">Chúc bạn một ngày tốt lành!</p>';
        $thank_you_content .= '<p style="margin-bottom:0; color:#555;">Trân trọng,<br><strong style="color:#111;">Phòng Nhân sự Wonom</strong></p>';
        
        $thank_you_body = wonom_email_template('TUYỂN DỤNG', $thank_you_content);
        wp_mail($email, $thank_you_subject, $thank_you_body, $headers);
    } else {
        $result['message'] = 'Có lỗi xảy ra khi gửi mail, vui lòng thử lại sau.';
    }

    echo json_encode($result);
    die();
}
