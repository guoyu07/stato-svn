<?php

class ActiveStoreTest extends ActiveTestCase
{
    public $fixtures = array('companies');
    
    function testFind()
    {
        $companies = ActiveStore::findAll('Company');
        $this->assertEqual(2, count($companies));
    }
    
    function testFindWithCondition()
    {
        $companies = ActiveStore::findAll('Company', "name = 'Groupe W'");
        $this->assertEqual(1, count($companies));
    }
    
    function testReplaceBindVariables()
    {
        $stmt = 'SELECT * FROM test WHERE name = ? AND profession = ?';
        $values = array('test1', 'test2');
        foreach ($values as $value) $stmt = preg_replace('/\?/i', $value, $stmt, 1);
        $this->assertEqual('SELECT * FROM test WHERE name = test1 AND profession = test2', $stmt);
    }
    
    function testReplaceNamedBindVariables()
    {
        $stmt = 'SELECT * FROM test WHERE name = :test1 AND profession = :test2';
        $values = array(':test1' => 'test1', ':test2' => 'test2');
        foreach ($values as $key => $value) $stmt = preg_replace('/'.$key.'/i', $value, $stmt, 1);
        $this->assertEqual('SELECT * FROM test WHERE name = test1 AND profession = test2', $stmt);
    }
}

?>
