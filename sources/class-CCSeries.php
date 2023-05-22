<?php
/**
 *	Series of CCs - Continuous Controller events
 *
 *	Copyright 2020-2023 Shawn Bulen
 *
 *	This file is part of the sjrbMIDI library.
 *
 *	sjrbMIDI is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *	
 *	sjrbMIDI is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with sjrbMIDI.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

class CCSeries extends EventSeries
{
	protected int $channel;
	protected int $controller;

	/**
	 * Constructor
	 *
	 * Builds object to establish a series of continuous controller events.
	 *
	 * @param int $chan - The MIDI channel
	 * @param int $controller - The MIDI controller #
	 * @param int $shape - The shape of the curve
	 * @param float $freq - The frequency, # of cycles per duration
	 * @param float $offset - The angle of offset, passed in degrees
	 * @param float $min_pct - Minimum value used in scaling, in percent
	 * @param float $max_pct - Maximum value used in scaling, in percent
	 * @param int $tick_inc - How far apart in ticks to spread the events
	 * @return void
	 */
	function __construct(int $chan = 0, int $controller = 11, int $shape = EVENTSeries::SINE, float $freq = 1, float $offset = 0, float $min_pct = 0, float $max_pct = 100, int $tick_inc = 48)
	{
		$this->channel = MIDIEvent::rangeCheck($chan, 0, 0xF);
		$this->controller = MIDIEvent::rangeCheck($controller);
		$this->type_min = EventSeries::CC_MIN;
		$this->type_max = EventSeries::CC_MAX;
		parent::__construct($shape, $freq, $offset, $min_pct, $max_pct, $tick_inc);
	}

	/**
	 * Generate & return the events
	 *
	 * @param int $start - The absolute start for the series
	 * @param int $dur - The duration of the series
	 * @return MIDIEvent::ControlChange[]
	 */
	public function genEvents(int $start, int $dur): array
	{
		$values = $this->genValues($start, $dur);
		$events = array();
		foreach($values AS $start => $value)
			$events[] = new ControlChange($start, $this->channel, $this->controller, $value);
		return $events;
	}
}

?>