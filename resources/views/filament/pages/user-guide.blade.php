<x-filament-panels::page>
<style>
/* =====================================================
   USER GUIDE — Custom Styles
   (inline agar tidak tergantung Tailwind build Filament)
===================================================== */
.ug-wrap { display:flex; flex-direction:column; gap:2.5rem; padding-bottom:4rem; }

/* ---- Hero ---- */
.ug-hero {
    position:relative; overflow:hidden; border-radius:1rem;
    background:linear-gradient(135deg,#4f46e5 0%,#6d28d9 100%);
    padding:2.5rem 2rem; box-shadow:0 4px 24px rgba(79,70,229,.35);
}
.ug-hero-circle1 { position:absolute; right:-4rem; top:-4rem; width:14rem; height:14rem; border-radius:50%; background:rgba(255,255,255,.06); pointer-events:none; }
.ug-hero-circle2 { position:absolute; left:-3rem; bottom:-3rem; width:10rem; height:10rem; border-radius:50%; background:rgba(255,255,255,.06); pointer-events:none; }
.ug-hero-eyebrow { display:flex; align-items:center; gap:.5rem; margin-bottom:.75rem; }
.ug-hero-eyebrow-icon { display:flex; align-items:center; justify-content:center; width:2.25rem; height:2.25rem; border-radius:.6rem; background:rgba(255,255,255,.18); }
.ug-hero-eyebrow-text { font-size:.7rem; font-weight:600; color:rgba(255,255,255,.75); letter-spacing:.1em; text-transform:uppercase; }
.ug-hero h1 { font-size:1.75rem; font-weight:700; color:#fff; margin:0 0 .5rem; }
.ug-hero p { font-size:.9rem; color:rgba(255,255,255,.85); max-width:42rem; margin:0 0 1.25rem; line-height:1.6; }
.ug-hero-badges { display:flex; flex-wrap:wrap; gap:.5rem; }
.ug-badge-dot { display:inline-flex; align-items:center; gap:.375rem; border-radius:9999px; background:rgba(255,255,255,.15); padding:.25rem .75rem; font-size:.7rem; font-weight:500; color:#fff; }
.ug-badge-dot span { width:.375rem; height:.375rem; border-radius:50%; }

/* ---- Section header ---- */
.ug-section-header { display:flex; align-items:center; gap:.75rem; margin-bottom:1.25rem; }
.ug-section-num { display:flex; align-items:center; justify-content:center; width:2.25rem; height:2.25rem; border-radius:.6rem; background:#4f46e5; color:#fff; font-weight:700; font-size:.8rem; flex-shrink:0; box-shadow:0 2px 8px rgba(79,70,229,.4); }
.ug-section-title { font-size:1.2rem; font-weight:700; color:var(--fg, #111827); }

/* ---- Card base ---- */
.ug-card { border-radius:.875rem; border:1px solid var(--border,#e5e7eb); background:var(--surface,#fff); box-shadow:0 1px 4px rgba(0,0,0,.06); overflow:hidden; }
.ug-card-header { padding:.875rem 1.25rem; border-bottom:1px solid var(--border,#e5e7eb); }
.ug-card-body { padding:1.25rem; }

/* ---- TOC ---- */
.ug-toc-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:.5rem; }
.ug-toc-link { display:flex; align-items:center; gap:.6rem; border-radius:.5rem; padding:.5rem .75rem; text-decoration:none; font-size:.83rem; color:#4b5563; transition:background .15s,color .15s; }
.ug-toc-link:hover { background:#eef2ff; color:#4f46e5; }
.ug-toc-link-num { display:flex; align-items:center; justify-content:center; width:1.5rem; height:1.5rem; border-radius:50%; background:#e0e7ff; font-size:.7rem; font-weight:700; color:#4f46e5; flex-shrink:0; }

/* ---- Role cards ---- */
.ug-roles-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1rem; }
.ug-role-card { border-radius:.875rem; border:1px solid; padding:1.1rem; }
.ug-role-card-top { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:.6rem; }
.ug-role-card-title { display:flex; align-items:center; gap:.5rem; }
.ug-role-card-emoji { font-size:1.4rem; }
.ug-role-card-name { font-weight:600; font-size:.9rem; }
.ug-role-badge { border-radius:9999px; padding:.2rem .6rem; font-size:.65rem; font-weight:600; }
.ug-role-desc { font-size:.8rem; color:#6b7280; margin-bottom:.75rem; line-height:1.5; }
.ug-role-perm { display:flex; align-items:flex-start; gap:.375rem; font-size:.75rem; color:#6b7280; margin-bottom:.25rem; }
.ug-role-perm svg { flex-shrink:0; margin-top:.1rem; }
.ug-perm-yes svg { color:#22c55e; }
.ug-perm-no  svg { color:#ef4444; }

/* color skins */
.role-slate  { background:#f8fafc; border-color:#cbd5e1; }
.role-blue   { background:#eff6ff; border-color:#bfdbfe; }
.role-teal   { background:#f0fdfa; border-color:#99f6e4; }
.role-cyan   { background:#ecfeff; border-color:#a5f3fc; }
.role-green  { background:#f0fdf4; border-color:#bbf7d0; }
.role-emerald{ background:#ecfdf5; border-color:#6ee7b7; }
.role-red    { background:#fef2f2; border-color:#fecaca; }
.role-amber  { background:#fffbeb; border-color:#fde68a; }

/* ---- Flow diagram ---- */
.ug-flow-wrap { position:relative; }
.ug-flow-step { display:flex; gap:1rem; }
.ug-flow-icon { display:flex; align-items:center; justify-content:center; width:3.25rem; height:3.25rem; border-radius:.875rem; flex-shrink:0; box-shadow:0 3px 10px rgba(0,0,0,.15); }
.ug-flow-content { flex:1; padding-top:.2rem; }
.ug-flow-status-row { display:flex; align-items:center; gap:.5rem; margin-bottom:.3rem; }
.ug-status-pill { border-radius:9999px; padding:.2rem .65rem; font-size:.65rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
.ug-flow-actor { font-size:.7rem; color:#9ca3af; }
.ug-flow-title { font-weight:600; font-size:.9rem; color:var(--fg,#111827); margin-bottom:.3rem; }
.ug-flow-desc { font-size:.78rem; color:#6b7280; line-height:1.55; }
.ug-flow-tags { display:flex; flex-wrap:wrap; gap:.35rem; margin-top:.5rem; }
.ug-tag { border-radius:.25rem; background:#f3f4f6; padding:.15rem .45rem; font-size:.7rem; color:#6b7280; }
.ug-flow-arrow { display:flex; gap:1rem; }
.ug-flow-arrow-line { display:flex; width:3.25rem; justify-content:center; }
.ug-flow-arrow-inner { width:2px; height:1.5rem; background:#d1d5db; }
.ug-flow-arrow-label { display:flex; align-items:center; gap:.25rem; font-size:.72rem; color:#9ca3af; padding-top:.35rem; }

/* branch */
.ug-flow-branches { display:flex; gap:1rem; }
.ug-flow-branches-spacer { width:3.25rem; flex-shrink:0; }
.ug-flow-branches-grid { flex:1; display:grid; grid-template-columns:repeat(3,1fr); gap:.75rem; margin-top:.25rem; }
.ug-branch { border-radius:.875rem; border:2px solid; padding:.875rem; }
.ug-branch-dot-row { display:flex; align-items:center; gap:.4rem; margin-bottom:.4rem; }
.ug-branch-dot { width:.5rem; height:.5rem; border-radius:50%; flex-shrink:0; }
.ug-branch-label { font-size:.7rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; }
.ug-branch-desc { font-size:.75rem; color:#6b7280; line-height:1.5; }
.ug-branch-pills { margin-top:.5rem; display:flex; flex-direction:column; gap:.25rem; }
.ug-branch-pill { border-radius:.25rem; padding:.2rem .5rem; font-size:.68rem; font-weight:500; text-align:center; display:block; }

.branch-green  { background:#f0fdf4; border-color:#4ade80; }
.branch-red    { background:#fef2f2; border-color:#f87171; }
.branch-orange { background:#fff7ed; border-color:#fb923c; }
.branch-green  .ug-branch-dot  { background:#22c55e; }
.branch-red    .ug-branch-dot  { background:#ef4444; }
.branch-orange .ug-branch-dot  { background:#f97316; }
.branch-green  .ug-branch-label{ color:#16a34a; }
.branch-red    .ug-branch-label{ color:#dc2626; }
.branch-orange .ug-branch-label{ color:#ea580c; }
.branch-green  .ug-branch-pill { background:#dcfce7; color:#166534; }
.branch-red    .ug-branch-pill { background:#fee2e2; color:#991b1b; }
.branch-orange .ug-branch-pill { background:#ffedd5; color:#9a3412; }

.ug-cancelled-note { display:flex; gap:.625rem; align-items:flex-start; border-radius:.5rem; border:1.5px dashed #d1d5db; background:#f9fafb; padding:.75rem 1rem; margin-top:.75rem; font-size:.78rem; color:#6b7280; line-height:1.5; }
.ug-cancelled-note svg { flex-shrink:0; color:#9ca3af; margin-top:.1rem; }
.ug-cancelled-note strong { color:#374151; }

/* ---- Guide steps ---- */
.ug-guide-card { border-radius:.875rem; border:1px solid; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.ug-guide-header { display:flex; align-items:center; gap:.75rem; padding:.875rem 1.25rem; border-bottom:1px solid; }
.ug-guide-header-emoji { font-size:1.25rem; }
.ug-guide-header-title { font-weight:600; font-size:.9rem; color:var(--fg,#111827); }
.ug-guide-body { padding:1.25rem; }
.ug-steps { display:flex; flex-direction:column; gap:1rem; }
.ug-step { display:flex; gap:.75rem; }
.ug-step-num { display:flex; align-items:center; justify-content:center; width:1.5rem; height:1.5rem; border-radius:50%; font-size:.7rem; font-weight:700; flex-shrink:0; margin-top:.15rem; }
.ug-step-title { font-size:.85rem; font-weight:600; color:var(--fg,#111827); margin-bottom:.2rem; }
.ug-step-desc { font-size:.8rem; color:#6b7280; line-height:1.6; }

.guide-slate  { border-color:#e2e8f0; }
.guide-slate  .ug-guide-header { background:#f8fafc; border-color:#e2e8f0; }
.guide-slate  .ug-step-num { background:#f1f5f9; color:#475569; }
.guide-blue   { border-color:#bfdbfe; }
.guide-blue   .ug-guide-header { background:#eff6ff; border-color:#bfdbfe; }
.guide-blue   .ug-step-num { background:#dbeafe; color:#1d4ed8; }
.guide-green  { border-color:#bbf7d0; }
.guide-green  .ug-guide-header { background:#f0fdf4; border-color:#bbf7d0; }
.guide-green  .ug-step-num { background:#dcfce7; color:#15803d; }

/* ---- Status table ---- */
.ug-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.ug-table thead tr { background:#f9fafb; }
.ug-table th { padding:.75rem 1rem; text-align:left; font-size:.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; }
.ug-table tbody tr { border-top:1px solid #f3f4f6; }
.ug-table tbody tr:hover { background:#fafafa; }
.ug-table td { padding:.75rem 1rem; color:#6b7280; vertical-align:middle; }
.ug-table td:first-child { white-space:nowrap; }

/* ---- Approval flow tiers ---- */
.ug-tiers { display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:1.25rem; }
.ug-tier { border-radius:.875rem; border:2px solid; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); }
.ug-tier-head { padding:.875rem 1rem; }
.ug-tier-head-row { display:flex; align-items:center; justify-content:space-between; margin-bottom:.25rem; }
.ug-tier-head-name { font-weight:700; font-size:.85rem; color:#fff; }
.ug-tier-head-badge { border-radius:9999px; background:rgba(255,255,255,.2); padding:.15rem .6rem; font-size:.68rem; font-weight:500; color:#fff; }
.ug-tier-head-amount { font-size:.75rem; color:rgba(255,255,255,.8); }
.ug-tier-body { padding:.875rem; }
.ug-tier-levels { display:flex; flex-direction:column; gap:.4rem; }
.ug-tier-level { display:flex; align-items:center; gap:.5rem; border-radius:.5rem; border:1px solid; padding:.5rem .75rem; }
.ug-tier-level-num { display:flex; align-items:center; justify-content:center; width:1.4rem; height:1.4rem; border-radius:50%; font-size:.68rem; font-weight:700; color:#fff; flex-shrink:0; }
.ug-tier-level-name { font-size:.78rem; font-weight:600; color:var(--fg,#111827); }
.ug-tier-level-sub { font-size:.68rem; color:#9ca3af; }
.ug-tier-level-final { margin-left:auto; font-size:.68rem; font-weight:600; }
.ug-tier-arrow-wrap { display:flex; justify-content:center; }
.ug-tier-arrow-wrap svg { color:#d1d5db; }
.ug-tier-note { display:flex; align-items:center; gap:.375rem; margin-top:.6rem; font-size:.72rem; color:#6b7280; }

.tier-teal   { border-color:#5eead4; }
.tier-teal   .ug-tier-head { background:linear-gradient(135deg,#0d9488,#0f766e); }
.tier-teal   .ug-tier-level { background:#f0fdfa; border-color:#99f6e4; }
.tier-teal   .ug-tier-level-num { background:#0d9488; }
.tier-teal   .ug-tier-level-final { color:#0d9488; }

.tier-blue   { border-color:#93c5fd; }
.tier-blue   .ug-tier-head { background:linear-gradient(135deg,#2563eb,#1d4ed8); }
.tier-blue   .ug-tier-level { background:#eff6ff; border-color:#bfdbfe; }
.tier-blue   .ug-tier-level-num { background:#2563eb; }
.tier-blue   .ug-tier-level-final { color:#2563eb; }

.tier-purple { border-color:#c4b5fd; }
.tier-purple .ug-tier-head { background:linear-gradient(135deg,#7c3aed,#6d28d9); }
.tier-purple .ug-tier-level { background:#f5f3ff; border-color:#ddd6fe; }
.tier-purple .ug-tier-level-num { background:#7c3aed; }
.tier-purple .ug-tier-level-final { color:#7c3aed; }

/* ---- Notif table ---- */
.ug-notif-table { width:100%; border-collapse:collapse; font-size:.82rem; }
.ug-notif-table thead tr { background:#f9fafb; }
.ug-notif-table th { padding:.75rem 1rem; text-align:left; font-size:.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.06em; }
.ug-notif-table tbody tr { border-top:1px solid #f3f4f6; }
.ug-notif-table tbody tr:hover { background:#fafafa; }
.ug-notif-table td { padding:.75rem 1rem; color:#6b7280; }
.ug-notif-dot { display:inline-block; width:.375rem; height:.375rem; border-radius:50%; background:#6366f1; margin-right:.35rem; vertical-align:middle; }

/* ---- Alert box ---- */
.ug-alert { display:flex; gap:.75rem; align-items:flex-start; border-radius:.625rem; border:1px solid; padding:1rem; }
.ug-alert-icon { flex-shrink:0; margin-top:.05rem; }
.ug-alert-title { font-size:.83rem; font-weight:600; margin-bottom:.2rem; }
.ug-alert-body  { font-size:.8rem; line-height:1.55; }
.ug-alert-amber { background:#fffbeb; border-color:#fde68a; }
.ug-alert-amber .ug-alert-icon svg { color:#f59e0b; }
.ug-alert-amber .ug-alert-title { color:#92400e; }
.ug-alert-amber .ug-alert-body  { color:#b45309; }
.ug-alert-blue  { background:#eff6ff; border-color:#bfdbfe; }
.ug-alert-blue  .ug-alert-icon svg { color:#3b82f6; }
.ug-alert-blue  .ug-alert-title { color:#1e40af; }
.ug-alert-blue  .ug-alert-body  { color:#1d4ed8; }

/* ---- FAQ ---- */
.ug-faq-list { display:flex; flex-direction:column; gap:.625rem; }
.ug-faq-item { border-radius:.875rem; border:1px solid #e5e7eb; background:#fff; overflow:hidden; }
.ug-faq-item-inner { display:flex; align-items:flex-start; gap:.75rem; padding:1rem 1.1rem; }
.ug-faq-emoji { font-size:1.1rem; flex-shrink:0; margin-top:.1rem; }
.ug-faq-q { font-size:.85rem; font-weight:600; color:var(--fg,#111827); margin-bottom:.35rem; }
.ug-faq-a { font-size:.8rem; color:#6b7280; line-height:1.6; }

/* ---- Footer ---- */
.ug-footer { border-radius:.875rem; border:1px solid #e5e7eb; background:#f9fafb; padding:1.25rem; text-align:center; }
.ug-footer p { font-size:.82rem; color:#6b7280; margin-bottom:.25rem; }
.ug-footer p strong { color:#374151; }
.ug-footer small { font-size:.72rem; color:#9ca3af; }

/* ---- Status badges ---- */
.sb { display:inline-flex; border-radius:9999px; padding:.2rem .65rem; font-size:.7rem; font-weight:600; }
.sb-gray   { background:#f3f4f6; color:#374151; }
.sb-amber  { background:#fef3c7; color:#92400e; }
.sb-blue   { background:#dbeafe; color:#1e40af; }
.sb-green  { background:#dcfce7; color:#166534; }
.sb-red    { background:#fee2e2; color:#991b1b; }
.sb-orange { background:#ffedd5; color:#9a3412; }
.sb-emerald{ background:#d1fae5; color:#065f46; }
.sb-slate  { background:#f1f5f9; color:#475569; }

/* ---- Scroll margin ---- */
[id^="section-"] { scroll-margin-top:5rem; }

/* ---- Dark mode overrides (Filament dark) ---- */
.dark .ug-card       { background:#1f2937; border-color:#374151; }
.dark .ug-card-header{ border-color:#374151; }
.dark .ug-section-title { color:#f9fafb; }
.dark .ug-flow-title  { color:#f9fafb; }
.dark .ug-step-title  { color:#f9fafb; }
.dark .ug-role-card-name { color:#f9fafb; }
.dark .ug-toc-link   { color:#9ca3af; }
.dark .ug-toc-link:hover { background:#312e81; color:#a5b4fc; }
.dark .ug-table tbody tr { border-color:#374151; }
.dark .ug-table thead tr { background:#111827; }
.dark .ug-table th   { color:#9ca3af; }
.dark .ug-table td:not(:first-child) { color:#9ca3af; }
.dark .ug-notif-table thead tr { background:#111827; }
.dark .ug-notif-table th { color:#9ca3af; }
.dark .ug-notif-table tbody tr { border-color:#374151; }
.dark .ug-notif-table td { color:#9ca3af; }
.dark .ug-faq-item   { background:#1f2937; border-color:#374151; }
.dark .ug-faq-q      { color:#f9fafb; }
.dark .ug-faq-a      { color:#9ca3af; }
.dark .ug-footer     { background:#1f2937; border-color:#374151; }
.dark .ug-footer p   { color:#9ca3af; }
.dark .ug-footer p strong { color:#f9fafb; }
.dark .ug-footer small { color:#6b7280; }
.dark .ug-tag        { background:#374151; color:#9ca3af; }
.dark .ug-cancelled-note { background:#111827; border-color:#374151; }
.dark .ug-guide-card { border-color:#374151; }
.dark .guide-slate   { border-color:#374151; }
.dark .guide-slate   .ug-guide-header { background:#111827; border-color:#374151; }
.dark .guide-blue    { border-color:#1e3a5f; }
.dark .guide-blue    .ug-guide-header { background:#1e3a5f; border-color:#1e40af; }
.dark .guide-green   { border-color:#14532d; }
.dark .guide-green   .ug-guide-header { background:#14532d; border-color:#166534; }
.dark .ug-tier-level-name { color:#f9fafb; }
.dark .sb-gray   { background:#374151; color:#e5e7eb; }
.dark .sb-amber  { background:#451a03; color:#fde68a; }
.dark .sb-blue   { background:#1e3a5f; color:#93c5fd; }
.dark .sb-green  { background:#14532d; color:#6ee7b7; }
.dark .sb-red    { background:#450a0a; color:#fca5a5; }
.dark .sb-orange { background:#431407; color:#fdba74; }
.dark .sb-emerald{ background:#064e3b; color:#6ee7b7; }
.dark .sb-slate  { background:#1e293b; color:#94a3b8; }
.dark .ug-alert-amber { background:#1c1200; border-color:#78350f; }
.dark .ug-alert-amber .ug-alert-title { color:#fde68a; }
.dark .ug-alert-amber .ug-alert-body  { color:#fbbf24; }
.dark .ug-alert-blue  { background:#0f172a; border-color:#1e40af; }
.dark .ug-alert-blue  .ug-alert-title { color:#93c5fd; }
.dark .ug-alert-blue  .ug-alert-body  { color:#60a5fa; }
.dark .role-slate  { background:#1e293b; border-color:#334155; }
.dark .role-blue   { background:#0f172a; border-color:#1e3a5f; }
.dark .role-teal   { background:#042f2e; border-color:#134e4a; }
.dark .role-cyan   { background:#082f49; border-color:#164e63; }
.dark .role-green  { background:#052e16; border-color:#14532d; }
.dark .role-emerald{ background:#022c22; border-color:#064e3b; }
.dark .role-red    { background:#2d0a0a; border-color:#450a0a; }
.dark .role-amber  { background:#1c1200; border-color:#451a03; }
.dark .ug-toc-link-num { background:#312e81; color:#a5b4fc; }
</style>

<div class="ug-wrap">

    {{-- ===================== HERO ===================== --}}
    <div class="ug-hero">
        <div class="ug-hero-circle1"></div>
        <div class="ug-hero-circle2"></div>
        <div style="position:relative;z-index:1">
            <div class="ug-hero-eyebrow">
                <div class="ug-hero-eyebrow-icon">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                </div>
                <span class="ug-hero-eyebrow-text">Sistem Purchasing</span>
            </div>
            <h1>Panduan Penggunaan Sistem</h1>
            <p>Dokumen ini menjelaskan cara penggunaan <strong>Purchase Request System</strong> — mulai dari pembuatan pengajuan, proses persetujuan bertingkat, hingga penyelesaian pengadaan barang &amp; jasa.</p>
            <div class="ug-hero-badges">
                <span class="ug-badge-dot"><span style="background:#4ade80"></span>Versi 1.0</span>
                <span class="ug-badge-dot"><span style="background:#60a5fa"></span>Multi-level Approval</span>
            </div>
        </div>
    </div>

    {{-- ===================== TOC ===================== --}}
    <div class="ug-card">
        <div class="ug-card-header">
            <div style="display:flex;align-items:center;gap:.5rem">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6366f1"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                <span style="font-weight:600;font-size:.9rem;color:var(--fg,#111827)">Daftar Isi</span>
            </div>
        </div>
        <div class="ug-card-body">
            <div class="ug-toc-grid">
                @foreach([
                    ['1','Peran Pengguna (User Roles)'],
                    ['2','Alur Proses (Flow Diagram)'],
                    ['3','Panduan per Peran'],
                    ['4','Status Purchase Request'],
                    ['5','Alur Persetujuan Bertingkat'],
                    ['6','Notifikasi & Email'],
                    ['7','Tips & FAQ'],
                ] as [$n,$label])
                <a href="#section-{{ $n }}" class="ug-toc-link">
                    <span class="ug-toc-link-num">{{ $n }}</span>
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===================== 1. USER ROLES ===================== --}}
    <div id="section-1">
        <div class="ug-section-header">
            <div class="ug-section-num">1</div>
            <h2 class="ug-section-title">Peran Pengguna (User Roles)</h2>
        </div>
        <p style="font-size:.83rem;color:#6b7280;margin:0 0 1.25rem;line-height:1.6">Sistem ini memiliki <strong>8 peran</strong> dengan hak akses yang berbeda. Setiap pengguna hanya dapat melakukan aksi yang sesuai dengan perannya.</p>

        <div class="ug-roles-grid">
            @foreach([
                ['👤','Requester','role-slate','Membuat dan mengajukan Purchase Request.',['Buat PR baru','Edit PR (Draft/Revisi)','Submit PR untuk approval','Lihat status PR miliknya'],['Approve/Reject PR','Kelola master data']],
                ['🛡️','Admin','role-blue','Mengelola sistem, assign PIC, dan mengarahkan PR ke approver.',['Lihat semua PR','Assign PIC ke PR','Kirim PR ke approver','Kelola User, Vendor, Departemen','Mark PR sebagai Completed'],['Approve sebagai pejabat keuangan']],
                ['📋','Section Head','role-teal','Approver Level 1 — persetujuan pengajuan s/d Rp 10 juta.',['Approve / Reject / Revisi PR','Lihat PR yang di-assign','Aksi via email (token)'],['Kelola master data','Assign PIC']],
                ['🏢','Division Head','role-cyan','Approver Level 2 — persetujuan Rp 10–50 juta.',['Approve / Reject / Revisi PR','Lihat PR yang di-assign'],['Kelola master data']],
                ['💰','Finance Admin','role-green','Approver Level 3 — persetujuan aspek keuangan untuk PR > Rp 50 juta.',['Approve / Reject / Revisi PR','Review anggaran & vendor'],['Approve tanpa Division Head terlebih dahulu']],
                ['🏦','Treasurer','role-emerald','Approver Level 4 (final) — otorisasi tertinggi untuk pengajuan besar.',['Approve / Reject PR (final)','Lihat semua PR yang menunggu'],['Edit data master']],
                ['⚙️','Super Admin','role-red','Akses penuh ke seluruh sistem tanpa batasan.',['Semua aksi di sistem','Konfigurasi Approval Flow','Hapus/edit semua data'],[]],
                ['🔍','Approver','role-amber','Approver umum — digunakan saat tidak ada role struktural yang cocok.',['Approve / Reject PR yang di-assign'],['Kelola master data']],
            ] as [$emoji,$name,$skin,$desc,$can,$cant])
            <div class="ug-role-card {{ $skin }}">
                <div class="ug-role-card-top">
                    <div class="ug-role-card-title">
                        <span class="ug-role-card-emoji">{{ $emoji }}</span>
                        <span class="ug-role-card-name">{{ $name }}</span>
                    </div>
                    <span class="ug-role-badge" style="background:rgba(0,0,0,.07);color:#374151;font-size:.65rem">Role</span>
                </div>
                <p class="ug-role-desc">{{ $desc }}</p>
                @foreach($can as $c)
                <div class="ug-role-perm ug-perm-yes">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    {{ $c }}
                </div>
                @endforeach
                @foreach($cant as $c)
                <div class="ug-role-perm ug-perm-no">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    {{ $c }}
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>

    {{-- ===================== 2. FLOW DIAGRAM ===================== --}}
    <div id="section-2">
        <div class="ug-section-header">
            <div class="ug-section-num">2</div>
            <h2 class="ug-section-title">Alur Proses (Flow Diagram)</h2>
        </div>

        <div class="ug-card">
            <div class="ug-card-header" style="background:#f9fafb">
                <span style="font-size:.83rem;color:#374151;font-weight:500">Purchase Request — Lifecycle lengkap dari pembuatan hingga penyelesaian</span>
            </div>
            <div class="ug-card-body">
                <div class="ug-flow-wrap">

                    {{-- Step 1: Create --}}
                    <div class="ug-flow-step" style="padding-bottom:.25rem">
                        <div class="ug-flow-icon" style="background:#475569">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </div>
                        <div class="ug-flow-content" style="padding-bottom:1.25rem">
                            <div class="ug-flow-status-row">
                                <span class="ug-status-pill sb-gray">DRAFT</span>
                                <span class="ug-flow-actor">oleh Requester</span>
                            </div>
                            <div class="ug-flow-title">Buat Purchase Request</div>
                            <div class="ug-flow-desc">Requester mengisi form PR: tujuan pembelian, jumlah, prioritas, vendor yang diinginkan, tanggal kebutuhan, dan lampiran dokumen pendukung (quotation, spesifikasi).</div>
                            <div class="ug-flow-tags">
                                @foreach(['Tujuan Pembelian','Total Amount','Vendor','Tanggal Butuh','Prioritas','Lampiran'] as $f)
                                <span class="ug-tag">{{ $f }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Arrow --}}
                    <div class="ug-flow-arrow" style="padding-bottom:.25rem">
                        <div class="ug-flow-arrow-line"><div class="ug-flow-arrow-inner"></div></div>
                        <div class="ug-flow-arrow-label">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 16l-6-6h12z"/></svg>
                            Submit untuk approval
                        </div>
                    </div>

                    {{-- Step 2: Waiting --}}
                    <div class="ug-flow-step" style="padding-bottom:.25rem">
                        <div class="ug-flow-icon" style="background:#f59e0b">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="ug-flow-content" style="padding-bottom:1.25rem">
                            <div class="ug-flow-status-row">
                                <span class="ug-status-pill sb-amber">WAITING APPROVAL</span>
                                <span class="ug-flow-actor">notifikasi → Admin</span>
                            </div>
                            <div class="ug-flow-title">Menunggu Tindakan Admin</div>
                            <div class="ug-flow-desc">Sistem mengirim notifikasi ke semua Admin. Admin dapat assign PIC (opsional) lalu mengirimkan PR ke approver yang sesuai berdasarkan department &amp; jumlah.</div>
                        </div>
                    </div>

                    {{-- Arrow --}}
                    <div class="ug-flow-arrow" style="padding-bottom:.25rem">
                        <div class="ug-flow-arrow-line"><div class="ug-flow-arrow-inner"></div></div>
                        <div class="ug-flow-arrow-label">
                            <svg width="10" height="10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 16l-6-6h12z"/></svg>
                            Email approval link → Approver
                        </div>
                    </div>

                    {{-- Step 3: Review --}}
                    <div class="ug-flow-step" style="padding-bottom:.25rem">
                        <div class="ug-flow-icon" style="background:#2563eb">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="ug-flow-content" style="padding-bottom:1rem">
                            <div class="ug-flow-status-row">
                                <span class="ug-status-pill sb-blue">REVIEW</span>
                                <span class="ug-flow-actor">oleh Approver</span>
                            </div>
                            <div class="ug-flow-title">Proses Persetujuan</div>
                            <div class="ug-flow-desc">Approver menerima email berisi link approval (berlaku 7 hari). Approver membuka link, mereview detail PR, lalu memilih tindakan:</div>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.625rem;margin-top:.75rem">
                                <div style="border-radius:.6rem;border:1px solid #bbf7d0;background:#f0fdf4;padding:.75rem;text-align:center">
                                    <div style="font-size:1.2rem;margin-bottom:.2rem">✅</div>
                                    <div style="font-size:.75rem;font-weight:700;color:#16a34a;margin-bottom:.2rem">Approve</div>
                                    <div style="font-size:.7rem;color:#6b7280">Lanjut ke level berikutnya atau selesai</div>
                                </div>
                                <div style="border-radius:.6rem;border:1px solid #fecaca;background:#fef2f2;padding:.75rem;text-align:center">
                                    <div style="font-size:1.2rem;margin-bottom:.2rem">❌</div>
                                    <div style="font-size:.75rem;font-weight:700;color:#dc2626;margin-bottom:.2rem">Reject</div>
                                    <div style="font-size:.7rem;color:#6b7280">PR ditolak + alasan penolakan</div>
                                </div>
                                <div style="border-radius:.6rem;border:1px solid #fed7aa;background:#fff7ed;padding:.75rem;text-align:center">
                                    <div style="font-size:1.2rem;margin-bottom:.2rem">✏️</div>
                                    <div style="font-size:.75rem;font-weight:700;color:#ea580c;margin-bottom:.2rem">Revisi</div>
                                    <div style="font-size:.7rem;color:#6b7280">PR dikembalikan untuk diperbaiki</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Branches --}}
                    <div class="ug-flow-branches" style="margin-top:.5rem">
                        <div class="ug-flow-branches-spacer"></div>
                        <div class="ug-flow-branches-grid">
                            <div class="ug-branch branch-green">
                                <div class="ug-branch-dot-row">
                                    <div class="ug-branch-dot"></div>
                                    <span class="ug-branch-label">Approved</span>
                                </div>
                                <p class="ug-branch-desc">Notifikasi dikirim ke Requester. Admin mark PR sebagai <strong>Completed</strong> setelah pengadaan selesai.</p>
                                <div class="ug-branch-pills">
                                    <span class="ug-branch-pill">→ APPROVED</span>
                                    <span class="ug-branch-pill">→ COMPLETED</span>
                                </div>
                            </div>
                            <div class="ug-branch branch-red">
                                <div class="ug-branch-dot-row">
                                    <div class="ug-branch-dot"></div>
                                    <span class="ug-branch-label">Rejected</span>
                                </div>
                                <p class="ug-branch-desc">PR ditolak. Alasan penolakan tercatat. Notifikasi dikirim ke Requester. PR tidak bisa diajukan ulang.</p>
                                <div class="ug-branch-pills">
                                    <span class="ug-branch-pill">→ REJECTED (final)</span>
                                </div>
                            </div>
                            <div class="ug-branch branch-orange">
                                <div class="ug-branch-dot-row">
                                    <div class="ug-branch-dot"></div>
                                    <span class="ug-branch-label">Need Revision</span>
                                </div>
                                <p class="ug-branch-desc">Requester memperbaiki PR sesuai catatan, lalu submit ulang → kembali ke proses approval dari awal.</p>
                                <div class="ug-branch-pills">
                                    <span class="ug-branch-pill">→ NEED_REVISION → resubmit</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Cancelled --}}
                    <div class="ug-flow-branches" style="margin-top:.875rem">
                        <div class="ug-flow-branches-spacer"></div>
                        <div style="flex:1">
                            <div class="ug-cancelled-note">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                <span><strong>CANCELLED</strong> — Admin dapat membatalkan PR kapan saja selama belum berstatus Completed.</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ===================== 3. PANDUAN PER PERAN ===================== --}}
    <div id="section-3">
        <div class="ug-section-header">
            <div class="ug-section-num">3</div>
            <h2 class="ug-section-title">Panduan Per Peran</h2>
        </div>
        <div style="display:flex;flex-direction:column;gap:1.25rem">

            {{-- Requester --}}
            <div class="ug-guide-card guide-slate">
                <div class="ug-guide-header">
                    <span class="ug-guide-header-emoji">👤</span>
                    <span class="ug-guide-header-title">Requester — Cara Membuat Purchase Request</span>
                </div>
                <div class="ug-guide-body">
                    <ol class="ug-steps">
                        @foreach([
                            ['Login ke sistem','Masuk dengan akun Requester Anda di halaman login.'],
                            ['Buka menu Purchase Request','Klik menu <strong>Purchase Request</strong> di sidebar kiri, lalu klik tombol <strong>New Purchase Request</strong>.'],
                            ['Isi form dengan lengkap','Lengkapi semua field: <strong>Tujuan Pembelian</strong>, <strong>Total Amount</strong>, <strong>Tanggal Dibutuhkan</strong>, <strong>Prioritas</strong>, <strong>Vendor</strong> (opsional), dan <strong>Lampiran</strong> (PDF, max 10MB).'],
                            ['Simpan sebagai Draft','Klik <strong>Save</strong> untuk menyimpan sebagai Draft. PR dapat diedit kembali selama masih Draft.'],
                            ['Submit untuk Approval','Klik tombol <strong>Submit for Approval</strong> pada halaman detail PR. Status berubah menjadi Waiting Approval.'],
                            ['Pantau status PR','Status PR dapat dipantau di halaman daftar Purchase Request. Anda akan menerima email notifikasi saat PR disetujui atau ditolak.'],
                            ['Jika perlu revisi','Jika approver meminta revisi, PR kembali ke status <strong>Need Revision</strong>. Edit PR sesuai catatan, lalu submit ulang.'],
                        ] as $i => [$t,$d])
                        <li class="ug-step">
                            <span class="ug-step-num">{{ $i+1 }}</span>
                            <div>
                                <div class="ug-step-title">{{ $t }}</div>
                                <div class="ug-step-desc">{!! $d !!}</div>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            {{-- Admin --}}
            <div class="ug-guide-card guide-blue">
                <div class="ug-guide-header">
                    <span class="ug-guide-header-emoji">🛡️</span>
                    <span class="ug-guide-header-title">Admin — Mengelola Purchase Request</span>
                </div>
                <div class="ug-guide-body">
                    <ol class="ug-steps">
                        @foreach([
                            ['Terima notifikasi PR baru','Admin menerima notifikasi email saat Requester mengajukan PR baru.'],
                            ['Review PR yang masuk','Buka halaman <strong>Purchase Request</strong>, review detail PR termasuk tujuan, jumlah, dan lampiran.'],
                            ['Assign PIC (opsional)','Klik aksi <strong>Assign PIC</strong> untuk menentukan staf yang bertanggung jawab memproses pengadaan.'],
                            ['Kirim ke Approver','Klik <strong>Send for Approval</strong>. Sistem otomatis menentukan approver berdasarkan departemen dan jumlah PR. Email approval link dikirim ke approver.'],
                            ['Mark sebagai Completed','Setelah PR disetujui dan pengadaan selesai dilakukan, klik <strong>Mark as Completed</strong>.'],
                            ['Batalkan jika diperlukan','Gunakan aksi <strong>Cancel</strong> untuk membatalkan PR yang tidak dilanjutkan.'],
                            ['Kelola Master Data','Tambah/edit data <strong>User</strong>, <strong>Vendor</strong>, <strong>Department</strong>, dan <strong>Approval Flow</strong> di menu Master Data.'],
                        ] as $i => [$t,$d])
                        <li class="ug-step">
                            <span class="ug-step-num">{{ $i+1 }}</span>
                            <div>
                                <div class="ug-step-title">{{ $t }}</div>
                                <div class="ug-step-desc">{!! $d !!}</div>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            {{-- Approver --}}
            <div class="ug-guide-card guide-green">
                <div class="ug-guide-header">
                    <span class="ug-guide-header-emoji">✅</span>
                    <span class="ug-guide-header-title">Approver — Cara Menyetujui / Menolak PR</span>
                </div>
                <div class="ug-guide-body">
                    <ol class="ug-steps">
                        @foreach([
                            ['Terima email approval','Anda akan menerima email berisi link approval. Link ini berlaku selama <strong>7 hari</strong>.'],
                            ['Buka approval link','Klik link di email. Anda akan diarahkan ke halaman review PR (perlu login jika belum login).'],
                            ['Review detail PR','Periksa tujuan pembelian, jumlah, vendor, dan lampiran dokumen yang dilampirkan Requester.'],
                            ['Pilih tindakan','<strong>Approve</strong> — setujui PR (jika ada level berikutnya, PR diteruskan otomatis) &bull; <strong>Reject</strong> — tolak dengan mengisi alasan penolakan &bull; <strong>Need Revision</strong> — kembalikan ke Requester dengan catatan perbaikan'],
                            ['Submit keputusan','Klik tombol sesuai keputusan Anda. Sistem akan memproses dan mengirim notifikasi ke pihak terkait.'],
                            ['Via panel (alternatif)','Anda juga dapat approve langsung dari panel di menu <strong>Purchase Request</strong> → pilih PR dengan status Waiting Approval.'],
                        ] as $i => [$t,$d])
                        <li class="ug-step">
                            <span class="ug-step-num">{{ $i+1 }}</span>
                            <div>
                                <div class="ug-step-title">{{ $t }}</div>
                                <div class="ug-step-desc">{!! $d !!}</div>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== 4. STATUS TABLE ===================== --}}
    <div id="section-4">
        <div class="ug-section-header">
            <div class="ug-section-num">4</div>
            <h2 class="ug-section-title">Status Purchase Request</h2>
        </div>
        <div class="ug-card">
            <table class="ug-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Deskripsi</th>
                        <th>Aksi Tersedia</th>
                        <th>Siapa</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['sb-gray','DRAFT','PR dibuat, belum diajukan','Edit, Submit, Delete','Requester'],
                        ['sb-amber','WAITING APPROVAL','PR menunggu persetujuan approver','Approve, Reject, Revisi, Cancel','Approver / Admin'],
                        ['sb-blue','IN REVIEW','PR sedang dalam tinjauan','Forward ke Approver','Admin'],
                        ['sb-green','APPROVED','PR disetujui semua level approver','Mark as Completed, Cancel','Admin'],
                        ['sb-red','REJECTED','PR ditolak oleh approver','Lihat alasan','Read-only'],
                        ['sb-orange','NEED REVISION','PR dikembalikan untuk diperbaiki','Edit, Resubmit','Requester'],
                        ['sb-emerald','COMPLETED','Pengadaan selesai dilaksanakan','—','Read-only'],
                        ['sb-slate','CANCELLED','PR dibatalkan','—','Read-only'],
                    ] as [$cls,$status,$desc,$actions,$who])
                    <tr>
                        <td><span class="sb {{ $cls }}">{{ $status }}</span></td>
                        <td>{{ $desc }}</td>
                        <td style="font-size:.75rem">{{ $actions }}</td>
                        <td style="font-size:.75rem;font-weight:600">{{ $who }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== 5. APPROVAL TIERS ===================== --}}
    <div id="section-5">
        <div class="ug-section-header">
            <div class="ug-section-num">5</div>
            <h2 class="ug-section-title">Alur Persetujuan Bertingkat</h2>
        </div>
        <p style="font-size:.83rem;color:#6b7280;margin:0 0 1.25rem;line-height:1.6">Sistem menentukan siapa yang harus menyetujui PR secara otomatis berdasarkan <strong>jumlah (amount)</strong> dan <strong>departemen</strong> pemohon.</p>

        <div class="ug-tiers">

            {{-- Standard --}}
            <div class="ug-tier tier-teal">
                <div class="ug-tier-head">
                    <div class="ug-tier-head-row">
                        <span class="ug-tier-head-name">Standard Approval</span>
                        <span class="ug-tier-head-badge">1 Level</span>
                    </div>
                    <div class="ug-tier-head-amount">Rp 0 — Rp 10.000.000</div>
                </div>
                <div class="ug-tier-body">
                    <div class="ug-tier-levels">
                        <div class="ug-tier-level">
                            <span class="ug-tier-level-num">1</span>
                            <div>
                                <div class="ug-tier-level-name">Section Head</div>
                                <div class="ug-tier-level-sub">Final Approver</div>
                            </div>
                            <span class="ug-tier-level-final">Final</span>
                        </div>
                    </div>
                    <div class="ug-tier-note">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="#22c55e"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Proses paling cepat
                    </div>
                </div>
            </div>

            {{-- Management --}}
            <div class="ug-tier tier-blue">
                <div class="ug-tier-head">
                    <div class="ug-tier-head-row">
                        <span class="ug-tier-head-name">Management Approval</span>
                        <span class="ug-tier-head-badge">2 Level</span>
                    </div>
                    <div class="ug-tier-head-amount">Rp 10.000.001 — Rp 50.000.000</div>
                </div>
                <div class="ug-tier-body">
                    <div class="ug-tier-levels">
                        <div class="ug-tier-level">
                            <span class="ug-tier-level-num">1</span>
                            <div>
                                <div class="ug-tier-level-name">Section Head</div>
                                <div class="ug-tier-level-sub">Approval Level 1</div>
                            </div>
                        </div>
                        <div class="ug-tier-arrow-wrap">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div class="ug-tier-level">
                            <span class="ug-tier-level-num">2</span>
                            <div>
                                <div class="ug-tier-level-name">Division Head</div>
                                <div class="ug-tier-level-sub">Final Approver</div>
                            </div>
                            <span class="ug-tier-level-final">Final</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Executive --}}
            <div class="ug-tier tier-purple">
                <div class="ug-tier-head">
                    <div class="ug-tier-head-row">
                        <span class="ug-tier-head-name">Executive Approval</span>
                        <span class="ug-tier-head-badge">4 Level</span>
                    </div>
                    <div class="ug-tier-head-amount">Rp 50.000.001 ke atas</div>
                </div>
                <div class="ug-tier-body">
                    <div class="ug-tier-levels">
                        @foreach([['1','Section Head','Approval L1'],['2','Division Head','Approval L2'],['3','Finance Admin','Approval L3'],['4','Treasurer','Final Approver']] as [$lv,$rn,$rs])
                        <div class="ug-tier-level">
                            <span class="ug-tier-level-num">{{ $lv }}</span>
                            <div>
                                <div class="ug-tier-level-name">{{ $rn }}</div>
                                <div class="ug-tier-level-sub">{{ $rs }}</div>
                            </div>
                            @if($lv=='4')<span class="ug-tier-level-final">Final</span>@endif
                        </div>
                        @if($lv != '4')
                        <div class="ug-tier-arrow-wrap">
                            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        <div class="ug-alert ug-alert-amber" style="margin-top:1rem">
            <div class="ug-alert-icon">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </div>
            <div>
                <div class="ug-alert-title">Catatan Penting</div>
                <div class="ug-alert-body">Jika tidak ada <strong>Approval Flow</strong> yang cocok dengan departemen dan jumlah PR, sistem <strong>tidak dapat mengirim PR ke approver</strong>. Pastikan Admin sudah mengkonfigurasi Approval Flow yang sesuai di menu Master Data.</div>
            </div>
        </div>
    </div>

    {{-- ===================== 6. NOTIFIKASI ===================== --}}
    <div id="section-6">
        <div class="ug-section-header">
            <div class="ug-section-num">6</div>
            <h2 class="ug-section-title">Notifikasi &amp; Email</h2>
        </div>
        <div class="ug-card">
            <table class="ug-notif-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Penerima</th>
                        <th>Isi Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['PR Baru Dibuat','Semua Admin','Notifikasi PR baru membutuhkan tindakan'],
                        ['PIC Ditugaskan','PIC yang ditugaskan','Detail PR dan tanggung jawab sebagai PIC'],
                        ['Dikirim ke Approver','Approver (current)','Link approval (berlaku 7 hari) + detail PR'],
                        ['PR Disetujui','Requester','Konfirmasi PR disetujui + detail'],
                        ['PR Ditolak','Requester','Alasan penolakan + detail PR'],
                    ] as [$ev,$rc,$ct])
                    <tr>
                        <td>
                            <span class="ug-notif-dot"></span>
                            <strong style="color:var(--fg,#111827);font-size:.8rem">{{ $ev }}</strong>
                        </td>
                        <td>{{ $rc }}</td>
                        <td>{{ $ct }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="ug-alert ug-alert-blue" style="margin-top:.75rem">
            <div class="ug-alert-icon">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
            </div>
            <div>
                <div class="ug-alert-body">Semua aktivitas email tersimpan di tabel <strong>Email Log</strong> yang dapat dilihat oleh Admin untuk keperluan audit trail.</div>
            </div>
        </div>
    </div>

    {{-- ===================== 7. FAQ ===================== --}}
    <div id="section-7">
        <div class="ug-section-header">
            <div class="ug-section-num">7</div>
            <h2 class="ug-section-title">Tips &amp; FAQ</h2>
        </div>
        <div class="ug-faq-list">
            @foreach([
                ['❓','PR saya sudah disubmit tapi tidak ada yang approve?','Pastikan Admin sudah menjalankan aksi "Send for Approval". PR yang sudah disubmit belum otomatis dikirim ke approver — Admin harus memvalidasi dan mengirimkan secara manual.'],
                ['❓','Link approval di email sudah expired?','Link approval berlaku 7 hari. Jika sudah expired, hubungi Admin untuk mengirim ulang link approval melalui aksi "Resend Approval Email" di panel Admin.'],
                ['❓','Bagaimana jika tidak ada approval flow yang cocok?','Admin perlu membuat Approval Flow baru di menu Master Data → Approval Flow. Pastikan range amount dan department sesuai dengan PR yang akan diajukan.'],
                ['❓','Apakah PR yang sudah Rejected bisa diajukan ulang?','Tidak. PR yang sudah berstatus Rejected bersifat final. Requester perlu membuat PR baru jika ingin mengajukan kembali.'],
                ['❓','Berapa batas ukuran file lampiran?','Maksimum 10 MB per file. Format yang diterima adalah PDF. Pastikan dokumen sudah dalam format PDF sebelum diupload.'],
                ['💡','Tips: Isi tujuan pembelian dengan detail','Semakin detail tujuan pembelian yang Anda tulis, semakin cepat approver dapat mengambil keputusan. Sertakan justifikasi kebutuhan dan dampak jika tidak disetujui.'],
                ['💡','Tips: Upload quotation dari minimal 2 vendor','Lampirkan quotation dari minimal 2 vendor sebagai perbandingan harga. Ini membantu approver menilai kewajaran harga.'],
                ['💡','Tips: Tetapkan tanggal kebutuhan yang realistis','Berikan waktu yang cukup untuk proses approval (minimal 3–5 hari kerja untuk PR biasa). Gunakan prioritas "Urgent" hanya untuk kebutuhan yang benar-benar mendesak.'],
            ] as [$ic,$q,$a])
            <div class="ug-faq-item">
                <div class="ug-faq-item-inner">
                    <span class="ug-faq-emoji">{{ $ic }}</span>
                    <div>
                        <div class="ug-faq-q">{{ $q }}</div>
                        <div class="ug-faq-a">{{ $a }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ===================== FOOTER ===================== --}}
    <div class="ug-footer">
        <p>Panduan ini dibuat untuk sistem <strong>Purchase Request Management</strong>. Jika ada pertanyaan atau kendala, silakan hubungi <strong>Administrator Sistem</strong>.</p>
        <small>v1.0 — Laravel + Filament — Multi-level Approval System</small>
    </div>

</div>
</x-filament-panels::page>
