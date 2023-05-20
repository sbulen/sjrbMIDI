<?php
/**
 *	Class for an instrument - a set of parameters defining instrument behavior.
 *	This class focuses on the instrument/tone/channel relationship...
 *	Is the instrument defined by a fixed number of tones (like a drum kit)?  If so, multiple
 *	sub-instruments are defined, one per drum in the set.
 *	All other instruments will only use a single sub-instrument, and use all tones.
 *
 *	Allows for sharing & consistency of instrument processing across drums & songs.
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

class Instrument
{
	/**
	 * Properties
	 */
	protected $channel;			// MIDI Channel
	protected $track_name;		// Name for the track
	protected $sub_insts;		// Sub-insts = an array that allows for things like drums; melodic insts only have 1
								// Sub-insts have at least 4 parameters each: a tone, min hits per rhythmic beat, max hits per rhythmic beat, and a velocity factor for mixing
	protected $track;			// MIDI Track object

	/**
	 * Constructor
	 *
	 * Builds object to hold a set of parameters to define an instrument.
	 *
	 * @param int $channel
	 * @param string $track_name
	 * @param array() $sub_insts
	 * @return void
	 */
	function __construct($channel, $track_name = 'Track', $sub_insts = array(-1 => array(1, 1, 1)))
	{
		Errors::info('load_insts');

		if (is_int($channel) && ($channel >= 0) && ($channel <= 15))
			$this->channel = $channel;
		else
			Errors::fatal('inv_chan');

		if (is_string($track_name))
			$this->track_name = $track_name;
		else
			Errors::fatal('inv_track');

		// Make sure sub_insts make sense...
		if (is_array($sub_insts) && (count($sub_insts) > 0))
		{
			foreach ($sub_insts AS $tone => $vars)
			{
				if (!is_int($tone) || ($tone < -1) || ($tone > 127))
					Errors::fatal('inv_sitone');
				if (!is_int($vars[0]) || ($vars[0] < 0))
					Errors::fatal('inv_siminhits');
				// Note that -1 has special meaning: max available...
				if (!is_int($vars[1]) || ($vars[1] < -1))
					Errors::fatal('inv_simaxhits');
				if ((!is_float($vars[2]) && !is_int($vars[2])) || ($vars[2] < 0) || ($vars[2] > 1))
					Errors::fatal('inv_sivelfact');
				$this->sub_insts[$tone] = array('min_hits' => $vars[0], 'max_hits' => $vars[1], 'vel_factor' => $vars[2]);
			}
		}
		else
			Errors::fatal('inv_subinst');
	}

	/**
	 * Add Midi Track...
	 * Can only be done after the instrument has been associated with a MIDI file.
	 *
	 * @return void
	 */
	public function setTrack($track)
	{
		if (is_a($track, 'MIDITrk'))
		    $this->track = $track;
		else
			Errors::fatal('inv_assoc');
	}

	/**
	 * Get channel...
	 *
	 * @return int
	 */
	public function getChan()
	{
		return $this->channel;
	}

	/**
	 * Get pattern...
	 *
	 * @return string
	 */
	public function getTrackName()
	{
		return $this->track_name;
	}

	/**
	 * Get sub instrument definitions (tone, min hits, max hits, velocity factor...)
	 *
	 * @return array()
	 */
	public function getSubInsts()
	{
		return $this->sub_insts;
	}

	/**
	 * Get track...
	 *
	 * @return MIDITrk
	 */
	public function getTrack()
	{
	    return $this->track;
	}
	
}
?>