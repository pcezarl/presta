-- Banco ASA (594) — renumera o Numero do Documento dos boletos ja emitidos.
--
-- Motivo: o ASA le o campo das posicoes 63-77 do segmento P com apenas 10
-- caracteres. O formato antigo (6 digitos do CPF + MMAA + 1 sequencial, 11
-- caracteres) tem os 10 primeiros identicos para o mesmo sacado no mesmo mes,
-- entao dois titulos do mesmo pagador chegavam com o mesmo numero. Recusa
-- "RC02 Documento em duplicidade" na homologacao de 16/09/2026.
--
-- A partir de 28/09/2026 o sistema passa a usar o proprio nosso numero,
-- zero-preenchido em 10 posicoes. Este script alinha o que ja estava gravado.
--
-- ATENCAO: rode um backup antes. O nosso numero e o codigo de barras NAO mudam,
-- entao boletos ja entregues ao pagador continuam pagaveis; o que muda e o texto
-- impresso em "Numero do documento". Reimprima os boletos que ja circularam.

-- Confira o que sera alterado ANTES de aplicar:
SELECT b.id_boleto, b.bo_ndoc AS antes, LPAD(b.bo_nnum, 10, '0') AS depois, b.remessa_id
  FROM boletos b JOIN contas c ON c.id = b.conta_id
 WHERE c.banco = 'ASA' AND b.bo_nnum IS NOT NULL AND b.bo_nnum <> '';

UPDATE boletos b
  JOIN contas c ON c.id = b.conta_id
   SET b.bo_ndoc = LPAD(b.bo_nnum, 10, '0')
 WHERE c.banco = 'ASA' AND b.bo_nnum IS NOT NULL AND b.bo_nnum <> '';

-- Os boletos das remessas recusadas precisam voltar a ficar disponiveis para um
-- novo envio. Descomente APENAS os numeros de remessa que o banco recusou:
-- UPDATE boletos b JOIN contas c ON c.id = b.conta_id
--    SET b.remessa_id = 0
--  WHERE c.banco = 'ASA' AND b.remessa_id IN (1, 2);
