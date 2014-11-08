<?php
/**
*
* Auto Groups extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\autogroups\conditions\type;

/**
* Auto Groups service class
*/
abstract class base implements \phpbb\autogroups\conditions\type\type_interface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/**
	* @var string The database table the auto group rules are stored in
	*/
	protected $autogroups_rules_table;

	/**
	* @var string The database table the auto group conditions are stored in
	*/
	protected $autogroups_condition_types_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface    $db                                 Database object
	* @param \phpbb\user                          $user                               User object
	* @param string                               $autogroups_rules_table             Name of the table used to store auto group rules data
	* @param string                               $autogroups_condition_types_table   Name of the table used to store auto group conditions data
	*
	* @return \phpbb\autogroups\conditions\type\base
	* @access public
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, $autogroups_rules_table, $autogroups_condition_types_table)
	{
		$this->db = $db;
		$this->user = $user;

		$this->autogroups_rules_table = $autogroups_rules_table;
		$this->autogroups_condition_types_table = $autogroups_condition_types_table;
	}

	/**
	* Get auto group rules for condition
	*
	* @param string $condition Auto group condition type name
	* @return array Auto group rows
	* @access public
	*/
	public function get_group_rules($condition)
	{
		$sql_array = array(
			'SELECT'	=> 'ag.*',
			'FROM'	=> array(
				$this->autogroups_rules_table => 'ag',
				$this->autogroups_condition_types_table => 'agc',
			),
			'WHERE'	=> 'ag.condition_type_id = agc.condition_type_id
				AND agc.condition_type_name = ' . $condition,
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Get users group ids
	*
	* @return array User group ids array
	* @access public
	*/
	public function get_users_groups()
	{
		$sql = 'SELECT group_id
			FROM ' . USER_GROUP_TABLE . '
			WHERE user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	* Add user to groups
	*
	* @param array $groups_data Data array where a group id is a key and default is value
	* @return null
	* @access public
	*/
	public function add_user_to_groups($groups_data)
	{
		foreach ($groups_data as $group_id => $default)
		{
			group_user_add($group_id, $this->user->data['user_id'], false, false, $default);
		}
	}

	/**
	* Remove user from groups
	*
	* @param array $groups_data Data array with group ids
	* @return null
	* @access public
	*/
	public function remove_user_from_groups($groups_data)
	{
		foreach ($groups_data as $group_id)
		{
			group_user_del($group_id, $this->user->data['user_id']);
		}
	}
}
