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
$pulse = 16;
for ($beats = 0; $beats <= $pulse; $beats++)
{
	$euclid = new Euclid($beats, $pulse - $beats);
	echo $euclid->getPattern() . '<br>';
}

// Step thru them all...
$pulse = 32;
for ($beats = 0; $beats <= $pulse; $beats++)
{
	$euclid = new Euclid($beats, $pulse - $beats);
	echo $euclid->getPattern() . '<br>';
}

// Step thru them all...
$pulse = 64;
for ($beats = 0; $beats <= $pulse; $beats++)
{
	$euclid = new Euclid($beats, $pulse - $beats);
	echo $euclid->getPattern() . '<br>';
}

// Step thru them all...
$pulse = 128;
for ($beats = 0; $beats <= $pulse; $beats++)
{
	$euclid = new Euclid($beats, $pulse - $beats);
	echo $euclid->getPattern() . '<br>';
}

?>