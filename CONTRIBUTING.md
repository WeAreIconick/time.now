# Contributing to Google Calendar Block

Thank you for your interest in contributing to the Google Calendar Block plugin! This document provides guidelines and information for contributors.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Testing](#testing)
- [Submitting Changes](#submitting-changes)
- [Reporting Issues](#reporting-issues)
- [Feature Requests](#feature-requests)

## Code of Conduct

This project adheres to the WordPress community guidelines. Please be respectful, constructive, and inclusive in all interactions.

## Getting Started

### Prerequisites

- WordPress development environment
- Node.js and npm
- Git
- PHP 7.4 or higher
- WordPress 5.0 or higher

### Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:
   ```bash
   git clone https://github.com/your-username/calendar-block.git
   cd calendar-block
   ```

## Development Setup

### 1. Install Dependencies

```bash
npm install
```

### 2. Development Commands

```bash
# Start development server with hot reloading
npm start

# Build for production
npm run build

# Lint JavaScript code
npm run lint:js

# Lint CSS code
npm run lint:css

# Format code
npm run format

# Run unit tests
npm run test:unit

# Run end-to-end tests
npm run test:e2e
```

### 3. WordPress Environment

1. Set up a local WordPress installation
2. Install the plugin in your development environment
3. Configure a Google Calendar API key for testing
4. Create test calendars and events

## Coding Standards

### PHP Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Use PHPCS for code quality checking
- Ensure all output is properly escaped
- Use WordPress functions instead of PHP equivalents when available

```php
// Good
$title = esc_html( get_the_title() );
$url = esc_url( $link );

// Bad
$title = get_the_title();
$url = $link;
```

### JavaScript Standards

- Follow [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)
- Use ESLint for code quality
- Prefer modern ES6+ syntax
- Use meaningful variable and function names

```javascript
// Good
const initializeCalendar = (container) => {
  // Implementation
};

// Bad
const initCal = (c) => {
  // Implementation
};
```

### CSS Standards

- Follow WordPress CSS coding standards
- Use meaningful class names
- Prefer semantic HTML over styling hacks
- Ensure responsive design

```css
/* Good */
.calendar-block-wrapper {
  display: flex;
  flex-direction: column;
}

/* Bad */
.wrapper {
  display: flex;
  flex-direction: column;
}
```

## Testing

### Unit Tests

```bash
npm run test:unit
```

Write unit tests for:
- PHP classes and methods
- JavaScript functions
- API integrations
- Data transformations

### End-to-End Tests

```bash
npm run test:e2e
```

Test complete user workflows:
- Block insertion and configuration
- Calendar display and interaction
- API error handling
- Mobile responsiveness

### Manual Testing Checklist

- [ ] Block appears in WordPress editor
- [ ] Calendar displays correctly with events
- [ ] Responsive design works on mobile
- [ ] API errors are handled gracefully
- [ ] Caching works properly
- [ ] Performance is acceptable

## Submitting Changes

### 1. Create a Branch

```bash
git checkout -b feature/your-feature-name
```

### 2. Make Changes

- Write clean, documented code
- Add tests for new functionality
- Update documentation as needed
- Follow coding standards

### 3. Test Your Changes

```bash
npm run lint:js
npm run lint:css
npm run test:unit
npm run build
```

### 4. Commit Changes

Use descriptive commit messages:

```bash
git commit -m "Add support for recurring events

- Implement recurring event detection
- Add visual indicators for recurring events
- Update documentation with new features"
```

### 5. Push and Create Pull Request

```bash
git push origin feature/your-feature-name
```

Create a pull request with:
- Clear description of changes
- Reference to related issues
- Screenshots or videos if applicable
- Testing instructions

## Reporting Issues

### Bug Reports

Include the following information:

1. **WordPress Version**: e.g., 6.4.1
2. **Plugin Version**: e.g., 1.0.0
3. **PHP Version**: e.g., 8.1.0
4. **Browser**: e.g., Chrome 119.0
5. **Steps to Reproduce**: Detailed steps
6. **Expected Behavior**: What should happen
7. **Actual Behavior**: What actually happens
8. **Error Messages**: Any console errors or PHP errors
9. **Screenshots**: If applicable

### Template

```markdown
**WordPress Version**: 6.4.1
**Plugin Version**: 1.0.0
**PHP Version**: 8.1.0
**Browser**: Chrome 119.0

**Description**
Brief description of the issue.

**Steps to Reproduce**
1. Go to '...'
2. Click on '...'
3. See error

**Expected Behavior**
What should happen.

**Actual Behavior**
What actually happens.

**Screenshots**
If applicable, add screenshots.

**Additional Context**
Any other context about the problem.
```

## Feature Requests

### Guidelines

- Check existing issues first
- Provide clear use case
- Explain the benefit to users
- Consider implementation complexity
- Provide mockups or examples if applicable

### Template

```markdown
**Feature Request**
Brief description of the feature.

**Use Case**
Why is this feature needed?

**Proposed Solution**
How should this work?

**Alternatives Considered**
Other ways to solve this problem.

**Additional Context**
Mockups, examples, or related features.
```

## Development Guidelines

### Security

- Always sanitize user input
- Escape all output
- Use WordPress nonces for forms
- Validate all data
- Follow security best practices

### Performance

- Minimize database queries
- Use caching appropriately
- Optimize JavaScript loading
- Consider mobile performance
- Test with large datasets

### Accessibility

- Use semantic HTML
- Provide alt text for images
- Ensure keyboard navigation
- Test with screen readers
- Follow WCAG guidelines

### Documentation

- Document all public functions
- Update README for new features
- Add inline comments for complex logic
- Include usage examples
- Keep changelog updated

## Getting Help

- **Documentation**: Check the README and inline docs
- **Issues**: Search existing GitHub issues
- **Discussions**: Use GitHub Discussions for questions
- **WordPress.org**: Plugin support forum

## Recognition

Contributors will be recognized in:
- README.md contributors section
- Plugin changelog
- Release notes

Thank you for contributing to the WordPress community! 🎉
