<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title ?? 'Air MVC') ?></title>
        <link rel="stylesheet" href="<?= e(asset('pico/css/pico.min.css')) ?>" />
    </head>
    <body>
        <main class="container">
            <nav>
                <ul>
                    <li><strong>Air MVC</strong></li>
                </ul>
                <ul>
                    <li><a href="/" aria-current="page">Home</a></li>
                    <li><a href="/login">Login</a></li>
                    <li><a href="/user/1">Profile</a></li>
                </ul>
            </nav>

            <header>
                <p><small>PHP Framework v2</small></p>
                <hgroup>
                    <h1>Build fast. Stay light.</h1>
                    <p>Air MVC is a lean, extensible PHP framework with clean routing, middleware, and a modern core&mdash;ready for XAMPP or Composer workflows.</p>
                </hgroup>
            </header>

            <div class="grid">
                <article>
                    <header><strong>Try the request flow</strong></header>
                    <p>Submit a value through the POST route and see it rendered by the controller.</p>
                    <form method="POST" action="/click">
                        <label for="enter">Message</label>
                        <input type="text" id="enter" name="enter" placeholder="Type something..." autocomplete="off" />
                        <button type="submit">Send Request</button>
                    </form>
                </article>
                <article>
                    <header><strong>Core features</strong></header>
                    <ul>
                        <li><strong>PSR-4:</strong> Namespaced <code>Air\</code> core and <code>App\</code> skeleton</li>
                        <li><strong>HTTP:</strong> Routing, request/response, middleware pipeline</li>
                        <li><strong>Ready:</strong> CLI, tests, and XAMPP-friendly entry point</li>
                    </ul>
                </article>
            </div>

            <footer><small>Air MVC &middot; Lightweight PHP framework foundation</small></footer>
        </main>
    </body>
</html>