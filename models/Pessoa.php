<?php
class Pessoa
{
    private $conn;
    private $tabela = "pessoas";

    public $id;
    public $nome;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function criar()
    {
        $query = "INSERT INTO " . $this->tabela . " (nome) VALUES (:nome)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nome", $this->nome);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function ler($id = null)
    {
        $query = "SELECT * FROM " . $this->tabela;
        $query .= $id ? " WHERE id = :id" : "";
        $stmt = $this->conn->prepare($query);

        if ($id) $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $id ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function atualizar()
    {
        $query = "UPDATE " . $this->tabela . " SET nome = :nome WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function excluir()
    {
        $query = "DELETE FROM " . $this->tabela . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }
}
