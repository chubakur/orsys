<?php
require_once('session_mgr.php');
if (!isset($_SESSION['user_id'])) {
    end_script_immediately('{"status":"noauth"}');
}
session_write_close();
require_once('config.php');
require_once('events.php');
$db_params = $config['mysql']['orders'];
$connection = create_mysql_connection($db_params);
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    //валидация
    //обновляем

    if (isset($_GET['minid']) && filter_input(INPUT_GET, 'minid', FILTER_VALIDATE_INT)) {
        $minid = $_GET['minid'];
        $sql_cond = "WHERE performer IS NULL AND orders.id < $minid";
    } else {
        $sql_cond = "WHERE performer IS NULL";
    }
    $sql_corpus = "SELECT orders.id, client, description, cost, date, users.email as client_email FROM orders JOIN orsys_users.users AS users ON users.id=client $sql_cond ORDER BY date DESC LIMIT 20";
//    print_r($sql_corpus.'<br>');
    $result = mysqli_query($connection, $sql_corpus);
    $answers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $answers[] = $row;
    }
    end_script_immediately(json_encode(['status' => 'ok', 'ts' => time(), 'results' => $answers]), $connection);
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ts']) && filter_input(INPUT_POST, 'ts', FILTER_VALIDATE_INT)) {
    $db_events_params = $config['mysql']['events'];
    $start_time = time();
    $events_query = 0;
    while ((time() - $start_time) < 25) {
        $events = get_events($db_events_params, $_POST['ts']);
        if (count($events) > 0) {
            end_script_immediately(json_encode(['ts' => time(), 'events' => $events]), $connection);
        }
        sleep(1);
    }
    end_script_immediately(json_encode(['ts' => time(), 'events' => []]), $connection);
}
end_script_immediately('{"status":"invalid"}', $connection);