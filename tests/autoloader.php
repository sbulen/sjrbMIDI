<?php
/**
 *	Auto loader.
 *	Keeps it simple...  Just include all the classes in the sources folder.
 *
 *	Copyright 2020 Shawn Bulen
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

// This protects you in case they get loaded out of sequence...
spl_autoload_register(function ($class_name) {
		include_once '..\sources\class-' . $class_name . '.php';
	}
);

$files = glob("..\sources\class-*.php", GLOB_NOESCAPE | GLOB_ERR);

foreach ($files AS $file)
{
	echo 'Including file: ' . $file . PHP_EOL;
	include_once($file);
}

?>