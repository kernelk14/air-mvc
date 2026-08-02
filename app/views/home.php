<?php
$helper = new Helper();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>The First MVC</title>
        <link rel="stylesheet" href="<?= $helper->asset('pico/css/pico.min.css') ?>" />
    </head>
    <body>
        <main class="container">
            <h1>Hello World!</h1>
            <form method="POST" action="/click">
                <input type="text" name="enter" placeholder="Enter something" />
                <input type="submit" value="Submit" />
            </form>
        </main>
    </body>
</html>