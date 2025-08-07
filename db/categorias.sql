Table categorias {
  id_categoria int(11) [pk, not null]
  id_usuario int(11) [null] // NULL para categorias padrão
  nome_categoria varchar(100) [not null]
  descricao text [null] // opcional: útil pra dar mais contexto
  icone varchar(100) [null] // opcional: pode salvar o nome da classe do ícone (ex: ph-car)
  tipo enum('receita', 'despesa') [not null]
  categoria_padrao boolean [not null, default: false]
  ativa boolean [not null, default: true] // pra desativar sem apagar
  data_criacao datetime [not null, default: 'CURRENT_TIMESTAMP']
}