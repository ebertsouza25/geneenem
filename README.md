# GeneEnem — Genética para o ENEM

## Integrantes

- Kaio Dantas
- Ebert Souza
- Alexandre Brito
- Guilherme Emanuel
- Moises Medeiros

**Professor:** Felipe Ramos

## Tema

Plataforma de estudo de Genética para o ENEM: revisões curtas, videoaulas, quadro de
Punnett, laboratório com DNA interativo, heredograma, flashcards e um banco com 10
questões de provas anteriores com correção comentada.

O projeto também tem:

- **GeneIA**, assistente virtual de Genética com botão flutuante nas páginas internas.
- **Login e perfil do aluno**, com simulados, acertos, erros, pontuação e histórico
  gravados no MySQL.

## Endereço público

(https://geneenem-wasmer-app-deployments.wasmer.app/)

## Como rodar localmente (XAMPP)

1. Copie a pasta `geneenem` para `C:\xampp\htdocs\`.
2. Inicie o **Apache** e o **MySQL** no painel do XAMPP.
3. Abra o phpMyAdmin, crie um banco chamado `geneenem` e importe
   `banco_geneenem.sql` dentro dele.
4. As credenciais locais ficam em `config.local.php` (fora do Git e do deploy).
   Se o arquivo não existir, copie `config.local.example.php` e preencha:
   `localhost` / `root` / senha vazia / `geneenem` / porta `3307`.
5. Acesse `http://localhost/geneenem/login.php` e crie a primeira conta.
6. Para ativar a GeneIA, crie `ia/.env` a partir de `ia/.env.example` e preencha a
   chave (veja abaixo).

Requisitos: PHP 8 ou superior com as extensões `mysqli` e `curl`, e MySQL/MariaDB.
