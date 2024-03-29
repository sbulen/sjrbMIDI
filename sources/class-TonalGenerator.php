<?php
/**
 *	Tonal Generator
 *
 *	Copyright 2021-2023 Shawn Bulen
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

class TonalGenerator extends AbstractGenerator
{
	/**
	 * Constructor
	 *
	 * @param MidiFile $midi_file - MidiFile object
	 * @param array $seqs - array that defines sequences to be generated
	 * @param array $instruments - array that defines instruments to be used
	 * @param array $root_seq - array that defines roots of chords/phrases to be generated
	 * @return void
	 */
	function __construct(MIDIFile $midi_file, array $seqs = null, array $instruments = null)
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
	function doInstrument(int $start, array $subinfo, Instrument $inst, int $tone, array $sub_inst_vars, array $rhythm_vars, AbstractSequence $seq, array &$new_notes): void
	{
		// Chose one of the phrases & transform
		// Transpose is based on beat of primary rhythm...
		$beat = $rhythm_vars['beat'];
		$phrases = count($seq->getPhrases());
		$phrase = clone $seq->getPhrases()[rand(0, $phrases - 1)];
		$phrase->setStartDur($start, $subinfo['dur']);
		$phrase->transpose($seq->getIntervals()[$beat % count($seq->getIntervals())]);

		// Use the common func here...  Always use the instrument channel...
		foreach ($phrase AS $note_obj)
		{
			$note_obj->setChan($inst->getChan());
			$this->genNote($note_obj, $sub_inst_vars['vel_factor'], $seq->getNotePct(), $seq->getTripPct(), $inst->getTrackName(), $new_notes);
		}
	}
}
?>