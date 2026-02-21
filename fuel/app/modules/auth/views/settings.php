<?php echo \Asset::css('auth/auth.css'); ?>
<?php echo \Asset::js('auth/settings.js'); ?>
<div class="settings-container">
    <h1 class="settings-title">Settings</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error mb125">
            <ul style="margin: 0; padding-left: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success && empty($errors)): ?>
        <div class="alert alert-success mb125">
            Done. Your changes are saved.
        </div>
    <?php endif; ?>

    <form method="POST" action="/auth/update">
        <div class="form-group mb125">
            <label for="atcoder_username">AtCoder User ID</label>
            <input type="text" id="atcoder_username" name="atcoder_username" value="<?php echo htmlspecialchars($atcoder_username ?: ''); ?>" placeholder="AtCoder username">
        </div>
        <div class="form-group mb125">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="form-group mb125">
            <label for="current_password">Password</label>
            <input type="password" id="current_password" name="current_password" placeholder="Required to update settings">
        </div>
        <div class="form-group password-section mb125" id="password-section">
            <button type="button" class="password-toggle" id="password-toggle" aria-expanded="false">Change Password</button>
            <div class="password-fields">
                <div class="form-group mb125">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Only when changing password">
                </div>
                <div class="form-group mb125">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Only when changing password">
                </div>
            </div>
        </div>
        <button type="submit" class="btn-submit">更新</button>
    </form>
    <div class="settings-footer flex">
        <div class="footer-left flex">
            <form method="POST" action="/auth/logout">
                <button type="submit" class="btn-logout">ログアウト</button>
            </form>
        </div>
        <span class="sep">|</span>
        <div class="footer-right flex">
            <form method="POST" action="/auth/leave" id="leave-form">
                <button type="button" class="btn-leave" id="btn-leave">退会</button>
            </form>
        </div>
    </div>
</div>
