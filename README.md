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
│   ├── css/
│   ├── images/
│   └── js/
├── config/
├── database/
├── includes/
├── pages/
├── planejamento/
└── README.md
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

## DECLARAÇÃO DE USO DE INTELIGÊNCIA ARTIFICIAL

### ChatGPT
<ul>
  
  <li>
    <strong>Etapa:</strong> Artigo;
    <br>
    <strong>Finalidade:</strong> Utilizado na organização e estrturação do arquivo README.md;
    <br>
    <strong>Validação:</strong> Revisado pelos integrantes de PinguInvest com devidas alterações tomadas visando a identidade da marca.
  </li>
  <br>
  <li>
    <strong>Etapa:</strong> Desenvolvimento;
    <br>
    <strong>Finalidade:</strong> Sugestões sobre a estrutura e organização do banco de dados;
    <br>
    <strong>Validação:</strong> Revisão por parte dos integrantes com exclusão de partes vistas como desnecessárias e inclusão de outras não inclusas inicialmente.
  </li>
  <br>
  <li>
    <strong>Etapa:</strong> Desenvolvimento;
    <br>
    <strong>Finalidade:</strong> Auxílio com códigos PHP e JS;
    <br>
    <strong>Validação:</strong> Devidamente testado visando o objetivo que devia cumprir e usado de base para aprendizado e compreensão mais profunda sobre o código.
  </li>
  <br>
  <li>
    <strong>Etapa:</strong> Desenvolvimento;
    <br>
    <strong>Finalidade:</strong> Auxílio com estilização CSS;
    <br>
    <strong>Validação:</strong> Olhando no localhost e validando código usado para o cumprimento do que foi pedido.
  </li>
  
</ul>

### Claude
<ul>

  <li>
    <strong>Etapa:</strong> Desenvolvimento;
    <br>
    <strong>Finalidade:</strong> Auxílio com códigos PHP e JS;
    <br>
    <strong>Validação:</strong> Devidamente testado visando o objetivo que devia cumprir e usado de base para aprendizado e compreensão mais profunda sobre o código.
  </li>
  <br>
  <li>
    <strong>Etapa:</strong> Finalização;
    <br>
    <strong>Finalidade:</strong> Organização do código estruturalmente e com comentários;
    <br>
    <strong>Validação:</strong> Códigos mudados checados e testados para averiguar mudanças no site, comentários lidos para ver se apresentavam corretamente o que devia.
  </li>
  
</ul>

## GitHub Copilot
<ul>

  <li>
    <strong>Etapa:</strong> Desenvolvimento;
    <br>
    <strong>Finalidade:</strong> Sugestões e autocompletar código;
    <br>
    <strong>Validação:</strong> Códigos averiguados por meio de testes e compreensão do que foi feito.
  </li>
  
</ul>
