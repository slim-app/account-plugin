<?php
/**
 * API route group
 */
$app->group('/account', function () use ($app) {
    $app->get('/login', function () use ($app) {
    	$app->config(array(
		    'templates.path' => __DIR__."/"
		));
        $app->render('/../../vendor/slimapp/account/views/login.php', array());
    });
    $app->post('/login', function () use ($app)
    {
        $loginstatus = SlimPlugin\Account\Accounts::login($app->request->params("username"), $app->request->params("password"));
        if($loginstatus[0]!==false)
        {
            $_SESSION["id"]=$loginstatus[0];
            $app->redirect(url::createURL(""));
        }
        else
        {
            $msg = "<b>Login fehlgeschlagen</b>";
            if($loginstatus[1]!==false)
            {
                $msg .= "<br>Status: ".$loginstatus[1];
            }
            $app->render('/../../vendor/slimapp/account/views/msg.php', array("msg"=>$msg));
        }
        #var_dump($loginstatus);
    });
    $app->get("/confirm/:mail/:hash", function ($mail, $hash) use ($app) {
        $mail = base64_decode($mail);
        $i = SlimPlugin\Account\Accounts::checkHash($mail, $hash);
        if($i==true)
        {
            $app->render('/../../vendor/slimapp/account/views/msg.php', array("msg"=>"Acount aktiviert"));
        }
        else
        {
            $app->render('/../../vendor/slimapp/account/views/msg.php', array("msg"=>"Aktivierung fehlgeschlagen"));
        }
    });
});