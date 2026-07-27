# Integração Banco ASA SCD (594) — Design

**Data:** 2026-07-27
**Branch:** `feature/light-refactoring`
**Escopo:** Adicionar o Banco ASA SCD ao fluxo de pagamentos com paridade de funcionalidade em relação a Bradesco, Caixa e Sicoob — emissão de boleto e geração de arquivo de remessa.

## Contexto e restrições

- Sistema PHP legado (~2009) de gestão de boletos e prestações. Ver [2026-07-23-light-refactoring-design.md](2026-07-23-light-refactoring-design.md) para as restrições gerais.
- **PHP 5.3 fixo.** Código novo deve ser sintaxe-compatível: `array()` e não `[]`, sem traits, sem `finally`, sem `::class`, sem variadics.
- **Sem PHP na máquina de desenvolvimento.** A validação final roda na máquina física do usuário. Isso motiva a estratégia de verificação da seção 7.
- Os três bancos existentes estão em produção e não podem ser testados aqui. Todo o trabalho do ASA entra **isolado**, sem alterar caminhos existentes.

### Fontes normativas

| Documento | Onde | O que define |
|---|---|---|
| `1.MANUAL BOLETO 1.pdf` | `Kit Implantação Cobrança Vinculada/` | Código de barras, linha digitável, módulo 10 e 11, fator de vencimento, composição do campo livre |
| `1.MANUAL NUMERO BANCARIO.pdf` | idem | Cálculo do DV do nosso número, regra da faixa, desvios do ASA no CNAB 240 |
| `Modelo de boleto Cobrança Vinculada.png` | idem | Boleto-modelo, origem do vetor de aceitação |
| `Modelo Calculo DV nosso número.xlsx` | idem | Três validadores de DV: módulo 10, módulo 11 de 11 caracteres, módulo 11 de 8 caracteres |
| Manual Bradesco CNAB 240 | https://assets.bradesco/content/dam/portal-bradesco/assets/pessoajuridica/pdf/MPO-Troca-Arquivos-Layout-240P.pdf | Layout completo da remessa (header arquivo `084`, header lote `042`, segmentos P/Q/R, trailers) |

### Dados de liberação da conta

Banco `594` — ASA SCD · Agência `0001-9` · Conta vinculada `600001425-8` · Operação `0004142` · Carteira `121` · Juros 1% · Multa 2% · Cartório automático não contratado · Emissão do boleto pela empresa · Layout CNAB: **Bradesco 240**.

## Decisões

| # | Decisão | Racional |
|---|---|---|
| D1 | Nosso número alocado por faixa cadastrada na conta, próximo = `MAX(bo_nnum)+1` | O ASA recusa número repetido ou fora da faixa. Deriva do que foi realmente emitido, sem um contador paralelo que possa dessincronizar. |
| D2 | Remessa em CNAB 240 no layout Bradesco | Consta explicitamente nos dados de liberação da conta. |
| D3 | Multa 2%, juros 1% ao mês, sem protesto, limite de 30 dias | Igual aos demais bancos do sistema; confirmado pelos dados de liberação. |
| D4 | ASA entra como 4ª ramificação nos `if/else` existentes | Menor risco. Não toca nos caminhos de Bradesco/Caixa/Sicoob, que estão em produção e não podem ser testados aqui. |
| D5 | Posições 38-57 do segmento P isoladas num método com duas variantes | Os manuais do ASA e do Bradesco se contradizem nessa faixa (ver seção 8). Isolar torna a troca uma edição de uma linha. |
| D6 | O número de operação não é gravado na remessa por ora | Não há campo evidente no segmento P do Bradesco. Suspenso por decisão do usuário, a retomar. |

## 1. Arquitetura

Três arquivos novos, espelhando a estrutura existente:

| Arquivo | Conteúdo |
|---|---|
| `lib/class_boleto_ASA.php` | classe `boleto_ASA` — campo livre, código de barras, linha digitável, render do layout |
| `lib/class_remessa_ASA.php` | classe `remessa_ASA` — `header()`, `registro()`, `trailer()` gerando CNAB 240 |
| `cboleto/include/layout_asa.php` | template HTML do boleto |

O `__autoload` de [lib/var.php:10](../../../lib/var.php#L10) resolve `boleto_ASA` → `lib/class_boleto_ASA.php` sem registro adicional. Os nomes de classe devem casar exatamente com os nomes de arquivo (sistema de arquivos sensível a maiúsculas).

**Reaproveitado sem alteração de `class_boleto_SICOOB`:** `modulo_10()`, `dvCodigoBarras()`, `_fator_vencimento()` (já com a data-base 22/02/2025), `_dateToDays()`, `_fbarcode()`, `formata_numero()`, `set()`, `val()`, `reset()`, `init()`, `pagina()`, `fim()`.

**Deliberadamente não reaproveitado:** `monta_linha_digitavel()` e `monta_campo_livre()` do Sicoob são específicos daquele banco — empacotam modalidade e parcela dentro do nosso número. O ASA usa a composição FEBRABAN genérica.

## 2. Nosso número

### Armazenamento

`boletos.bo_nnum` guarda o nosso número **sem o DV**, como inteiro em texto. Bradesco e Sicoob guardam com DV; o ASA não pode, porque `MAX(bo_nnum)+1` sobre um número com DV pularia posições da faixa. O DV é determinístico e recalculado onde for necessário.

### Alocação

Executada apenas no ramo do ASA, **antes** de `$b->draw()`:

1. Buscar boleto já existente para a prestação (`SELECT bo_nnum FROM boletos WHERE bo_presta = ?`). Se existir com `bo_nnum` preenchido, **reusar**. Reimpressão nunca consome número novo.
2. Caso contrário, alocar: `SELECT MAX(CAST(bo_nnum AS UNSIGNED)) FROM boletos WHERE conta_id = ?`, somar 1. Se não houver nenhum registro, usar `faixa_inicio`.
3. Se o valor exceder `faixa_fim`, abortar com mensagem clara **sem emitir o boleto**.
4. Envolver os passos 2 e 3 em `LOCK TABLES boletos WRITE` (a `class_db` já expõe `lock()`/`unlock()`), liberando após o `INSERT`. Número duplicado faz o ASA recusar a remessa inteira.

A conta é nova e nenhum boleto foi emitido, portanto a primeira alocação parte de `faixa_inicio`.

### Dígito verificador

Módulo 10 sobre a concatenação `agência(4) + carteira(3) + nosso número(10)`, totalizando 17 dígitos:

- pesos 2,1,2,1… aplicados da direita para a esquerda;
- produtos maiores que 9 têm seus dígitos somados (12 → 1+2 = 3);
- `DV = 10 - (soma mod 10)`, sendo 0 quando o resto é 0.

O `modulo_10()` existente em `class_boleto_SICOOB` implementa exatamente esse algoritmo e é reaproveitado verbatim.

Fica isolado num método `dvNossoNumero()` porque a planilha do kit traz duas variantes alternativas — módulo 11 de 11 caracteres (pesos 3,2,9,8,7,6,5,4,3,2) e módulo 11 de 8 caracteres (pesos 8..2). O manual atribui o módulo 10 à carteira 121, que é a contratada.

## 3. Boleto

### Campo livre (25 posições)

| Posição | Tamanho | Conteúdo |
|---|---|---|
| 01-04 | 4 | Agência sem DV — `0001` |
| 05-07 | 3 | Carteira — `121` |
| 08-14 | 7 | Número da operação — `0004142` |
| 15-25 | 11 | Nosso número com DV, zeros à esquerda |

### Código de barras (44 posições)

`594` + `9` (Real) + DV geral + fator de vencimento (4) + valor (10, 2 decimais) + campo livre (25).

O DV geral é módulo 11 sobre os outros 43 dígitos: pesos 2 a 9 cíclicos da direita para a esquerda, `DV = 11 - resto`, com **DV = 1** quando o resto é 0, 1 ou 10. É o que `dvCodigoBarras()` já faz.

### Linha digitável

Composição FEBRABAN padrão em cinco campos, com DV módulo 10 nos três primeiros:

| Campo | Conteúdo | Formato |
|---|---|---|
| 1 | banco (3) + moeda (1) + campo livre 1-5 + DV | `XXXXX.XXXXX` |
| 2 | campo livre 6-15 + DV | `XXXXX.XXXXXX` |
| 3 | campo livre 16-25 + DV | `XXXXX.XXXXXX` |
| 4 | DV geral do código de barras | `X` |
| 5 | fator de vencimento (4) + valor (10) | `XXXXXXXXXXXXXX` |

### Layout impresso

Clonado a partir de `layout_sicoob.php`, com os campos obrigatórios do manual: local de pagamento, vencimento, beneficiário com CNPJ, agência/código do beneficiário (`0001-9 / 600001425-8`), data do documento, número do documento, espécie `DM`, aceite `N`, data de processamento, nosso número, carteira `121`, espécie `R$`, valor do documento, pagador e código de barras.

A marca do ASA no modelo é a palavra "ASA" em sans-serif pesada, então o layout usa texto, substituível por `cboleto/imagens/logoasa.png` se o arquivo oficial for obtido.

## 4. Remessa CNAB 240 (layout Bradesco)

Banco `594` em todos os registros. Arquivo salvo em `remessa_arquivo/asa_ddmmYYYYHHii.rem`, com terminador `\r\n`, seguindo o padrão das classes existentes.

### Header de arquivo (tipo 0)

`1-3` banco `594` · `4-7` lote `0000` · `8` tipo `0` · `9-17` brancos · `18` tipo de inscrição `2` · `19-32` CNPJ (14) · `33-52` convênio (20) · `53-57` agência · `58` DV agência · `59-70` conta (12) · `71` DV conta · `72` DV ag/conta · `73-102` nome da empresa · `103-132` nome do banco · `133-142` brancos · `143` código remessa `1` · `144-151` data `DDMMAAAA` · `152-157` hora `HHMMSS` · `158-163` NSA · `164-166` versão `084` · `167-171` densidade · `172-191` reservado banco · `192-211` reservado empresa · `212-240` brancos.

### Header de lote (tipo 1)

`1-3` banco · `4-7` lote `0001` · `8` tipo `1` · `9` operação `R` · `10-11` serviço `01` · `12-13` brancos · `14-16` versão `042` · `17` branco · `18` tipo de inscrição `2` · `19-33` CNPJ (15) · `34-53` convênio (20) · `54-58` agência · `59` DV agência · `60-71` conta (12) · `72` DV conta · `73` DV ag/conta · `74-103` nome da empresa · `104-143` mensagem 1 · `144-183` mensagem 2 · `184-191` número sequencial da remessa · `192-199` data de gravação · `200-207` data do crédito · `208-240` brancos.

O campo `184-191` é o sequencial de remessa que o banco valida — o do header de arquivo (`158-163`) não é considerado. O manual rejeita o arquivo inteiro se o sequencial for inferior ao da remessa anterior.

### Segmento P (tipo 3)

`1-3` banco · `4-7` lote · `8` tipo `3` · `9-13` sequencial no lote · `14` `P` · `15` branco · `16-17` movimento `01` (entrada de títulos) · `18-22` agência · `23` DV agência · `24-35` conta `600001425` · `36` DV conta `8` · `37` DV ag/conta · **`38-57` identificação do título (ver seção 8)** · `58` carteira `2` (Cobrança Vinculada) · `59` cadastramento `1` (com registro) · `60` tipo de documento · `61` emissão `2` (cliente emitente) · `62` distribuição `2` · `63-77` número do documento · `78-85` vencimento `DDMMAAAA` · `86-100` valor (15, 2 dec) · `101-106` agência cobradora e DV · `107-108` espécie `02` (DM) · `109` aceite `N` · `110-117` data de emissão · `118` código de juros `2` (taxa mensal) · `119-126` data dos juros · `127-141` juros 1% · `142-165` desconto 1 · `166-180` IOF · `181-195` abatimento · `196-220` identificação do título na empresa · `221` protesto `3` (não protestar) · `222-223` prazo de protesto `00` · `224` baixa/devolução `1` · `225-227` prazo de baixa `030` · `228-229` moeda `09` · `230-239` número do contrato · `240` branco.

O código de carteira na posição 58 é `2` = Cobrança Vinculada, que é o produto contratado. O manual do Bradesco anota explicitamente que esse campo **não equivale à identificação do produto** — daí o `121` viver na região 38-57.

**Campos numéricos sem destino definido são preenchidos com zeros** enquanto a decisão D6 estiver valendo: convênio (header de arquivo `33-52` e header de lote `34-53`), agência cobradora (`101-106`) e número do contrato (`230-239`). O convênio e o número do contrato são justamente os candidatos a receber o número da operação — ver seção 8.3.

### Segmento Q (tipo 3)

`1-17` idem ao P com `Q` na posição 14 · `18` tipo de inscrição do pagador · `19-33` inscrição (15) · `34-73` nome · `74-113` endereço · `114-128` bairro · `129-133` CEP · `134-136` sufixo do CEP · `137-151` cidade · `152-153` UF · `154-169` beneficiário final · `170-209` nome do beneficiário final · **`210-212` banco correspondente = zeros (desvio ASA)** · `213-232` nosso número no banco correspondente · `233-240` brancos.

### Segmento R (tipo 3)

`1-17` idem com `R` na posição 14 · `18-41` desconto 2 · `42-65` desconto 3 · `66` código da multa `2` (percentual) · `67-74` data da multa (vencimento + 1 dia) · `75-89` multa 2% · `90-99` informação ao pagador · `100-139` mensagem 3 · `140-179` mensagem 4 · `180-199` brancos · `200-207` ocorrência do pagador · `208-231` dados de débito automático (não usados) · `232-240` brancos.

### Trailer de lote (tipo 5)

`1-3` banco · `4-7` lote · `8` tipo `5` · `9-17` brancos · `18-23` quantidade de registros no lote · `24-29` e `30-46` cobrança simples (zerados) · **`47-52` e `53-69` cobrança vinculada — quantidade e valor total dos títulos** · `70-92` caucionada (zerados) · `93-115` descontada (zerados) · `116-123` número do aviso · `124-240` brancos.

Os totais vão no bloco de Cobrança Vinculada, coerente com a carteira `2` da posição 58 do segmento P e com o produto contratado.

### Trailer de arquivo (tipo 9)

`1-3` banco · `4-7` `9999` · `8` tipo `9` · `9-17` brancos · `18-23` quantidade de lotes · `24-29` quantidade de registros do arquivo · `30-35` quantidade de contas para conciliação · `36-240` brancos.

## 5. Schema

Quatro colunas novas em `contas`, num `ALTER TABLE` idempotente entregue como `db/migrations/2026-07-27-contas-asa.sql`. Seguem a convenção da tabela, que já tem colunas específicas de banco sem prefixo (`acessorio` é do Bradesco, `cod_cliente` é do Sicoob):

| Coluna | Tipo | Uso |
|---|---|---|
| `operacao` | `VARCHAR(7)` | campo livre do boleto, posições 8-14 |
| `carteira` | `VARCHAR(3)` default `121` | campo livre, posições 5-7 |
| `faixa_inicio` | `INT UNSIGNED` | primeiro nosso número da faixa |
| `faixa_fim` | `INT UNSIGNED` | último nosso número da faixa |
| `agencia_dv` | `VARCHAR(1)` | dígito da agência — `9` |
| `conta_dv` | `VARCHAR(1)` | dígito da conta vinculada — `8` |

As duas últimas colunas existem porque `agencia` e `conta` guardam os valores **sem dígito** (o formulário atual diz "s/ dígito") e os dígitos do ASA são atribuídos pelo banco, não calculáveis. O segmento P exige os dois (posições 23 e 36), assim como os cabeçalhos de arquivo e de lote.

## 6. Pontos de integração

| Arquivo | Alteração |
|---|---|
| `tpl/tpl_form_add_conta.html` | opção `ASA` no `<select>`; exibir os quatro campos novos quando ASA estiver selecionado, pelo mesmo mecanismo de show/hide já usado para `acessorio` |
| `proc_add_conta.php` | ler e gravar os quatro campos novos |
| `proc_lista_boletos.php` | ramo `ASA`: carregar dados da conta, alocar o nosso número antes do `draw()`, gravar `bo_nnum` sem DV |
| `proc_lista_remessa.php` | ramo `ASA`: instanciar `remessa_ASA` e mapear os campos do pagador (endereço, bairro, cidade, estado, CEP) |

## 7. Verificação

Sem PHP na máquina de desenvolvimento, a verificação é um script autocontido `sandbox/verifica_asa.php` que o usuário roda na máquina física antes de emitir qualquer boleto real. Ele instancia `boleto_ASA` com os dados do vetor abaixo e compara campo livre, código de barras e linha digitável com os valores esperados, imprimindo OK ou FALHA por item.

### Vetor de aceitação

Derivado do boleto-modelo do kit e conferido dígito a dígito:

```
entrada : agência 0001 · carteira 121 · operação 0001316 · nosso número 465280
          vencimento 30/05/2025 · valor R$ 20,00
saída   : DV do nosso número = 9 · fator de vencimento = 1097 · DV do código de barras = 5

campo livre     = 0001121000131600004652809
código de barras = 59495109700000020000001121000131600004652809
linha digitável  = 59490.00110 21000.131603 00046.528097 5 10970000002000
```

A operação `0001316` é a do exemplo do manual, não a da conta. Trocá-la muda a linha digitável inteira, então o vetor só é válido com esse valor.

A remessa não tem vetor equivalente: a validação é o ASA aceitar o arquivo. Antes do primeiro envio real, gerar um arquivo de teste e submetê-lo à homologação do banco.

## 8. Pendências com o banco

### 8.1 Partição das posições 38-57 do segmento P

Os dois manuais dividem os mesmos 20 caracteres de forma incompatível:

| Faixa | Bradesco 240 (campo 13.3P) | Manual ASA (item 5) |
|---|---|---|
| 38-40 | Identificação do Produto (3) | `5` em 38; zeros em 39-41 |
| 41-45 | Zeros (5) | — |
| 42-43 | — | não especificado |
| 44-46 | — | `121` |
| 46-56 | Nosso Número (11) | — |
| 47-57 | — | Nosso Número com DV (11) |
| 57 | Dígito do Nosso Número (1) | — |

O nosso número fica deslocado em uma posição entre as duas leituras, e arquivo desalinhado é recusado por inteiro.

**Tratamento:** implementar num método `identificacaoTitulo()` com as duas variantes e uma constante de seleção no topo da classe. O padrão é a partição do Bradesco, porque os dados de liberação dizem "Layout CNAB: Bradesco 240". Trocar para a leitura do ASA é uma edição de uma linha.

### 8.2 Faixa de nosso número

Mínimo e máximo ainda não fornecidos. São cadastrados em `contas` quando chegarem; a implementação não depende deles, mas a emissão sim.

### 8.3 Número da operação na remessa — **suspenso**

No boleto a operação `0004142` é obrigatória e está definida. Na remessa não há campo evidente no segmento P do Bradesco; os candidatos são "Código do Convênio" (header de lote, 34-53) e "Número do Contrato" (segmento P, 230-239). Por decisão do usuário, a operação **não é gravada na remessa** nesta etapa. **Retomar este item antes do primeiro envio em produção.**

## Fora de escopo

- Processamento de arquivo de **retorno** do ASA. Nenhum banco do sistema tem isso hoje; o ASA não é exceção.
- Extração do dispatch `if/else` para um mapa banco → classe. Registrado como candidato do light refactoring, a fazer depois que o ASA estiver validado no banco.
- Remoção ou alteração de qualquer caminho de Bradesco, Caixa ou Sicoob.
