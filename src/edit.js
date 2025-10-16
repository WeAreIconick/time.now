import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	ToggleControl,
	RangeControl,
	Spinner,
	Placeholder,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit( { attributes, setAttributes } ) {
	const { calendarId, defaultView, showWeekends, eventLimit } =
		attributes;

	const blockProps = useBlockProps();

	const viewOptions = [
		{ label: __( 'Month', 'calendar-block' ), value: 'dayGridMonth' },
		{ label: __( 'Week', 'calendar-block' ), value: 'timeGridWeek' },
		{ label: __( 'Day', 'calendar-block' ), value: 'timeGridDay' },
		{ label: __( 'Agenda', 'calendar-block' ), value: 'listWeek' },
	];

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Calendar Settings', 'calendar-block' ) }
					initialOpen={ true }
				>
					<TextControl
						label={ __(
							'Google Calendar ID or Share URL',
							'calendar-block'
						) }
						value={ calendarId }
						onChange={ ( value ) =>
							setAttributes( { calendarId: value } )
						}
						help={ __(
							'Paste the share URL from Google Calendar',
							'calendar-block'
						) }
						placeholder="your-email@gmail.com or https://calendar.google.com/calendar/..."
					/>

					<SelectControl
						label={ __( 'Default View', 'calendar-block' ) }
						value={ defaultView }
						options={ viewOptions }
						onChange={ ( value ) =>
							setAttributes( { defaultView: value } )
						}
					/>

					<ToggleControl
						label={ __( 'Show Weekends', 'calendar-block' ) }
						checked={ showWeekends }
						onChange={ ( value ) =>
							setAttributes( { showWeekends: value } )
						}
					/>

					<RangeControl
						label={ __(
							'Events Per Day (Month View)',
							'calendar-block'
						) }
						value={ eventLimit }
						onChange={ ( value ) =>
							setAttributes( { eventLimit: value } )
						}
						min={ 2 }
						max={ 5 }
						help={ __(
							'Maximum number of events to show per day in month view',
							'calendar-block'
						) }
					/>

				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ calendarId ? (
					<ServerSideRender
						block="calendar-block/google-calendar"
						attributes={ attributes }
						LoadingResponsePlaceholder={ () => (
							<div
								style={ {
									textAlign: 'center',
									padding: '40px',
									background: '#f9f9f9',
									borderRadius: '4px',
									border: '1px dashed #ddd',
								} }
							>
								<Spinner />
								<p style={ { margin: '16px 0 0 0' } }>
									{ __(
										'Loading calendar…',
										'calendar-block'
									) }
								</p>
							</div>
						) }
						ErrorResponsePlaceholder={ ( { response } ) => (
							<div
								style={ {
									padding: '20px',
									background: '#fef2f2',
									borderRadius: '4px',
									border: '1px solid #fca5a5',
									color: '#991b1b',
								} }
							>
								<strong>
									{ __(
										'Error loading calendar:',
										'calendar-block'
									) }
								</strong>
								<p style={ { margin: '8px 0 0 0' } }>
									{ response?.message ||
										__(
											'Unknown error',
											'calendar-block'
										) }
								</p>
							</div>
						) }
					/>
				) : (
					<Placeholder
						icon="calendar-alt"
						label={ __(
							'Google Calendar Block',
							'calendar-block'
						) }
						instructions={ __(
							'Enter your Google Calendar ID in the block settings to display your calendar events.',
							'calendar-block'
						) }
					>
						<div
							style={ {
								textAlign: 'center',
								padding: '20px',
								background: '#f0f0f1',
								borderRadius: '4px',
								margin: '16px 0',
							} }
						>
							<p
								style={ {
									margin: '0 0 8px 0',
									fontWeight: 'bold',
								} }
							>
								{ __(
									'Google Calendar Block',
									'calendar-block'
								) }
							</p>
							<p style={ { margin: '0', fontSize: '14px' } }>
								{ __(
									'Please enter your Google Calendar ID in the block settings →',
									'calendar-block'
								) }
							</p>
						</div>
					</Placeholder>
				) }
			</div>
		</>
	);
}
