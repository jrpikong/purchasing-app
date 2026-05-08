<x-filament-panels::page>
<style>
/* Dashboard Styles */
.dash-wrap { display:flex; flex-direction:column; gap:1.5rem; }

/* Hero */
.dash-hero {
    position:relative; overflow:hidden; border-radius:1rem;
    background:linear-gradient(135deg,#4f46e5 0%,#6d28d9 100%);
    padding:2rem 1.5rem; box-shadow:0 4px 20px rgba(79,70,229,.3);
}
.dash-hero-circle1 { position:absolute; right:-3rem; top:-3rem; width:12rem; height:12rem; border-radius:50%; background:rgba(255,255,255,.06); pointer-events:none; }
.dash-hero-circle2 { position:absolute; left:-2rem; bottom:-2rem; width:8rem; height:8rem; border-radius:50%; background:rgba(255,255,255,.06); pointer-events:none; }
.dash-hero-content { position:relative; z-index:1; }
.dash-hero h1 { font-size:1.5rem; font-weight:700; color:#fff; margin:0 0 .5rem; }
.dash-hero p { font-size:.9rem; color:rgba(255,255,255,.85); margin:0; }

/* Actions */
.dash-actions-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:1rem; }
.dash-action-card { border-radius:.875rem; border:1px solid #e5e7eb; background:#fff; padding:1rem; text-decoration:none; transition:all .15s; display:block; }
.dash-action-card:hover { border-color:#6366f1; box-shadow:0 4px 12px rgba(99,102,241,.15); transform:translateY(-2px); }
.dash-action-header { display:flex; align-items:center; gap:.75rem; margin-bottom:.5rem; }
.dash-action-icon { display:flex; align-items:center; justify-content:center; width:2.5rem; height:2.5rem; border-radius:.625rem; }
.dash-action-icon-blue { background:#dbeafe; color:#1d4ed8; }
.dash-action-icon-green { background:#dcfce7; color:#16a34a; }
.dash-action-icon-purple { background:#f3e8ff; color:#7c3aed; }
.dash-action-title { font-size:.875rem; font-weight:600; color:#111827; }
.dash-action-desc { font-size:.8rem; color:#6b7280; }

/* Dark mode */
.dark .dash-action-card { background:#1f2937; border-color:#374151; }
.dark .dash-action-title { color:#f9fafb; }
.dark .dash-action-desc { color:#9ca3af; }
</style>

<div class="dash-wrap">
    {{-- Hero --}}
    <div class="dash-hero">
        <div class="dash-hero-circle1"></div>
        <div class="dash-hero-circle2"></div>
        <div class="dash-hero-content">
            <h1>👑 Admin Dashboard</h1>
            <p>Manage purchase requests, configure approval flows, and oversee the entire purchasing system.</p>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="dash-actions-grid">
        <a href="{{ route('filament.admin.resources.purchase-requests.index') }}" class="dash-action-card">
            <div class="dash-action-header">
                <div class="dash-action-icon dash-action-icon-blue">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="dash-action-title">View All PRs</span>
            </div>
            <p class="dash-action-desc">Manage all purchase requests in the system</p>
        </a>

        <a href="{{ route('filament.admin.resources.purchase-requests.create') }}" class="dash-action-card">
            <div class="dash-action-header">
                <div class="dash-action-icon dash-action-icon-green">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                </div>
                <span class="dash-action-title">Create New PR</span>
            </div>
            <p class="dash-action-desc">Submit a new purchase request</p>
        </a>

        <a href="{{ route('filament.admin.resources.departments.index') }}" class="dash-action-card">
            <div class="dash-action-header">
                <div class="dash-action-icon dash-action-icon-purple">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 01-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 01-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 01-3 0m-9.75 0h9.75"/>
                    </svg>
                </div>
                <span class="dash-action-title">Manage Master Data</span>
            </div>
            <p class="dash-action-desc">Users, vendors, departments & flows</p>
        </a>
    </div>
</div>
</x-filament-panels::page>
