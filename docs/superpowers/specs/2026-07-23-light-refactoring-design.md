# Light Refactoring — Design

**Data:** 2026-07-23
**Branch:** `feature/light-refactoring`
**Escopo:** Itens 3, 4, 5, 6, 7 da análise inicial. Segurança (auth, remoção de artefatos suspeitos) fica para um segundo momento — o app roda isolado da internet, em máquina física.

## Contexto e restrições

- Sistema PHP legado (~2009) de gestão de boletos e prestações de condomínio/imóveis.
- **PHP 5.3 fixo** — não é possível alterar infraestrutura. Todo código novo deve ser **sintaxe-compatível com 5.3**: usar `array()` (não `[]`), sem traits, sem `finally`, sem `::class`, sem variadics `...`. Closures são permitidas (existem no 5.3). PDO está disponível (desde 5.1).
- **Sem ambiente PHP nesta máquina de desenvolvimento** — validação final é feita pelo usuário na máquina física. Compensar com passos pequenos, reversíveis e revisão cuidadosa.
- Estratégia geral aprovada: **in-place incremental** (mantém 1 arquivo por página, template engine próprio, `class_db`).
- Superfície legada: **`mysql_*` é chamado diretamente em 40 dos 61 arquivos** (48× `mysql_fetch_object`, 10× `fetch_array`, 11× `mysql_result`), sobre `$db->result`. Isso proíbe "trocar a `class_db` e pronto".

## Ordem de execução (menor risco → maior)

1. **Item 5** — schema/SQL (isolado)
2. **Item 3** — PDO + shim de compatibilidade
3. **Item 4** — escape de saída
4. **Item 7** — CSS/frontend
5. **Item 6** — limpeza (por último, para não remover referência ainda em uso)

---

## Item 3 — Migração para PDO sem quebrar os 40 arquivos

**Abordagem: PDO por dentro + camada de compatibilidade (shim).**

1. Reescrever `lib/class_db.php` para conectar via **PDO** (MySQL, charset utf8), mantendo a **mesma API pública**: `query()`, `->result`, `->status`, `->erro`, `->lastid`, `->affect`, `->rows`, `reset()`, `transact()`, `lock()`, `unlock()`, `get_val()`.
2. `query($sql, $params = array())` ganha 2º parâmetro opcional:
   - Com `$params` → **prepared statement** (`prepare` + `execute`).
   - Sem `$params` → `query()` direto (compatível com todas as chamadas atuais).
3. `->result` passa a ser um objeto **`DbResult`** que guarda as linhas (`fetchAll`) + um cursor interno. Motivo: PDO tem cursor forward-only e não expõe `data_seek`; materializar em array resolve `data_seek`, `num_rows`, `result($i,$f)` trivialmente.
4. Novo `lib/mysql_compat.php` **redefine** as funções `mysql_*` usadas, operando sobre `DbResult`:
   - `mysql_fetch_object`, `mysql_fetch_array`, `mysql_fetch_assoc` → avançam o cursor.
   - `mysql_result($res,$i,$f)` → indexação no array.
   - `mysql_data_seek($res,$i)` → reposiciona o cursor.
   - `mysql_num_rows`/`mysql_numrows`, `mysql_error`, `mysql_errno`, `mysql_insert_id`, `mysql_affected_rows`, `mysql_free_result` → delegam ao estado do `DbResult`/PDO.
   - **Resultado: os 40 arquivos continuam funcionando sem edição.**
5. Migração cirúrgica: só os `proc_*.php` que recebem `$_GET`/`$_POST` passam a usar `query($sql, $params)` com prepared statements (onde injeção importa). Loops de leitura seguem via shim.

**Nota de escopo (YAGNI):** no PHP 5.3 congelado, `mysql_*` ainda funciona e não é deprecado nessa versão. O ganho imediato do item 3 é **eliminar injeção nas escritas e limpar a base**, não destravar PHP moderno. Por isso **não** reescrever os 40 loops de leitura — mantê-los via shim é o custo/benefício correto.

**Definições / interfaces:**
- `db::query($sql, array $params = array())` — executa; popula `->result` (DbResult), `->status`, `->erro`, `->lastid`, `->affect`, `->rows`.
- `DbResult` — `rows` (array de linhas assoc), cursor; métodos internos para o shim. Não consumido diretamente pelas páginas (elas usam o shim `mysql_*`).
- `lib/mysql_compat.php` — incluído cedo (via autoload/`var.php`) para que as funções existam antes de qualquer chamada.

**Risco/validação:** manter assinaturas idênticas; testar na máquina física página a página. Ponto de atenção: `mysql_fetch_array` vs `mysql_fetch_object` devolvem formatos diferentes — o shim deve respeitar cada um (array numérico+assoc para `fetch_array`, objeto para `fetch_object`).

---

## Item 4 — Escape de saída (XSS / dados quebrados)

- Helper `h($s)` em `lib/func.php`: `htmlspecialchars($s, ENT_QUOTES, 'UTF-8')`.
- Aplicar **apenas em conteúdo dinâmico** impresso no HTML (nome, obs, endereço, e-mail, valores de formulário). Não escapar HTML legítimo dos templates.
- Foco nos pontos onde dados do banco/entrada vão direto ao HTML.

---

## Item 5 — Schema + queries por data

- **Índices** (script `ALTER TABLE` idempotente, aplicável no banco existente — não recria tabelas):
  - `prestacoes(pr_vencimento)`, `prestacoes(pr_apto)`, `prestacoes(pr_venda)`
  - `boletos(bo_presta)`
  - `aptos(ap_ed)`, `aptos(ap_prop)`
- **Reescrever filtros de data**: substituir `day()/month()/year(pr_vencimento)` no WHERE por **range** (`pr_vencimento BETWEEN '<início>' AND '<fim>'`) em `index.php` e `lista-mes-atual*.php`, permitindo uso de índice. Resultado visual idêntico.
- Entregar o script de índices como arquivo separado (ex.: `db/migrations/2026-07-23-indices.sql`) para o usuário aplicar na máquina.

---

## Item 6 — Limpeza (duplicatas óbvias) + encoding

- **Antes de apagar, verificar referências** (grep por include/require e por links). Candidatos (removidos só se não referenciados):
  - `proc_venda_apto antigo.php`, `proc_venda_apto_velho.php`, `proc_venda_apto_vis.php`, `qproc_venda_apto_vis.php`
  - `lista-mes-atual-antigo.php`
  - `noname1.html`, `noname2.html`, `jquery UI theme test.html`, `demo click menu.html`, `blank.html`
- Nada em dúvida é removido. Git preserva histórico.
- **Encoding**: converter arquivos ISO-8859 para UTF-8 (ex.: `proc_add_cli.php`) e corrigir acentos quebrados. A conexão já usa `UTF8`; isso alinha código e banco.

---

## Item 7 — Frontend (modernizar mantendo identidade)

- Consolidar CSS em `css/app.css`: **manter a identidade** (cores/estrutura reconhecíveis), tornar **responsivo**, remover estilos inline, melhorar legibilidade de tabelas e formulários.
- **Sem alterar o fluxo de telas** — o operador reconhece o sistema.

---

## Fora de escopo (segundo momento)

- Autenticação/login e guard de sessão.
- Remoção de artefatos suspeitos (`secretsenhas.txt`, `aut_nat.php`, `lib_aut.lib`, `sandbox/`) e limpeza do histórico git.
- Rotação de credenciais / mover credenciais para fora do repo.
- Upgrade de versão de PHP.
