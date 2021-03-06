<?php
/**
 * sjrbMIDI example
 * Demonstrates generating chord progressions.  Melodies following each other.
 * Simple ABCDx4 song structure.  Random rhythm.  Random-ish chords within key.
 */

spl_autoload_register(function ($class_name) {
		include '..\sources\class-' . $class_name . '.php';
	}
);

$out_name = 'example-6.mid';

$myFile = new MIDIFile();
$myFile->setBPM(97);
$pad_track = $myFile->addTrack('Pad');
$bass_track = $myFile->addTrack('Bass');
$harm1_track = $myFile->addTrack('Harm 1');
$harm2_track = $myFile->addTrack('Harm 2');
$harm3_track = $myFile->addTrack('Harm 3');

// Set key signature for use by note/chord processing
$key = new Key(Key::G_NOTE, Key::MIXOLYDIAN_MODAL);
print_r($key);
echo '<br>';

// Sync with file's MIDI key signature
$myFile->setKeySignature($key->getMIDIsf(), $key->getMIDImm());

// Rhythm...
$rhythm = new Rhythm(1, 3, 1, 3, 2, 2, 2, 2);
print_r($rhythm);
echo '<br>';

/**
 * Chord progression & layered harmony-ish
 */

// AABC song structure.
$chan = 0;
$vel = 120;
$bass_chan = 0;
$harm1_chan = 1;
$harm2_chan = 2;
$harm3_chan = 3;

foreach(array(0, 1, 2, 3) AS $chunk)
{
	// Bass chord; Start everything at root, 5th octave
	if ($chunk == 0)
		$bassnote = $key->getD(5, 0);
	else
		$bassnote = $key->dAdd($bassnote, rand(-2, 2));;
	$chord = $key->buildChord($bassnote, Key::THIRD, Key::FIFTH, Key::SEVENTH);
	$harm1_note = $key->dAdd($bassnote, Key::OCTAVE);
	$harm2_note = null;
	$harm3_note = null;

	foreach(array($chunk*4 + 1, $chunk*4 + 2, $chunk*4 + 3, $chunk*4 + 4) AS $meas)
	{
		// Pad chords & bass
		$time = $myFile->mbt2at($meas, 1, 0);
		$dur = $myFile->b2dur(4);
		$pad_track->addChord($time, $chan, $chord, $vel, $myFile->b2dur(4));
		$bass_track->addNote($time, $bass_chan, $key->d2m($bassnote), $vel, $myFile->b2dur(4));

		// Solos & harmonies...
		// Hold solo notes in 4th measure...
		if (($meas % 4) == 0)
		{
			$time = $myFile->mbt2at($meas, 1, 0);
			$harm1_track->addNote($time, $harm1_chan, $key->d2m($harm1_note), $vel, $myFile->b2dur(4));
			$harm2_track->addNote($time, $harm2_chan, $key->d2m($harm2_note), $vel, $myFile->b2dur(4));
			$harm3_track->addNote($time, $harm3_chan, $key->d2m($harm3_note), $vel, $myFile->b2dur(4));
		}
		// Otherwise follow each other...
		else
		{
			$rhythm->setStartDur($myFile->mbt2at($meas), $myFile->b2dur(4));
			foreach ($rhythm->walkAll AS $start => $info)
			{
				$beats = rand(0, $info['pulses']);
				$subeuclid = new Euclid($beats, $info['pulses'] - $beats);
				$subeuclid->setStartDur($start, $info['dur']);
				foreach ($subeuclid->walkSD AS $substart => $subdur)
				{
					$harm1_track->addNote($substart, $harm1_chan, $key->d2m($harm1_note), $vel, $subdur);
					if ($harm2_note != null)
						$harm2_track->addNote($substart, $harm2_chan, $key->d2m($harm2_note), $vel, $subdur);
					if ($harm3_note != null)
						$harm3_track->addNote($substart, $harm3_chan, $key->d2m($harm3_note), $vel, $subdur);

					$harm3_note = $harm2_note;
					$harm2_note = $harm1_note;
					$harm1_note = $key->dAdd($harm1_note, rand(-2, 2));
				}
			}
		}

		$bassnote = $key->dAdd($bassnote, rand(-2, 2));
		$chord = $key->buildChord($bassnote, Key::THIRD, Key::FIFTH);

	}	// end measure
}	// end chunk

// Each track must have a TrackEnd
$pad_track->addTrackEnd($myFile->mbt2at(17,1,0));
$bass_track->addTrackEnd($myFile->mbt2at(17,1,0));
$harm1_track->addTrackEnd($myFile->mbt2at(17,1,0));
$harm2_track->addTrackEnd($myFile->mbt2at(17,1,0));
$harm3_track->addTrackEnd($myFile->mbt2at(17,1,0));

/**
 * DRUMS
 * Main rhythm - same as chords above - throughout
 * Individual drum notes based on a rhythm-within-the-rhythm...  A subrhythm...
 */

$drum_track = $myFile->addTrack('Drums');

$chan = 9;
$vel = 120;

for ($meas = 1; $meas <= 16; $meas++)
{
	$rhythm->setStartDur($myFile->mbt2at($meas), $myFile->b2dur(4));
	foreach ($rhythm->walkAll AS $start => $info)
	{
		// Kick...
		$beats = rand(0, 1);
		$subeuclid = new Euclid($beats, $info['pulses'] - $beats);
		$subeuclid->setStartDur($start, $info['dur']);
		foreach ($subeuclid->walkSD AS $substart => $subdur)
		{
			$drum_track->addNote($substart, $chan, MIDIEvent::DRUM_AC_BASS, $vel, $subdur);
		}

		// Snare...
		$beats = rand(0, 1);
		$subeuclid = new Euclid($beats, $info['pulses'] - $beats);
		$subeuclid->setStartDur($start, $info['dur']);
		foreach ($subeuclid->walkSD AS $substart => $subdur)
		{
			$drum_track->addNote($substart, $chan, MIDIEvent::DRUM_AC_SNARE, $vel, $subdur);
		}

		// Ride bell...
		$beats = rand(0, $info['pulses']);
		$subeuclid = new Euclid($beats, $info['pulses'] - $beats);
		$subeuclid->setStartDur($start, $info['dur']);
		foreach ($subeuclid->walkSD AS $substart => $subdur)
		{
			$drum_track->addNote($substart, $chan, MIDIEvent::DRUM_RIDE_BELL, $vel, $subdur);
		}

		// Ride...
		$beats = rand(0, $info['pulses']);
		$subeuclid = new Euclid($beats, $info['pulses'] - $beats);
		$subeuclid->setStartDur($start, $info['dur']);
		foreach ($subeuclid->walkSD AS $substart => $subdur)
		{
			$drum_track->addNote($substart, $chan, MIDIEvent::DRUM_RIDE, $vel, $subdur);
		}
	}
}

// Last step for each track...
$drum_track->addTrackEnd($myFile->mbt2at(17,1,0));

// Write & dump the file
$myFile->writeMIDIFile($out_name);
$myFile->displayMIDIFile();

return;

?>