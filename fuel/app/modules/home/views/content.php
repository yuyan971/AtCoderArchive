<style>
    :root{
        --diff-unknown: rgba(194, 181, 181, 0.75);
    }
    .content {
        padding: 2rem;
        position: relative;
    }
    .content-title {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 1rem;
        color: #2d3436;
        text-align: center;
    }
    .add-contest-form-wrap {
        position: absolute;
        top: 2rem;
        right: 2rem;
    }
    .add-contest-form {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .add-contest-form input[type="text"] {
        padding: 0.5rem 0.75rem;
        border: 1px solid #dfe6e9;
        border-radius: 4px;
        font-size: 0.9rem;
        width: 150px;
        transition: border-color 0.2s;
    }
    .add-contest-form input[type="text"]:focus {
        outline: none;
        border-color: #007bff;
    }
    .add-contest-form button {
        padding: 0.5rem 1rem;
        background: #007bff;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .add-contest-form button:hover {
        background: #0056b3;
    }
    .add-contest-form button:active {
        background: #004085;
    }
    .add-contest-form input.contest-id-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.25);
    }
    .contest-id-notice {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        margin: 0.35rem 0 0;
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        color: #dc3545;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        max-width: 280px;
        z-index: 10;
        white-space: pre-wrap;
    }
    .contest-id-notice.is-visible {
        display: block;
    }
    .contest-circle {
        color: #00f;
    }
    .contest-name {
        color: #007bff;
    }
    .contest-name a {
        color: inherit;
        text-decoration: none;
    }
    /* コンテスト名もセル全体をクリック可能に */
    .contest-name a::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }
    .contest-table {
        width: 80%;
        border-collapse: collapse;
        table-layout: fixed;
        margin: 0 auto;
        /* box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); */
    }
    .contest-table th,
    .contest-table td {
        border: 1px solid #dfe6e9;
        padding: 0.75rem 1rem;
        text-align: left;
        overflow: visible;
        position: relative;
    }
    .contest-table th {
        background: #2d3436;
        color: #fff;
        text-align: left;
    }
    .diff-gray { color: #808080; }
    .diff-brown { color: #804000; }
    .diff-green { color: #008000; }
    .diff-cyan { color: #00c0c0; }
    .diff-blue { color: #0000ff; }
    .diff-yellow { color: #c0c000; }
    .diff-orange { color: #ff8000; }
    .diff-red { color: #ff0000; }

    .state-ac { background-color: #c3e6cb; }
    .state-wa { background-color: #ffeeba; }

    .problem-cell {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.5rem;
        overflow: visible;
    }
    .problem-name {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .diff-circle {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .problem-cell a {
        color: inherit;
        text-decoration: none;
    }
    /* セル全体をクリック可能にする */
    .problem-cell .problem-name a::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }
    
    /* 立体的なボタンスタイル */
    .contest-table td {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), 
                    inset 0 1px 0 rgba(255, 255, 255, 0.8);
        transition: all 0.1s ease;
    }
    /* state がないセルは白背景 */
    .contest-table td:not(.state-ac):not(.state-wa) {
        background-color: #fff;
    }
    .contest-table td:hover {
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        transform: translateY(1px);
        z-index: 50; /* ホバー時にセルを最前面に（ツールチップが隠れないように） */
    }
    .contest-table td:active {
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        transform: translateY(2px);
    }

    /* カスタムツールチップ（diff-circleホバー時のみ表示） */
    .diff-circle {
        position: relative;
        cursor: pointer;
        z-index: 10; /* リンクのオーバーレイより上に配置 */
    }
    .diff-circle::after {
        content: attr(data-diff);
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-top: 6px;
        padding: 4px 8px;
        background: #2d3436;
        color: #fff;
        font-size: 12px;
        border-radius: 4px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.1s ease;
        pointer-events: none;
        z-index: 100;
    }
    .diff-circle::before {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-top: -4px;
        border: 5px solid transparent;
        border-bottom-color: #2d3436;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.1s ease;
        pointer-events: none;
        z-index: 100;
    }
    .diff-circle:hover::after,
    .diff-circle:hover::before {
        opacity: 1;
        visibility: visible;
    }
    /* diff が -1（不明）のとき円内に ? を表示 */
    .diff-circle.diff-unknown {
        font-size: 7px;
        line-height: 10px;
        font-weight: bold;
        color:var(--diff-unknown);
        text-align: center;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    /* diff が不明なセルは問題名の文字色を薄く */
    .contest-table td.diff-unknown-cell .problem-name,
    .contest-table td.diff-unknown-cell .problem-name a {
        color:var(--diff-unknown);
    }
    /* ローディングインジケーター */
    .loading-container {
        text-align: center;
        padding: 3rem;
        color: #636e72;
    }
    .loading-spinner {
        display: inline-block;
        width: 40px;
        height: 40px;
        border: 4px solid #dfe6e9;
        border-top-color:rgb(0, 0, 0);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 1rem;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    .loading-text {
        font-size: 1rem;
        margin-top: 0.5rem;
    }
    .error-message {
        text-align: center;
        padding: 2rem;
        color: #dc3545;
        background: #fff5f5;
        border: 1px solid #ffcccc;
        border-radius: 4px;
        margin: 1rem auto;
        max-width: 600px;
    }
</style>

<div class="content">
    <div class="add-contest-form-wrap">
        <form class="add-contest-form" method="POST" action="/home/import">
            <input type="text" name="contest_id" placeholder="Contest ID here" required>
            <button type="submit">fetch</button>
        </form>
        <p class="contest-id-notice" id="contest-id-notice" aria-live="polite"></p>
    </div>
    <script>
    (function () {
        var form = document.querySelector('.add-contest-form');
        var input = form && form.querySelector('input[name="contest_id"]');
        var notice = document.getElementById('contest-id-notice');
        if (!form || !input) return;

        var noticeText = "format examples : \n'abc123', 'abc220', '123', '220'";

        function showInvalid() {
            input.classList.add('contest-id-invalid');
            if (notice) {
                notice.textContent = noticeText;
                notice.classList.add('is-visible');
            }
        }

        form.addEventListener('submit', function (e) {
            var v = (input.value || '').trim();
            if (!/^(abc[0-9]{3}|[0-9]{3})$/i.test(v)) {
                e.preventDefault();
                showInvalid();
                input.focus();
                return false;
            }
        });

        input.addEventListener('input', function () {
            input.classList.remove('contest-id-invalid');
            if (notice && notice.classList.contains('is-visible')) {
                notice.classList.remove('is-visible');
                notice.textContent = '';
            }
        });
    })();
    </script>
    <h2 class="content-title">AtCoder Beginner Contest</h2>
    <div id="error-message" class="error-message" style="display: none;"></div>
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
                <td colspan="8" class="loading-container">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Now Loading...</div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<script>
(function() { // 非同期でコンテストデータを取得してテーブルを更新
    var tbody = document.getElementById('contest-table-body');
    var errorMessage = document.getElementById('error-message');
    
    function showError(message) {
        errorMessage.textContent = 'Error: ' + message;
        errorMessage.style.display = 'block';
        tbody.innerHTML = '';
    }
    
    function hideError() {
        errorMessage.style.display = 'none';
    }
    
    function renderTable(contests) {
        if (!Array.isArray(contests) || contests.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 2rem; color: #636e72;">データがありません</td></tr>';
            return;
        }
        
        var html = '';
        var problemLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        
        contests.forEach(function(contest) {
            if (contest._error) return;
            var contestUrl = contest.contest_url || '#';
            html += '<tr>';
            html += '<td class="contest-name">';
            html += '<span class="contest-circle">◉</span>';
            html += '<a href="' + contestUrl + '" target="_blank" rel="noopener noreferrer">' + (contest.name || '') + '</a>';
            html += '</td>';
            
            problemLetters.forEach(function(letter) {
                var problem = contest[letter] || {};
                var problemName = problem.name || '';
                var problemUrl = problem.problem_url || '#';
                var diffClass = problem.diff_class || 'diff-gray';
                var stateClass = problem.state_class || '';
                var diffCircleStyle = problem.diff_circle_style || '';
                var diffDisplay = problem.diff_display !== undefined ? problem.diff_display : '?';
                var isUnknown = diffDisplay === '?';
                var unknownClass = isUnknown ? ' diff-unknown-cell' : '';
                var diffCircleClass = isUnknown ? ' diff-unknown' : '';
                
                html += '<td class="' + diffClass + ' ' + stateClass + unknownClass + '">';
                html += '<div class="problem-cell">';
                html += '<span class="diff-circle' + diffCircleClass + '" data-diff="Diff: ' + diffDisplay + '" style="' + diffCircleStyle + '">' + (isUnknown ? '?' : '') + '</span>';
                html += '<span class="problem-name">';
                html += '<a href="' + problemUrl + '" target="_blank" rel="noopener noreferrer">' + problemName + '</a>';
                html += '</span>';
                html += '</div>';
                html += '</td>';
            });
            
            html += '</tr>';
        });
        
        tbody.innerHTML = html;
    }
    
    fetch('/home/get_contests')
        .then(function(response) {
            if (!response.ok) {
                throw new Error('HTTPエラー: ' + response.status);
            }
            return response.json();
        })
        .then(function(data) {
            hideError();
            renderTable(data);
        })
        .catch(function(error) {
            showError('Failed to load data. Please refresh the page.');
        });
})();
</script>
