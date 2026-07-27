# Integração Banco ASA SCD (594) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Adicionar o Banco ASA SCD (594) ao fluxo de pagamentos do sistema, com emissão de boleto e geração de arquivo de remessa CNAB 240, em paridade com Bradesco, Caixa e Sicoob.

**Architecture:** Um núcleo de cálculo puro (`asa_calc`) sem I/O, consumido tanto pela classe de boleto quanto pela de remessa — é o que torna o algoritmo testável numa máquina sem o stack web. Sobre ele, `boleto_ASA` renderiza o HTML e `remessa_ASA` monta os registros CNAB 240. O ASA entra como quarta ramificação nos `if/else` existentes, sem tocar nos caminhos dos outros três bancos.

**Tech Stack:** PHP 5.3, MySQL, sem framework, sem gerenciador de dependências. Testes são scripts PHP autocontidos com um helper de asserção de 20 linhas.

**Spec:** [../specs/2026-07-27-integracao-banco-asa-design.md](../specs/2026-07-27-integracao-banco-asa-design.md)

## Global Constraints

- **PHP 5.3.** Usar `array()` e nunca `[]`. Sem traits, sem `finally`, sem `::class`, sem variadics `...`, sem `??`, sem short-echo de array. Closures são permitidas.
- **Sem PHP na máquina de desenvolvimento** (verificado: `which php` não retorna nada). Ver "Ambiente de teste" abaixo.
- **Não alterar nenhum caminho de Bradesco (`BRA`), Caixa (`CEF`) ou Sicoob (`SICOOB`).** Esses bancos estão em produção e não podem ser testados aqui. Toda alteração em arquivo compartilhado deve ser uma ramificação nova, aditiva.
- **Código do banco é `594`** em todos os registros da remessa.
- **Encargos fixos:** multa 2% (percentual), juros 1% ao mês (taxa mensal), sem protesto, prazo de baixa 30 dias.
- **Todo registro CNAB 240 tem exatamente 240 caracteres.** Terminador de linha `\r\n`, como nas classes existentes.
- **Não usar acentos em constantes de texto que vão para o arquivo de remessa** — a função `minimo()` já passa por `clean()`, mas mensagens devem ser escritas sem acento na origem, como no Bradesco e no Sicoob.

## Ambiente de teste

As Tasks 1, 3 e 5 produzem código de string/aritmética pura, sem banco e sem sessão — roda em qualquer PHP, não só no 5.3. Instalar PHP localmente habilita o ciclo vermelho/verde nessas três tasks:

```bash
brew install php      # opcional, mas recomendado; qualquer versão serve para os testes puros
php --version
```

Sem isso, os comandos `php tests/*.php` deste plano só rodam na máquina física do usuário, e as tasks devem ser entregues com o teste escrito mas não executado — declarando isso explicitamente, nunca afirmando que passou.

As Tasks 2, 4 e 6 dependem de banco de dados e sessão e **só podem ser verificadas na máquina física**, independentemente de haver PHP local.

**Desvio em relação ao design:** a seção 7 do spec previa um único script `tests/verifica_asa.php`. Este plano o substitui por três arquivos em `tests/` — um por unidade testável, com um helper de asserção comum. Mesmo propósito e mesmos valores esperados, mas cada task fecha com o seu próprio teste em vez de todas dependerem de um script final.

## File Structure

| Arquivo | Responsabilidade |
|---|---|
| `lib/class_asa_calc.php` (novo) | Cálculo puro: módulo 10, módulo 11, DV do nosso número, campo livre, código de barras, linha digitável, fator de vencimento, formatação de valor. Sem I/O. |
| `lib/class_boleto_ASA.php` (novo) | Renderização do boleto: carrega o layout, preenche tokens, desenha o código de barras. |
| `lib/class_remessa_ASA.php` (novo) | Registros CNAB 240: header de arquivo, header de lote, segmentos P/Q/R, trailers. |
| `cboleto/include/layout_asa.php` (novo) | Template HTML do boleto. |
| `db/migrations/2026-07-27-contas-asa.sql` (novo) | Colunas novas em `contas`. |
| `tests/assert.php` (novo) | Helper de asserção. |
| `tests/teste_asa_calc.php` (novo) | Vetor de aceitação do boleto. |
| `tests/teste_asa_remessa.php` (novo) | Larguras e posições dos registros CNAB. |
| `tpl/tpl_form_add_conta.html` (modificar) | Opção ASA e campos novos. |
| `proc_add_conta.php` (modificar) | Persistir campos novos. |
| `proc_lista_boletos.php` (modificar) | Ramo ASA + alocação do nosso número. |
| `proc_lista_remessa.php` (modificar) | Ramo ASA + mapeamento de campos do pagador. |

---

### Task 1: Núcleo de cálculo `asa_calc`

O coração da integração. Tudo que é verificável sem banco vive aqui, e o vetor de aceitação do spec valida o conjunto de uma vez.

**Files:**
- Create: `tests/assert.php`
- Create: `tests/teste_asa_calc.php`
- Create: `lib/class_asa_calc.php`

**Interfaces:**
- Consumes: nada.
- Produces: classe `asa_calc` com métodos estáticos —
  - `modulo_10($num)` → int
  - `modulo_11_barras($num)` → int
  - `dv_nosso_numero($agencia, $carteira, $nosso_numero)` → int
  - `nosso_numero_com_dv($agencia, $carteira, $nosso_numero)` → string de 11
  - `campo_livre($agencia, $carteira, $operacao, $nosso_numero)` → string de 25
  - `fator_vencimento($data_dmy)` → string de 4, formato `dd/mm/aaaa`
  - `valor_centavos($valor, $tamanho)` → string zero-preenchida à esquerda
  - `dv_codigo_barras($campo_livre, $fator, $valor10)` → int
  - `codigo_barras($campo_livre, $fator, $valor10)` → string de 44
  - `linha_digitavel($campo_livre, $fator, $valor10, $dv_barras)` → string formatada
  - constantes `asa_calc::BANCO` = `'594'`, `asa_calc::MOEDA` = `'9'`

- [ ] **Step 1: Criar o helper de asserção**

Arquivo `tests/assert.php`:

```php
<?php

$GLOBALS['t_total']  = 0;
$GLOBALS['t_falhas'] = 0;

function t_equals($esperado, $obtido, $label) {
    $GLOBALS['t_total']++;
    if ((string) $esperado === (string) $obtido) {
        echo "  OK    $label\n";
        return;
    }
    $GLOBALS['t_falhas']++;
    echo "  FALHA $label\n";
    echo "          esperado: [$esperado]\n";
    echo "          obtido:   [$obtido]\n";
}

function t_fim() {
    $total  = $GLOBALS['t_total'];
    $falhas = $GLOBALS['t_falhas'];
    echo "\n$total verificacoes, $falhas falha(s)\n";
    exit($falhas > 0 ? 1 : 0);
}
```

- [ ] **Step 2: Escrever o teste que falha**

Arquivo `tests/teste_asa_calc.php`. Os valores esperados vêm do boleto-modelo do kit e estão conferidos dígito a dígito no spec:

```php
<?php

require_once dirname(__FILE__) . '/assert.php';
require_once dirname(__FILE__) . '/../../lib/class_asa_calc.php';

echo "asa_calc :: vetor de aceitacao do boleto-modelo\n";

$agencia   = '0001';
$carteira  = '121';
$operacao  = '0001316';
$nn        = '465280';
$vencimento = '30/05/2025';
$valor     = '20.00';

t_equals(9, asa_calc::dv_nosso_numero($agencia, $carteira, $nn), 'DV do nosso numero');
t_equals('00004652809', asa_calc::nosso_numero_com_dv($agencia, $carteira, $nn), 'nosso numero com DV');

$campo_livre = asa_calc::campo_livre($agencia, $carteira, $operacao, $nn);
t_equals('0001121000131600004652809', $campo_livre, 'campo livre');
t_equals(25, strlen($campo_livre), 'campo livre tem 25 posicoes');

$fator = asa_calc::fator_vencimento($vencimento);
t_equals('1097', $fator, 'fator de vencimento');

$valor10 = asa_calc::valor_centavos($valor, 10);
t_equals('0000002000', $valor10, 'valor em centavos com 10 posicoes');

$dv_barras = asa_calc::dv_codigo_barras($campo_livre, $fator, $valor10);
t_equals(5, $dv_barras, 'DV do codigo de barras');

$barras = asa_calc::codigo_barras($campo_livre, $fator, $valor10);
t_equals('59495109700000020000001121000131600004652809', $barras, 'codigo de barras');
t_equals(44, strlen($barras), 'codigo de barras tem 44 posicoes');

t_equals(
    '59490.00110 21000.131603 00046.528097 5 10970000002000',
    asa_calc::linha_digitavel($campo_livre, $fator, $valor10, $dv_barras),
    'linha digitavel'
);

echo "\nasa_calc :: casos de borda\n";

t_equals('1000', asa_calc::fator_vencimento('22/02/2025'), 'fator na data-base nova');
t_equals('9999', asa_calc::fator_vencimento('21/02/2025'), 'fator no limite da data-base antiga');
t_equals('1000', asa_calc::fator_vencimento('03/07/2000'), 'fator no inicio da data-base antiga');
t_equals('000000000000123456', asa_calc::valor_centavos('1234.56', 18), 'valor com truncamento de ponto flutuante');
t_equals('0000000000', asa_calc::valor_centavos('0', 10), 'valor zero');

t_fim();
```

- [ ] **Step 3: Rodar o teste e confirmar que falha**

```bash
php tests/teste_asa_calc.php
```

Esperado: erro fatal `Class 'asa_calc' not found` ou falha ao incluir `lib/class_asa_calc.php`. Se PHP não estiver disponível, pular para o Step 4 e registrar que o teste não foi executado.

- [ ] **Step 4: Implementar `asa_calc`**

Arquivo `lib/class_asa_calc.php`:

```php
<?php

/**
 * Calculo puro do boleto do Banco ASA SCD (594).
 * Sem banco de dados, sem sessao, sem saida — para poder ser testado isoladamente.
 * Regras: Kit Implantacao Cobranca Vinculada, manuais de boleto e de numero bancario.
 */
class asa_calc {

    const BANCO = '594';
    const MOEDA = '9';

    /**
     * Modulo 10 da FEBRABAN: pesos 2,1,2,1... da direita para a esquerda,
     * somando os digitos dos produtos maiores que 9.
     */
    public static function modulo_10($num) {
        $soma  = 0;
        $fator = 2;
        for ($i = strlen($num); $i > 0; $i--) {
            $produto = ((int) substr($num, $i - 1, 1)) * $fator;
            if ($produto > 9) {
                $produto = ((int) ($produto / 10)) + ($produto % 10);
            }
            $soma += $produto;
            $fator = ($fator == 2) ? 1 : 2;
        }
        $resto = $soma % 10;
        return ($resto == 0) ? 0 : 10 - $resto;
    }

    /**
     * Modulo 11 do codigo de barras: pesos 2 a 9 ciclicos da direita para a
     * esquerda. DV = 11 - resto, sendo 1 quando o resto e 0, 1 ou 10.
     */
    public static function modulo_11_barras($num) {
        $soma  = 0;
        $fator = 2;
        for ($i = strlen($num); $i > 0; $i--) {
            $soma += ((int) substr($num, $i - 1, 1)) * $fator;
            $fator = ($fator == 9) ? 2 : $fator + 1;
        }
        $resto = $soma % 11;
        if ($resto == 0 || $resto == 1 || $resto == 10) {
            return 1;
        }
        return 11 - $resto;
    }

    /**
     * DV do nosso numero: modulo 10 sobre agencia(4) + carteira(3) + nosso numero(10).
     * O manual do ASA traz variantes em modulo 11 para outras carteiras; se o banco
     * indicar uma delas, e este o unico metodo a mudar.
     */
    public static function dv_nosso_numero($agencia, $carteira, $nosso_numero) {
        $base = str_pad($agencia, 4, '0', STR_PAD_LEFT)
              . str_pad($carteira, 3, '0', STR_PAD_LEFT)
              . str_pad($nosso_numero, 10, '0', STR_PAD_LEFT);
        return self::modulo_10($base);
    }

    public static function nosso_numero_com_dv($agencia, $carteira, $nosso_numero) {
        return str_pad($nosso_numero, 10, '0', STR_PAD_LEFT)
             . self::dv_nosso_numero($agencia, $carteira, $nosso_numero);
    }

    /** Campo livre (25): agencia(4) + carteira(3) + operacao(7) + nosso numero com DV(11). */
    public static function campo_livre($agencia, $carteira, $operacao, $nosso_numero) {
        return str_pad($agencia, 4, '0', STR_PAD_LEFT)
             . str_pad($carteira, 3, '0', STR_PAD_LEFT)
             . str_pad($operacao, 7, '0', STR_PAD_LEFT)
             . self::nosso_numero_com_dv($agencia, $carteira, $nosso_numero);
    }

    /** Valor em centavos, zero-preenchido a esquerda. Evita erro de ponto flutuante. */
    public static function valor_centavos($valor, $tamanho) {
        $normalizado = number_format((float) $valor, 2, '.', '');
        return str_pad(str_replace('.', '', $normalizado), $tamanho, '0', STR_PAD_LEFT);
    }

    private static function data_para_dias($ano, $mes, $dia) {
        $seculo = substr($ano, 0, 2);
        $ano    = substr($ano, 2, 2);
        if ($mes > 2) {
            $mes -= 3;
        } else {
            $mes += 9;
            if ($ano) {
                $ano--;
            } else {
                $ano = 99;
                $seculo--;
            }
        }
        return floor((146097 * $seculo) / 4)
             + floor((1461 * $ano) / 4)
             + floor((153 * $mes + 2) / 5)
             + $dia + 1721119;
    }

    /**
     * Fator de vencimento FEBRABAN. A partir de 22/02/2025 o ciclo reinicia em 1000,
     * conforme o comunicado FB-082/2012. Data no formato dd/mm/aaaa.
     */
    public static function fator_vencimento($data_dmy) {
        $partes = explode('/', $data_dmy);
        $dias   = self::data_para_dias($partes[2], $partes[1], $partes[0]);
        if ($dias <= 2460728) {
            $fator = abs(self::data_para_dias('1997', '10', '07') - $dias);
        } else {
            $fator = abs(self::data_para_dias('2025', '02', '22') - $dias) + 1000;
        }
        return str_pad($fator, 4, '0', STR_PAD_LEFT);
    }

    public static function dv_codigo_barras($campo_livre, $fator, $valor10) {
        return self::modulo_11_barras(self::BANCO . self::MOEDA . $fator . $valor10 . $campo_livre);
    }

    /** Codigo de barras (44): banco(3) + moeda(1) + DV(1) + fator(4) + valor(10) + campo livre(25). */
    public static function codigo_barras($campo_livre, $fator, $valor10) {
        return self::BANCO . self::MOEDA
             . self::dv_codigo_barras($campo_livre, $fator, $valor10)
             . $fator . $valor10 . $campo_livre;
    }

    /** Linha digitavel FEBRABAN em cinco campos, com DV modulo 10 nos tres primeiros. */
    public static function linha_digitavel($campo_livre, $fator, $valor10, $dv_barras) {
        $campo1 = self::BANCO . self::MOEDA . substr($campo_livre, 0, 5);
        $campo1 .= self::modulo_10($campo1);

        $campo2 = substr($campo_livre, 5, 10);
        $campo2 .= self::modulo_10($campo2);

        $campo3 = substr($campo_livre, 15, 10);
        $campo3 .= self::modulo_10($campo3);

        return substr($campo1, 0, 5) . '.' . substr($campo1, 5)
             . ' ' . substr($campo2, 0, 5) . '.' . substr($campo2, 5)
             . ' ' . substr($campo3, 0, 5) . '.' . substr($campo3, 5)
             . ' ' . $dv_barras
             . ' ' . $fator . $valor10;
    }
}
```

- [ ] **Step 5: Rodar o teste e confirmar que passa**

```bash
php tests/teste_asa_calc.php
```

Esperado: `15 verificacoes, 0 falha(s)` e código de saída 0. Se algum valor divergir, o erro está na implementação — os valores esperados foram conferidos contra o boleto-modelo e não devem ser ajustados para acomodar o código.

- [ ] **Step 6: Commit**

```bash
git add lib/class_asa_calc.php tests/assert.php tests/teste_asa_calc.php
git commit -m "feat(asa): nucleo de calculo do boleto com vetor de aceitacao"
```

---

### Task 2: Schema e cadastro de conta

Sem essas colunas não há de onde tirar operação, carteira e faixa. Tem que vir antes de qualquer código que leia a conta.

**Files:**
- Create: `db/migrations/2026-07-27-contas-asa.sql`
- Modify: `tpl/tpl_form_add_conta.html`
- Modify: `proc_add_conta.php`

**Interfaces:**
- Consumes: nada.
- Produces: colunas `operacao`, `carteira`, `faixa_inicio`, `faixa_fim`, `agencia_dv`, `conta_dv` na tabela `contas`; valor `ASA` na coluna `banco`.

- [ ] **Step 1: Escrever a migração**

Arquivo `db/migrations/2026-07-27-contas-asa.sql`. O MySQL do sistema é antigo e não suporta `ADD COLUMN IF NOT EXISTS`, então a idempotência fica documentada em comentário e o operador roda uma vez:

```sql
-- Integracao Banco ASA SCD (594) — colunas de configuracao da conta.
-- Rodar UMA vez. Se alguma coluna ja existir, o MySQL aborta com
-- "Duplicate column name"; nesse caso remova a linha correspondente e rode de novo.

ALTER TABLE contas ADD COLUMN operacao     VARCHAR(7)  NULL DEFAULT NULL;
ALTER TABLE contas ADD COLUMN carteira     VARCHAR(3)  NULL DEFAULT '121';
ALTER TABLE contas ADD COLUMN faixa_inicio INT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE contas ADD COLUMN faixa_fim    INT UNSIGNED NULL DEFAULT NULL;
ALTER TABLE contas ADD COLUMN agencia_dv   VARCHAR(1)  NULL DEFAULT NULL;
ALTER TABLE contas ADD COLUMN conta_dv     VARCHAR(1)  NULL DEFAULT NULL;
```

- [ ] **Step 2: Adicionar a opção ASA e os campos no formulário**

Em `tpl/tpl_form_add_conta.html`, adicionar a opção no `<select name="banco">` (atualmente nas linhas 52-56), como primeira opção:

```html
<option value="ASA">Banco ASA SCD</option>
```

No bloco `$('#banco').change(...)` (linhas 12-24), acrescentar o ramo do ASA **sem alterar os ramos existentes**:

```javascript
} else if ( this.value == "ASA" ) {
    $('.show_acessorio').hide();
    $('.show_asa').show();
    $('.text.conta').html('<span class="obr">!</span>Conta Vinculada:<sup>s/ d&iacute;gito</sup>')
}
```

Nos ramos `BRA`, `CEF` e `SICOOB` já existentes, acrescentar `$('.show_asa').hide();` como primeira linha de cada — é a única alteração permitida neles.

Logo após o `$(document).ready(function() {`, esconder o bloco por padrão:

```javascript
$('.show_asa').hide();
```

Adicionar a linha de campos do ASA na tabela, antes da linha de espaçamento (`<tr><td>&nbsp;</td>...`):

```html
<tr class="show_asa">
  <td class="text"><span class="obr">!</span>Opera&ccedil;&atilde;o:</td>
  <td><input name="operacao" type="text" id="operacao" size="40" maxlength="7" /></td>
  <td class="text"><span class="obr">!</span>Carteira:</td>
  <td><input name="carteira" type="text" id="carteira" size="40" maxlength="3" value="121" /></td>
  <td class="text">D&iacute;gitos: <sup>ag / conta</sup></td>
  <td>
    <input name="agencia_dv" type="text" id="agencia_dv" size="2" maxlength="1" style="width:38px !important" />
    <input name="conta_dv" type="text" id="conta_dv" size="2" maxlength="1" style="width:38px !important" />
  </td>
</tr>
<tr class="show_asa">
  <td class="text"><span class="obr">!</span>Nosso n&uacute;mero de:</td>
  <td><input name="faixa_inicio" type="text" id="faixa_inicio" size="40" maxlength="10" /></td>
  <td class="text"><span class="obr">!</span>at&eacute;:</td>
  <td><input name="faixa_fim" type="text" id="faixa_fim" size="40" maxlength="10" /></td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
</tr>
```

- [ ] **Step 3: Persistir os campos novos**

`proc_add_conta.php` está em ISO-8859-1 — preservar o encoding do arquivo ao editar. Após a linha `$acessorio = limpa($_POST["acessorio"]);`, acrescentar:

```php
$operacao     = limpa($_POST["operacao"]);
$carteira     = limpa($_POST["carteira"]);
$faixa_inicio = (int) $_POST["faixa_inicio"];
$faixa_fim    = (int) $_POST["faixa_fim"];
$agencia_dv   = limpa($_POST["agencia_dv"]);
$conta_dv     = limpa($_POST["conta_dv"]);
```

Após o bloco de validação existente, acrescentar a validação específica do ASA:

```php
if ( $banco == 'ASA' ) {
	if ( (!$operacao) || (!$carteira) || (!$faixa_inicio) || (!$faixa_fim) || (!$agencia_dv) || (!$conta_dv) ) {
		die("
			<body style=\"color:white;background-color:#A21A24;font-family:verdana  \">
				<h2 style=\"display:inline \">OPPS!!</h2>:<b>DADOS DO ASA INCOMPLETOS</B><br />
				<span style=\"font-size:8pt\">Operacao, carteira, digitos e faixa de nosso numero sao obrigatorios</span>"
		);
	}
	if ( $faixa_fim < $faixa_inicio ) {
		die("
			<body style=\"color:white;background-color:#A21A24;font-family:verdana  \">
				<h2 style=\"display:inline \">OPPS!!</h2>:<b>FAIXA DE NOSSO NUMERO INVALIDA</B><br />
				<span style=\"font-size:8pt\">O numero final deve ser maior ou igual ao inicial</span>"
		);
	}
}
```

Substituir o `INSERT` pela versão com as colunas novas:

```php
$sql = "INSERT INTO contas (id, razao, cnpj, agencia, conta, data, info, banco, acessorio, operacao, carteira, faixa_inicio, faixa_fim, agencia_dv, conta_dv) values (NULL,'$razao','$cnpj','$agencia','$conta','$data','$info', '$banco', '$acessorio', '$operacao', '$carteira', '$faixa_inicio', '$faixa_fim', '$agencia_dv', '$conta_dv');";
```

- [ ] **Step 4: Verificar a sintaxe PHP**

```bash
php -l proc_add_conta.php
```

Esperado: `No syntax errors detected`. Sem PHP local, este passo fica para a máquina física.

- [ ] **Step 5: Commit**

```bash
git add db/migrations/2026-07-27-contas-asa.sql tpl/tpl_form_add_conta.html proc_add_conta.php
git commit -m "feat(asa): colunas de configuracao da conta e campos no cadastro"
```

- [ ] **Step 6: Registrar a verificação manual pendente**

Esta task não é verificável aqui. Anotar no relatório de entrega, para o usuário executar na máquina física:
1. Rodar a migração no MySQL.
2. Abrir o cadastro de conta, escolher "Banco ASA SCD" e confirmar que os campos de operação, carteira, dígitos e faixa aparecem, e somem ao trocar para outro banco.
3. Cadastrar a conta real: agência `0001`, dígito `9`, conta `600001425`, dígito `8`, operação `0004142`, carteira `121`, faixa conforme o banco informar.

---

### Task 3: Layout e classe do boleto

**Files:**
- Create: `cboleto/include/layout_asa.php`
- Create: `lib/class_boleto_ASA.php`
- Create: `tests/teste_asa_boleto.php`

**Interfaces:**
- Consumes: `asa_calc` (Task 1).
- Produces: classe `boleto_ASA` com a mesma API pública de `boleto_SICOOB`, consumida por `proc_lista_boletos.php` — `init()`, `val($chave, $valor)`, `set($token, $valor)`, `draw()`, `pagina()`, `fim()`, `reset()`, propriedade pública `$layout` e array público `$dadosboleto`. Após `draw()`, `$dadosboleto['nosso_numero_completo']` contém o nosso número com DV e `$dadosboleto['linha_digitavel']` a linha digitável.

- [ ] **Step 1: Criar o layout a partir do Sicoob**

```bash
cp cboleto/include/layout_sicoob.php cboleto/include/layout_asa.php
```

Ajustar em `cboleto/include/layout_asa.php`:
- Trocar a imagem do logotipo (`logosicoob.png`) pelo texto do ASA, já que o kit não traz arquivo de logo: `<span style="font-family:Arial,Helvetica,sans-serif;font-size:26pt;font-weight:bold;letter-spacing:1px">ASA</span>`.
- Trocar toda ocorrência textual de "SICOOB" por "BANCO ASA SCD".
- Remover o token `{{parcela}}` se existir, e qualquer rótulo de parcela — o ASA não usa parcela no nosso número.

Os tokens que o layout deve continuar tendo são exatamente: `{{aceite}}`, `{{agencia_codigo}}`, `{{carteira}}`, `{{cedente}}`, `{{codigo_banco_com_dv}}`, `{{codigo_barras}}`, `{{cpf_cnpj}}`, `{{data_documento}}`, `{{data_processamento}}`, `{{data_vencimento}}`, `{{demonstrativo1}}`, `{{demonstrativo2}}`, `{{demonstrativo3}}`, `{{endereco1}}`, `{{endereco2}}`, `{{especie_doc}}`, `{{especie}}`, `{{identificacao}}`, `{{instrucoes1}}`, `{{instrucoes2}}`, `{{instrucoes3}}`, `{{instrucoes4}}`, `{{linha_digitavel}}`, `{{nosso_numero}}`, `{{numero_documento}}`, `{{quantidade}}`, `{{sacado}}`, `{{valor_boleto}}`, `{{valor_unitario}}`.

- [ ] **Step 2: Escrever o teste que falha**

Arquivo `tests/teste_asa_boleto.php`. Testa que a classe alimenta o layout com os valores certos, usando o mesmo vetor de aceitação:

```php
<?php

require_once dirname(__FILE__) . '/assert.php';
require_once dirname(__FILE__) . '/../../lib/class_asa_calc.php';

// A classe carrega o layout por caminho relativo a raiz do projeto.
chdir(dirname(__FILE__) . '/../..');
require_once 'lib/class_boleto_ASA.php';

echo "boleto_ASA :: renderizacao com o vetor de aceitacao\n";

$b = new boleto_ASA();
$b->val('agencia',          '0001');
$b->val('agencia_dv',       '9');
$b->val('conta',            '600001425');
$b->val('conta_dv',         '8');
$b->val('operacao',         '0001316');
$b->val('carteira',         '121');
$b->val('nosso_numero',     '465280');
$b->val('numero_documento', 'TESTE ASA');
$b->val('valor_boleto',     '20.00');
$b->val('data_vencimento',  '30/05/2025');
$b->val('razao',            'EMPRESA TESTE');
$b->val('cnpj',             '00.000.000/0000-00');
$b->draw();

t_equals('00004652809', $b->dadosboleto['nosso_numero_completo'], 'nosso numero com DV no dadosboleto');
t_equals(
    '59490.00110 21000.131603 00046.528097 5 10970000002000',
    $b->dadosboleto['linha_digitavel'],
    'linha digitavel no dadosboleto'
);

t_equals(0, substr_count($b->layout, '{{'), 'nenhum token nao substituido no layout');
t_equals(true, strpos($b->layout, '0465280-9') !== false, 'nosso numero formatado aparece no layout');
t_equals(true, strpos($b->layout, '0001-9 / 600001425-8') !== false, 'agencia/codigo do beneficiario aparece no layout');
t_equals(true, strpos($b->layout, '594-0') !== false, 'codigo do banco com DV aparece no layout');

t_fim();
```

- [ ] **Step 3: Rodar o teste e confirmar que falha**

```bash
php tests/teste_asa_boleto.php
```

Esperado: `Class 'boleto_ASA' not found`.

- [ ] **Step 4: Implementar `boleto_ASA`**

Arquivo `lib/class_boleto_ASA.php`. O desenho do código de barras (`_fbarcode`, `esquerda`, `direita`) é copiado de `class_boleto_SICOOB.php` linhas 239-324, sem alteração — aqueles métodos são `private` lá e não podem ser reaproveitados por herança nem por chamada:

```php
<?php

class boleto_ASA {

    const inst1 = "Sr. Caixa, apos vencimento cobrar multa de 2%";
    const inst2 = "Nao receber apos 30 dias do vencimento.";
    const inst3 = "";
    const inst4 = "";

    public $layout;
    public $layout_original;
    public $dadosboleto = array();
    public $imprimir;

    public function __construct() {
        $this->load_layout();
    }

    private function load_layout() {
        $this->layout_original = @file_get_contents("cboleto/include/layout_asa.php");
        if (strlen($this->layout_original) < 2) {
            die("
            <h1>Classe ASA::</h1>
            Erro ao carregar arquivo do layout do boleto, nao e possivel completar a operacao.<hr />
            $_SERVER[SERVER_SIGNATURE]");
        }
        $this->layout = $this->layout_original;
    }

    public function init() {
        $im = ($this->imprimir) ? "onload='print()'" : "";
        $this->layout = "
        <html><head><title>Boleto - Banco ASA SCD</title></head><body $im>
        " . $this->layout;
    }

    public function val($t, $v) {
        $this->dadosboleto[$t] = $v;
    }

    public function set($t, $v) {
        $this->layout = preg_replace("(\{\{$t\}\})", "$v", $this->layout);
    }

    public function reset() {
        unset($this->layout);
        $this->layout = $this->layout_original;
    }

    public function pagina() {
        $this->layout .= "<p style=\"page-break-after:always;\"></p>";
    }

    public function fim() {
        $this->layout .= "</body></html>";
    }

    private function mil($num) {
        $num = str_replace(",", ".", $num);
        return number_format($num, 2, ",", ".");
    }

    public function draw() {
        $agencia  = str_pad($this->dadosboleto['agencia'], 4, '0', STR_PAD_LEFT);
        $carteira = str_pad($this->dadosboleto['carteira'], 3, '0', STR_PAD_LEFT);
        $operacao = str_pad($this->dadosboleto['operacao'], 7, '0', STR_PAD_LEFT);
        $nn       = $this->dadosboleto['nosso_numero'];

        $dv_nn = asa_calc::dv_nosso_numero($agencia, $carteira, $nn);
        $this->dadosboleto['nosso_numero_completo'] = asa_calc::nosso_numero_com_dv($agencia, $carteira, $nn);

        $campo_livre = asa_calc::campo_livre($agencia, $carteira, $operacao, $nn);
        $fator       = asa_calc::fator_vencimento($this->dadosboleto['data_vencimento']);
        $valor10     = asa_calc::valor_centavos($this->dadosboleto['valor_boleto'], 10);
        $dv_barras   = asa_calc::dv_codigo_barras($campo_livre, $fator, $valor10);

        $codigo_barras = asa_calc::codigo_barras($campo_livre, $fator, $valor10);
        $this->dadosboleto['linha_digitavel'] = asa_calc::linha_digitavel($campo_livre, $fator, $valor10, $dv_barras);

        $nn_exibicao = str_pad($nn, 7, '0', STR_PAD_LEFT) . '-' . $dv_nn;

        $this->set("codigo_banco_com_dv", '594-0');
        $this->set("linha_digitavel", $this->dadosboleto['linha_digitavel']);
        $this->set("codigo_barras", $this->fbarcode($codigo_barras));
        $this->set("agencia_codigo", $agencia . '-' . $this->dadosboleto['agencia_dv']
                                   . ' / ' . $this->dadosboleto['conta'] . '-' . $this->dadosboleto['conta_dv']);
        $this->set("nosso_numero", $nn_exibicao);
        $this->set("carteira", $carteira);
        $this->set("data_vencimento", $this->dadosboleto['data_vencimento']);
        $this->set("data_processamento", date('d/m/Y'));
        $this->set("valor_boleto", $this->mil($this->dadosboleto['valor_boleto']));
        $this->set("cedente", $this->dadosboleto['razao']);
        $this->set("identificacao", $this->dadosboleto['razao']);
        $this->set("cpf_cnpj", $this->dadosboleto['cnpj']);
        $this->set("numero_documento", $this->dadosboleto['numero_documento']);
        $this->set("especie", "R\$");
        $this->set("especie_doc", "DM");
        $this->set("aceite", "N");
        $this->set("quantidade", "");
        $this->set("valor_unitario", "");
        $this->set("demonstrativo3", "");
        $this->set("instrucoes1", self::inst1);
        $this->set("instrucoes2", self::inst2);
        $this->set("instrucoes3", self::inst3);
        $this->set("instrucoes4", self::inst4);
    }

    private function fbarcode($n) {
        ob_start();
        $this->_fbarcode($n);
        return ob_get_clean();
    }

    // _fbarcode(), esquerda() e direita() sao copias literais de
    // lib/class_boleto_SICOOB.php linhas 239-324. Copiar os tres metodos
    // sem qualquer alteracao, trocando apenas a visibilidade se necessario.
}
```

Copiar os métodos `_fbarcode`, `esquerda` e `direita` de `lib/class_boleto_SICOOB.php` para dentro da classe, substituindo o comentário final.

- [ ] **Step 5: Rodar o teste e confirmar que passa**

```bash
php tests/teste_asa_boleto.php
```

Esperado: `6 verificacoes, 0 falha(s)`.

Se a asserção "nenhum token nao substituido" falhar, o layout tem tokens que o `draw()` não preenche — os tokens `demonstrativo1`, `demonstrativo2`, `endereco1`, `endereco2`, `sacado` e `data_documento` são preenchidos por `proc_lista_boletos.php`, não pela classe. Ajustar o teste para preenchê-los antes de `draw()` via `$b->set(...)`, e não remover tokens do layout.

- [ ] **Step 6: Commit**

```bash
git add cboleto/include/layout_asa.php lib/class_boleto_ASA.php tests/teste_asa_boleto.php
git commit -m "feat(asa): layout e classe de emissao do boleto"
```

---

### Task 4: Alocação do nosso número

O ponto de maior risco do plano. Número repetido ou fora da faixa faz o ASA recusar a remessa inteira, e reimpressão não pode consumir número novo.

**Files:**
- Modify: `proc_lista_boletos.php`

**Interfaces:**
- Consumes: `boleto_ASA` (Task 3), colunas de `contas` (Task 2).
- Produces: `boletos.bo_nnum` contendo o nosso número **sem DV** para contas ASA.

- [ ] **Step 1: Adicionar o ramo ASA no dispatch**

Em `proc_lista_boletos.php`, imediatamente antes do `} else {` da linha 63, inserir:

```php
	} else if ( $dados[0] == 'ASA' ) {
		$b = new boleto_ASA();
		$b->init();
		if($db->rows<1)error("Erro ao gerar boletos, dados insufucientes para completar a operação");
		while($d=mysql_fetch_object($db->result)){
			$b->val("razao"      , $d->razao);
			$b->val("cnpj"       , $d->cnpj);
			$b->val("agencia"    , $d->agencia);
			$b->val("agencia_dv" , $d->agencia_dv);
			$b->val("conta"      , $d->conta);
			$b->val("conta_dv"   , $d->conta_dv);
			$b->val("operacao"   , $d->operacao);
			$b->val("carteira"   , $d->carteira);
			$conta_asa = $d;
			$dados[2] = $d->id;
		}
```

- [ ] **Step 2: Alocar o nosso número antes do draw**

Ainda em `proc_lista_boletos.php`, dentro do loop de boletos, **antes** da linha `$b->draw();` (linha 125) e depois de `$b->val("numero_documento",$ndoc);`, inserir:

```php
		if ( $dados[0] == 'ASA' ) {
			// Reimpressao nunca consome numero novo: reusa o que ja foi emitido.
			$cb->reset();
			$cb->query("SELECT bo_nnum FROM boletos WHERE bo_presta = '{$d->id_presta}' AND bo_nnum IS NOT NULL AND bo_nnum <> '' LIMIT 1");
			$nnum_asa = $cb->get_val("bo_nnum");
			$asa_lock = false;

			if ( $nnum_asa == '' ) {
				// Alocacao sob lock: numero duplicado faz o ASA recusar a remessa inteira.
				$cb->reset();
				$cb->query("LOCK TABLES boletos WRITE");
				$asa_lock = true;

				$cb->reset();
				$cb->query("SELECT MAX(CAST(bo_nnum AS UNSIGNED)) AS ultimo FROM boletos WHERE conta_id = ".$dados[2]);
				$ultimo = (int) $cb->get_val("ultimo");

				$nnum_asa = ( $ultimo < (int) $conta_asa->faixa_inicio )
					? (int) $conta_asa->faixa_inicio
					: $ultimo + 1;

				if ( $nnum_asa > (int) $conta_asa->faixa_fim ) {
					$cb->reset();
					$cb->query("UNLOCK TABLES");
					erro("A faixa de nosso numero da conta ASA acabou (limite: {$conta_asa->faixa_fim}). Solicite uma nova faixa ao banco antes de emitir mais boletos.");
				}
			}
			$b->val("nosso_numero", $nnum_asa);
		}
```

**Por que tudo na conexão `$cb`:** no MySQL o `LOCK TABLES` vale para a conexão que o executou, e essa conexão só enxerga as tabelas que travou. O `INSERT` do Step 3 roda em `$cb`, então o lock, o `SELECT MAX` e o `INSERT` têm que estar todos nela — travar numa conexão e inserir por outra faria o `INSERT` bloquear contra o próprio lock. Nenhuma outra conexão toca `boletos` entre o lock e o unlock dentro da mesma iteração.

O `LOCK TABLES` é liberado no Step 3, depois do `INSERT`.

- [ ] **Step 3: Gravar o nosso número sem DV e liberar o lock**

No bloco de inserção (linhas 164-195), o trecho que extrai o `$nnum` por banco (linhas 167-171) passa a ter o caso do ASA. Substituir:

```php
			if ($dados[0] != 'SICOOB') {
				$nnum = substr(str_replace(array('/','-',' '), '', $b->nossonumero), 2);
			} else {
				$nnum = str_replace(array('/','-',' '), '', $b->dadosboleto["nosso_numero_completo"]);
			}
```

por:

```php
			if ($dados[0] == 'ASA') {
				// Gravado SEM o DV: a alocacao usa MAX(bo_nnum)+1 e o DV e recalculado.
				$nnum = $nnum_asa;
			} else if ($dados[0] != 'SICOOB') {
				$nnum = substr(str_replace(array('/','-',' '), '', $b->nossonumero), 2);
			} else {
				$nnum = str_replace(array('/','-',' '), '', $b->dadosboleto["nosso_numero_completo"]);
			}
```

Imediatamente após o `$cb->query($sql);` que faz o `INSERT`, liberar o lock:

```php
			if ( $dados[0] == 'ASA' && $asa_lock ) {
				$cb->reset();
				$cb->query("UNLOCK TABLES");
				$asa_lock = false;
			}
```

No caminho de reimpressão o lock nunca é adquirido (`$asa_lock` fica `false`), então não há o que liberar.

No ramo `else` (boleto já existente), o bloco de atualização das linhas 181-194 usa `$b->nossonumero`, que não existe em `boleto_ASA`. Envolver aquele bloco para não rodar no ASA:

```php
		} else if ( $dados[0] != 'ASA' ) {
```

- [ ] **Step 4: Verificar a sintaxe**

```bash
php -l proc_lista_boletos.php
```

Esperado: `No syntax errors detected`.

- [ ] **Step 5: Commit**

```bash
git add proc_lista_boletos.php
git commit -m "feat(asa): alocacao do nosso numero por faixa na emissao do boleto"
```

- [ ] **Step 6: Registrar a verificação manual pendente**

Anotar no relatório de entrega, para a máquina física:
1. Emitir um boleto para a conta ASA e conferir que `bo_nnum` recebeu exatamente `faixa_inicio`.
2. Emitir um segundo boleto e conferir que recebeu `faixa_inicio + 1`.
3. **Reimprimir o primeiro** e conferir que `bo_nnum` não mudou e que o boleto saiu com o mesmo nosso número.
4. Ajustar `faixa_fim` temporariamente para um valor já consumido e confirmar que a emissão é bloqueada com a mensagem de faixa esgotada, sem gravar boleto.
5. Conferir a linha digitável do boleto impresso contra o cálculo manual da planilha do kit.

---

### Task 5: Registros CNAB 240

**Files:**
- Create: `lib/class_remessa_ASA.php`
- Create: `tests/teste_asa_remessa.php`

**Interfaces:**
- Consumes: `asa_calc` (Task 1).
- Produces: classe `remessa_ASA` com a API consumida por `proc_lista_remessa.php` — `header($data)`, `registro($data)`, `trailer($data)`. Métodos internos retornando string, expostos para teste: `header_arquivo($data)`, `header_lote($data)`, `segmento_p($data)`, `segmento_q($data)`, `segmento_r($data)`, `trailer_lote()`, `trailer_arquivo()`, `identificacao_titulo($carteira, $agencia, $nosso_numero)`.

- [ ] **Step 1: Escrever o teste que falha**

Arquivo `tests/teste_asa_remessa.php`:

```php
<?php

require_once dirname(__FILE__) . '/assert.php';
require_once dirname(__FILE__) . '/../../lib/class_asa_calc.php';

chdir(dirname(__FILE__) . '/../..');
require_once 'lib/func.php';
require_once 'lib/class_remessa_ASA.php';

$data = array(
    'documento'                => '00.000.000/0000-00',
    'nome_empresa'             => 'EMPRESA TESTE',
    'agencia'                  => '0001',
    'agencia_dv'               => '9',
    'conta_corrente'           => '600001425',
    'conta_dv'                 => '8',
    'operacao'                 => '0004142',
    'carteira'                 => '121',
    'numero_sequencia_remessa' => '1',
    'valor_titulo'             => '20.00',
    'data_vencimento_titulo'   => '2025-05-30',
    'data_emissao_titulo'      => '2025-04-10',
    'numero_documento'         => 'TESTE ASA',
    'nosso_numero'             => '465280',
    'documento_pagador'        => '111.222.333-44',
    'nome_pagador'             => 'PAGADOR TESTE',
    'endereco_pagador'         => 'RUA TESTE - 100',
    'bairro_pagador'           => 'CENTRO',
    'cidade_pagador'           => 'SAO PAULO',
    'estado_pagador'           => 'SP',
    'cep_pagador'              => '01000-000',
);

echo "remessa_ASA :: largura dos registros\n";

$r = new remessa_ASA();
$r->header($data);

$registros = array(
    'header de arquivo' => $r->header_arquivo($data),
    'header de lote'    => $r->header_lote($data),
    'segmento P'        => $r->segmento_p($data),
    'segmento Q'        => $r->segmento_q($data),
    'segmento R'        => $r->segmento_r($data),
    'trailer de lote'   => $r->trailer_lote(),
    'trailer de arquivo'=> $r->trailer_arquivo(),
);
foreach ($registros as $nome => $linha) {
    t_equals(240, strlen($linha), "$nome tem 240 posicoes");
}

echo "\nremessa_ASA :: posicoes fixas\n";

$p = $r->segmento_p($data);
t_equals('594', substr($p, 0, 3),    'P: banco nas posicoes 1-3');
t_equals('3',   substr($p, 7, 1),    'P: tipo de registro na posicao 8');
t_equals('P',   substr($p, 13, 1),   'P: codigo do segmento na posicao 14');
t_equals('01',  substr($p, 15, 2),   'P: movimento entrada de titulos nas posicoes 16-17');
t_equals('00001', substr($p, 17, 5), 'P: agencia nas posicoes 18-22');
t_equals('9',   substr($p, 22, 1),   'P: DV da agencia na posicao 23');
t_equals('000600001425', substr($p, 23, 12), 'P: conta nas posicoes 24-35');
t_equals('8',   substr($p, 35, 1),   'P: DV da conta na posicao 36');
t_equals('2',   substr($p, 57, 1),   'P: carteira cobranca vinculada na posicao 58');
t_equals('2',   substr($p, 60, 1),   'P: emissao pelo cliente na posicao 61');
t_equals('30052025', substr($p, 77, 8), 'P: vencimento nas posicoes 78-85');
t_equals('000000000002000', substr($p, 85, 15), 'P: valor nas posicoes 86-100');
t_equals('02',  substr($p, 106, 2),  'P: especie DM nas posicoes 107-108');
t_equals('2',   substr($p, 117, 1),  'P: juros taxa mensal na posicao 118');
t_equals('000000000000100', substr($p, 126, 15), 'P: juros de 1,00% nas posicoes 127-141');
t_equals('3',   substr($p, 220, 1),  'P: nao protestar na posicao 221');
t_equals('09',  substr($p, 227, 2),  'P: moeda real nas posicoes 228-229');

$q = $r->segmento_q($data);
t_equals('Q',   substr($q, 13, 1),   'Q: codigo do segmento na posicao 14');
t_equals('1',   substr($q, 17, 1),   'Q: tipo de inscricao CPF na posicao 18');
t_equals('01000', substr($q, 128, 5),'Q: CEP nas posicoes 129-133');
t_equals('000', substr($q, 209, 3), 'Q: banco correspondente zerado nas posicoes 210-212');

$s = $r->segmento_r($data);
t_equals('R',   substr($s, 13, 1),   'R: codigo do segmento na posicao 14');
t_equals('2',   substr($s, 65, 1),   'R: multa percentual na posicao 66');
t_equals('000000000000200', substr($s, 74, 15), 'R: multa de 2,00% nas posicoes 75-89');

echo "\nremessa_ASA :: identificacao do titulo (posicoes 38-57)\n";

t_equals(20, strlen($r->identificacao_titulo('121', '0001', '465280')), 'identificacao do titulo tem 20 posicoes');
t_equals('12100000000004652809', $r->identificacao_titulo('121', '0001', '465280'), 'particao Bradesco');

t_fim();
```

Nota sobre a partição Bradesco: produto `121` (3) + zeros (5) + nosso número sem DV com 11 posições `00000465280` (11) + DV `9` (1) = `121` + `00000` + `00000465280` + `9` = `12100000000004652809`, 20 caracteres.

- [ ] **Step 2: Rodar o teste e confirmar que falha**

```bash
php tests/teste_asa_remessa.php
```

Esperado: `Class 'remessa_ASA' not found`.

- [ ] **Step 3: Implementar `remessa_ASA`**

Arquivo `lib/class_remessa_ASA.php`. As funções `minimo()`, `vazios()`, `zeros()` e `unmask()` vêm de `lib/func.php`, como nas outras classes de remessa:

```php
<?php

include_once 'lib/file.php';

/**
 * Remessa CNAB 240 do Banco ASA SCD (594), no layout Bradesco 240
 * (versao de arquivo 084, versao de lote 042).
 */
class remessa_ASA {

    const BANCO = '594';
    const LOTE  = '0001';

    /**
     * Particao das posicoes 38-57 do segmento P.
     * 'BRADESCO' = produto(3) + zeros(5) + nosso numero(11) + DV(1)
     * 'ASA'      = 5(1) + zeros(3) + zeros(2) + carteira(3) + nosso numero com DV(11)
     * Os dois manuais se contradizem nessa faixa; ver secao 8.1 do design.
     * Trocar esta constante e a unica mudanca necessaria para testar a outra leitura.
     */
    const IDENTIFICACAO_TITULO = 'BRADESCO';

    const MENSAGEM_1 = 'Apos vencimento, cobrar multa de 2%';
    const MENSAGEM_2 = 'Nao receber apos 30 dias do vencimento.';

    public $header;
    public $registros;
    public $txt;

    private $sequencial_lote      = 0;
    private $quantidade_titulos   = 0;
    private $valor_total          = 0;
    private $dados;

    public function reset() {
        unset($this->header);
        unset($this->registros);
        unset($this->txt);
    }

    public function identificacao_titulo($carteira, $agencia, $nosso_numero) {
        $dv = asa_calc::dv_nosso_numero($agencia, $carteira, $nosso_numero);
        if (self::IDENTIFICACAO_TITULO == 'ASA') {
            return '5' . zeros(3) . zeros(2)
                 . str_pad($carteira, 3, '0', STR_PAD_LEFT)
                 . str_pad($nosso_numero, 10, '0', STR_PAD_LEFT) . $dv;
        }
        return str_pad($carteira, 3, '0', STR_PAD_LEFT)
             . zeros(5)
             . str_pad($nosso_numero, 11, '0', STR_PAD_LEFT)
             . $dv;
    }

    public function header_arquivo($data) {
        return self::BANCO                                        // 1-3
             . '0000'                                             // 4-7
             . '0'                                                // 8
             . vazios(9)                                          // 9-17
             . '2'                                                // 18   CNPJ
             . minimo(unmask($data['documento']), 14, 1)          // 19-32
             . zeros(20)                                          // 33-52  convenio
             . minimo($data['agencia'], 5, 1)                     // 53-57
             . minimo($data['agencia_dv'], 1)                     // 58
             . minimo($data['conta_corrente'], 12, 1)             // 59-70
             . minimo($data['conta_dv'], 1)                       // 71
             . ' '                                                // 72
             . minimo($data['nome_empresa'], 30)                  // 73-102
             . minimo('BANCO ASA SCD', 30)                        // 103-132
             . vazios(10)                                         // 133-142
             . '1'                                                // 143  remessa
             . date('dmY')                                        // 144-151
             . date('His')                                        // 152-157
             . minimo($data['numero_sequencia_remessa'], 6, 1)    // 158-163
             . '084'                                              // 164-166
             . zeros(5)                                           // 167-171
             . vazios(20)                                         // 172-191
             . vazios(20)                                         // 192-211
             . vazios(29);                                        // 212-240
    }

    public function header_lote($data) {
        return self::BANCO                                        // 1-3
             . self::LOTE                                         // 4-7
             . '1'                                                // 8
             . 'R'                                                // 9
             . '01'                                               // 10-11
             . vazios(2)                                          // 12-13
             . '042'                                              // 14-16
             . ' '                                                // 17
             . '2'                                                // 18
             . minimo(unmask($data['documento']), 15, 1)          // 19-33
             . zeros(20)                                          // 34-53  convenio
             . minimo($data['agencia'], 5, 1)                     // 54-58
             . minimo($data['agencia_dv'], 1)                     // 59
             . minimo($data['conta_corrente'], 12, 1)             // 60-71
             . minimo($data['conta_dv'], 1)                       // 72
             . ' '                                                // 73
             . minimo($data['nome_empresa'], 30)                  // 74-103
             . minimo(self::MENSAGEM_1, 40)                       // 104-143
             . minimo(self::MENSAGEM_2, 40)                       // 144-183
             . minimo($data['numero_sequencia_remessa'], 8, 1)    // 184-191
             . date('dmY')                                        // 192-199
             . zeros(8)                                           // 200-207
             . vazios(33);                                        // 208-240
    }

    private function prefixo_detalhe($segmento) {
        return self::BANCO                                        // 1-3
             . self::LOTE                                         // 4-7
             . '3'                                                // 8
             . minimo($this->sequencial_lote, 5, 1)               // 9-13
             . $segmento                                          // 14
             . ' '                                                // 15
             . '01';                                              // 16-17
    }

    public function segmento_p($data) {
        $vencimento = date('dmY', strtotime($data['data_vencimento_titulo']));
        $juros_em   = date('dmY', strtotime($data['data_vencimento_titulo'] . ' +1 day'));

        return $this->prefixo_detalhe('P')                        // 1-17
             . minimo($data['agencia'], 5, 1)                     // 18-22
             . minimo($data['agencia_dv'], 1)                     // 23
             . minimo($data['conta_corrente'], 12, 1)             // 24-35
             . minimo($data['conta_dv'], 1)                       // 36
             . ' '                                                // 37
             . $this->identificacao_titulo($data['carteira'], $data['agencia'], $data['nosso_numero']) // 38-57
             . '2'                                                // 58  cobranca vinculada
             . '1'                                                // 59  com cadastramento
             . '2'                                                // 60  tipo de documento
             . '2'                                                // 61  cliente emite
             . '2'                                                // 62  cliente distribui
             . minimo($data['numero_documento'], 15)              // 63-77
             . $vencimento                                        // 78-85
             . asa_calc::valor_centavos($data['valor_titulo'], 15)// 86-100
             . zeros(5)                                           // 101-105 agencia cobradora
             . ' '                                                // 106
             . '02'                                               // 107-108 DM
             . 'N'                                                // 109 aceite
             . date('dmY', strtotime($data['data_emissao_titulo']))// 110-117
             . '2'                                                // 118 juros: taxa mensal
             . $juros_em                                          // 119-126
             . minimo('100', 15, 1)                               // 127-141 1,00%
             . '0'                                                // 142 sem desconto
             . zeros(8)                                           // 143-150
             . zeros(15)                                          // 151-165
             . zeros(15)                                          // 166-180 IOF
             . zeros(15)                                          // 181-195 abatimento
             . minimo($data['numero_documento'], 25)              // 196-220
             . '3'                                                // 221 nao protestar
             . '00'                                               // 222-223
             . '1'                                                // 224 baixar/devolver
             . '030'                                              // 225-227
             . '09'                                               // 228-229 real
             . zeros(10)                                          // 230-239 numero do contrato
             . ' ';                                               // 240
    }

    public function segmento_q($data) {
        $cep  = minimo(unmask($data['cep_pagador']), 8, 1);
        $tipo = strlen(unmask($data['documento_pagador'])) > 11 ? '2' : '1';

        return $this->prefixo_detalhe('Q')                        // 1-17
             . $tipo                                              // 18
             . minimo(unmask($data['documento_pagador']), 15, 1)  // 19-33
             . minimo($data['nome_pagador'], 40)                  // 34-73
             . minimo($data['endereco_pagador'], 40)              // 74-113
             . minimo($data['bairro_pagador'], 15)                // 114-128
             . substr($cep, 0, 5)                                 // 129-133
             . substr($cep, 5, 3)                                 // 134-136
             . minimo($data['cidade_pagador'], 15)                // 137-151
             . minimo($data['estado_pagador'], 2)                 // 152-153
             . '0'                                                // 154 sem beneficiario final
             . zeros(15)                                          // 155-169
             . vazios(40)                                         // 170-209
             . zeros(3)                                           // 210-212 desvio ASA
             . vazios(20)                                         // 213-232
             . vazios(8);                                         // 233-240
    }

    public function segmento_r($data) {
        $multa_em = date('dmY', strtotime($data['data_vencimento_titulo'] . ' +1 day'));

        return $this->prefixo_detalhe('R')                        // 1-17
             . '0'                                                // 18 sem desconto 2
             . zeros(8)                                           // 19-26
             . zeros(15)                                          // 27-41
             . '0'                                                // 42 sem desconto 3
             . zeros(8)                                           // 43-50
             . zeros(15)                                          // 51-65
             . '2'                                                // 66 multa percentual
             . $multa_em                                          // 67-74
             . minimo('200', 15, 1)                               // 75-89 2,00%
             . vazios(10)                                         // 90-99
             . vazios(40)                                         // 100-139 mensagem 3
             . vazios(40)                                         // 140-179 mensagem 4
             . vazios(20)                                         // 180-199
             . zeros(8)                                           // 200-207
             . zeros(3)                                           // 208-210
             . zeros(5)                                           // 211-215
             . ' '                                                // 216
             . zeros(12)                                          // 217-228
             . ' '                                                // 229
             . ' '                                                // 230
             . '0'                                                // 231
             . vazios(9);                                         // 232-240
    }

    public function trailer_lote() {
        // header de lote + detalhes + o proprio trailer
        $quantidade_registros = $this->sequencial_lote + 2;

        return self::BANCO                                        // 1-3
             . self::LOTE                                         // 4-7
             . '5'                                                // 8
             . vazios(9)                                          // 9-17
             . minimo($quantidade_registros, 6, 1)                // 18-23
             . zeros(6)                                           // 24-29  simples
             . zeros(17)                                          // 30-46
             . minimo($this->quantidade_titulos, 6, 1)            // 47-52  vinculada
             . asa_calc::valor_centavos($this->valor_total, 17)   // 53-69
             . zeros(6)                                           // 70-75  caucionada
             . zeros(17)                                          // 76-92
             . zeros(6)                                           // 93-98  descontada
             . zeros(17)                                          // 99-115
             . zeros(8)                                           // 116-123
             . vazios(117);                                       // 124-240
    }

    public function trailer_arquivo() {
        // header de arquivo + header de lote + detalhes + trailer de lote + este
        $total_registros = $this->sequencial_lote + 4;

        return self::BANCO                                        // 1-3
             . '9999'                                             // 4-7
             . '9'                                                // 8
             . vazios(9)                                          // 9-17
             . minimo('1', 6, 1)                                  // 18-23 um lote
             . minimo($total_registros, 6, 1)                     // 24-29
             . zeros(6)                                           // 30-35
             . vazios(205);                                       // 36-240
    }

    public function header($data) {
        $this->dados     = $data;
        $this->registros = '';
        $this->header    = $this->header_arquivo($data) . "\r\n" . $this->header_lote($data);
        return $this->header;
    }

    public function registro($data) {
        $this->sequencial_lote++;
        $p = $this->segmento_p($data);
        $this->sequencial_lote++;
        $q = $this->segmento_q($data);
        $this->sequencial_lote++;
        $r = $this->segmento_r($data);

        $this->quantidade_titulos++;
        $this->valor_total += (float) $data['valor_titulo'];

        $this->registros .= $p . "\r\n" . $q . "\r\n" . $r . "\r\n";
        return $this->registros;
    }

    public function trailer($data) {
        $txt = $this->header . "\r\n" . $this->registros
             . $this->trailer_lote() . "\r\n" . $this->trailer_arquivo();

        $filename = 'asa_' . date('dmYHi') . '.rem';
        $path     = 'remessa_arquivo/';
        $file     = new file();
        $file->save($txt, $path . $filename);
        $file->prepare($path, $filename);
        exit;
    }
}
```

- [ ] **Step 4: Rodar o teste e confirmar que passa**

```bash
php tests/teste_asa_remessa.php
```

Esperado: `33 verificacoes, 0 falha(s)`.

Se alguma largura der diferente de 240, somar os tamanhos dos campos do registro em questão contra o mapa de posições da seção 4 do design — o erro é sempre um campo com tamanho errado, nunca a asserção.

- [ ] **Step 5: Commit**

```bash
git add lib/class_remessa_ASA.php tests/teste_asa_remessa.php
git commit -m "feat(asa): registros CNAB 240 da remessa no layout Bradesco"
```

---

### Task 6: Ligação da remessa e verificação final

**Files:**
- Modify: `proc_lista_remessa.php`

**Interfaces:**
- Consumes: `remessa_ASA` (Task 5), colunas de `contas` (Task 2), `boletos.bo_nnum` (Task 4).
- Produces: arquivo `remessa_arquivo/asa_ddmmYYYYHHii.rem`.

- [ ] **Step 1: Adicionar o ramo ASA no dispatch**

Em `proc_lista_remessa.php`, antes do `} else {` da linha 18, inserir:

```php
	} else if ( $_SESSION['banco'] == 'ASA' ) {
		$b = new remessa_ASA();
```

- [ ] **Step 2: Carregar os dados da conta específicos do ASA**

Após a linha `$data['numero_sequencia_registro'] = 1;` (linha 43), inserir:

```php
	$data['agencia_dv'] = $row['agencia_dv'];
	$data['conta_dv']   = $row['conta_dv'];
	$data['operacao']   = $row['operacao'];
	$data['carteira']   = $row['carteira'];
```

- [ ] **Step 3: Mapear os campos do pagador**

No bloco por banco das linhas 69-84, antes do fechamento do `if/else`, inserir o ramo do ASA:

```php
		} else if ( $_SESSION['banco'] == 'ASA' ) {
			$data['data_vencimento_titulo'] = $d->bo_data_vence;
			$data['endereco_pagador'] = $d->cli_rua.' - '.$d->cli_numero;
			$data['cidade_pagador']   = $d->cli_cidade;
			$data['bairro_pagador']   = $d->cli_bairro;
			$data['estado_pagador']   = $d->cli_estado;
			$data['nosso_numero']     = $d->bo_nnum;
		}
```

O campo `nosso_numero` é lido de `bo_nnum`, que para o ASA está gravado **sem DV** — a classe recalcula o dígito.

- [ ] **Step 4: Verificar a sintaxe**

```bash
php -l proc_lista_remessa.php
```

Esperado: `No syntax errors detected`.

- [ ] **Step 5: Rodar toda a bateria de testes**

```bash
php tests/teste_asa_calc.php && \
php tests/teste_asa_boleto.php && \
php tests/teste_asa_remessa.php && \
echo "TODOS OS TESTES PASSARAM"
```

Esperado: `TODOS OS TESTES PASSARAM`.

- [ ] **Step 6: Commit**

```bash
git add proc_lista_remessa.php
git commit -m "feat(asa): geracao do arquivo de remessa"
```

- [ ] **Step 7: Registrar a verificação manual pendente**

Anotar no relatório de entrega, para a máquina física:
1. Gerar uma remessa com dois ou três boletos da conta ASA.
2. Conferir com um editor que **toda** linha do arquivo tem exatamente 240 caracteres.
3. Conferir que o nosso número no segmento P bate com o `bo_nnum` gravado, mais o DV.
4. Conferir que o trailer de lote soma a quantidade e o valor corretos no bloco de Cobrança Vinculada (posições 47-69), e que o bloco de Cobrança Simples (24-46) está zerado.
5. **Antes de qualquer envio em produção**, submeter o arquivo à homologação do ASA e confirmar as três pendências da seção 8 do design: faixa de nosso número, partição das posições 38-57 e destino do número de operação.

---

## Pendências que este plano não resolve

Estão documentadas na seção 8 do design e permanecem abertas ao fim da implementação:

1. **Partição das posições 38-57 do segmento P.** Implementada com a leitura do Bradesco; a constante `remessa_ASA::IDENTIFICACAO_TITULO` alterna para a leitura do ASA.
2. **Faixa de nosso número.** O código a exige e a valida, mas os valores vêm do banco.
3. **Número da operação na remessa.** Suspenso por decisão do usuário; os campos candidatos (convênio e número do contrato) ficam zerados.
