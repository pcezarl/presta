# Rodando a aplicação localmente

Ambiente de desenvolvimento em Docker. Não tem relação com o servidor de produção, que continua sendo o XAMPP com PHP 5.3 no PC antigo.

## Por que Docker e não o PHP do sistema

A aplicação **não roda em PHP 7 ou 8**. Ela depende de coisas removidas nessas versões:

| Recurso | Removido em | Arquivos que usam |
|---|---|---|
| `mysql_*` (ext/mysql) | PHP 7.0 | 12 |
| `ereg` / `eregi` | PHP 7.0 | 3 |
| `split` | PHP 7.0 | 2 |
| `each` | PHP 8.0 | 6 |
| `__autoload` | PHP 8.0 | `lib/var.php` |

A imagem usada é `php:5.6-apache` — a mais antiga ainda publicada oficialmente. Não é exatamente o 5.3 de produção, mas mantém tudo que o código precisa. Se o editor acusar erro em `__autoload`, é o linter avaliando com PHP 8; em 5.6 funciona.

## Pré-requisito

[Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado. As imagens são `linux/amd64` e rodam emuladas no Apple Silicon — funciona, só sobe mais devagar na primeira vez.

## Subindo

```bash
docker compose up -d          # a primeira vez baixa as imagens e cria o banco
docker compose logs -f db     # acompanhe até ver "ready for connections"
```

Depois: **http://localhost:8080**

Não há tela de login — o `index.php` abre direto no painel.

O MySQL fica exposto em `localhost:3307` (não 3306, para não colidir com um MySQL que você já tenha), usuário `root`, senha `zaq1xsw2`, banco `teste`.

## O que é criado automaticamente

Na primeira subida, três arquivos rodam em ordem:

1. `db/init/01-schema.sql` — o schema atual do sistema
2. `db/migrations/2026-07-27-contas-asa.sql` — a migração do ASA (é o arquivo real, então subir o ambiente já valida a migração)
3. `db/init/03-seed.sql` — dados de exemplo

O seed já traz a **conta do ASA cadastrada com os dados reais de liberação** (agência `0001-9`, conta `600001425-8`, operação `0004142`, carteira `121`, faixa `0007862083`–`0007877082`), uma conta Bradesco para conferir que o caminho antigo não quebrou, um edifício, três clientes, três apartamentos e cinco prestações em aberto no mês corrente.

> O `tabelas.sql` da raiz **está desatualizado** — não tem a tabela `contas` nem as colunas `boletos.conta_id` e `boletos.remessa_id`. Não use ele nem o `instalar.php` para criar o banco; use o `db/init/01-schema.sql`.

## Recriando o banco do zero

```bash
docker compose down -v && docker compose up -d
```

O `-v` apaga o volume; sem ele os scripts de init não rodam de novo.

## Roteiro de teste do ASA

1. **Cadastro de conta** — `http://localhost:8080/add_conta.php`. Escolha "Banco ASA SCD" e confirme que os campos de operação, carteira, dígitos e faixa aparecem, e somem ao trocar para outro banco.
2. **Emitir boletos** — `http://localhost:8080/lista-boletos.php`. Selecione as prestações, escolha a conta ASA e gere. Confira no banco:

   ```bash
   docker compose exec db mysql -uroot -pzaq1xsw2 teste \
     -e "SELECT id_boleto, bo_presta, bo_ndoc, bo_nnum, conta_id, remessa_id FROM boletos ORDER BY id_boleto;"
   ```

   O primeiro `bo_nnum` deve ser **7862083** (início da faixa) e o seguinte **7862084**.
3. **Reimprimir** — gere o mesmo boleto de novo e confirme que `bo_nnum` **não mudou**. Reimpressão não pode consumir número novo.
4. **Faixa esgotada** — force o limite e confirme que a emissão é bloqueada sem gravar boleto:

   ```bash
   docker compose exec db mysql -uroot -pzaq1xsw2 teste \
     -e "UPDATE contas SET faixa_fim = 7862083 WHERE banco='ASA';"
   ```

   Depois reverta para `7877082`.
5. **Gerar remessa** — `http://localhost:8080/lista-remessa.php`. O arquivo sai em `remessa_arquivo/asa_*.rem`. Confira que toda linha tem 240 posições:

   ```bash
   awk '{ gsub(/\r/,""); if (length($0) != 240) print "LINHA " NR " TEM " length($0) }' remessa_arquivo/asa_*.rem
   ```

   Sem saída significa que está tudo certo.
6. **Não regrediu** — repita a emissão com a conta Bradesco e confirme que o boleto sai como antes.

## Testes automatizados

Rodam no PHP do sistema (8.x), sem Docker e sem banco — são cálculo puro:

```bash
for t in tests/teste_*.php; do php "$t"; done
```

## Escrita de arquivos

A geração de remessa escreve em `remessa_arquivo/`, que é um bind mount do diretório do projeto. Se der erro de permissão:

```bash
chmod 777 remessa_arquivo
```

## Derrubando

```bash
docker compose down       # para os containers, mantém o banco
docker compose down -v    # para e apaga o banco também
```
