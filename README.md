# 🐧 PinguInvest

<p align="center">
  <strong>Invista melhor. Aprenda mais. Controle tudo.</strong>
</p>

---

## 📖 Sobre o Projeto

O PinguInvest é uma plataforma web desenvolvida para auxiliar usuários no gerenciamento de investimentos e na educação financeira.

A proposta do sistema é centralizar informações financeiras em um único ambiente, permitindo o acompanhamento da carteira de investimentos, análise patrimonial e acesso a conteúdos educativos relacionados ao mercado financeiro.

<i align="center;">Durante o desenvolvimento do projeto foram utilizadas ferramentas de Inteligência Artificial como apoio à revisão de código e à comparação de possíveis soluções. As respostas obtidas foram analisadas, verificadas e testadas pelo aluno. As ferramentas utilizadas, suas finalidades, etapas de utilização e formas de validação encontram-se detalhadas na DECLARAÇÃO DE USO DE INTELIGÊNCIA ARTIFICIAL, apresentada ao final desta atividade.</i>

---

## 🎯 Objetivo

Desenvolver uma plataforma moderna e intuitiva capaz de auxiliar investidores iniciantes e experientes no controle de seus investimentos e na tomada de decisões financeiras.

---

## 👥 Público-Alvo

- Investidores iniciantes;
- Estudantes de educação financeira;
- Usuários interessados em organização patrimonial;
- Pessoas que desejam acompanhar seus investimentos de forma simples.

---

## 💡 Diferenciais

- Controle centralizado de investimentos;
- Dashboard financeiro intuitivo;
- Histórico de movimentações;
- Educação financeira integrada;
- Interface moderna inspirada em fintechs.

---

## ✨ Funcionalidades

### 👤 Usuários
- Cadastro de contas;
- Login seguro;
- Gerenciamento de perfil.

### 💰 Carteira de Investimentos
- Cadastro de ativos;
- Controle patrimonial.

### 📈 Dashboard
- Patrimônio total;
- Valor investido;
- Rentabilidade da carteira;
- Indicadores financeiros.

### 📚 Educação Financeira
- Artigos;
- Vídeo-aulas;
- Conteúdo educativo.

---

## 💵 Tipos de Investimentos

- Ações
- Fundos Imobiliários (FIIs)
- Renda Fixa
- ETFs
- Criptomoedas

---

## 🚀 Tecnologias Utilizadas

### Front-End
- HTML5
- CSS3
- JavaScript

### Back-End
- PHP
- PDO

### Banco de Dados
- MySQL

### Ferramentas
- Git
- GitHub
- Visual Studio Code

---

## 📂 Estrutura do Projeto

```text
PinguInvest/
├── assets/
│   ├── css/             # Estilos e aparência da interface
│   ├── images/          # Imagens, ícones e identidade visual
│   └── js/              # Scripts e interações da aplicação
│
├── config/              # Configurações, conexões e integrações
├── database/            # Scripts e estrutura do banco de dados
├── includes/            # Componentes e funções PHP reutilizáveis
├── pages/               # Páginas e funcionalidades do sistema
├── planejamento/        # Documentação e planejamento do projeto
└── README.md            # Documentação principal
```

---

## 👨‍💻 Equipe

- Bruno Lourenço de Lima
- Henrique Silvestre Martin
- Isaac Faleiros Quevedo

Projeto desenvolvido para fins acadêmicos no curso de Análise e Desenvolvimento de Sistemas.

---

## 📈 Status

🚧 Em desenvolvimento.

---

## 📊 Integração de Mercado — HG Brasil + brapi.dev

A página `pages/mercado.php` usa duas fontes no backend PHP. Nenhuma chave é enviada ao navegador.

### HG Brasil

Responsável por índices, moedas, Bitcoin, CDI e Selic. O backend usa **uma única chamada** ao endpoint `/finance` e mantém o resultado em cache por 30 minutos.

Configure `config/hgbrasil.local.php`:

```php
<?php
return [
    'api_key' => 'SUA_CHAVE_HG_AQUI',
];
```

Em produção, prefira a variável de ambiente `HGBRASIL_API_KEY`.

### brapi.dev

Responsável por ações, FIIs e ETFs da B3. O backend prioriza o endpoint `/api/quote/list`, que já retorna vários ativos e evita fazer uma requisição para cada ticker.

Configure `config/brapi.local.php`:

```php
<?php
return [
    'api_key' => 'SEU_TOKEN_BRAPI_AQUI',
];
```

Em produção, prefira a variável de ambiente `BRAPI_API_KEY`.

Sem token da brapi, o sistema tenta a listagem pública e, se houver exigência de autenticação, usa como fallback os quatro tickers oficiais de sandbox (`PETR4`, `VALE3`, `ITUB4` e `MGLU3`) para não derrubar a página.

Os dados da brapi ficam em cache por 15 minutos. Se qualquer uma das APIs estiver temporariamente indisponível, o último cache salvo continua sendo exibido quando existir.

> Não versione `config/hgbrasil.local.php` nem `config/brapi.local.php`. Chaves de API devem ser tratadas como segredo.

---

## 🤖 DECLARAÇÃO DE USO DE INTELIGÊNCIA ARTIFICIAL

Durante o desenvolvimento do **PinguInvest**, foram utilizadas ferramentas de Inteligência Artificial generativa como recursos de apoio ao planejamento, à programação, à produção de elementos visuais e à documentação do projeto.

As sugestões e os materiais produzidos com auxílio dessas ferramentas foram submetidos à análise dos integrantes, com adaptações, revisões e testes conforme sua finalidade.

A tabela a seguir apresenta as ferramentas utilizadas, suas respectivas aplicações e os procedimentos de validação adotados pelo grupo.

| Ferramenta | Etapa | Finalidade | Validação |
|---|---|---|---|
| **Gemini** | Planejamento | Auxílio na criação de imagens, logotipos e elementos da identidade visual do projeto. | Os materiais gerados foram analisados e editados para corrigir inconsistências e adequá-los à identidade visual do PinguInvest. |
| **ChatGPT** | Documentação | Auxílio na organização e estruturação do arquivo `README.md`. | O conteúdo foi revisado pelos integrantes, com correções e adaptações para representar adequadamente o projeto. |
| **ChatGPT** | Desenvolvimento | Sugestões para a modelagem, estruturação e organização do banco de dados. | As propostas foram analisadas pelo grupo, que removeu elementos desnecessários e acrescentou estruturas conforme as necessidades do sistema. |
| **ChatGPT** | Desenvolvimento | Auxílio na elaboração, compreensão e correção de códigos PHP e JavaScript. | As soluções foram analisadas e testadas no ambiente de desenvolvimento, sendo utilizadas também para aprofundar a compreensão do código. |
| **ChatGPT** | Desenvolvimento | Sugestões de estilização CSS e melhorias na apresentação visual da plataforma. | As alterações foram verificadas no ambiente local (`localhost`), com análise dos resultados visuais e ajustes quando necessários. |
| **Claude** | Desenvolvimento | Auxílio na elaboração, revisão e correção de códigos PHP e JavaScript. | Os códigos foram analisados, testados e adaptados conforme as necessidades da aplicação, buscando compreender seu funcionamento antes da integração ao projeto. |
| **Claude** | Organização do código | Auxílio na reorganização estrutural dos arquivos e na elaboração de comentários explicativos. | As modificações foram revisadas e testadas para verificar possíveis impactos no funcionamento do sistema. Os comentários foram analisados quanto à clareza e à correspondência com o código. |
| **GitHub Copilot** | Desenvolvimento | Sugestões de código e preenchimento automático durante a programação. | As sugestões utilizadas foram revisadas e verificadas por meio de testes, buscando garantir sua adequação às funcionalidades desenvolvidas. |
