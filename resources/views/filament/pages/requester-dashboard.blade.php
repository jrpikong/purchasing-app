<x-filament-panels::page>
<style>
/* Dashboard Styles */
.dash-wrap { display:flex; flex-direction:column; gap:1.5rem; }

/* Welcome */
.dash-welcome {
    position:relative; overflow:hidden; border-radius:1rem;
    background:linear-gradient(135deg,#3b82f6 0%,#6366f1 100%);
    padding:1.75rem 1.5rem; box-shadow:0 4px 20px rgba(59,130,246,.3);
}
.dash-welcome-content { position:relative; z-index:1; }
.dash-welcome h1 { font-size:1.25rem; font-weight:700; color:#fff; margin:0 0 .25rem; }
.dash-welcome p { font-size:.875rem; color:rgba(255,255,255,.85); margin:0; }

/* Card */
.dash-card { border-radius:.875rem; border:1px solid #e5e7eb; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,.06); overflow:hidden; }
.dash-card-head { padding:1rem 1.25rem; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; }
.dash-card-head-title { font-size:1rem; font-weight:600; color:#111827; }
.dash-card-body { padding:0; }

/* Table */
.dash-table { width:100%; border-collapse:collapse; }
.dash-table thead tr { background:#f9fafb; }
.dash-table th { padding:.75rem 1rem; text-align:left; font-size:.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; }
.dash-table tbody tr { border-top:1px solid #f3f4f6; }
.dash-table tbody tr:hover { background:#fafafa; }
.dash-table td { padding:.75rem 1rem; font-size:.875rem; }
.dash-table td:first-child { font-weight:600; color:#111827; }

/* Status Badge */
.dash-badge { border-radius:9999px; padding:.2rem .625rem; font-size:.7rem; font-weight:600; }
.dash-badge-gray { background:#f3f4f6; color:#374151; }
.dash-badge-yellow { background:#fef3c7; color:#92400e; }
.dash-badge-blue { background:#dbeafe; color:#1e40af; }
.dash-badge-green { background:#dcfce7; color:#166534; }
.dash-badge-red { background:#fee2e2; color:#991b1b; }
.dash-badge-orange { background:#ffedd5; color:#9a3412; }
.dash-badge-emerald { background:#d1fae5; color:#065f46; }

/* Actions */
.dash-actions-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; }
.dash-action-card { border-radius:.875rem; border:1px solid #e5e7eb; background:#fff; padding:1rem; text-decoration:none; transition:all .15s; display:block; }
.dash-action-card:hover { border-color:#6366f1; box-shadow:0 4px 12px rgba(99,102,241,.15); transform:translateY(-2px); }
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
.dash-empty-text { font-size:.875rem; color:#6b7280; margin-bottom:.75rem; }

/* Dark mode */
.dark .dash-card { background:#1f2937; border-color:#374151; }
.dark .dash-card-head { border-color:#374151; }
.dark .dash-card-head-title { color:#f9fafb; }
.dark .dash-table thead tr { background:#111827; }
.dark .dash-table th { color:#9ca3af; }
.dark .dash-table td { color:#9ca3af; }
.dark .dash-table td:first-child { color:#f9fafb; }
.dark .dash-table tbody tr { border-color:#374151; }
.dark .dash-action-card { background:#1f2937; border-color:#374151; }
.dark .dash-action-title { color:#f9fafb; }
.dark .dash-action-desc { color:#9ca3af; }
</style>

<div class="dash-wrap">
    {{-- Welcome --}}
    <div class="dash-welcome">
        <div class="dash-welcome-content">
            <h1>Welcome back, {{ auth()->user()->name }}!</h1>
            <p>Create and manage your purchase requests, track approval status in real-time.</p>
        </div>
    </div>

    {{-- Recent Requests --}}
    <div class="dash-card">
        <div class="dash-card-head">
            <span class="dash-card-head-title">Recent Requests</span>
            <a href="{{ route('filament.admin.resources.purchase-requests.index') }}"
               class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                View All →
            </a>
        </div>
        <div class="dash-card-body">
            @if($recentPrs->count() > 0)
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>PR Number</th>
                            <th>Purpose</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Current Approver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentPrs as $pr)
                        <tr>
                            <td>{{ $pr->pr_number }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($pr->purpose, 40) }}</td>
                            <td>Rp {{ number_format((float)$pr->total_amount, 0, ',', '.') }}</td>
                            <td>
                                @if($pr->status === 'draft')
                                    <span class="dash-badge dash-badge-gray">Draft</span>
                                @elseif($pr->status === 'waiting_approval')
                                    <span class="dash-badge dash-badge-yellow">Waiting Approval</span>
                                @elseif($pr->status === 'in_review')
                                    <span class="dash-badge dash-badge-blue">In Review</span>
                                @elseif($pr->status === 'approved')
                                    <span class="dash-badge dash-badge-green">Approved</span>
                                @elseif($pr->status === 'rejected')
                                    <span class="dash-badge dash-badge-red">Rejected</span>
                                @elseif($pr->status === 'need_revision')
                                    <span class="dash-badge dash-badge-orange">Need Revision</span>
                                @elseif($pr->status === 'completed')
                                    <span class="dash-badge dash-badge-emerald">Completed</span>
                                @else
                                    <span class="dash-badge dash-badge-gray">{{ \Illuminate\Support\Str::title(str_replace('_', ' ', $pr->status)) }}</span>
                                @endif
                            </td>
                            <td>{{ $pr->currentApprover?->name ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="dash-empty">
                    <svg class="dash-empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="dash-empty-text">No purchase requests yet</p>
                    <a href="{{ route('filament.admin.resources.purchase-requests.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors">
                        Create Your First PR
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="dash-actions-grid">
        <a href="{{ route('filament.admin.resources.purchase-requests.create') }}" class="dash-action-card">
            <div class="dash-action-header">
                <div class="dash-action-icon dash-action-icon-blue">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </div>
                <span class="dash-action-title">New Purchase Request</span>
            </div>
            <p class="dash-action-desc">Create a new PR</p>
        </a>

        <a href="{{ route('filament.admin.resources.purchase-requests.index') }}" class="dash-action-card">
            <div class="dash-action-header">
                <div class="dash-action-icon dash-action-icon-purple">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="dash-action-title">My Requests</span>
            </div>
            <p class="dash-action-desc">View all my PRs</p>
        </a>

        <a href="{{ route('filament.admin.resources.purchase-requests.index', ['status' => 'approved']) }}" class="dash-action-card">
            <div class="dash-action-header">
                <div class="dash-action-icon dash-action-icon-green">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="dash-action-title">Approved PRs</span>
            </div>
            <p class="dash-action-desc">View approved requests</p>
        </a>
    </div>
</div>
</x-filament-panels::page>
