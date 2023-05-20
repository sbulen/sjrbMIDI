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
	protected int $chan;
	protected int $abs_time;
	protected array $dNote;
	protected int $vel;
	protected int $dur;

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
	function __construct(int $chan, int $abs_time, array $dNote, int $vel, int $dur)
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
	public function setChan(int $chan = 0): void
	{
		$this->chan = MIDIEvent::rangeCheck($chan, 0x0, 0xF);
	}

	/**
	 * Get channel...
	 *
	 * @return int
	 */
	public function getChan(): int
	{
		return $this->chan;
	}

	/**
	 * Set abs_time
	 *
	 * @param int $abs_time - absolute time of note
	 * @return void
	 */
	public function setAt(int $abs_time): void
	{
		$this->abs_time = MIDIEvent::rangeCheck($abs_time, 0, 0xFFFFFFF);
	}

	/**
	 * Return abs_time
	 *
	 * @return int
	 */
	public function getAt(): int
	{
		return $this->abs_time;
	}

	/**
	 * Set dNote...
	 *
	 * @param mixed $dNote - dNote
	 * @return void
	 */
	public function setDNote(array $dNote): void
	{
		$this->dNote = Key::cleanseDNote($dNote);
	}

	/**
	 * Get dNote...
	 *
	 * @return array
	 */
	public function getDNote(): array
	{
		return $this->dNote;
	}

	/**
	 * Set velocity...
	 *
	 * @param int $vel - MIDI velocity
	 * @return void
	 */
	public function setVel(int $vel = 100): void
	{
		$this->vel = MIDIEvent::rangeCheck($vel);
	}

	/**
	 * Get velocity...
	 *
	 * @return int
	 */
	public function getVel(): int
	{
		return $this->vel;
	}

	/**
	 * Set duration...
	 *
	 * @param int $dur - duration in ticks
	 * @return void
	 */
	public function setDur($dur = 960): void
	{
		$this->dur = MIDIEvent::rangeCheck($dur, 0, 0xFFFFFFF);
	}

	/**
	 * Get duration...
	 *
	 * @return int
	 */
	public function getDur(): int
	{
		return $this->dur;
	}

}
?>