<?php
/**
 *	MIDI class for a Note object.  This Note represents a fully-attributed
 *	musical note, including the channel, velocity, duration, etc.
 *
 *	*** Note the concept deliberately omitted here is the concept of a key...
 *	For purposes of d2m conversions, the key must be provided from 
 *	context, either from the associated Phrase object or MIDIFile object. ***
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

class Note
{
	/*
	 * Properties
	 */
	protected $chan;
	protected $abs_time;
	protected $dNote;
	protected $vel;
	protected $dur;

	/**
	 * Constructor
	 *
	 * Builds a Note object
	 *
	 * @param int $chan - MIDI channel
	 * @param mixed $dNote - diatonic note following sjrbMIDI (base7) convention
	 * @param int $velocity - MIDI note velocity
	 * @param int $dur - note duration in ticks
	 * @return void
	 */
	function __construct($chan, $abs_time, $dNote, $vel, $dur)
	{
		$this->chan = MIDIEvent::rangeCheck($chan, 0x0, 0xF);
		$this->abs_time = MIDIEvent::rangeCheck($abs_time, 0, 0xFFFFFFF);
		$this->dNote = Key::cleanseDNote($dNote);
		$this->vel = MIDIEvent::rangeCheck($vel);
		$this->dur = MIDIEvent::rangeCheck($dur, 0, 0xFFFFFFF);
	}

	/**
	 * Set channel...
	 *
	 * @param int $chan - MIDI channel
	 * @return void
	 */
	public function setChan($chan = 0)
	{
		$this->chan = MIDIEvent::rangeCheck($chan, 0x0, 0xF);
		return;
	}

	/**
	 * Get channel...
	 *
	 * @return int
	 */
	public function getChan()
	{
		return $this->chan;
	}

	/**
	 * Set abs_time
	 *
	 * @param int $abs_time - absolute time of note
	 * @return void
	 */
	public function setAt($abs_time)
	{
		$this->abs_time = MIDIEvent::rangeCheck($abs_time, 0, 0xFFFFFFF);
		return;
	}

	/**
	 * Return abs_time
	 *
	 * @return int
	 */
	public function getAt()
	{
		return $this->abs_time;
	}

	/**
	 * Set dNote...
	 *
	 * @param mixed $dNote - dNote
	 * @return void
	 */
	public function setDNote($dNote)
	{
		$this->dNote = Key::cleanseDNote($dNote);
		return;
	}

	/**
	 * Get dNote...
	 *
	 * @return array
	 */
	public function getDNote()
	{
		return $this->dNote;
	}

	/**
	 * Set velocity...
	 *
	 * @param int $vel - MIDI velocity
	 * @return void
	 */
	public function setVel($vel = 100)
	{
		$this->vel = MIDIEvent::rangeCheck($vel);
		return;
	}

	/**
	 * Get velocity...
	 *
	 * @return int
	 */
	public function getVel()
	{
		return $this->vel;
	}

	/**
	 * Set duration...
	 *
	 * @param int $dur - duration in ticks
	 * @return void
	 */
	public function setDur($dur = 960)
	{
		$this->dur = MIDIEvent::rangeCheck($dur, 0, 0xFFFFFFF);
		return;
	}

	/**
	 * Get duration...
	 *
	 * @return int
	 */
	public function getDur()
	{
		return $this->dur;
	}

}
?>