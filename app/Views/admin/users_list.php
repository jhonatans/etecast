<?php require_once BASE_PATH . '/app/Views/partials/header.php'; ?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Gerenciar Usuários</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            Novo Usuário
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nome</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['matricula']); ?></td>
                        <td><?php echo htmlspecialchars($user['nome']); ?></td>
                        <td>
                            <span class="badge <?php 
                                echo match($user['role']) {
                                    'professor' => 'bg-success',
                                    'special' => 'bg-warning text-dark',
                                    default => 'bg-secondary'
                                };
                            ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="/admin/users/delete/<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Tem certeza?');">Excluir</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/admin/users/add" method="POST">
      <div class="modal-header">
        <h5 class="modal-title">Adicionar Usuário</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label>Nome Completo</label>
            <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Matrícula (Login)</label>
            <input type="text" name="matricula" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Data Nascimento (Senha Inicial)</label>
            <input type="date" name="data_nascimento" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Tipo de Usuário</label>
            <select name="role" class="form-select">
                <option value="student">Estudante</option>
                <option value="professor">Professor</option>
                <option value="special">Estudante Especial</option>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>
      </form>
    </div>
  </div>
</div>

<?php require_once BASE_PATH . '/app/Views/partials/footer.php'; ?>