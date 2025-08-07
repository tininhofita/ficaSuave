# 💸 Fica Suave — Controle Financeiro Pessoal de Quebrada

O **Fica Suave** é um sistema web de controle financeiro pessoal com foco em organização,
clareza visual e facilidade de uso. Com ele, você pode registrar receitas, despesas, contas bancárias,
cartões, categorias e muito mais — tudo dentro de uma estrutura MVC em PHP nativo.

---

## 📁 Estrutura do Projeto

```
/ficasuave/
├── app/
│   ├── config/              # Configurações gerais e do banco
│   ├── controllers/         # Lógica dos controllers (Auth, Cadastro etc.)
│   ├── helpers/             # Helpers como AuthHelper e logger
│   ├── layouts/             # Cabeçalho, rodapé, nav e layout base
│   ├── lib/                 # Biblioteca do roteador
│   ├── models/              # Regras de negócio e acesso ao banco
│   ├── routers/             # Arquivo de rotas da aplicação
│   └── views/               # Views (telas) da aplicação
├── public/
│   ├── assets/              # CSS, JS e imagens
│   ├── views/               # Páginas públicas (login, cadastro, boas-vindas)
│   └── index.php            # Ponto de entrada da aplicação
├── tests/                   # Scripts de teste (ex: conexão com DB)
└── fiveserver.config.js     # Config local para live-reload (opcional)
```

---

## 🧠 Principais Funcionalidades

- 📌 Cadastro e login com autenticação segura (`AuthHelper`, `password_hash`)
- 📥 Cadastro de receitas e despesas, com suporte a:
  - Parcelamento
  - Recorrência
  - Anexos
  - Status (pago, pendente, previsto, etc.)
- 🏦 Gerenciamento de contas bancárias e cartões de crédito
- 🧾 Categorias e subcategorias customizáveis (com ícones)
- 💳 Controle de formas de transação (PIX, dinheiro, cartão, etc.)
- 📊 Painel com saldos e visão geral do mês
- 📂 Logs de atividade do usuário
- 🌐 Sistema de rotas amigáveis

---

## 🗃️ Banco de Dados

O projeto possui scripts SQL para popular todas as tabelas:

- `usuarios.sql`
- `categorias.sql`
- `subcategorias.sql`
- `receitas.sql`
- `despesa.sql`
- `cartoes.sql`
- `contas_bancarias.sql`
- `formas_transacao.sql`

Exemplo de campos personalizados:

```sql
Table "despesas" {
  valor_pago decimal(10,2)
  juros decimal(10,2)
  desconto decimal(10,2)
  parcelado boolean
  recorrente boolean
  grupo_despesa varchar(255)  -- para controle de parcelas em grupo
}
```

---

## ⚙️ Tecnologias Usadas

- PHP 8+
- MySQL/MariaDB
- HTML, CSS, JavaScript
- Font Awesome + Phosphor Icons
- FiveServer (opcional) para hot reload
- XAMPP + VSCode (ambiente de desenvolvimento local)

---

## 🚀 Como Rodar Localmente

1. Clone o repositório:

   ```bash
   git clone https://github.com/seu-usuario/ficasuave.git
   ```

2. Coloque o projeto dentro do diretório `htdocs` do XAMPP.

3. Importe os arquivos `.sql` para o seu MySQL.

4. Altere as configurações do banco no arquivo:

   ```
   app/config/db_config.php
   ```

5. Acesse no navegador:
   ```
   http://localhost/ficasuave/public/
   ```

---

## ✅ To-Do

- [x] Autenticação com logs de acesso
- [x] Cadastro e edição de receitas e despesas
- [x] Controle de cartões e contas
- [x] Filtros e pesquisa por mês, ano e status
- [ ] Exportação para Excel/CSV (em andamento)
- [ ] Dashboard com gráficos (em andamento)
- [ ] API para mobile (em andamento)

---

## 🐛 Testes

O projeto inclui um teste básico de conexão com o banco:

```
/tests/db_test.php
```

---

## 🧠 Contribuindo

Quer contribuir? Manda ver! Abra uma issue ou faça um fork e crie um pull request. 💜

---

## 🐧 Autor

Desenvolvido por [Tininho Fita](https://github.com/tininhofita) com 💙 e muito café.

---
