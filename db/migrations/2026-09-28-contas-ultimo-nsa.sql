-- NSA (numero sequencial do arquivo de remessa) passa a ter contador proprio por conta.
--
-- Antes o NSA era calculado como MAX(boletos.remessa_id) + 1. Se os boletos da ultima
-- remessa voltassem a remessa_id = 0 (regerar nosso numero, correcao manual), o MAX
-- caia e a proxima remessa saia com um NSA que o banco ja tinha recebido — arquivo
-- recusado como duplicado. Agora proc_lista_remessa.php incrementa contas.ultimo_nsa.
--
-- IMPORTANTE: os bancos so tem ambiente de producao. O NSA que importa e o de
-- producao; local e homologacao tem contadores proprios que o banco nunca viu.
-- Nunca envie ao banco um arquivo gerado fora de producao, e nao copie o valor de
-- ultimo_nsa de um ambiente para outro.

-- ---------------------------------------------------------------------------
-- Passo 1 — cria a coluna
-- ---------------------------------------------------------------------------
ALTER TABLE contas ADD COLUMN ultimo_nsa INT UNSIGNED NOT NULL DEFAULT 0;

-- ---------------------------------------------------------------------------
-- Passo 2 — continua de onde a regra antiga parou
-- ---------------------------------------------------------------------------
UPDATE contas c
   SET c.ultimo_nsa = (SELECT COALESCE(MAX(b.remessa_id), 0) FROM boletos b WHERE b.conta_id = c.id);

-- ---------------------------------------------------------------------------
-- Passo 3 — so em PRODUCAO: confira com o ultimo NSA que o banco recebeu
-- ---------------------------------------------------------------------------
-- Pela regra antiga, o NSA pode ter recuado alguma vez e o banco ja ter recebido um
-- numero maior que o MAX(remessa_id) de hoje. Compare cada conta com o ultimo arquivo
-- aceito pelo banco (portal do banco ou arquivo de retorno) e, se o banco estiver a
-- frente, ajuste:
--
-- SELECT id, banco, agencia, conta, ultimo_nsa FROM contas ORDER BY banco, id;
-- UPDATE contas SET ultimo_nsa = <ULTIMO_NSA_RECEBIDO_PELO_BANCO> WHERE id = <ID>;
