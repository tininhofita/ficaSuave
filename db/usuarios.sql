Table usuarios {
  id_usuario int(11) [pk, not null]
  nome_usuario varchar(100) [not null]
  email varchar(100) [not null, unique]
  senha_hash varchar(255) [not null]
  data_criacao datetime [not null, default: 'CURRENT_TIMESTAMP']
  ultimo_login datetime [default: NULL]
  quantidade_logins int [default: 0]
  cep varchar(8) [not null]
  rua varchar(255) [not null]
  estado varchar(100) [not null]
  cidade varchar(100) [not null]
  pais varchar(100) [not null]
  uf varchar(2) [not null]
  renda VARCHAR(20)
}