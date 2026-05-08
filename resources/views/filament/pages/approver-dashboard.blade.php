<x-filament-panels::page>
<style>
/* Dashboard Styles */
.dash-wrap { display:flex; flex-direction:column; gap:1.5rem; }

/* Alert */
.dash-alert { display:flex; gap:.75rem; align-items:flex-start; border-radius:.625rem; border:1px solid; padding:1rem; }
.dash-alert-icon { flex-shrink:0; margin-top:.05rem; }
.dash-alert-body { font-size:.875rem; line-height:1.5; }
.dash-alert-yellow { background:#fef3c7; border-color:#fde68a; }
.dash-alert-yellow .dash-alert-icon svg { color:#f59e0b; }
.dash-alert-yellow .dash-alert-body { color:#92400e; }

/* Card */
.dash-card { border-radius:.875rem; border:1px solid #e5e7eb; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,.06); overflow:hidden; }
.dash-card-head { padding:1rem 1.25rem; border-bottom:1px solid #e5e7eb; }
.dash-card-head-title { font-size:1rem; font-weight:600; color:#111827; }
.dash-card-head-count { display:flex; align-items:center; gap:.5rem; margin-left:auto; }
.dash-count-badge { border-radius:9999px; background:#fef3c7; padding:.25rem .75rem; font-size:.75rem; font-weight:600; color:#92400e; }
.dash-card-body { padding:0; }

/* PR Item */
.dash-pr-list { display:flex; flex-direction:column; }
.dash-pr-item { padding:1rem 1.25rem; border-bottom:1px solid #f3f4f6; display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; }
.dash-pr-item:last-child { border-bottom:none; }
.dash-pr-left { flex:1; min-width:0; }
.dash-pr-header { display:flex; align-items:center; gap:.5rem; margin-bottom:.375rem; }
.dash-pr-number { font-size:.875rem; font-weight:600; color:#111827; }
.dash-pr-status { border-radius:9999px; padding:.2rem .625rem; font-size:.7rem; font-weight:600; }
.dash-status-yellow { background:#fef3c7; color:#92400e; }
.dash-status-blue { background:#dbeafe; color:#1e40af; }
.dash-pr-purpose { font-size:.875rem; color:#6b7280; margin-bottom:.5rem; line-height:1.5; }
.dash-pr-meta { display:flex; align-items:center; gap:.75rem; font-size:.8rem; color:#9ca3af; }
.dash-pr-meta span { display:flex; align-items:center; gap:.25rem; }
.dash-pr-action { flex-shrink:0; }

/* Stats Grid */
.dash-stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; }
.dash-stat-card { border-radius:.875rem; border:1px solid; padding:1.25rem; }
.dash-stat-value { font-size:2rem; font-weight:700; margin-bottom:.25rem; }
.dash-stat-label { font-size:.875rem; color:#6b7280; }

.dash-stat-blue { background:#eff6ff; border-color:#bfdbfe; }
.dash-stat-blue .dash-stat-value { color:#1d4ed8; }
.dash-stat-green { background:#f0fdf4; border-color:#bbf7d0; }
.dash-stat-green .dash-stat-value { color:#16a34a; }
.dash-stat-purple { background:#f5f3ff; border-color:#ddd6fe; }
.dash-stat-purple .dash-stat-value { color:#7c3aed; }

/* Quick Actions */
.dash-actions-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
.dash-action-card { border-radius:.875rem; border:1px solid #e5e7eb; background:#fff; padding:1rem; text-decoration:none; transition:all .15s; display:block; }
.dash-action-card:hover { border-color:#6366f1; box-shadow:0 4px 12px rgba(99,102,241,.15); }
.dash-action-header { display:flex; align-items:center; gap:.75rem; margin-bottom:.5rem; }
.dash-action-icon { display:flex; align-items:center; justify-content:center; width:2.5rem; height:2.5rem; border-radius:.625rem; }
.dash-action-icon-blue { background:#dbeafe; color:#1d4ed8; }
.dash-action-icon-green { background:#dcfce7; color:#16a34a; }
.dash-action-icon-purple { background:#f3e8ff; color:#7c3aed; }
.dash-action-title { font-size:.875rem; font-weight:600; color:#111827; }
.dash-action-desc { font-size:.8rem; color:#6b7280; }

/* Empty State */
.dash-empty { text-align:center; padding:2.5rem 1.5rem; }
.dash-empty-icon { width:3rem; height:3rem; margin:0 auto .75rem; color:#d1d5db; }
.dash-empty-text { font-size:.875rem; color:#6b7280; }

/* Dark mode */
.dark .dash-card { background:#1f2937; border-color:#374151; }
.dark .dash-card-head { border-color:#374151; }
.dark .dash-card-head-title { color:#f9fafb; }
.dark .dash-pr-item { border-color:#374151; }
.dark .dash-pr-number { color:#f9fafb; }
.dark .dash-pr-purpose { color:#9ca3af; }
.dark .dash-stat-label { color:#9ca3af; }
.dark .dash-action-card { background:#1f2937; border-color:#374151; }
.dark .dash-action-title { color:#f9fafb; }
.dark .dash-action-desc { color:#9ca3af; }
.dark .dash-pr-meta { color:#6b7280; }
</style>

<div class="dash-wrap">
    {{-- Pending Alert --}}
    @if($pendingCount > 0)
        <div class="dash-alert dash-alert-yellow">
            <div class="dash-alert-icon">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <div class="dash-alert-body">
                <strong>{{ $pendingCount }} request(s)</strong> waiting for your review. Please review them promptly.
            </div>
        </div>
    @endif

    {{-- Pending Approvals --}}
    <div class="dash-card">
        <div class="dash-card-head">
            <span class="dash-card-head-title">Pending Approvals</span>
            @if($pendingCount > 0)
                <div class="dash-card-head-count">
                    <span class="dash-count-badge">{{ $pendingCount }}</span>
                </div>
            @endif
        </div>
        <div class="dash-card-body">
            @if($pendingApprovals->count() > 0)
                <div class="dash-pr-list">
                    @foreach($pendingApprovals as $pr)
                    <div class="dash-pr-item">
                        <div class="dash-pr-left">
                            <div class="dash-pr-header">
                                <span class="dash-pr-number">{{ $pr->pr_number }}</span>
                                @if($pr->status === 'waiting_approval')
                                    <span class="dash-pr-status dash-status-yellow">Waiting Approval</span>
                                @else
                                    <span class="dash-pr-status dash-status-blue">In Review</span>
                                @endif
                            </div>
                            <p class="dash-pr-purpose">{{ \Illuminate\Support\Str::limit($pr->purpose, 80) }}</p>
                            <div class="dash-pr-meta">
                                <span>
                                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                    </svg>
                                    {{ $pr->requester->name }}
                                </span>
                                <span>•</span>
                                <span>Rp {{ number_format((float)$pr->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="dash-pr-action">
                            <a href="{{ route('filament.admin.resources.purchase-requests.edit', $pr) }}"
                               class="inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors">
                                Review
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="dash-empty">
                    <svg class="dash-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="dash-empty-text">No pending approvals</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Performance Stats --}}
    <div class="dash-stats-grid">
        <div class="dash-stat-card dash-stat-blue">
            <div class="dash-stat-value">{{ $thisMonthCount }}</div>
            <div class="dash-stat-label">Approved This Month</div>
        </div>
        <div class="dash-stat-card dash-stat-green">
            <div class="dash-stat-value">{{ $approvedCount }}</div>
            <div class="dash-stat-label">Total Approved</div>
        </div>
        <div class="dash-stat-card dash-stat-purple">
            <div class="dash-stat-value">{{ number_format($avgResponseTime, 1) }}h</div>
            <div class="dash-stat-label">Avg Response Time</div>
        </div>
    </div>

    @if($pendingCount > 5)
    <div style="text-align:center;margin-top:.5rem">
        <a href="{{ route('filament.admin.resources.purchase-requests.index') }}"
           class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
            View All Pending ({{ $pendingCount }}) →
        </a>
    </div>
    @endif
</div>
</x-filament-panels::page>
