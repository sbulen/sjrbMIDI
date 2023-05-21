<?php
/**
 *	Drum sequence - a set of parameters for generating some music, oriented toward drum tracks.
 *	In general, one channel, multiple instruments (e.g., kick, hihat, toms, cymbals), simple parameters...
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

class DrumSequence extends AbstractSequence
{
	/**
	 * Constructor
	 *
	 * Builds object to hold a set of parameters to generate some music.
	 *
	 * @param Rhythm $rhythm
	 * @param int $downbeat
	 * @param int $duration
	 * @param int[] $destinations
	 * @param float $note_pct
	 * @param float $trip_pct
	 * @return void
	 */
	function __construct(Rhythm $rhythm, int $downbeat = 1, int $dur = 1, array $dests = array(1), float $note_pct = 1, float $trip_pct = 0)
	{
		parent::__construct($rhythm, $downbeat, $dur, $dests, $note_pct, $trip_pct);
	}
}
?>