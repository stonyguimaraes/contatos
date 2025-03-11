<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Origin: *");

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once '../config/database.php';
require_once '../models/Pessoa.php';
require_once '../models/Contato.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $pessoa = new Pessoa($conn);
    $contato = new Contato($conn);

    $request_method = $_SERVER["REQUEST_METHOD"];


    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $uri = explode('/', trim($path_info, '/'));


    switch ($request_method) {
        case 'GET':

            if ($uri[0] === 'pessoas') {
                if (isset($uri[1])) {
                    $result = $pessoa->ler($uri[1]);
                    $result['contatos'] = $contato->ler($uri[1]);
                } else {
                    $result = $pessoa->ler();
                }
                echo json_encode($result);
            }
            break;

        case 'POST':

            $data = json_decode(file_get_contents("php://input"));

            if ($uri[0] === 'pessoas') {

                if (!$data || !isset($data->nome)) {
                    echo json_encode(["erro" => "Dados inválidos"]);
                    exit;
                }
                $pessoa->nome = $data->nome;
                $pessoa_id = $pessoa->criar();

                if ($pessoa_id) {
                    if (isset($data->contatos)) {
                        foreach ($data->contatos as $c) {
                            $contato->pessoa_id = $pessoa_id;
                            $contato->tipo = $c->tipo;
                            $contato->valor = $c->valor;
                            $contato->criar();
                        }
                    }
                    $response = json_encode(["mensagem" => "Pessoa criada", "id" => $pessoa_id]);
                    echo $response;
                    exit;
                } else {
                    echo json_encode(["erro" => "Falha ao criar pessoa"]);
                    exit;
                }
            } else {
                echo json_encode(["erro" => "Rota inválida"]);
                exit;
            }
            break;

        case 'PUT':
            file_put_contents('log.txt', "Entrou no PUT\n", FILE_APPEND);
            $data = json_decode(file_get_contents("php://input"));
            file_put_contents('log.txt', "Dados recebidos: " . print_r($data, true) . "\n", FILE_APPEND);

            if ($uri[0] === 'pessoas' && isset($uri[1])) {
                file_put_contents('log.txt', "Tentando atualizar pessoa ID " . $uri[1] . "\n", FILE_APPEND);
                if (!$data || !isset($data->nome)) {
                    echo json_encode(["erro" => "Dados inválidos"]);
                    file_put_contents('log.txt', "Erro: Dados inválidos\n", FILE_APPEND);
                    exit;
                }
                $pessoa->id = $uri[1];
                $pessoa->nome = $data->nome;
                if ($pessoa->atualizar()) {
                    // Atualizar ou adicionar contatos
                    if (isset($data->contatos)) {
                        // Opcional: Excluir contatos existentes antes de atualizar (se desejar sobrescrever)
                        // $contato->excluirPorPessoa($uri[1]);

                        foreach ($data->contatos as $c) {
                            $contato->pessoa_id = $uri[1];
                            $contato->tipo = $c->tipo;
                            $contato->valor = $c->valor;
                            if (isset($c->id) && !empty($c->id)) {
                                // Atualizar contato existente
                                $contato->id = $c->id;
                                $contato->atualizar();
                                file_put_contents('log.txt', "Contato atualizado: ID " . $c->id . "\n", FILE_APPEND);
                            } else {
                                // Criar novo contato
                                $contato->criar();
                                file_put_contents('log.txt', "Novo contato criado: " . $c->tipo . " - " . $c->valor . "\n", FILE_APPEND);
                            }
                        }
                    }
                    echo json_encode(["mensagem" => "Pessoa atualizada"]);
                    file_put_contents('log.txt', "Sucesso: Pessoa atualizada ID " . $uri[1] . "\n", FILE_APPEND);
                } else {
                    echo json_encode(["erro" => "Falha ao atualizar pessoa"]);
                    file_put_contents('log.txt', "Erro: Falha ao atualizar pessoa ID " . $uri[1] . "\n", FILE_APPEND);
                }
            } else {
                echo json_encode(["erro" => "Rota inválida para PUT"]);
                file_put_contents('log.txt', "Erro: Rota inválida para PUT\n", FILE_APPEND);
            }
            exit;
            break;

        case 'DELETE':
            file_put_contents('log.txt', "Entrou no DELETE\n", FILE_APPEND);
            if ($uri[0] === 'pessoas' && isset($uri[1])) {

                $pessoa->id = $uri[1];
                if ($pessoa->excluir()) {
                    echo json_encode(["mensagem" => "Pessoa excluída"]);
                } else {
                    echo json_encode(["erro" => "Falha ao excluir pessoa"]);
                }
            } elseif ($uri[0] === 'contatos' && isset($uri[1])) {
                if ($contato->excluir($uri[1])) {
                    echo json_encode(["mensagem" => "Contato excluído"]);
                } else {
                    echo json_encode(["erro" => "Falha ao excluir contato"]);
                }
            } else {
                echo json_encode(["erro" => "Rota inválida para DELETE"]);
            }
            exit;
            break;
    }
} catch (Exception $e) {
    echo json_encode(["erro" => "Erro no servidor: " . $e->getMessage()]);
}
