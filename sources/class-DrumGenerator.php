<?php
/**
 *	Drum Generator
 *
 *	Copyright 2021 Shawn Bulen
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

class DrumGenerator extends AbstractGenerator
{
	/**
	 * Constructor
	 *
	 * @param MidiFile $midi_file - MidiFile object
	 * @param array $seqs - array that defines sequences to be generated
	 * @param array $instruments - array that defines instruments to be used
	 * @return void
	 */
	function __construct($midi_file, $seqs = null, $instruments = null)
	{
		parent::__construct($midi_file, $seqs, $instruments);
	}

	/**
	 * Do Instrument - Gen the notes per instructions for one particular instrument, one particular sequence
	 *
	 * @param int start
	 * @param array sub euclid parameters
	 * @param Instrument inst
	 * @param int sub inst tone
	 * @param array sub inst parameters
	 * @param array primary rhythm parameters
	 * @param Sequence
	 * @param Note[]
	 * @return void
	 */
	function doInstrument($start, $subinfo, $inst, $tone, $sub_inst_vars, $rhythm_vars, $seq, &$new_notes)
	{
		// Drums use the sub_inst tones.
		// But the tones are in mnotes...  We have to convert them to dnotes here...
		$dnote = $this->key->m2d($tone);
		
		// Dummy out vel, it's set later
		$note = new Note($inst->getChan(), $start, $dnote, 0, $subinfo['dur']);

		// Use the common func here...
		$this->genNote($note, $sub_inst_vars['vel_factor'], $seq->getNotePct(), $seq->getTripPct(), $inst->getTrackName(), $new_notes);
	}

	// Add one note to a track...  Special version for drums, so we can close an open hi-hat...
	protected function addNoteToTrack($note, $track_name)
	{
		$mnote = $this->key->d2m($note->getDnote());
		$this->instruments[$track_name]->getTrack()->addEvent(new NoteOn($note->getAt(), $note->getChan(), $mnote, $note->getVel()));
		$this->instruments[$track_name]->getTrack()->addEvent(new NoteOff($note->getAt() + $note->getDur(), $note->getChan(), $mnote, 0x40));

		// If a high hat, attempt a proper open-close.
		// Of course this assumes they're using standard notes, & not everyone does, but worth a try...
		// (Subtract 1 tick so it's properly closed at the end of loops...)
		if ($mnote === MIDIEvent::DRUM_OPEN_HH)
			$this->instruments[$track_name]->getTrack()->addEvent(new NoteOff($note->getAt() + $note->getDur() - 1, $note->getChan(), MIDIEvent::DRUM_PEDAL_HH, $note->getVel()));
	}
}
?>