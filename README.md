# Time.now() - Google Calendar Block for WordPress

A powerful WordPress block that displays Google Calendar events in a beautiful, customizable calendar interface. Perfect for showcasing events, schedules, and appointments on your WordPress website.

## Features

- 🗓️ **Beautiful Calendar Display** - Modern, responsive calendar interface
- 📱 **Mobile Responsive** - Works perfectly on all devices
- 🎨 **Customizable Design** - Match your site's branding with accent colors
- ⚡ **Fast Performance** - Built-in caching and optimized loading
- 🔧 **Easy Setup** - Simple Google Calendar integration
- 📊 **Multiple Views** - Day, week, and month views
- 🌐 **Share URL Support** - Just paste your Google Calendar share URL
- 🔒 **Secure** - Follows WordPress security best practices

## Installation

### From WordPress Admin

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for "Time.now()" or "Google Calendar Block"
3. Click **Install** and then **Activate**

### Manual Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/time-now/` directory
3. Activate the plugin through the **Plugins** menu in WordPress

## Quick Start

### 1. Get Your Google Calendar API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable the **Google Calendar API**
4. Create credentials (API Key)
5. Copy your API key

### 2. Configure the Plugin

1. Go to **Settings > Time.now()** in WordPress admin
2. Enter your Google Calendar API key
3. Save settings

### 3. Add Calendar to Your Page

1. Edit any page or post
2. Click the **+** button to add a block
3. Search for "Google Calendar" and select it
4. Enter your Calendar ID or share URL
5. Customize the display options
6. Publish your page

## Configuration

### Getting Your Calendar ID

#### Method 1: Share URL (Easiest)
1. Open your Google Calendar
2. Click the **Settings** gear icon
3. Select **Settings and sharing**
4. Scroll down to **Integrate calendar**
5. Copy the **Public URL to this calendar**
6. Paste this URL into the Calendar ID field

#### Method 2: Calendar ID
1. In Google Calendar settings, find your **Calendar ID**
2. It looks like: `your-email@gmail.com` or `group@group.calendar.google.com`
3. Copy and paste this ID

### Block Settings

- **Calendar ID/URL**: Your Google Calendar identifier or share URL
- **Default View**: Choose between day, week, or month view
- **Event Limit**: Maximum number of events to display
- **Show Weekends**: Toggle weekend visibility
- **Accent Color**: Customize the calendar's accent color

## Customization

### Styling

The calendar uses CSS custom properties for easy theming:

```css
.calendar-block-wrapper {
  --accent-color: #3b82f6;
  --text-color: #1f2937;
  --border-color: #e5e7eb;
  --background-color: #ffffff;
}
```

### Time Range

The calendar displays events from **10:00 AM to 8:00 PM** by default. This can be customized by modifying the JavaScript code.

### Event Display

Events show:
- **Title** (from Google Calendar event summary/title)
- **Time** (start and end times)
- **Color coding** (based on calendar color or random assignment)

## API Reference

### PHP Classes

#### `Calendar_Block`
Main plugin class that handles initialization and block registration.

```php
$calendar_block = new Calendar_Block();
```

#### `Google_Calendar_API`
Handles all Google Calendar API interactions.

```php
$api = new Google_Calendar_API();
$events = $api->get_events($calendar_id);
```

#### `Block_Renderer`
Responsible for server-side block rendering.

```php
$renderer = new Block_Renderer();
$output = $renderer->render($attributes, $content, $block);
```

### JavaScript Functions

#### `initializeAllCalendars()`
Initializes all calendar blocks on the page.

#### `createCalendarHTML(events, accentColor, weekStartDate)`
Creates the calendar HTML structure.

#### `positionEvents(container, events)`
Positions events within the calendar grid.

## Hooks and Filters

### Actions

```php
// Before calendar block renders
do_action('calendar_block_before_render', $attributes);

// After calendar block renders
do_action('calendar_block_after_render', $attributes, $events);
```

### Filters

```php
// Modify events before display
$events = apply_filters('calendar_block_events', $events, $calendar_id);

// Modify calendar attributes
$attributes = apply_filters('calendar_block_attributes', $attributes);
```

## Troubleshooting

### Common Issues

#### "API Error 404: Not Found"
- Verify your Calendar ID is correct
- Ensure the calendar is public or you have proper access
- Check that your API key has Calendar API enabled

#### Events Show "No Title"
- Check that your Google Calendar events have titles/summaries
- Verify the API key has proper permissions
- Enable WordPress debug mode to see detailed logs

#### Calendar Not Loading
- Check browser console for JavaScript errors
- Verify the frontend script is loading
- Ensure no caching plugins are interfering

### Debug Mode

Enable WordPress debug mode to see detailed logging:

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Debug logs will be written to `/wp-content/debug.log`

## Development

### Building the Plugin

```bash
# Install dependencies
npm install

# Build for production
npm run build

# Watch for changes during development
npm start

# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css
```

### File Structure

```
calendar-block/
├── admin/                 # Admin interface
├── build/                # Compiled assets
├── includes/             # PHP classes
├── src/                  # Source files
│   ├── edit.js          # Block editor
│   ├── view.js          # Frontend display
│   ├── style.scss       # Styles
│   └── editor.scss      # Editor styles
├── block.json           # Block configuration
├── calendar-block.php   # Main plugin file
└── render.php           # Server-side rendering
```

### Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and linting
5. Submit a pull request

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## Support

- **Documentation**: [Plugin Documentation](https://github.com/your-repo/calendar-block/wiki)
- **Issues**: [GitHub Issues](https://github.com/your-repo/calendar-block/issues)
- **Community**: [WordPress.org Support Forum](https://wordpress.org/support/plugin/calendar-block/)

## License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## Credits

- **Google Calendar API** - For providing the calendar data
- **WordPress Block API** - For the block development framework
- **Tailwind CSS** - For utility-first CSS framework
- **FullCalendar** - For calendar functionality inspiration

---

**Made with ❤️ for the WordPress community**