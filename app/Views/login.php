<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title ?? 'Login') ?></title>
        <link rel="stylesheet" href="<?= e(asset('pico/css/pico.min.css')) ?>" />
    </head>
    <body>
        <main class="container">
            <nav>
                <ul><li><strong>Air MVC</strong></li></ul>
                <ul>
                    <li><a href="/">Home</a></li>
                    <li><a href="/login" aria-current="page">Login</a></li>
                    <li><a href="/user/1">Profile</a></li>
                </ul>
            </nav>

            <article>
                <header>
                    <hgroup>
                        <h1>Welcome back</h1>
                        <p>This demo route is ready for your auth flow in the next milestone.</p>
                    </hgroup>
                </header>
                <form>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" />
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Password" autocomplete="current-password" />
                    <button type="submit">Sign in</button>
                </form>
            </article>
        </main>
    </body>
</html>