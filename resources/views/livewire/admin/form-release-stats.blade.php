@assets
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
// Shared floating tooltip element (created once, reused across charts)
function _getFrsTooltip() {
    let el = document.getElementById('frs-global-tooltip');
    if (!el) {
        el = document.createElement('div');
        el.id = 'frs-global-tooltip';
        el.className = 'frs-chart-tooltip';
        el.style.display = 'none';
        document.body.appendChild(el);
    }
    return el;
}

function _makeTooltipHandler(opts) {
    return function({ chart, tooltip }) {
        const el = _getFrsTooltip();

        if (tooltip.opacity === 0) {
            el.style.display = 'none';
            return;
        }

        const idx = tooltip.dataPoints[0].dataIndex;
        const opt = opts[idx];

        // Header: option label + count
        let html = `<div class="frs-tt-head">
            <span class="frs-tt-opt-label">${opt.label}</span>
            <span class="frs-tt-stat">${opt.count}&nbsp;<span class="frs-tt-pct">(${opt.pct}%)</span></span>
        </div>`;

        if (opt.respondents && opt.respondents.length > 0) {
            html += '<div class="frs-tt-divider"></div><div class="frs-tt-names">';
            opt.respondents.forEach(name => {
                const initial = (name || '?').charAt(0).toUpperCase();
                html += `<div class="frs-tt-name-row">
                    <span class="frs-tt-avatar">${initial}</span>
                    <span class="frs-tt-name-text">${name}</span>
                </div>`;
            });
            html += '</div>';
        } else {
            html += '<div class="frs-tt-divider"></div><div class="frs-tt-empty">No responses</div>';
        }

        el.innerHTML = html;
        el.style.display = 'block';

        // Position near cursor using canvas viewport coords
        const rect = chart.canvas.getBoundingClientRect();
        const tipW = el.offsetWidth;
        const tipH = el.offsetHeight;
        let left = rect.left + tooltip.caretX + 14;
        let top  = rect.top  + tooltip.caretY - tipH / 2;

        // Keep within viewport
        if (left + tipW > window.innerWidth - 8)  left = rect.left + tooltip.caretX - tipW - 10;
        if (top < 8)                               top  = 8;
        if (top + tipH > window.innerHeight - 8)  top  = window.innerHeight - tipH - 8;

        el.style.left = left + 'px';
        el.style.top  = top  + 'px';
    };
}

function frsQuestion(data) {
    return {
        showUnused: false,
        chart: null,
        data: data,
        buildChart() {
            if (this.chart) { this.chart.destroy(); this.chart = null; }
            const canvas = this.$refs.canvas;
            if (!canvas || typeof Chart === 'undefined') return;
            const opts = this.data.options;

            // Hide tooltip when mouse leaves chart area
            canvas.addEventListener('mouseleave', () => {
                const el = _getFrsTooltip();
                el.style.display = 'none';
            });

            this.chart = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: opts.map(o => o.label),
                    datasets: [{
                        data: opts.map(o => o.count),
                        backgroundColor: opts.map(o =>
                            o.unused ? 'rgba(239,68,68,0.15)' : 'rgba(99,102,241,0.75)'
                        ),
                        borderColor: opts.map(o =>
                            o.unused ? 'rgba(239,68,68,0.7)' : 'rgba(99,102,241,1)'
                        ),
                        borderWidth: 1,
                        borderRadius: 3,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: false,
                            external: _makeTooltipHandler(opts),
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, precision: 0, font: { size: 11 } },
                            grid: { color: 'rgba(0,0,0,0.05)' },
                        },
                        y: {
                            ticks: { font: { size: 11 } },
                            grid: { display: false },
                        },
                    },
                },
            });
        },
    };
}
</script>
<style>
/* Floating chart tooltip */
.frs-chart-tooltip {
    position: fixed;
    z-index: 9999;
    background: #1e293b;
    color: #f8fafc;
    border-radius: .5rem;
    box-shadow: 0 8px 24px rgba(0,0,0,.25);
    min-width: 180px;
    max-width: 260px;
    pointer-events: none;
    font-size: .75rem;
    overflow: hidden;
}
.frs-tt-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .5rem;
    padding: .5rem .75rem;
}
.frs-tt-opt-label {
    font-weight: 600;
    color: #f1f5f9;
    flex: 1;
    line-height: 1.3;
    word-break: break-word;
}
.frs-tt-stat {
    flex-shrink: 0;
    font-weight: 700;
    color: #fff;
    white-space: nowrap;
}
.frs-tt-pct {
    font-weight: 400;
    color: #94a3b8;
}
.frs-tt-divider {
    height: 1px;
    background: rgba(255,255,255,.1);
}
.frs-tt-names {
    padding: .375rem .5rem;
    max-height: 180px;
    overflow-y: auto;
}
.frs-tt-name-row {
    display: flex;
    align-items: center;
    gap: .375rem;
    padding: .2rem .25rem;
    border-radius: .25rem;
}
.frs-tt-name-row:hover {
    background: rgba(255,255,255,.07);
}
.frs-tt-avatar {
    flex-shrink: 0;
    width: 1.25rem;
    height: 1.25rem;
    border-radius: 9999px;
    background: rgba(99,102,241,.4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .5625rem;
    font-weight: 700;
    color: #c7d2fe;
}
.frs-tt-name-text {
    font-size: .6875rem;
    color: #cbd5e1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.frs-tt-empty {
    padding: .375rem .75rem .5rem;
    font-size: .6875rem;
    color: #64748b;
    font-style: italic;
}
</style>
@endassets
<style>
.frs-wrap { padding: .875rem 1.5rem 1rem; }
.frs-loading { padding: .5rem 0 1rem; }
.frs-skeleton { background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size: 200% 100%; animation: frs-shimmer 1.4s infinite; border-radius: .375rem; }
@keyframes frs-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* Summary cards */
.frs-summary { display:flex; gap:.625rem; margin-bottom:1rem; flex-wrap:wrap; }
.frs-summary-card { flex:1; min-width:7rem; background:#f8fafc; border:1px solid #e2e8f0; border-radius:.5rem; padding:.5rem .75rem; }
.frs-summary-num { display:block; font-size:1.125rem; font-weight:700; color:#1e293b; line-height:1.2; }
.frs-summary-num.frs-warn { color:#dc2626; }
.frs-summary-lbl { display:block; font-size:.6875rem; color:#94a3b8; margin-top:.125rem; }

/* Question sections */
.frs-q-section { border:1px solid #f1f5f9; border-radius:.5rem; margin-bottom:.625rem; overflow:hidden; background:#fff; }
.frs-q-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.5rem .875rem; background:#f8fafc; border-bottom:1px solid #f1f5f9; flex-wrap:wrap; }
.frs-q-meta { display:flex; align-items:center; gap:.5rem; min-width:0; }
.frs-q-num { font-size:.6875rem; font-weight:700; color:#94a3b8; flex-shrink:0; }
.frs-q-label { font-size:.8125rem; font-weight:500; color:#1e293b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:30rem; }
.frs-q-type { font-size:.625rem; font-weight:600; text-transform:uppercase; letter-spacing:.05em; color:#64748b; background:#e2e8f0; border-radius:.25rem; padding:.1rem .375rem; flex-shrink:0; }
.frs-q-head-right { display:flex; align-items:center; gap:.5rem; flex-shrink:0; }
.frs-answered { font-size:.75rem; color:#64748b; }
.frs-unused-badge { font-size:.6875rem; font-weight:600; color:#b45309; background:#fef3c7; border:1px solid #fde68a; border-radius:9999px; padding:.1rem .5rem; white-space:nowrap; }

/* Chart area */
.frs-chart-wrap { padding:.75rem .875rem; }
.frs-chart-canvas-wrap { position:relative; }

/* "Show unused" toggle */
.frs-toggle-btn { display:inline-flex; align-items:center; gap:.25rem; margin-top:.5rem; font-size:.6875rem; font-weight:600; color:#6366f1; background:none; border:1px solid #e0e7ff; border-radius:.375rem; padding:.25rem .625rem; cursor:pointer; transition:background .15s,border-color .15s; }
.frs-toggle-btn:hover { background:#eef2ff; border-color:#c7d2fe; }

/* Unused list panel */
.frs-unused-panel { padding:.75rem .875rem; }
.frs-unused-title { font-size:.6875rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:.5rem; }
.frs-unused-item { display:flex; align-items:center; gap:.5rem; padding:.3rem 0; font-size:.8125rem; color:#374151; }
.frs-unused-dot { flex-shrink:0; width:.5rem; height:.5rem; border-radius:9999px; background:#ef4444; }

/* Non-choice question: count bar */
.frs-count-row { padding:.625rem .875rem; display:flex; align-items:center; gap:.75rem; }
.frs-count-track { flex:1; max-width:16rem; height:.375rem; background:#f1f5f9; border-radius:9999px; overflow:hidden; }
.frs-count-fill { height:.375rem; background:#6366f1; border-radius:9999px; transition:width .4s; }
.frs-count-lbl { font-size:.75rem; color:#64748b; }
</style>
@endassets

<div wire:init="loadStats">
    @if (!$loaded)
        {{-- Skeleton --}}
        <div class="frs-wrap frs-loading">
            <div class="frs-skeleton" style="width:55%;height:.625rem;margin-bottom:.375rem"></div>
            <div class="frs-skeleton" style="width:35%;height:.625rem;margin-bottom:1rem"></div>
            <div class="frs-skeleton" style="width:100%;height:3rem;margin-bottom:.5rem"></div>
            <div class="frs-skeleton" style="width:100%;height:3rem"></div>
        </div>
    @else
        <div class="frs-wrap">

            {{-- Summary row --}}
            @php
                $totalUnused = array_sum(array_column(array_filter($stats, fn ($s) => $s['options'] !== null), 'unused_count'));
                $completionPct = $total > 0 ? (int) round($totalSubmitted / $total * 100) : 0;
            @endphp
            <div class="frs-summary">
                <div class="frs-summary-card">
                    <span class="frs-summary-num">{{ $totalSubmitted }}/{{ $total }}</span>
                    <span class="frs-summary-lbl">Submitted</span>
                </div>
                <div class="frs-summary-card">
                    <span class="frs-summary-num">{{ $completionPct }}%</span>
                    <span class="frs-summary-lbl">Completion rate</span>
                </div>
                <div class="frs-summary-card">
                    <span class="frs-summary-num {{ $totalUnused > 0 ? 'frs-warn' : '' }}">{{ $totalUnused }}</span>
                    <span class="frs-summary-lbl">Unused options total</span>
                </div>
            </div>

            {{-- Per-question sections --}}
            @foreach ($stats as $stat)
                <div class="frs-q-section">

                    {{-- Header --}}
                    <div class="frs-q-head">
                        <div class="frs-q-meta">
                            <span class="frs-q-num">Q{{ $loop->iteration }}</span>
                            <span class="frs-q-label" title="{{ $stat['label'] }}">{{ $stat['label'] }}</span>
                            <span class="frs-q-type">{{ $stat['type'] }}</span>
                        </div>
                        @if ($stat['options'] !== null)
                            <div class="frs-q-head-right">
                                <span class="frs-answered">{{ $stat['response_count'] }} answered</span>
                                @if ($stat['unused_count'] > 0)
                                    <span class="frs-unused-badge">⚠ {{ $stat['unused_count'] }} unused</span>
                                @endif
                            </div>
                        @else
                            <span class="frs-answered">{{ $stat['response_count'] }}/{{ $totalSubmitted }} responded</span>
                        @endif
                    </div>

                    {{-- Body --}}
                    @if ($stat['options'] !== null)
                        <div x-data="frsQuestion({{ Js::from($stat) }})">

                            {{-- Chart view --}}
                            <div class="frs-chart-wrap" x-show="!showUnused">
                                <div class="frs-chart-canvas-wrap"
                                     style="height:{{ max(80, count($stat['options']) * 30) }}px">
                                    <canvas x-ref="canvas" x-init="buildChart()"></canvas>
                                </div>
                                @if ($stat['unused_count'] > 0)
                                    <button class="frs-toggle-btn" @click="showUnused = true">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                                        </svg>
                                        Show unused only ({{ $stat['unused_count'] }})
                                    </button>
                                @endif
                            </div>

                            {{-- Unused-only list --}}
                            <div class="frs-unused-panel" x-show="showUnused" x-cloak>
                                <p class="frs-unused-title">Never selected options</p>
                                @foreach ($stat['options'] as $opt)
                                    @if ($opt['unused'])
                                        <div class="frs-unused-item">
                                            <span class="frs-unused-dot"></span>
                                            {{ $opt['label'] }}
                                        </div>
                                    @endif
                                @endforeach
                                <button class="frs-toggle-btn" style="margin-top:.625rem" @click="showUnused = false">
                                    ← Show all options
                                </button>
                            </div>

                        </div>
                    @else
                        {{-- Non-choice: count bar --}}
                        @php
                            $pct = $totalSubmitted > 0
                                ? (int) round($stat['response_count'] / $totalSubmitted * 100)
                                : 0;
                        @endphp
                        <div class="frs-count-row">
                            <div class="frs-count-track">
                                <div class="frs-count-fill" style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="frs-count-lbl">
                                {{ $stat['response_count'] }}/{{ $totalSubmitted }} responded ({{ $pct }}%)
                            </span>
                        </div>
                    @endif

                </div>
            @endforeach

        </div>
    @endif
</div>
