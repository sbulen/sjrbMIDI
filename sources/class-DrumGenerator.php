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

class DrumGenerator
{

	// You need to know the midi file for time signature, ticks, etc.
	protected $midi_file = null;
	protected $chan = 9;
	protected $dynamics = null;

	// Starting measure #
	protected $start_measure = 1;
	protected $curr_measure = 1;

	// Multiple sequences can be requested, defaults here.  Each request has the following params:
	// - Euclid = true/false; if false a standard rhythm
	// - Pattern = a 1-measure pattern; for Euclid, beats/rests; for Rhythm, array of lengths; random if null
	// - Start beat
	// - Pattern measures
	// - Pattern note pct, 0 - 1.0
	// - Pattern triplet pct, 0 - 1.0
	// - Fill measures
	// - Fill note pct, 0 - 1.0
	// - Fill triplet pct, 0 - 1.0
	protected $sequences = array(
		array(
			'euclid' => true,
			'pattern' => array(7, 9),
			'start_beat' => 1,
			'patt_meas' => 3,
			'patt_note_pct' => .8,
			'patt_trip_pct' => .1,
			'fill_meas' => 1,
			'fill_note_pct' => 1,
			'fill_trip_pct' => 0
		),
		array(
			'euclid' => false,
			'pattern' => array(4, 4, 4, 4),
			'start_beat' => 2,
			'patt_meas' => 3,
			'patt_note_pct' => 1,
			'patt_trip_pct' => .0,
			'fill_meas' => 1,
			'fill_note_pct' => 1,
			'fill_trip_pct' => .8
		),
		array(
			'euclid' => true,
			'pattern' => null,
			'start_beat' => 1,
			'patt_meas' => 3,
			'patt_note_pct' => .8,
			'patt_trip_pct' => .1,
			'fill_meas' => 1,
			'fill_note_pct' => 1,
			'fill_trip_pct' => 0
		),
		array(
			'euclid' => false,
			'pattern' => null,
			'start_beat' => 2,
			'patt_meas' => 3,
			'patt_note_pct' => 1,
			'patt_trip_pct' => .0,
			'fill_meas' => 1,
			'fill_note_pct' => 1,
			'fill_trip_pct' => .8
		),
	);

	// Multiple instruments can be used, defaults here:
	// - Instrument
	// - Min hits per rhythmic beat, always an int >= 0 
	// - Max hits per rhythmic beat, always an int >= -1; -1 means "use the # of pulses"
	// - Velocity factor, 0 - 1.0; scales back returned velocity this much, allowing you to blend drums better
	protected $instruments = array(
		MIDIEvent::DRUM_AC_BASS => array('min_hits' => 0, 'max_hits' => 1, 'vel_factor' => 1),
		MIDIEvent::DRUM_AC_SNARE => array('min_hits' => 0, 'max_hits' => 1, 'vel_factor' => 1),
		MIDIEvent::DRUM_LOW_MID_TOM => array('min_hits' => 0, 'max_hits' => -1, 'vel_factor' => .8),
		MIDIEvent::DRUM_CLOSED_HH => array('min_hits' => 0, 'max_hits' => -1, 'vel_factor' => .6),
		MIDIEvent::DRUM_RIDE => array('min_hits' => 0, 'max_hits' => -1, 'vel_factor' => .7),
	);

	/**
	 * Constructor
	 *
	 * @param MidiFile $midi_file - MidiFile object
	 * @param int $chan - channel for drum events created
	 * @param int $start_measure - where this pattern starts
	 * @param array $seqs - array that defines sequences to be generated
	 * @param array $instruments - array that defines instruments to be used
	 * @return void
	 */
	function __construct($midi_file, $chan = 9, $start_measure = 1, $seqs = null, $instruments = null)
	{
		if (is_a($midi_file, 'MIDIFile'))
			$this->midi_file = $midi_file;
		else
			die('Fatal error: Drum Generator must be passed the midifile!');

		$this->chan = MIDIEvent::rangeCheck($chan, 0x0, 0xF);

		$this->start_measure = MIDIEvent::rangeCheck($start_measure, 0, 0xFFFFFFF);

		if ($seqs !== null && is_array($seqs))
			$this->seqs = $seqs;

		if ($instruments !== null && is_array($instruments))
			$this->instruments = $instruments;
	}

	/**
	 * Execute the algorithm & generate the notes
	 *
	 * @return array
	 */
	private function doSequence($euclid, $pattern, $db, $pmeas, $pnpct, $ptpct, $fmeas, $fnpct, $ftpct)
	{
		$notes = array();

		// Get your rhythm sorted out...
		$default_pulses = $this->midi_file->getTimeSignature()['top'] * 4;
		if ($euclid)
		{
			if ($pattern == null)
			{
				$beats = rand(1, $default_pulses);
				$rests = $default_pulses - $beats;
			}
			else
			{
				$beats = $pattern[0];
				$rests = $pattern[1];
			}
			$rhythm = new Euclid($beats, $rests);
		}
		else
		{
			if ($pattern == null)
			{
				$beats = rand(1, $default_pulses);
				$rhythm_array = $this->randomRhythm($beats, $default_pulses);
			}
			else
				$rhythm_array = $pattern;

			$rhythm = new Rhythm(...$rhythm_array);
		}

		// dynamics setup... (params: rhythm, measure duration, start beat, maxvel, minvel, dropoff, time sig top, time sig bottom)
		$this->dynamics = new Dynamics($rhythm, $this->midi_file->b2dur($this->midi_file->getTimeSignature()['top']), $db, 120, 30, 10, $this->midi_file->getTimeSignature()['top'], $this->midi_file->getTimeSignature()['bottom']);

		// Do your pattern measures
		$rhythm->setStartDur($this->midi_file->mbt2at($this->curr_measure), $this->midi_file->b2dur($this->midi_file->getTimeSignature()['top']));
		if ($pmeas > 0)
			$this->genDrums($pmeas, $pnpct, $ptpct, $rhythm, $notes);

		// Do your fill measures
		$rhythm->setStartDur($this->midi_file->mbt2at($this->curr_measure), $this->midi_file->b2dur($this->midi_file->getTimeSignature()['top']));
		if ($fmeas > 0)
			$this->genDrums($fmeas, $fnpct, $ftpct, $rhythm, $notes);

		return $notes;

	}

	// Generate an array of lengths with $beats # of elements that add up to $pulses.
	// All lengths must be ints > 0.
	private function randomRhythm($beats, $pulses)
	{
		$lengths = array();

		if (($beats < 1) || ($pulses < 1) || ($beats > $pulses))
			return $lengths;

		// Need to repeat truing up the last entry, because it sometimes comes up with 0 or negative
		// values if too many rounding errors in individual lengths...
		// So keep trying until you get something that makes sense.
		while (!isset($lengths[$beats - 1]) || ($lengths[$beats - 1] < 1))
		{
			// Pick random set
			for ($i = 0; $i < $beats; $i++)
				$lengths[$i] = rand(1, $pulses);
		
			// Adjust all but last entry downward to match total, while keeping all entries > 0
			$factor = $pulses / array_sum($lengths);
			for ($i = 0; $i < $beats - 1; $i++)
			{
				$lengths[$i] = (int) ($lengths[$i] * $factor);
				if ($lengths[$i] == 0)
					$lengths[$i] = 1;
			}
		
			// True up last entry...
			$lengths[$beats - 1] = $pulses - array_sum($lengths) + $lengths[$beats - 1];
		}

		echo 'Random rhythm generated:<br>';
		print_r($lengths);
		echo '<br>';

		return $lengths;
	}

	// Generate drums & copy measures
	private function genDrums($num_meas, $npct, $tpct, $rhythm, &$notes)
	{
		// Step thru primary rhythm
		$new_notes = array();
		foreach ($rhythm->walkAll AS $start => $info)
		{
			// Now do subrhythms...
			foreach ($this->instruments AS $inst => $vars)
			{
				if ($vars['max_hits'] == -1)
					$max = rand(0, $info['pulses']);
				else
					$max = $vars['max_hits'];

				// Sanity check...
				if ($max > $info['pulses'])
					$max = $info['pulses'];

				// Safety check...
				if ($vars['min_hits'] > $max)
					$vars['min_hits'] = $max;
					
				$beats = rand($vars['min_hits'], $max);

				$subeuclid = new Euclid($beats, $info['pulses'] - $beats);
				$subeuclid->setStartDur($start, $info['dur']);
				foreach ($subeuclid->walkSD AS $substart => $subdur)
					$this->genNote(array('note' => $inst, 'start' => $substart, 'dur' => $subdur, 'vel' => 100), $vars['vel_factor'], $npct, $tpct, $new_notes);
			}
		}

		// Copy to all requested measures
		for ($meas = 0; $meas < $num_meas; $meas++)
		{
			foreach ($new_notes AS $note)
			{
				// Add one measure to each note start...
				$note['start'] = $note['start'] + $meas * $this->midi_file->b2dur($this->midi_file->getTimeSignature()['top']);
				$notes[] = $note;
			}
			$this->curr_measure++;
		}
	}

	// Generate drum notes (consider a triplet a note...)
	private function genNote($note, $vel_factor, $npct, $tpct, &$new_notes)
	{
		// Apply a triplet?
		if (MathFuncs::randomFloat() <= $tpct)
		{
			$new_dur = (int) ($note['dur'] / 3);
			$note['dur'] = $new_dur;
			for ($i = 0; $i < 3; $i++)
			{
				if (MathFuncs::randomFloat() <= $npct)
				{
					$note['vel'] = $this->dynamics->getVel($note['start']) * $vel_factor;
					$new_notes[] = $note;
				}
				$note['start'] = $note['start'] + $new_dur;
			}
			return;
		}		

		// Apply note pct
		if (MathFuncs::randomFloat() >= $npct)
			return;

		// Apply dynamics
		$note['vel'] = $this->dynamics->getVel($note['start']) * $vel_factor;

		$new_notes[] = $note;

		return;
	}

	/**
	 * Set the start measure
	 *
	 * @param int $start_measure
	 * @return void
	 */
	public function setStartMeasure($start_measure)
	{
		if (is_numeric($start_measure) && $start_measure >= 1)
		{
			$this->start_measure = (int) $start_measure;
			$this->curr_measure = (int) $start_measure;
		}
	}

	/**
	 * Set the sequences
	 *
	 * @param array $seqs
	 * @return void
	 */
	public function setSequences($seqs)
	{
		if (is_array($seqs))
			$this->sequences = $seqs;
	}

	/**
	 * Set the instruments
	 *
	 * @param array $instruments
	 * @return void
	 */
	public function setInstruments($instruments)
	{
		if (is_array($instruments))
			$this->instruments = $instruments;
	}

	/**
	 * Generate & return all the drum notes.
	 *
	 * Notes had been constructed in a simple fashion, with one array entry per note.
	 * These must be split out into MIDI events to be added to the drum track here.
	 *
	 * @return MIDIEvents[]
	 */
	public function getNotes()
	{
		// Process all of the sequences, building a single combined $notes array.
		$notes = array();
		foreach($this->sequences AS $seq)
		{
			$new_notes = $this->doSequence(
				$seq['euclid'],
				$seq['pattern'],
				$seq['start_beat'],
				$seq['patt_meas'],
				$seq['patt_note_pct'],
				$seq['patt_trip_pct'],
				$seq['fill_meas'],
				$seq['fill_note_pct'],
				$seq['fill_trip_pct']
			);
			$notes = array_merge($notes, $new_notes);
		}

		// Convert notes into MIDI events.
		$events = array();
		foreach ($notes AS $note)
		{
			$events[] = new NoteOn($note['start'], $this->chan, $note['note'], $note['vel']);
			$events[] = new NoteOff($note['start'] + $note['dur'], $this->chan, $note['note'], 0x40);
		}

		return $events;
	}

}
?>