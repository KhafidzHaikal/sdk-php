# Changelog

All notable changes to `bjb/qrismpm-sdk` will be documented in this file.

## [1.0.0] - 2026-04-16

### Added
- Access token generation (RSA SHA256withRSA → lowercase hex)
- QRIS Generate QR API (`POST /v1.0/qr/qr-mpm-generate`)
- QRIS Check Status API (`POST /v1.0/qr/qr-mpm-query`)
- Callback signature verification (HMAC-SHA512, timing-safe)
- Auto SSL skip for dev environment (`devapi` URL detection)
- Structured logging with request/response headers and body
- Custom `ApiException` with SNAP BI `responseCode` / `responseMessage`
- Native PHP HTTP client (no Guzzle dependency)
- PSR-4 autoload, PHP 8.0+ typed properties
