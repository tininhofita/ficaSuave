<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Controle suas finanças de forma simples e prática.">
    <title>Login - Fica Suave!</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="icon" type="image/x-icon" href="../assets/img/fav_tininho.ico">
    <!-- Ícones -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>


<body>
    <main>
        <div class="container">
            <section class="boas-vindas">
                <div class="box-imgs">
                    <img src="../assets/img/mascote1.png" alt="Mascote Fica Suave" class="logo img-fluid">
                    <img src="../assets/img/escrita1.png" alt="Escrita do Fica Suave" class="img-fluid">
                </div>
                <div class="caixa-texto">
                    <h2>O controle da sua grana tá na mão!</h2>
                    <p>Chega de perrengue no fim do mês! Com o Fica Suave, você vai cuidar da sua grana sem complicação.
                        Organiza suas contas, faz seu planejamento e vê onde dá pra economizar para chegar longe! Bora
                        fazer
                        sua
                        grana render e dar aquele passo no futuro. Tá no controle, tá suave!
                    </p>
                </div>
            </section>

            <section class="login">
                <form id="login-form" novalidate>
                    <div class="input-group">
                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" placeholder="E-mail" required autocomplete="email">
                    </div>
                    <div class="input-group">
                        <label for="password">Senha:</label>
                        <input type="password" id="password" name="password" placeholder="Senha" required
                            autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn-padrao">Entrar</button>
                    <div id="login-error" class="erro-login" style="display: none;"></div>

                </form>
                <div class="caixa-cadastro">
                    <p>Ainda não tem uma conta? cola aqui e bora cuidar da grana.</p>
                    <a href="/cadastro">Criar minha conta e ficar suave</a>
                </div>
            </section>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2024 Fica Suave. Todos os direitos reservados.</p>
        <p>
            Desenvolvido com
            <i class="ph ph-heart"></i> e
            <i class="ph ph-coffee"></i>
            e muita visão por
            <a href="#" target="_blank"> Tininho Fita</a>
        </p>
    </footer>

    <script src="../assets/js/login.js"></script>
</body>

</html>