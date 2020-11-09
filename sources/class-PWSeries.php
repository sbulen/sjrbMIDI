<?php
/**
 *	Series of Pitch Wheel events
 *
 *	Copyright 2020 Shawn Bulen
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

class PWSeries extends EventSeries
{
	protected $channel;

	/**
	 * Constructor
	 *
	 * Builds object to establish a series of pitch wheel events.
	 *
	 * @param int $chan - The MIDI channel
	 * @param int $shape - The shape of the curve
	 * @param int $freq - The frequency, # of cycles per duration
	 * @param int $offset - The angle of offset, passed in degrees
	 * @param int $min_pct - Minimum value used in scaling, in percent
	 * @param int $max_pct - Maximum value used in scaling, in percent
	 * @param int $tick_inc - How far apart in ticks to spread the events
	 * @return void
	 */
	function __construct($chan = 0, $shape = EVENTSeries::SINE, $freq = 1, $offset = 0, $min_pct = 0, $max_pct = 100, $tick_inc = 48)
	{
		$this->channel = MIDIEvent::rangeCheck($chan, 0, 0xF);
		$this->type_min = EventSeries::PW_MIN;
		$this->type_max = EventSeries::PW_MAX;
		parent::__construct($shape, $freq, $offset, $min_pct, $max_pct, $tick_inc);
	}

	/**
	 * Generate & return the events
	 *
	 * @param int $start - The absolute start for the series
	 * @param int $dur - The duration of the series
	 * @return MIDIEvent::PitchWheel[]
	 */
	public function genEvents($start, $dur)
	{
		$values = $this->genValues($start, $dur);
		$events = array();
		foreach($values AS $start => $value)
			$events[] = new PitchWheel($start, $this->channel, $value);
		return $events;
	}
}

?>