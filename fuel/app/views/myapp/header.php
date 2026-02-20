<style>
    body {
        margin: 0;
        padding: 0;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 2rem;
        background: linear-gradient(to bottom, #2d3436 0%, #1e2526 100%);
        color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        position: relative;
    }
    .header-logo {
        font-weight: bold;
        font-size: 1.2rem;
        flex: 1;
    }
    .header-nav {
        display: flex;
        gap: 2rem;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }
    .header-auth {
        display: flex;
        gap: 1rem;
        flex: 1;
        justify-content: flex-end;
    }
    .nav-link {
        color: #fff;
        text-decoration: none;
        padding: 0.75rem 1rem;
        border-radius: 3px;
        transition: background 0.2s;
    }
    .nav-link:hover {
        background: #636e72;
    }
    .nav-link.active {
        background: rgba(255, 255, 255, 0.04);
    }
    .btn-settings {
        color: #fff;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border: 1px solid #fff;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .btn-settings:hover {
        background: #fff;
        color: #2d3436;
    }
    .btn-settings--hidden {
        visibility: hidden;
        pointer-events: none;
    }
    .header.header--logo-only .header-nav,
    .header.header--logo-only .header-auth {
        visibility: hidden;
        pointer-events: none;
    }
</style>

<?php
    $currentPath = \Uri::segment(1);
    $currentAction = \Uri::segment(2);
    $headerLogoOnly = ($currentPath === 'auth' && in_array($currentAction, array('login', 'register'), true)); // auth/login or auth/register の場合はロゴのみ表示
?>
<header class="header <?= $headerLogoOnly ? 'header--logo-only' : '' ?>">
    <div class="header-logo">AtCoder Archive</div>
    <nav class="header-nav">
        <a href="/home" class="nav-link <?= $currentPath === 'home' ? 'active' : '' ?>">Home</a>
        <a href="/stats" class="nav-link <?= $currentPath === 'stats' ? 'active' : '' ?>">Stats</a>
    </nav>
    <div class="header-auth">
        <a href="/auth/settings" class="btn-settings <?= $currentPath === 'auth' ? 'btn-settings--hidden' : '' ?>">Settings</a>
    </div>
</header>
