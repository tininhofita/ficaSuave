Table despesas {
  id_despesa int [pk]
  grupo_despesa varchar(36)
  id_usuario int [ref: > usuarios.id_usuario]
  id_categoria int [ref: > categorias.id_categoria]
  id_subcategoria int [ref: > subcategorias.id_subcategoria]
  descricao text
  valor decimal(10,2)
  valor_pago decimal(10,2)
  juros decimal(10,2)
  desconto decimal(10,2)
  id_conta INT [ref: > contas_bancarias.id_conta]
  id_cartao INT [ref: > cartoes.id_cartao]
  id_forma_transacao INT [ref: > formas_transacao.id_forma_transacao]
  data_vencimento date
  data_pagamento date
  status enum('pendente', 'pago', 'atrasado')
  recorrente boolean
  parcelado boolean
  numero_parcelas int
  total_parcelas int
  ultima_parcela boolean [default: false]
  anexo varchar(255)
  observacoes text
  criado_em datetime
  atualizado_em datetime
}