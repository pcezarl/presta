-- SICOOB — nova numeracao de nosso numero / numero do documento.
--
-- A numeracao antiga derivava do CPF: substr(cpf,0,4) + mes, SEM o ano, deixando
-- 1 caractere para o sequencial. Isso limitava a 9 titulos por balde, e o balde
-- era compartilhado por todos os clientes com os mesmos 4 digitos iniciais de CPF
-- e por todos os anos. Ao estourar, documento e nosso numero repetiam e o codigo
-- de barras saia com 45 posicoes em vez de 44.
--
-- A partir de agora o numero e um sequencial proprio da conta, de 7 digitos, cujo
-- ponto de partida vem de contas.faixa_inicio. Os boletos ja emitidos NAO sao
-- alterados — a regra vale daqui pra frente.
--
-- IMPORTANTE: o valor de faixa_inicio e especifico de CADA AMBIENTE. A base local
-- tem dados diferentes da producao, entao rode o passo 1 em cada uma e use o
-- resultado dali. Nao copie o numero de um ambiente para o outro.

-- ---------------------------------------------------------------------------
-- Passo 1 — descubra o maior nosso numero ja usado nesta conta
-- ---------------------------------------------------------------------------
SELECT c.id,
       c.conta,
       MAX(CAST(LEFT(b.bo_nnum,7) AS UNSIGNED)) AS maior_nosso_numero_usado,
       COUNT(*)                                 AS boletos
  FROM contas c
  LEFT JOIN boletos b ON b.conta_id = c.id AND b.bo_nnum IS NOT NULL AND b.bo_nnum <> ''
 WHERE c.banco = 'SICOOB'
 GROUP BY c.id, c.conta;

-- ---------------------------------------------------------------------------
-- Passo 2 — defina a faixa com folga sobre o valor acima
-- ---------------------------------------------------------------------------
-- Troque <INICIO> por um numero confortavelmente acima do maior_nosso_numero_usado
-- (por exemplo, arredonde para a proxima centena de milhar) e <ID> pelo id da conta.
-- O limite superior do campo e 9999999, porque o nosso numero do SICOOB tem 7 digitos.
--
-- UPDATE contas SET faixa_inicio = <INICIO>, faixa_fim = 9999999 WHERE id = <ID>;

-- ---------------------------------------------------------------------------
-- Passo 3 — confira
-- ---------------------------------------------------------------------------
-- SELECT id, conta, banco, faixa_inicio, faixa_fim FROM contas WHERE banco = 'SICOOB';
