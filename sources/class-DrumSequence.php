<?php
/**
 *	Drum sequence - a set of parameters for generating some music, oriented toward drum tracks.
 *	In general, one channel, multiple instruments (e.g., kick, hihat, toms, cymbals), simple parameters...
 *
 *	Copyright 2020-2021 Shawn Bulen
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

class DrumSequence extends AbstractSequence
{
	/**
	 * Properties
	 */
	protected $channel;		// Channel for drum track generated

	/**
	 * Constructor
	 *
	 * Builds object to hold a set of parameters to generate some music.
	 *
	 * @param bool $euclid
	 * @param int[] $pattern
	 * @param int $downbeat
	 * @param int $duration
	 * @param int[] $destinations
	 * @param float $note_pct
	 * @param float $trip_pct
	 * @param int $channel
	 * @return void
	 */
	function __construct($euclid = false, $pattern = array(4, 4, 4, 4), $downbeat = 1, $dur = 1, $dests = array(1), $note_pct = 1, $trip_pct = 0, $channel = 9)
	{
		$this->channel = MIDIEvent::rangeCheck($channel, 0, 0xF);

		parent::__construct($euclid, $pattern, $downbeat, $dur, $dests, $note_pct, $trip_pct);
	}

	/**
	 * Get channel...
	 *
	 * @return int	
	 */

	public function getChannel()
	{
		return $this->channel;
	}

}
?>