export const meta = {
  name: 'jagakampung-security-verify',
  description: 'Adversarially verify the security remediation of the jagakampung Laravel app — 5 dimension reviewers try to break the fixes, skeptics refute each finding, completeness critic finds gaps',
  phases: [
    { title: 'Review', detail: '5 dimension reviewers adversarially probe the applied fixes' },
    { title: 'Verify', detail: 'each finding gets an independent skeptic that tries to refute it' },
    { title: 'Synthesize', detail: 'completeness critic finds gaps + ranks priorities' },
  ],
}

const FINDINGS_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  properties: {
    dimension: { type: 'string' },
    findings: {
      type: 'array',
      items: {
        type: 'object',
        additionalProperties: false,
        properties: {
          severity: { type: 'string', enum: ['critical', 'high', 'medium', 'low', 'info'] },
          title: { type: 'string' },
          file: { type: 'string' },
          line: { type: 'string' },
          description: { type: 'string' },
          exploit_scenario: { type: 'string' },
          evidence: { type: 'string', description: 'exact code/path you observed' },
          recommendation: { type: 'string' },
          confidence: { type: 'string', enum: ['high', 'medium', 'low'] },
        },
        required: ['severity', 'title', 'file', 'description', 'exploit_scenario', 'evidence', 'recommendation', 'confidence'],
      },
    },
    coverage_notes: { type: 'string', description: 'what you checked, what you could not check, blind spots' },
  },
  required: ['dimension', 'findings', 'coverage_notes'],
}

const VERDICT_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  properties: {
    is_real: { type: 'boolean', description: 'true ONLY if the exploit is concretely achievable in THIS codebase as it stands now' },
    reasoning: { type: 'string' },
    severity_confirmed: { type: 'string', enum: ['critical', 'high', 'medium', 'low', 'info', 'refuted'] },
    corrected_severity: { type: 'string', description: 'only if you disagree with the reported severity' },
    notes: { type: 'string' },
  },
  required: ['is_real', 'reasoning', 'severity_confirmed'],
}

const CRITIC_SCHEMA = {
  type: 'object',
  additionalProperties: false,
  properties: {
    gaps: {
      type: 'array',
      items: {
        type: 'object',
        additionalProperties: false,
        properties: {
          area: { type: 'string' },
          what_is_missing: { type: 'string' },
          why_it_matters: { type: 'string' },
        },
        required: ['area', 'what_is_missing', 'why_it_matters'],
      },
    },
    confirmed_real_findings_count: { type: 'integer' },
    top_priorities: { type: 'array', items: { type: 'string' } },
    overall_assessment: { type: 'string' },
  },
  required: ['gaps', 'confirmed_real_findings_count', 'top_priorities', 'overall_assessment'],
}

const CONTEXT = `
You are auditing the "jagakampung" Laravel 12 / Livewire 3 app at the current working directory.
Auth is CUSTOM session-based (NOT Laravel Auth): session('id') = user id, session('role_id') = 0 for admin, 1 for regular user.
checkSession middleware only checks truthy session('id'). adminSession middleware (new) checks role_id===0.

A prior remediation already applied these fixes. YOUR JOB IS ADVERSARIAL: try to BREAK each fix, find residual bypasses, incomplete coverage, or NEW issues the fix introduced. Do NOT just re-report the original vuln — report only what STILL holds or is newly broken. If a fix is solid, say so in coverage_notes and return few/no findings for it.

Read the actual files with Read/Grep/Bash before asserting anything. Every claim must cite exact file:line evidence you actually saw.
`

const DIMENSIONS = [
  {
    key: 'packages',
    prompt: `${CONTEXT}

DIMENSION: Packages & supply chain.
Claimed fixes: intervention/image pinned to ^3.11 (was "*"); unisharp/laravel-filemanager kept at ^2.12 (removal DEFERRED because TinyMCE depends on it); composer audit reported 0; npm audit reported 0.

Adversarially verify:
1. RUN "composer audit" and "npm audit" yourself (Bash) — do they actually return 0 vulnerabilities / exit 0? Report exact output.
2. Is intervention/image ^3.11 the safe current line, or does ^3.11 resolve to a version with a known advisory? Check composer.lock for the resolved version.
3. unisharp/laravel-filemanager is ABANDONED with public CVEs (double-extension upload bypass CVE-2019-18394-ish, LFI via img param). It is still installed and routed at /cms/fire-filemanager. Is "deferred removal" acceptable, or is this a LIVE critical given the route is reachable behind only checkSession? Judge severity given the OTHER mitigations in place (.htaccess, blockPathTraversal, lfm.php denylists) — does the combination actually neutralize the CVEs, or does a residual path remain?
4. Any other abandoned/known-vulnerable packages in composer.lock or package-lock.json?

Files: composer.json, composer.lock, package.json, package-lock.json, config/lfm.php, and run the audit commands.`,
  },
  {
    key: 'auth-rbac-session',
    prompt: `${CONTEXT}

DIMENSION: Auth, RBAC, session — THE KEY ADVERSARIAL CHECK.
Claimed fixes: session()->regenerate() added on successful login (session fixation); logout changed to POST with session()->flush() + regenerate(); adminSession middleware + route grouping for admin-only routes; in-component admin checks (if role_id!==0 abort 403) added to CmsUsers.deleting, CmsGroup/CmsInstansi/CmsPerusahaan.deleting, EditGroup/EditInstansi/EditPerusahaan.storeDatabase, TambahUser.storeDatabase, EditUser.mount.

THE CRITICAL ADVERSARIAL CHECK — Livewire route-middleware bypass:
Livewire action methods do NOT reliably re-run route middleware, and public component properties are client-mutable in Livewire snapshots. So route-level RBAC (adminSession on the route) is BYPASSABLE. The robust fix is an in-component DB-backed authz re-check inside EVERY mutating/sensitive action.

Verify exhaustively:
1. List EVERY public method on EVERY Livewire component that mutates the DB or is security-sensitive. For EACH, determine: does it have an in-component role or ownership check? Flag any mutating method that relies ONLY on route middleware (no in-component check) — that is a bypass.
   - Specifically scrutinize: EditKonflik (simpanLampiran, updateLampiranTemp, deleteTags, removeImage, removeNewImage, addLampiran, editPerkembangan, delete, selectRegion, updatedChooseRegion, storeDatabase — which mutate DB?), CmsUsers (any method besides deleting?), TambahKonflik (storeDatabase, any image/lampiran handlers).
2. The in-component admin checks added use session('role_id') directly — is session('role_id') client-mutable in Livewire? (Livewire does NOT sync session to the client, so session reads are server-side safe — confirm this is true and the checks are not reading a public property instead.)
3. Session fixation: is regenerate-on-login enough, or is there a fixation window (e.g. regenerate happens AFTER setting session data, or not on role escalation)? Does logout truly invalidate?
4. checkSession only checks truthy session('id') — any auth-bypass via a crafted session value?
5. Are the admin checks placed BEFORE any DB mutation, or after (dead code)?

Files: app/Livewire/*.php (all), app/Http/Middleware/*.php, routes/web.php, app/Livewire/LoginComponent.php.`,
  },
  {
    key: 'idor-privesc',
    prompt: `${CONTEXT}

DIMENSION: IDOR & privilege escalation.
Claimed fixes: EditKonflik mount() + storeDatabase() now DB-backed ownership re-check (owner or admin); TambahArtikel.storeDatabase checks the target konflik belongs to the user/admin; EditArtikel mount/storeDatabase/deleteArtikel check the artikel's konflik ownership; KonflikController::destroy does owner-or-admin authz; LocalServiceController strips user_id and filters publish-only.

Adversarially verify:
1. For EditKonflik.storeDatabase: the ownership re-check queries konflik by idDB. Is idDB a public Livewire property (client-mutable)? Could an attacker swap idDB to a konflik they don't own BETWEEN the check and the update? (Single request, so likely fine — but confirm the check uses the SAME idDB that the update uses, and that idDB can't be changed after the check within one dispatch.)
2. Are there OTHER mutating paths that write to konflik/artikel/konflik_gambar/konflik_lampiran/konflik_lembaga WITHOUT an ownership check? (e.g. simpanLampiran/updateLampiranTemp in EditKonflik append to $this->lampirans — the actual DB write happens in storeDatabase which IS checked, but verify nothing writes earlier.)
3. Mass-assignment-via-DB::table()->insert: TambahKonflik/TambahArtikel/TambahUser use DB::table()->insert with $this-> properties. Are any client-mutable properties leaking into columns they shouldn't (role, user_id, status, is_active)? Specifically: can a non-admin set status='publish' (TambahKonflik forces draft for role_id===1 — verify the comparison is correct: ===1 vs !==0; what if role_id is null/string?). TambahUser role is whitelisted — verify the whitelist.
4. KonflikController::destroy — read it; is the authz check correct and does it delete related rows safely? Is the route POST (CSRF-protected)?
5. Are there controllers (ArtikelController, GrupController, InstansiController, PerusahaanController, UsersController) whose add/edit/index methods leak or allow cross-user data? These are route controllers that may load data before the Livewire component mounts.

Files: app/Livewire/{EditKonflik,TambahKonflik,TambahArtikel,EditArtikel,TambahUser,EditUser}.php, app/Http/Controllers/*.php.`,
  },
  {
    key: 'uploads-lfm',
    prompt: `${CONTEXT}

DIMENSION: File uploads & Laravel Filemanager (LFM).
Claimed fixes: storage/app/public/.htaccess denies PHP/cgi/script execution; config/lfm.php broadened disallowed_extensions and disallowed_mimetypes (added svg, php variants, htm, js, etc.) and enabled should_validate_size; blockPathTraversal middleware blocks any LFM param containing '..' or starting with '/' or '\\\\'; image uploads (TambahKonflik images, EditKonflik newImages, TambahArtikel/EditArtikel gambar) now validate mimes:jpg,jpeg,png,webp.

Adversarially verify:
1. DOUBLE-EXTENSION BYPASS: LfmUploadValidator uses getOriginalClientExtension() which reads the LAST extension, so shell.php.jpg passes as 'jpg'. Does the .htaccess in storage/app/public actually stop a stored .php file from executing? Read the .htaccess. Is it Apache-only — and is that a residual risk for nginx deployments (flag severity accordingly)? Is .htaccess even loaded if AllowOverride is off?
2. blockPathTraversal: does it cover ALL params LFM uses for path operations (img, working_dir, folder, file, name, path, item — are there others like 'base_folder', 'fldr')? Does Laravel request->input() URL-decode %2e%2e%2f — i.e. would an encoded ../ bypass the string check? Test/confirm.
3. Upload mimes validation: TambahArtikel validates gambar|required|image|mimes. EditArtikel validates ONLY when gambar is a TemporaryUploadedFile (conditional) — is there a path where gambar is a client-supplied string path that bypasses validation? EditKonflik newImages validated, but what about the LAMPIRAN upload (simpanLampiran allows mimes:pdf,jpg,jpeg,png,webp) and the filename uses getOriginalClientName() — stored XSS via a filename containing HTML rendered in the UI? Is PDF exec-safe (yes, but can it be served as something else)?
4. storeAs uses getOriginalClientName() to build filenames — could a malicious original name contain a path separator or traversal to write outside the intended dir?
5. Is the LFM route group actually wrapped by blockPathTraversal? Read routes/web.php. Does blockPathTraversal run for the upload endpoint (ANY /upload)?

Files: storage/app/public/.htaccess, config/lfm.php, app/Http/Middleware/blockPathTraversal.php, routes/web.php, app/Livewire/{TambahKonflik,EditKonflik,TambahArtikel,EditArtikel}.php, vendor/unisharp/laravel-filemanager/src/LfmUploadValidator.php, vendor/unisharp/laravel-filemanager/src/Lfm.php.`,
  },
  {
    key: 'disclosure-config',
    prompt: `${CONTEXT}

DIMENSION: Information disclosure & configuration hardening.
Claimed fixes: LocalServiceController.index() now selects only id,lat,long,status with where status='publish' and stripped user_id from properties; kasusDetail() adds where status='publish' + 404 if not found; .env.example hardened (APP_DEBUG=false, SESSION_ENCRYPT=true, SESSION_SECURE_COOKIE=true, LOG_LEVEL=warning, DB creds block). The live .env was deliberately NOT modified (operator action).

Adversarially verify:
1. RUN the app's route list and identify EVERY public (no auth) endpoint. For each, does it leak sensitive data or PII? rest-map is intentionally public — read LocalServiceController.index/kasusDetail: does the published konflik data expose anything sensitive (user names, raw coordinates of at-risk communities, admin notes)? Does kasusDetail return the FULL row or a safe subset?
2. Are there OTHER public controllers leaking data (search for routes outside the checkSession group in routes/web.php — rest-map, login, '/', anything else)?
3. SESSION_ENCRYPT=true requires a valid APP_KEY. Is APP_KEY present/strong in .env.example (it should be a placeholder) and is rotating it flagged (encrypting sessions with a weak key = false security)? Is the cipher AES-256-GCM or CBC (check config/app.php 'cipher')? If CBC with a null/weak key, flag.
4. Debug/diagnostic exposure: is APP_DEBUG actually false in the live config path? Are debugbar/telescope/horizon/_ignition routes exposed? Is /storage publicly symlinked and does it serve arbitrary files?
5. CORS config (config/cors.php) — is it wildcard for sensitive routes?
6. Is the "live .env not modified" gap a real risk that must be flagged as an operator action item, and is it clearly communicated?

Files: app/Http/Controllers/LocalServiceController.php, app/Http/Controllers/*.php, routes/web.php, .env.example, config/app.php, config/cors.php, config/session.php.`,
  },
]

phase('Review')
const results = await pipeline(
  DIMENSIONS,
  d => agent(d.prompt, { label: `review:${d.key}`, phase: 'Review', schema: FINDINGS_SCHEMA }),
  (review, d) => {
    if (!review || !review.findings || !review.findings.length) return { dimension: d.key, review, verified: [] }
    return parallel(review.findings.map(f => () =>
      agent(
        `${CONTEXT}

A reviewer reported this security finding in the jagakampung app. Adversarially VERIFY it by trying to REFUTE it: read the cited file(s) and determine whether the exploit is concretely achievable in the codebase AS IT STANDS NOW (after the remediation). If the fix actually neutralizes it, or the exploit requires conditions that do not hold here, refute it (is_real=false). Only mark is_real=true if you personally confirmed the vulnerable code path is reachable and unprotected.

FINDING:
- dimension: ${d.key}
- severity: ${f.severity}
- title: ${f.title}
- file: ${f.file}${f.line ? `:${f.line}` : ''}
- description: ${f.description}
- exploit_scenario: ${f.exploit_scenario}
- evidence: ${f.evidence}
- reviewer confidence: ${f.confidence}

Read the file(s) yourself. Cite what you actually saw in your reasoning.`,
        { label: `verify:${d.key}:${f.file}`, phase: 'Verify', schema: VERDICT_SCHEMA }
      ).then(v => ({ ...f, dimension: d.key, verdict: v }))
    )).then(verified => ({ dimension: d.key, review, verified: verified.filter(Boolean) }))
  }
)

const allFindings = results.filter(Boolean).flatMap(r => r.verified || [])
const confirmed = allFindings.filter(f => f.verdict && f.verdict.is_real)
log(`Review+verify complete: ${allFindings.length} findings raised, ${confirmed.length} confirmed real`)

phase('Synthesize')
const critic = await agent(
  `${CONTEXT}

You are the COMPLETENESS CRITIC for a security audit of the jagakampung Laravel app. Below are all findings raised by 5 dimension reviewers (packages, auth-rbac-session, idor-privesc, uploads-lfm, disclosure-config), each already adversarially verified by an independent skeptic. Your job: find what was MISSED.

Verified findings (JSON):
${JSON.stringify(allFindings, null, 2)}

Confirmed-real findings (JSON):
${JSON.stringify(confirmed, null, 2)}

Identify GAPS: modalities not run, claims unverified, files unread, attack surfaces none of the dimensions covered (e.g. SQL injection in raw DB::raw / where ILIKE with user input, XSS in blade outputs, CSRF on state-changing GET routes, insecure deserialization, rate-limiting/brute-force on login, secrets in git history, the pgsql_gis connection credentials, the GeoJSON/geom handling). Rank top priorities for remediation. Give an overall assessment of residual risk.`,
  { label: 'completeness-critic', phase: 'Synthesize', schema: CRITIC_SCHEMA }
)

return {
  dimensions: results.filter(Boolean).map(r => ({ dimension: r.dimension, coverage_notes: r.review?.coverage_notes, raised: (r.verified||[]).length, confirmed: (r.verified||[]).filter(f => f.verdict?.is_real).length })),
  total_raised: allFindings.length,
  total_confirmed: confirmed.length,
  confirmed_findings: confirmed,
  refuted_findings: allFindings.filter(f => f.verdict && !f.verdict.is_real),
  critic,
}