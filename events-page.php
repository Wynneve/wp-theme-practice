<!--
    Template Name: EventsPage
-->

<!DOCTYPE html>
<html>
    <?php
        if(have_posts()):
    ?>
        <head>
            <title><?php the_title();?></title>
            <!-- <?= 'asdsadas' ?> -->
        </head>
        <body>
            <h1><?php the_title(); ?></h1>
            <?php
                $events = new WP_Query('post_type=event&posts_per_page=10');
                $locations = get_locations();

                while($events->have_posts()): $events->the_post();
                    $post_id = get_the_ID();
                    $date = get_post_meta($post_id, 'date', true);
                    $location_id = get_post_meta($post_id, 'location', true);
                    $date = get_post_meta($post_id, 'date', true);

                    if(array_key_exists('location_sort', $_GET) && $_GET['location_sort'] != $location_id) continue;
                    if(array_key_exists('date_start', $_GET) && !empty($_GET['date_start']) && strtotime($_GET['date_start']) > strtotime($date) ) continue;
                    if(array_key_exists('date_finish', $_GET) && !empty($_GET['date_finish']) && strtotime($_GET['date_finish']) < strtotime($date) ) continue;

                    echo '<a href="' . get_the_permalink() . '"><h1>'; the_title(); echo '</h1></a>';
                    the_excerpt();

                endwhile;

                echo
                '
                    <form>
                    <h2>Сортировка по площадке:</h2>
                    <select id="location_sort_field" name="location_sort">    
                ';
                foreach ($locations as $location) {
                    echo
                    '
                        <option value="' . $location[0] . '" ' . ((array_key_exists('location_sort', $_GET) && $location[0] == $_GET['location_sort']) ? 'selected' : '') . '>' . $location[1] . '</option>
                    ';
                }
                echo
                '
                    </select>
                ';

                echo
                '
                    <h2>Сортировка по дате проведения:</h2>
                    <h3>Начальная дата:</h3>
                    <input type="date" id="date_start_field" name="date_start" value="' . (array_key_exists('date_start', $_GET) ? $_GET['date_start'] : '') . '">
                    <h3>Конечная дата:</h3>
                    <input type="date" id="date_finish_field" name="date_finish" value="' . (array_key_exists('date_finish', $_GET) ? $_GET['date_finish'] : '') . '">
                    <p></p>
                    <input type="submit" value="Сортировать">
                    </form>
                ';
            ?>
        </body>
    <?php else: ?>
        <head>
            <title>No posts</title>
        </head>
        <body>
            <p>No posts found.</p>
        </body>
    <?php endif; ?>
</html>