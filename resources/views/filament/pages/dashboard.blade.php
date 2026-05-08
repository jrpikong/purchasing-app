<x-filament-panels::page>
<style>
/* Dashboard Styles */
.dash-wrap { display:flex; flex-direction:column; gap:2rem; padding-bottom:2rem; }

/* Hero */
.dash-hero {
    position:relative; overflow:hidden; border-radius:1rem;
    background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 50%,#a855f7 100%);
    padding:2.5rem 2rem; box-shadow:0 8px 30px rgba(99,102,241,.3);
}
.dash-hero-circle1 { position:absolute; right:-4rem; top:-4rem; width:14rem; height:14rem; border-radius:50%; background:rgba(255,255,255,.08); pointer-events:none; }
.dash-hero-circle2 { position:absolute; left:-3rem; bottom:-3rem; width:10rem; height:10rem; border-radius:50%; background:rgba(255,255,255,.06); pointer-events:none; }
.dash-hero-circle3 { position:absolute; right:20%; bottom:-2rem; width:6rem; height:6rem; border-radius:50%; background:rgba(255,255,255,.04); pointer-events:none; }
.dash-hero-content { position:relative; z-index:1; display:flex; align-items:center; gap:1.5rem; }
.dash-hero-icon {
    display:flex; align-items:center; justify-content:center;
    width:4rem; height:4rem; border-radius:1rem;
    background:rgba(255,255,255,.2); backdrop-filter:blur(10px);
}
.dash-hero-icon svg { width:2rem; height:2rem; color:#fff; }
.dash-hero-text h1 { font-size:1.75rem; font-weight:700; color:#fff; margin:0 0 .5rem; }
.dash-hero-text p { font-size:.95rem; color:rgba(255,255,255,.85); margin:0; max-width:40rem; line-height:1.6; }

/* Stats Grid */
.dash-stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; }
.dash-stat-card {
    border-radius:.875rem; border:1px solid; padding:1.25rem;
    transition:all .2s; position:relative; overflow:hidden;
}
.dash-stat-card::before {
    content:''; position:absolute; top:0; right:0; width:80px; height:80px;
    border-radius:50%; background:currentColor; opacity:.05; pointer-events:none;
}
.dash-stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(0,0,0,.1); }
.dash-stat-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:.75rem; position:relative; }
.dash-stat-icon { display:flex; align-items:center; justify-content:center; width:2.5rem; height:2.5rem; border-radius:.625rem; }
.dash-stat-value { font-size:2rem; font-weight:700; margin-bottom:.25rem; position:relative; }
.dash-stat-label { font-size:.875rem; font-weight:500; position:relative; }

.dash-stat-blue { background:#eff6ff; border-color:#bfdbfe; }
.dash-stat-blue .dash-stat-icon { background:#3b82f6; color:#fff; }
.dash-stat-blue .dash-stat-value { color:#1d4ed8; }
.dash-stat-blue .dash-stat-label { color:#1e40af; }
.dash-stat-blue::before { color:#3b82f6; }

.dash-stat-yellow { background:#fef3c7; border-color:#fde68a; }
.dash-stat-yellow .dash-stat-icon { background:#f59e0b; color:#fff; }
.dash-stat-yellow .dash-stat-value { color:#b45309; }
.dash-stat-yellow .dash-stat-label { color:#92400e; }
.dash-stat-yellow::before { color:#f59e0b; }

.dash-stat-green { background:#f0fdf4; border-color:#bbf7d0; }
.dash-stat-green .dash-stat-icon { background:#22c55e; color:#fff; }
.dash-stat-green .dash-stat-value { color:#15803d; }
.dash-stat-green .dash-stat-label { color:#166534; }
.dash-stat-green::before { color:#22c55e; }

.dash-stat-red { background:#fef2f2; border-color:#fecaca; }
.dash-stat-red .dash-stat-icon { background:#ef4444; color:#fff; }
.dash-stat-red .dash-stat-value { color:#b91c1c; }
.dash-stat-red .dash-stat-label { color:#991b1b; }
.dash-stat-red::before { color:#ef4444; }

.dash-stat-orange { background:#ffedd5; border-color:#fed7aa; }
.dash-stat-orange .dash-stat-icon { background:#f97316; color:#fff; }
.dash-stat-orange .dash-stat-value { color:#c2410c; }
.dash-stat-orange .dash-stat-label { color:#9a3412; }
.dash-stat-orange::before { color:#f97316; }

.dash-stat-purple { background:#f5f3ff; border-color:#ddd6fe; }
.dash-stat-purple .dash-stat-icon { background:#8b5cf6; color:#fff; }
.dash-stat-purple .dash-stat-value { color:#6d28d9; }
.dash-stat-purple .dash-stat-label { color:#7c3aed; }
.dash-stat-purple::before { color:#8b5cf6; }

/* Quick Actions */
.dash-actions-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:1rem; }
.dash-action-card {
    border-radius:.875rem; border:1px solid #e5e7eb; background:#fff;
    padding:1.25rem; text-decoration:none; transition:all .2s; display:block;
    position:relative; overflow:hidden;
}
.dash-action-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:3px;
    background:linear-gradient(90deg,#6366f1,#8b5cf6); transform:scaleX(0);
    transition:transform .3s;
}
.dash-action-card:hover::before { transform:scaleX(1); }
.dash-action-card:hover {
    border-color:#6366f1; box-shadow:0 8px 25px rgba(99,102,241,.15);
    transform:translateY(-3px);
}
.dash-action-header { display:flex; align-items:center; gap:.875rem; margin-bottom:.75rem; }
.dash-action-icon { display:flex; align-items:center; justify-content:center; width:3rem; height:3rem; border-radius:.75rem; }
.dash-action-icon-lg-blue { background:#eff6ff; color:#1d4ed8; }
.dash-action-icon-lg-green { background:#f0fdf4; color:#16a34a; }
.dash-action-icon-lg-purple { background:#f5f3ff; color:#7c3aed; }
.dash-action-icon-lg-orange { background:#ffedd5; color:#ea580c; }
.dash-action-title { font-size:1rem; font-weight:600; color:#111827; }
.dash-action-desc { font-size:.875rem; color:#6b7280; line-height:1.5; }

/* Role Cards */
.dash-roles-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:1rem; margin-top:.5rem; }
.dash-role-card { border-radius:.875rem; border:1px solid; padding:1.25rem; position:relative; overflow:hidden; }
.dash-role-card::before {
    content:''; position:absolute; top:-50%; right:-50%; width:200%; height:200%;
    background:radial-gradient(circle,currentColor 0%,transparent 70%); opacity:.05;
    pointer-events:none;
}
.dash-role-header { display:flex; align-items:center; gap:.625rem; margin-bottom:.75rem; position:relative; }
.dash-role-emoji { font-size:1.5rem; }
.dash-role-name { font-size:.95rem; font-weight:600; }
.dash-role-desc { font-size:.875rem; line-height:1.6; }

.role-slate { background:#f8fafc; border-color:#e2e8f0; }
.role-slate .dash-role-name { color:#1e293b; }
.role-slate .dash-role-desc { color:#64748b; }

.role-blue { background:#eff6ff; border-color:#bfdbfe; }
.role-blue .dash-role-name { color:#1e3a8a; }
.role-blue .dash-role-desc { color:#1e40af; }

.role-teal { background:#f0fdfa; border-color:#99f6e4; }
.role-teal .dash-role-name { color:#134e4a; }
.role-teal .dash-role-desc { color:#0f766e; }

.role-cyan { background:#ecfeff; border-color:#a5f3fc; }
.role-cyan .dash-role-name { color:#164e63; }
.role-cyan .dash-role-desc { color:#0e7490; }

.role-green { background:#f0fdf4; border-color:#bbf7d0; }
.role-green .dash-role-name { color:#14532d; }
.role-green .dash-role-desc { color:#166534; }

.role-emerald { background:#ecfdf5; border-color:#6ee7b7; }
.role-emerald .dash-role-name { color:#064e3b; }
.role-emerald .dash-role-desc { color:#047857; }

.role-red { background:#fef2f2; border-color:#fecaca; }
.role-red .dash-role-name { color:#7f1d1d; }
.role-red .dash-role-desc { color:#991b1b; }

.role-amber { background:#fffbeb; border-color:#fde68a; }
.role-amber .dash-role-name { color:#78350f; }
.role-amber .dash-role-desc { color:#92400e; }

/* Dark mode */
.dark .dash-stat-blue { background:#1e3a5f; border-color:#1e40af; }
.dark .dash-stat-blue .dash-stat-value { color:#93c5fd; }
.dark .dash-stat-blue .dash-stat-label { color:#60a5fa; }
.dark .dash-stat-yellow { background:#451a03; border-color:#78350f; }
.dark .dash-stat-yellow .dash-stat-value { color:#fde68a; }
.dark .dash-stat-yellow .dash-stat-label { color:#fbbf24; }
.dark .dash-stat-green { background:#14532d; border-color:#166534; }
.dark .dash-stat-green .dash-stat-value { color:#6ee7b7; }
.dark .dash-stat-green .dash-stat-label { color:#22c55e; }
.dark .dash-stat-red { background:#450a0a; border-color:#7f1d1d; }
.dark .dash-stat-red .dash-stat-value { color:#fca5a5; }
.dark .dash-stat-red .dash-stat-label { color:#ef4444; }
.dark .dash-stat-orange { background:#431407; border-color:#9a3412; }
.dark .dash-stat-orange .dash-stat-value { color:#fdba74; }
.dark .dash-stat-orange .dash-stat-label { color:#f97316; }
.dark .dash-stat-purple { background:#4c1d95; border-color:#5b21b6; }
.dark .dash-stat-purple .dash-stat-value { color:#c4b5fd; }
.dark .dash-stat-purple .dash-stat-label { color:#a78bfa; }
.dark .dash-action-card { background:#1f2937; border-color:#374151; }
.dark .dash-action-title { color:#f9fafb; }
.dark .dash-action-desc { color:#9ca3af; }
</style>

<div class="dash-wrap">
    {{-- Hero Section --}}
    <div class="dash-hero">
        <div class="dash-hero-circle1"></div>
        <div class="dash-hero-circle2"></div>
        <div class="dash-hero-circle3"></div>
        <div class="dash-hero-content">
            <div class="dash-hero-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                </svg>
            </div>
            <div class="dash-hero-text">
                <h1>Purchase Request System</h1>
                <p>Sistem manajemen pengadaan barang & jasa dengan multi-level approval. Kelola purchase request dari pembuatan hingga penyelesaian dalam satu platform terintegrasi.</p>
            </div>
        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="dash-stats-grid">
        <div class="dash-stat-card dash-stat-blue">
            <div class="dash-stat-header">
                <div class="dash-stat-icon">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
            </div>
            <div class="dash-stat-value">{{ \App\Models\PurchaseRequest::count() }}</div>
            <div class="dash-stat-label">Total PRs</div>
        </div>

        <div class="dash-stat-card dash-stat-yellow">
            <div class="dash-stat-header">
                <div class="dash-stat-icon">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="dash-stat-value">{{ \App\Models\PurchaseRequest::whereIn('status', ['draft', 'waiting_approval', 'in_review', 'need_revision'])->count() }}</div>
            <div class="dash-stat-label">In Progress</div>
        </div>

        <div class="dash-stat-card dash-stat-green">
            <div class="dash-stat-header">
                <div class="dash-stat-icon">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="dash-stat-value">{{ \App\Models\PurchaseRequest::where('status', 'approved')->count() }}</div>
            <div class="dash-stat-label">Approved</div>
        </div>

        <div class="dash-stat-card dash-stat-red">
            <div class="dash-stat-header">
                <div class="dash-stat-icon">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="dash-stat-value">{{ \App\Models\PurchaseRequest::where('status', 'rejected')->count() }}</div>
            <div class="dash-stat-label">Rejected</div>
        </div>

        <div class="dash-stat-card dash-stat-purple">
            <div class="dash-stat-header">
                <div class="dash-stat-icon">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="dash-stat-value">{{ \App\Models\PurchaseRequest::where('status', 'completed')->count() }}</div>
            <div class="dash-stat-label">Completed</div>
        </div>

        <div class="dash-stat-card dash-stat-orange">
            <div class="dash-stat-header">
                <div class="dash-stat-icon">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                    </svg>
                </div>
            </div>
            <div class="dash-stat-value">{{ \App\Models\PurchaseRequest::where('status', 'need_revision')->count() }}</div>
            <div class="dash-stat-label">Need Revision</div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="dash-actions-grid">
        <a href="{{ route('filament.admin.resources.purchase-requests.index') }}" class="dash-action-card">
            <div class="dash-action-header">
                <div class="dash-action-icon dash-action-icon-lg-blue">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="dash-action-title">All Purchase Requests</span>
            </div>
            <p class="dash-action-desc">View and manage all purchase requests in the system</p>
        </a>

        <a href="{{ route('filament.admin.resources.purchase-requests.create') }}" class="dash-action-card">
            <div class="dash-action-header">
                <div class="dash-action-icon dash-action-icon-lg-green">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </div>
                <span class="dash-action-title">Create New PR</span>
            </div>
            <p class="dash-action-desc">Submit a new purchase request</p>
        </a>

        <a href="{{ route('filament.admin.resources.departments.index') }}" class="dash-action-card">
            <div class="dash-action-header">
                <div class="dash-action-icon dash-action-icon-lg-purple">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 01-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 01-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 01-3 0m-9.75 0h9.75"/>
                    </svg>
                </div>
                <span class="dash-action-title">Master Data</span>
            </div>
            <p class="dash-action-desc">Configure departments, users, vendors & approval flows</p>
        </a>

        <a href="{{ route('filament.admin.pages.user-guide') }}" class="dash-action-card">
            <div class="dash-action-header">
                <div class="dash-action-icon dash-action-icon-lg-orange">
                    <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v12a9.003 9.003 0 005.566-1.912 1.026 1.026 0 012.328-.877c.285-.24.563-.49.857-.726m1.414-2.048a1.125 1.125 0 00-1.376-1.376 8.967 8.967 0 00-2.292 2.292"/>
                    </svg>
                </div>
                <span class="dash-action-title">User Guide</span>
            </div>
            <p class="dash-action-desc">Documentation and how-to guides</p>
        </a>
    </div>

    {{-- Role-Based Quick Access --}}
    <div style="margin-top:1rem">
        <h3 style="font-size:1.125rem;font-weight:600;color:#111827;margin-bottom:1rem;">🚀 Quick Access by Role</h3>
        <div class="dash-roles-grid">
            <a href="{{ route('filament.admin.pages.requester-dashboard') }}" class="dash-role-card role-blue">
                <div class="dash-role-header">
                    <span class="dash-role-emoji">🛒</span>
                    <span class="dash-role-name">Requester Dashboard</span>
                </div>
                <p class="dash-role-desc">Create PRs, track status, manage submissions</p>
            </a>

            <a href="{{ route('filament.admin.pages.approver-dashboard') }}" class="dash-role-card role-teal">
                <div class="dash-role-header">
                    <span class="dash-role-emoji">✅</span>
                    <span class="dash-role-name">Approver Dashboard</span>
                </div>
                <p class="dash-role-desc">Review approvals, approve/reject PRs</p>
            </a>

            <a href="{{ route('filament.admin.pages.admin-dashboard') }}" class="dash-role-card role-emerald">
                <div class="dash-role-header">
                    <span class="dash-role-emoji">👑</span>
                    <span class="dash-role-name">Admin Dashboard</span>
                </div>
                <p class="dash-role-desc">System management & configuration</p>
            </a>
        </div>
    </div>
</div>
</x-filament-panels::page>
