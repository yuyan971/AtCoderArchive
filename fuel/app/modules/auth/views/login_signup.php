<?php echo \Asset::css('auth/auth.css'); ?>
<div class="auth-container">
    <h1 class="auth-title"><?php echo htmlspecialchars($title); ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error mb125" role="alert">
            <ul class="auth-error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($action); ?>">
        <?php if ($is_login): ?>
            <div class="form-group mb125">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required autocomplete="email">
            </div>
            <div class="form-group mb125">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
        <?php else: ?>
            <div class="form-group mb125">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required autocomplete="email">
            </div>
            <div class="form-group mb125">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
            </div>
            <div class="form-group mb125">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
            </div>
            <div class="form-group mb125">
                <label for="atcoder_username">AtCoder Username</label>
                <input type="text" id="atcoder_username" name="atcoder_username" value="<?php echo isset($atcoder_username) ? htmlspecialchars($atcoder_username) : ''; ?>" required autocomplete="atcoder_username">
            </div>
        <?php endif; ?>
        <button type="submit" class="btn-submit"><?php echo htmlspecialchars($submit_label); ?></button>
    </form>

    <div class="auth-footer">
        <a href="<?php echo htmlspecialchars($switch_url); ?>"><?php echo htmlspecialchars($switch_label); ?></a>
    </div>
</div>
