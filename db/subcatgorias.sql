  Table subcategorias {
  id_subcategoria int(11) [pk, not null]
  id_categoria int(11) [not null]
  id_usuario int(11) [null]
  nome_subcategoria varchar(100) [not null]
  descricao text [null]
  icone varchar(100) [null]
  subcategoria_padrao boolean [not null, default: false]
  ativa boolean [not null, default: true]
  data_criacao datetime [not null, default: 'CURRENT_TIMESTAMP']
}