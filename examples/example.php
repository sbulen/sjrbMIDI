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
$pulses = 16;
$notes = rand(1, $pulses);
$euclid = new Euclid($notes, $pulses - $notes);
print_r($euclid);
echo '<br>';

// Note setup...
$chan = 0;
$note = 64;
$vel = 120;

// PW series setup... (params: chan, shape, frequency,  offset, min, max, ticks apart)
$freq = 1;
$pw_series = new PWSeries($chan, EVENTSeries::EXPO, $freq, 0, 50, 0, 12);

// CC series setup... (params: chan, cc, shape, frequency,  offset, min, max, ticks apart)
$cc = 11;
$freq = 0.75;
$cc_series = new CCSeries($chan, $cc, EVENTSeries::SINE, $freq, 0, 0, 100, 12);

// Follow the same rhythm each measure
for ($meas = 1; $meas <= 16; $meas++)
{
	// Walks make it easy to play to the rhythm...
	// First, you let it know the start & dur
	$euclid->setStartDur($myFile->mbt2at($meas), $myFile->b2dur(4));
	// ...then you can just do a foreach:
	foreach ($euclid->walkSD AS $start => $dur)
	{
		// Brownian walk to that random rhythm...
		$new_track->addNote($start, $chan, $note, $vel, $dur);
		$note = MIDIEvent::rangeCheck($note + rand(-12, 12));

		// Bend down at the end of each note...
		$new_track->addEvents($pw_series->genEvents($start, $dur));

		// Some LFO...  Each note will start with a random angle, 1/4 of a circle...
		$cc_series->setOffset(rand(0, 3) * 90);
		$new_track->addEvents($cc_series->genEvents($start, $dur));
	}
}

// Each track must have a TrackEnd
$new_track->addTrackEnd($myFile->mbt2at(17,1,0));

// Write & dump the file
$myFile->writeMIDIFile($out_name);
$myFile->displayMIDIFile();

?>