<?php
// assets/css/style_inline.php — Shared styles included inline
?>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;700&family=IBM+Plex+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:      #0d1117; --surface:#161b22; --border:#30363d;
    --accent:  #58a6ff; --green:#3fb950; --yellow:#d29922;
    --red:     #f85149; --purple:#bc8cff; --text:#e6edf3; --muted:#8b949e;
  }
  * { box-sizing:border-box; margin:0; padding:0; }
  body { background:var(--bg); color:var(--text); font-family:'IBM Plex Sans',sans-serif; display:flex; min-height:100vh; }
  .sidebar {
    width:230px; min-height:100vh; background:var(--surface);
    border-right:1px solid var(--border); display:flex; flex-direction:column;
    padding:1.5rem 0; position:fixed; top:0; left:0; bottom:0; overflow-y:auto;
  }
  .logo { font-family:'IBM Plex Mono',monospace; font-size:1.4rem; font-weight:700; color:var(--accent); padding:0 1.5rem 1.5rem; border-bottom:1px solid var(--border); margin-bottom:1rem; }
  .logo small { display:block; font-size:.65rem; font-weight:400; color:var(--muted); margin-top:.15rem; }
  .nav-section { padding:.5rem 1rem .25rem; font-size:.7rem; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; }
  .nav-item { display:flex; align-items:center; gap:.65rem; padding:.55rem 1.5rem; color:var(--muted); text-decoration:none; font-size:.88rem; transition:all .15s; border-left:3px solid transparent; }
  .nav-item:hover, .nav-item.active { color:var(--text); background:rgba(88,166,255,.08); border-left-color:var(--accent); }
  .nav-item .icon { font-size:1rem; width:18px; text-align:center; }
  .sidebar-footer { margin-top:auto; padding:1rem 1.5rem; border-top:1px solid var(--border); font-size:.8rem; }
  .role-badge { display:inline-block; background:rgba(88,166,255,.15); color:var(--accent); border:1px solid rgba(88,166,255,.3); border-radius:4px; padding:.15rem .5rem; font-size:.7rem; font-family:'IBM Plex Mono',monospace; }
  .sidebar-footer .name { color:var(--text); font-weight:600; margin-bottom:.25rem; }
  .logout-btn { display:block; margin-top:.75rem; text-align:center; padding:.4rem; background:rgba(248,81,73,.1); color:var(--red); border:1px solid rgba(248,81,73,.3); border-radius:6px; text-decoration:none; font-size:.8rem; transition:.2s; }
  .logout-btn:hover { background:rgba(248,81,73,.2); }
  .main { margin-left:230px; padding:2rem; flex:1; width:calc(100% - 230px); }
  .page-header { margin-bottom:1.75rem; }
  .page-header h1 { font-size:1.4rem; font-weight:700; }
  .page-header p { color:var(--muted); font-size:.87rem; margin-top:.25rem; }
  .content-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; }
  @media(max-width:900px){ .content-grid{grid-template-columns:1fr;} }
  .panel { background:var(--surface); border:1px solid var(--border); border-radius:10px; overflow:hidden; }
  .panel-header { padding:1rem 1.25rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
  .panel-header h2 { font-size:.95rem; font-weight:600; }
  .panel-header a { font-size:.78rem; color:var(--accent); text-decoration:none; }
  .panel-full { grid-column:1/-1; }
  table { width:100%; border-collapse:collapse; font-size:.83rem; }
  th { padding:.65rem 1rem; text-align:left; color:var(--muted); font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--border); }
  td { padding:.65rem 1rem; border-bottom:1px solid rgba(48,54,61,.6); }
  tr:last-child td { border-bottom:none; }
  tr:hover td { background:rgba(88,166,255,.04); }
  .badge { display:inline-block; padding:.2rem .55rem; border-radius:4px; font-size:.72rem; font-family:'IBM Plex Mono',monospace; }
  .badge-green  { background:rgba(63,185,80,.15);  color:var(--green);  border:1px solid rgba(63,185,80,.3); }
  .badge-yellow { background:rgba(210,153,34,.15); color:var(--yellow); border:1px solid rgba(210,153,34,.3); }
  .badge-red    { background:rgba(248,81,73,.15);  color:var(--red);    border:1px solid rgba(248,81,73,.3); }
  .badge-blue   { background:rgba(88,166,255,.15); color:var(--accent); border:1px solid rgba(88,166,255,.3); }
  .empty { padding:2rem; text-align:center; color:var(--muted); font-size:.85rem; }
  .form-group { margin-bottom:1rem; }
  .form-group label { display:block; font-size:.83rem; color:var(--muted); margin-bottom:.4rem; }
  .form-group input, .form-group select, .form-group textarea {
    width:100%; background:var(--bg); border:1px solid var(--border); border-radius:6px;
    color:var(--text); padding:.65rem .9rem; font-size:.9rem; font-family:inherit;
    transition:border-color .2s;
  }
  .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline:none; border-color:var(--accent); }
  .form-group select option { background:var(--surface); }
  .btn { background:var(--accent); color:#000; border:none; border-radius:6px; padding:.7rem 1.5rem; font-size:.9rem; font-weight:600; cursor:pointer; transition:opacity .2s; }
  .btn:hover { opacity:.85; }
  .btn-danger { background:var(--red); color:#fff; }
  .btn-green { background:var(--green); color:#000; }
  .alert { padding:.85rem 1.1rem; border-radius:8px; margin-bottom:1.25rem; font-size:.88rem; }
  .alert.success { background:rgba(63,185,80,.12); border:1px solid rgba(63,185,80,.3); color:var(--green); }
  .alert.error   { background:rgba(248,81,73,.12); border:1px solid rgba(248,81,73,.3); color:var(--red); }
  .alert.warning { background:rgba(210,153,34,.12); border:1px solid rgba(210,153,34,.3); color:var(--yellow); }
  .stat-row { display:flex; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap; }
  .stat-mini { background:var(--surface); border:1px solid var(--border); border-radius:8px; padding:1rem 1.25rem; min-width:130px; }
  .stat-mini .val { font-size:1.6rem; font-weight:700; font-family:'IBM Plex Mono',monospace; }
  .stat-mini .lbl { font-size:.72rem; color:var(--muted); margin-top:.2rem; }
</style>
