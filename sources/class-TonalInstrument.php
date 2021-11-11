<?php
/**
 *	Tonal instrument - a set of parameters for defining a tonal instrument.
 *	In general, tonal instruments have things like chords & phrases, drums don't...
 *	They have one voice per instrument definition.
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

class TonalInstrument extends AbstractInstrument
{
	/**
	 * Properties
	 */
	protected $max_notes_per_hand;	// Max notes per hand = Not many folks have 6 fingers...
	protected $spread_per_hand;		// Range each hand can cover; not many folks have 8" fingers; in diatonic tones, 7 = an octave

	/**
	 * Constructor
	 *
	 * Builds object to hold a set of parameters to generate some music.
	 *
	 * @return void
	 */
	function __construct($channel, $track_name = 'Track', $sub_insts = array(-1 => array(1, 1, 1)), $max_notes_per_hand = 5, $spread_per_hand = 7)
	{
		// For tonal instruments, only one instrument per definition
		if (count($sub_insts) > 1)
			Errors::fatal('inv_subinst');

		if (is_int($max_notes_per_hand) && ($max_notes_per_hand >= 0))
			$this->max_notes_per_hand = $max_notes_per_hand;
		else
			Errors::fatal('inv_mnph');

		if (is_int($spread_per_hand) && ($spread_per_hand >= 0))
			$this->spread_per_hand = $spread_per_hand;
		else
			Errors::fatal('inv_spread');

		parent::__construct($channel, $track_name, $sub_insts);
	}

	/*
	 * Get max_notes_per_hand...
	 *
	 * @return int
	 */

	public function getMaxNotesPerHand()
	{
		return $this->max_notes_per_hand;
	}

	/*
	 * Get spread_per_hand...
	 *
	 * @return int
	 */

	public function getSpreadPerHand()
	{
		return $this->spread_per_hand;
	}
}
?>