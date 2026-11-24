<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>User <?= e($id ?? '') ?> | Air MVC</title>
        <link rel="stylesheet" href="<?= e(asset('pico/css/pico.min.css')) ?>" />
    </head>
    <body>
        <main class="container">
            <nav>
                <ul><li><strong>Air MVC</strong></li></ul>
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/login">Login</a></li>
                    <li><a href="/user/<?= e($id ?? '1') ?>" aria-current="page">Profile</a></li>
                </ul>
            </nav>

            <article>
                <header>
                    <hgroup>
                        <h1>User profile</h1>
                        <p>Route parameters are captured by the router and passed to your controller.</p>
                    </hgroup>
                </header>
                <p><strong>ID:</strong> <?= e($id ?? '') ?></p>
                <footer><a href="/" role="button" class="secondary">Back home</a></footer>
            </article>
        </main>
    </body>
</html>