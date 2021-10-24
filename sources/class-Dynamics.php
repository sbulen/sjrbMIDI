<?php
/**
 *	MIDI class for a Dynamics object.  This is intended to help automate setting
 *	velocities, emphasizing notes on time divisions.  1/2 notes get emphasized 
 *  heavily, 1/4 notes a little less so, 1/8 notes less so, etc.
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

class Dynamics
{
	/*
	 * Properties
	 */
	protected $rhythm;
	protected $downbeat;
	protected $dur;
	protected $maxvel;
	protected $minvel;
	protected $spread;
	protected $timesigtop;
	protected $timesigbot;

	// Used internally for quick vel lookups for this obj
	private $buckets;

	/**
	 * Constructor
	 *
	 * Builds a Dynamics object
	 *
	 * @param int $rhythm - Rhythm object
	 * @param int $dur - Measure duration in ticks
	 * @param int $downbeat - E.g., 1 for rock (1,3), 2 for jazz (2,4)... Beat where rhythm pattern starts...
	 * @param int $maxvel - Max velocity to be returned
	 * @param int $minvel - Min velocity to be returned
	 * @param int $spread - Dropoff per note division; e.g., how much heavier is a 1/4 note than a 1/8 note
	 * @param int $timesigtop - Top of time signature, e.g., 4 for 4/4, or 6 for 6/8
	 * @param int $timesigbot - Bottom of time signature
	 * @return void
	 */
	function __construct($rhythm, $dur = 3840, $downbeat = 1, $maxvel = 120, $minvel = 60, $spread = 20, $timesigtop = 4, $timesigbot = 4)
	{
		if (is_a($rhythm, 'Rhythm'))
			$this->rhythm = $rhythm;
		else
			die('Rhythm must be passed to dynamics object');

		$this->downbeat = MIDIEvent::rangeCheck($downbeat, 1, 0xF);
		$this->dur = MIDIEvent::rangeCheck($dur, 1, 0xFFFFFFF);
		$this->maxvel = MIDIEvent::rangeCheck($maxvel, 0x0, 0x7F);
		$this->minvel = MIDIEvent::rangeCheck($minvel, 0x0, $this->maxvel);
		$this->spread = MIDIEvent::rangeCheck($spread, 0x0, 0x7F);
		$this->timesigtop = MIDIEvent::rangeCheck($timesigtop, 1, 0x7F);
		$this->timesigbot = MIDIEvent::rangeCheck($timesigbot, 1, 0x7F);

		// Initialize for this instance
		$this->buckets = array();
	}

	/**
	 * Given a time, calculate velocity...
	 * Kind of a brute force method...
	 *
	 * @param int $at - time to be calc'd for
	 * @return int
	 */
	public function getVel($at)
	{
		static $exp = 6;			// 1/(2^6) = 1/64
		static $bpb = 16;			// buckets per beat

		if (empty($this->buckets))
		{
			$this->buckets = array();

			// Special treatment for 4/4
			if ($this->is4_4())
			{
				$num_buckets = 2**$exp;
				for ($i = 0; $i < $num_buckets; $i++)
				{
					if ($i % (2**($exp - 1)) == 0) $this->buckets[$i] = $this->maxvel - ($this->spread * 1);		// 1/2 notes
					elseif ($i % (2**($exp - 2)) == 0) $this->buckets[$i] = $this->maxvel - ($this->spread * 2);	// 1/4 notes
					elseif ($i % (2**($exp - 3)) == 0) $this->buckets[$i] = $this->maxvel - ($this->spread * 3);	// 1/8 notes
					elseif ($i % (2**($exp - 4)) == 0) $this->buckets[$i] = $this->maxvel - ($this->spread * 4);	// 1/16 notes
					elseif ($i % (2**($exp - 5)) == 0) $this->buckets[$i] = $this->maxvel - ($this->spread * 5);	// 1/32 notes
					else $this->buckets[$i] = $this->maxvel - ($this->spread * 6);								// 1/64 notes
				}
			}
			else
			{
				$num_buckets = $this->timesigtop * $bpb;
				for ($i = 0; $i < $num_buckets; $i++)
				{
					if ($i % $bpb == 0) $this->buckets[$i] = $this->maxvel - ($this->spread * 2);				// beats
					elseif ($i % ($bpb/2) == 0) $this->buckets[$i] = $this->maxvel - ($this->spread * 3);		// 1/2 beats
					elseif ($i % ($bpb/4) == 0) $this->buckets[$i] = $this->maxvel - ($this->spread * 4);		// 1/4 beats
					elseif ($i % ($bpb/8) == 0) $this->buckets[$i] = $this->maxvel - ($this->spread * 5);		// 1/8 beats
					elseif ($i % ($bpb/16) == 0) $this->buckets[$i] = $this->maxvel - ($this->spread * 6);	// 1/16 beats
					else $this->buckets[$i] = $this->minvel;													// catchall...
				}

				// Emphasize what's in the rhythm...
				$emphasis = 0;
				foreach ($this->rhythm->getRhythm() AS $pulses)
				{
					if ($emphasis < $num_buckets)
						$this->buckets[$emphasis] = $this->maxvel - ($this->spread * 1);
					$emphasis += (int) ($pulses * $bpb * $this->timesigtop / $this->rhythm->getPulses());
				}
			}

			// Whole notes always the strongest, bucket 0...
			$this->buckets[0] = $this->maxvel;
		}

		// Basic range checks
		if ($at < 0)
			return $this->minvel;

		// Allow for any measure...
		$at = $at % $this->dur;

		// downbeat should be <= timesigtop...
		$db = $this->downbeat;
		if ($db > $this->timesigtop)
		{
			$db = $db % $this->timesigtop;
			if ($db == 0)
				$db = $this->timesigtop;
		}

		// Divide measure into buckets (*centered* by adding 1/2 a bucket width); which bucket is this time in?
		$fraction = ($at + ($this->dur / count($this->buckets) / 2)) / $this->dur;
		$bucket = (int) floor($fraction * count($this->buckets));

		// Offset bucket if start beat != 1
		if ($db > 1)
			$bucket += (int) (($this->timesigtop - $db + 1) * $bpb);

		// Normalize to 1st measure
		$bucket = $bucket % count($this->buckets);
		$vel = $this->buckets[$bucket];

		// One last sanity check
		$vel = MIDIEvent::rangeCheck($vel, $this->minvel, $this->maxvel);

		return $vel;
	}

	/**
	 * Is 4/4 - determine if the rhythm provided is aligned with a 4/4 signature.
	 * Simple check: Need 4 groups, with equal pulses each.
	 *
	 * @return void
	 */
	private function is4_4()
	{
		if ((count($this->rhythm->getRhythm()) != 4) || ($this->timesigtop != 4) || ($this->timesigbot !=4))
			return false;

		$pulses = $this->rhythm->getRhythm()[0];
		if (($this->rhythm->getRhythm()[1] == $pulses) && ($this->rhythm->getRhythm()[2] == $pulses) && ($this->rhythm->getRhythm()[3] == $pulses))
			return true;
		else
			return false;
	}

	/**
	 * Set rhythm
	 *
	 * @param Rhythm $rhythm
	 * @return void
	 */
	public function setRhythm($rhythm = null)
	{
		if (is_a($rhythm, 'Rhythm'))
			$this->rhythm = $rhythm;
		else
			$this->rhythm = new Rhythm();

		return;
	}

	/**
	 * Set downbeat...
	 *
	 * @param int $downbeat
	 * @return void
	 */
	public function setDownbeat($downbeat = 1)
	{
		$this->downbeat = MIDIEvent::rangeCheck($downbeat, 1, 0xF);
		return;
	}

	/**
	 * Set duration...
	 *
	 * @param int $dur - duration in ticks
	 * @return void
	 */
	public function setDur($dur = 960)
	{
		$this->dur = MIDIEvent::rangeCheck($dur, 1, 0xFFFFFFF);
		return;
	}

	/**
	 * Set maxvel...
	 * May need to tweak minvel accordingly
	 *
	 * @param int $maxvel - max velocity
	 * @return void
	 */
	public function setMaxvel($maxvel = 0x7F)
	{
		$this->maxvel = MIDIEvent::rangeCheck($maxvel, 0, 0x7F);
		$this->minvel = MIDIEvent::rangeCheck($this->minvel, 0, $this->maxvel);
		return;
	}

	/**
	 * Set minvel...
	 * May need to tweak maxvel accordingly
	 *
	 * @param int $minvel - min velocity
	 * @return void
	 */
	public function setMinvel($minvel = 0)
	{
		$this->minvel = MIDIEvent::rangeCheck($minvel, 0, 0x7F);
		$this->maxvel = MIDIEvent::rangeCheck($this->maxvel, $this->minvel, 0x7F);
		return;
	}

	/**
	 * Set spread...
	 *
	 * @param int $spread - variance between note divisions
	 * @return void
	 */
	public function setSpread($spread = 10)
	{
		$this->spread = MIDIEvent::rangeCheck($spread, 0, 0x7F);
		return;
	}

	/**
	 * Set timesigtop...
	 *
	 * @param int $timesigtop - top of time signature
	 * @return void
	 */
	public function setTimeSigTop($timesigtop = 4)
	{
		$this->timesigtop = MIDIEvent::rangeCheck($timesigtop, 1, 0x7F);
		return;
	}

	/**
	 * Set timesigbot...  Must be a power of 2...  Thems the rules...
	 *
	 * @param int $timesigbot - bottom of time signature
	 * @return void
	 */
	public function setTimeSigBot($timesigbot = 4)
	{
		if (MathFuncs::isPow2($timesigbot))
			$this->timesigbot = MIDIEvent::rangeCheck($timesigbot, 1, 0x7F);

		return;
	}

}
?>