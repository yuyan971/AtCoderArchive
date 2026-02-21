(function() {
    'use strict';

    var statsData = window.statsData || {};
    var ko = window.ko;
    var Chart = window.Chart;
    if (!ko || !Chart) return;

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
    function setChartCanvasesVisible(tabName, visible) { // trueのとき円グラフを表示 falseのとき非表示
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
        function runChartTabTransition(tabName, switchId) {
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    if (switchId !== tabSwitchId) return;
                    replayChartAnimation(tabName); // アニメーションを再再生
                    setTimeout(function() {
                        if (switchId !== tabSwitchId) return;
                        setChartCanvasesVisible(tabName, true); // 円グラフを表示
                    }, CHART_ANIM_DELAY); // 期待感をもたせるための遅延演出
                });
            });
        }
        self.selectTab = function(tabName) {
            if (tabName === self.currentTab()) return;
            tabSwitchId++;
            var switchId = tabSwitchId; // タブを切り替えた回数で一意のIDを生成
            self.currentTab(tabName); // 監視中の値の更新
            setChartCanvasesVisible(tabName, false); // 円グラフを非表示
            if (tabName === 'by-problem') {
                if (chartsByProblem.length === 0) drawByProblemCharts(); // init()で実行してるため， ここが実行されることはないはずだが， 念の為
                runChartTabTransition(tabName, switchId);
            } else if (tabName === 'by-difficulty') {
                if (chartsByDifficulty.length === 0) drawByDifficultyCharts();
                runChartTabTransition(tabName, switchId);
            }
        };
    }

    function init() {
        var appEl = document.getElementById('stats-app');
        if (!appEl) return;
        ko.applyBindings(new StatsViewModel(), appEl);
        drawByProblemCharts();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
