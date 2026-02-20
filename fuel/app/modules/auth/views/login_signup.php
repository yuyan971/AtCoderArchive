<style>
    .auth-container {
        max-width: 420px;
        margin: 3rem auto;
        padding: 2.5rem 2rem;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        border: 1px solid #e9ecef;
    }
    .auth-title {
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: -0.02em;
        margin-bottom: 1.75rem;
        color: #212529;
    }
    .auth-container .form-group {
        margin-bottom: 1.25rem;
    }
    .auth-container .form-group label {
        display: block;
        margin-bottom: 0.35rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #495057;
    }
    .auth-container .form-group input[type="text"],
    .auth-container .form-group input[type="email"],
    .auth-container .form-group input[type="password"] {
        width: 100%;
        padding: 0.6rem 0.75rem;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        font-size: 0.9375rem;
        color: #212529;
        transition: border-color 0.15s, box-shadow 0.15s;
        box-sizing: border-box;
    }
    .auth-container .form-group input::placeholder {
        color: #adb5bd;
    }
    .auth-container .form-group input:focus {
        outline: none;
        border-color: #2d3436;
        box-shadow: 0 0 0 3px rgba(45, 52, 54, 0.08);
    }
    .auth-container .btn-submit {
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
    .auth-container .btn-submit:hover {
        background: #1e2526;
    }
    .auth-container .alert {
        padding: 0.75rem 1rem;
        margin-bottom: 1.25rem;
        border-radius: 6px;
        font-size: 0.875rem;
    }
    .auth-container .alert-error {
        background: #fff5f5;
        color: #c92a2a;
        border: 1px solid #ffd6d6;
    }
    .auth-container .alert-error ul,
    .auth-container .alert-error li {
        color: #c92a2a;
    }
    .auth-container .auth-error-list {
        margin: 0;
        padding-left: 1.5rem;
    }
    .auth-footer {
        margin-top: 1.5rem;
        padding-top: 1.25rem;
        border-top: 1px solid #e9ecef;
        text-align: center;
    }
    .auth-footer a {
        font-size: 0.875rem;
        color: #495057;
        text-decoration: underline;
    }
    .auth-footer a:hover {
        color: #2d3436;
    }
</style>

<div class="auth-container">
    <h1 class="auth-title"><?php echo htmlspecialchars($title); ?></h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert">
            <ul class="auth-error-list">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($action); ?>">
        <?php if ($is_login): ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
        <?php else: ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
            </div>
            <div class="form-group">
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
