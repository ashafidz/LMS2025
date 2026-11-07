# ADR-015: reCAPTCHA v3 and attempt limits on login, register, and forgot password

- **Status**: Accepted
- **Date**: 2026-11-07
- **Deciders**: Project owner

---

## Context and Problem Statement
Only the contact form had reCAPTCHA. Login had a throttle (5 per minute per email and IP), registration had no limit at all,
and forgot password only the broker's one email per 60 seconds per address. The owner asked for a captcha and a 5-attempt limit.

## Decision
- **reCAPTCHA v3, invisible**, reusing the contact form's keys. No checkbox, nothing for the user to solve. Empty keys switch it off
  completely (local, tests): no attribute, script, input, or legal line is rendered and no request goes to Google.
- One verification service (`App\Services\Recaptcha`) shared by the contact form and the auth forms. Fortify's routes live in
  `vendor/`, so `App\Http\Middleware\ProtectAuthForms` (web group) acts on the route names `login.store`, `register.store`,
  `password.email`. The browser side is `public/js/rs-recaptcha.js`: it asks for a token on submit, then sends the form again.
- **Login fails open** when Google cannot be reached (logged), so a Google outage never locks people out; its own throttle stays.
  **Register and forgot password fail closed**, because they create accounts or send email.
- **Register limit**: 5 per minute per IP (`AUTH_REGISTER_PER_MINUTE`), checked before the captcha so a flood does not cause calls
  to Google. Over the limit shows the existing 429 page.
- Failures are logged without email, password, or token. The user only sees a plain Indonesian message.

## Consequences
- Schools often share one public IP (NAT). Thirty students registering together in a lab will hit 5 per minute; the owner can raise
  `AUTH_REGISTER_PER_MINUTE` in `.env` without a code change.
- A failed captcha on login is rejected before Fortify's throttle runs, so it does not count toward the login limit.
- The score threshold is `RECAPTCHA_MIN_SCORE` (shared with the contact form).
