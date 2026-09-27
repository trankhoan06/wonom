<?php
/**
 * Template Name: Policy
 */
get_header();

$policy_title = tr_posts_field('policy_title');
$policy_content = tr_posts_field('policy_content');
?>
<main class="main" data-namespace="policy">
    <section class="policy_section section_pt section_pb" data-mode="black">
        <div class="container">
            <div class="policy_wrap">
                <?php if ($policy_title): ?>
                    <h1 class="policy_title h2 heading h5_mb"><?php echo esc_html($policy_title); ?></h1>
                <?php endif; ?>
                <?php if ($policy_content): ?>
                    <div class="policy_content txt_18">
                        <?php echo $policy_content; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php
get_footer();
