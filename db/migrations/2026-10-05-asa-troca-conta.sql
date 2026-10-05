-- Banco ASA (594) — troca de conta, operacao e faixa de nosso numero.
--
-- O banco informou em 05/10/2026 novos dados para a conta de cobranca. A linha da
-- tabela contas e atualizada no lugar: o id nao muda, entao sessoes abertas e o
-- contador de NSA (ultimo_nsa) continuam valendo.
--
-- Onde cada campo pesa:
--   conta / conta_dv  -> header e segmento P da remessa; impresso no boleto
--   operacao          -> campo livre do codigo de barras (campos 13-19 da linha digitavel)
--   faixa_inicio/fim  -> alocacao do nosso numero em proc_lista_boletos.php
--
-- Rodar em HOMOLOGACAO e em PRODUCAO, um ambiente de cada vez. Os valores novos
-- sao os mesmos nos dois (vem do banco); o diagnostico do passo 1 NAO e — cada
-- base tem os seus boletos.
--
-- Como rodar: as variaveis @ so existem na conexao que as criou. Execute
-- "passo 0 + passo 1" juntos e analise; depois "passo 0 + passo 2 + passo 3"
-- juntos. No phpMyAdmin, cada clique em Executar e uma conexao nova.

-- ---------------------------------------------------------------------------
-- Passo 0 — valores novos (preencha so aqui)
-- ---------------------------------------------------------------------------
SET @conta_atual       = '600001425';
SET @nova_conta        = '<CONTA>';      -- so digitos, sem o DV
SET @novo_conta_dv     = '<DV>';         -- 1 caractere
SET @nova_operacao     = '<OPERACAO>';   -- 7 digitos, com zeros a esquerda
SET @nova_faixa_inicio = <INICIO>;       -- nosso numero sem o DV, sem zeros a esquerda
SET @nova_faixa_fim    = <FIM>;

-- ---------------------------------------------------------------------------
-- Passo 1 — diagnostico (nao altera nada)
-- ---------------------------------------------------------------------------
-- 1a. Tem que voltar exatamente 1 linha, com conta = 600001425. Guarde este
--     resultado: e o que o passo 4 restaura.
SELECT id, agencia, agencia_dv, conta, conta_dv, operacao, carteira,
       faixa_inicio, faixa_fim, ultimo_nsa
  FROM contas
 WHERE banco = 'ASA';

-- 1b. Boletos ja emitidos na conta ASA e se a faixa nova e compativel com eles.
--     A alocacao parte de MAX(bo_nnum) + 1 da conta: se ja existir nosso numero
--     maior ou igual ao inicio da faixa nova, o proximo boleto NAO sai no inicio
--     dela — e, se a faixa nova estiver abaixo da antiga, a emissao trava com
--     "faixa acabou". Nesse caso, pare e ajuste o codigo antes de seguir.
SELECT COUNT(b.id_boleto)                                     AS boletos_asa,
       COALESCE(SUM(b.remessa_id > 0 AND p.pr_pago = 'n'), 0) AS abertos_ja_registrados,
       COALESCE(SUM(b.remessa_id = 0 AND p.pr_pago = 'n'), 0) AS abertos_nao_enviados,
       MAX(CAST(b.bo_nnum AS UNSIGNED))                       AS maior_nosso_numero,
       CASE
         WHEN @nova_faixa_inicio IS NULL OR @nova_faixa_fim IS NULL
           THEN 'ERRO: execute o passo 0 junto com este'
         WHEN COUNT(c.id) = 0
           THEN 'ERRO: conta ASA 600001425 nao encontrada; os numeros acima nao valem'
         WHEN @nova_faixa_inicio > @nova_faixa_fim
           THEN 'ERRO: inicio da faixa maior que o fim'
         WHEN MAX(CAST(b.bo_nnum AS UNSIGNED)) IS NULL
           OR MAX(CAST(b.bo_nnum AS UNSIGNED)) < @nova_faixa_inicio
           THEN 'OK: proximo boleto sai no inicio da faixa nova'
         ELSE 'PARE: ja existe nosso numero >= inicio da faixa nova'
       END                                                    AS faixa,
       CASE
         WHEN @nova_conta IS NULL                   THEN 'ERRO: execute o passo 0 junto com este'
         WHEN NOT (@nova_conta REGEXP '^[0-9]+$')   THEN 'ERRO: conta so com digitos, sem o DV'
         WHEN CHAR_LENGTH(@novo_conta_dv) <> 1      THEN 'ERRO: DV da conta com 1 caractere'
         WHEN NOT (@nova_operacao REGEXP '^[0-9]{7}$') THEN 'ERRO: operacao com 7 digitos'
         ELSE 'OK'
       END                                                    AS valores
  FROM contas c
  LEFT JOIN boletos b    ON b.conta_id = c.id
  LEFT JOIN prestacoes p ON p.id_presta = b.bo_presta
 WHERE c.banco = 'ASA' AND c.conta = @conta_atual;

-- abertos_ja_registrados: titulos que o banco recebeu na conta antiga e ainda nao
--   foram pagos. Os boletos impressos continuam com a operacao antiga no codigo de
--   barras; uma REIMPRESSAO depois da troca sai com a operacao nova. Confirme com o
--   banco o que acontece com esses titulos antes de reimprimir qualquer um.
-- abertos_nao_enviados: se entrarem numa remessa depois da troca, vao com a conta
--   nova no header e o nosso numero da faixa antiga. Confirme com o banco se aceita
--   ou se devem ser reemitidos na faixa nova.

-- ---------------------------------------------------------------------------
-- Passo 2 — aplica
-- ---------------------------------------------------------------------------
-- As condicoes do WHERE barram a atualizacao se a conta atual nao for a esperada,
-- se algum valor novo estiver fora do formato ou se a faixa nova esbarrar em
-- nosso numero ja usado (o "PARE" do passo 1b). Nesse caso nada e alterado.
UPDATE contas
   SET conta        = @nova_conta,
       conta_dv     = @novo_conta_dv,
       operacao     = @nova_operacao,
       faixa_inicio = @nova_faixa_inicio,
       faixa_fim    = @nova_faixa_fim
 WHERE banco = 'ASA'
   AND conta = @conta_atual
   AND @nova_conta REGEXP '^[0-9]+$'
   AND CHAR_LENGTH(@novo_conta_dv) = 1
   AND @nova_operacao REGEXP '^[0-9]{7}$'
   AND @nova_faixa_inicio BETWEEN 1 AND @nova_faixa_fim
   AND @nova_faixa_fim <= 4294967295    -- limite do INT UNSIGNED das colunas de faixa
   AND NOT EXISTS (SELECT 1 FROM boletos b
                    WHERE b.conta_id = contas.id
                      AND CAST(b.bo_nnum AS UNSIGNED) >= @nova_faixa_inicio);

-- Tem que voltar 1. Se voltar 0, nada foi alterado: revise o passo 0.
SELECT ROW_COUNT() AS linhas_alteradas;

-- ---------------------------------------------------------------------------
-- Passo 3 — confira
-- ---------------------------------------------------------------------------
SELECT id, agencia, agencia_dv, conta, conta_dv, operacao, carteira,
       faixa_inicio, faixa_fim, ultimo_nsa
  FROM contas
 WHERE banco = 'ASA';

-- Depois, na aplicacao: entre em "Gerar Remessa" e selecione a conta ASA de novo
-- no combo (a sessao guarda o numero da conta antigo para marcar a opcao).

-- ---------------------------------------------------------------------------
-- Passo 4 — so se precisar desfazer
-- ---------------------------------------------------------------------------
-- Dados anteriores a troca (os de liberacao de 27/07/2026). Confira com o
-- resultado do passo 1a antes de rodar.
--
-- UPDATE contas
--    SET conta = '600001425', conta_dv = '8', operacao = '0004142',
--        faixa_inicio = 7862083, faixa_fim = 7877082
--  WHERE banco = 'ASA' AND conta = @nova_conta;
