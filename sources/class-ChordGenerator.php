<?php
/**
 *	Chord Sequence Generator
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

class ChordGenerator extends AbstractGenerator
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
		// Chose one of the chords & transform
		// Transpose is based on beat of primary rhythm...
		$beat = $rhythm_vars['beat'];
		$chords = count($seq->getChords());
		$chord = clone $seq->getChords()[rand(0, $chords - 1)];

		// Get raw info from chord
		$dnote = $chord->getDnote();
		$ints = $chord->getIntervals();

		// Transpose, & convert to array of note objs
		$dnote = $seq->getKey()->dAdd($dnote, $seq->getIntervals()[$beat % count($seq->getIntervals())]);

		$note_arr = array();
		$note_arr[] = new Note($inst->getChan(), $start, $dnote, 100, $subinfo['dur']);
		foreach ($ints AS $int)
			$note_arr[] = new Note($inst->getChan(), $start, $seq->getKey()->dAdd($dnote, $int), 100, $subinfo['dur']);


		// split chord triplets here....
		$trip_dur = (int) ($subinfo['dur'] / 3);
		if (MathFuncs::randomFloat() <= $seq->getChordTripPct())
			foreach ($note_arr AS $note)
			{
				$note->setDur($trip_dur);
				for ($i = 0; $i < 3; $i++)
				{
					$trip_note = clone $note;
					$trip_note->setAt($start + ($trip_dur * $i));
					$this->genNote($trip_note, $sub_inst_vars['vel_factor'], $seq->getNotePct(), $seq->getTripPct(), $inst->getTrackName(), $new_notes);
				}
			}
		else
			foreach ($note_arr AS $note)
				$this->genNote($note, $sub_inst_vars['vel_factor'], $seq->getNotePct(), $seq->getTripPct(), $inst->getTrackName(), $new_notes);
	}
}
?>