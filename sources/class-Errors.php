<?php
/**
 *	Simple class for info & error reporting.
 *
 *	Copyright 2020-2024 Shawn Bulen
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

class Errors
{
	// Where to find language info
	static string $language_dir = '';
	static string $language = 'en_US';
	static ?array $txt = null;

	static bool $verbose = false;

	/**
	 * Info - just print what's passed.
	 * Infos are only printed in verbose mode.
	 *
	 * @void
	 */
	public static function info(string $key, string $more = ''): void
	{
		if (!self::$verbose)
			return;

		// Confirm it's loaded...
		self::loadLanguage();
		if (!empty(self::$txt[$key]))
			$key = self::$txt[$key];

		echo $key . ' ' . $more . '<br>';
	}

	/**
	 * Warning - just print the error, class & func.
	 * Warnings are only printed in verbose mode.
	 *
	 * @void
	 */
	public static function warning(string $key, string $more = ''): void
	{
		if (!self::$verbose)
			return;

		// Confirm it's loaded...
		self::loadLanguage();
		if (!empty(self::$txt[$key]))
			$key = self::$txt[$key];

		$trace = debug_backtrace();

		// If it's from the rangeCheck function, need to go back one more...
		if (!empty($trace[1]))
			if (($trace[1]['function'] == 'rangeCheck') && ($trace[1]['class'] == 'MIDIEvent') && !empty($trace[2]))
			{
				$class = $trace[2]['class'];
				$func = $trace[2]['function'];
			}
			else
			{
				$class = $trace[1]['class'];
				$func = $trace[1]['function'];
			}
		else
		{
			$class = '';
			$func = '';
		}

		echo self::$txt['warning'] . ': ' . $key . ' ' . $class . ' ' . $func . ' ' . $more . '<br>';

		echo '<pre>' . print_r($trace, true) . '</pre><br>';
	}

	/**
	 * Fatal - print the error & a trace & die.
	 * Fatal messages always printed.
	 *
	 * @void
	 */
	public static function fatal(string $key, string $more = ''): void
	{
		// Confirm it's loaded...
		self::loadLanguage();
		if (!empty(self::$txt[$key]))
			$key = self::$txt[$key];

		$trace = debug_backtrace();

		die(self::$txt['error'] . ': ' . $key . ' ' . $more . '<br><pre>' . print_r($trace, true) . '</pre><br>');
	}

	/**
	 * Set Verbosity - stack info on warnings
	 *
	 * @void
	 */
	public static function setVerbosity(bool $on): void
	{
		// Confirm it's loaded...
		self::loadLanguage();

		if (is_bool($on))
			self::$verbose = $on;
		else
			Errors::fatal('inv_bool');
	}

	/**
	 * Set Language Directory - where to find language files
	 *
	 * @void
	 */
	public static function setLanguageDir(string $langdir): void
	{
		self::$language_dir = $langdir;
	}

	/**
	 * Kinda sorta a constructor...  Makes sure language is loaded...
	 * PHP doesn't allow static constructors.  Yet.
	 * Should be first thing called by all of these funcs OR early in program.
	 *
	 * @void
	 */
	public static function loadLanguage(): void
	{
		if (empty(self::$txt))
		{
			require(self::$language_dir . self::$language . '\\errors.php');
			self::$txt = $txt;
		}
	}
}
?>