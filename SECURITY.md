# Security Policy

## Security model

Syriable Filament Translator resolves convention-based translation strings for Filament UIs. It has
an intentionally minimal security surface:

| Area | Posture |
| --- | --- |
| **Authorization** | None. The package never reads user data, performs permission checks, or exposes a privilege surface — it only maps class/component names to lang keys and returns the resolved string (or falls back to Filament's native label). |
| **Output / XSS** | Not applicable. The package renders no HTML and produces no markup; resolved strings are handed back to Filament/Laravel, which apply their normal escaping. |
| **HTTP / CSRF** | Not applicable. No routes, controllers, or request handling are registered by the package. |
| **SQL / data access** | Not applicable. No database queries are issued by the package. |
| **Filesystem writes** | Limited to local development. See "Lang file trust model" below. |

### Lang file trust model

Laravel loads `lang/*.php` files with `require`, which **executes the file as PHP**. This package
follows that same convention:

- `MissingTranslationKeyWriter` reads existing lang files via `require` when merging newly
  scaffolded keys. A compromised lang file means code execution on read — this is the standard
  Laravel assumption and is **not** specific to this package.
- **Treat `lang/*.php` as trusted source code.** Do not load lang files from untrusted or
  user-writable locations.

### Automatic key creation safeguards

`MissingTranslationKeyWriter` scaffolds missing **required** keys during development only:

- **Production-gated.** Writes are skipped on production requests regardless of configuration; lang
  files are never written on live requests.
- **Path-validated.** The target file path derived from a convention key is canonicalised and
  validated to remain within `lang_path()`. Crafted keys containing `..` or directory separators
  cannot escape the lang directory; an invalid path raises an exception instead of writing outside
  the intended location.
- **Non-destructive.** Existing values are never overwritten, and only required attributes are
  seeded.

### Dependency trust boundary

The package integrates with Filament internals using `spatie/invade` and reflection. This couples
it to trusted first-party Filament/Laravel code only; it does not expand the trust boundary to any
external input. See the "Filament compatibility & upgrade strategy" section of the
[README](README.md) for how internal access is guarded across Filament versions.

## Supported versions

Security fixes target the latest released minor version. Please keep up to date with the current
`syriable/filament-translator` release.

## Reporting a vulnerability

Please report security vulnerabilities through GitHub's private vulnerability reporting on the
[repository security page](https://github.com/syriable/filament-translator/security/policy) rather
than via public issues. We will acknowledge your report and work on a fix as a priority.
