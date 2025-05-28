<<<<<<< HEAD
# Projeto Sistema de Autenticação Full Stack (Login e Cadastro)

Este projeto é um sistema completo de autenticação (login e cadastro de usuários) seguindo a arquitetura MVC. Ele foi desenvolvido com foco na prática de integração entre frontend e backend, incluindo segurança de senhas e sistema de logs.

## Tecnologias utilizadas

-   **Frontend**: React (com `react-router-dom` para navegação entre telas)
-   **Backend**: PHP (Controller e Model)
-   **Banco de dados**: PostgreSQL
-   **Servidor local**: XAMPP (Apache para PHP)

## Funcionalidades Atuais

-   **Cadastro de Usuários**:
    * Formulário intuitivo para registro de novos usuários.
    * Validação de e-mail e senha.
    * Armazenamento seguro de senhas via `password_hash()` (BCRYPT).
    * Verificação de e-mail duplicado antes do cadastro.
-   **Login de Usuários**:
    * Autenticação de usuários existentes.
    * Verificação segura de senhas via `password_verify()`.
    * Redirecionamento para a tela de Dashboard após login bem-sucedido.
-   **Sistema de Logs**:
    * Registro de eventos importantes (sucesso de login/cadastro, falhas, erros) no banco de dados (`logs`).
    * Mecanismo de fallback para arquivo de log (`application_errors.log`) em caso de falha na conexão com o DB.
-   **Navegação por Rotas**:
    * Utiliza `react-router-dom` para gerenciar as rotas `/` (Login), `/register` (Cadastro) e `/dashboard` (Página de Sucesso).
-   **Estilização Profissional**:
    * Interface de usuário com design limpo e moderno.

## Como Rodar o Projeto (Configuração e Implantação)

Siga estas etapas para colocar o projeto em funcionamento no seu ambiente local:

### 1. Configurar o Ambiente de Desenvolvimento

Certifique-se de ter as seguintes ferramentas instaladas:

* **Node.js e npm**: Baixe a versão LTS em [nodejs.org](https://nodejs.org/).
* **XAMPP**: Baixe em [apachefriends.org](https://www.apachefriends.org/index.html/). Inicie o Apache após a instalação.
* **PostgreSQL**: Baixe em [postgresql.org/download/](https://www.postgresql.org/download/). Certifique-se de instalar o pgAdmin junto.

### 2. Preparar o Banco de Dados PostgreSQL

1.  **Abra o pgAdmin**.
2.  **Conecte-se ao seu servidor PostgreSQL**.
3.  **Crie o banco de dados `login`**:
    * Clique com o botão direito em "Databases" -> "Create" -> "Database...".
    * No campo "Database", digite `login`. Clique em "Save".
4.  **Crie as tabelas `usuarios` e `logs`**:
    * Expanda o banco de dados `login` -> "Schemas" -> "public" -> "Tables".
    * Clique com o botão direito em "Tables" e selecione "Query Tool".
    * Execute os seguintes comandos SQL:

    ```sql
    -- Tabela de Usuários
    CREATE TABLE usuarios (
        id SERIAL PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    -- Tabela de Logs (para o sistema de logs do backend)
    CREATE TABLE IF NOT EXISTS logs (
        id SERIAL PRIMARY KEY,
        level VARCHAR(10) NOT NULL,
        message TEXT NOT NULL,
        context JSONB,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ```

### 3. Organizar os Arquivos do Projeto

1.  **Baixe o projeto** do GitHub (como um ZIP ou via `git clone`).
2.  **Descompacte** o projeto.
3.  **Mova a pasta descompactada** (ex: `loginmvc`) para dentro do diretório `htdocs` do XAMPP:
    * Windows: `C:\xampp\htdocs\`
    * macOS: `/Applications/XAMPP/htdocs/`
    * A estrutura deve ser `C:\xampp\htdocs\loginmvc\backend` e `C:\xampp\htdocs\loginmvc\frontend`.

### 4. Configurar o Backend (PHP)

1.  **Inicie o Apache** no Painel de Controle do XAMPP.
2.  **Habilite a extensão PDO PostgreSQL no `php.ini`**:
    * Abra `C:\xampp\php\php.ini`.
    * Procure por `;extension=pdo_pgsql` e **remova o ponto e vírgula** no início.
    * **Salve e reinicie o Apache.**
3.  **Ajuste a conexão com o banco de dados**:
    * Abra o arquivo `backend/config/Database.php`.
    * Verifique se as credenciais e o nome do banco de dados estão corretos:
        ```php
        private $db_name = "login"; // Certifique-se que é 'login'
        private $username = "postgres";
        private $password = "santosfc123"; // Sua senha do PostgreSQL
        ```
4.  **Configure o roteamento (URL Rewriting) com `.htaccess`**:
    * **Confirme `AllowOverride All` no Apache**: Abra `C:\xampp\apache\conf\httpd.conf`. Procure pelo bloco `<Directory "C:/xampp/htdocs">` e certifique-se de que `AllowOverride All` esteja definido. **Salve e reinicie o Apache.**
    * Confirme que o arquivo `.htaccess` está presente na pasta `backend/controller/` com o seguinte conteúdo:
        ```apache
        # backend/controller/.htaccess
        <IfModule mod_rewrite.c>
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteRule ^(.*)$ UserController.php [QSA,L]
        </IfModule>
        ```
5.  **Crie a pasta de logs de fallback**:
    * Crie a pasta `backend/logs` na raiz do seu backend (`C:\xampp\htdocs\loginmvc\backend\logs`). Certifique-se de que o Apache tenha permissão de escrita nessa pasta.

### 5. Configurar e Iniciar o Frontend (React)

1.  **Abra o terminal** e navegue até a pasta `frontend` do seu projeto:
    ```bash
    cd C:\xampp\htdocs\loginmvc\frontend
    ```
2.  **Instale as dependências do React**:
    ```bash
    npm install
    ```
3.  **Verifique a URL da API no `Register.js` e `Login.js`**:
    * Abra `frontend/src/Register.js` e confirme que a `fetch` aponta para:
        ```javascript
        "http://localhost/loginmvc/backend/controller/UserController.php"
        ```
    * Abra `frontend/src/Login.js` e confirme que a `fetch` aponta para:
        ```javascript
        "http://localhost/loginmvc/backend/controller/UserController.php/login"
        ```
4.  **Inicie o aplicativo React**:
    ```bash
    npm start
    ```
    Isso abrirá automaticamente `http://localhost:3000` no seu navegador.

### 6. Testar o Projeto Completo

1.  Acesse `http://localhost:3000` no seu navegador.
2.  **Teste o Cadastro**:
    * Clique em "Cadastre-se".
    * Preencha e-mail e senha e clique em "Cadastrar".
    * Verifique a mensagem de sucesso e o redirecionamento para o login.
    * Tente cadastrar o mesmo e-mail novamente e verifique a mensagem "Este e-mail já está cadastrado.".
3.  **Teste o Login**:
    * Use um e-mail e senha que você cadastrou.
    * Verifique se o login é bem-sucedido e você é redirecionado para a tela "Parabéns você acessou!".
    * Tente fazer login com e-mail/senha incorretos e veja a mensagem "E-mail ou senha inválidos.".
4.  **Verifique os Logs**:
    * No pgAdmin, execute `SELECT * FROM logs;` no banco `login` para ver os registros de INFO, WARNING e ERROR.
    * Verifique o arquivo `backend/logs/application_errors.log` se o log de DB falhar por algum motivo.

---
=======
# Projeto Tela de Cadastro - Full Stack

Este projeto é um sistema simples de cadastro de usuário usando arquitetura MVC.

## Tecnologias utilizadas

- **Frontend**: React
- **Backend**: PHP (Controller e Model)
- **Banco de dados**: PostgreSQL
- **Servidor local**: XAMPP

## Funcionalidade atual

- Tela de cadastro
- Dados são salvos no banco de dados PostgreSQL
- Backend recebe requisição do React e insere no banco via PDO

## Como rodar o projeto

1. Instale Node.js, PHP, PostgreSQL e XAMPP
2. Coloque o projeto dentro da pasta `htdocs` do XAMPP
3. Crie o banco `loginmvc` e a tabela `usuarios`
4. Ajuste a conexão com o banco no arquivo: `backend/config/db.php`
5. No terminal, vá até `frontend/` e rode:

```bash
npm install
npm start
# login
# login
# login
# login
>>>>>>> 6a1e99a490e7a70324a1eb194a411ddde497eaa0
