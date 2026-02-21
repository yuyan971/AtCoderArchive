<?php echo \Asset::css('home/home.css'); ?>
<?php echo \Asset::js('home/home.js'); ?>
<div class="content">
    <div class="add-contest-form-wrap">
        <form class="add-contest-form" method="POST" action="/home/import">
            <input type="hidden" name="<?php echo Config::get('security.csrf_token_key'); ?>" value="<?php echo \Security::fetch_token(); ?>">
            <input type="text" name="contest_id" placeholder="Contest ID here" required>
            <button type="submit">fetch</button>
        </form>
        <p class="contest-id-notice" id="contest-id-notice" aria-live="polite"></p>
    </div>
    <h2 class="content-title center bl">AtCoder Beginner Contest</h2>
    <div id="error-message" class="error-message center" style="display: none;"></div>
    <table class="contest-table">
        <thead>
            <tr>
                <th>Contest</th>
                <th>A</th>
                <th>B</th>
                <th>C</th>
                <th>D</th>
                <th>E</th>
                <th>F</th>
                <th>G</th>
            </tr>
        </thead>
        <tbody id="contest-table-body">
            <tr>
                <td colspan="8" class="loading-container center">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Now Loading...</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
