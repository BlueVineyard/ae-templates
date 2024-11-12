<?php
/*
Plugin Name: AE Templates
Description: A plugin to create and manage multiple templates with their own styles and scripts.
Version: 1.0
Author: Rohan T George
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class AE_Templates
{

    public function __construct()
    {
        add_action('init', [$this, 'register_shortcodes']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_template_scripts']);

        add_action('wp_ajax_filter_job_listings', [$this, 'filter_job_listings']);
        add_action('wp_ajax_nopriv_filter_job_listings', [$this, 'filter_job_listings']);
    }

    /**
     * Register shortcodes
     *
     * @return AE
     */
    public function register_shortcodes()
    {
        add_shortcode('ae_template', [$this, 'render_template']);
    }

    /**
     * Enqueue template-specific styles and scripts
     *
     * @return AE
     */
    public function enqueue_template_scripts()
    {
        wp_enqueue_style('category-filter-style', plugin_dir_url(__FILE__) . 'css/category-filter-style.css');
        wp_enqueue_script('category-filter-script', plugin_dir_url(__FILE__) . 'js/category-filter-script.js', array('jquery'), null, true);


        wp_enqueue_style('swiper-css', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.css');
        wp_enqueue_script('swiper-js', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/11.0.5/swiper-bundle.min.js', array('jquery'), null, true);
        wp_enqueue_style('employer-slider-style', plugin_dir_url(__FILE__) . 'css/employer-slider-style.css');
        wp_enqueue_script('employer-slider-script', plugin_dir_url(__FILE__) . 'js/employer-slider-script.js', array('jquery', 'swiper-js'), null, true);
    }

    /**
     * Render template based on attributes
     *
     * @return AE
     */
    public function render_template($atts)
    {
        $atts = shortcode_atts([
            'name' => 'default',
        ], $atts, 'ae_template');

        $template_name = sanitize_text_field($atts['name']);

        ob_start();

        // Locate the template file
        $template_file = plugin_dir_path(__FILE__) . 'templates/' . $template_name . '.php';

        if (file_exists($template_file)) {
            include $template_file;
        } else {
            echo 'Template not found.';
        }

        return ob_get_clean();
    }

    /**
     * AJAX handler for filtering job listings
     *
     * @return AE
     */
    public function filter_job_listings()
    {
        $category = sanitize_text_field($_POST['category']);

        $args = array(
            'post_type' => 'job_listing',
            'posts_per_page' => 6,
            'orderby' => 'modified', // Order by modified date
            'order' => 'DESC', // Latest modified first
            'post_status' => 'publish', // Only show published jobs
        );

        if (!empty($category)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'job_listing_category',
                    'field'    => 'slug',
                    'terms'    => $category,
                ),
            );
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                $title = get_the_title();
                $company_name = get_post_meta(get_the_ID(), '_company_name', true);
                $job_types = wp_get_post_terms(get_the_ID(), 'job_listing_type', array('fields' => 'names'));
                $location = get_post_meta(get_the_ID(), '_job_location', true);
                $salary = get_post_meta(get_the_ID(), '_job_salary', true);
                $salaryUnit = get_post_meta(get_the_ID(), '_job_salary_unit', true);
                $salaryCurrency = get_post_meta(get_the_ID(), '_job_salary_currency', true);
                $jobDuration = get_post_meta(get_the_ID(), '_job_expires', true);
                $publish_date = get_the_date();
                $last_updated = human_time_diff(get_the_modified_time('U'), current_time('timestamp')) . ' ago';

                $map_svg = '<svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.33337 8.95258C3.33337 5.20473 6.31814 2.1665 10 2.1665C13.6819 2.1665 16.6667 5.20473 16.6667 8.95258C16.6667 12.6711 14.5389 17.0102 11.2192 18.5619C10.4453 18.9236 9.55483 18.9236 8.78093 18.5619C5.46114 17.0102 3.33337 12.6711 3.33337 8.95258Z" stroke="#3D3935" stroke-width="1.5" /><ellipse cx="10" cy="8.8335" rx="2.5" ry="2.5" stroke="#3D3935" stroke-width="1.5" /></svg>';
                $salary_svg = '<svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="10" cy="10.4998" r="8.33333" stroke="#3D3935" stroke-width="1.5"/><path d="M10 5.5V15.5" stroke="#3D3935" stroke-width="1.5" stroke-linecap="round"/><path d="M12.5 8.41683C12.5 7.26624 11.3807 6.3335 10 6.3335C8.61929 6.3335 7.5 7.26624 7.5 8.41683C7.5 9.56742 8.61929 10.5002 10 10.5002C11.3807 10.5002 12.5 11.4329 12.5 12.5835C12.5 13.7341 11.3807 14.6668 10 14.6668C8.61929 14.6668 7.5 13.7341 7.5 12.5835" stroke="#3D3935" stroke-width="1.5" stroke-linecap="round"/></svg>';
                $time_svg = '<svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1.66659 10.5003C1.66659 15.1027 5.39755 18.8337 9.99992 18.8337C14.6023 18.8337 18.3333 15.1027 18.3333 10.5003C18.3333 5.89795 14.6023 2.16699 9.99992 2.16699" stroke="#D83636" stroke-width="1.5" stroke-linecap="round"/><path d="M10 8V11.3333H13.3333" stroke="#D83636" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="10.5003" r="8.33333" stroke="#D83636" stroke-width="1.5" stroke-linecap="round" stroke-dasharray="0.5 3.5"/></svg>';

                echo '<div class="ae_job_card">';
                echo '<div class="ae_job_card-top">';
                echo '<img class="ae_job_card__img" src="' . esc_url($featured_image) . '" alt="' . esc_attr($title) . '">';
                echo '<div>';
                echo '<h4 class="ae_job_card__title"><a href="' . esc_url(get_the_permalink()) . '">' . esc_html($title) . '</a></h4>';
                echo '<span class="ae_job_card__company">' . esc_html($company_name) . '</span>';
                echo '<span style="color: #CACACA; font-size: 14px;"> | </span>';

                if (!empty($job_types)) {
                    foreach ($job_types as $job_type) {
                        $job_type_color = '';
                        switch ($job_type) {
                            case 'Full Time':
                                $job_type_color = '17B86A';
                                break;
                            case 'Part Time':
                                $job_type_color = 'FF8200';
                                break;
                            case 'Contract':
                                $job_type_color = '0275F4';
                                break;
                            case 'Casual':
                                $job_type_color = '101010';
                                break;
                        }
                        echo '<span class="ae_job_card__type" style="color: #' . $job_type_color . ';">' . esc_html($job_type) . '</span>';
                    }
                }

                echo '</div>';
                echo '</div>';
                if ($location) {
                    echo '<div class="ae_job_card__location">' . $map_svg . ' <span>' . esc_html($location) . '</span></div>';
                }
                // if ($salary) {
                //     echo '<div class="ae_job_card__salary">' . $salary_svg . ' <span>' . esc_html($salary) . ' ' . $salaryCurrency . '</span></div>';
                // }
                echo '<hr/>';
                echo '<div class="ae_job_card-bottom">';
                echo '<div class="ae_job_card__published">';
                if ($jobDuration) {
                    $formattedJobDuration = date_i18n('M jS, Y', strtotime($jobDuration));
                    echo '' . $time_svg . ' <span>' . esc_html($formattedJobDuration) . '</span>';
                }
                echo '</div>';
                echo '<span class="ae_job_card__modified">Updated ' . esc_html($last_updated) . '</span>';
                echo '</div>';
                echo '</div>';

            endwhile;
        else :
            echo '<p>No job listings found.</p>';
        endif;

        wp_reset_postdata();

        wp_die(); // Required to terminate immediately and return a proper response
    }
}

new AE_Templates();
