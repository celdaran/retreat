<?php namespace Service\Data;

class Database
{
    private $dbh;

    public function __construct()
    {

    }

    public function connect(string $host, string $username, ?string $password, string $database)
    {
        try {
            $this->dbh = new \PDO("mysql:host=$host;dbname=$database", $username, $password);
        } catch (PDOException $e) {
            print "Error connecting to database: " . $e->getMessage() . "\n";
            die();
        }
    }

    public function select(string $query)
    {
        $rows = [];

        foreach ($this->dbh->query($query) as $row) {
            $rows[] = $row;
        }

        return $rows;
    }


}
