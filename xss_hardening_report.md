# XSS Hardening Report — prioritized

High Risk

- resources/views/dashboard/mainreports/_streportContent.blade.php (lines ~120-370): `bodyEl.innerHTML = html` and `stListEl.innerHTML = html` — runtime-built HTML inserted unescaped. Suggestion: audit the source of `html`; if it contains only text use `textContent`. If HTML is required, wrap with `sanitizeHtml(html)` or build DOM nodes programmatically. See [resources/views/dashboard/mainreports/_streportContent.blade.php](resources/views/dashboard/mainreports/_streportContent.blade.php#L163).

- resources/views/Login/accounts/register.blade.php (line ~507): `errorContainer.innerHTML = html`. Suggestion: prefer `textContent` or `escHtml(...)` for user-provided messages, or sanitize before insertion. See [resources/views/Login/accounts/register.blade.php](resources/views/Login/accounts/register.blade.php#L507).

- temp_block.js (insertAdjacentHTML/use of `subHtml`): dynamic `insertAdjacentHTML('afterend', subHtml)` (line ~1219). Suggestion: build nodes using `createElement` and `appendChild`, or sanitize `subHtml` before insertion. See [temp_block.js](temp_block.js#L1219).

- temp_parse.cjs (line ~8): `new Function(script)` — executing generated/untrusted code. Suggestion: remove use or require strict provenance and sandboxing; avoid on user data.

- resources/views/Login/accounts/profile.blade.php (lines ~656): `outerHTML` built by concatenating `src` into an `img` tag. Ensure `src` is validated/escaped.

Medium Risk

- Inline `onclick` handlers that interpolate values (pagination/buttons) — prefer `addEventListener` and ensure interpolated values are strictly typed (numbers/ids). See examples in [resources/views/dashboard/main.blade.php](resources/views/dashboard/main.blade.php#L4210).

- Vendor Livewire files (many): `innerHTML`, `insertAdjacentHTML`, `document.write`, `outerHTML` — these are vendor runtime behaviors; do not edit vendor files. Instead rely on CSP, input validation, and keep vendor updated.

Low Risk / Acceptable

- Static `innerHTML` assignments using fixed HTML strings (spinners, static notices) are acceptable.

Raw Blade `{!! ... !!}`

- Found in vendor translations and exception renderers (e.g., resources/views/vendor/livewire/* and resources/views/vendor/laravel-exceptions-renderer/*). Action: avoid changing vendor; for non-vendor `{!!}` occurrences, audit and convert to `{{ }}` where the value is not intentionally raw HTML.

Next recommended steps (conservative, non-destructive)

1. Keep this snapshot (done) and run the application tests / smoke the main pages to confirm current behaviour.
2. I can produce a per-file actionable diff (preview) for the top high-risk files; I will not apply them until you approve.
3. After your approval, I can auto-apply conservative fixes:
   - Replace `innerHTML = variable` → `textContent = variable` when `variable` appears to be plain text.
   - Wrap HTML sinks that require HTML with `sanitizeHtml(...)` when safe.
   - Replace simple inline-event interpolations by attaching event listeners where trivial.
4. For vendor files (Livewire), rely on CSP and server-side sanitization; do not modify vendor.

If you want me to proceed now, choose:
- `apply` — auto-apply conservative fixes for the top high-risk files (I will show a preview diff before committing).
- `preview` — produce file-by-file diffs/suggestions only (no changes).
- `pause` — I stop and wait for your instruction.

