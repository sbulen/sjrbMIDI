<?php
/**
 *	Information about a rhythm - an array of relative lengths of time
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

class Rhythm
{
	// Minimal information about a rhythm - bests, rests, & an array of relative lengths
	protected $beats = 4;
	protected $rests = 0;
	protected $pulses = 4;
	protected $rhythm = array();

	// Once start & dur have been provided for the rhythm, we can use iterators to obtain starts & durs of each beat
	protected $start = 0;
	protected $dur = 0;

	// The iterators.  The simple one just returns $start => $durs.  The other returns more info.
	public $walkSD = null;
	public $walkAll = null;

	/**
	 * Constructor
	 *
	 * Passed a series of #s, corresponding to the relative time durations.
	 * E.g., if passed "4, 4, 4, 4", that's basically 4/4 time with a pulse
	 * of 1/16 notes.  "2, 2, 2, 2", is also 4/4 time, but with a pulse of
	 * of 1/8 notes.
	 *
	 * @param int $lengths - A variable number of lengths
	 * @return void
	 */
	function __construct(...$lengths)
	{
		$this->beats = 0;
		$this->rests = 0;
		$this->pulses = 0;

		$this->rhythm = array();
		foreach($lengths as $length)
		{
			$this->rhythm[] = $length;
			$this->pulses += $length;
		}

		$this->beats = count($this->rhythm);
		$this->rests = $this->pulses - $this->beats;

		$this->start = 0;
		$this->dur = 0;
		$this->walkSD = new RhythmWalkSD($this->rhythm, $this->start, $this->dur);
		$this->walkAll = new RhythmWalkAll($this->rhythm, $this->start, $this->dur);

	}

	/**
	 * Return the number of beats.
	 *
	 * @return int
	 */
	public function getBeats()
	{
		return $this->beats;
	}

	/**
	 * Return the number of rests.
	 *
	 * @return int
	 */
	public function getRests()
	{
		return $this->rests;
	}

	/**
	 * Return the rhythm array.
	 *
	 * @return int[]
	 */
	public function getRhythm()
	{
		return $this->rhythm;
	}

	/**
	 * Return the number of pulses.
	 *
	 * @return int
	 */
	public function getPulses()
	{
		return $this->pulses;
	}

	/**
	 * Set start & duration.
	 * And recalc iterators.
	 *
	 * @param int $start
	 * @param int $dur
	 * @return void
	 */
	public function setStartDur($start, $dur)
	{
		$this->start = $start;
		$this->dur = $dur;

		// This will recreate the iterators
		$this->walkSD = new RhythmWalkSD($this->rhythm, $this->start, $this->dur);
		$this->walkAll = new RhythmWalkAll($this->rhythm, $this->start, $this->dur);
	}

}
?>