# Google Calendar Block API Documentation

This document provides comprehensive API documentation for developers working with the Google Calendar Block plugin.

## Table of Contents

- [PHP Classes](#php-classes)
- [JavaScript API](#javascript-api)
- [WordPress Hooks](#wordpress-hooks)
- [Google Calendar API Integration](#google-calendar-api-integration)
- [Block Attributes](#block-attributes)
- [Event Data Structure](#event-data-structure)
- [Customization](#customization)

## PHP Classes

### Calendar_Block

Main plugin class that handles initialization and block registration.

#### Methods

##### `__construct()`
Initializes the plugin and registers hooks.

##### `register_block()`
Registers the calendar block with WordPress.

##### `enqueue_editor_scripts()`
Enqueues scripts and styles for the block editor.

##### `enqueue_frontend_scripts()`
Enqueues scripts and styles for the frontend.

#### Usage

```php
$calendar_block = new Calendar_Block();
```

### Google_Calendar_API

Handles all Google Calendar API interactions.

#### Methods

##### `__construct($api_key)`
Initializes the API client with the provided API key.

**Parameters:**
- `$api_key` (string) - Google Calendar API key

##### `get_events($calendar_id, $start_date = null, $end_date = null)`
Retrieves events from a Google Calendar.

**Parameters:**
- `$calendar_id` (string) - Calendar ID or share URL
- `$start_date` (string, optional) - Start date in Y-m-d format
- `$end_date` (string, optional) - End date in Y-m-d format

**Returns:** Array of events or WP_Error on failure

##### `extract_calendar_id($input)`
Extracts calendar ID from various input formats.

**Parameters:**
- `$input` (string) - Calendar ID, share URL, or other format

**Returns:** Extracted calendar ID

#### Usage

```php
$api = new Google_Calendar_API('your-api-key');
$events = $api->get_events('calendar-id@gmail.com', '2024-01-01', '2024-01-31');
```

### Block_Renderer

Responsible for server-side block rendering.

#### Methods

##### `render($attributes, $content, $block)`
Renders the calendar block on the server side.

**Parameters:**
- `$attributes` (array) - Block attributes
- `$content` (string) - Block content
- `$block` (WP_Block) - Block instance

**Returns:** HTML string

#### Usage

```php
$renderer = new Block_Renderer();
$output = $renderer->render($attributes, $content, $block);
```

### Calendar_Cache

Handles event caching for improved performance.

#### Methods

##### `get_cache_key($calendar_id, $start_date, $end_date)`
Generates a unique cache key for the request.

##### `get_cached_events($cache_key)`
Retrieves cached events.

##### `set_cached_events($cache_key, $events, $expiration = 1800)`
Stores events in cache.

##### `clear_cache($calendar_id = null)`
Clears cached events.

#### Usage

```php
$cache = new Calendar_Cache();
$cached_events = $cache->get_cached_events($cache_key);
```

## JavaScript API

### Main Functions

#### `initializeAllCalendars()`
Initializes all calendar blocks on the page.

```javascript
// Automatically called on DOM ready
initializeAllCalendars();
```

#### `createCalendarHTML(events, accentColor, weekStartDate)`
Creates the calendar HTML structure.

**Parameters:**
- `events` (Array) - Array of event objects
- `accentColor` (string) - Hex color code
- `weekStartDate` (Date) - Starting date for the week view

**Returns:** HTML string

#### `positionEvents(container, events)`
Positions events within the calendar grid.

**Parameters:**
- `container` (HTMLElement) - Calendar container element
- `events` (Array) - Array of event objects

#### `createEventElement(event, accentColor)`
Creates HTML for a single event.

**Parameters:**
- `event` (Object) - Event data object
- `accentColor` (string) - Hex color code

**Returns:** HTMLElement

### Utility Functions

#### `formatEventTime(startTime, endTime)`
Formats event time for display.

**Parameters:**
- `startTime` (Date) - Event start time
- `endTime` (Date, optional) - Event end time

**Returns:** Formatted time string

#### `getColorClassFromHex(hexColor)`
Converts hex color to CSS class name.

**Parameters:**
- `hexColor` (string) - Hex color code

**Returns:** CSS class name

#### `parseEventsFromAttributes(container)`
Parses event data from container data attributes.

**Parameters:**
- `container` (HTMLElement) - Calendar container

**Returns:** Array of event objects

## WordPress Hooks

### Actions

#### `calendar_block_before_render`
Fired before the calendar block renders.

```php
add_action('calendar_block_before_render', function($attributes) {
    // Modify attributes or add custom logic
}, 10, 1);
```

#### `calendar_block_after_render`
Fired after the calendar block renders.

```php
add_action('calendar_block_after_render', function($attributes, $events) {
    // Add custom content or tracking
}, 10, 2);
```

#### `calendar_block_enqueue_scripts`
Fired when scripts are enqueued.

```php
add_action('calendar_block_enqueue_scripts', function() {
    // Add custom scripts or styles
});
```

### Filters

#### `calendar_block_events`
Filter events before display.

```php
add_filter('calendar_block_events', function($events, $calendar_id) {
    // Modify events array
    return $events;
}, 10, 2);
```

#### `calendar_block_attributes`
Filter block attributes.

```php
add_filter('calendar_block_attributes', function($attributes) {
    // Modify default attributes
    return $attributes;
});
```

#### `calendar_block_cache_expiration`
Filter cache expiration time.

```php
add_filter('calendar_block_cache_expiration', function($expiration) {
    // Modify cache duration (default: 1800 seconds)
    return 3600; // 1 hour
});
```

#### `calendar_block_api_url`
Filter Google Calendar API URL.

```php
add_filter('calendar_block_api_url', function($url, $calendar_id) {
    // Modify API URL for custom endpoints
    return $url;
}, 10, 2);
```

## Google Calendar API Integration

### Authentication

The plugin uses API key authentication for public calendar access.

```php
// Set API key in WordPress admin or programmatically
update_option('calendar_block_api_key', 'your-api-key');
```

### API Endpoints

#### Events Endpoint
```
GET https://www.googleapis.com/calendar/v3/calendars/{calendarId}/events
```

**Parameters:**
- `key` - API key
- `timeMin` - Start time (RFC3339)
- `timeMax` - End time (RFC3339)
- `singleEvents` - true (expand recurring events)
- `orderBy` - startTime
- `maxResults` - 2500

### Rate Limits

- 1,000,000 queries per day
- 100 queries per 100 seconds per user

### Error Handling

```php
// Check for API errors
if (is_wp_error($events)) {
    $error_message = $events->get_error_message();
    // Handle error
}

// Check for API response errors
if (isset($events['error'])) {
    $error_message = $events['error'];
    // Handle error
}
```

## Block Attributes

### Available Attributes

```javascript
const blockAttributes = {
    calendarId: {
        type: 'string',
        default: ''
    },
    defaultView: {
        type: 'string',
        default: 'dayGridMonth',
        enum: ['dayGridMonth', 'timeGridWeek', 'timeGridDay']
    },
    eventLimit: {
        type: 'number',
        default: 3
    },
    showWeekends: {
        type: 'boolean',
        default: true
    },
    accentColor: {
        type: 'string',
        default: '#3b82f6'
    }
};
```

### Default Values

```javascript
const defaultAttributes = {
    calendarId: '',
    defaultView: 'dayGridMonth',
    eventLimit: 3,
    showWeekends: true,
    accentColor: '#3b82f6'
};
```

## Event Data Structure

### Google Calendar API Response

```json
{
    "id": "event-id",
    "summary": "Event Title",
    "description": "Event description",
    "start": {
        "dateTime": "2024-01-01T10:00:00-05:00",
        "timeZone": "America/New_York"
    },
    "end": {
        "dateTime": "2024-01-01T11:00:00-05:00",
        "timeZone": "America/New_York"
    },
    "location": "Event Location",
    "htmlLink": "https://calendar.google.com/event?eid=...",
    "colorId": "1"
}
```

### Transformed Event Object

```javascript
{
    id: "event-id",
    title: "Event Title",
    start: "2024-01-01T10:00:00-05:00",
    end: "2024-01-01T11:00:00-05:00",
    allDay: false,
    description: "Event description",
    location: "Event Location",
    url: "https://calendar.google.com/event?eid=...",
    backgroundColor: "#3b82f6",
    borderColor: "#3b82f6"
}
```

## Customization

### CSS Custom Properties

```css
.calendar-block-wrapper {
    --accent-color: #3b82f6;
    --text-color: #1f2937;
    --border-color: #e5e7eb;
    --background-color: #ffffff;
    --hover-color: #f3f4f6;
    --today-color: #dbeafe;
}
```

### Custom Styling

```css
/* Custom calendar styling */
.calendar-block-wrapper {
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.calendar-event {
    border-radius: 6px;
    font-weight: 500;
}

.calendar-event:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
```

### JavaScript Customization

```javascript
// Override default behavior
document.addEventListener('DOMContentLoaded', function() {
    // Custom initialization
    const calendars = document.querySelectorAll('.calendar-block-wrapper');
    
    calendars.forEach(container => {
        // Add custom event listeners
        container.addEventListener('eventClick', function(event) {
            // Custom event handling
            console.log('Event clicked:', event.detail);
        });
    });
});
```

## Debugging

### Enable Debug Mode

```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Debug Information

The plugin provides comprehensive debugging information:

- **PHP Debug Logs**: Written to `/wp-content/debug.log`
- **Console Output**: JavaScript debugging in browser console
- **API Response Logging**: Full Google Calendar API responses
- **Event Processing**: Step-by-step event transformation

### Debug Functions

```php
// Log debug information
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Debug message: ' . print_r($data, true));
}

// Console output for JavaScript debugging
echo '<script>console.log("Debug:", ' . wp_json_encode($data) . ');</script>';
```

## Performance Considerations

### Caching Strategy

- **Event Cache**: 30 minutes default expiration
- **API Key Cache**: Stored in WordPress options
- **Block Cache**: WordPress block caching

### Optimization Tips

1. **Limit Events**: Use `eventLimit` attribute
2. **Date Range**: Limit API requests to necessary date ranges
3. **Caching**: Enable WordPress object caching
4. **CDN**: Use CDN for static assets

### Monitoring

```php
// Monitor API usage
add_action('calendar_block_api_request', function($calendar_id, $start_date, $end_date) {
    // Log API usage for monitoring
    error_log("API Request: {$calendar_id} from {$start_date} to {$end_date}");
}, 10, 3);
```

---

This API documentation provides comprehensive information for developers working with the Google Calendar Block plugin. For additional support, please refer to the [Contributing Guide](CONTRIBUTING.md) or create an issue on GitHub.
