# Frase do Dia

Projeto completo para exibir uma **Frase do Dia** com:
- Fonte **local** (`data/quotes.json`)
- Seleção **determinística** (mesma frase durante o dia inteiro, por fuso)
- **Fallback** opcional para APIs públicas (Quotable, FavQs QOTD, ZenQuotes)
- Exemplo em **PHP puro** (endpoint JSON com cache diário)
- **Integração Laravel** (service + rota)
- Demos **front‑end** em **Vanilla JS** e **jQuery**

> Fuso configurado: `America/Sao_Paulo`. Você pode ajustar no código se quiser.

---

## Estrutura

```
frase-do-dia/
├─ data/
│  └─ quotes.json
├─ cache/
│  └─ .gitkeep
├─ src/
│  ├─ QuotePicker.php
│  ├─ QuoteSources.php
│  └─ helpers.php
├─ public/
│  ├─ index.html            # demo Vanilla JS
│  ├─ index-jquery.html     # demo jQuery
│  ├─ demo.php              # endpoint PHP com fallback + cache
│  └─ quotes.php            # serve o quotes.json para o front
├─ laravel/
│  ├─ QuoteService.php
│  └─ routes-example.php
├─ scripts/
│  └─ enrich_from_apis.php
├─ composer.json            # autoload opcional (PSR-4)
├─ LICENSE (MIT)
└─ .gitignore
```

---

## Como rodar (PHP puro)

1. **Clonar** este repositório
2. Subir um servidor embutido do PHP apontando para `public/`:
   ```bash
   php -S localhost:8080 -t public
   ```
3. Acessar:
   - **Frontend Vanilla JS**: `http://localhost:8080/index.html`
   - **Frontend jQuery**: `http://localhost:8080/index-jquery.html`
   - **Endpoint JSON (fallback + cache)**: `http://localhost:8080/demo.php`

> O endpoint `demo.php` tenta: Quotable → FavQs QOTD → ZenQuotes; se falhar, usa a **fonte local** (`data/quotes.json`). Um cache diário é gravado em `cache/qotd.json`.

### Seleção determinística (como funciona)
- A frase do dia é escolhida calculando um índice baseado em `YYYY-MM-DD` + um **SALT** (`v1-rotacao-anual`), e então aplicando um hash que mapeia para uma posição da lista.
- Isso garante **mesma frase** ao longo do dia, **sem banco** e **sem cron**.
- Alterar o `SALT` muda a ordem de rotação (útil para “embaralhar” o ano seguinte).

### Timezone
- O cálculo do “dia atual” respeita `America/Sao_Paulo` tanto em PHP quanto nos exemplos em JS.

### Cache
- `public/demo.php` grava `cache/qotd.json` com a frase resolvida para a data.
- Evita chamadas externas repetidas e garante uma resposta rápida/estável.

---

## Como usar no **Laravel**

1. Copie `src/` para o seu projeto (ou use este repo como submódulo) e **registre o autoload** (opcional via `composer.json` deste repo).
2. Coloque `data/quotes.json` na raiz do seu projeto Laravel.
3. Copie `laravel/QuoteService.php` para `app/Services/QuoteService.php`.
4. Adicione a rota do exemplo (arquivo `laravel/routes-example.php`) dentro do seu `routes/api.php`:

   ```php
   use Illuminate\\Support\\Facades\\Route;
   use App\\Services\\QuoteService;

   Route::get('/api/quote-of-the-day', function (QuoteService $svc) {
       $q = $svc->quoteOfTheDay();
       return response()->json([
           'date' => now('America/Sao_Paulo')->toDateString(),
           'quote' => $q['quote'],
           'author' => $q['author'] ?? null,
           'meta' => [
               'source' => $q['source'] ?? 'local',
               'tags' => $q['tags'] ?? [],
           ],
       ]);
   });
   ```

5. Pronto: `GET /api/quote-of-the-day` retorna JSON com a frase do dia.

> Se quiser usar **autoload PSR-4**, adicione no seu `composer.json`:
> ```json
> "autoload": { "psr-4": { "FraseDoDia\\\\": "src/" } }
> ```
> e rode `composer dump-autoload`.

---

## Demos Front‑end

### Vanilla JS (`public/index.html`)
- Tenta uma frase de API externa (Quotable). Se falhar (sem internet/CORS), carrega a lista local via `quotes.php` e escolhe a frase do dia em JS (determinístico).

### jQuery (`public/index-jquery.html`)
- Mesma lógica, mas usando jQuery 3.7.x.

> Por que `quotes.php` e não `/data/quotes.json` direto? Porque ao servir a pasta `public/` como docroot, arquivos fora dela não são acessíveis pelo navegador. `quotes.php` apenas lê e devolve o JSON local.

---

## Fallback para APIs públicas

- **Quotable**: `https://api.quotable.io/random` (open-source, CORS ok)
- **FavQs (QOTD)**: `https://favqs.com/api/qotd`
- **ZenQuotes**: `https://zenquotes.io/api/random`

A ordem de tentativa e o cache diário estão em `public/demo.php`.  
Você também pode rodar `scripts/enrich_from_apis.php` para **enriquecer** seu `data/quotes.json` com novas frases (deduplicando por `quote+author`).

> **Atenção**: verifique licenças/atribuições das fontes e traduções de citações. Este repositório inclui um conjunto **exemplificativo** de frases. Recomenda-se curar suas próprias frases e garantir conformidade de direitos autorais, principalmente para traduções.

---

## Trocar o SALT / Fuso

- Mude o `SALT` (string) em:
  - `src/QuotePicker.php` (quando usado via PHP/Laravel)
  - `public/index.html` e `public/index-jquery.html` (variáveis JS)
- Mude o fuso (`America/Sao_Paulo`) nos mesmos arquivos caso necessário.

---

## Teste rápido via terminal (PHP)

```bash
php -S localhost:8080 -t public
curl -s http://localhost:8080/demo.php | jq
```

---

## Contribuições

- Envie PRs com frases novas editando `data/quotes.json`.
- Formato:
  ```json
  { "id": "autor-xyz", "quote": "texto", "author": "Autor", "lang": "pt", "tags": ["tema"], "source": "local" }
  ```

---

## Licença

[MIT](./LICENSE)


---

## Tradução automática (opcional, pt-BR)

Se a frase vier de APIs em inglês, você pode habilitar tradução automática para **pt-BR** usando **LibreTranslate** (público ou self-host).

1. Defina as variáveis de ambiente antes de rodar o PHP:
   ```bash
   export LT_URL="https://libretranslate.com/translate"   # ou sua instância self-host
   export LT_API_KEY=""                                    # se necessário
   php -S localhost:8080 -t public
   ```
2. O `public/demo.php` irá tentar traduzir quando `lang != 'pt'`. A tradução é **opcional** (se `LT_URL` não estiver setado, mantém o texto original).

> Você pode substituir por qualquer serviço de tradução que exponha um endpoint HTTP; basta editar `src/Translator.php`.
