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
Errors::setVerbosity(true);

$myFile = new MIDIFile();
$myFile->setBPM(97);
$new_track = $myFile->addTrack('Drums');

// Note setup...
$chan = 9;
$vel = 120;

for ($meas = 1; $meas <= 16; $meas++)
{
	// Kick...
	$pulses = 4;
	$beats = rand(1, $pulses);
	$euclid = new Euclid($beats, $pulses - $beats);

	$euclid->setStartDur($myFile->mbt2at($meas), $myFile->b2dur(4));
	foreach ($euclid->walkSD AS $start => $dur)
		$new_track->addNote($start, $chan, MIDIEvent::DRUM_AC_BASS, $vel, $dur);

	// Snare...
	$pulses = 2;
	$beats = rand(1, $pulses);
	$euclid = new Euclid($beats, $pulses - $beats);

	$euclid->setStartDur($myFile->mbt2at($meas), $myFile->b2dur(4));
	foreach ($euclid->walkSD AS $start => $dur)
		$new_track->addNote($start, $chan, MIDIEvent::DRUM_AC_SNARE, $vel, $dur);

	// Ride bell...
	$pulses = 4;
	$beats = rand(1, $pulses);
	$euclid = new Euclid($beats, $pulses - $beats);

	$euclid->setStartDur($myFile->mbt2at($meas), $myFile->b2dur(4));
	foreach ($euclid->walkSD AS $start => $dur)
		$new_track->addNote($start, $chan, MIDIEvent::DRUM_RIDE_BELL, $vel, $dur);

	// Ride...
	$pulses = 16;
	$beats = rand(1, $pulses);
	$euclid = new Euclid($beats, $pulses - $beats);

	$euclid->setStartDur($myFile->mbt2at($meas), $myFile->b2dur(4));
	foreach ($euclid->walkSD AS $start => $dur)
		$new_track->addNote($start, $chan, MIDIEvent::DRUM_RIDE, $vel, $dur);
}

// Write & dump the file if you wanna
$myFile->writeMIDIFile($out_name);
//$myFile->displayMIDIFile();

?>