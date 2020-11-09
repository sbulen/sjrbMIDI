<?php
/**
 * sjrbMIDI example
 * Random drum track.  And I mean random.
 */

spl_autoload_register(function ($class_name) {
		include '..\sources\class-' . $class_name . '.php';
	}
);

$out_name = 'example-2.mid';

$myFile = new MIDIFile();
$myFile->setBPM(97);
$new_track = $myFile->addTrack('Drums');

$chan = 9;
$vel = 120;

for ($meas = 1; $meas <= 16; $meas++)
{
	// Kick...
	$pulse = 4;
	$beats = rand(1, $pulse);
	$euclid = new Euclid($beats, $pulse - $beats);
	$rhythm = $euclid->getRhythm();
	print_r($euclid);
	echo '<br>';

	$start = 0;
	foreach ($rhythm AS $rlen)
	{
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $pulse;
		$new_track->addNote($time, $chan, MIDIEvent::DRUM_AC_BASS, $vel, $dur);
		$start += $dur;
	}

	// Snare...
	$pulse = 2;
	$beats = rand(1, $pulse);
	$euclid = new Euclid($beats, $pulse - $beats);
	$rhythm = $euclid->getRhythm();
	print_r($euclid);
	echo '<br>';

	$start = 0;
	foreach ($rhythm AS $rlen)
	{
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $pulse;
		$new_track->addNote($time, $chan, MIDIEvent::DRUM_AC_SNARE, $vel, $dur);
		$start += $dur;
	}

	// Ride bell...
	$pulse = 4;
	$beats = rand(1, $pulse);
	$euclid = new Euclid($beats, $pulse - $beats);
	$rhythm = $euclid->getRhythm();
	print_r($euclid);
	echo '<br>';

	$start = 0;
	foreach ($rhythm AS $rlen)
	{
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $pulse;
		$new_track->addNote($time, $chan, MIDIEvent::DRUM_RIDE_BELL, $vel, $dur);
		$start += $dur;
	}

	// Ride...
	$pulse = 16;
	$beats = rand(1, $pulse);
	$euclid = new Euclid($beats, $pulse - $beats);
	$rhythm = $euclid->getRhythm();
	print_r($euclid);
	echo '<br>';

	$start = 0;
	foreach ($rhythm AS $rlen)
	{
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $pulse;
		$new_track->addNote($time, $chan, MIDIEvent::DRUM_RIDE, $vel, $dur);
		$start += $dur;
	}
}

// Last step for each track...
$new_track->addTrackEnd($myFile->mbt2at(17,1,0));

// Write it & dump it...
$myFile->writeMIDIFile($out_name);
$myFile->displayMIDIFile();

?>