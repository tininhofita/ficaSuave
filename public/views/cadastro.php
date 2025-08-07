<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fica Suave - Cadastro</title>
    <link rel="stylesheet" href="../assets/css/cadastro.css">
    <!-- Ícones -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>

<body>
    <main>

        <header class="header">
            <div class="container">
                <div class="logo">
                    <img src="../assets/img/escrita3.png" alt="Fica Suave">
                </div>
                <nav>
                    <a href="/login" class="btn-header btn-padrao">Iniciar Sessão</a>
                </nav>
            </div>
        </header>

        <section class="info">
            <div>
                <h1>Fica Suave</h1>
                <p>Chega de perrengue no fim do mês!</p>
                <p>Com o <strong>Fica Suave</strong>, você cuida da sua grana sem complicação.</p>
                <p>Organiza suas contas, planeja seus gastos e ainda descobre onde economizar.</p>
                <p><strong>Tá no controle, tá suave!</strong></p>
            </div>
            <div class="mascote">
                <img src="../assets/img/mascote.png" alt="Mascote Fica Suave">
            </div>
        </section>

        <section>
            <form id="cadastro-form" method="POST">

                <fieldset class="dados-pessoais">
                    <legend>Dados Pessoais</legend>
                    <div class="box-input">
                        <div class="input-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" id="nome" name="nome" placeholder="O nome da lenda!" required>
                        </div>

                        <div class="input-group">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" placeholder="Seu melhor e-mail" required>
                        </div>
                    </div>

                    <div class="box-input">
                        <div class="input-group">
                            <label for="senha">Senha</label>
                            <input type="password" id="senha" name="senha" placeholder="Cria uma senha segura" required minlength="8">
                        </div>

                        <div class="input-group">
                            <label for="confirmarSenha">Confirmar Senha</label>
                            <input type="password" id="confirmarSenha" name="confirmarSenha" placeholder="Confirma ela aqui" required minlength="8">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="endereco">
                    <legend>Endereço</legend>

                    <div class="input-group">
                        <label for="cep">CEP</label>
                        <input type="text" id="cep" name="cep" placeholder="Digite seu CEP" required>
                        <button type="button" id="buscarEndereco">🔎 Buscar</button>
                    </div>

                    <div class="box-input">
                        <div class="input-group">
                            <label for="rua">Rua</label>
                            <input type="text" id="rua" name="rua" placeholder="Nome da rua" required>
                        </div>

                        <div class="input-group">
                            <label for="cidade">Cidade</label>
                            <input type="text" id="cidade" name="cidade" placeholder="Cidade" required>
                        </div>
                    </div>

                    <div class="box-input">
                        <div class="input-group">
                            <label for="estado">Estado</label>
                            <input type="text" id="estado" name="estado" placeholder="Estado" required>
                        </div>

                        <div class="input-group">
                            <label for="uf">UF</label>
                            <input type="text" id="uf" name="uf" placeholder="UF" required>
                        </div>

                        <div class="input-group">
                            <label for="pais">País</label>
                            <input type="text" id="pais" name="pais" placeholder="País" value="Brasil" required>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="renda">
                    <legend>Renda</legend>
                    <label for="renda">Faixa de renda (opcional)</label>
                    <select id="renda" name="renda">
                        <option value="">Selecione...</option>
                        <option value="ate_2k">Até R$2.000</option>
                        <option value="2k_5k">De R$2.001 até R$5.000</option>
                        <option value="5k_10k">De R$5.001 até R$10.000</option>
                        <option value="10k_acima">Acima de R$10.000</option>
                    </select>
                </fieldset>
                <button type="submit" class="btn-padrao">🔒 Começar minha jornada financeira</button>
            </form>
        </section>

    </main>

    <footer>
        <p class="login-link">Já tem conta? <a href="/login">Faça login</a></p>
        <p>&copy; 2024 Fica Suave. Todos os direitos reservados.</p>
        <p>
            Desenvolvido com
            <i class="ph ph-heart"></i> e
            <i class="ph ph-coffee"></i>
            e muita visão por
            <a href="#" target="_blank"> Tininho Fita</a>
        </p>
    </footer>


    <script src="../assets/js/cadastro.js"></script>
    <script src="../assets/js/buscar-cep.js"></script>
</body>

</html>