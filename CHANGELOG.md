# Changelog

All notable changes to the Google Calendar Block plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Enhanced debugging system with console output
- Support for Google Calendar share URLs
- Automatic calendar ID extraction from URLs
- Comprehensive error logging
- WordPress debug mode integration

### Changed
- Improved title resolution with multiple fallback options
- Enhanced security with proper output escaping
- Updated to use WordPress coding standards
- Optimized API request handling

### Fixed
- Fixed calendar ID encoding for group calendars
- Resolved "No Title" issue with proper title extraction
- Fixed month title updating during navigation
- Corrected time range display (10am-8pm)
- Fixed scrolling and sticky header functionality

### Security
- Added proper output escaping for all user data
- Implemented nonce verification for forms
- Enhanced input validation and sanitization
- Added WordPress debug mode checks for production safety

## [1.0.0] - 2024-10-16

### Added
- Initial release of Google Calendar Block
- Google Calendar API integration
- Custom calendar display with modern design
- Block editor integration
- Responsive design for mobile devices
- Customizable accent colors
- Multiple view options (day, week, month)
- Event caching system
- WordPress admin settings page
- Comprehensive error handling

### Features
- **Calendar Display**: Beautiful, responsive calendar interface
- **API Integration**: Seamless Google Calendar API connection
- **Customization**: Accent colors, view options, event limits
- **Performance**: Built-in caching and optimized loading
- **Security**: WordPress security best practices
- **Accessibility**: WCAG compliant interface
- **Mobile Ready**: Responsive design for all devices

### Technical
- WordPress Block API implementation
- Google Calendar API v3 integration
- SCSS/CSS styling system
- JavaScript frontend functionality
- PHP backend processing
- Caching system with transients
- Error logging and debugging
- WordPress coding standards compliance

---

## Version History Summary

### v1.0.0 (Initial Release)
- Complete Google Calendar integration
- Modern, responsive calendar design
- WordPress block editor support
- Admin settings and configuration
- Comprehensive documentation

### Development Notes
- Built with WordPress Block API
- Uses Google Calendar API v3
- Follows WordPress coding standards
- Includes comprehensive testing
- Production-ready with security features

---

**Note**: This changelog follows semantic versioning. Breaking changes will be noted with a major version bump, new features with minor version bumps, and bug fixes with patch version bumps.
