<?php echo \Asset::css('home/home.css'); ?>
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
