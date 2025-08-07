Table cartoes {
  id_cartao int(11) [pk, not null]
  id_usuario int(11) [null]
  nome_cartao varchar(100) [not null]
  tipo ENUM('credito', 'debito') [not null]
  bandeira VARCHAR(50) // Ex: Visa, MasterCard
  icone_bandeira VARCHAR(100) // Ex: 'fa-brands fa-cc-visa'
  id_conta INT [ref: > contas_bancarias.id_conta]
  limite decimal(10,2) [not null]
  saldo_atual decimal(10,2) [not null]
  vencimento_fatura int(2) [not null]
  dia_fechamento int(2) [not null]
  data_criacao datetime [not null, default: 'CURRENT_TIMESTAMP']
}
