-- Adicionar coluna de cor na tabela de cartões
ALTER TABLE cartoes ADD COLUMN cor_cartao VARCHAR(7) DEFAULT '#3b82f6' AFTER bandeira;

-- Atualizar cartões existentes com cores padrão
UPDATE cartoes SET cor_cartao = '#8b5cf6' WHERE nome_cartao LIKE '%nubank%' OR nome_cartao LIKE '%roxo%';
UPDATE cartoes SET cor_cartao = '#3b82f6' WHERE cor_cartao IS NULL;
