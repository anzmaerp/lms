<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Find Certificate</title>

  <!-- Google font (optional) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg-1: #0f1724;       /* page background */
      --bg-2: #0b1220;       /* inner gradient */
      --card: rgba(255,255,255,0.04);
      --glass: rgba(255,255,255,0.03);
      --accent: #4f46e5;     /* indigo */
      --accent-2: #06b6d4;   /* teal */
      --muted: rgba(255,255,255,0.62);
      --danger: #ef4444;
      --radius: 12px;
    }

    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      background: radial-gradient(1200px 600px at 10% 10%, rgba(79,70,229,0.12), transparent),
                  radial-gradient(900px 500px at 90% 90%, rgba(6,182,212,0.08), transparent),
                  linear-gradient(180deg,var(--bg-1),var(--bg-2));
      color:#e6eef8;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      padding:32px;
      display:flex;
      align-items:center;
      justify-content:center;
      gap:24px;
      min-height:100vh;
    }

    /* Container */
    .wrap{
      width:100%;
      max-width:920px;
      display:grid;
      grid-template-columns: 1fr 420px;
      gap:28px;
      align-items:start;
    }

    /* Left: info / results */
    .panel {
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      border-radius: var(--radius);
      padding:28px;
      box-shadow: 0 10px 30px rgba(2,6,23,0.6);
      border:1px solid rgba(255,255,255,0.03);
      min-height:320px;
    }

    .brand {
      display:flex;
      gap:12px;
      align-items:center;
      margin-bottom:10px;
    }
    .logo {
      width:52px;
      height:52px;
      border-radius:10px;
      background: linear-gradient(135deg,var(--accent),var(--accent-2));
      display:flex;
      align-items:center;
      justify-content:center;
      font-weight:700;
      color:white;
      font-size:18px;
      box-shadow: 0 6px 18px rgba(79,70,229,0.14), inset 0 -6px 12px rgba(255,255,255,0.03);
    }
    h1 { margin:0; font-size:20px; font-weight:600; color:#f8fbff; }
    p.lead { margin:6px 0 18px; color:var(--muted); font-size:14px; line-height:1.45; }

    /* Certificate card */
    .cert-card {
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      padding:18px;
      border-radius:10px;
      border:1px solid rgba(255,255,255,0.025);
      display:flex;
      flex-direction:column;
      gap:8px;
    }
    .cert-title { font-weight:600; font-size:16px; color:#f6f8ff; }
    .meta { color:var(--muted); font-size:13px; }
    .cert-actions { margin-top:10px; display:flex; gap:10px; align-items:center; }

    .btn {
      display:inline-flex;
      align-items:center;
      gap:8px;
      background: linear-gradient(90deg, var(--accent), var(--accent-2));
      color:white;
      border:none;
      padding:9px 14px;
      border-radius:10px;
      cursor:pointer;
      font-weight:600;
      text-decoration:none;
      box-shadow: 0 8px 24px rgba(79,70,229,0.18);
      transition:transform .12s ease, box-shadow .12s ease;
    }
    .btn:active{ transform:translateY(1px) }
    .btn.ghost {
      background:transparent;
      border:1px solid rgba(255,255,255,0.06);
      color:var(--muted);
      box-shadow:none;
    }

    /* Right: search box */
    .search-box{
      background: linear-gradient(180deg, rgba(255,255,255,0.025), rgba(255,255,255,0.01));
      padding:22px;
      border-radius:14px;
      border:1px solid rgba(255,255,255,0.03);
      box-shadow: 0 10px 30px rgba(2,6,23,0.55);
    }
    label {
      display:block;
      font-size:13px;
      color:var(--muted);
      margin-bottom:8px;
      font-weight:600;
    }
    .field {
      display:flex;
      gap:10px;
      align-items:center;
    }
    input[type="text"]{
      flex:1;
      min-width:0;
      background:linear-gradient(180deg, rgba(255,255,255,0.018), transparent);
      border:1px solid rgba(255,255,255,0.035);
      padding:12px 14px;
      color: #f2f7ff;
      border-radius:10px;
      outline:none;
      font-size:14px;
      transition:box-shadow .12s ease, border-color .12s ease, transform .08s ease;
      box-shadow: inset 0 -6px 18px rgba(0,0,0,0.25);
    }
    input[type="text"]:focus{
      border-color: rgba(99,102,241,0.95);
      box-shadow: 0 6px 22px rgba(79,70,229,0.12);
      transform:translateY(-1px);
    }
    .invalid-feedback{
      margin-top:8px;
      color:var(--danger);
      font-size:13px;
    }

    .helper { font-size:13px; color:var(--muted); margin-top:12px; }

    /* Small screens */
    @media (max-width:880px){
      .wrap{grid-template-columns:1fr; padding:16px}
      .search-box{order:-1}
    }
  </style>
</head>
<body>
  <div class="wrap">

    <!-- LEFT: results / info -->
    <section class="panel" aria-labelledby="pageTitle">
      <div class="brand">
        <div class="logo">CR</div>
        <div>
          <h1 id="pageTitle">Find Certificate</h1>
          <p class="lead">Type the certificate number on the right and press <strong>Search</strong>. Results appear here instantly.</p>
        </div>
      </div>

      {{-- If the user searched (keeps your original logic) --}}
      @if(request()->filled('certificate_number'))
        @if($certificate)
          <div class="cert-card" role="region" aria-label="certificate details">
            <div style="display:flex;justify-content:space-between;align-items:center">
              <div>
                <div class="cert-title">Certificate — {{ $certificate->number }}</div>
                <div class="meta">Issued: {{ optional($certificate->issued_at)->format('Y-m-d') ?? '—' }} • Holder: {{ $certificate->holder_name ?? '—' }}</div>
              </div>
              <div style="text-align:right">
                <div style="font-size:12px;color:var(--muted)">Status</div>
                <div style="margin-top:6px;background:linear-gradient(90deg,#10b981,#34d399);color:#05222a;padding:6px 10px;border-radius:8px;font-weight:700;font-size:13px">VALID</div>
              </div>
            </div>

            @if($certificate->file_path)
              <div class="cert-actions">
                <a target="_blank" href="{{ Storage::disk('public')->url($certificate->file_path) }}" class="btn" aria-label="view certificate">View / Download</a>
                <a href="?certificate_number={{ urlencode(request('certificate_number')) }}&download=1" class="btn ghost" onclick="event.preventDefault(); document.getElementById('download-form').submit();">Download PDF</a>

                <!-- small hidden form to trigger download (server must handle download param) -->
                <form id="download-form" method="GET" action="" style="display:none">
                  <input type="hidden" name="certificate_number" value="{{ request('certificate_number') }}">
                  <input type="hidden" name="download" value="1">
                </form>
              </div>
            @endif
          </div>
        @else
          <div class="cert-card" style="border-left:4px solid var(--danger);">
            <div class="cert-title">Not found</div>
            <div class="meta">Certificate <strong>{{ e(request('certificate_number')) }}</strong> was not found in our records.</div>
            <div style="margin-top:10px" class="helper">Double-check the number or contact support if you think this is an error.</div>
          </div>
        @endif
      @else
        <div class="cert-card">
          <div class="cert-title">No search yet</div>
          <div class="meta">Enter a certificate number in the box to the right to look it up.</div>
          <div style="margin-top:12px" class="helper">For best results use the full certificate number (no extra spaces).</div>
        </div>
      @endif

    </section>

    <!-- RIGHT: search -->
    <aside class="search-box" aria-labelledby="searchLabel">
      <form method="GET" action="">
        @csrf {{-- harmless for GET --}}
        <label id="searchLabel" for="certificate_number">Certificate number</label>
        <div class="field">
          <input
            id="certificate_number"
            name="certificate_number"
            type="text"
            class="@error('certificate_number') is-invalid @enderror"
            value="{{ old('certificate_number', request('certificate_number')) }}"
            placeholder="e.g. ABC-2025-000123"
            autocomplete="off"
            required
            aria-required="true"
            autofocus
          >
          <button type="submit" class="btn" id="searchBtn" aria-label="Search certificate">
            <svg id="searchIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" aria-hidden="true">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M21 21l-4.35-4.35M10.75 18A7.25 7.25 0 1 1 10.75 3a7.25 7.25 0 0 1 0 15z"/>
            </svg>
            <span>Search</span>
          </button>
        </div>

        @error('certificate_number')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <p class="helper">We search by exact certificate number. If you want partial matches, change the query in your controller to use <code>->where('number','like', '%...%')</code>.</p>
      </form>
    </aside>

  </div>

  <script>
    // small UX: disable button on submit and show spinner
    (function(){
      const form = document.querySelector('form');
      const btn = document.getElementById('searchBtn');
      if(!form || !btn) return;

      form.addEventListener('submit', function(e){
        // quick client-side trim + validation
        const input = form.querySelector('[name="certificate_number"]');
        if(input){
          input.value = input.value.trim();
          if(input.value.length === 0){
            e.preventDefault();
            input.focus();
            return;
          }
        }

        // disable button to prevent double submit
        btn.disabled = true;
        btn.style.opacity = '0.9';
        btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right:8px"><path stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M21 12A9 9 0 1 1 3 12"></path></svg><span>Searching…</span>';
      });
    })();
  </script>
</body>
</html>
