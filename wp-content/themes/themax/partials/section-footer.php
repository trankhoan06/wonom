<?php 
    $addFooter = !empty($template_args['footer']) ?$template_args['footer'] :0;
    $addPartner = !empty($template_args['partner']) ?$template_args['partner'] :0;
?>

<?php

/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 */
 
    $facebook= tr_options_field('tr_theme_options.facebook');
    $tiktok= tr_options_field('tr_theme_options.tiktok');
    $youtube= tr_options_field('tr_theme_options.youtube');

    $currentLang = substr(get_locale(), 0,2);
    $companyContactKey ="company_contact";
    if($currentLang!="vi"){
        $companyContactKey .="_".$currentLang;
    }
    $company_contact= tr_options_field('tr_theme_options.'.$companyContactKey);

    $company_form= tr_options_field('tr_theme_options.company_form');
    $company_zalo= tr_options_field('tr_theme_options.company_zalo');


    $link_document= tr_options_field('tr_theme_options.link_document');
    $link_360= tr_options_field('tr_theme_options.link_360');
    $link_messenger= tr_options_field('tr_theme_options.link_messenger');



  $homeUrl = home_url();
  $isFrontPage = is_front_page();

    global $hotline;

 

?>
<?php if($addFooter){ ?>

    <section id="footer" class="animatedParent animateOnce fp-auto-height-responsive cover  " data-anchor="lien-he"
    s data-title="<?= __("Liên hệ","tbs") ?>">
    <div class="section-content-wrapper">
        <?php if($addPartner){ ?>
            <?php  nmc_get_template_part( 'partials/section-partner', ["sectionId"=>1] ); ?>

        <?php } ?>
        <div class="section-padding footer-content-wrapper relative dark">
            <div class="container-d ">
                <div class="row gutter-xxl">
                    <div class="col-lg-5  order-lg-last">
                        <h4 class="title lg text-uppercase <?= defaultAnimation(1) ?>"><?= __("Nhận thông tin từ chúng tôi","tbs") ?></h3>
                        <div class="contact-info-wrapper <?= defaultAnimation(3) ?>">
                            <div class="">
                                <?php  nmc_get_template_part( 'partials/form-newsletter', [] ); ?>
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-7 mt-5 mt-lg-0  ">
                        <div class="row  gutter-xxl">
                            <div class="col-md-5 offset-lg-1 order-md-last">
                                <div class="footer-menu">
                                    <?php wp_nav_menu( array( 'theme_location' => 'footer','container' => 'ul', 'menu_class' => 'menu' ) ); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4 class="title lg text-uppercase">Văn phòng trụ sở chính</h4>
                                <div class="contact-items">
                                    <div class="item"><i class="fal fa-map-marker-alt"></i> 5A Xa lộ Xuyên Á, P. An Bình, TP. Dĩ An, Tỉnh Bình Dương</div>
                                    <div class="item"><i class="fal fa-phone-alt"></i> <a href="tel:">(84 28) 37 241 241</a></div>
                                    <div class="item"><i class="fal fa-fax"></i> (84 28) 38 960 223</div>
                                    <div class="item"><i class="fal fa-map-marker-alt"></i> <a href="mailto:info@tbsgroup.vn">info@tbsgroup.vn</a></div>

                                </div>
                            </div>
                        </div>

                    </div>


                </div>
            </div>

        </div>
    </div>
        <button id="gotop" class="btn-gotop"><i class="fal fa-long-arrow-up"></i></button>
    </section>
<?php } ?>
</div>
<?php if(  $isFrontPage && 1==2) { ?>
<div class="mask intro-bg">
    <div class="mask-bg animatedParent animateOnce">
        <div class="mask-logo">
            <img class="img-fluid" src="<?= get_template_directory_uri() . '/images/logo.svg' ?>" alt="<?= __("TBS Group","tbs") ?>"  />
        </div>
    </div>
</div>
<?php } ?>

<ul id="fullpageMenu" class="fullpage-nav b-static animated fadeIn go font-1"></ul>


<div class="fixed-btn bottom left b-static animated fadeIn go">
    <div class="copyright">
        <?= sprintf(__("© %d Wonom. All Rights Reserved.", "wonom"), date("Y")); ?>
    </div>
</div>

<div class="fixed-btn fixed-action-btn bottom right b-static">
    <a href="<?= $facebook ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
     <a href="<?= $youtube ?>" target="_blank" rel="noopener" aria-label="Youtube"><i class="fab fa-youtube"></i></a>
     <?php if(!empty($hotline)) { ?>
    <a href="tel:<?= preg_replace("/[^0-9]/", "", $hotline ) ?>" class="svg-ani1 hover-rotate">
        <i class="fal fa-phone-alt"></i>
    </a>
    <?php } ?>
    <a href="<?= $company_zalo ?>" target="_blank" class="svg-ani1 zalo hover-rotate">
      <img class="svg" src="<?= get_template_directory_uri() . '/images/icon-zalo.svg' ?>"  alt="" />
    </a>
</div>




<div class="modal fade modal-form-popup" tabindex="-1" id="formModal" >
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content div_zindex  intro-bg">
      <div class="modal-body  ">
        <a class="close close-modal" href="javascript:void(0)" data-bs-dismiss="modal"></a>

        <div class="form-popup-modal">
            <div class="form-popup bg-rotate animated fadeIn delay-250">
                <h3 class="section-title xl text-center  <?= defaultAnimation(0) ?>"><?= __("<span>Đăng ký</span> <strong>Tư vấn</strong>","tbs") ?></h3>
                <div class="<?= defaultAnimation(2) ?>">
                    <?php  nmc_get_template_part( 'partials/form-register-popup', [] ); ?>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
    $popupKey ="enable_popup";
    $popupImageKey ="banner_popup";
    $popupLinkKey ="link_popup";

    $popupEnable = tr_options_field('tr_theme_options.'.$popupKey);
    $popupImage = wp_get_attachment_image_src( tr_options_field('tr_theme_options.'.$popupImageKey) ,'full');
    $popupLink = tr_options_field('tr_theme_options.'.$popupLinkKey);
?>
<?php if($popupEnable =="1" && $isFrontPage) : ?>
<div class="d-none">
    <div id="startpopup" class="startpopup p-0">
        <a href="<?= $popupLink ?>">
            <img src="<?= $popupImage[0] ?>" alt="<?= __("Popup","tbs") ?>" />
        </a>
    </div>
</div>
<script type="text/javascript">

  jQuery(document).ready(function($) {
    if($("#startpopup").length>0){
        $.fancybox.open({ // FancyBox 3
            src: $('#startpopup img').first().attr("src"),
            type: 'image',
            opts: {
                baseClass: 'start-popup',
                smallBtn: true,
                infobar :false,
                buttons :false,
                afterShow: function (instance, current) {
                    if ($('#startpopup a').length > 0) {
                        var $link = $('#startpopup a').first();
                        var $link2 = $("<a>");
                        $link2.attr("href", $link.attr("href"));
                        $(".fancybox-is-open .fancybox-image-wrap .fancybox-image").wrap($link2);
                    }
                }
            }
        });
    }
  });
</script>
<?php endif; ?>

