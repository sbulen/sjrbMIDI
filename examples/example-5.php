<?php
/**
 * sjrbMIDI example
 * Demonstrates generating chord progressions.  With a solo...
 * Simple AABC song structure.  Random rhythm.  Random-ish chords within key.
 */

spl_autoload_register(function ($class_name) {
		include '..\sources\class-' . $class_name . '.php';
	}
);

$out_name = 'example-5.mid';

$myFile = new MIDIFile();
$myFile->setBPM(97);
$chord_track = $myFile->addTrack('Chord Progression');
$bass_track = $myFile->addTrack('Bass');
$solo_track = $myFile->addTrack('Solo');

// Set key signature for use by note/chord processing
$key = new Key(Key::Eb_NOTE, Key::LOCRIAN_MODAL);
print_r($key);
echo '<br>';

// Sync with file's MIDI key signature
$myFile->setKeySignature($key->getMIDIsf(), $key->getMIDImm());

// Random rhythm...
$pulse = 16;
$notes = rand(1, 13);
$euclid = new Euclid($notes, $pulse - $notes);
print_r($euclid);
echo '<br>';

/**
 * Chord progression & SOLO
 */

// AABC song structure.
$chan = 0;
$vel = 120;
$bass_chan = 0;
$solo_chan = 0;

// Bass chord; Start everything at root, 5th octave
$bassnote = $key->getD(5, 0);
$chord = $key->buildChord($bassnote, Key::THIRD, Key::FIFTH);

foreach(array(1, 3, 5, 7) AS $meas)
{
	$start = 0;
	foreach ($euclid->getRhythm() AS $rlen)
	{
		// Chords
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $euclid->getPulse();
		$chord_track->addChord($time, $chan, $chord, $vel, $dur);
		$bass_track->addNote($time, $bass_chan, $key->d2m($bassnote), $vel, $dur);

			// Solo...
			$solo_note = $key->dAdd($bassnote, Key::OCTAVE);
			$beats = rand(0, $rlen);
			$subeuclid = new Euclid($beats, $rlen - $beats);
			$substart = $start;
			foreach ($subeuclid->getRhythm() AS $srlen)
			{
				$time = $myFile->mbt2at($meas, 1, $substart);
				$subdur = $srlen * $dur / $rlen;
				$solo_track->addNote($time, $solo_chan, $key->d2m($solo_note), $vel, $subdur);
				$solo_note = $key->dAdd($solo_note, rand(-4, 4));
				$substart += $subdur;
			}

		$start += $dur;
	}
}

// Bass chord tweaked a little
$tweaked = $key->dAdd($bassnote, rand(-3, 3));
$chord = $key->buildChord($tweaked, Key::THIRD, Key::FIFTH);

foreach(array(2, 4, 6, 8) AS $meas)
{
	$start = 0;
	foreach ($euclid->getRhythm() AS $rlen)
	{
		// Chords
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $euclid->getPulse();
		$chord_track->addChord($time, $chan, $chord, $vel, $dur);
		$bass_track->addNote($time, $bass_chan, $key->d2m($tweaked), $vel, $dur);

			// Solo...
			$beats = rand(1, $rlen);
			$subeuclid = new Euclid($beats, $rlen - $beats);
			$substart = $start;
			foreach ($subeuclid->getRhythm() AS $srlen)
			{
				$time = $myFile->mbt2at($meas, 1, $substart);
				$subdur = $srlen * $dur / $rlen;
				$solo_track->addNote($time, $solo_chan, $key->d2m($solo_note), $vel, $subdur);
				$solo_note = $key->dAdd($solo_note, rand(-4, 4));
				$substart += $subdur;
			}

		$start += $dur;
	}
}

// Modulate a good chunk...
$tweaked = $key->dAdd($bassnote, rand(-7, 7));
$chord = $key->buildChord($tweaked, Key::THIRD, Key::FIFTH, Key::SEVENTH);

foreach(array(9, 11) AS $meas)
{
	$start = 0;
	foreach ($euclid->getRhythm() AS $rlen)
	{
		// Chords
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $euclid->getPulse();
		$chord_track->addChord($time, $chan, $chord, $vel, $dur);
		$bass_track->addNote($time, $bass_chan, $key->d2m($tweaked), $vel, $dur);

			// Solo...
			$solo_note = $key->dAdd($tweaked, Key::OCTAVE);
			$beats = rand(0, $rlen);
			$subeuclid = new Euclid($beats, $rlen - $beats);
			$substart = $start;
			foreach ($subeuclid->getRhythm() AS $srlen)
			{
				$time = $myFile->mbt2at($meas, 1, $substart);
				$subdur = $srlen * $dur / $rlen;
				$solo_track->addNote($time, $solo_chan, $key->d2m($solo_note), $vel, $subdur);
				$solo_note = $key->dAdd($solo_note, rand(-4, 4));
				$substart += $subdur;
			}

		$start += $dur;
	}
}

// Tweak THAT a little...
$tweaked = $key->dAdd($tweaked, rand(-3, 3));
$chord = $key->buildChord($tweaked, Key::THIRD, Key::FIFTH, Key::SEVENTH);

foreach(array(10, 12) AS $meas)
{
	$start = 0;
	foreach ($euclid->getRhythm() AS $rlen)
	{
		// Chords
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $euclid->getPulse();
		$chord_track->addChord($time, $chan, $chord, $vel, $dur);
		$bass_track->addNote($time, $bass_chan, $key->d2m($tweaked), $vel, $dur);

			// Solo...
			$beats = rand(1, $rlen);
			$subeuclid = new Euclid($beats, $rlen - $beats);
			$substart = $start;
			foreach ($subeuclid->getRhythm() AS $srlen)
			{
				$time = $myFile->mbt2at($meas, 1, $substart);
				$subdur = $srlen * $dur / $rlen;
				$solo_track->addNote($time, $solo_chan, $key->d2m($solo_note), $vel, $subdur);
				$solo_note = $key->dAdd($solo_note, rand(-4, 4));
				$substart += $subdur;
			}

		$start += $dur;
	}
}

// Bring it on home... 1 of 4
$tweaked = $key->dAdd($bassnote, rand(-9, 9));
$chord = $key->buildChord($tweaked, Key::THIRD, Key::FIFTH, Key::SEVENTH);

foreach(array(13) AS $meas)
{
	$start = 0;
	foreach ($euclid->getRhythm() AS $rlen)
	{
		// Chords
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $euclid->getPulse();
		$chord_track->addChord($time, $chan, $chord, $vel, $dur);
		$bass_track->addNote($time, $bass_chan, $key->d2m($tweaked), $vel, $dur);

			// Solo...
			$solo_note = $key->dAdd($tweaked, Key::OCTAVE);
			$beats = rand(1, $rlen);
			$subeuclid = new Euclid($beats, $rlen - $beats);
			$substart = $start;
			foreach ($subeuclid->getRhythm() AS $srlen)
			{
				$time = $myFile->mbt2at($meas, 1, $substart);
				$subdur = $srlen * $dur / $rlen;
				$solo_track->addNote($time, $solo_chan, $key->d2m($solo_note), $vel, $subdur);
				$solo_note = $key->dAdd($solo_note, rand(-4, 4));
				$substart += $subdur;
			}

		$start += $dur;
	}
}

// Bring it on home... 2 of 4
$tweaked = $key->dAdd($bassnote, rand(-5, 5));
$chord = $key->buildChord($tweaked, Key::THIRD, Key::FIFTH, Key::SEVENTH);

foreach(array(14) AS $meas)
{
	$start = 0;
	foreach ($euclid->getRhythm() AS $rlen)
	{
		// Chords
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $euclid->getPulse();
		$chord_track->addChord($time, $chan, $chord, $vel, $dur);
		$bass_track->addNote($time, $bass_chan, $key->d2m($tweaked), $vel, $dur);

			// Solo...
			$beats = rand(1, $rlen);
			$subeuclid = new Euclid($beats, $rlen - $beats);
			$substart = $start;
			foreach ($subeuclid->getRhythm() AS $srlen)
			{
				$time = $myFile->mbt2at($meas, 1, $substart);
				$subdur = $srlen * $dur / $rlen;
				$solo_track->addNote($time, $solo_chan, $key->d2m($solo_note), $vel, $subdur);
				$solo_note = $key->dAdd($solo_note, rand(-4, 4));
				$substart += $subdur;
			}

		$start += $dur;
	}
}

// Bring it on home... 3 of 4
$tweaked = $key->dAdd($bassnote, rand(-3, 3));
$chord = $key->buildChord($tweaked, Key::THIRD, Key::FIFTH, Key::SEVENTH);

foreach(array(15) AS $meas)
{
	$start = 0;
	foreach ($euclid->getRhythm() AS $rlen)
	{
		// Chords
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $euclid->getPulse();
		$chord_track->addChord($time, $chan, $chord, $vel, $dur);
		$bass_track->addNote($time, $bass_chan, $key->d2m($tweaked), $vel, $dur);

			// Solo...
			$beats = rand(1, $rlen);
			$subeuclid = new Euclid($beats, $rlen - $beats);
			$substart = $start;
			foreach ($subeuclid->getRhythm() AS $srlen)
			{
				$time = $myFile->mbt2at($meas, 1, $substart);
				$subdur = $srlen * $dur / $rlen;
				$solo_track->addNote($time, $solo_chan, $key->d2m($solo_note), $vel, $subdur);
				$solo_note = $key->dAdd($solo_note, rand(-4, 4));
				$substart += $subdur;
			}

		$start += $dur;
	}
}

// Bring it on home... 4 of 4
$chord = $key->buildChord($bassnote, Key::THIRD, Key::FIFTH, Key::SEVENTH, Key::NINTH);

foreach(array(16) AS $meas)
{
	$start = 0;
	foreach ($euclid->getRhythm() AS $rlen)
	{
		// Chords
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $euclid->getPulse();
		$chord_track->addChord($time, $chan, $chord, $vel, $dur);
		$bass_track->addNote($time, $bass_chan, $key->d2m($bassnote), $vel, $dur);

			// Solo...
			$beats = rand(1, $rlen);
			$subeuclid = new Euclid($beats, $rlen - $beats);
			$substart = $start;
			foreach ($subeuclid->getRhythm() AS $srlen)
			{
				$time = $myFile->mbt2at($meas, 1, $substart);
				$subdur = $srlen * $dur / $rlen;
				$solo_track->addNote($time, $solo_chan, $key->d2m($solo_note), $vel, $subdur);
				$solo_note = $key->dAdd($solo_note, rand(-4, 4));
				$substart += $subdur;
			}

		$start += $dur;
	}
}

// Each track must have a TrackEnd
$chord_track->addTrackEnd($myFile->mbt2at(17,1,0));
$bass_track->addTrackEnd($myFile->mbt2at(17,1,0));
$solo_track->addTrackEnd($myFile->mbt2at(17,1,0));

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
	$start = 0;
	foreach ($euclid->getRhythm() AS $rlen)
	{
		$dur = $rlen * $myFile->b2dur(4) / $euclid->getPulse();

		// Kick...
		$beats = rand(0, 1);
		$subeuclid = new Euclid($beats, $rlen - $beats);
		$substart = $start;
		foreach ($subeuclid->getRhythm() AS $srlen)
		{
			$time = $myFile->mbt2at($meas, 1, $substart);
			$subdur = $srlen * $dur / $rlen;
			$drum_track->addNote($time, $chan, MIDIEvent::DRUM_AC_BASS, $vel, $subdur);
			$substart += $subdur;
		}

		// Snare...
		$beats = rand(0, 1);
		$subeuclid = new Euclid($beats, $rlen - $beats);
		$substart = $start;
		foreach ($subeuclid->getRhythm() AS $srlen)
		{
			$time = $myFile->mbt2at($meas, 1, $substart);
			$subdur = $srlen * $dur / $rlen;
			$drum_track->addNote($time, $chan, MIDIEvent::DRUM_AC_SNARE, $vel, $subdur);
			$substart += $subdur;
		}

		// Ride bell...
		$beats = rand(0, $rlen);
		$subeuclid = new Euclid($beats, $rlen - $beats);
		$substart = $start;
		foreach ($subeuclid->getRhythm() AS $srlen)
		{
			$time = $myFile->mbt2at($meas, 1, $substart);
			$subdur = $srlen * $dur / $rlen;
			$drum_track->addNote($time, $chan, MIDIEvent::DRUM_RIDE_BELL, $vel, $subdur);
			$substart += $subdur;
		}

		// Ride...
		$beats = rand(0, $rlen);
		$subeuclid = new Euclid($beats, $rlen - $beats);
		$substart = $start;
		foreach ($subeuclid->getRhythm() AS $srlen)
		{
			$time = $myFile->mbt2at($meas, 1, $substart);
			$subdur = $srlen * $dur / $rlen;
			$drum_track->addNote($time, $chan, MIDIEvent::DRUM_RIDE, $vel, $subdur);
			$substart += $subdur;
		}

		$start += $dur;
	}
}

// Last step for each track...
$drum_track->addTrackEnd($myFile->mbt2at(17,1,0));

// Write & dump the file
$myFile->writeMIDIFile($out_name);
$myFile->displayMIDIFile();

return;

?>