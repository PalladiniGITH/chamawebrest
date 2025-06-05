# chamawebrest

Este projeto demonstra uma aplicação PHP dividida em microserviços. Cada
funcionalidade principal roda em seu próprio container Docker e todas as
requisições passam por um gateway Nginx.

Microserviços expostos:

- **login** – telas de autenticação (index, login, logout, reset)
- **dashboard** – listagem de chamados
- **criar** – abertura de chamados
- **admin** – painel administrativo e relatórios
- **chamados**, **tickets**, **actions**, **stats** – APIs REST de suporte
