
<?php
require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/hello/:name/:bla', function ($name, $bla) {
    echo "Hello, $name $bla test";
});

$app->run();

exit();
?>

Something is wrong with the XAMPP installation :-(
