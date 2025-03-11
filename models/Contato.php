<?php
class Contato
{
    private $conn;
    private $tabela = "contatos";

    public $id;
    public $pessoa_id;
    public $tipo;
    public $valor;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function criar()
    {
        $query = "INSERT INTO " . $this->tabela . " (pessoa_id, tipo, valor) 
                 VALUES (:pessoa_id, :tipo, :valor)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pessoa_id", $this->pessoa_id);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":valor", $this->valor);

        return $stmt->execute();
    }

    public function ler($pessoa_id)
    {
        $query = "SELECT * FROM " . $this->tabela . " WHERE pessoa_id = :pessoa_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pessoa_id", $pessoa_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function excluir($id)
    {
        $query = "DELETE FROM " . $this->tabela . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    // Novo método para atualizar um contato existente
    public function atualizar()
    {
        $query = "UPDATE " . $this->tabela . " SET tipo = :tipo, valor = :valor WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":tipo", $this->tipo);
        $stmt->bindParam(":valor", $this->valor);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
