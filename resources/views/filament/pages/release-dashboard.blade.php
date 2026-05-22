<x-filament-panels::page>
<style>
/* ── Release Dashboard scoped styles ─────────────────────── */
.rd-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:5rem 1rem;text-align:center}
.rd-empty-icon{width:4rem;height:4rem;border-radius:9999px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin-bottom:1rem}
.rd-empty-icon svg{width:2rem;height:2rem;color:#94a3b8}
.rd-stack{display:flex;flex-direction:column;gap:1rem}

/* Release-set card */
.rd-card{border-radius:1rem;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.08);border:1px solid #e2e8f0;overflow:hidden}
.rd-card-top{display:flex;align-items:stretch;border-bottom:1px solid transparent;transition:border-color .15s}
.rd-card-top.has-sep{border-bottom-color:#f1f5f9}
.rd-card-btn{flex:1;display:flex;align-items:center;gap:1rem;padding:.875rem 1.25rem;text-align:left;background:none;border:none;cursor:pointer;transition:background .15s}
.rd-card-btn:hover{background:#f8fafc}
.rd-status-bar{width:.375rem;align-self:stretch;border-radius:9999px;flex-shrink:0}
.rd-status-open   {background:linear-gradient(to bottom,#10b981,#14b8a6)}
.rd-status-scheduled{background:linear-gradient(to bottom,#f59e0b,#f97316)}
.rd-status-closed {background:linear-gradient(to bottom,#94a3b8,#64748b)}
.rd-status-default{background:linear-gradient(to bottom,#f87171,#ef4444)}
.rd-set-meta{flex:1;min-width:0}
.rd-set-title{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap}
.rd-set-name{font-size:.875rem;font-weight:600;color:#0f172a}
.rd-badge{font-size:.75rem;font-weight:500;padding:.125rem .625rem;border-radius:9999px;border:1px solid transparent}
.rd-badge-open      {background:#d1fae5;color:#065f46;border-color:#a7f3d0}
.rd-badge-scheduled {background:#fef3c7;color:#92400e;border-color:#fde68a}
.rd-badge-closed    {background:#f1f5f9;color:#475569;border-color:#e2e8f0}
.rd-badge-default   {background:#fee2e2;color:#991b1b;border-color:#fecaca}
.rd-set-deadline{font-size:.75rem;color:#94a3b8}
.rd-set-progress{margin-top:.5rem;display:flex;align-items:center;gap:.75rem}
.rd-progress-bar-wrap{flex:1;max-width:16rem;background:#f1f5f9;border-radius:9999px;height:.375rem;overflow:hidden}
.rd-progress-fill{height:.375rem;border-radius:9999px;transition:width .5s}
.rd-fill-indigo{background:#6366f1}
.rd-fill-emerald{background:#10b981}
.rd-fill-amber{background:#f59e0b}
.rd-set-stat{font-size:.75rem;color:#64748b;white-space:nowrap;flex-shrink:0}
.rd-set-stat strong{font-weight:600;color:#1e293b}
.rd-chevron{flex-shrink:0;color:#94a3b8;transition:transform .2s}
.rd-chevron-open{transform:rotate(180deg)}

/* Export action link (shared by set-level and form-level) */
.rd-export-link{flex-shrink:0;display:flex;align-items:center;gap:.3rem;padding:.5rem .875rem;font-size:.6875rem;font-weight:600;color:#94a3b8;text-decoration:none;border-left:1px solid #f1f5f9;white-space:nowrap;transition:background .15s,color .15s}
.rd-export-link:hover{background:#f8fafc;color:#4f46e5}
.rd-export-link svg{flex-shrink:0}

/* Forms list */
.rd-forms{display:none}
.rd-forms.is-open{display:block}
.rd-form-empty{padding:.875rem 1.5rem;font-size:.875rem;color:#94a3b8}
.rd-form-list{border-top:1px solid #f1f5f9}
.rd-form-row{border-bottom:1px solid #f1f5f9}
.rd-form-row:last-child{border-bottom:none}
.rd-form-head{display:flex;align-items:stretch}
.rd-form-btn{flex:1;display:flex;align-items:center;gap:.875rem;padding:.75rem 1.5rem;text-align:left;background:none;border:none;cursor:pointer;transition:background .15s}
.rd-form-btn:hover{background:#f8fafc}
.rd-form-badge{flex-shrink:0;width:1.5rem;height:1.5rem;border-radius:9999px;display:flex;align-items:center;justify-content:center}
.rd-form-badge-done{background:#d1fae5}
.rd-form-badge-pending{background:#f1f5f9}
.rd-form-badge-num{font-size:.6875rem;font-weight:700;color:#94a3b8}
.rd-form-info{flex:1;min-width:0;text-align:left}
.rd-form-title{font-size:.8125rem;font-weight:500;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rd-form-optional{font-size:.75rem;font-weight:400;color:#94a3b8;margin-left:.25rem}
.rd-form-progress{margin-top:.375rem;display:flex;align-items:center;gap:.5rem}
.rd-form-bar-wrap{width:8rem;background:#f1f5f9;border-radius:9999px;height:.375rem;overflow:hidden}
.rd-form-stat{font-size:.75rem;color:#64748b}
.rd-form-stat strong{font-weight:600;color:#1e293b}
.rd-form-stat-done strong{color:#10b981}
.rd-form-pending-lbl{font-weight:500;color:#d97706}
.rd-form-chevron{flex-shrink:0;color:#94a3b8;transition:transform .2s}
.rd-form-chevron.is-open{transform:rotate(180deg)}
.rd-form-spacer{width:14px;flex-shrink:0}

/* Pending participants panel */
.rd-pending{display:none;background:#f8fafc;border-top:1px solid #e2e8f0;padding:.75rem 1.5rem}
.rd-pending.is-open{display:block}
.rd-pending-title{font-size:.6875rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem}
.rd-pending-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.375rem}
.rd-p-card{display:flex;align-items:center;gap:.5rem;border-radius:.5rem;background:#fff;border:1px solid #e2e8f0;padding:.375rem .625rem;cursor:default;transition:border-color .15s,box-shadow .15s}
.rd-p-card:hover{border-color:#a5b4fc;box-shadow:0 0 0 2px rgba(99,102,241,.1)}
.rd-p-avatar{flex-shrink:0;width:1.25rem;height:1.25rem;border-radius:9999px;background:#e0e7ff;display:flex;align-items:center;justify-content:center}
.rd-p-initial{font-size:.625rem;font-weight:700;color:#4f46e5}
.rd-p-name{font-size:.75rem;font-weight:500;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rd-p-div{font-size:.6875rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* Search inside pending panel */
.rd-search{position:relative;margin-bottom:.625rem}
.rd-search-input{width:100%;border:1px solid #e2e8f0;border-radius:.5rem;background:#f8fafc;padding:.375rem .625rem .375rem 2rem;font-size:.6875rem;color:#374151;outline:none;transition:border-color .15s,box-shadow .15s}
.rd-search-input:focus{border-color:#818cf8;box-shadow:0 0 0 2px rgba(99,102,241,.15)}
.rd-search-input::placeholder{color:#94a3b8;opacity:.8}
.rd-search-icon{position:absolute;left:.5rem;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none}
.rd-no-results{font-size:.6875rem;color:#9ca3af;text-align:center;padding:.375rem 0;grid-column:1/-1}
/* Floating name tooltip */
.rd-tip{position:fixed;z-index:9999;background:#1e293b;color:#f8fafc;font-size:.6875rem;font-weight:500;padding:.25rem .625rem;border-radius:.375rem;pointer-events:none;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,.2)}

/* Tab bar */
.rd-tab-bar{display:flex;border-bottom:1px solid #f1f5f9;padding:0 1.5rem;background:#fff}
.rd-tab{padding:.5rem .875rem;font-size:.75rem;font-weight:500;color:#64748b;background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;transition:color .15s,border-color .15s;margin-bottom:-1px;white-space:nowrap}
.rd-tab:hover{color:#1e293b}
.rd-tab-active{color:#6366f1;border-bottom-color:#6366f1}
.rd-tab-count{font-size:.6875rem;font-weight:600;background:#f1f5f9;color:#475569;border-radius:9999px;padding:.05rem .375rem;margin-left:.25rem}
.rd-tab-count-warn{background:#fef3c7;color:#92400e}
.rd-all-submitted{padding:.875rem 1.5rem;font-size:.8125rem;color:#10b981;display:flex;align-items:center;gap:.375rem}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
function rdSet(defaultOpen) {
    return {
        open: defaultOpen,
        toggle() { this.open = !this.open; }
    };
}
function rdForm() {
    return {
        open: false,
        tab: 'pending',
        toggle() { this.open = !this.open; },
    };
}
function rdPending(names) {
    return {
        search: '',
        names: names,
        tip: { show: false, name: '', x: 0, y: 0 },
        get hasResults() {
            return !this.search || this.names.some(n => n.includes(this.search.toLowerCase()));
        },
        showTip(name, event) {
            this.tip = { show: true, name, x: event.clientX + 12, y: event.clientY - 34 };
        },
        hideTip() { this.tip.show = false; },
    };
}

// ── Statistics panel ──────────────────────────────────────────────────────────

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
        if (tooltip.opacity === 0) { el.style.display = 'none'; return; }

        const idx = tooltip.dataPoints[0].dataIndex;
        const opt = opts[idx];

        let html = `<div class="frs-tt-head">
            <span class="frs-tt-opt-label">${opt.label}</span>
            <span class="frs-tt-stat">${opt.count}&nbsp;<span class="frs-tt-pct">(${opt.pct}%)</span></span>
        </div><div class="frs-tt-divider"></div>`;

        if (opt.respondents && opt.respondents.length > 0) {
            html += '<div class="frs-tt-names">';
            opt.respondents.forEach(name => {
                const initial = (name || '?').charAt(0).toUpperCase();
                html += `<div class="frs-tt-name-row"><span class="frs-tt-avatar">${initial}</span><span class="frs-tt-name-text">${name}</span></div>`;
            });
            html += '</div>';
        } else {
            html += '<div class="frs-tt-empty">No responses</div>';
        }

        el.innerHTML = html;
        el.style.display = 'block';

        const rect = chart.canvas.getBoundingClientRect();
        const tipW = el.offsetWidth, tipH = el.offsetHeight;
        let left = rect.left + tooltip.caretX + 14;
        let top  = rect.top  + tooltip.caretY - tipH / 2;
        if (left + tipW > window.innerWidth - 8)  left = rect.left + tooltip.caretX - tipW - 10;
        if (top < 8)                               top  = 8;
        if (top + tipH > window.innerHeight - 8)  top  = window.innerHeight - tipH - 8;
        el.style.left = left + 'px';
        el.style.top  = top  + 'px';
    };
}

function frsChartQuestion(stat) {
    return {
        showUnused: false,
        chart: null,
        stat: stat,
        build() {
            if (this.chart) { this.chart.destroy(); this.chart = null; }
            const canvas = this.$refs.canvas;
            if (!canvas || typeof Chart === 'undefined') return;
            const opts = this.stat.options;

            canvas.addEventListener('mouseleave', () => {
                const el = _getFrsTooltip();
                if (el) el.style.display = 'none';
            });

            this.chart = new Chart(canvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: opts.map(o => o.label),
                    datasets: [{
                        data: opts.map(o => o.count),
                        backgroundColor: opts.map(o => o.unused ? 'rgba(239,68,68,0.15)' : 'rgba(99,102,241,0.75)'),
                        borderColor:     opts.map(o => o.unused ? 'rgba(239,68,68,0.7)'  : 'rgba(99,102,241,1)'),
                        borderWidth: 1, borderRadius: 3,
                    }],
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false, external: _makeTooltipHandler(opts) },
                    },
                    scales: {
                        x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.05)' } },
                        y: { ticks: { font: { size: 11 } }, grid: { display: false } },
                    },
                },
            });
        },
    };
}

function formStats(releaseId, total) {
    return {
        loaded: false,
        loading: false,
        error: null,
        stats: [],
        totalSubmitted: 0,
        total: total,

        async loadStats() {
            if (this.loaded || this.loading) return;
            this.loading = true;
            this.error = null;
            try {
                const res = await fetch(`/admin/api/release-stats/${releaseId}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = await res.json();
                this.totalSubmitted = data.total_submitted;
                this.stats = data.stats;
                this.loaded = true;
            } catch (e) {
                this.error = 'Failed to load statistics. Please try again.';
            }
            this.loading = false;
        },

        get totalUnused() {
            return this.stats.filter(s => s.options).reduce((sum, s) => sum + s.unused_count, 0);
        },
        get completionPct() {
            return this.total > 0 ? Math.round(this.totalSubmitted / this.total * 100) : 0;
        },
    };
}
</script>

<style>
/* Floating chart tooltip */
.frs-chart-tooltip{position:fixed;z-index:9999;background:#1e293b;color:#f8fafc;border-radius:.5rem;box-shadow:0 8px 24px rgba(0,0,0,.25);min-width:180px;max-width:260px;pointer-events:none;font-size:.75rem;overflow:hidden}
.frs-tt-head{display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;padding:.5rem .75rem}
.frs-tt-opt-label{font-weight:600;color:#f1f5f9;flex:1;line-height:1.3;word-break:break-word}
.frs-tt-stat{flex-shrink:0;font-weight:700;color:#fff;white-space:nowrap}
.frs-tt-pct{font-weight:400;color:#94a3b8}
.frs-tt-divider{height:1px;background:rgba(255,255,255,.1)}
.frs-tt-names{padding:.375rem .5rem;max-height:180px;overflow-y:auto}
.frs-tt-name-row{display:flex;align-items:center;gap:.375rem;padding:.2rem .25rem;border-radius:.25rem}
.frs-tt-name-row:hover{background:rgba(255,255,255,.07)}
.frs-tt-avatar{flex-shrink:0;width:1.25rem;height:1.25rem;border-radius:9999px;background:rgba(99,102,241,.4);display:flex;align-items:center;justify-content:center;font-size:.5625rem;font-weight:700;color:#c7d2fe}
.frs-tt-name-text{font-size:.6875rem;color:#cbd5e1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.frs-tt-empty{padding:.375rem .75rem .5rem;font-size:.6875rem;color:#64748b;font-style:italic}

/* Stats panel layout */
.frs-wrap{padding:.875rem 1.5rem 1rem}
.frs-loading-row{display:flex;flex-direction:column;gap:.375rem;padding:.75rem 0 1rem}
.frs-skeleton{background:linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);background-size:200% 100%;animation:frs-shimmer 1.4s infinite;border-radius:.375rem}
@keyframes frs-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.frs-summary{display:flex;gap:.625rem;margin-bottom:1rem;flex-wrap:wrap}
.frs-summary-card{flex:1;min-width:7rem;background:#f8fafc;border:1px solid #e2e8f0;border-radius:.5rem;padding:.5rem .75rem}
.frs-summary-num{display:block;font-size:1.125rem;font-weight:700;color:#1e293b;line-height:1.2}
.frs-summary-num.frs-warn{color:#dc2626}
.frs-summary-lbl{display:block;font-size:.6875rem;color:#94a3b8;margin-top:.125rem}
.frs-q-section{border:1px solid #f1f5f9;border-radius:.5rem;margin-bottom:.625rem;overflow:hidden;background:#fff}
.frs-q-head{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.5rem .875rem;background:#f8fafc;border-bottom:1px solid #f1f5f9;flex-wrap:wrap}
.frs-q-meta{display:flex;align-items:center;gap:.5rem;min-width:0}
.frs-q-num{font-size:.6875rem;font-weight:700;color:#94a3b8;flex-shrink:0}
.frs-q-label{font-size:.8125rem;font-weight:500;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:30rem}
.frs-q-type{font-size:.625rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#64748b;background:#e2e8f0;border-radius:.25rem;padding:.1rem .375rem;flex-shrink:0}
.frs-q-head-right{display:flex;align-items:center;gap:.5rem;flex-shrink:0}
.frs-answered{font-size:.75rem;color:#64748b}
.frs-unused-badge{font-size:.6875rem;font-weight:600;color:#b45309;background:#fef3c7;border:1px solid #fde68a;border-radius:9999px;padding:.1rem .5rem;white-space:nowrap}
.frs-chart-wrap{padding:.75rem .875rem}
.frs-toggle-btn{display:inline-flex;align-items:center;gap:.25rem;margin-top:.5rem;font-size:.6875rem;font-weight:600;color:#6366f1;background:none;border:1px solid #e0e7ff;border-radius:.375rem;padding:.25rem .625rem;cursor:pointer;transition:background .15s,border-color .15s}
.frs-toggle-btn:hover{background:#eef2ff;border-color:#c7d2fe}
.frs-unused-panel{padding:.75rem .875rem}
.frs-unused-title{font-size:.6875rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:.5rem}
.frs-unused-item{display:flex;align-items:center;gap:.5rem;padding:.3rem 0;font-size:.8125rem;color:#374151}
.frs-unused-dot{flex-shrink:0;width:.5rem;height:.5rem;border-radius:9999px;background:#ef4444}
.frs-count-row{padding:.625rem .875rem;display:flex;align-items:center;gap:.75rem}
.frs-count-track{flex:1;max-width:16rem;height:.375rem;background:#f1f5f9;border-radius:9999px;overflow:hidden}
.frs-count-fill{height:.375rem;background:#6366f1;border-radius:9999px;transition:width .4s}
.frs-count-lbl{font-size:.75rem;color:#64748b}
.frs-error{padding:.875rem 1.5rem;font-size:.8125rem;color:#dc2626}
</style>

@if ($releaseSets->isEmpty())
    <div class="rd-empty">
        <div class="rd-empty-icon">
            <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
            </svg>
        </div>
        <p style="font-size:.9375rem;font-weight:600;color:#374151">{{ __('admin.dashboard_no_sets') }}</p>
        <p style="font-size:.875rem;color:#94a3b8;margin-top:.25rem">{{ __('admin.dashboard_no_sets_desc') }}</p>
    </div>
@else
    <div class="rd-stack">
        @foreach ($releaseSets as $item)
            @php
                $set       = $item['set'];
                $statusKey = $set->status;
                $barClass  = match ($statusKey) {
                    'open'      => 'rd-status-open',
                    'scheduled' => 'rd-status-scheduled',
                    'closed'    => 'rd-status-closed',
                    default     => 'rd-status-default',
                };
                $badgeClass = match ($statusKey) {
                    'open'      => 'rd-badge-open',
                    'scheduled' => 'rd-badge-scheduled',
                    'closed'    => 'rd-badge-closed',
                    default     => 'rd-badge-default',
                };
                $isOpen    = in_array($statusKey, ['open', 'scheduled']);
                $setId     = 'rdset-' . $set->id;
                $setFillColor = $item['set_percent'] === 100 ? 'rd-fill-emerald' : 'rd-fill-indigo';
            @endphp

            <div class="rd-card" x-data="rdSet({{ $isOpen ? 'true' : 'false' }})">

                {{-- Release set header --}}
                <div class="rd-card-top" :class="open && 'has-sep'">
                <button
                    type="button"
                    class="rd-card-btn"
                    :aria-expanded="open.toString()"
                    @click="toggle()"
                >
                    <div class="rd-status-bar {{ $barClass }}"></div>

                    <div class="rd-set-meta">
                        <div class="rd-set-title">
                            <span class="rd-set-name">{{ $set->name }}</span>
                            <span class="rd-badge {{ $badgeClass }}">{{ ucfirst($set->status) }}</span>
                            @if ($set->end_at)
                                <span class="rd-set-deadline">· {{ __('admin.dashboard_closes') }} {{ $set->end_at->format('d M Y') }}</span>
                            @endif
                        </div>

                        <div class="rd-set-progress">
                            <div class="rd-progress-bar-wrap">
                                <div class="rd-progress-fill {{ $setFillColor }}"
                                     style="width:{{ $item['set_percent'] }}%"></div>
                            </div>
                            <span class="rd-set-stat">
                                <strong>{{ $item['complete_forms'] }}/{{ $item['total_forms'] }}</strong>
                                {{ __('admin.dashboard_forms_complete') }} &bull;
                                {{ trans_choice('admin.dashboard_participant_choice', $item['total'], ['count' => $item['total']]) }}
                            </span>
                        </div>
                    </div>

                    <svg width="16" height="16" :class="open ? 'rd-chevron rd-chevron-open' : 'rd-chevron'"
                         fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
                <a href="{{ route('admin.release-sets.export', $set) }}"
                   class="rd-export-link"
                   title="{{ __('admin.dashboard_export_all_title') }}">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    {{ __('admin.dashboard_export') }}
                </a>
                </div>

                {{-- Forms list --}}
                <div :class="open ? 'rd-forms is-open' : 'rd-forms'">
                    @if ($item['forms']->isEmpty())
                        <p class="rd-form-empty">{{ __('admin.dashboard_no_forms') }}</p>
                    @else
                        <div class="rd-form-list">
                            @foreach ($item['forms'] as $fi)
                                @php
                                    $release        = $fi['release'];
                                    $pct            = $fi['percent'];
                                    $fillColor      = $fi['is_complete'] ? 'rd-fill-emerald'
                                                    : ($pct >= 50 ? 'rd-fill-indigo' : 'rd-fill-amber');
                                    $formId         = 'rdform-' . $release->id;
                                    $adminStatus    = $release->getAdminStatus();
                                    $effectiveStart = $release->getEffectiveStartAt();
                                    $effectiveEnd   = $release->getEffectiveEndAt();
                                @endphp

                                <div class="rd-form-row" x-data="rdForm()">

                                    {{-- Form row header --}}
                                    <div class="rd-form-head">
                                    <button
                                        type="button"
                                        class="rd-form-btn"
                                        @click="toggle()"
                                    >
                                        {{-- Badge: tick or number --}}
                                        <div class="rd-form-badge {{ $fi['is_complete'] ? 'rd-form-badge-done' : 'rd-form-badge-pending' }}">
                                            @if ($fi['is_complete'])
                                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="#10b981">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                                </svg>
                                            @else
                                                <span class="rd-form-badge-num">{{ $loop->iteration }}</span>
                                            @endif
                                        </div>

                                        {{-- Title + progress --}}
                                        <div class="rd-form-info">
                                            <p class="rd-form-title">
                                                {{ $release->form?->title ?? 'Form ' . $release->id }}
                                                @unless ($release->is_required)
                                                    <span class="rd-form-optional">{{ __('admin.dashboard_optional') }}</span>
                                                @endunless
                                                {{-- Snapshot status badge --}}
                                                @if ($adminStatus === 'pending')
                                                    <span style="font-size:.6875rem;font-weight:500;color:#92400e;background:#fef3c7;border:1px solid #fde68a;border-radius:9999px;padding:.1rem .5rem;margin-left:.25rem">{{ __('admin.status_release_pending') }}</span>
                                                @elseif ($adminStatus === 'unpublished')
                                                    <span style="font-size:.6875rem;font-weight:500;color:#475569;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:9999px;padding:.1rem .5rem;margin-left:.25rem">{{ __('admin.status_release_unpublished') }}</span>
                                                @endif
                                            </p>
                                            {{-- Per-release effective dates --}}
                                            @if ($effectiveStart || $effectiveEnd)
                                                <p style="font-size:.6875rem;color:#94a3b8;margin-top:.125rem">
                                                    @if ($effectiveStart)
                                                        {{ __('admin.col_opens') }}: {{ $effectiveStart->format('d M Y') }}
                                                    @endif
                                                    @if ($effectiveStart && $effectiveEnd) &bull; @endif
                                                    @if ($effectiveEnd)
                                                        {{ __('admin.col_closes') }}: {{ $effectiveEnd->format('d M Y') }}
                                                    @endif
                                                </p>
                                            @endif
                                            <div class="rd-form-progress">
                                                <div class="rd-form-bar-wrap">
                                                    <div class="rd-progress-fill {{ $fillColor }}"
                                                         style="width:{{ $pct }}%"></div>
                                                </div>
                                                <span class="rd-form-stat {{ $fi['is_complete'] ? 'rd-form-stat-done' : '' }}">
                                                    <strong>{{ $fi['submitted_count'] }}/{{ $fi['total'] }}</strong>
                                                    {{ __('admin.dashboard_submitted') }}
                                                    @if ($fi['pending']->isNotEmpty())
                                                        &bull; <span class="rd-form-pending-lbl">{{ $fi['pending']->count() }} {{ __('admin.dashboard_pending') }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Expand chevron --}}
                                        <svg width="14" height="14"
                                             :class="open ? 'rd-form-chevron is-open' : 'rd-form-chevron'"
                                             fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                        </svg>
                                    </button>
                                    <a href="{{ route('admin.releases.export', $release) }}"
                                       class="rd-export-link"
                                       title="{{ __('admin.dashboard_export_form_title') }}">
                                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                                        </svg>
                                        {{ __('admin.dashboard_export') }}
                                    </a>
                                    </div>

                                    {{-- Tab bar + panels (shown when expanded) --}}
                                    <div x-show="open" style="display:none">

                                        {{-- Tab bar --}}
                                        <div class="rd-tab-bar">
                                            <button type="button"
                                                    class="rd-tab"
                                                    :class="tab === 'pending' && 'rd-tab-active'"
                                                    @click="tab = 'pending'">
                                                Not Submitted
                                                <span class="rd-tab-count {{ $fi['pending']->isNotEmpty() ? 'rd-tab-count-warn' : '' }}">{{ $fi['pending']->count() }}</span>
                                            </button>
                                            <button type="button"
                                                    class="rd-tab"
                                                    :class="tab === 'stats' && 'rd-tab-active'"
                                                    @click="tab = 'stats'; $dispatch('frs-load-{{ $release->id }}')">
                                                Statistics
                                            </button>
                                        </div>

                                        {{-- Pending panel --}}
                                        <div x-show="tab === 'pending'">
                                            @if ($fi['pending']->isNotEmpty())
                                                <div class="rd-pending is-open">
                                                    <div x-data="rdPending({{ Js::from($fi['pending']->pluck('name')->map(fn ($n) => strtolower($n))->values()) }})">
                                                        {{-- Fixed tooltip --}}
                                                        <div class="rd-tip"
                                                             x-show="tip.show"
                                                             x-text="tip.name"
                                                             :style="`left:${tip.x}px;top:${tip.y}px`"
                                                             style="display:none"></div>

                                                        <p class="rd-pending-title">
                                                            {{ __('admin.dashboard_not_yet_submitted', ['count' => $fi['pending']->count()]) }}
                                                        </p>
                                                        <div class="rd-search">
                                                            <svg class="rd-search-icon" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/>
                                                            </svg>
                                                            <input
                                                                x-model="search"
                                                                type="text"
                                                                class="rd-search-input"
                                                                placeholder="{{ __('admin.dashboard_search_participant') }}"
                                                            />
                                                        </div>
                                                        <div class="rd-pending-grid">
                                                            @foreach ($fi['pending'] as $participant)
                                                                <div class="rd-p-card"
                                                                     data-name="{{ strtolower($participant->name) }}"
                                                                     data-fullname="{{ $participant->name }}"
                                                                     x-show="!search || $el.dataset.name.includes(search.toLowerCase())"
                                                                     @mouseenter="showTip($el.dataset.fullname, $event)"
                                                                     @mouseleave="hideTip()">
                                                                    <div class="rd-p-avatar">
                                                                        <span class="rd-p-initial">{{ strtoupper(substr($participant->name, 0, 1)) }}</span>
                                                                    </div>
                                                                    <div style="min-width:0">
                                                                        <p class="rd-p-name">{{ $participant->name }}</p>
                                                                        @if ($participant->division)
                                                                            <p class="rd-p-div">{{ $participant->division->name }}</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                            <p class="rd-no-results" x-show="!hasResults" style="display:none">{{ __('admin.dashboard_no_participants_match') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="rd-all-submitted">
                                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    All participants have submitted.
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Statistics panel --}}
                                        <div x-show="tab === 'stats'"
                                             x-data="formStats({{ $release->id }}, {{ $fi['total'] }})"
                                             @frs-load-{{ $release->id }}.window="loadStats()">
                                            <div class="frs-wrap">

                                                {{-- Loading skeleton --}}
                                                <div x-show="loading" class="frs-loading-row">
                                                    <div class="frs-skeleton" style="width:55%;height:.625rem"></div>
                                                    <div class="frs-skeleton" style="width:35%;height:.625rem"></div>
                                                    <div class="frs-skeleton" style="width:100%;height:3rem;margin-top:.5rem"></div>
                                                    <div class="frs-skeleton" style="width:100%;height:3rem;margin-top:.375rem"></div>
                                                </div>

                                                {{-- Error --}}
                                                <p x-show="error" x-text="error" class="frs-error"></p>

                                                {{-- Summary --}}
                                                <div x-show="loaded" class="frs-summary">
                                                    <div class="frs-summary-card">
                                                        <span class="frs-summary-num" x-text="totalSubmitted + '/{{ $fi['total'] }}'"></span>
                                                        <span class="frs-summary-lbl">Submitted</span>
                                                    </div>
                                                    <div class="frs-summary-card">
                                                        <span class="frs-summary-num" x-text="completionPct + '%'"></span>
                                                        <span class="frs-summary-lbl">Completion rate</span>
                                                    </div>
                                                    <div class="frs-summary-card">
                                                        <span class="frs-summary-num" :class="totalUnused > 0 && 'frs-warn'" x-text="totalUnused"></span>
                                                        <span class="frs-summary-lbl">Unused options total</span>
                                                    </div>
                                                </div>

                                                {{-- Per-question sections --}}
                                                <template x-if="loaded">
                                                    <div>
                                                        <template x-for="(stat, idx) in stats" :key="stat.id">
                                                            <div class="frs-q-section">

                                                                {{-- Header --}}
                                                                <div class="frs-q-head">
                                                                    <div class="frs-q-meta">
                                                                        <span class="frs-q-num" x-text="'Q' + (idx + 1)"></span>
                                                                        <span class="frs-q-label" x-text="stat.label" :title="stat.label"></span>
                                                                        <span class="frs-q-type" x-text="stat.type"></span>
                                                                    </div>
                                                                    <template x-if="stat.options">
                                                                        <div class="frs-q-head-right">
                                                                            <span class="frs-answered" x-text="stat.response_count + ' answered'"></span>
                                                                            <span x-show="stat.unused_count > 0" class="frs-unused-badge" x-text="'⚠ ' + stat.unused_count + ' unused'"></span>
                                                                        </div>
                                                                    </template>
                                                                    <template x-if="!stat.options">
                                                                        <span class="frs-answered" x-text="stat.response_count + '/' + totalSubmitted + ' responded'"></span>
                                                                    </template>
                                                                </div>

                                                                {{-- Choice question with chart --}}
                                                                <template x-if="stat.options">
                                                                    <div x-data="frsChartQuestion(stat)">
                                                                        {{-- Chart view --}}
                                                                        <div x-show="!showUnused" class="frs-chart-wrap">
                                                                            <div :style="'position:relative;height:' + Math.max(80, stat.options.length * 30) + 'px'">
                                                                                <canvas x-ref="canvas" x-init="build()"></canvas>
                                                                            </div>
                                                                            <button x-show="stat.unused_count > 0"
                                                                                    class="frs-toggle-btn"
                                                                                    @click="showUnused = true">
                                                                                ⚠ Show unused only (<span x-text="stat.unused_count"></span>)
                                                                            </button>
                                                                        </div>
                                                                        {{-- Unused-only list --}}
                                                                        <div x-show="showUnused" class="frs-unused-panel">
                                                                            <p class="frs-unused-title">Never selected options</p>
                                                                            <template x-for="opt in stat.options.filter(o => o.unused)" :key="opt.label">
                                                                                <div class="frs-unused-item">
                                                                                    <span class="frs-unused-dot"></span>
                                                                                    <span x-text="opt.label"></span>
                                                                                </div>
                                                                            </template>
                                                                            <button class="frs-toggle-btn" style="margin-top:.625rem" @click="showUnused = false">← Show all options</button>
                                                                        </div>
                                                                    </div>
                                                                </template>

                                                                {{-- Non-choice: count bar --}}
                                                                <template x-if="!stat.options">
                                                                    <div class="frs-count-row">
                                                                        <div class="frs-count-track">
                                                                            <div class="frs-count-fill"
                                                                                 :style="'width:' + (totalSubmitted > 0 ? Math.round(stat.response_count / totalSubmitted * 100) : 0) + '%'">
                                                                            </div>
                                                                        </div>
                                                                        <span class="frs-count-lbl"
                                                                              x-text="stat.response_count + '/' + totalSubmitted + ' responded (' + (totalSubmitted > 0 ? Math.round(stat.response_count / totalSubmitted * 100) : 0) + '%)'">
                                                                        </span>
                                                                    </div>
                                                                </template>

                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>

                                            </div>
                                        </div>

                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        @endforeach
    </div>
@endif

</x-filament-panels::page>
