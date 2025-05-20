# Projeto Desafio SENAC

## Visão Geral

Este é um projeto web desenvolvido em PHP, concebido como parte de um desafio ou exercício do SENAC. O objetivo principal é a implementação de um sistema de gerenciamento de usuários, incluindo funcionalidades de autenticação, com dados armazenados em um arquivo JSON.

## Estrutura do Projeto

A estrutura de diretórios e arquivos do projeto é a seguinte:
Desafio_senac/
├── config.php
├── index.php
├── login.php
├── logout.php
├── registro.php
└── storage/
└── usuarios.json

*   `config.php`: Contém as configurações centrais do projeto, como definições de constantes (URL base, caminho para armazenamento) e funções utilitárias essenciais.
*   `index.php`: Página inicial da aplicação.
*   `login.php`: Página para autenticação de usuários existentes.
*   `logout.php`: Script para encerrar a sessão do usuário.
*   `painel.php`: Painel de controle do usuário, acessível após o login.
*   `registro.php`: Página para o cadastro de novos usuários.
*   `storage/usuarios.json`: Arquivo JSON utilizado para armazenar os dados dos usuários.

## Requisitos

*   Servidor web com suporte a PHP (Ex: Apache, Nginx).
*   PHP 7.0 ou superior.
*   Permissão de leitura e escrita para o servidor web no arquivo `storage/usuarios.json` e no diretório `storage/`.

## Funcionalidades Principais

*   **Registro de Usuários:** Permite que novos usuários criem uma conta.
*   **Autenticação de Usuários:**
    *   **Login:** Permite que usuários registrados acessem o sistema.
    *   **Logout:** Permite que usuários encerrem sua sessão de forma segura.
*   **Painel do Usuário:** Área restrita que exibe informações ou funcionalidades específicas para usuários autenticados.
*   **Gerenciamento de Sessão:** Utiliza sessões PHP para manter o estado do usuário.
*   **Persistência de Dados:** Armazena informações dos usuários em um arquivo JSON.

## Configuração Essencial (`config.php`)

O arquivo `config.php` é vital para o projeto e desempenha as seguintes funções:

*   **Inicialização de Sessão:** `session_start()` é invocado para habilitar o uso de sessões.
*   **Definição de Constantes:**
    *   `BASE_URL`: Define a URL raiz da aplicação (ex: `http://localhost/desafiosenac/Desafio_senac/` ou `http://localhost/projeto_web_oo` dependendo da sua configuração). **Ajuste conforme necessário.**
    *   `STORAGE_PATH`: Especifica o caminho absoluto para o arquivo `usuarios.json`.
*   **Funções Utilitárias:**
    *   `carregarUsuarios()`: Carrega os dados dos usuários a partir do `usuarios.json`. Cria o arquivo se ele não existir.
    *   `salvarUsuarios($usuarios)`: Grava os dados dos usuários no `usuarios.json`.
    *   `estaLogado()`: Verifica o status de login do usuário através da sessão.
    *   `redirect($url)`: Redireciona o usuário para uma URL especificada.

## Como Configurar e Executar

1.  **Ambiente:** Certifique-se de ter um servidor web com PHP instalado e configurado (XAMPP, WAMP, MAMP são boas opções para desenvolvimento local).
2.  **Arquivos do Projeto:** Copie a pasta `Desafio_senac` para o diretório raiz do seu servidor web (geralmente `htdocs` no XAMPP ou `www` no WAMP/MAMP).
3.  **Permissões:** Garanta que o servidor web tenha permissão de escrita no diretório `Desafio_senac/storage/` para que o arquivo `usuarios.json` possa ser criado e modificado.
4.  **Configurar `BASE_URL`:** Edite o arquivo `Desafio_senac/config.php` e ajuste a constante `BASE_URL` para refletir o endereço correto do projeto no seu servidor (ex: `http://localhost/nome_da_pasta_do_projeto/Desafio_senac/`).
5.  **Acesso:** Abra seu navegador e acesse a `BASE_URL` que você configurou.

---
Este README visa fornecer uma compreensão clara do projeto. Sinta-se à vontade para expandi-lo com mais detalhes conforme o projeto evolui!
