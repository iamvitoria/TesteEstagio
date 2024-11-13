document.addEventListener('DOMContentLoaded', function () {

    const BASE_URL = 'http://localhost/TesteEstagio/public/index.php?action=tasks';

    function fetchTasks() {
        fetch(BASE_URL)
            .then(response => response.json())
            .then(data => renderTasks(data))
            .catch(error => {
                console.error('Erro ao buscar tarefas:', error);
                document.getElementById('taskList').innerHTML = '<tr><td colspan="5">Erro ao carregar tarefas.</td></tr>';
            });
    }

    function formatCurrency(value) {
        return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return `${String(date.getDate()).padStart(2, '0')}/${String(date.getMonth() + 1).padStart(2, '0')}/${date.getFullYear()}`;
    }

    function renderTasks(tasks) {
        const taskList = document.getElementById('taskList');
        taskList.innerHTML = '';

        tasks.forEach(task => {
            const row = document.createElement('tr');
            row.setAttribute('data-task-id', task.id);
            if (task.custo >= 1000) row.classList.add('highlight');

            row.innerHTML = `
                <td>${task.id}</td>
                <td>${task.nome}</td>
                <td>${formatCurrency(task.custo)}</td>
                <td>${formatDate(task.dataLimite)}</td>
                <td>
                    <button onclick="editTask(${task.id})" class="action-btn"><i class="fas fa-pencil-alt"></i></button>
                    <button onclick="deleteTask(${task.id})" class="action-btn"><i class="fas fa-trash"></i></button>
                </td>
                <td>
                    <button onclick="reorderTask(${task.id}, 'up')">&#9650;</button>
                    <button onclick="reorderTask(${task.id}, 'down')">&#9660;</button>
                </td>
            `;
            taskList.appendChild(row);
        });
    }

    window.editTask = function (taskId) {
        selectedTaskId = taskId;
        fetch(`${BASE_URL}&id=${taskId}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editNome').value = data.nome;
                document.getElementById('editCusto').value = data.custo;
                document.getElementById('editDataLimite').value = data.dataLimite;
                document.getElementById('editPopup').style.display = 'block';
            })
            .catch(error => {
                console.error('Erro ao buscar tarefa para edição:', error);
                alert('Erro ao carregar os dados da tarefa.');
            });
    };

    document.getElementById('updateTaskButton').addEventListener('click', function () {
        const nome = document.getElementById('editNome').value;
        const custo = parseFloat(document.getElementById('editCusto').value);
        const dataLimite = document.getElementById('editDataLimite').value;

        if (!nome || isNaN(custo) || !dataLimite) {
            alert('Por favor, preencha todos os campos corretamente.');
            return;
        }

        fetch(`${BASE_URL}&id=${selectedTaskId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nome, custo, data_limite: dataLimite })
        })
        .then(response => response.ok ? response.json() : response.json().then(data => Promise.reject(data.message)))
        .then(() => {
            fetchTasks();
            document.getElementById('editPopup').style.display = 'none';
        })
        .catch(error => {
            console.error('Erro ao atualizar tarefa:', error);
            alert(error || 'Erro ao atualizar tarefa.');
        });
    });

    window.deleteTask = function (taskId) {
        if (confirm('Você tem certeza de que deseja excluir esta tarefa?')) {
            fetch(`${BASE_URL}&id=${taskId}`, { method: 'DELETE' })
                .then(response => response.json())
                .then(() => fetchTasks())
                .catch(error => {
                    console.error('Erro ao excluir tarefa:', error);
                    alert('Erro ao excluir tarefa.');
                });
        }
    };

    window.reorderTask = function (taskId, direction) {
        fetch(`${BASE_URL}&action=reorder&id=${taskId}&direction=${direction}`, { method: 'POST' })
            .then(response => {
                // Verifique se a resposta é válida
                if (!response.ok) {
                    return response.json().then(data => Promise.reject(data.message || 'Erro desconhecido'));
                }
                return response.json();
            })
            .then(data => {
                // Verifique se o status de sucesso foi retornado
                if (data.status === 'success') {
                    fetchTasks(); // Atualizar as tarefas na tela
                } else {
                    throw new Error(data.message || 'Erro ao reordenar tarefa.');
                }
            })
            .catch(error => {
                console.error('Erro ao reordenar tarefa:', error);
                alert(error || 'Erro ao reordenar tarefa.');
            });
    };    

    fetchTasks();
});
