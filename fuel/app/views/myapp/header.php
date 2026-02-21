<style>
    body {
        margin: 0;
        padding: 0;
    }
    .header {
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 2rem;
        background: linear-gradient(to bottom, #2d3436 0%, #1e2526 100%);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        position: relative;
    }
    .header-logo {
        font-weight: bold;
        font-size: 1.2rem;
        flex: 1;
    }
    .header-nav {
        gap: 2rem;
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }
    .header-auth {
        gap: 1rem;
        flex: 1;
        justify-content: flex-end;
    }
    .nav-link {
        padding: 0.75rem 1rem;
        transition: background 0.2s;
    }
    .nav-link:hover {
        background: #636e72;
    }
    .btn-settings {
        padding: 0.5rem 1rem;
        border: 1px solid #fff;
        transition: all 0.2s;
    }
    .btn-settings:hover {
        background: #fff;
        color: #2d3436;
    }
</style>

<?php
    $currentPath = \Uri::segment(1);
    $currentAction = \Uri::segment(2);
    $headerLogoOnly = ($currentPath === 'auth' && in_array($currentAction, array('login', 'register'), true)); // auth/login or auth/register の場合はロゴのみ表示
?>
<header class="header wh flex">
    <div class="header-logo red-co">AtCoder Archive</div>
    <nav class="header-nav flex <?= $headerLogoOnly ? 'hidden' : '' ?>">
        <a href="/home" class="nav-link no-deco round wh <?= $currentPath === 'home' ? 'bg-emph' : '' ?>">Home</a>
        <a href="/stats" class="nav-link no-deco round wh <?= $currentPath === 'stats' ? 'bg-emph' : '' ?>">Stats</a>
    </nav>
    <div class="header-auth flex <?= $headerLogoOnly ? 'hidden' : '' ?>">
        <a href="/auth/settings" class="btn-settings no-deco round wh <?= $currentPath === 'auth' ? 'hidden' : '' ?>">Settings</a>
    </div>
</header>
