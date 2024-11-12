<?php
/**
 * sjrbMIDI example
 * Displays all variants of Euclidean rhythms for 16 & 32 & 64 pulses.
 */

// First things first, these scripts need to know source & language folders.
$sourcedir = 'd:\wamp64\www\sjrbMIDI\sources\\';
$langdir = 'd:\wamp64\www\sjrbMIDI\languages\\';

spl_autoload_register(function ($class_name) use ($sourcedir) {
		include $sourcedir . '\class-' . $class_name . '.php';
	}
);
Errors::setLanguageDir($langdir);
Errors::setVerbosity(true);

// Step thru them all...
echo '<font size="3" face="Courier New">';
$pulses = 16;
for ($beats = 0; $beats <= $pulses; $beats++)
{
	$euclid = new Euclid($beats, $pulses - $beats);
	echo $euclid->getPattern() . '<br>';
}

// Step thru them all...
$pulses = 32;
for ($beats = 0; $beats <= $pulses; $beats++)
{
	$euclid = new Euclid($beats, $pulses - $beats);
	echo $euclid->getPattern() . '<br>';
}

// Step thru them all...
$pulses = 64;
for ($beats = 0; $beats <= $pulses; $beats++)
{
	$euclid = new Euclid($beats, $pulses - $beats);
	echo $euclid->getPattern() . '<br>';
}

// Step thru them all...
$pulses = 128;
for ($beats = 0; $beats <= $pulses; $beats++)
{
	$euclid = new Euclid($beats, $pulses - $beats);
	echo $euclid->getPattern() . '<br>';
}

?>