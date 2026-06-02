# Template & Frontend Security Invariants

These rules exist so that one careless edit cannot reintroduce a class of
vulnerability that the audit closed. Anything in this file is enforced by
review, not by automation — keep the list short and concrete.

## Twig rules

1. **Never use `|raw` on user-controlled data inside `<script>` blocks.**
   Twig autoescape only protects HTML context; inside `<script>` you also
   need to escape quotes and angle brackets. Prefer a `<script type="application/json">`
   sidecar:

   ```twig
   <script type="application/json" id="stats-data">
     {{ payload|json_encode|raw }}
   </script>
   <script>
     const data = JSON.parse(document.getElementById('stats-data').textContent);
   </script>
   ```

2. **If you must embed JSON inside an executable `<script>`, pass the
   hex-escape flags to `json_encode`:**

   ```twig
   {% set json_safe = constant('JSON_HEX_TAG') b-or constant('JSON_HEX_AMP') b-or constant('JSON_HEX_APOS') b-or constant('JSON_HEX_QUOT') %}
   const data = {{ payload|json_encode(json_safe)|raw }};
   ```

   This is what `templates/admin/_stats.html.twig` does. Without these
   flags, a user value containing `</script>` or an apostrophe breaks out
   of the surrounding script or string.

3. **No `|raw` inside `#fiche-immersion-print`.**
   That block (`templates/admin/questionnaire/detail.html.twig`) is round-tripped
   through `innerHTML` + `doc.write()` for printing. Any `|raw` or inline
   event handler inside would re-execute on print. The block itself has a
   comment marking the invariant.

## JavaScript rules

4. **No new `onclick=`, `onload=`, `onsubmit=`, etc. in templates.**
   Inline event handlers force `'unsafe-inline'` in CSP `script-src`.
   Use `data-*` attributes + `addEventListener` instead.

5. **No `innerHTML` with concatenated runtime values.**
   Use `textContent` for plain strings or `document.createElement` + `replaceChildren`
   for structured content. The `renderAlert` / `renderStatus` helpers in
   `templates/easyadmin/json_field.html.twig` are the model.

## CDN scripts and styles

6. **New third-party CDN assets must be version-pinned and carry SRI.**

   - No `@latest`, no floating tags.
   - Add `integrity="sha384-..."` with the actual hash of the pinned file.
     Compute with:
     ```sh
     curl -sS <url> | openssl dgst -sha384 -binary | openssl base64 -A
     ```
   - Add `crossorigin="anonymous"` so SRI can actually be checked.
   - Update the CSP `script-src` / `style-src` in `frankenphp/Caddyfile`
     if the host is new.

## CSP

CSP is currently in **report-only** mode (`Content-Security-Policy-Report-Only`
in `frankenphp/Caddyfile`). It keeps `'unsafe-inline'` and `'unsafe-eval'`
while:

- `@json-editor/json-editor` still needs `eval`,
- some EasyAdmin views still emit inline styles.

Before promoting CSP to enforcing mode:

1. Remove every remaining inline event handler and `<style>` block we own.
2. Drop `'unsafe-inline'` from `script-src`, then `style-src`.
3. Drop `'unsafe-eval'` once @json-editor no longer needs it (or replace
   the library).
4. Rename the header from `Content-Security-Policy-Report-Only` to
   `Content-Security-Policy`.

## CSRF

The autosave endpoint (`POST /admin/model-questionnaire/{id}/autosave`)
validates an `X-CSRF-Token` header against the `autosave` stateless token
id declared in `config/packages/csrf.yaml`. New admin-side fetches that
mutate state must do the same — request a token in Twig with
`csrf_token('<id>')`, send it as `X-CSRF-Token`, validate in the controller
with `$this->isCsrfTokenValid('<id>', $token)`. Register the id in
`framework.csrf_protection.stateless_token_ids` first.
