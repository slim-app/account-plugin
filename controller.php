<?php
/**
 * API route group
 */
$app->group('/account', function () use ($app) {
    $app->get('/login', function () use ($app) {
    	$app->config(array(
		    'templates.path' => __DIR__."/"
		));
        $app->render('views/login.php', array());
    });
    $app->post('/login', function () use ($app)
    {
        $loginstatus = SlimApp\Accounts::login($app->request->params("username"), $app->request->params("password"));
        var_dump($loginstatus);
    });
});