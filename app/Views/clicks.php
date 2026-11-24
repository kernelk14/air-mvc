<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Submitted | Air MVC</title>
        <link rel="stylesheet" href="<?= e(asset('pico/css/pico.min.css')) ?>" />
    </head>
    <body>
        <main class="container">
            <nav>
                <ul><li><strong>Air MVC</strong></li></ul>
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/login">Login</a></li>
                    <li><a href="/user/1">Profile</a></li>
                </ul>
            </nav>

            <article>
                <header>
                    <hgroup>
                        <h1>Request received</h1>
                        <p>Your message was passed through the controller and rendered safely.</p>
                    </hgroup>
                </header>
                <p><mark><?= e($message ?? '') ?></mark></p>
                <footer><a href="/" role="button" class="secondary">Back home</a></footer>
            </article>
        </main>
    </body>
</html>