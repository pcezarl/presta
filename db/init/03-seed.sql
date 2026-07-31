-- Dados de exemplo para desenvolvimento local. Nao vao para producao.
--
-- As prestacoes usam datas relativas a CURDATE() porque lista-boletos.php e
-- lista-remessa.php filtram pelo mes e ano correntes — com datas fixas a lista
-- apareceria vazia dependendo de quando voce sobe o ambiente.

-- -----------------------------------------------------
-- Conta do Banco ASA, com os dados reais de liberacao
-- -----------------------------------------------------
INSERT INTO `contas`
  (`razao`, `cnpj`, `agencia`, `agencia_dv`, `conta`, `conta_dv`, `data`, `info`,
   `banco`, `operacao`, `carteira`, `faixa_inicio`, `faixa_fim`)
VALUES
  ('EMPRESA TESTE LTDA', '00.000.000/0001-00', '0001', '9', '600001425', '8',
   CURDATE(), 'Conta de desenvolvimento — dados de liberacao reais do ASA',
   'ASA', '0004142', '121', 7862083, 7877082);

-- Uma conta Bradesco para conferir que o caminho antigo nao quebrou
INSERT INTO `contas`
  (`razao`, `cnpj`, `agencia`, `conta`, `data`, `banco`, `acessorio`)
VALUES
  ('EMPRESA TESTE LTDA', '00.000.000/0001-00', '1234', '5678901',
   CURDATE(), 'BRA', '12345678901234567890');

-- -----------------------------------------------------
-- Edificio, clientes e apartamentos
-- -----------------------------------------------------
INSERT INTO `edificios` (`ed_nome`, `ed_end`, `ed_info`) VALUES
  ('Edificio Aurora', 'Rua das Palmeiras, 500 - Centro', 'Predio de exemplo');

INSERT INTO `clientes`
  (`cli_nome`, `cli_email`, `cli_tel`, `cli_cpf`, `cli_data_cadastro`,
   `cli_rua`, `cli_numero`, `cli_bairro`, `cli_cidade`, `cli_cep`, `cli_estado`)
VALUES
  ('Maria Aparecida de Souza', 'maria@exemplo.local', '(11) 98888-1111',
   '111.222.333-44', CURDATE(), 'Rua das Acacias', '120', 'Jardim Paulista',
   'Sao Paulo', '01415-000', 'SP'),
  ('Joao Batista Pereira', 'joao@exemplo.local', '(11) 97777-2222',
   '555.666.777-88', CURDATE(), 'Avenida Brasil', '2300', 'Centro',
   'Campinas', '13010-100', 'SP'),
  ('Construtora Horizonte Ltda', 'contato@exemplo.local', '(11) 3333-4444',
   '11.222.333/0001-44', CURDATE(), 'Rua Sete de Setembro', '45', 'Centro',
   'Santos', '11010-200', 'SP');

INSERT INTO `aptos`
  (`ap_num`, `ap_ed`, `ap_valor`, `ap_prop`, `ap_data_compra`, `ap_total_presta`, `ap_vendido`)
VALUES
  ('101', 1, 250000.00, 1, CURDATE(), 60, 's'),
  ('102', 1, 310000.00, 2, CURDATE(), 60, 's'),
  ('201', 1, 480000.00, 3, CURDATE(), 60, 's');

-- -----------------------------------------------------
-- Prestacoes em aberto no mes corrente
-- -----------------------------------------------------
INSERT INTO `prestacoes`
  (`pr_apto`, `pr_prop`, `pr_valor`, `pr_vencimento`, `pr_pago`, `pr_data_pago`, `pr_tipo`, `pr_num`)
VALUES
  (1, 1,   20.00, DATE_FORMAT(CURDATE(), '%Y-%m-10'), 'n', '0000-00-00', 'n', 1),
  (2, 2,  157.35, DATE_FORMAT(CURDATE(), '%Y-%m-10'), 'n', '0000-00-00', 'n', 1),
  (3, 3, 1250.00, DATE_FORMAT(CURDATE(), '%Y-%m-15'), 'n', '0000-00-00', 'n', 1),
  (1, 1,   20.00, DATE_FORMAT(CURDATE(), '%Y-%m-20'), 'n', '0000-00-00', 'n', 2),
  (2, 2,  157.35, DATE_FORMAT(CURDATE(), '%Y-%m-25'), 'n', '0000-00-00', 'n', 2);
