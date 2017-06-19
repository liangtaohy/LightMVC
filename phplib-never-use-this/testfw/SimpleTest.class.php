<?php

/**
 * 通用测试类（适用于简单的单测场景）
 * @author liangtao01
 * @date 2016/01/23 22:13
 */

class SimpleTest
{
	private static $failed = 0;
	private static $passed = 0;

	private static $failed_trace = array();

	public static function assert_value( $got, $should_be, $section = '' )
	{
		self::register_case( $got == $should_be, $section);

		if ( $got != $should_be )
		{
			echo self::colorize('TEST-->Got: ' . json_encode($got) . ' expected: ' . json_encode($should_be) . '', 'FAILURE') . "\n";
		}
	}

	public static function assert_true( $expression, $section = '' )
	{
		self::register_case($expression === true, $section);
	}

	public static function assert_false( $expression, $section = '' )
	{
		self::register_case($expression === false, $section);
	}

	public static function assert_null( $expression, $section = '' )
	{
		self::register_case($expression === null, $section);
	}

	private static function register_case( $passed, $section )
	{
		$passed ? self::$passed++ : self::$failed++;

		if ( $section )
		{
			echo "\n" . $section . "\n";
		}

		//echo $passed ? '.' : 'F';

		if ( !$passed )
		{
			$trace = debug_backtrace();
			self::$failed_trace[] = $trace[1];
		}
	}

	public static function summary()
	{
		echo "\n\n" . 'Test summary' . "\n";
		echo self::colorize('Passed: ' . self::$passed, 'SUCCESS');
		echo "\n\n";
		echo self::colorize('Failed: ' . self::$failed, 'FAILURE');

		if ( self::$failed_trace )
		{
			echo "\n\nFailed trace:\n";
			foreach ( self::$failed_trace as $trace )
			{
				echo "- line: {$trace['line']}, {$trace['function']}\n";
			}
		}

		echo "\n";
	}

	private static function colorize($text, $status) {
		$out = "";
		switch($status) {
			case "SUCCESS":
				$out = "[42m"; //Green background
				break;
			case "FAILURE":
				$out = "[41m"; //Red background
				break;
			case "WARNING":
				$out = "[43m"; //Yellow background
				break;
			case "NOTE":
				$out = "[44m"; //Blue background
				break;
			default:
				throw new Exception("Invalid status: " . $status);
		}
		return chr(27) . "$out" . "$text" . chr(27) . "[0m";
	}
}
?>
