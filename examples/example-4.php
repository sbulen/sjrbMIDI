<?php
/**
 * sjrbMIDI example
 * Displays all variants of Euclidean rhythms for 16 & 32 & 64 pulses.
 */

spl_autoload_register(function ($class_name) {
		include '..\sources\class-' . $class_name . '.php';
	}
);

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