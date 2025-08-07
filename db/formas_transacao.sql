Table formas_transacao {
  id_forma_transacao INT [pk, not null]
  id_usuario INT [ref: > usuarios.id_usuario] // NULL para formas padrão do sistema
  nome VARCHAR(50) [not null] // Ex: 'PIX', 'Cartão de Crédito'
  tipo ENUM('dinheiro', 'cartao', 'transferencia', 'boleto', 'outro') [not null]
  uso ENUM('pagamento', 'recebimento', 'ambos')
  ativa BOOLEAN [not null, default: true]
  padrao BOOLEAN [not null, default: false] // se for do sistema
  data_criacao DATETIME [not null, default: 'CURRENT_TIMESTAMP']
}
