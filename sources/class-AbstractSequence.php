<?php
/**
 *	Abstract class for a sequence - a set of parameters for generating some music.
 *
 *	Allows for sharing & consistency of sequence processing across drums & songs.
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

abstract class AbstractSequence
{
	/**
	 * Properties
	 */
	protected $rhythm;			// Rhythm
	protected $downbeat;		// Rhythm generation: 1 for rock, 2 for jazz, r&b, etc...
	protected $duration;		// Duration - number of measures for this chunk
	protected $destinations;	// Destinations - where to place output
	protected $note_pct;		// What percentage of notes are kept
	protected $trip_pct;		// What percentage of notes are triplets

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
	protected function __construct($rhythm, $downbeat = 1, $duration = 1, $destinations = array(1), $note_pct = 1, $trip_pct = 0)
	{
		Errors::info('load_seqs');

		if (is_a($rhythm, 'Rhythm'))
			$this->rhythm = $rhythm;
		else
			Errors::fatal('inv_rhythm');

		if (is_int($downbeat) && $downbeat > 0)
			$this->downbeat = $downbeat;
		else
			Errors::fatal('inv_downbeat');

		if (is_int($duration) && $duration > 0)
			$this->duration = $duration;
		else
			Errors::fatal('inv_dur');

		// Make sure destinations are all ints
		if (is_array($destinations) && (count($destinations) > 0) && ($destinations == array_filter($destinations, function($a) {return is_int($a);})))
			$this->destinations = $destinations;
		else
			Errors::fatal('inv_dests');

		if (is_numeric($note_pct) && ($note_pct >= 0) && ($note_pct <= 1))
			$this->note_pct = $note_pct;
		else
			Errors::fatal('inv_notepct');

		if (is_numeric($trip_pct) && ($trip_pct >= 0) && ($trip_pct <= 1))
			$this->trip_pct = $trip_pct;
		else
			Errors::fatal('inv_trippct');
	}

	/**
	 * Get rhythm...
	 *
	 * @return Rhythm
	 */
	public function getRhythm()
	{
		return $this->rhythm;
	}

	/**
	 * Get downbeat...
	 *
	 * @return int
	 */
	public function getDownbeat()
	{
		return $this->downbeat;
	}

	/**
	 * Get duration...
	 *
	 * @return int
	 */
	public function getDuration()
	{
		return $this->duration;
	}

	/**
	 * Get destinations...
	 *
	 * @return int[]
	 */
	public function getDestinations()
	{
		return $this->destinations;
	}

	/**
	 * Get note_pct...
	 *
	 * @return float
	 */
	public function getNotePct()
	{
		return $this->note_pct;
	}

	/**
	 * Get trip_pct...
	 *
	 * @return float
	 */
	public function getTripPct()
	{
		return $this->trip_pct;
	}
}
?>