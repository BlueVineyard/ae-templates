<div class="employer-slider-container">
    <div class="swiper-container">
        <div class="swiper-wrapper">
            <?php
            $today = date('Y-m-d');
            $args = array(
                'post_type' => 'job_listing',
                'posts_per_page' => 6,
                'post_status' => 'publish',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => '_company_name',
                        'compare' => 'EXISTS',
                    ),
                    // Add expiration date check if needed
                    // array(
                    //     'key' => '_job_expires',
                    //     'value' => $today,
                    //     'compare' => '>=',
                    //     'type' => 'DATE'
                    // ),
                ),
            );
            $query = new WP_Query($args);

            if ($query->have_posts()) :
                $companies = [];

                while ($query->have_posts()) : $query->the_post();
                    $company_name = get_post_meta(get_the_ID(), '_company_name', true);

                    if (!in_array($company_name, array_column($companies, 'name'))) {
                        // Count total jobs for this company directly in the query
                        $total_jobs = count(array_filter($query->posts, function ($post) use ($company_name) {
                            return get_post_meta($post->ID, '_company_name', true) === $company_name;
                        }));

                        if ($total_jobs > 0) {
                            $companies[] = [
                                'name' => $company_name,
                                'image' => get_the_post_thumbnail_url(get_the_ID(), 'full'),
                                'jobs' => $total_jobs,
                            ];
                        }
                    }
                endwhile;

                foreach ($companies as $company) :
                    $company_url = esc_url(home_url('/job-filter/?company_names=' . urlencode($company['name']) . ''));
                    $first_letter = strtoupper(substr($company['name'], 0, 1));
            ?>
                    <div class="swiper-slide">
                        <a href="<?php echo $company_url; ?>" class="employer-slide-link">
                            <div class="employer-slide">
                                <?php if ($company['image']) : ?>
                                    <img class="employer-slide__img" src="<?php echo esc_url($company['image']); ?>" alt="<?php echo esc_attr($company['name']); ?>" loading="lazy">
                                <?php else : ?>
                                    <div class="employer-slide__placeholder">
                                        <?php echo esc_html($first_letter); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="employer-slide__company"><?php echo esc_html($company['name']); ?></div>
                                    <div class="employer-slide__jobs"><?php echo esc_html($company['jobs']); ?> Jobs Available</div>
                                </div>
                            </div>
                        </a>
                    </div>
            <?php
                endforeach;
            else :
                echo '<p>No employers found.</p>';
            endif;

            wp_reset_postdata();
            ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="custom-swiper-button-next">
            <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.125 1.375L14.75 7M14.75 7L9.125 12.625M14.75 7H1.25" stroke="#FF8200" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
        <div class="custom-swiper-button-prev">
            <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6.875 1.375L1.25 7M1.25 7L6.875 12.625M1.25 7H14.75" stroke="#FF8200" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
    </div>
</div>