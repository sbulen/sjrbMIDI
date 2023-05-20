<?php
/**
 *	Abstract Generator - helps with reuse and consistency across 
 *	DrumGenerator and SongGenerator.
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

abstract class AbstractGenerator
{
	protected ?MIDIFile $midi_file = null;
	protected array $sequences = array();
	protected array $instruments = array();

	protected ?Dynamics $dynamics = null;
	protected ?int $key = null;

	// To be passed when Dynamics obj built
	protected int $maxvel = 120;
	protected int $minvel = 30;
	protected int $spread = 10;

	/**
	 * Constructor
	 *
	 * @param MidiFile $midi_file - MidiFile object
	 * @param array $seqs - array that defines sequences to be generated
	 * @param array $instruments - array that defines instruments to be used
	 * @return void
	 */
	function __construct(MIDIFile $midi_file, array $sequences = null, array $instruments = null)
	{
		if (is_a($midi_file, 'MIDIFile'))
			$this->midi_file = $midi_file;
		else
			Errors::fatal('inv_midifile');

		// Set the object's key sig based on the file
		$key_sig = $this->midi_file->getKeySignature();
		$this->key = new Key();
		$this->key->setKeyFromMIDI($key_sig['sharps'], $key_sig['minor']); 

		$this->sequences = array();
		if ($sequences !== null && is_array($sequences) && ($sequences == array_filter($sequences, function($a) {return is_a($a, 'AbstractSequence');})))
			$this->sequences = $sequences;
		else
			Errors::fatal('inv_seqs');

		$this->instruments = array();
		if ($instruments !== null && is_array($instruments) && ($instruments == array_filter($instruments, function($a) {return is_a($a, 'Instrument');})))
			foreach($instruments AS $inst)
			{
				// Load into array...  Track Name must be unique...
				if (key_exists($inst->getTrackName(), $this->instruments))
					Errors::fatal('unique_trknm');
				$this->instruments[$inst->getTrackName()] = $inst;
			}
		else
			Errors::fatal('inv_insts');

		// Add a MIDItrk object for each instrument...
		// Either add track to the midi file or use an existing one with the same name if found.
		foreach ($this->instruments AS $track_name => $inst)
		{
			$found = false;
			foreach($midi_file->getTracks() AS $track)
			{
				$event = $track->getEvent(MIDIEvent::META_TRACK_NAME);
				if (($event !== false) && ($event->getName() === $track_name))
				{
					$found = true;
					break;
				}
			}

			if ($found)
				$this->instruments[$track_name]->setTrack($track);
			else
				$this->instruments[$track_name]->setTrack($this->midi_file->addTrack($track_name));
		}
	}

	/**
	 * Execute the algorithm & generate the notes
	 *
	 * @return array
	 */
	private function doSequence(AbstractSequence $seq): array
	{
		$notes = array();

		// Dynamics setup... (params: rhythm, measure duration, start beat, maxvel, minvel, dropoff, time sig top, time sig bottom)
		$this->dynamics = new Dynamics($seq->getRhythm(), $this->midi_file->b2dur($this->midi_file->getTimeSignature()['top']), $seq->getDownbeat(), $this->maxvel, $this->minvel, $this->spread, $this->midi_file->getTimeSignature()['top'], $this->midi_file->getTimeSignature()['bottom']);

		// Do the instruments; allow for variable duration.
		// Always start from 0.  Easiest to work with.
		$new_notes = array();
		$seq->getRhythm()->setStartDur(0, $this->midi_file->b2dur($this->midi_file->getTimeSignature()['top'] * $seq->getDuration()));
		$this->doInstruments($seq, $new_notes);

		// Clone generated sequence to requested locations
		foreach ($seq->getDestinations() AS $meas)
		{
			foreach ($new_notes AS $track_name => $note_arr)
			{
				foreach ($note_arr AS $note)
				{
					$note = clone $note;
					$note->setAt($note->getAt() + (($meas - 1) * $this->midi_file->b2dur($this->midi_file->getTimeSignature()['top'])));
					$notes[$track_name][] = $note;
				}
			}
		}

		// OK, got all our notes for the sequence...  Add them to the tracks...
		foreach ($notes AS $track_name => $note_arr)
		{
			foreach ($note_arr AS $note)
			{
				$this->addNoteToTrack($note, $track_name);
			}
		}
	}

	// Loop thru whatever instruments you're asked to do...
	private function doInstruments(AbstractSequence $seq, array &$new_notes): void
	{
		// Step thru primary rhythm
		foreach ($seq->getRhythm()->walkAll AS $start => $info)
		{
			// Now do subrhythms for each inst/sub_inst
			foreach ($this->instruments AS $track_name => $inst)
			{
				foreach ($inst->getSubInsts() AS $tone => $sub_inst_vars)
				{
					if ($sub_inst_vars['max_hits'] == -1)
						$max = rand(0, $info['pulses']);
					else
						$max = $sub_inst_vars['max_hits'];

					// Sanity check...
					if ($max > $info['pulses'])
						$max = $info['pulses'];

					// Safety check...
					if ($sub_inst_vars['min_hits'] > $max)
						$sub_inst_vars['min_hits'] = $max;
						
					$beats = rand($sub_inst_vars['min_hits'], $max);

					$subeuclid = new Euclid($beats, $info['pulses'] - $beats);
					$subeuclid->setStartDur($start, $info['dur']);
					foreach ($subeuclid->walkAll AS $substart => $subinfo)
						$this->doInstrument($substart, $subinfo, $inst, $tone, $sub_inst_vars, $info, $seq, $new_notes);
				}
			}
		}
	}

	// Generate notes (consider a triplet a note...)
	protected function genNote(Note $note, float $vel_factor, float $npct, float $tpct, string $track_name, array &$new_notes): void
	{
		// Apply a triplet?
		if (MathFuncs::randomFloat() <= $tpct)
		{
		    $start = $note->getAt();
			$new_dur = (int) ($note->getDur() / 3);
			$note->setDur($new_dur);
			for ($i = 0; $i < 3; $i++)
			{
				if (MathFuncs::randomFloat() <= $npct)
				{
					$note = clone $note;
					$note->setAt($start + ($i * $new_dur));
					$note->setVel(round($this->dynamics->getVel($note->getAt()) * $vel_factor));
					$new_notes[$track_name][] = $note;
				}
			}
			return;
		}		

		// Apply note pct
		if (MathFuncs::randomFloat() >= $npct)
			return;

		// Apply dynamics
		$note->setVel(round($this->dynamics->getVel($note->getAt()) * $vel_factor));

		$new_notes[$track_name][] = $note;
	}

	// Add one note to a track...  Last step...  Split out to allow it to be overridden if needed...
	protected function addNoteToTrack(Note $note, string $track_name): void
	{
		// Convert notes into MIDI events & add to the appropriate track
		$mnote = $this->key->d2m($note->getDnote());
		$this->instruments[$track_name]->getTrack()->addNote($note->getAt(), $note->getChan(), $mnote, $note->getVel(), $note->getDur());
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
	abstract function doInstrument(int $start, array $subinfo, Instrument $inst, int $tone, array $sub_inst_vars, array $rhythm_vars, AbstractSequence $seq, array &$new_notes);

	/**
	 * Set the sequences
	 *
	 * @param Sequence[]
	 * @return void
	 */
	public function setSequences(array $sequences): void
	{
		$this->sequences = array();
		if ($sequences !== null && is_array($sequences) && ($sequences == array_filter($sequences, function($a) {return is_a($a, 'AbstractSequence');})))
			$this->sequences = $sequences;
		else
			Errors::fatal('inv_seqs');
	}

	/**
	 * Set the instruments
	 *
	 * @param array $instruments
	 * @return void
	 */
	public function setInstruments(array $instruments): void
	{
		$this->instruments = array();
		if ($instruments !== null && is_array($instruments) && ($instruments == array_filter($instruments, function($a) {return is_a($a, 'Instrument');})))
			foreach($instruments AS $inst)
			{
				// Load into array...  Channel must be unique...
				if (key_exists($inst->getTrackName(), $this->instruments))
					Errors::fatal('unique_trknm');
				$this->instruments[$inst->getTrackName()] = $inst;
			}
		else
			Errors::fatal('inv_insts');
	}

	/**
	 * Call all the sequences & generate everything...
	 *
	 * Notes had been constructed in a simple fashion, with one array entry per note.
	 * These must be split out into MIDI events to be added to the drum track here.
	 *
	 * @return MIDIEvents[]
	 */
	public function generate(): array
	{
		Errors::info('started');

		// Process all of the sequences, building a single combined $notes array.
		foreach($this->sequences AS $seq)
			$this->doSequence($seq);

		Errors::info('ended');
	}

	/**
	 * Set maxvel...
	 * May need to tweak minvel accordingly
	 *
	 * @param int $maxvel - max velocity
	 * @return void
	 */
	public function setMaxvel(int $maxvel = 0x7F): array
	{
		$this->maxvel = MIDIEvent::rangeCheck($maxvel, 0, 0x7F);
		$this->minvel = MIDIEvent::rangeCheck($this->minvel, 0, $this->maxvel);
	}

	/**
	 * Set minvel...
	 * May need to tweak maxvel accordingly
	 *
	 * @param int $minvel - min velocity
	 * @return void
	 */
	public function setMinvel(int $minvel = 0): void
	{
		$this->minvel = MIDIEvent::rangeCheck($minvel, 0, 0x7F);
		$this->maxvel = MIDIEvent::rangeCheck($this->maxvel, $this->minvel, 0x7F);
	}

	/**
	 * Set spread...
	 *
	 * @param int $spread - variance between note divisions
	 * @return void
	 */
	public function setSpread(int $spread = 10): void
	{
		$this->spread = MIDIEvent::rangeCheck($spread, 0, 0x7F);
	}

}
?>