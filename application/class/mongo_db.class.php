<?php
/**
 * nosql数据库mongodb类
 *
 * @author qingmu
 * @version 1.0
 * Created at:  2011-12-09
 */
class mongo_db {

	private $connection;
	private $db;

	private $select = array();
	private $where = array();
	private $limit = NULL;
	private $offset = NULL;
	private $sort = array();

	public $error = '';

	/* Constuct function
	 *
	 * Checks that the Mongo PECL library is installed and enabled
	 *
	 */
	public function __construct($db = NULL)
	{
		if(!class_exists('Mongo'))
		{
			throw new Exception('It looks like the MongoDB PECL extension isn\'t installed or enabled');
			return;
		}

		// Attempt to connect
		$this->connect($db);
	}

	/* Connect function
	 *
	 * Connect to a Mongo database
	 *
	 * Usage: $this->mongo_db->connect();
	 */ 
	private function connect($db = NULL)
	{
		$host = MONGO_HOST;
		$port = MONGO_PORT;
		$username = MONGO_USERNAME;
		$password = MONGO_PASSWORD;

		if($host == "" || $port == "")
		{
			throw new Exception('No host or port configured to connect to MongoDB');
			return;	
		}

		$auth = '';
		if($username !== "" && $password !== "")
		{
			$auth = "{$username}:{$password}@";
		}

		$connection_string = "mongodb://{$auth}{$host}:{$port}/{$db}";

		// Make the connection
		try
		{
			$this->connection = new Mongo($connection_string);
		}
		catch(MongoConnectionException $e)
		{
			$this -> error = $e -> getMessage();
		}

		if(!empty($db))
		{
			$this->db = $db;
		}
		else
		{
			throw new Exception('No Mongo database selected');
		}

		return $this;
	}

	/* Switch_db function
	 *
	 * Switch to a different Mongo database
	 *
	 * Usage: $this->mongo_db->switch_db("foobar");
	 */ 
	public function switch_db($database = "")
	{
		$database = trim($database);
		if(empty($database))
		{
			throw new Exception('Failed to switch to a different MongoDB database because name is empty');
		}

		else
		{
			$this->db = $database;
		}

		return $this;
	}

	//! Get Functions

	/* Select function
	 *
	 * Select specific fields from a document
	 *
	 * Usage: $this->mongo_db->select(array('foo','bar'))->get('foobar');
	 */ 
	public function select($what = array())
	{
		if(is_array($what) && count($what) > 0)
		{
			$this->select = $what;
		}
		elseif($what !== "")
		{
			$this->select = array();
			$this->select[] = $what;
		}

		return $this;
	}

	/* Where function
	 *
	 * Get documents where something
	 *
	 * Usage: $this->mongo_db->where(array('foo' => 1))->get('foobar');
	 */ 
	public function where($where = array())
	{
		$this->where = $where;
		return $this;
	}

	/* Where_in function
	 *
	 * Get documents where something is in an array of something
	 *
	 * Usage: $this->mongo_db->where_in('foo', array(1,2,3))->get('foobar');
	 */ 
	public function where_in($what = "", $in = array())
	{
		$this->_where_init($what);

		$this->where[$what]['$in'] = $in;
		return $this;
	}

	/* Where_in function
	 *
	 * Get documents where something is in all of an array of something
	 *
	 * Usage: $this->mongo_db->where_in_all('foo', array(1,2,3))->get('foobar');
	 */
	public function where_in_all($what = "", $in = array())
	{
		$this->_where_init($what);

		$this->where[$what]['$all'] = $in;
		return $this;
	}

	/* Where_not_in function
	 *
	 * Get documents where something is not in an array of something
	 *
	 * Usage: $this->mongo_db->where_not_in('foo', array(1,2,3))->get('foobar');
	 */
	public function where_not_in($what = "", $in)
	{
		$this->_where_init($what);

		$this->where[$what]['$nin'] = $in;
		return $this;
	}

	/* Where_gt function
	 *
	 * Get documents where something is greater than something
	 *
	 * Usage: $this->mongo_db->where_gt('foo', 1)->get('foobar');
	 */
	public function where_gt($what, $gt)
	{
		$this->_where_init($what);

		$this->where[$what]['$gt'] = $gt;
		return $this;
	}

	/* Where_gte function
	 *
	 * Get documents where something is greater than or equal to something
	 *
	 * Usage: $this->mongo_db->where_gte('foo', 1)->get('foobar');
	 */
	public function where_gte($what, $gte)
	{
		$this->_where_init($what);

		$this->where[$what]['$gte'] = $gte;
		return $this;
	}

	/* Where_lt function
	 *
	 * Get documents where something is lee than something
	 *
	 * Usage: $this->mongo_db->where_lt('foo', 1)->get('foobar');
	 */
	public function where_lt($what, $lt)
	{
		$this->_where_init($what);

		$this->where[$what]['$lt'] = $lt;
		return $this;
	}

	/* Where_lte function
	 *
	 * Get documents where something is less than or equal to something
	 *
	 * Usage: $this->mongo_db->where_lte('foo', 1)->get('foobar');
	 */
	public function where_lte($what, $lte)
	{
		$this->_where_init($what);

		$this->where[$what]['$lte'] = $lte;
		return $this;
	}

	/* Where_lte function
	 *
	 * Get documents where something is not equal to something
	 *
	 * Usage: $this->mongo_db->where_not_equal('foo', 1)->get('foobar');
	 */
	public function where_not_equal($what, $to)
	{
		$this->_where_init($what);

		$this->where[$what]['$ne'] = $to;
		return $this;
	}

	/* Order_by function
	 *
	 * Order documents by something ascending (1) or descending (-1)
	 *
	 * Usage: $this->mongo_db->order_by('foo', 1)->get('foobar');
	 */
	public function order_by($what, $order = "ASC")
	{
		if($order == "ASC"){ $order = 1; }
		elseif($order == "DESC"){ $order = -1; }
		$this->sort[$what] = $order;
		return $this;
	}

	/* Limit function
	 *
	 * Limit the returned documents by something (and optionally an offset)
	 *
	 * Usage: $this->mongo_db->limit(5,5)->get('foobar');
	 */
	public function limit($limit = NULL, $offset = NULL)
	{
		if($limit !== NULL && is_numeric($limit) && $limit >= 1)
		{
			$this->limit = $limit;
		}

		if($offset !== NULL && is_numeric($offset) && $offset >= 1)
		{
			$this->offset = $offset;
		}

		return $this;
	}

	/* Get_where function
	 *
	 * Get documents where something
	 *
	 * Usage: $this->mongo_db->get_where('foobar', array('foo' => 'bar'));
	 */
	public function get_where($collection = "", $where = array())
	{
		return $this->where($where)->get($collection);
	}

	/* Get function
	 *
	 * Get documents from a collection
	 *
	 * Usage: $this->mongo_db->get('foobar');
	 */
	public function get($collection = "")
	{
		if($collection !== "")
		{
			$results = array();

			// Initial query
			$documents = $this->connection->{$this->db}->{$collection}->find($this->where);

			// Sort the results
			if(!empty($this->sort))
			{
				$documents = $documents->sort($this->sort);
			}

			// Limit the results
			if($this->limit !== NULL)
			{
				$documents = $documents->limit($this->limit);
			}

			// Offset the results
			if($this->offset !== NULL)
			{
				$documents = $documents->skip($this->offset);
			}

			// Get the results
			while($documents->hasNext())
			{
				$document = $documents->getNext();
				if($this->select !== NULL && count($this->select) > 0)
				{
					foreach($this->select as $s)
					{
						if(isset($document[$s])){
							$results[][$s] = $document[$s];
						}
					}
				}
				else
				{
					$results[] = $document;
				}

			}

			return $results;
		}

		else
		{
			throw new Exception('No Mongo collection selected to query');
		}	
	}

	/* Count function
	 *
	 * Count the number of documents
	 *
	 * Usage: $this->mongo_db->where(array('foo' => 'bar'))->count('foobar');
	 */
	public function count($collection = "")
	{
		if($collection !== "")
		{			
			// Initial query
			$documents = $this->connection->{$this->db}->{$collection}->find($this->where);

			// Limit the results
			if($this->limit !== NULL)
			{
				$documents = $documents->limit($this->limit);
			}

			// Offset the results
			if($this->offset !== NULL)
			{
				$documents = $documents->skip($this->offset);
			}

			$this->_clear();
			return $documents->count();
		}

		else
		{
			$this->_clear();
			throw new Exception('No Mongo collection selected');
		}
	}

	//! Insert functions

	/* Insert function
	 *
	 * Insert a new document into a collection
	 *
	 * Usage: $this->mongo_db->insert('foobar', array('foo' => 'bar'));
	 */
	public function insert($collection = "", $insert = array())
	{
		if($collection == "")
		{
			throw new Exception("No Mongo collection selected to insert into");
		}

		if(count($insert) == 0 || !is_array($insert))
		{
			throw new Exception("Nothing to insert into Mongo collection or insert is not an array");
		}

		return $this->connection->{$this->db}->{$collection}->insert($insert);
	}

	//! Update functions

	/* Update function
	 *
	 * Update a single document in a collection
	 *
	 * Usage: $this->mongo_db->where(array('foo' => 'bar'))->update('foobar', array('foo' => 'foobar'));
	 */
	public function update($collection = "", $update = array())
	{
		if($collection == "")
		{
			throw new Exception("No Mongo collection selected to insert into");
		}

		if(count($update) == 0 || !is_array($update))
		{
			throw new Exception("Nothing to update in Mongo collection or update is not an array");
		}

		$update_result = $this->connection->{$this->db}->{$collection}->update($this->where, array('$set' => $update));
		$this->_clear();
		return $update_result;
	}

	/* Update function
	 *
	 * Update a all documents in a collection
	 *
	 * Usage: $this->mongo_db->where(array('foo' => 'bar'))->update('foobar', array('foo' => 'foobar'));
	 */
	public function update_all($collection = "", $update = array())
	{
		if($collection == "")
		{
			throw new Exception("No Mongo collection selected to insert into");
		}

		if(count($update) == 0 || !is_array($update))
		{
			throw new Exception("Nothing to update in Mongo collection or update is not an array");
		}

		$update_result = $this->connection->{$this->db}->{$collection}->update($this->where, array('$set' => $update), array('multiple'=>TRUE));
		$this->_clear();
		return $update_result;
	}

	//! Delete functions

	/* Delete function
	 *
	 * Delete a single document in a collection
	 *
	 * Usage: $this->mongo_db->delete('foobar', array('foo' => 'foobar'));
	 */
	public function delete($collection = "", $delete = array())
	{
		if($collection == "")
		{
			throw new Exception("No Mongo collection selected to insert into");
		}

		if(count($delete) == 0 || !is_array($delete))
		{
			throw new Exception("Nothing to delete from Mongo collection or delete is not an array");
		}

		if(isset($delete["_id"]))
		{
			if(gettype($delete["_id"] == "string"))
			{
				$delete["_id"] = new MongoID($delete["_id"]);
			}
		}

		return $this->connection->{$this->db}->{$collection}->remove($delete, array('justOne'=>TRUE));
	}

	/* Delete function
	 *
	 * Delete all documents in a collection
	 *
	 * Usage: $this->mongo_db->delete('foobar', array('foo' => 'foobar'));
	 */
	public function delete_all($collection = "", $delete = array())
	{
		if($collection == "")
		{
			throw new Exception("No Mongo collection selected to insert into");
		}

		if(count($delete) == 0 || !is_array($delete))
		{
			throw new Exception("Nothing to delete from Mongo collection or delete is not an array");
		}

		if(isset($delete["_id"]))
		{
			if(gettype($delete["_id"] == "string"))
			{
				$delete["_id"] = new MongoID($delete["_id"]);
			}
		}

		return $this->connection->{$this->db}->{$collection}->remove($delete);
	}


	public function list_databases()
	{
		$databases = $this->connection->admin->command(array('listDatabases' => 1));

		$return = array();
		foreach($databases['databases'] as $db)
		{
			$return[$db['name']] = $db;
		}
		ksort($return);
		return $return;
	}

	public function drop_database($db)
	{
		return $this->connection->{$db}->drop();
	}

	public function repair()
	{
		return $this->connection->{$this->db}->repair($collection);
	}

	public function create_collection($collection)
	{
		return $this->connection->{$this->db}->createCollection($collection);
	}

	public function drop_collection($collection)
	{
		return $this->connection->{$this->db}->{$collection}->drop();
	}

	public function list_collections()
	{
		$collections = $this->connection->{$this->db}->listCollections();

		$return = array();
		foreach($collections as $coll)
		{
			$coll = substr(strstr((string) $coll, '.'), 1);
			$return[$coll] = $this->connection->{$this->db}->{$coll}->count();
		}
		ksort($return);
		return $return;
	}


	/*
	 * Internal function to clear params so there are no conflicts
	 */
	private function _clear()
	{
		$this->select = array();
		$this->where = array();
		$this->limit = NULL;
		$this->offset = NULL;
		$this->sort = array();
	}

	/*
	 * Internal function to initialise parameters for where calls
	 */
	private function _where_init($what)
	{
		if(!isset($this->where[$what]))
		{
			$this->where[$what] = array();
		}
	}

}    
?>