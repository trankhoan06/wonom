<?php 
    $sectionId = !empty($template_args['sectionId']) ?$template_args['sectionId'] :0;    
    //$home_partner = tr_posts_field('home_partner',$sectionId);
    //$home_partner_other = tr_posts_field('home_partner_other',$sectionId);

    $sectionTitle =  tr_posts_field('partner_title',$sectionId);
    $partner_image =   tr_posts_field('partner_image',$sectionId);
    $partnerItems =tr_posts_field('partner_items',$sectionId);

?>


<div  class=" animatedParent animateOnce section-partner" data-anchor="doi-tac"  >
    <div class="container-d">
        <div class="section-content-wrapper relative">
            <div class="section-content section-padding">
                <h2 class="section-title text-center <?= defaultAnimation(1) ?>">
                    <?= __("Đối tác","tbs") ?>
                </h2>
                <div class="section-slide">
                    <div class="swiper swiper-default partner-slide " >
                        <div class="swiper-wrapper">
                            <?php foreach ($partnerItems as $value) { ?>
                            <div class="swiper-slide" >
                                <div class="item">
                                    <?= wp_get_attachment_image($value["image"], 'full', false, ["class"=>"img-fluid"] ) ?>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/ardor.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/coach-new-york-logo.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/decathlon.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/DHL.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/ecco.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/geo.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/lancaster.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/landscul.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/nam-long.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/sketcher.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/tory-burch.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/vertical.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/ardor.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                            <div class="swiper-slide" >
                                 <div class="item">
                                    <img src="<?= get_template_directory_uri() ?>/images/tmp/doi-tac/ardor.png" alt="TBS Group" class="img-fluid" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="slide-control ">
                        <div class="swiper-pagination mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>