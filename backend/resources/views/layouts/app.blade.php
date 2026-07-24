<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StackFlow Manager — @yield('title')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #121212;
            color: #e0e0e0;
            line-height: 1.7;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            border-bottom: 1px solid #2e2e2e;
            padding: 1.5rem 2rem;
        }
        header h1 {
            color: #fe2c55;
            font-size: 1.25rem;
            font-weight: 700;
        }
        main {
            flex: 1;
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 2rem;
            width: 100%;
        }
        h2 {
            color: #fff;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .meta {
            color: #888;
            font-size: 0.85rem;
            margin-bottom: 2.5rem;
        }
        section { margin-bottom: 2rem; }
        h3 {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        p { margin-bottom: 1rem; }
        ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 1rem;
        }
        ul li::before {
            content: "— ";
            color: #fe2c55;
        }
        li { margin-bottom: 0.5rem; }
        footer {
            border-top: 1px solid #2e2e2e;
            padding: 1.5rem 2rem;
            text-align: center;
            font-size: 0.8rem;
            color: #555;
        }
        a { color: #25f4ee; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <header>
        <h1>StackFlow Manager</h1>
    </header>
    <main>
        <h2>@yield('title')</h2>
        <p class="meta">Last updated: July 13, 2026</p>
        @yield('content')
    </main>
    <footer>
        &copy; {{ date('Y') }} StackFlow Manager. All rights reserved.
    </footer>
</body>
</html>
