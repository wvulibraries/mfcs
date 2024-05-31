<?php
require_once "/home/mfcs.lib.wvu.edu/public_html/public_html/includes/classes/mfcs.php";
require_once "/home/mfcs.lib.wvu.edu/public_html/public_html/includes/classes/users.php";
require_once '/home/mfcs.lib.wvu.edu/public_html/public_html/includes/classes/log.php'; // Include the log class file

// Mock errorHandle class
// class errorHandle
// {
//     public static function newError($message, $level)
//     {
//         // Simulate error handling (you can modify this as needed for testing purposes)
//         echo "Error: $message\n";
//     }
// }

class LogTest extends PHPUnit\Framework\TestCase
{
    public function testInsert()
    {
        // Mock the database connection and error handling
        $dbMock = $this->getMockBuilder('stdClass')
            ->setMethods(['escape', 'query'])
            ->getMock();
        $dbMock->expects($this->any())
            ->method('escape')
            ->willReturnCallback(function ($value) {
                return addslashes($value); // Simulate escaping
            });
        $dbMock->expects($this->any())
            ->method('query')
            ->willReturn(['result' => true]); // Simulate successful query

        mfcs::$engine = new stdClass(); // Mock mfcs::$engine
        mfcs::$engine->openDB = $dbMock;

        // Mock $_SERVER['REMOTE_ADDR']
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        // Test insert with valid parameters
        $this->assertTrue(log::insert('action'));

        // Test insert with empty action (should return false)
        $this->assertFalse(log::insert(''));
    }

    public function testPullActions()
    {
        // Mock the database connection
        $dbMock = $this->getMockBuilder('stdClass')
            ->setMethods(['escape', 'query'])
            ->getMock();
        $dbMock->expects($this->any())
            ->method('escape')
            ->willReturnCallback(function ($value) {
                return addslashes($value); // Simulate escaping
            });
    
        // Create a mock result object that mimics a MySQL result resource
        $mockResult = new stdClass();
        $mockResult->num_rows = 2; // Simulate num_rows property
        $mockRows = [
            ['username' => 'user1', 'date' => time()],
            ['username' => 'user2', 'date' => time() + 3600], // Add more rows if needed
        ];
        $currentIndex = 0;
    
        $mockResult->fetch_array = function () use (&$mockRows, &$currentIndex) {
            if ($currentIndex < count($mockRows)) {
                return $mockRows[$currentIndex++];
            }
            return null; // Return null when all rows are fetched
        };
    
        // Mock the query method to return the mock result
        $dbMock->expects($this->any())
            ->method('query')
            ->willReturn(['result' => true, 'result' => $mockResult]);
    
        mfcs::$engine = new stdClass(); // Mock mfcs::$engine
        mfcs::$engine->openDB = $dbMock;
    
        // Test pull_actions with valid actions and objectID
        $actions = ['action1', 'action2'];
        $objectID = 123;
        $blame = log::pull_actions($actions, $objectID);
    
        // Check that $blame is not empty and contains expected data
        $this->assertNotEmpty($blame);
        $this->assertEquals('user1', $blame[0][0]);
        // Add more assertions based on your expected data structure
    }     
    
}