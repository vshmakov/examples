Examples
===============

This web application created to study children to solve arithmetic examples.

- The examples are generated randomly.
- There are settings profiles, which control generation of examples.
- Solved examples are merged into the attempts.
- You can see all your attempts, examples, count of errors and much-much more.
- You can create your own settings profiles, appoint a teacher, do your homework and etcetera.

System requirements
----------------

- PHP 7.2
- Composer
- node.js
- npm
- MySQL

Installation
----------------

- git clone https://github.com/shm-vadim/examples
- cd examples
- git checkout develop
- composer install --no-scripts
- Create .env.local and set there your own DATABASE_URL like in .env
- bin/console doctrine:database:create && bin/console doctrine:migrations:migrate -n && bin/console doctrine:fixtures:load -n
- composer run-script auto-scripts
- npm install
- npm run dev
- bin/console server:run
- Open http://localhost:8000 in yor browser.

Enjoy!