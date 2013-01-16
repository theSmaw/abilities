<?php
/**
* API Class
* Simply handles the calls to save test performance details once a test has finished.
*/
class API
{
    /**
     * The MySQL connection
     *
     * @var object
     * @access private
     */
    private $_con;

    /**
     * Constructor
     *
     * @access public
     */
    function __construct()
    {
        // let's connected to our db
        $this->_connect();

        // handle our request
        $this->_handle_request();
    }

    /**
     * Adds a test result into our DB
     *
     * @param $device_id int the DB ID of the device entry
     * @param $name string the name of the test, usually this is the JS test file name (minus .js)
     * @param $description string the description of the test
     * @param $value blob the resulting value of the test
     *
     * @access private
     */
    private function _add_result($device_id, $name, $description, $value)
    {
        // run the insert query
        $query = $this->_con->query('INSERT INTO ' . MYSQL_TABLE_TEST_RESULT . ' (`device_id`, `name`, `description`, `value`) \
            VALUES ("' . $device_id . '", "' . $name . '", "' . $description . '", "' . $value . '")');

        // return the insert ID, just for lulz
        return $this->_con->insert_id;
    }

    /**
     * Adds test results into our DB
     * It also checks to see if a device has been registered yet
     * This method accepts an array
     *
     * @param $results array an array of results
     *
     * @access private
     */
    private function _add_results($results)
    {
        // try and get the device's id and user agent
        $device = $this->_con->real_escape_string($_SERVER['HTTP_X_REQUEST_DEVICE']);
        $user_agent = $this->_con->real_escape_string($_SERVER['HTTP_USER_AGENT']);

        // check our db to see if this device exists
        $device_id = $this->_get_device_id($device, $user_agent);

        // loop through our results and add them to the db
        foreach ($results as $result)
        {
            // sanatise the result
            foreach ($result as $key => $value)
            {
                $result[$key] = $this->_con->real_escape_string($value);
            }

            $this->_add_result($device_id, $result['name'], $result['description'], $result['value']);
        }

        // return the device id as a response as a valid JSON string
        echo '"' . $device_id . '"';
    }

    /**
     * Connects to our MySQL database
     * Uses the config.php file required before this class is init'd
     *
     * @access private
     */
    private function _connect()
    {
        // we're gonna use mysqli
        $this->_con = new mysqli(MYSQL_HOST, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DB); // as defined in our config

        // can we haz connection?
        if ($this->_con->connect_error)
        {
            die('Failed to connect to DB');
        }
    }

    /**
     * Gets or generates a device ID
     *
     * @param $device string the device ID, if any
     * @param $user_agent string the browser user agent
     *
     * @access private
     */
    private function _get_device_id($device, $user_agent)
    {
        // run el query
        $query = $this->_con->query('SELECT `id` FROM ' . MYSQL_TABLE_DEVICE . ' WHERE device = "' . $device . '" AND user_agent = "' . $user_agent . '"');

        if ($query->num_rows === 1)
        {
            // set the device id
            $device_id = $query->fetch_object()->id;
        }
        else
        {
            // create a record for this device
            $query = $this->_con->query('INSERT INTO ' . MYSQL_TABLE_DEVICE . ' (`device`, `user_agent`) VALUES ("' . $device . '", "' . $user_agent . '")');

            $device_id = $this->_con->insert_id;
        }

        return $device_id;
    }

    /**
     * Handles our request to the API
     * Currently you can only POST test results
     *
     * @access private
     */
    private function _handle_request()
    {
        // check for post
        if (empty($_POST))
        {
            // gracefully fail
            throw new BadMethodCallException('$_GET requests not implemeted yet');
        }

        // handle our request, assuming all the requested vars are present
        if (isset($_POST['results']))
        {
            // stick these in our db
            $this->_add_results($_POST['results']);
        }
        else
        {
            // bad call
            throw new UnexpectedValueException('Some $_POST requests seem to be missing');
        }
    }
}

// get our config
require_once 'config.php';

// init it
new API();