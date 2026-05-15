<x-filament-panels::page>
<style>
/* ── Release Dashboard scoped styles ─────────────────────── */
.rd-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:5rem 1rem;text-align:center}
.rd-empty-icon{width:4rem;height:4rem;border-radius:9999px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin-bottom:1rem}
.rd-empty-icon svg{width:2rem;height:2rem;color:#94a3b8}
.rd-stack{display:flex;flex-direction:column;gap:1rem}

/* Release-set card */
.rd-card{border-radius:1rem;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.08);border:1px solid #e2e8f0;overflow:hidden}
.rd-card-btn{width:100%;display:flex;align-items:center;gap:1rem;padding:.875rem 1.25rem;text-align:left;background:none;border:none;border-bottom:1px solid transparent;cursor:pointer;transition:background .15s}
.rd-card-btn:hover{background:#f8fafc}
.rd-card-btn[aria-expanded="true"]{border-bottom-color:#f1f5f9}
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

/* Forms list */
.rd-forms{display:none;border-top:1px solid #f1f5f9}
.rd-forms.is-open{display:block}
.rd-form-empty{padding:.875rem 1.5rem;font-size:.875rem;color:#94a3b8}
.rd-form-list{border-top:1px solid #f1f5f9}
.rd-form-row{border-bottom:1px solid #f1f5f9}
.rd-form-row:last-child{border-bottom:none}
.rd-form-btn{width:100%;display:flex;align-items:center;gap:.875rem;padding:.75rem 1.5rem;text-align:left;background:none;border:none;cursor:pointer;transition:background .15s}
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
.rd-pending{display:none;background:#fffbeb;border-top:1px solid #fde68a;padding:.75rem 1.5rem}
.rd-pending.is-open{display:block}
.rd-pending-title{font-size:.6875rem;font-weight:700;color:#b45309;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem}
.rd-pending-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.375rem}
.rd-p-card{display:flex;align-items:center;gap:.5rem;border-radius:.5rem;background:#fff;border:1px solid #fde68a;padding:.375rem .625rem}
.rd-p-avatar{flex-shrink:0;width:1.25rem;height:1.25rem;border-radius:9999px;background:#fef3c7;display:flex;align-items:center;justify-content:center}
.rd-p-initial{font-size:.625rem;font-weight:700;color:#d97706}
.rd-p-name{font-size:.75rem;font-weight:500;color:#374151;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.rd-p-div{font-size:.6875rem;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
</style>

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
        toggle() { this.open = !this.open; }
    };
}
</script>

@if ($releaseSets->isEmpty())
    <div class="rd-empty">
        <div class="rd-empty-icon">
            <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
            </svg>
        </div>
        <p style="font-size:.9375rem;font-weight:600;color:#374151">No release sets yet</p>
        <p style="font-size:.875rem;color:#94a3b8;margin-top:.25rem">Create a release set to see the dashboard.</p>
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
                                <span class="rd-set-deadline">· closes {{ $set->end_at->format('d M Y') }}</span>
                            @endif
                        </div>

                        <div class="rd-set-progress">
                            <div class="rd-progress-bar-wrap">
                                <div class="rd-progress-fill {{ $setFillColor }}"
                                     style="width:{{ $item['set_percent'] }}%"></div>
                            </div>
                            <span class="rd-set-stat">
                                <strong>{{ $item['complete_forms'] }}/{{ $item['total_forms'] }}</strong>
                                forms complete &bull;
                                {{ $item['total'] }} participant{{ $item['total'] != 1 ? 's' : '' }}
                            </span>
                        </div>
                    </div>

                    <svg width="16" height="16" :class="open ? 'rd-chevron rd-chevron-open' : 'rd-chevron'"
                         fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>

                {{-- Forms list --}}
                <div :class="open ? 'rd-forms is-open' : 'rd-forms'">
                    @if ($item['forms']->isEmpty())
                        <p class="rd-form-empty">No forms in this release set.</p>
                    @else
                        <div class="rd-form-list">
                            @foreach ($item['forms'] as $fi)
                                @php
                                    $release   = $fi['release'];
                                    $pct       = $fi['percent'];
                                    $fillColor = $fi['is_complete'] ? 'rd-fill-emerald'
                                               : ($pct >= 50 ? 'rd-fill-indigo' : 'rd-fill-amber');
                                    $formId    = 'rdform-' . $release->id;
                                @endphp

                                <div class="rd-form-row" x-data="rdForm()">

                                    {{-- Form row button --}}
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
                                                    <span class="rd-form-optional">(optional)</span>
                                                @endunless
                                            </p>
                                            <div class="rd-form-progress">
                                                <div class="rd-form-bar-wrap">
                                                    <div class="rd-progress-fill {{ $fillColor }}"
                                                         style="width:{{ $pct }}%"></div>
                                                </div>
                                                <span class="rd-form-stat {{ $fi['is_complete'] ? 'rd-form-stat-done' : '' }}">
                                                    <strong>{{ $fi['submitted_count'] }}/{{ $fi['total'] }}</strong>
                                                    submitted
                                                    @if ($fi['pending']->isNotEmpty())
                                                        &bull; <span class="rd-form-pending-lbl">{{ $fi['pending']->count() }} pending</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Expand chevron --}}
                                        @if ($fi['pending']->isNotEmpty())
                                            <svg width="14" height="14"
                                                 :class="open ? 'rd-form-chevron is-open' : 'rd-form-chevron'"
                                                 fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                            </svg>
                                        @else
                                            <div class="rd-form-spacer"></div>
                                        @endif
                                    </button>

                                    {{-- Pending participants --}}
                                    @if ($fi['pending']->isNotEmpty())
                                        <div :class="open ? 'rd-pending is-open' : 'rd-pending'">
                                            <p class="rd-pending-title">
                                                Not yet submitted ({{ $fi['pending']->count() }})
                                            </p>
                                            <div class="rd-pending-grid">
                                                @foreach ($fi['pending'] as $participant)
                                                    <div class="rd-p-card">
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
                                            </div>
                                        </div>
                                    @endif

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
