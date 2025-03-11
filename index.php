<?php
// Configuração inicial
$api_url = 'http://api.cdcgyn.com/api';

function fetchPersons()
{
    global $api_url;
    $ch = curl_init("$api_url/pessoas");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$persons = fetchPersons();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Contatos</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <header>
        <h1>Gerenciador de Contatos</h1>
    </header>
    <div class="container">
        <div class="form-section">
            <h2>Adicionar/Editar Pessoa</h2>
            <form id="personForm">
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" required>
                </div>
                <div id="contactsContainer">
                    <div class="contact-group">
                        <select name="tipo[]">
                            <option value="telefone">Telefone</option>
                            <option value="email">Email</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                        <input type="text" name="valor[]" placeholder="Valor">
                        <input type="hidden" name="contactId[]" value="">
                    </div>
                </div>
                <button type="button" class="add-contact" id="addContactBtn">+ Adicionar Contato</button>
                <div class="form-buttons">
                    <button type="submit">Salvar</button>
                    <button type="button" id="newBtn" class="new-btn">Novo</button>
                    <button type="button" id="cancelBtn" class="cancel-btn" style="display: none;">Cancelar</button>
                </div>
                <input type="hidden" id="editId" name="editId">
            </form>
            <div id="message" class="message" style="display: none;"></div>
        </div>
        <div class="list-section">
            <h2>Lista de Pessoas</h2>
            <div class="person-list">
                <ul id="personList">
                    <?php if ($persons && !isset($persons['erro'])): ?>
                        <?php foreach ($persons as $person): ?>
                            <li class="person-item" data-id="<?php echo $person['id']; ?>">
                                <h3><?php echo htmlspecialchars($person['nome']); ?></h3>
                                <ul class="contact-list">
                                    <li>Contatos não disponíveis nesta lista</li>
                                </ul>
                                <div style="margin-top: 0.5rem;">
                                    <button class="edit-btn" data-id="<?php echo $person['id']; ?>" data-nome="<?php echo htmlspecialchars(addslashes($person['nome'])); ?>">Editar</button>
                                    <button class="delete-btn" data-person-id="<?php echo $person['id']; ?>">Excluir</button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Nenhuma pessoa cadastrada.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <script src="js/scripts.js"></script>
</body>

</html>