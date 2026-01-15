# Changelog

All notable changes to this package will be documented in this file.

## [1.1.0] - 2026-01-15

### Added
- Pusher-compatible API for seamless Laravel Echo integration
- `php artisan livewave:install` command for easy setup
- Support for multi-app architecture (like Laravel Reverb)
- `trigger()` method for Pusher-compatible event triggering
- `authorizeChannel()` and `authorizePresenceChannel()` methods
- `getChannelInfo()`, `getChannels()`, and `getPresenceUsers()` methods
- `getEchoConfig()` helper for frontend configuration
- WebSocket configuration options (host, port, scheme, TLS)
- Blade directive `@livewaveScripts` for injecting Echo config

### Changed
- Configuration structure updated for multi-app support
- `LiveWaveClient` now uses Pusher-compatible authentication
- `LiveWaveBroadcaster` updated for proper channel authorization
- Webhook signature verification improved with timestamp support

### Removed
- Old `api_key` and `api_secret` config keys (replaced with `app_key` and `app_secret`)

## [1.0.0] - 2026-01-13

### Added
- Initial release
- `LiveWave` facade for easy access
- Broadcasting driver for Laravel
- Channel management API
- Webhook handling with signature verification
- Notification builder
- `LiveWaveFake` for testing
