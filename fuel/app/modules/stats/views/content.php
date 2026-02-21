<?php echo \Asset::css('stats/stats.css'); ?>
<div class="content">
    <h2 class="content-title center bl">Statistics</h2>
    <div class="stats-container" id="stats-app">
        <div class="tab-navigation flex" data-bind="foreach: tabs">
            <button class="tab-button gray" data-bind="click: $root.selectTab.bind($root, id), css: { active: $root.currentTab() === id }, text: label"></button>
        </div>
        <div class="tab-content" data-bind="css: { active: isByProblemTab() }">
            <div class="by-problem-grid grid" id="by-problem-grid">
                <?php for ($i = 0; $i < 7; $i++): $letter = chr(ord('A') + $i); ?>
                <div class="by-problem-cell flex">
                    <canvas id="chart-by-problem-<?php echo $letter; ?>" width="180" height="180"></canvas>
                    <div class="by-problem-label bl">Problem <?php echo $letter; ?></div>
                    <div class="by-problem-count gray" id="count-by-problem-<?php echo $letter; ?>">0 / 0</div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="tab-content" data-bind="css: { active: isByDifficultyTab() }">
            <div id="by-difficulty-chart-area">
                <div class="by-difficulty-grid grid" id="by-difficulty-grid">
                    <?php
                    $diffOrder = array('gray', 'brown', 'green', 'cyan', 'blue', 'yellow', 'orange', 'red');
                    $diffLabels = array(
                        'gray' => '0 ~ 399', 'brown' => '400 ~ 799', 'green' => '800 ~ 1199', 'cyan' => '1200 ~ 1599',
                        'blue' => '1600 ~ 1999', 'yellow' => '2000 ~ 2399', 'orange' => '2400 ~ 2799', 'red' => '2800 ~'
                    );
                    foreach ($diffOrder as $diff): ?>
                    <div class="by-difficulty-cell flex">
                        <canvas id="chart-by-difficulty-<?php echo $diff; ?>" width="210" height="210"></canvas>
                        <div class="by-difficulty-label bl mt2"><?php echo $diffLabels[$diff]; ?></div>
                        <div class="by-problem-count gray" id="count-by-difficulty-<?php echo $diff; ?>">0 / 0</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="tab-content" data-bind="css: { active: isLanguageTab() }">
            <?php
            $by_lang = isset($stats_data['by_language']) && is_array($stats_data['by_language']) ? $stats_data['by_language'] : array();
            ?>
            <?php if (empty($by_lang)): ?>
            <div class="tab-placeholder gray center">
                <div class="tab-placeholder-desc">No data yet.</div>
            </div>
            <?php else: ?>
            <div class="language-grid grid mt2">
                <?php foreach ($by_lang as $lang => $ac_count): ?>
                <div class="language-cell center">
                    <div class="language-name gray">AC in <?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="language-count bl"><?php echo (int) $ac_count; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Knockout.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.min.js"></script>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    var statsData = <?php echo json_encode(isset($stats_data) ? $stats_data : array(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var COLORS = {
        noSub: 'rgb(88, 97, 106)',
        nonAc: 'rgb(255, 221, 153)',
        ac: 'rgb(50, 205, 50)',
        difficulty: {
            gray: 'rgb(128, 128, 128)',
            brown: 'rgb(128, 64, 0)',
            green: 'rgb(0, 128, 0)',
            cyan: 'rgb(0, 192, 192)',
            blue: 'rgb(0, 0, 255)',
            yellow: 'rgb(192, 192, 0)',
            orange: 'rgb(255, 128, 0)',
            red: 'rgb(255, 0, 0)'
        }
    };
    var chartsByProblem = [];
    var chartsByDifficulty = [];
    var CHART_ANIM_DURATION = 1400;
    var CHART_ANIM_DELAY = 900;
    var CHART_ANIM_EASING = 'easeOutQuad';

    /*
    dataSource - statsData.by_letter または statsData.by_difficulty
    keys - キー配列（例: ['A','B',...] または ['gray','brown',...]）
    idPrefix - ID接頭辞（'problem' または 'difficulty'）
    chartsArray - チャートインスタンスを格納する配列
    opts - { cutout: string, getAcColor: function(key) }
     */
    function drawDoughnutCharts(dataSource, keys, idPrefix, chartsArray, opts) {
        if (!dataSource) return;
        chartsArray.forEach(function(ch) { if (ch) ch.destroy(); });
        chartsArray.length = 0;
        var cutout = (opts && opts.cutout) || '70%';
        var getAcColor = (opts && opts.getAcColor) ? opts.getAcColor : function() { return COLORS.ac; };
        var defaultRow = { total: 0, ac: 0, non_ac: 0, no_sub: 0 };
        keys.forEach(function(key) {
            var d = dataSource[key] || defaultRow;
            var total = d.total || 0, ac = d.ac || 0, nonAc = d.non_ac || 0, noSub = d.no_sub || 0;
            var countEl = document.getElementById('count-by-' + idPrefix + '-' + key);
            if (countEl) countEl.textContent = ac + ' / ' + total;
            var ctx = document.getElementById('chart-by-' + idPrefix + '-' + key);
            if (!ctx) return;
            var labels = [], data = [], colors = [];
            if (noSub > 0) { labels.push('NoSub'); data.push(noSub); colors.push(COLORS.noSub); }
            if (nonAc > 0) { labels.push('Non-AC'); data.push(nonAc); colors.push(COLORS.nonAc); }
            if (ac > 0) { labels.push('AC'); data.push(ac); colors.push(getAcColor(key)); }
            if (data.length === 0) { chartsArray.push(null); return; }
            labels.reverse(); data.reverse(); colors.reverse();
            var chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        hoverBackgroundColor: colors,
                        borderWidth: 1,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    cutout: cutout,
                    rotation: 0,
                    responsive: true,
                    maintainAspectRatio: true,
                    animation: {
                        duration: CHART_ANIM_DURATION,
                        delay: CHART_ANIM_DELAY,
                        easing: CHART_ANIM_EASING
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    var t = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                    var pct = t ? ((ctx.raw / t) * 100).toFixed(1) : 0;
                                    return ctx.label + ': ' + ctx.raw + ' (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
            chartsArray.push(chart);
        });
    }

    var LETTERS = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
    var DIFF_ORDER = ['gray', 'brown', 'green', 'cyan', 'blue', 'yellow', 'orange', 'red'];

    function drawByProblemCharts() {
        drawDoughnutCharts(statsData.by_letter, LETTERS, 'problem', chartsByProblem, { cutout: '70%' });
    }
    function drawByDifficultyCharts() {
        drawDoughnutCharts(statsData.by_difficulty, DIFF_ORDER, 'difficulty', chartsByDifficulty, {
            cutout: '75%',
            getAcColor: function(key) { return COLORS.difficulty[key] || COLORS.ac; }
        });
    }
    function setChartCanvasesVisible(tabName, visible) {
        var v = visible ? '' : 'hidden';
        if (tabName === 'by-problem') {
            var grid = document.getElementById('by-problem-grid');
            if (grid) {
                var canvases = grid.querySelectorAll('canvas');
                for (var i = 0; i < canvases.length; i++) canvases[i].style.visibility = v;
            }
        } else if (tabName === 'by-difficulty') {
            var grid = document.getElementById('by-difficulty-grid');
            if (grid) {
                var canvases = grid.querySelectorAll('canvas');
                for (var i = 0; i < canvases.length; i++) canvases[i].style.visibility = v;
            }
        }
    }
    function replayChartAnimation(tabName) {
        if (tabName === 'by-problem' && chartsByProblem.length) {
            chartsByProblem.forEach(function(ch) {
                if (ch) { ch.stop(); ch.reset(); ch.update(); }
            });
        }
        if (tabName === 'by-difficulty' && chartsByDifficulty.length) {
            chartsByDifficulty.forEach(function(ch) {
                if (ch) { ch.stop(); ch.reset(); ch.update(); }
            });
        }
    }
    var tabSwitchId = 0;
    function StatsViewModel() {
        var self = this;
        self.tabs = ko.observableArray([
            { id: 'by-problem', label: 'By Problem' },
            { id: 'by-difficulty', label: 'By Difficulty' },
            { id: 'language', label: 'Language' }
        ]);
        self.currentTab = ko.observable('by-problem');
        self.isByProblemTab = ko.computed(function() { return self.currentTab() === 'by-problem'; });
        self.isByDifficultyTab = ko.computed(function() { return self.currentTab() === 'by-difficulty'; });
        self.isLanguageTab = ko.computed(function() { return self.currentTab() === 'language'; });
        function runChartTabTransition(tabName, switchId, drawCharts, isFirst) {
            if (isFirst) {
                drawCharts(); // 初回は描画（と必要なら表示）をコールバックに任せる
                return;
            }
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    if (switchId !== tabSwitchId) return;
                    replayChartAnimation(tabName);
                    setTimeout(function() {
                        if (switchId !== tabSwitchId) return;
                        setChartCanvasesVisible(tabName, true);
                    }, CHART_ANIM_DELAY);
                });
            });
        }
        self.selectTab = function(tabName) {
            if (tabName === self.currentTab()) return;
            tabSwitchId++;
            var switchId = tabSwitchId;
            self.currentTab(tabName);
            setChartCanvasesVisible(tabName, false);
            if (tabName === 'by-problem') {
                if (chartsByProblem.length === 0) drawByProblemCharts();
                runChartTabTransition(tabName, switchId, drawByProblemCharts, false);
            } else if (tabName === 'by-difficulty') {
                var isFirst = (chartsByDifficulty.length === 0);
                setTimeout(function() {
                    if (switchId !== tabSwitchId) return;
                    runChartTabTransition(tabName, switchId, function() {
                        drawByDifficultyCharts();
                        setChartCanvasesVisible(tabName, true);
                    }, isFirst);
                }, 120);
            }
        };
    }
    ko.applyBindings(new StatsViewModel(), document.getElementById('stats-app'));
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            drawByProblemCharts();
        });
    } else {
        drawByProblemCharts();
    }
</script>