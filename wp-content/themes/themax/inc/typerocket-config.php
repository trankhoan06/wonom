<?php
//$int = (int) filter_var($str, FILTER_SANITIZE_NUMBER_INT);
$editorSettings = [];

// Adding Matrix field to existing "page" post type 
add_action('edit_form_after_title', function($post) use($editorSettings) {
    // Page TypeRocket configurations have been moved to individual files in the /inc directory 
    // and are included in functions.php
});

add_action('edit_form_after_editor', function($post) use($editorSettings) {
    echo '<div class="typerocket-container">';
    if($post->post_type == 'page' && basename(get_page_template())=="pdppolicy.php") {

    }
    echo '</div>';
});



/*
$floor = tr_post_type('floor','Tầng')->setSlug('tang')->setIcon('Book');
$args = $floor->getArguments();
$args = array_merge( $args, array(
  'supports'  =>  array('title','thumbnail'),
  'publicly_queryable'  => false
  )
);

$floor->setArguments( $args );
$floorDetails = tr_meta_box('floor_detail')->setLabel("Chi tiết");
$floorDetails->setCallback(function(){
    $form = tr_form();

    echo $form->row(
        $form->text('subdivision_title')->setLabel("Tiêu đề"),
        $form->image('subdivision_bg')->setLabel("Hình nền")
    );
    echo $form->repeater('prod_items')->setLabel("Sản phẩm")->setFields([
            $form->row(
                $form->text('title')->setLabel("Tên sản phẩm"),
                $form->search('prod')->setPostType('prod')->setLabel("Chọn Sản phẩm"),
                $form->text('position')->setLabel("Vị trí trên hình (Top,Left)")
            ),
            $form->textarea('coordinates')->setLabel("Tọa độ")
    ]);
    echo $form->repeater('legend')->setLabel("Ghi chú")->setFields([
        $form->row(
            $form->text('title')->setLabel("Tên sản phẩm"),
            $form->color('color')->setLabel("Mã màu")
        )
    ]);



});
$floor->apply($floorDetails);

$floorcate = tr_taxonomy('floorcate', 'Tháp');
$floorcate->setSlug('thap');
$floorcate->setHierarchical();

$floorcate->setMainForm(function() {
    $form = tr_form();
    echo $form->image('map')->setLabel("Hình vị trí");
});


$args = $floorcate->getArguments();
$args = array_merge( $args, array( 'show_admin_column' => true ) );
$floorcate->setArguments( $args );
$floor->apply($floorcate);



$prod = tr_post_type('prod','Sản phẩm')->setSlug('san-pham')->setIcon('Book');
$args = $prod->getArguments();
$args = array_merge( $args, array(
  'supports'  =>  array('title','thumbnail'),
  'publicly_queryable'  => false
  )
);

$prod->setArguments( $args );
$prodDetails = tr_meta_box('prod_detail')->setLabel("Chi tiết");
$prodDetails->setCallback(function(){
    $form = tr_form();
    echo $form->row(
        $form->text('bed')->setLabel("Phòng ngủ"),
        $form->text('bath')->setLabel("Phòng tắm"),
        $form->text('acreage_1')->setLabel("Diện tích tim tưởng"),
        $form->text('acreage_2')->setLabel("Diện tích thông thủy")

    );
    echo $form->row(
        $form->text('link_360')->setLabel("Link 360")
        //$form->image('map')->setLabel("Hình vị trí"),

    );

    echo $form->repeater('image_items')->setLabel("Hình ảnh mặt bằng")->setFields([
        $form->row(
            $form->text('title')->setLabel("Tiêu đề"),
            $form->image('image')->setLabel("Hình")
        )
    ]);

    echo $form->repeater('other_items')->setLabel("Hình ảnh Phối cảnh")->setFields([
        $form->row(
            $form->text('title')->setLabel("Tiêu đề"),
            $form->image('image')->setLabel("Hình")
        )
    ]);

});
$prod->apply($prodDetails);

*/



$register = tr_post_type('register','Liên hệ')->setSlug('dang-ky-slug')->setIcon('Book');  
$args = $register->getArguments();
$args = array_merge( $args, array( 
  'supports'  =>  array('title'),
  'publicly_queryable'  => false
  )
);

$register->setArguments( $args );
$registerDetails = tr_meta_box('register_detail')->setLabel("Chi tiết");
$registerDetails->setCallback(function(){
    $form = tr_form();
    echo $form->text('mobile')->setLabel("Số điện thoại");
    echo $form->text('email')->setLabel("Email");
    //echo $form->text('address')->setLabel("Địa chỉ");
    echo $form->text('interest')->setLabel("Quan tâm");
    echo $form->text('device')->setLabel("Thiết Bị");
    echo $form->text('source')->setLabel("Nguồn");
    echo $form->text('user_agent')->setLabel("User Agent");
    echo $form->textarea('content')->setLabel("Nội dung");

});
$register->apply($registerDetails);

$register->addColumn('mobile', true, 'Số điện thoại', function($value) {
    echo $value;
}, 'text');
$register->addColumn('email', true, 'Email', function($value) {
    echo $value;
}, 'text');
/*$register->addColumn('address', true, 'Địa chỉ', function($value) {
    echo $value;
}, 'text');
*/
$register->addColumn('interest', true, 'Sản phẩm quan tâm', function($value) {
    echo $value;
}, 'text');

$register->addColumn('content', true, 'Nội dung', function($value) {
    echo $value;
}, 'text');
$register->addColumn('device', true, 'Thiết bị', function($value) {
    echo $value;
}, 'text');
$register->addColumn('source', true, 'Nguồn', function($value) {
    echo $value;
}, 'text');

// Limit TypeRocket search by current language
add_action('pre_get_posts', function($query) {
    if (defined('REST_REQUEST') && REST_REQUEST) {
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (strpos($request_uri, '/wp-json/typerocket/v1/search') !== false) {
            if (!empty($_SERVER['HTTP_REFERER'])) {
                $referer = $_SERVER['HTTP_REFERER'];
                if (strpos($referer, 'post.php') !== false) {
                    $query_string = parse_url($referer, PHP_URL_QUERY);
                    if (is_string($query_string)) {
                        parse_str($query_string, $query_params);
                        if (!empty($query_params['post']) && function_exists('pll_get_post_language')) {
                            $post_id = intval($query_params['post']);
                            $lang = pll_get_post_language($post_id, 'slug');
                            if ($lang) {
                                $tax_query = $query->get('tax_query');
                                if (!is_array($tax_query)) {
                                    $tax_query = array();
                                }
                                $tax_query[] = array(
                                    'taxonomy' => 'language',
                                    'field'    => 'slug',
                                    'terms'    => $lang,
                                );
                                $query->set('tax_query', $tax_query);
                            }
                        }
                    }
                }
            }
        }
    }
});
