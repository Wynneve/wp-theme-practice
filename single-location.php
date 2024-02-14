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
            $address = get_post_meta($post_id, 'address', true);
            $telephone = get_post_meta($post_id, 'telephone', true);
    ?>
        <head>
            <title><?php the_title(); ?></title>
        </head>
        <body>
            <h1> <?php the_title(); ?> </h1>

            <?php the_content(); ?>

            <p> Address: <?php echo $address ?>
            <p> Telephone: <?php echo $telephone ?> </p>
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