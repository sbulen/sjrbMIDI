<?php

use PHPUnit\Framework\TestCase;

class KeyTest extends TestCase {

	/*
	 * Testing the VLQ functions
	 */

	public function test_m2d_d2m(){

		//Array of string, value, lengths for test data
		// midi note, dnote octave, dnote interval, sharp/flat
		// C MAJOR scale
		$data = array (
			array('mn' => 0, 'dn' => 0, 'sf' => 0),
			array('mn' => 1, 'dn' => 0, 'sf' => 1),
			array('mn' => 2, 'dn' => 1, 'sf' => 0),
			array('mn' => 3, 'dn' => 1, 'sf' => 1),
			array('mn' => 4, 'dn' => 2, 'sf' => 0),
			array('mn' => 5, 'dn' => 3, 'sf' => 0),
			array('mn' => 6, 'dn' => 3, 'sf' => 1),
			array('mn' => 7, 'dn' => 4, 'sf' => 0),
			array('mn' => 8, 'dn' => 4, 'sf' => 1),
			array('mn' => 9, 'dn' => 5, 'sf' => 0),
			array('mn' => 10, 'dn' => 5, 'sf' => 1),
			array('mn' => 11, 'dn' => 6, 'sf' => 0),
			array('mn' => 12, 'dn' => 10, 'sf' => 0),
			array('mn' => 13, 'dn' => 10, 'sf' => 1),
			array('mn' => 14, 'dn' => 11, 'sf' => 0),
			array('mn' => 15, 'dn' => 11, 'sf' => 1),
			array('mn' => 16, 'dn' => 12, 'sf' => 0),
			array('mn' => 17, 'dn' => 13, 'sf' => 0),
			array('mn' => 18, 'dn' => 13, 'sf' => 1),
			array('mn' => 19, 'dn' => 14, 'sf' => 0),
			array('mn' => 20, 'dn' => 14, 'sf' => 1),
			array('mn' => 21, 'dn' => 15, 'sf' => 0),
			array('mn' => 22, 'dn' => 15, 'sf' => 1),
			array('mn' => 23, 'dn' => 16, 'sf' => 0),
			array('mn' => 24, 'dn' => 20, 'sf' => 0),
			array('mn' => 25, 'dn' => 20, 'sf' => 1),
			array('mn' => 26, 'dn' => 21, 'sf' => 0),
			array('mn' => 27, 'dn' => 21, 'sf' => 1),
			array('mn' => 28, 'dn' => 22, 'sf' => 0),
			array('mn' => 29, 'dn' => 23, 'sf' => 0),
			array('mn' => 30, 'dn' => 23, 'sf' => 1),
			array('mn' => 31, 'dn' => 24, 'sf' => 0),
			array('mn' => 32, 'dn' => 24, 'sf' => 1),
			array('mn' => 33, 'dn' => 25, 'sf' => 0),
			array('mn' => 34, 'dn' => 25, 'sf' => 1),
			array('mn' => 35, 'dn' => 26, 'sf' => 0),
			array('mn' => 36, 'dn' => 30, 'sf' => 0),
			array('mn' => 37, 'dn' => 30, 'sf' => 1),
			array('mn' => 38, 'dn' => 31, 'sf' => 0),
			array('mn' => 39, 'dn' => 31, 'sf' => 1),
			array('mn' => 40, 'dn' => 32, 'sf' => 0),
			array('mn' => 41, 'dn' => 33, 'sf' => 0),
			array('mn' => 42, 'dn' => 33, 'sf' => 1),
			array('mn' => 43, 'dn' => 34, 'sf' => 0),
			array('mn' => 44, 'dn' => 34, 'sf' => 1),
			array('mn' => 45, 'dn' => 35, 'sf' => 0),
			array('mn' => 46, 'dn' => 35, 'sf' => 1),
			array('mn' => 47, 'dn' => 36, 'sf' => 0),
			array('mn' => 48, 'dn' => 40, 'sf' => 0),
			array('mn' => 49, 'dn' => 40, 'sf' => 1),
			array('mn' => 50, 'dn' => 41, 'sf' => 0),
			array('mn' => 51, 'dn' => 41, 'sf' => 1),
			array('mn' => 52, 'dn' => 42, 'sf' => 0),
			array('mn' => 53, 'dn' => 43, 'sf' => 0),
			array('mn' => 54, 'dn' => 43, 'sf' => 1),
			array('mn' => 55, 'dn' => 44, 'sf' => 0),
			array('mn' => 56, 'dn' => 44, 'sf' => 1),
			array('mn' => 57, 'dn' => 45, 'sf' => 0),
			array('mn' => 58, 'dn' => 45, 'sf' => 1),
			array('mn' => 59, 'dn' => 46, 'sf' => 0),
			array('mn' => 60, 'dn' => 50, 'sf' => 0),
			array('mn' => 61, 'dn' => 50, 'sf' => 1),
			array('mn' => 62, 'dn' => 51, 'sf' => 0),
			array('mn' => 63, 'dn' => 51, 'sf' => 1),
			array('mn' => 64, 'dn' => 52, 'sf' => 0),
			array('mn' => 65, 'dn' => 53, 'sf' => 0),
			array('mn' => 66, 'dn' => 53, 'sf' => 1),
			array('mn' => 67, 'dn' => 54, 'sf' => 0),
			array('mn' => 68, 'dn' => 54, 'sf' => 1),
			array('mn' => 69, 'dn' => 55, 'sf' => 0),
			array('mn' => 70, 'dn' => 55, 'sf' => 1),
			array('mn' => 71, 'dn' => 56, 'sf' => 0),
			array('mn' => 72, 'dn' => 60, 'sf' => 0),
			array('mn' => 73, 'dn' => 60, 'sf' => 1),
			array('mn' => 74, 'dn' => 61, 'sf' => 0),
			array('mn' => 75, 'dn' => 61, 'sf' => 1),
			array('mn' => 76, 'dn' => 62, 'sf' => 0),
			array('mn' => 77, 'dn' => 63, 'sf' => 0),
			array('mn' => 78, 'dn' => 63, 'sf' => 1),
			array('mn' => 79, 'dn' => 64, 'sf' => 0),
			array('mn' => 80, 'dn' => 64, 'sf' => 1),
			array('mn' => 81, 'dn' => 65, 'sf' => 0),
			array('mn' => 82, 'dn' => 65, 'sf' => 1),
			array('mn' => 83, 'dn' => 66, 'sf' => 0),
			array('mn' => 84, 'dn' => 100, 'sf' => 0),
			array('mn' => 85, 'dn' => 100, 'sf' => 1),
			array('mn' => 86, 'dn' => 101, 'sf' => 0),
			array('mn' => 87, 'dn' => 101, 'sf' => 1),
			array('mn' => 88, 'dn' => 102, 'sf' => 0),
			array('mn' => 89, 'dn' => 103, 'sf' => 0),
			array('mn' => 90, 'dn' => 103, 'sf' => 1),
			array('mn' => 91, 'dn' => 104, 'sf' => 0),
			array('mn' => 92, 'dn' => 104, 'sf' => 1),
			array('mn' => 93, 'dn' => 105, 'sf' => 0),
			array('mn' => 94, 'dn' => 105, 'sf' => 1),
			array('mn' => 95, 'dn' => 106, 'sf' => 0),
			array('mn' => 96, 'dn' => 110, 'sf' => 0),
			array('mn' => 97, 'dn' => 110, 'sf' => 1),
			array('mn' => 98, 'dn' => 111, 'sf' => 0),
			array('mn' => 99, 'dn' => 111, 'sf' => 1),
			array('mn' => 100, 'dn' => 112, 'sf' => 0),
			array('mn' => 101, 'dn' => 113, 'sf' => 0),
			array('mn' => 102, 'dn' => 113, 'sf' => 1),
			array('mn' => 103, 'dn' => 114, 'sf' => 0),
			array('mn' => 104, 'dn' => 114, 'sf' => 1),
			array('mn' => 105, 'dn' => 115, 'sf' => 0),
			array('mn' => 106, 'dn' => 115, 'sf' => 1),
			array('mn' => 107, 'dn' => 116, 'sf' => 0),
			array('mn' => 108, 'dn' => 120, 'sf' => 0),
			array('mn' => 109, 'dn' => 120, 'sf' => 1),
			array('mn' => 110, 'dn' => 121, 'sf' => 0),
			array('mn' => 111, 'dn' => 121, 'sf' => 1),
			array('mn' => 112, 'dn' => 122, 'sf' => 0),
			array('mn' => 113, 'dn' => 123, 'sf' => 0),
			array('mn' => 114, 'dn' => 123, 'sf' => 1),
			array('mn' => 115, 'dn' => 124, 'sf' => 0),
			array('mn' => 116, 'dn' => 124, 'sf' => 1),
			array('mn' => 117, 'dn' => 125, 'sf' => 0),
			array('mn' => 118, 'dn' => 125, 'sf' => 1),
			array('mn' => 119, 'dn' => 126, 'sf' => 0),
			array('mn' => 120, 'dn' => 130, 'sf' => 0),
			array('mn' => 121, 'dn' => 130, 'sf' => 1),
			array('mn' => 122, 'dn' => 131, 'sf' => 0),
			array('mn' => 123, 'dn' => 131, 'sf' => 1),
			array('mn' => 124, 'dn' => 132, 'sf' => 0),
			array('mn' => 125, 'dn' => 133, 'sf' => 0),
			array('mn' => 126, 'dn' => 133, 'sf' => 1),
			array('mn' => 127, 'dn' => 134, 'sf' => 0),
		);

		$myKey = new Key(Key::C_NOTE, KEY::MAJOR_SCALE);

		// m2d
		foreach ($data AS $notestuff)
		{
			$dnote = $myKey->m2d($notestuff['mn']);
			$this->assertEquals($dnote['dn'], $notestuff['dn'], 'm2d test - dn failed');
			$this->assertEquals($dnote['sf'], $notestuff['sf'], 'm2d test - sf failed');
		}

		// d2m
		foreach ($data AS $notestuff)
		{
			$dnote = array('dn' => $notestuff['dn'], 'sf' => $notestuff['sf']);
			$mnote = $myKey->d2m($dnote);
			$this->assertEquals($mnote, $notestuff['mn'], 'd2m test failed');
		}

		// Repeat everything in the Eb LOCRIAN modal
		$data = array (
			array('mn' => 0, 'dn' => 5, 'sf' => 1),
			array('mn' => 1, 'dn' => 6, 'sf' => 0),
			array('mn' => 2, 'dn' => 6, 'sf' => 1),
			array('mn' => 3, 'dn' => 10, 'sf' => 0),
			array('mn' => 4, 'dn' => 11, 'sf' => 0),
			array('mn' => 5, 'dn' => 11, 'sf' => 1),
			array('mn' => 6, 'dn' => 12, 'sf' => 0),
			array('mn' => 7, 'dn' => 12, 'sf' => 1),
			array('mn' => 8, 'dn' => 13, 'sf' => 0),
			array('mn' => 9, 'dn' => 14, 'sf' => 0),
			array('mn' => 10, 'dn' => 14, 'sf' => 1),
			array('mn' => 11, 'dn' => 15, 'sf' => 0),
			array('mn' => 12, 'dn' => 15, 'sf' => 1),
			array('mn' => 13, 'dn' => 16, 'sf' => 0),
			array('mn' => 14, 'dn' => 16, 'sf' => 1),
			array('mn' => 15, 'dn' => 20, 'sf' => 0),
			array('mn' => 16, 'dn' => 21, 'sf' => 0),
			array('mn' => 17, 'dn' => 21, 'sf' => 1),
			array('mn' => 18, 'dn' => 22, 'sf' => 0),
			array('mn' => 19, 'dn' => 22, 'sf' => 1),
			array('mn' => 20, 'dn' => 23, 'sf' => 0),
			array('mn' => 21, 'dn' => 24, 'sf' => 0),
			array('mn' => 22, 'dn' => 24, 'sf' => 1),
			array('mn' => 23, 'dn' => 25, 'sf' => 0),
			array('mn' => 24, 'dn' => 25, 'sf' => 1),
			array('mn' => 25, 'dn' => 26, 'sf' => 0),
			array('mn' => 26, 'dn' => 26, 'sf' => 1),
			array('mn' => 27, 'dn' => 30, 'sf' => 0),
			array('mn' => 28, 'dn' => 31, 'sf' => 0),
			array('mn' => 29, 'dn' => 31, 'sf' => 1),
			array('mn' => 30, 'dn' => 32, 'sf' => 0),
			array('mn' => 31, 'dn' => 32, 'sf' => 1),
			array('mn' => 32, 'dn' => 33, 'sf' => 0),
			array('mn' => 33, 'dn' => 34, 'sf' => 0),
			array('mn' => 34, 'dn' => 34, 'sf' => 1),
			array('mn' => 35, 'dn' => 35, 'sf' => 0),
			array('mn' => 36, 'dn' => 35, 'sf' => 1),
			array('mn' => 37, 'dn' => 36, 'sf' => 0),
			array('mn' => 38, 'dn' => 36, 'sf' => 1),
			array('mn' => 39, 'dn' => 40, 'sf' => 0),
			array('mn' => 40, 'dn' => 41, 'sf' => 0),
			array('mn' => 41, 'dn' => 41, 'sf' => 1),
			array('mn' => 42, 'dn' => 42, 'sf' => 0),
			array('mn' => 43, 'dn' => 42, 'sf' => 1),
			array('mn' => 44, 'dn' => 43, 'sf' => 0),
			array('mn' => 45, 'dn' => 44, 'sf' => 0),
			array('mn' => 46, 'dn' => 44, 'sf' => 1),
			array('mn' => 47, 'dn' => 45, 'sf' => 0),
			array('mn' => 48, 'dn' => 45, 'sf' => 1),
			array('mn' => 49, 'dn' => 46, 'sf' => 0),
			array('mn' => 50, 'dn' => 46, 'sf' => 1),
			array('mn' => 51, 'dn' => 50, 'sf' => 0),
			array('mn' => 52, 'dn' => 51, 'sf' => 0),
			array('mn' => 53, 'dn' => 51, 'sf' => 1),
			array('mn' => 54, 'dn' => 52, 'sf' => 0),
			array('mn' => 55, 'dn' => 52, 'sf' => 1),
			array('mn' => 56, 'dn' => 53, 'sf' => 0),
			array('mn' => 57, 'dn' => 54, 'sf' => 0),
			array('mn' => 58, 'dn' => 54, 'sf' => 1),
			array('mn' => 59, 'dn' => 55, 'sf' => 0),
			array('mn' => 60, 'dn' => 55, 'sf' => 1),
			array('mn' => 61, 'dn' => 56, 'sf' => 0),
			array('mn' => 62, 'dn' => 56, 'sf' => 1),
			array('mn' => 63, 'dn' => 60, 'sf' => 0),
			array('mn' => 64, 'dn' => 61, 'sf' => 0),
			array('mn' => 65, 'dn' => 61, 'sf' => 1),
			array('mn' => 66, 'dn' => 62, 'sf' => 0),
			array('mn' => 67, 'dn' => 62, 'sf' => 1),
			array('mn' => 68, 'dn' => 63, 'sf' => 0),
			array('mn' => 69, 'dn' => 64, 'sf' => 0),
			array('mn' => 70, 'dn' => 64, 'sf' => 1),
			array('mn' => 71, 'dn' => 65, 'sf' => 0),
			array('mn' => 72, 'dn' => 65, 'sf' => 1),
			array('mn' => 73, 'dn' => 66, 'sf' => 0),
			array('mn' => 74, 'dn' => 66, 'sf' => 1),
			array('mn' => 75, 'dn' => 100, 'sf' => 0),
			array('mn' => 76, 'dn' => 101, 'sf' => 0),
			array('mn' => 77, 'dn' => 101, 'sf' => 1),
			array('mn' => 78, 'dn' => 102, 'sf' => 0),
			array('mn' => 79, 'dn' => 102, 'sf' => 1),
			array('mn' => 80, 'dn' => 103, 'sf' => 0),
			array('mn' => 81, 'dn' => 104, 'sf' => 0),
			array('mn' => 82, 'dn' => 104, 'sf' => 1),
			array('mn' => 83, 'dn' => 105, 'sf' => 0),
			array('mn' => 84, 'dn' => 105, 'sf' => 1),
			array('mn' => 85, 'dn' => 106, 'sf' => 0),
			array('mn' => 86, 'dn' => 106, 'sf' => 1),
			array('mn' => 87, 'dn' => 110, 'sf' => 0),
			array('mn' => 88, 'dn' => 111, 'sf' => 0),
			array('mn' => 89, 'dn' => 111, 'sf' => 1),
			array('mn' => 90, 'dn' => 112, 'sf' => 0),
			array('mn' => 91, 'dn' => 112, 'sf' => 1),
			array('mn' => 92, 'dn' => 113, 'sf' => 0),
			array('mn' => 93, 'dn' => 114, 'sf' => 0),
			array('mn' => 94, 'dn' => 114, 'sf' => 1),
			array('mn' => 95, 'dn' => 115, 'sf' => 0),
			array('mn' => 96, 'dn' => 115, 'sf' => 1),
			array('mn' => 97, 'dn' => 116, 'sf' => 0),
			array('mn' => 98, 'dn' => 116, 'sf' => 1),
			array('mn' => 99, 'dn' => 120, 'sf' => 0),
			array('mn' => 100, 'dn' => 121, 'sf' => 0),
			array('mn' => 101, 'dn' => 121, 'sf' => 1),
			array('mn' => 102, 'dn' => 122, 'sf' => 0),
			array('mn' => 103, 'dn' => 122, 'sf' => 1),
			array('mn' => 104, 'dn' => 123, 'sf' => 0),
			array('mn' => 105, 'dn' => 124, 'sf' => 0),
			array('mn' => 106, 'dn' => 124, 'sf' => 1),
			array('mn' => 107, 'dn' => 125, 'sf' => 0),
			array('mn' => 108, 'dn' => 125, 'sf' => 1),
			array('mn' => 109, 'dn' => 126, 'sf' => 0),
			array('mn' => 110, 'dn' => 126, 'sf' => 1),
			array('mn' => 111, 'dn' => 130, 'sf' => 0),
			array('mn' => 112, 'dn' => 131, 'sf' => 0),
			array('mn' => 113, 'dn' => 131, 'sf' => 1),
			array('mn' => 114, 'dn' => 132, 'sf' => 0),
			array('mn' => 115, 'dn' => 132, 'sf' => 1),
			array('mn' => 116, 'dn' => 133, 'sf' => 0),
			array('mn' => 117, 'dn' => 134, 'sf' => 0),
			array('mn' => 118, 'dn' => 134, 'sf' => 1),
			array('mn' => 119, 'dn' => 135, 'sf' => 0),
			array('mn' => 120, 'dn' => 135, 'sf' => 1),
			array('mn' => 121, 'dn' => 136, 'sf' => 0),
			array('mn' => 122, 'dn' => 136, 'sf' => 1),
			array('mn' => 123, 'dn' => 140, 'sf' => 0),
			array('mn' => 124, 'dn' => 141, 'sf' => 0),
			array('mn' => 125, 'dn' => 141, 'sf' => 1),
			array('mn' => 126, 'dn' => 142, 'sf' => 0),
			array('mn' => 127, 'dn' => 142, 'sf' => 1),
		);

		$myKey = new Key(Key::Eb_NOTE, KEY::LOCRIAN_MODAL);

		// m2d
		foreach ($data AS $notestuff)
		{
			$dnote = $myKey->m2d($notestuff['mn']);
			$this->assertEquals($dnote['dn'], $notestuff['dn'], 'm2d modal test - dn failed');
			$this->assertEquals($dnote['sf'], $notestuff['sf'], 'm2d modal test - sf failed');
		}

		// d2m
		foreach ($data AS $notestuff)
		{
			$dnote = array('dn' => $notestuff['dn'], 'sf' => $notestuff['sf']);
			$mnote = $myKey->d2m($dnote);
			$this->assertEquals($mnote, $notestuff['mn'], 'd2m modal test failed');
		}
	}

	public function test_cleanseDNote(){

		$tests = array(
			3,
			-1,
			0,
			126,
			333,
			array(),
			array(3, 1),
			array('dn' => 66, 'sf' => 999),
			array('dn' => 66, 'sf' => 0),
		);

		$myKey = new Key(Key::C_NOTE, KEY::MAJOR_SCALE);

		// Confirm resturned structure is sound...
		foreach ($tests AS $test)
		{
			$dnote = $myKey->cleanseDnote($test);
			$this->assertArrayHasKey('dn', $dnote, 'cleanseDNote test failed');
			$this->assertArrayHasKey('sf', $dnote, 'cleanseDNote test failed');
			$this->assertEquals(2, count($dnote), 'cleanseDNote test failed');
		}
	}
}
?>