<?php
/**
*
* @package testing
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

abstract class phpbb_cache_common_test_case extends phpbb_database_test_case
{
	public function test_get_put_exists()
	{
		$this->assertFalse($this->driver->_exists('test_key'));
		$this->assertSame(false, $this->driver->get('test_key'));

		$this->driver->put('test_key', 'test_value');

		$this->assertTrue($this->driver->_exists('test_key'));
		$this->assertEquals(
			'test_value',
			$this->driver->get('test_key'),
			'File ACM put and get'
		);
	}

	public function test_purge()
	{
		$this->driver->put('test_key', 'test_value');

		$this->assertEquals(
			'test_value',
			$this->driver->get('test_key'),
			'File ACM put and get'
		);

		$this->driver->purge();

		$this->assertSame(false, $this->driver->get('test_key'));
	}

	public function test_destroy()
	{
		$this->driver->put('first_key', 'first_value');
		$this->driver->put('second_key', 'second_value');

		$this->assertEquals(
			'first_value',
			$this->driver->get('first_key')
		);
		$this->assertEquals(
			'second_value',
			$this->driver->get('second_key')
		);

		$this->driver->destroy('first_key');

		$this->assertFalse($this->driver->_exists('first_key'));
		$this->assertEquals(
			'second_value',
			$this->driver->get('second_key')
		);
	}

	public function test_cache_sql()
	{
		global $db, $cache;
		$db = $this->new_dbal();
		$cache = new phpbb_cache_service($this->driver);

		$sql = "SELECT * FROM phpbb_config
			WHERE config_name = 'foo'";

		$result = $db->sql_query($sql, 300);
		$first_result = $db->sql_fetchrow($result);
		$expected = array('config_name' => 'foo', 'config_value' => '23', 'is_dynamic' => 0);
		$this->assertEquals($expected, $first_result);

		$sql = 'DELETE FROM phpbb_config';
		$result = $db->sql_query($sql);

		$sql = "SELECT * FROM phpbb_config
			WHERE config_name = 'foo'";
		$result = $db->sql_query($sql, 300);

		$this->assertEquals($expected, $db->sql_fetchrow($result));

		$sql = "SELECT * FROM phpbb_config
			WHERE config_name = 'foo'";
		$result = $db->sql_query($sql);

		$no_cache_result = $db->sql_fetchrow($result);
		$this->assertSame(false, $no_cache_result);

		$db->sql_close();
	}
}
