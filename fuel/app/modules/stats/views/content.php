<?php echo \Asset::css('stats/stats.css'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/knockout/3.5.1/knockout-latest.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>window.statsData = <?php echo json_encode(isset($stats_data) ? $stats_data : array(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
<?php echo \Asset::js('stats/stats.js'); ?>
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
