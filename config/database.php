<?php
class Database
{
    private $host = "127.0.0.1";
    private $db_name = "cdcgyn86_agenda";
    private $username = "cdcgyn86_admin";
    private $password = "Sgg@020517";
    public $conn;



    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
