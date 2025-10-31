/**
 * Frontend JavaScript for Google Calendar Block - Figma Design
 *
 * @package
 */

/**
 * Initialize the custom calendar with Figma design
 * @param {HTMLElement} container - The calendar container element
 * @returns {void}
 */
function initializeCustomCalendar(container) {
  // Prevent double initialization
  if (container.dataset.initialized === 'true') {
    return;
  }
  container.dataset.initialized = 'true';
  
  let calendarId = container.dataset.calendarId;
  let events = [];
  const defaultView = container.dataset.defaultView || "timeGridWeek";
  const accentColor = container.dataset.accentColor || "#3b82f6";

  // Try to parse events from data-events attribute
  if (container.dataset.events) {
    try {
      events = JSON.parse(container.dataset.events);
    } catch (e) {
      // Error parsing events - continue with empty array
    }
  } else {
    // No data-events attribute found
  }

  // If no events found in data-events, check if calendarId contains JSON data
  if (!events || events.length === 0) {
    if (
      calendarId &&
      (calendarId.startsWith("[") || calendarId.startsWith("{"))
    ) {
      try {
        events = JSON.parse(calendarId);
        // Clear calendarId since it actually contains events
        calendarId = "";
      } catch (e) {
        // Error parsing events from calendarId
      }
    }
  }

  // If still no events, check all data attributes for JSON data
  if (!events || events.length === 0) {
    const allAttributes = container.attributes;
    for (let i = 0; i < allAttributes.length; i++) {
      const attr = allAttributes[i];
      const attrName = attr.name;
      const attrValue = attr.value;

      // Skip non-data attributes
      if (!attrName.startsWith("data-")) {
        continue;
      }

      // Check if this attribute contains JSON data
      if (
        attrValue &&
        (attrValue.startsWith("[") || attrValue.startsWith("{"))
      ) {
        try {
          const parsedData = JSON.parse(attrValue);
          if (Array.isArray(parsedData) && parsedData.length > 0) {
            events = parsedData;
            break;
          }
        } catch (e) {
          // Not valid JSON, continue
        }
      }
    }
  }

  // Set initial week date to today (always start with current date)
  let initialWeekDate = new Date();
  let eventDateRange = null;
  if (events && events.length > 0) {
    const firstEventDate = new Date(events[0].start);
    const lastEventDate = new Date(events[events.length - 1].start);

    // Always start with today, but store the event date range for navigation limits
    container.dataset.currentWeekDate = initialWeekDate.toISOString();

    // Calculate the date range of loaded events
    eventDateRange = {
      start: firstEventDate,
      end: lastEventDate,
    };
    container.dataset.eventDateRange = JSON.stringify({
      start: firstEventDate.toISOString(),
      end: lastEventDate.toISOString(),
    });
  }

  // Create calendar HTML
  const calendarHTML = createCalendarHTML(events, accentColor, initialWeekDate);
  container.innerHTML = calendarHTML;

  // Position events on the calendar
  positionEvents(container, events);

  // Add event listeners for navigation
  addNavigationListeners(container, events, accentColor, eventDateRange);
}

/**
 * Create the HTML structure matching the Figma design
 * @param {Array<Object>} events - Array of event objects
 * @param {string} accentColor - Hex color code for accent color
 * @param {Date} [weekStartDate] - Starting date for the week view
 * @returns {string} HTML string for the calendar
 */
function createCalendarHTML(events, accentColor, weekStartDate = new Date()) {
  // Calculate current week dates
  const currentWeekDates = getWeekDates(weekStartDate);
  const currentMonth = currentWeekDates[0].toLocaleDateString("en-US", {
    month: "long",
    year: "numeric",
  });

  // Day names
  const dayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

  // Generate day headers HTML
  let dayHeadersHTML = "";
  currentWeekDates.forEach((date, index) => {
    const dayName = dayNames[index];
    const dayNumber = date.getDate();
    const isToday = date.toDateString() === new Date().toDateString();

    dayHeadersHTML += `
			<div class="text-center py-3 border-r-2 border-black last:border-r-0">
				<div class="text-xs uppercase tracking-widest">${dayName}</div>
				<div class="text-2xl mt-1 ${
          isToday
            ? "bg-black text-white w-10 h-10 flex items-center justify-center mx-auto border-2 border-black"
            : ""
        }">${dayNumber}</div>
			</div>
		`;
  });

  // Generate time column HTML (10am - 8pm)
  let timeColumnHTML = "";
  for (let hour = 10; hour <= 20; hour++) {
    const timeLabel =
      hour === 12 ? "12 PM" : hour < 12 ? `${hour} AM` : `${hour - 12} PM`;
    timeColumnHTML += `<div class="h-16 border-b border-black pr-2 text-right pt-1 text-xs uppercase tracking-wide">${timeLabel}</div>`;
  }

  // Generate day columns HTML
  let dayColumnsHTML = "";
  currentWeekDates.forEach((date, index) => {
    const isToday = date.toDateString() === new Date().toDateString();
    dayColumnsHTML += `
			<div class="day-column border-r-2 border-black last:border-r-0 relative ${
        isToday ? "bg-yellow-50" : ""
      }">
				${Array(11)
          .fill()
          .map(
            (hour) =>
              `<div class="h-16 border-b border-black hover:bg-gray-50 transition-colors cursor-pointer"></div>`,
          )
          .join("")}
				<div class="absolute top-0 left-0 right-0 p-2 pointer-events-none">
					<div class="events-container space-y-1 pointer-events-auto"></div>
				</div>
			</div>
		`;
  });

  return `
		<!-- Height calculation: 
		     Desktop: Content(900px) + Border(8px) = 908px total
		     Mobile: Content(500px) + Border(8px) = 508px total
		-->
		<div class="h-[508px] md:h-[908px] flex flex-col bg-white border-4 border-black">
			<!-- Header -->
			<div class="flex flex-col md:flex-row items-center justify-between p-2 md:p-4 border-b-4 border-black bg-white gap-2 md:gap-4">
				<div class="flex items-center gap-2 md:gap-4 flex-wrap justify-center md:justify-start">
					<div class="flex items-center gap-2 px-3 py-1 bg-black text-white border-2 border-black">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
							<path d="M8 2v4"></path>
							<path d="M16 2v4"></path>
							<rect width="18" height="18" x="3" y="4" rx="2"></rect>
							<path d="M3 10h18"></path>
						</svg>
						<span class="uppercase tracking-wider text-sm md:text-base">Calendar</span>
					</div>
					<button class="today-button border-2 border-black hover:bg-black hover:text-white transition-colors px-3 py-1 text-xs md:text-sm font-medium">Today</button>
					<div class="flex items-center">
						<button class="nav-button border-2 border-black border-r-0 rounded-none hover:bg-black hover:text-white transition-colors w-8 h-8 md:w-9 md:h-9 flex items-center justify-center" data-action="prev">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 md:w-5 md:h-5">
								<path d="m15 18-6-6 6-6"></path>
							</svg>
						</button>
						<button class="nav-button border-2 border-black rounded-none hover:bg-black hover:text-white transition-colors w-8 h-8 md:w-9 md:h-9 flex items-center justify-center" data-action="next">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 md:w-5 md:h-5">
								<path d="m9 18 6-6-6-6"></path>
							</svg>
						</button>
					</div>
				</div>
				<h2 class="uppercase tracking-wide text-sm md:text-base text-center md:text-left">${currentMonth}</h2>
			</div>
			
			<!-- Desktop Week View -->
			<div class="hidden md:flex flex-col bg-white">
				<div class="sticky top-0 bg-white border-b-2 border-black z-10 grid grid-cols-[60px_repeat(7,1fr)]">
					<div class="border-r-2 border-black"></div>
					${dayHeadersHTML}
				</div>
				<div>
					<div class="grid grid-cols-[60px_repeat(7,1fr)]">
						<div class="border-r-2 border-black bg-gray-50">
							${timeColumnHTML}
						</div>
						${dayColumnsHTML}
					</div>
				</div>
			</div>
			
			<!-- Mobile Day View -->
			<div class="md:hidden flex flex-col bg-white">
				<div class="sticky top-0 bg-white border-b-2 border-black z-10 p-3">
					<div class="text-center">
						<h3 class="text-base font-medium uppercase tracking-wide">${weekStartDate.toLocaleDateString(
              "en-US",
              { weekday: "long", month: "long", day: "numeric" },
            )}</h3>
					</div>
				</div>
				<div class="overflow-y-auto">
					<div class="p-3">
						<div class="space-y-3">
							${
                events
                  .filter((event) => {
                    const eventDate = new Date(event.start);
                    return (
                      eventDate.toDateString() === weekStartDate.toDateString()
                    );
                  })
                  .map((event) => {
                    const eventStart = new Date(event.start);
                    const eventEnd = new Date(event.end);
                    const startTime = eventStart.toLocaleTimeString("en-US", {
                      hour: "numeric",
                      minute: "2-digit",
                      hour12: true,
                    });
                    const endTime = eventEnd.toLocaleTimeString("en-US", {
                      hour: "numeric",
                      minute: "2-digit",
                      hour12: true,
                    });

                    return `
									<div class="border-2 border-black bg-white p-3 shadow cursor-pointer hover:bg-gray-50 transition-colors" onclick="showEventPopup(${JSON.stringify(
                    event,
                  ).replace(/"/g, "&quot;")})">
										<div class="flex items-center justify-between">
											<h4 class="font-medium text-sm">${event.title}</h4>
											<span class="text-xs text-gray-600">${startTime} - ${endTime}</span>
										</div>
									</div>
								`;
                  })
                  .join("") ||
                '<div class="text-center text-gray-500 py-8">No events today</div>'
              }
						</div>
					</div>
				</div>
			</div>
		</div>
	`;
}

/**
 * Get the dates for the current week
 * @param {Date} date - The date to get the week for
 * @returns {Array<Date>} Array of dates for the week
 */
function getWeekDates(date) {
  const weekDates = [];
  const startOfWeek = new Date(date);
  const day = startOfWeek.getDay();
  const diff = startOfWeek.getDate() - day;
  startOfWeek.setDate(diff);

  for (let i = 0; i < 7; i++) {
    const weekDate = new Date(startOfWeek);
    weekDate.setDate(startOfWeek.getDate() + i);
    weekDates.push(weekDate);
  }

  return weekDates;
}

/**
 * Position events on the calendar based on their start time
 * @param {HTMLElement} container - The calendar container element
 * @param {Array<Object>} events - Array of event objects
 * @returns {void}
 */
function positionEvents(container, events) {
  // Handle desktop view only (mobile uses list view)
  const dayColumns = container.querySelectorAll('.day-column');

  let currentWeekDate = new Date();
  if (container.dataset.currentWeekDate) {
    currentWeekDate = new Date(container.dataset.currentWeekDate);
  }
  const weekDates = getWeekDates(currentWeekDate);

  // Clear existing events in desktop view
  dayColumns.forEach((column) => {
    const eventsList = column.querySelector('.events-container');
    if (eventsList) {
      eventsList.innerHTML = '';
    }
  });

  // Position each event
  let positionedCount = 0;
  events.forEach((event, index) => {
    if (!event.start) {
      return;
    }

    const eventStart = new Date(event.start);
    const dayIndex = weekDates.findIndex(
      (date) => date.toDateString() === eventStart.toDateString(),
    );

    if (dayIndex === -1) {
      return; // Event not in current week
    }

    const dayColumn = dayColumns[dayIndex];
    if (!dayColumn) {
      return;
    }

    const eventsList = dayColumn.querySelector(".events-container");
    if (!eventsList) {
      return;
    }

    const eventElement = createEventElement(event);
    const startHour = eventStart.getHours();
    const startMinute = eventStart.getMinutes();

    // Only show events between 10am and 8pm
    if (startHour < 10 || startHour > 20) {
      return; // Skip events outside the 10am-8pm range
    }

    // Desktop view: Position in day column
    const desktopDayColumn = dayColumns[dayIndex];
    if (desktopDayColumn) {
      const desktopEventsList =
        desktopDayColumn.querySelector('.events-container');
      if (desktopEventsList) {
        // Calculate position (10am-8pm range, 64px per hour)
        const topPosition = (startHour - 10) * 64 + (startMinute / 60) * 64;

        eventElement.style.top = `${topPosition}px`;
        eventElement.style.position = 'absolute';

        desktopEventsList.appendChild(eventElement);
        positionedCount++;
      }
    }
  });
}

/**
 * Create an event element with proper styling
 * @param {Object} event - Event data object
 * @returns {HTMLElement} The created event element
 */
function createEventElement(event) {
  const eventDiv = document.createElement('div');

  // Determine event title with fallback logic
  let eventTitle = 'No Title';
  if (event.title && event.title !== 'No Title') {
    eventTitle = event.title;
  } else if (event.summary && event.summary !== 'No Title') {
    eventTitle = event.summary;
  } else if (event.name) {
    eventTitle = event.name;
  } else if (event.subject) {
    eventTitle = event.subject;
  }

  const timeString = formatEventTime(
    new Date(event.start),
    event.end ? new Date(event.end) : null,
  );

  // Use ONLY Tailwind classes with dynamic background color
  const backgroundColor = event.backgroundColor || "#3b82f6";
  const bgClass =
    backgroundColor === "#3b82f6"
      ? "bg-blue-200"
      : backgroundColor === "#10b981"
      ? "bg-green-200"
      : backgroundColor === "#f59e0b"
      ? "bg-yellow-200"
      : backgroundColor === "#ef4444"
      ? "bg-red-200"
      : backgroundColor === "#8b5cf6"
      ? "bg-purple-200"
      : backgroundColor === "#ec4899"
      ? "bg-pink-200"
      : "bg-blue-200"; // fallback

  eventDiv.className = `calendar-event ${bgClass} text-black p-2 mb-1 cursor-pointer transition-all hover:translate-x-1 hover:-translate-y-0.5 border-2 border-black shadow`;

  eventDiv.innerHTML = `
		<div class="text-xs uppercase tracking-wide">${timeString}</div>
		<div class="mt-0.5">${eventTitle}</div>
	`;

  // Add click handler to show popup
  eventDiv.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    showEventPopup(event);
  });

  return eventDiv;
}

/**
 * Format event time for display
 * @param {Date} startTime - Event start time
 * @param {Date} [endTime] - Event end time (optional)
 * @returns {string} Formatted time string
 */
function formatEventTime(startTime, endTime = null) {
  const formatTime = (time) => {
    const hours = time.getHours();
    const minutes = time.getMinutes();
    const ampm = hours >= 12 ? "PM" : "AM";
    const displayHours = hours % 12 || 12;
    return `${displayHours}:${minutes.toString().padStart(2, "0")} ${ampm}`;
  };

  const startStr = formatTime(startTime);
  const endStr = endTime ? formatTime(endTime) : null;

  return endStr ? `${startStr} - ${endStr}` : startStr;
}

/**
 * Add navigation event listeners
 * @param {HTMLElement} container - The calendar container element
 * @param {Array<Object>} events - Array of event objects
 * @param {string} accentColor - Hex color code for accent color
 * @param {Object|null} eventDateRange - Date range of loaded events
 * @returns {void}
 */
function addNavigationListeners(
  container,
  events,
  accentColor,
  eventDateRange = null,
) {
  // Function to update navigation button states
  const updateNavigationButtons = () => {
    const currentWeekDate = new Date(
      container.dataset.currentWeekDate || new Date(),
    );
    const prevButton = container.querySelector(
      '.nav-button[data-action="prev"]',
    );
    const nextButton = container.querySelector(
      '.nav-button[data-action="next"]',
    );

    if (eventDateRange) {
      // Check if we can go to previous week
      const prevWeekDate = new Date(currentWeekDate);
      prevWeekDate.setDate(currentWeekDate.getDate() - 7);
      const canGoPrev = prevWeekDate >= eventDateRange.start;

      // Check if we can go to next week
      const nextWeekDate = new Date(currentWeekDate);
      nextWeekDate.setDate(currentWeekDate.getDate() + 7);
      const canGoNext = nextWeekDate <= eventDateRange.end;

      // Update button states
      if (prevButton) {
        prevButton.disabled = !canGoPrev;
        prevButton.style.opacity = canGoPrev ? "1" : "0.5";
        prevButton.style.cursor = canGoPrev ? "pointer" : "not-allowed";
      }

      if (nextButton) {
        nextButton.disabled = !canGoNext;
        nextButton.style.opacity = canGoNext ? "1" : "0.5";
        nextButton.style.cursor = canGoNext ? "pointer" : "not-allowed";
      }
    }
  };

  // Update button states initially
  updateNavigationButtons();

  // Today button
  const todayButton = container.querySelector(".today-button");
  if (todayButton) {
    todayButton.addEventListener("click", () => {
      const currentDate = new Date();
      container.dataset.currentWeekDate = currentDate.toISOString();
      container.innerHTML = createCalendarHTML(
        events,
        accentColor,
        currentDate,
      );
      positionEvents(container, events);
      addNavigationListeners(container, events, accentColor);
    });
  }

  // Previous button (day on mobile, week on desktop)
  const prevButton = container.querySelector('.nav-button[data-action="prev"]');
  if (prevButton) {
    prevButton.addEventListener("click", () => {
      const currentWeekDate = new Date(
        container.dataset.currentWeekDate || new Date(),
      );
      const newDate = new Date(currentWeekDate);

      // Check if we're on mobile (screen width < 768px)
      const isMobile = window.innerWidth < 768;

      if (isMobile) {
        // Mobile: navigate by day
        newDate.setDate(currentWeekDate.getDate() - 1);
      } else {
        // Desktop: navigate by week
        newDate.setDate(currentWeekDate.getDate() - 7);
      }

      // Check if the new date is within the loaded event range
      if (eventDateRange && newDate < eventDateRange.start) {
        return;
      }

      container.dataset.currentWeekDate = newDate.toISOString();
      container.innerHTML = createCalendarHTML(events, accentColor, newDate);
      positionEvents(container, events);
      addNavigationListeners(container, events, accentColor);
    });
  }

  // Next button (day on mobile, week on desktop)
  const nextButton = container.querySelector('.nav-button[data-action="next"]');
  if (nextButton) {
    nextButton.addEventListener("click", () => {
      const currentWeekDate = new Date(
        container.dataset.currentWeekDate || new Date(),
      );
      const newDate = new Date(currentWeekDate);

      // Check if we're on mobile (screen width < 768px)
      const isMobile = window.innerWidth < 768;

      if (isMobile) {
        // Mobile: navigate by day
        newDate.setDate(currentWeekDate.getDate() + 1);
      } else {
        // Desktop: navigate by week
        newDate.setDate(currentWeekDate.getDate() + 7);
      }

      // Check if the new date is within the loaded event range
      if (eventDateRange && newDate > eventDateRange.end) {
        return;
      }

      container.dataset.currentWeekDate = newDate.toISOString();
      container.innerHTML = createCalendarHTML(events, accentColor, newDate);
      positionEvents(container, events);
      addNavigationListeners(container, events, accentColor);
    });
  }
}

/**
 * Show event popup with description and details
 * @param {Object} event - Event data object
 * @returns {void}
 */
function showEventPopup(event) {
  // Remove existing popup if any
  const existingPopup = document.querySelector('.calendar-event-popup');
  if (existingPopup) {
    existingPopup.remove();
  }

  // Create popup overlay
  const overlay = document.createElement("div");
  overlay.className = "calendar-event-popup-overlay";
  overlay.addEventListener("click", function (e) {
    if (e.target === overlay) {
      closeEventPopup();
    }
  });

  // Create popup content
  const popup = document.createElement("div");
  popup.className = "calendar-event-popup";

  // Format date and time
  const startDate = new Date(event.start);
  const endDate = event.end ? new Date(event.end) : null;
  const dateStr = startDate.toLocaleDateString("en-US", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
  const timeStr = formatEventTime(startDate, endDate);

  // Build popup content
  let popupContent = `
		<div class="popup-header">
			<h3 class="popup-title">${event.title}</h3>
			<button class="popup-close">&times;</button>
		</div>
		<div class="popup-body">
			<div class="popup-date">${dateStr}</div>
			<div class="popup-time">${timeStr}</div>
	`;

  // Add location if available
  if (event.location) {
    popupContent += `<div class="popup-location">📍 ${event.location}</div>`;
  }

  // Add description if available
  if (event.description) {
    popupContent += `<div class="popup-description">${event.description}</div>`;
  } else {
    popupContent += `<div class="popup-description popup-no-description">No description available</div>`;
  }

  popupContent += `</div>`;

  popup.innerHTML = popupContent;

  // Add close button event listener
  const closeButton = popup.querySelector(".popup-close");
  closeButton.addEventListener("click", closeEventPopup);

  overlay.appendChild(popup);
  document.body.appendChild(overlay);

  // Add escape key handler
  document.addEventListener("keydown", handlePopupEscape);
}

/**
 * Close event popup
 * @returns {void}
 */
function closeEventPopup() {
  const popup = document.querySelector(".calendar-event-popup-overlay");
  if (popup) {
    popup.remove();
  }
  document.removeEventListener("keydown", handlePopupEscape);
}

/**
 * Handle escape key for popup
 * @param {KeyboardEvent} e - Keyboard event
 * @returns {void}
 */
function handlePopupEscape(e) {
  if (e.key === "Escape") {
    closeEventPopup();
  }
}

// Make functions globally accessible
window.closeEventPopup = closeEventPopup;
window.showEventPopup = showEventPopup;

/**
 * Initialize all calendar containers on the page
 * @returns {void}
 */
function initializeAllCalendars() {
  // Try multiple selectors to find calendar containers
  const selectors = [
    ".time-now-calendar-wrapper[data-calendar-id]",
    ".calendar-block-wrapper[data-calendar-id]",
    "[data-block-id]",
    ".wp-block-time-now-google-calendar",
    '[class*="calendar"]',
  ];

  let containers = [];
  for (const selector of selectors) {
    const found = document.querySelectorAll(selector);
    if (found.length > 0) {
      containers = Array.from(found);
      break;
    }
  }

  // Filter containers that have calendar data
  containers = containers.filter((container) => {
    return container.dataset.calendarId || container.dataset.events;
  });

  // Remove duplicates
  containers = [...new Set(containers)];

  // Initialize each container
  containers.forEach((container) => {
    initializeCustomCalendar(container);
  });
}

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeAllCalendars);
} else {
  initializeAllCalendars();
}

// Fallback initialization with a delay
setTimeout(initializeAllCalendars, 1000);

// Watch for dynamically added calendar blocks
document.addEventListener("DOMContentLoaded", function () {
  new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      mutation.addedNodes.forEach(function (node) {
        if (node.nodeType === 1) {
          // Element node
          // Check if the added node is a calendar container
          if (
            node.classList &&
            (node.classList.contains("time-now-calendar-wrapper") ||
             node.classList.contains("calendar-block-wrapper"))
          ) {
            initializeCustomCalendar(node);
          }
          // Check if the added node contains calendar containers
          const calendarContainers = node.querySelectorAll?.(
            ".time-now-calendar-wrapper[data-calendar-id], .calendar-block-wrapper[data-calendar-id]",
          );
          if (calendarContainers) {
            calendarContainers.forEach(initializeCustomCalendar);
          }
        }
      });
    });
  }).observe(document.body, {
    childList: true,
    subtree: true,
  });
});
