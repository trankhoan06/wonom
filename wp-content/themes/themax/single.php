<?php get_header(); ?>

<main class="main default-single-page" data-namespace="singlePost">
    <div class="container" style="padding-top: 150px; padding-bottom: 80px;">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <h1 class="h2 heading"><?php the_title(); ?></h1>
            <div class="post-meta txt_14" style="margin-bottom: 30px; color: #888;">
                <span>Ngày đăng: <?php echo get_the_date(); ?></span>
            </div>
            
            <div class="post-content txt_16">
                <!-- Đây là nơi hiển thị nội dung nhập từ trình soạn thảo mặc định -->
                <?php the_content(); ?> 
            </div>
        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
