<!--
    Template Name: Event
    Template Post Type: event
-->

<!DOCTYPE html>
<html>
    <?php
        if(have_posts()):
            while(have_posts()): the_post();
            $post_id = get_the_ID();
            $date = get_post_meta($post_id, 'date', true);
            $location_id = get_post_meta($post_id, 'location', true);
            $location = get_post_field('post_title', $location_id);
            $address = get_post_meta($location_id, 'address', true);
    ?>
        <head>
            <title><?php the_title(); ?></title>
        </head>
        <body>
            <h1> <?php the_title(); ?> </h1>

            <?php the_content(); ?>

            <p> By: <?php the_author_posts_link(); ?> </p>
            <p> Location: <?php echo $location; ?>, <?php echo $address ?> </p>
            <p> Date: <?php echo $date; ?> </p>
        </body>
    <?php endwhile; else: ?>
        <head>
            <title>No content</title>
        </head>
        <body>
            <p>No content found.</p>
        </body>
    <?php endif; ?>
</html>