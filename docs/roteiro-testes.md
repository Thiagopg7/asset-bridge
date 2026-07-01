# Roteiro de Testes — Ajustes pós-Fase 5

Roteiro manual para validar os ajustes levantados após a Fase 5 (Grupos A, B e C).

## Pré-requisitos

```bash
docker exec asset-bridge-app-1 php artisan migrate --force
docker exec asset-bridge-app-1 php artisan db:seed --class=RolePermissionSeeder --force
npm run dev   # ou npm run build
```

**Usuários necessários** (crie via painel de Usuários, como admin):

| Filial       | Cargos a criar                        |
| ------------ | ------------------------------------- |
| **Filial A** | 1 gerente, 1 colaborador, 1 logística |
| **Filial B** | 1 gerente, 1 colaborador, 1 logística |
| (global)     | 1 admin                               |

---

## Bloco 1 — Navegação e permissões

### #3 — Esconder Filiais do colaborador + menu Estoque

1. Entre como **colaborador (Filial A)**.
2. Esperado: a sidebar **não** mostra "Filiais"; mostra um menu **"Estoque"**.
3. Clique em **Estoque** → deve abrir o estoque **da Filial A** (a dele), sem passar por lista de filiais.
4. Acesse `/branches` na URL direto → deve ser bloqueado (403 → **toast** em navegação interna).
5. Entre como **gerente/admin** → "Filiais" continua aparecendo normalmente.

### #11 — Label "Logística"

1. Como admin, vá em **Usuários** e em **Cargos**.
2. Esperado: o cargo aparece como **"Logística"** (L maiúsculo, com acento), não "logistica".

---

## Bloco 2 — Ativos

### #4 — Nota explicativa no catálogo

1. Abra a tela **Ativos** (qualquer perfil com acesso).
2. Esperado: um **ícone de informação (ⓘ)** ao lado do título; ao passar o mouse, aparece o tooltip explicando que a lista reúne os ativos elegíveis para transferência e orientando a falar com o gerente para incluir novos itens.

---

## Bloco 3 — Solicitações internas

### #7 — Bloquear necessidade com estoque

1. Como **colaborador (Filial A)**, garanta que a Filial A tem estoque > 0 de um ativo X (via Estoque).
2. Crie uma **Solicitação → Necessidade** do ativo X.
3. Esperado: **bloqueado**, com aviso de que o ativo parece existir no estoque da filial e que se deve verificar com o gerente.
4. Repita com um ativo Y **sem estoque** na filial → deve **permitir** criar.

### #1 — Autor editar solicitação pendente

1. Como colaborador, crie uma solicitação (fica **Pendente**).
2. Na lista, deve aparecer o **botão de editar (lápis)** na linha dela.
3. Edite quantidade/observações e salve → alteração persistida.
4. Peça ao **gerente** para **aprovar** essa solicitação; volte como colaborador → o **botão de editar some** (não edita mais após revisada).
5. Tente editar uma solicitação de **outro colaborador** → sem botão / bloqueado.

### #2 — Gerente ver detalhes + aprovar dentro e fora

1. Como **gerente (Filial A)**, abra **Solicitações**.
2. Clique no **botão de detalhes (olho)** na linha de uma solicitação pendente → abre a **tela de detalhes** (dados completos, solicitante, observações).
3. Aprove/rejeite **dentro** da tela de detalhes.
4. Volte à lista → confirme que também dá pra aprovar/rejeitar **direto na lista** (botões Aprovar / Rejeitar).
5. Tente abrir detalhes de solicitação de **outra filial** → bloqueado (403 → toast).

---

## Bloco 4 — Marketplace e Transferências

### #6 — Necessidades no marketplace

1. Como **colaborador (Filial B)**, abra **Marketplace**.
2. Esperado: duas seções — **"Ofertas de excesso disponíveis"** (com botão Solicitar) e **"Necessidades de outras filiais"** (somente leitura).
3. Crie uma **necessidade** aprovada na Filial A → ela deve aparecer na seção de necessidades para a Filial B (e **não** para a própria Filial A).

> Observação: as necessidades são **somente leitura** — o fluxo de transferência é sempre feito contra uma **oferta de excesso**. Para testar o botão **Solicitar**, é preciso ter uma oferta de excesso aprovada e **com saldo disponível** de outra filial.

### #8 — Campo de quantidade sem "0" preso

1. No Marketplace, clique **Solicitar** numa oferta.
2. No campo **Quantidade**, apague o valor.
3. Esperado: o campo fica **vazio** (não mostra "0" preso na frente); ao digitar "5", vira "5" (não "05").

### #10 — Bloquear pedido duplicado + #9 concorrência

1. Como colaborador (Filial B), solicite transferência de um ativo de uma oferta da Filial A.
2. Tente solicitar **de novo** o mesmo ativo enquanto o 1º está pendente → **bloqueado**, avisando que a filial já registrou um pedido para esse ativo.
3. (#9) Como colaborador de **outra filial**, solicite a **mesma oferta** → deve **permitir** (filiais concorrem); a filial de origem escolhe qual autorizar.

---

## Bloco 5 — Expedição e estoque

### #12 — Recebimento reduz só a origem

1. Fluxo completo: colaborador A cria **excesso** → gerente A aprova → colaborador B solicita no Marketplace → gerente B autoriza (gera expedição "Pronto para envio").
2. Como **logística (Filial A)** → **Expedição** → **Enviar** (status "Em trânsito").
3. Como **logística (Filial B)** → **Confirmar recebimento**.
4. Esperado: o estoque da **Filial A (origem) reduz** na quantidade transferida; o estoque da **Filial B (destino) NÃO é creditado** (não vira sobra falsa).

### #5 — 403 vira toast (transversal)

- Em qualquer ação sem permissão feita pela interface → aparece um **toast de erro** "Você não tem permissão…", sem cair na tela cheia de erro 403.
