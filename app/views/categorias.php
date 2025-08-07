<?php

$agrupadas = [
    'despesa' => [],
    'receita' => [],
];
foreach ($categorias as $cat) {
    $agrupadas[$cat['tipo']][] = $cat;
}
function renderIconClass($classe)
{
    if (str_starts_with($classe, 'ph-')) {
        return "ph $classe"; // Phosphor
    } elseif (str_starts_with($classe, 'fa-')) {
        return "fa $classe"; // Font Awesome
    } else {
        return $classe; // fallback
    }
}
?>

<!-- Estilo exclusivo da página -->
<link rel="stylesheet" href="/assets/css/views/categorias.css">

<div class="container">
    <div class="topo">
        <img src="/assets/img/mascote.png" alt="">

        <div class="botoes-topo">
            <a href="/categorias/nova" class="btn-padrao">
                <i class="fa fa-plus-circle"></i> Adicionar Categoria
            </a>
            <a href="/subcategorias/nova" class="btn-padrao">
                <i class="fa fa-plus-square"></i> Adicionar Subcategoria
            </a>
        </div>
    </div>

    <?php foreach (['despesa' => 'Categorias de Despesas', 'receita' => 'Categorias de Receitas'] as $tipo => $titulo): ?>
        <section class="bloco-categorias">
            <h2 class="titulo-categorias"><?= $titulo ?></h2>
            <table class="tabela-categorias">
                <thead>
                    <tr>
                        <th>Ícone</th>
                        <th>Categoria</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($agrupadas[$tipo] as $categoria): ?>
                        <!-- Categoria -->
                        <tr class="linha-categoria" data-id="<?= $categoria['id_categoria'] ?>">
                            <td>
                                <div class="icones-categoria">
                                    <button class="toggle-subcategorias" data-id="<?= $categoria['id_categoria'] ?>">
                                        <i class="ph ph-caret-right"></i>
                                    </button>
                                    <i class="<?= renderIconClass($categoria['icone']) ?>"></i>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($categoria['nome_categoria']) ?></td>
                            <td><?= htmlspecialchars($categoria['descricao']) ?></td>
                            <td>
                                <span class="status <?= $categoria['ativa'] ? 'ativo' : 'arquivada' ?>">
                                    <?= $categoria['ativa'] ? 'Ativa' : 'Arquivada' ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-editar">✏️</button>
                                <?php if ($categoria['ativa']): ?>
                                    <form method="POST" action="/categorias/excluir" style="display:inline">
                                        <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
                                        <button class="btn-arquivar" type="submit" title="Arquivar">🗃️</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn-ativar">✅</button>
                                <?php endif; ?>
                            </td>
                        </tr>


                        <!-- Subcategorias -->
                        <?php foreach ($categoria['subcategorias'] as $sub): ?>
                            <tr class="linha-subcategoria subcat-<?= $categoria['id_categoria'] ?>" style="display: none;">
                                <td></td>
                                <td>— <?= htmlspecialchars($sub['nome_subcategoria']) ?></td>
                                <td><?= htmlspecialchars($sub['descricao'] ?? '') ?></td>
                                <td>
                                    <span class="status <?= $sub['ativa'] ? 'ativo' : 'arquivada' ?>">
                                        <?= $sub['ativa'] ? 'Ativa' : 'Arquivada' ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-editar">✏️</button>
                                    <?php if ($sub['ativa']): ?>
                                        <button class="btn-arquivar">🗃️</button>
                                    <?php else: ?>
                                        <button class="btn-ativar">✅</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endforeach; ?>
</div>

<script src="/assets/js/views/categorias.js"></script>