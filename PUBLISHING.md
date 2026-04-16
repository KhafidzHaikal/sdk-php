# Publishing Guide — bjb/qrismpm-sdk

How to publish the PHP SDK to Packagist so partners can install via `composer require bjb/qrismpm-sdk`.

---

## Prerequisites

- GitHub account
- Packagist account (https://packagist.org) — sign in with GitHub
- SDK code ready in `sdk-php/`

---

## Step 1: Create GitHub Repository

```bash
cd sdk-php
git init
git add .
git commit -m "Initial release v1.0.0"
```

Go to https://github.com/new and create a repo (e.g. `bjb-qrismpm-sdk-php`).

```bash
git remote add origin https://github.com/YOUR_USERNAME/bjb-qrismpm-sdk-php.git
git branch -M main
git push -u origin main
```

---

## Step 2: Create a Git Tag (Version)

Packagist uses git tags for versioning. No tag = no installable version.

```bash
git tag v1.0.0
git push origin v1.0.0
```

---

## Step 3: Submit to Packagist

1. Go to https://packagist.org/packages/submit
2. Paste your GitHub repo URL: `https://github.com/YOUR_USERNAME/bjb-qrismpm-sdk-php`
3. Click "Check" → then "Submit"

Packagist reads `composer.json` and registers the package as `bjb/qrismpm-sdk`.

---

## Step 4: Enable Auto-Update (Recommended)

So Packagist picks up new tags automatically:

1. In your GitHub repo → Settings → Webhooks → Add webhook
2. Payload URL: `https://packagist.org/api/github?username=YOUR_PACKAGIST_USERNAME`
3. Content type: `application/json`
4. Secret: get from https://packagist.org/profile/ → "Show API Token"
5. Events: "Just the push event"
6. Save

Or use the Packagist GitHub integration:
1. Go to https://packagist.org/profile/ → "Settings"
2. Connect your GitHub account
3. Enable auto-sync

---

## Step 5: Verify

```bash
composer show bjb/qrismpm-sdk
```

Partners can now install:

```bash
composer require bjb/qrismpm-sdk
```

---

## Publishing New Versions

```bash
# Make changes
git add .
git commit -m "Fix: signature handling"

# Tag new version
git tag v1.0.1
git push origin main --tags
```

Packagist picks it up automatically (if webhook is set up).

### Version Naming

| Tag | Composer Version |
|-----|-----------------|
| `v1.0.0` | `1.0.0` |
| `v1.0.1` | `1.0.1` |
| `v1.1.0` | `1.1.0` |
| `v2.0.0` | `2.0.0` |

---

## Files Required for Packagist

Make sure these exist in your repo:

| File | Purpose |
|------|---------|
| `composer.json` | Package name, autoload, dependencies |
| `src/` | Source code (PSR-4) |
| `README.md` | Documentation |
| `LICENSE` | License file |
| `CHANGELOG.md` | Version history |

### Minimum `composer.json`

```json
{
  "name": "bjb/qrismpm-sdk",
  "description": "BJB QRIS MPM PHP SDK",
  "type": "library",
  "license": "MIT",
  "require": {
    "php": ">=8.0",
    "ext-json": "*",
    "ext-openssl": "*"
  },
  "autoload": {
    "psr-4": {
      "Bjb\\QrisMpm\\": "src/"
    }
  }
}
```

---

## .gitignore

Create `.gitignore` in `sdk-php/`:

```
/vendor/
composer.lock
.env
*.pem
```

---

## Quick Reference

```bash
# First time publish
cd sdk-php
git init
git add .
git commit -m "v1.0.0"
git remote add origin https://github.com/USER/bjb-qrismpm-sdk-php.git
git push -u origin main
git tag v1.0.0
git push origin v1.0.0
# Then submit URL on packagist.org/packages/submit

# New version
git add .
git commit -m "v1.0.1 — fix logging"
git tag v1.0.1
git push origin main --tags
```
