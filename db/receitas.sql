Table receitas {
  id_receita int [pk]
  id_usuario int [ref: > usuarios.id_usuario]
  id_categoria int [ref: > categorias.id_categoria]
  id_subcategoria int [ref: > subcategorias.id_subcategoria]
  descricao text
  valor decimal(10,2)
  valor_recebido decimal(10,2)
  juros decimal(10,2)
  desconto decimal(10,2)
  id_conta INT [ref: > contas_bancarias.id_conta]
  id_forma_transacao INT [ref: > formas_transacao.id_forma_transacao]
  data_vencimento date
  data_recebimento date
  status enum('previsto', 'recebido')
  recorrente boolean
  parcelado boolean
  numero_parcelas int
  total_parcelas int
  grupo_receita varchar(50)
  ultima_parcela boolean [default: false]
  anexo varchar(255)
  observacoes text
  criado_em datetime
  atualizado_em datetime
}
