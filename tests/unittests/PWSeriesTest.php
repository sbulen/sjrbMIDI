<?php

use PHPUnit\Framework\TestCase;

class PWSeriesTest extends TestCase {

    /*
     * Testing the PWSeries functions
     */

    public function testPWSeries(){

		$pw_data = array(
			0 => array('sine' => 0, 'saw' => -8192, 'square' => 8191, 'expo' => -8191),
			96 => array('sine' => 4814, 'saw' => -6553, 'square' => 'same', 'expo' => 'same'),
			192 => array('sine' => 7790, 'saw' => -4915, 'square' => 'same', 'expo' => 'same'),
			288 => array('sine' => 'same', 'saw' => -3277, 'square' => 'same', 'expo' => 'same'),
			384 => array('sine' => 4814, 'saw' => -1638, 'square' => 'same', 'expo' => 'same'),
			480 => array('sine' => 0, 'saw' => 0, 'square' => 'same', 'expo' => 'same'),
			576 => array('sine' => -4814, 'saw' => 1637, 'square' => -8192, 'expo' => -8189),
			672 => array('sine' => -7790, 'saw' => 3276, 'square' => 'same', 'expo' => -8162),
			768 => array('sine' => 'same', 'saw' => 4914, 'square' => 'same', 'expo' => -7900),
			864 => array('sine' => -4814, 'saw' => 6552, 'square' => 'same', 'expo' => -5278),
		);

		// SINE...
		$pw_series = new PWSeries(0, EVENTSeries::SINE, 1, 0, 0, 100, 96);
		$events = $pw_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($pw_data[$event->getAt()]['sine'], $event->getValue(), 'PWSeries sine test failed');
		}

		// Saw...
		$pw_series = new PWSeries(0, EVENTSeries::SAW, 1, 0, 0, 100, 96);
		$events = $pw_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($pw_data[$event->getAt()]['saw'], $event->getValue(), 'PWSeries saw test failed');
		}

		// Square...
		$pw_series = new PWSeries(0, EVENTSeries::SQUARE, 1, 0, 0, 100, 96);
		$events = $pw_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($pw_data[$event->getAt()]['square'], $event->getValue(), 'PWSeries square test failed');
		}

		// Expo...
		$pw_series = new PWSeries(0, EVENTSeries::EXPO, 1, 0, 0, 100, 96);
		$events = $pw_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($pw_data[$event->getAt()]['expo'], $event->getValue(), 'PWSeries expo test failed');
		}

		// Random...
		$pw_series = new PWSeries(0, EVENTSeries::RANDOM_STEPS, 1, 0, 0, 100, 96);
		$events = $pw_series->genEvents(0, 960);

		// Just get back one #, since frequency is 1
		$this->assertEquals(1, count($events), 'PWSeries random test failed');
		$this->assertTrue(($events[0]->getValue() >=-0x2000) && ($events[0]->getValue() <=0x1FFF), 'PWSeries random range test failed');
	}

    public function testPWSeriesRange(){

		// Similar series tests, but with the range reversed...
		$pw_data = array(
			0=> array('sine' => 0, 'saw' => 5733, 'square' => -5734, 'expo' => 5733),
			96=> array('sine' => -3371, 'saw' => 4586, 'square' => 'same', 'expo' => 'same'),
			192=> array('sine' => -5454, 'saw' => 3439, 'square' => 'same', 'expo' => 'same'),
			288=> array('sine' => 'same', 'saw' => 2293, 'square' => 'same', 'expo' => 'same'),
			384=> array('sine' => -3371, 'saw' => 1146, 'square' => 'same', 'expo' => 'same'),
			480=> array('sine' => 0, 'saw' => 0, 'square' => 'same', 'expo' => 'same'),
			576=> array('sine' => 3369, 'saw' => -1147, 'square' => 5733, 'expo' => 5731),
			672=> array('sine' => 5452, 'saw' => -2294, 'square' => 'same', 'expo' => 5713),
			768=> array('sine' => 'same', 'saw' => -3440, 'square' => 'same', 'expo' => 5529),
			864=> array('sine' => 3369, 'saw' => -4587, 'square' => 'same', 'expo' => 3694),
		);

		// SINE...
		$pw_series = new PWSeries(0, EVENTSeries::SINE, 1, 0, 85, 15, 96);
		$events = $pw_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($pw_data[$event->getAt()]['sine'], $event->getValue(), 'PWSeries sine test 2 failed');
		}

		// Saw...
		$pw_series = new PWSeries(0, EVENTSeries::SAW, 1, 0, 85, 15, 96);
		$events = $pw_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($pw_data[$event->getAt()]['saw'], $event->getValue(), 'PWSeries saw test 2 failed');
		}

		// Square...
		$pw_series = new PWSeries(0, EVENTSeries::SQUARE, 1, 0, 85, 15, 96);
		$events = $pw_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($pw_data[$event->getAt()]['square'], $event->getValue(), 'PWSeries square test 2 failed');
		}

		// Expo...
		$pw_series = new PWSeries(0, EVENTSeries::EXPO, 1, 0, 85, 15, 96);
		$events = $pw_series->genEvents(0, 960);

		foreach($events AS $event)
		{
			$this->assertEquals($pw_data[$event->getAt()]['expo'], $event->getValue(), 'PWSeries expo test 2 failed');
		}

		// Random...
		$pw_series = new PWSeries(0, EVENTSeries::RANDOM_STEPS, 1, 0, 85, 15, 96);
		$events = $pw_series->genEvents(0, 960);

		// Just get back one #, since frequency is 1
		$this->assertEquals(1, count($events), 'PWSeries random test failed');
		$this->assertTrue(($events[0]->getValue() >=-0x2000) && ($events[0]->getValue() <=0x1FFF), 'PWSeries random range test 2 failed');
	}
}
?>