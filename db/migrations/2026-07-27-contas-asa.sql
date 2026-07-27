-- Integracao Banco ASA SCD (594) — colunas de configuracao da conta.
-- Rodar UMA vez. Se alguma coluna ja existir, o MySQL aborta com
-- "Duplicate column name"; nesse caso remova a linha correspondente e rode de novo.

ALTER TABLE contas ADD COLUMN operacao     VARCHAR(7)   NULL DEFAULT NULL;
ALTER TABLE contas ADD COLUMN carteira     VARCHAR(3)   NULL DEFAULT '121';
ALTER TABLE contas ADD COLUMN faixa_inicio INT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE contas ADD COLUMN faixa_fim    INT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE contas ADD COLUMN agencia_dv   VARCHAR(1)   NULL DEFAULT NULL;
ALTER TABLE contas ADD COLUMN conta_dv     VARCHAR(1)   NULL DEFAULT NULL;
