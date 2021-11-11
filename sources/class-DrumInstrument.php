<?php
/**
 *	Drum instrument - a set of parameters for defining drums.
 *	Drum instruments don't do things like chords...
 *	However... they are a collection of tones with different characteristics on the same channel (e.g., kick, snare).
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

class DrumInstrument extends AbstractInstrument
{
	/**
	 * Constructor
	 *
	 * Builds object to hold a set of parameters to define an isntrument
	 *
	 * @return void
	 */
	function __construct($channel, $track_name = 'Track', $sub_insts = array(-1 => array(1, 1, 1)))
	{
		// No unique edits

		parent::__construct($channel, $track_name, $sub_insts);
	}
}
?>