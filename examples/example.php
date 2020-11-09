<?php
/**
 * sjrbMIDI example
 * Simple random sequence of notes, with pitch bend & expression controller.
 */

spl_autoload_register(function ($class_name) {
		include '..\sources\class-' . $class_name . '.php';
	}
);

$out_name = 'example.mid';

$myFile = new MIDIFile();
$myFile->setBPM(97);
$new_track = $myFile->addTrack();

// Random rhythm...
$pulse = 16;
$notes = rand(1, $pulse);
$euclid = new Euclid($notes, $pulse - $notes);
$rhythm = $euclid->getRhythm();
print_r($euclid);
echo '<br>';

// Brownian walk to that random rhythm...
$chan = 0;
$note = 64;
$vel = 120;
for ($meas = 1; $meas <= 16; $meas++)
{
	$start = 0;
	foreach ($rhythm AS $rlen)
	{
		$time = $myFile->mbt2at($meas, 1, $start);
		$dur = $rlen * $myFile->b2dur(4) / $pulse;
		$new_track->addNote($time, $chan, $note, $vel, $dur);
		$note = MIDIEvent::rangeCheck($note + rand(-12, 12));
		$start += $dur;
	}

	// Bend down at the end of each note...
	$freq = 1;
	$start = $myFile->mbt2at($meas, 1);
	$pw_series = new PWSeries($chan, EVENTSeries::EXPO, $freq, 0, 50, 0, 12);
	foreach ($rhythm AS $rlen)
	{
		$dur = $rlen * $myFile->b2dur(4) / $pulse;
		$new_track->addEvents($pw_series->genEvents($start, $dur));
		$start += $dur;
	}

	// Some LFO...
	$cc = 11;
	$freq = 0.75;
	$start = $myFile->mbt2at($meas, 1);
	$cc_series = new CCSeries($chan, $cc, EVENTSeries::SINE, $freq, 0, 0, 100, 12);
	foreach ($rhythm AS $rlen)
	{
		$dur = $rlen * $myFile->b2dur(4) / $pulse;

		// Each note will start with a random angle, 1/4 of a circle...
		$cc_series->setOffset(rand(0, 3) * 90);
		$new_track->addEvents($cc_series->genEvents($start, $dur));

		$start += $dur;
	}

}

// Each track must have a TrackEnd
$new_track->addTrackEnd($myFile->mbt2at(17,1,0));

// Write & dump the file
$myFile->writeMIDIFile($out_name);
$myFile->displayMIDIFile();

?>