Table contas_bancarias {
  id_conta INT [pk, not null]
  id_usuario INT [ref: > usuarios.id_usuario]
  nome_conta VARCHAR(100) [not null]                // Ex: Nubank, Inter, PicPay
  tipo ENUM('corrente', 'poupanca', 'salario', 'digital', 'investimento', 'outro') [not null]
  banco VARCHAR(100)                                // Ex: Nubank, Itaú
  saldo_inicial DECIMAL(10,2) [default: 0.00]
  saldo_atual DECIMAL(10,2) [default: 0.00]
  ativa BOOLEAN [default: true]
  favorita BOOLEAN [default: false]
  data_criacao DATETIME [not null, default: 'CURRENT_TIMESTAMP']
}