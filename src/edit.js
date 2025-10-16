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
		{ label: __( 'Month', 'time-now' ), value: 'dayGridMonth' },
		{ label: __( 'Week', 'time-now' ), value: 'timeGridWeek' },
		{ label: __( 'Day', 'time-now' ), value: 'timeGridDay' },
		{ label: __( 'Agenda', 'time-now' ), value: 'listWeek' },
	];

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Calendar Settings', 'time-now' ) }
					initialOpen={ true }
				>
					<TextControl
						label={ __(
							'Google Calendar ID or Share URL',
							'time-now'
						) }
						value={ calendarId }
						onChange={ ( value ) =>
							setAttributes( { calendarId: value } )
						}
						help={ __(
							'Paste the share URL from Google Calendar',
							'time-now'
						) }
						placeholder="your-email@gmail.com or https://calendar.google.com/calendar/..."
					/>

					<SelectControl
						label={ __( 'Default View', 'time-now' ) }
						value={ defaultView }
						options={ viewOptions }
						onChange={ ( value ) =>
							setAttributes( { defaultView: value } )
						}
					/>

					<ToggleControl
						label={ __( 'Show Weekends', 'time-now' ) }
						checked={ showWeekends }
						onChange={ ( value ) =>
							setAttributes( { showWeekends: value } )
						}
					/>

					<RangeControl
						label={ __(
							'Events Per Day (Month View)',
							'time-now'
						) }
						value={ eventLimit }
						onChange={ ( value ) =>
							setAttributes( { eventLimit: value } )
						}
						min={ 2 }
						max={ 5 }
						help={ __(
							'Maximum number of events to show per day in month view',
							'time-now'
						) }
					/>

				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ calendarId ? (
					<ServerSideRender
						block="time-now/google-calendar"
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
										'time-now'
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
										'time-now'
									) }
								</strong>
								<p style={ { margin: '8px 0 0 0' } }>
									{ response?.message ||
										__(
											'Unknown error',
											'time-now'
										) }
								</p>
							</div>
						) }
					/>
				) : (
					<Placeholder
						icon="calendar-alt"
						label={ __(
							'Time.now() Calendar Block',
							'time-now'
						) }
						instructions={ __(
							'Enter your Google Calendar ID in the block settings to display your calendar events.',
							'time-now'
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
									'Time.now() Calendar Block',
									'time-now'
								) }
							</p>
							<p style={ { margin: '0', fontSize: '14px' } }>
								{ __(
									'Please enter your Google Calendar ID in the block settings →',
									'time-now'
								) }
							</p>
						</div>
					</Placeholder>
				) }
			</div>
		</>
	);
}
