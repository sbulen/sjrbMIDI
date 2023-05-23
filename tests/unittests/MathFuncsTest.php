<?php

use PHPUnit\Framework\TestCase;

class MathFuncsTest extends TestCase {

	/*
	 * Testing the math functions
	 */

	public function testMathFuncs(){

		// First, test random float...
		// Just do a thousand random calls, make sure they're a float >= 0 and <= 1
		for ($i = 0; $i < 1000; $i++)
		{
			$result = MathFuncs::randomFloat();

			// check result
			$this->assertIsFloat($result, 'Math random float test 1 failed');
			$this->assertGreaterThanOrEqual(0, $result, 'Math random float test 2 failed');
			$this->assertLessThanOrEqual(1, $result, 'Math random float test 3 failed');
		}

		// Test power of 2 check...
		$data = array (
			array('in' => -8, 'out' => false),
			array('in' => -2.1, 'out' => false),
			array('in' => -1, 'out' => false),
			array('in' => 0, 'out' => false),
			array('in' => 0.125, 'out' => true),
			array('in' => 0.5, 'out' => true),
			array('in' => 1, 'out' => true),
			array('in' => 2, 'out' => true),
			array('in' => 3, 'out' => false),
			array('in' => 3.14, 'out' => false),
			array('in' => 2.17, 'out' => false),
			array('in' => 4, 'out' => true),
			array('in' => 4.000001, 'out' => false),
			array('in' => 8, 'out' => true),
			array('in' => 17, 'out' => false),
			array('in' => 64, 'out' => true),
			array('in' => 98, 'out' => false),
			array('in' => 512, 'out' => true),
			array('in' => 1000, 'out' => false),
			array('in' => 1024, 'out' => true),
			array('in' => 9999, 'out' => false),
		);

		foreach ($data AS $row)
		{
			$result = MathFuncs::isPow2($row['in']);

			// check result
			$this->assertEquals($row['out'], $result, 'Math pow2 test failed');
		}

		//Test even check....
		$data = array (
			array('in' => -2.00001, 'out' => false),
			array('in' => -2, 'out' => true),
			array('in' => -1, 'out' => false),
			array('in' => 0, 'out' => true),
			array('in' => 0.0, 'out' => true),
			array('in' => 0.00001, 'out' => false),
			array('in' => 1, 'out' => false),
			array('in' => 1.1, 'out' => false),
			array('in' => 2, 'out' => true),
			array('in' => 2.0000001, 'out' => false),
			array('in' => 1024, 'out' =>true),
			array('in' => 1024.1, 'out' =>false),
			array('in' => 1025, 'out' =>false),
		);

		foreach ($data AS $row)
		{
			$result = MathFuncs::isEven($row['in']);

			// check result
			$this->assertEquals($row['out'], $result, 'Math isEven test failed');
		}
	}
}
?>