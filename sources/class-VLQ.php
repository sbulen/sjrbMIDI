<?php
/**
 *	MIDI file class for Variable Length Quantities...
 *
 *	Copyright 2020-2021 Shawn Bulen
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

class VLQ
{
	/**
	 * The actual value here
	 */
	protected $value;
	protected $len;
	protected $str;
	
	/**
	 * Constructor
	 *
	 * If passed a string, parse it for the VLQ value
	 *
	 * @param string $string - raw binary data to parse
	 * @return int
	 */
	function __construct($string = null)
	{
		if ($string !== null)
			$this->readVLQ($string);
		else
		{
			$this->value = 0;
			$this->len = 0;
			$this->str = '';
		}
	}

	/**
	 * Read & interpret input string as VLQ
	 * Return the value.
	 *
	 * @param string $string - raw binary data to parse
	 * @return int
	 */
	public function readVLQ($string = '')
	{
		$this->value = 0;
		$this->len = 0;

		$length = strlen($string);
		for ($i = 0; $i < $length; $i++)
		{
			$this->len++;
			$temp = ord(substr($string, $i, 1));
			$this->value = ($this->value << 7) | ($temp & 0x7F);
			if (($temp & 0x80) === 0)
				break;
		};
		$this->str = substr($string, 0, $this->len);
		return $this->value;
	}

	/**
	 * Return the value
	 *
	 * @return int
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Return the length
	 *
	 * @return int
	 */
	public function getLen()
	{
		return $this->len;
	}

	/**
	 * Return the VLQ binary string itself
	 *
	 * @return string
	 */
	public function getStr()
	{
		return $this->str;
	}

	/**
	 * Set the value, len & str when passed an integer
	 * Return the binary string - helpful for all the pack routines.
	 *
	 * @param int $value
	 * @return string
	 */
	public function setValue($int = 0)
	{
		$this->value = $int;
		$this->len = 0;
		$this->str = '';
		$lastbyte = true;

		if ($int < 2^28)
		{
			while ($int > 0 || $lastbyte)
			{
				if ($lastbyte)
				{
					$newbyte = chr($int & 0x7F);
					$lastbyte = false;
				}
				else
					$newbyte = chr(0x80 | ($int & 0x7F));
				$this->str = $newbyte . $this->str;
				$this->len++;
				$int = $int >> 7;
			}
		}
		else
			// Invalid value
			$this->value = 0;

		return $this->str;
	}

}
?>