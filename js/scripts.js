// Constantes
const API_URL = "http://api.cdcgyn.com/api";

// Adicionar novo campo de contato
function addContact() {
  const container = document.getElementById("contactsContainer");
  const div = document.createElement("div");
  div.className = "contact-group";
  div.innerHTML = `
        <select name="tipo[]">
            <option value="telefone">Telefone</option>
            <option value="email">Email</option>
            <option value="whatsapp">WhatsApp</option>
        </select>
        <input type="text" name="valor[]" placeholder="Valor">
        <input type="hidden" name="contactId[]" value="">
    `;
  container.appendChild(div);
}

// Exibir mensagem
function showMessage(text, isError = false) {
  const messageDiv = document.getElementById("message");
  messageDiv.textContent = text;
  messageDiv.className = "message " + (isError ? "error" : "success");
  messageDiv.style.display = "block";
  setTimeout(() => {
    messageDiv.style.display = "none";
  }, 3000);
}

// Limpar formulário
function resetForm() {
  document.getElementById("personForm").reset();
  const container = document.getElementById("contactsContainer");
  container.innerHTML = `
        <div class="contact-group">
            <select name="tipo[]">
                <option value="telefone">Telefone</option>
                <option value="email">Email</option>
                <option value="whatsapp">WhatsApp</option>
            </select>
            <input type="text" name="valor[]" placeholder="Valor">
            <input type="hidden" name="contactId[]" value="">
        </div>
    `;
  document.getElementById("editId").value = "";
  document.getElementById("cancelBtn").style.display = "none";
}

// Atualizar a lista de pessoas
function updatePersonList() {
  fetch(`${API_URL}/pessoas`)
    .then((response) => response.json())
    .then((data) => {
      console.log("Lista inicial:", data);
      const list = document.getElementById("personList");
      list.innerHTML = "";
      if (data && Array.isArray(data) && data.length > 0) {
        Promise.all(
          data.map((person) =>
            fetch(`${API_URL}/pessoas/${person.id}`)
              .then((res) => res.json())
              .catch((err) => {
                console.error(`Erro ao buscar pessoa ${person.id}:`, err);
                return person;
              })
          )
        )
          .then((persons) => {
            persons.forEach((person) => {
              const li = document.createElement("li");
              li.className = "person-item";
              li.dataset.id = person.id;
              const personName =
                person.nome || person.name || "Nome não disponível";
              const contacts = person.contatos || person.contacts || [];
              li.innerHTML = `
                            <h3>${personName}</h3>
                            <ul class="contact-list">
                                ${
                                  contacts.length > 0
                                    ? contacts
                                        .map(
                                          (contact) => `
                                    <li class="contact-item" data-id="${contact.id}">
                                        <span>${contact.tipo}: ${contact.valor}</span>
                                        <button class="delete-btn" data-contact-id="${contact.id}">Excluir</button>
                                    </li>
                                `
                                        )
                                        .join("")
                                    : "<li>Sem contatos</li>"
                                }
                            </ul>
                            <div style="margin-top: 0.5rem;">
                                <button class="edit-btn" data-id="${
                                  person.id
                                }" data-nome="${personName.replace(
                /'/g,
                "\\'"
              )}">Editar</button>
                                <button class="delete-btn" data-person-id="${
                                  person.id
                                }">Excluir</button>
                            </div>
                        `;
              list.appendChild(li);
            });
            attachEventListeners();
          })
          .catch((error) => {
            console.error("Erro ao carregar detalhes:", error);
            showMessage("Erro ao carregar detalhes das pessoas", true);
          });
      } else {
        list.innerHTML = "<li>Nenhuma pessoa cadastrada.</li>";
      }
    })
    .catch((error) => {
      console.error("Erro ao buscar lista:", error);
      showMessage("Erro ao carregar pessoas", true);
    });
}

// Editar pessoa
function editPerson(id, nome) {
  fetch(`${API_URL}/pessoas/${id}`)
    .then((response) => response.json())
    .then((person) => {
      if (person && !person.erro) {
        const personName = person.nome || person.name || "";
        document.getElementById("nome").value = personName;
        document.getElementById("editId").value = id;
        const container = document.getElementById("contactsContainer");
        container.innerHTML = "";
        const contacts = person.contatos || person.contacts || [];

        if (contacts && contacts.length > 0) {
          contacts.forEach((contact) => {
            const contactType = contact.tipo || contact.type || "telefone";
            const contactValue = contact.valor || contact.value || "";
            const contactId = contact.id || "";
            const div = document.createElement("div");
            div.className = "contact-group";
            div.innerHTML = `
                            <select name="tipo[]">
                                <option value="telefone" ${
                                  contactType === "telefone" ||
                                  contactType === "phone"
                                    ? "selected"
                                    : ""
                                }>Telefone</option>
                                <option value="email" ${
                                  contactType === "email" ? "selected" : ""
                                }>Email</option>
                                <option value="whatsapp" ${
                                  contactType === "whatsapp" ? "selected" : ""
                                }>WhatsApp</option>
                            </select>
                            <input type="text" name="valor[]" value="${contactValue}">
                            <input type="hidden" name="contactId[]" value="${contactId}">
                        `;
            container.appendChild(div);
          });
        } else {
          addContact();
        }
        document.getElementById("cancelBtn").style.display = "inline-block"; // Mostra o botão Cancelar
      } else {
        showMessage("Erro ao carregar dados da pessoa", true);
      }
    })
    .catch(() => showMessage("Erro ao buscar pessoa para edição", true));
}

// Excluir pessoa
function deletePerson(id) {
  if (confirm("Tem certeza que deseja excluir esta pessoa?")) {
    fetch(`${API_URL}/pessoas/${id}`, { method: "DELETE" })
      .then((response) => response.json())
      .then((result) => {
        if (result.mensagem) {
          showMessage(result.mensagem);
          updatePersonList();
        } else {
          showMessage(result.erro || "Erro ao excluir pessoa", true);
        }
      })
      .catch(() => showMessage("Erro ao excluir pessoa", true));
  }
}

// Excluir contato
function deleteContact(id) {
  if (confirm("Tem certeza que deseja excluir este contato?")) {
    fetch(`${API_URL}/contatos/${id}`, { method: "DELETE" })
      .then((response) => response.json())
      .then((result) => {
        if (result.mensagem) {
          showMessage(result.mensagem);
          updatePersonList();
        } else {
          showMessage(result.erro || "Erro ao excluir contato", true);
        }
      })
      .catch(() => showMessage("Erro ao excluir contato", true));
  }
}

// Anexar eventos dinamicamente
function attachEventListeners() {
  document.querySelectorAll(".edit-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id;
      const nome = btn.dataset.nome;
      editPerson(id, nome);
    });
  });

  document.querySelectorAll(".delete-btn[data-person-id]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.personId;
      deletePerson(id);
    });
  });

  document.querySelectorAll(".delete-btn[data-contact-id]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.contactId;
      deleteContact(id);
    });
  });
}

// Inicialização
document.addEventListener("DOMContentLoaded", () => {
  document
    .getElementById("addContactBtn")
    .addEventListener("click", addContact);

  document.getElementById("newBtn").addEventListener("click", resetForm);

  document.getElementById("cancelBtn").addEventListener("click", resetForm);

  document
    .getElementById("personForm")
    .addEventListener("submit", function (e) {
      e.preventDefault();
      const nome = document.getElementById("nome").value;
      const editId = document.getElementById("editId").value;
      const contatos = [];
      const tipos = document.getElementsByName("tipo[]");
      const valores = document.getElementsByName("valor[]");
      const contactIds = document.getElementsByName("contactId[]");

      for (let i = 0; i < tipos.length; i++) {
        if (valores[i].value) {
          const contact = {
            tipo: tipos[i].value,
            valor: valores[i].value,
          };
          if (contactIds[i].value) {
            contact.id = contactIds[i].value;
          }
          contatos.push(contact);
        }
      }

      const data = { nome, contatos };
      const method = editId ? "PUT" : "POST";
      const url = editId
        ? `${API_URL}/pessoas/${editId}`
        : `${API_URL}/pessoas`;

      fetch(url, {
        method: method,
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      })
        .then((response) => response.json())
        .then((result) => {
          if (result.mensagem) {
            showMessage(result.mensagem);
            updatePersonList();
            resetForm(); // Limpa o formulário após salvar
          } else {
            showMessage(result.erro || "Erro ao salvar pessoa", true);
          }
        })
        .catch(() => showMessage("Erro ao salvar pessoa", true));
    });

  attachEventListeners();
  updatePersonList();
});
