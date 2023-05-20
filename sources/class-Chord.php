<?php
/**
 *	MIDI class for a musical Chord.
 *	Includes various transformations, e.g., inversion, retrograde & snowflake.
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

class Chord
{
	/*
	 * Properties
	 */
	protected $dnote;
	protected $intervals;

	/**
	 * Constructor
	 *
	 * Builds a Chord object
	 *
	 * @param dnote - root of chord
	 * @param int[] - array of intervals
	 * @return void
	 */
	function __construct($dnote, $intervals = array(2, 4))
	{
		// For ease of use, dnote may or may not have an sf indicator...
		if (is_int($dnote) || is_array($dnote))
			$this->dnote = Key::cleanseDNote($dnote);
		else
			Errors::fatal('inv_dnote');

		if (is_array($intervals) && ($intervals == array_filter($intervals, function($a) {return is_int($a);})))
			$this->intervals = $intervals;
		else
			Errors::fatal('inv_ints');
	}

	/**
	 * Get dnote of root
	 *
	 * @return array()
	 */
	public function getDNote()
	{
		return $this->dnote;
	}

	/**
	 * Get intervals
	 *
	 * @return int[]
	 */
	public function getIntervals()
	{
		return $this->intervals;
	}

	/**
	 * Transformation - Inversion...
	 * Really only supports none, 1st inversion & 2nd inversion
	 *
	 * @param int $inversion - 0 for none, 1 for 1st inversion, 2 for 2nd inversion
	 * @return void
	 */
	public function inversion($inversion)
	{
		// Sanity check...
		if (($inversion != 1) && ($inversion != 2))
			return;

		// Both 1st & 2nd boot the root up an octave; remember the 'dn' is in base 7...
		$temp = base_convert($this->dnote['dn'], 7, 10);
		$temp = $temp + 7;
		$this->dnote['dn'] = base_convert($temp, 10, 7);

		// Since the root's up an octave, gotta subtract an octave from all the intervals
		// The intervals are all in base 10...
		array_walk($this->intervals, function(&$a) {$a = $a - 7;});

		// A 2nd inversion means the 3rd (which is now a -5...) also goes up an octave (-5 + 7 = 2)
		if ($inversion == 2)
			array_walk($this->intervals, function(&$a) {$a = (($a == -5) ? 2 : $a);});

		// Get rid of any dupes or 0s...
		$this->intervals = array_filter($this->intervals, function($a) {return $a != 0;});
		$this->intervals = array_unique($this->intervals);
	}
}
?>