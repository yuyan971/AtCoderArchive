<style>
    .settings-container {
        max-width: 420px;
        margin: 3rem auto;
        padding: 2.5rem 2rem;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        border: 1px solid #e9ecef;
    }
    .settings-title {
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: -0.02em;
        margin-bottom: 1.75rem;
        color: #212529;
    }
    .form-group {
        margin-bottom: 1.25rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.35rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #495057;
    }
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="password"] {
        width: 100%;
        padding: 0.6rem 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-size: 0.9375rem;
        color: #212529;
        transition: border-color 0.15s, box-shadow 0.15s;
        box-sizing: border-box;
    }
    .form-group input::placeholder {
        color: #adb5bd;
    }
    .form-group input[type="text"]:focus,
    .form-group input[type="email"]:focus,
    .form-group input[type="password"]:focus {
        outline: none;
        border-color: #2d3436;
        box-shadow: 0 0 0 3px rgba(45, 52, 54, 0.08);
    }
    .btn-submit {
        display: block;
        width: 100%;
        margin-top: 0.5rem;
        padding: 0.65rem 1rem;
        background: #2d3436;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 0.9375rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-submit:hover {
        background: #1e2526;
    }
    .alert {
        padding: 0.75rem 1rem;
        margin-bottom: 1.25rem;
        border-radius: 6px;
        font-size: 0.875rem;
    }
    .alert-error {
        background: #fff5f5;
        color: #c92a2a;
        border: 1px solid #ffd6d6;
    }
    .alert-success {
        background: #f0f9f4;
        color: #0f5132;
        border: 1px solid #badbcc;
    }
    .password-section {
        margin-bottom: 1.25rem;
    }
    .password-toggle {
        font-size: 0.875rem;
        color: #495057;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        text-decoration: underline;
    }
    .password-toggle:hover {
        color: #2d3436;
    }
    .password-fields {
        display: none;
        margin-top: 1rem;
    }
    .password-section.is-open .password-fields {
        display: block;
    }
    .settings-footer {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        width: 100%;
        box-sizing: border-box;
    }
    .settings-footer .footer-left,
    .settings-footer .footer-right {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 1.5em;
    }
    .settings-footer .footer-left form,
    .settings-footer .footer-right form {
        margin: 0;
    }
    .settings-footer .sep {
        flex: 0 0 auto;
        color: #adb5bd;
        font-size: 0.875rem;
        line-height: 1.5;
        display: inline-flex;
        align-items: center;
    }
    .settings-footer .btn-logout,
    .settings-footer .btn-leave {
        font-size: 0.875rem;
        line-height: 1.5;
        color: #868e96;
        background: none;
        border: none;
        margin: 0;
        padding: 0;
        cursor: pointer;
        text-decoration: underline;
        -webkit-appearance: none;
        appearance: none;
        display: inline-flex;
        align-items: center;
    }
    .settings-footer .btn-logout:hover {
        color: #212529;
    }
    .settings-footer .btn-leave:hover {
        color: #c92a2a;
    }
</style>

<div class="settings-container">
    <h1 class="settings-title">Settings</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success && empty($errors)): ?>
        <div class="alert alert-success">
            Done. Your changes are saved.
        </div>
    <?php endif; ?>

    <form method="POST" action="/auth/update">
        <div class="form-group">
            <label for="atcoder_username">AtCoder User ID</label>
            <input type="text" id="atcoder_username" name="atcoder_username" value="<?php echo htmlspecialchars($atcoder_username ?: ''); ?>" placeholder="AtCoder username">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="form-group">
            <label for="current_password">Password</label>
            <input type="password" id="current_password" name="current_password" placeholder="Required to update settings">
        </div>
        <div class="form-group password-section" id="password-section">
            <button type="button" class="password-toggle" id="password-toggle" aria-expanded="false">Change Password</button>
            <div class="password-fields">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Only when changing password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Only when changing password">
                </div>
            </div>
        </div>
        <button type="submit" class="btn-submit">更新</button>
    </form>
    <div class="settings-footer">
        <div class="footer-left">
            <form method="POST" action="/auth/logout">
                <button type="submit" class="btn-logout">ログアウト</button>
            </form>
        </div>
        <span class="sep">|</span>
        <div class="footer-right">
            <form method="POST" action="/auth/leave" id="leave-form">
                <button type="button" class="btn-leave" id="btn-leave">退会</button>
            </form>
        </div>
    </div>
</div>
<script>
(function() {
    var section = document.getElementById('password-section');
    var toggle = document.getElementById('password-toggle');
    if (!section || !toggle) return;
    toggle.addEventListener('click', function() {
        var isOpen = section.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.textContent = isOpen ? 'Close' : 'Change Password';
    });
})();
(function() {
    var btn = document.getElementById('btn-leave');
    var form = document.getElementById('leave-form');
    if (!btn || !form) return;
    btn.addEventListener('click', function() {
        if (window.confirm('本当に退会しますか？この操作は取り消せません。')) {
            form.submit();
        }
    });
})();
</script>
