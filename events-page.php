<!--
    Template Name: EventsPage
-->

<!DOCTYPE html>
<html>
    <head>
        <title><?= the_title();?></title>
    </head>
    <body>
        <h1><?= the_title(); ?></h1>
        <?php
            $locations = get_locations();

            $endpoint = 'http://localhost/wordpress/wp-json/custom/events';
            $url = sprintf('%s?%s', $endpoint, http_build_query([
                'location' => isset($_GET['location_sort']) ? $_GET['location_sort'] : '',
                'date_start' => isset($_GET['date_start']) ? $_GET['date_start'] : '',
                'date_finish' => isset($_GET['date_finish']) ? $_GET['date_finish'] : '',
            ]));

            $results = json_decode(wp_remote_get($url)['body'], true);

            if(!isset($results['code']) || $results['code'] != 'no_posts'):
                foreach($results as $post): ?>
                    <a href="<?=get_permalink($post['ID'])?>"><h2><?=$post['post_title']?></h2></a>
                    <p>By: <?=get_user_by('id', $post['post_author'])->display_name?></p>
                    <p>Date: <?=$post['meta']['date'][0]?></p>
                    <p>Address: <?=get_post_field('post_title', $post['meta']['location'][0])?>, 
                                <?=get_post_meta($post['meta']['location'][0], 'address')[0]?></p>
                <?php endforeach;
            else: ?>
                <h1>Записи не найдены.</h1>
            <?php endif;

            ?>
                <form>
                <h2>Сортировка по площадке:</h2>
                <select id="location_sort_field" name="location_sort">
            <?php
            foreach ($locations as $location): ?>
                <option value="<?=$location[0]?>" <?=(array_key_exists('location_sort', $_GET) && $location[0] == $_GET['location_sort']) ? 'selected' : ''?>><?=$location[1]?></option>
            <?php endforeach;
            ?>
                </select>

                <h2>Сортировка по дате проведения:</h2>
                <h3>Начальная дата:</h3>
                <input type="date" id="date_start_field" name="date_start" value="<?=array_key_exists('date_start', $_GET) ? $_GET['date_start'] : ''?>">
                <h3>Конечная дата:</h3>
                <input type="date" id="date_finish_field" name="date_finish" value="<?=array_key_exists('date_finish', $_GET) ? $_GET['date_finish'] : ''?>">
                <p></p>
                <input type="submit" value="Сортировать">
                </form>
    </body>
</html>