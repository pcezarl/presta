-- Banco ASA (594) — troca de conta, operacao, CNPJ e faixa de nosso numero.
--
-- O banco informou em 05/10/2026 novos dados para a conta de cobranca. A linha da
-- tabela contas e atualizada no lugar: o id nao muda, entao sessoes abertas e o
-- contador de NSA (ultimo_nsa) continuam valendo.
--
-- Onde cada campo pesa:
--   conta / conta_dv  -> header e segmento P da remessa; impresso no boleto
--   cnpj              -> header da remessa; impresso no boleto
--   operacao          -> campo livre do codigo de barras (campos 13-19 da linha digitavel)
--   faixa_inicio/fim  -> alocacao do nosso numero em proc_lista_boletos.php
--
-- A faixa NAO e escolhida por nos: o banco cadastra uma faixa de nosso numero
-- para a carteira 121 atrelada a uma operacao ("1.MANUAL NUMERO BANCARIO.pdf",
-- item 1). O banco manteve a faixa 7862083-7877082 para a operacao nova; como
-- a alocacao parte de MAX(bo_nnum) + 1, os numeros ja usados nao se repetem.
--
-- So para HOMOLOGACAO (develop), onde a conta ASA ja existe com os dados antigos.
-- Em PRODUCAO a conta ASA nao existe — a release/0.0.1-prod nao tinha ASA — e e
-- cadastrada pela tela add_conta.php depois do deploy (ver PR #2). Se este
-- script for rodado la, o passo 2 nao altera nada.

-- ---------------------------------------------------------------------------
-- Passo 1 — diagnostico (nao altera nada)
-- ---------------------------------------------------------------------------
-- Pode rodar a qualquer momento, um SELECT de cada vez ou os dois juntos.

-- 1a. Dados atuais da conta. Guarde este resultado: e o que o passo 4 restaura.
SELECT id, razao, cnpj, agencia, agencia_dv, conta, conta_dv, operacao, carteira,
       faixa_inicio, faixa_fim, ultimo_nsa
  FROM contas
 WHERE banco = 'ASA';

-- 1b. Boletos ja emitidos na conta ASA.
SELECT c.id                                                   AS conta_id,
       c.conta,
       COUNT(b.id_boleto)                                     AS boletos_asa,
       COALESCE(SUM(b.remessa_id > 0 AND p.pr_pago = 'n'), 0) AS abertos_ja_registrados,
       COALESCE(SUM(b.remessa_id = 0 AND p.pr_pago = 'n'), 0) AS abertos_nao_enviados,
       MIN(CAST(b.bo_nnum AS UNSIGNED))                       AS menor_nosso_numero,
       MAX(CAST(b.bo_nnum AS UNSIGNED))                       AS maior_nosso_numero
  FROM contas c
  LEFT JOIN boletos b    ON b.conta_id = c.id
  LEFT JOIN prestacoes p ON p.id_presta = b.bo_presta
 WHERE c.banco = 'ASA'
 GROUP BY c.id, c.conta;

-- abertos_ja_registrados: titulos que o banco recebeu na conta antiga e ainda nao
--   foram pagos. Os boletos impressos continuam com a operacao antiga no codigo de
--   barras; uma REIMPRESSAO depois da troca sai com a operacao nova. Confirme com o
--   banco o que acontece com esses titulos antes de reimprimir qualquer um.
-- abertos_nao_enviados: se entrarem numa remessa depois da troca, vao com a conta
--   nova no header e o nosso numero da faixa antiga. Confirme com o banco se aceita
--   ou se devem ser reemitidos na faixa nova.
-- maior_nosso_numero: a alocacao parte de MAX(bo_nnum) + 1 da conta. Se esse
--   numero ja chegou ao fim da faixa, o passo 2 recusa a troca (ver la).
--   Ele tambem define o "Faixa de" da conta em PRODUCAO: o maior nosso numero ja
--   enviado ao banco, mais 1 — o banco recusa numero repetido.

-- ---------------------------------------------------------------------------
-- Passo 2 — aplica (rode o bloco inteiro de uma vez)
-- ---------------------------------------------------------------------------
-- As variaveis @ so existem na conexao que as criou: os SET, a verificacao e o
-- UPDATE precisam ir juntos. No phpMyAdmin, cole o bloco todo e clique Executar
-- uma vez so.

SET @conta_atual       = '600001425';
SET @nova_conta        = '000016537';             -- so digitos, sem o DV
SET @novo_conta_dv     = '0';                     -- 1 caractere
SET @novo_cnpj         = '21.987.647/0001-29';
SET @nova_operacao     = '0004254';
SET @nova_faixa_inicio = 7862083;                 -- nosso numero sem o DV, sem zeros a esquerda
SET @nova_faixa_fim    = 7877082;

-- 2a. Verificacao: explica um eventual "0 linhas afetadas" no UPDATE abaixo.
SELECT CASE
         WHEN COUNT(c.id) = 0
           THEN 'ERRO: conta ASA 600001425 nao encontrada'
         WHEN @nova_faixa_inicio > @nova_faixa_fim
           THEN 'ERRO: inicio da faixa maior que o fim'
         WHEN MAX(CAST(b.bo_nnum AS UNSIGNED)) >= @nova_faixa_fim
           THEN 'PARE: os numeros ja usados esgotam a faixa'
         WHEN MAX(CAST(b.bo_nnum AS UNSIGNED)) >= @nova_faixa_inicio
           THEN 'OK: proximo boleto continua do maior nosso numero usado + 1'
         ELSE 'OK: proximo boleto sai no inicio da faixa'
       END AS faixa,
       CASE
         WHEN NOT (@nova_conta REGEXP '^[0-9]+$')      THEN 'ERRO: conta so com digitos, sem o DV'
         WHEN CHAR_LENGTH(@novo_conta_dv) <> 1         THEN 'ERRO: DV da conta com 1 caractere'
         WHEN NOT (@novo_cnpj REGEXP '^[0-9]{2}[.][0-9]{3}[.][0-9]{3}/[0-9]{4}-[0-9]{2}$')
                                                       THEN 'ERRO: CNPJ no formato 00.000.000/0000-00'
         WHEN NOT (@nova_operacao REGEXP '^[0-9]{7}$') THEN 'ERRO: operacao com 7 digitos'
         ELSE 'OK'
       END AS valores
  FROM contas c
  LEFT JOIN boletos b ON b.conta_id = c.id
 WHERE c.banco = 'ASA' AND c.conta = @conta_atual;

-- 2b. As condicoes do WHERE barram a atualizacao se a verificacao acima nao der
--     OK nas duas colunas. Nesse caso nada e alterado.
UPDATE contas
   SET conta        = @nova_conta,
       conta_dv     = @novo_conta_dv,
       cnpj         = @novo_cnpj,
       operacao     = @nova_operacao,
       faixa_inicio = @nova_faixa_inicio,
       faixa_fim    = @nova_faixa_fim
 WHERE banco = 'ASA'
   AND conta = @conta_atual
   AND @nova_conta REGEXP '^[0-9]+$'
   AND CHAR_LENGTH(@novo_conta_dv) = 1
   AND @novo_cnpj REGEXP '^[0-9]{2}[.][0-9]{3}[.][0-9]{3}/[0-9]{4}-[0-9]{2}$'
   AND @nova_operacao REGEXP '^[0-9]{7}$'
   AND @nova_faixa_inicio BETWEEN 1 AND @nova_faixa_fim
   AND @nova_faixa_fim <= 4294967295    -- limite do INT UNSIGNED das colunas de faixa
   AND NOT EXISTS (SELECT 1 FROM boletos b
                    WHERE b.conta_id = contas.id
                      AND CAST(b.bo_nnum AS UNSIGNED) >= @nova_faixa_fim);

-- Tem que voltar 1 ("1 linha afetada" no phpMyAdmin). Se voltar 0, nada foi alterado.
SELECT ROW_COUNT() AS linhas_alteradas;

-- ---------------------------------------------------------------------------
-- Passo 3 — confira
-- ---------------------------------------------------------------------------
SELECT id, razao, cnpj, agencia, agencia_dv, conta, conta_dv, operacao, carteira,
       faixa_inicio, faixa_fim, ultimo_nsa
  FROM contas
 WHERE banco = 'ASA';

-- Depois, na aplicacao: entre em "Gerar Remessa" e selecione a conta ASA de novo
-- no combo (a sessao guarda o numero da conta antigo para marcar a opcao).

-- ---------------------------------------------------------------------------
-- Passo 4 — so se precisar desfazer
-- ---------------------------------------------------------------------------
-- Preencha com o resultado do passo 1a. Conta, operacao e faixa abaixo sao os de
-- liberacao de 27/07/2026; o CNPJ antigo varia por ambiente.
--
-- UPDATE contas
--    SET conta = '600001425', conta_dv = '8', operacao = '0004142',
--        cnpj = '<CNPJ DO PASSO 1a>',
--        faixa_inicio = 7862083, faixa_fim = 7877082
--  WHERE banco = 'ASA' AND conta = '<CONTA NOVA>';
