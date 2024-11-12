<div class="category-filter-template">
    <div id="job-category-buttons">
        <button class="job-category-button active" data-category="">ALL</button>
        <?php
        $categories = get_terms('job_listing_category', array('hide_empty' => true));
        foreach ($categories as $category) {
            echo '<button class="job-category-button" data-category="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</button>';
        }
        ?>
    </div>

    <div id="job-listings-container">
        <!-- Filtered job listings will appear here -->
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        function loadJobListings(category) {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'filter_job_listings',
                    category: category,
                },
                success: function(response) {
                    $('#job-listings-container').html(response);
                }
            });
        }

        // Initial load for all job listings
        loadJobListings('');

        // Button click event
        $('.job-category-button').click(function() {
            $('.job-category-button').removeClass('active');
            $(this).addClass('active');

            var category = $(this).data('category');
            loadJobListings(category);
        });
    });
</script>