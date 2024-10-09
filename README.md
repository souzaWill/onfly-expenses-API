<p align="center"><a href="hhttps://www.onfly.com.br" target="_blank"><img src="https://www.onfly.com.br/wp-content/uploads/2024/07/onfly-logo-azul-01-768x307-1.webp" width="600" alt="onfly"></a></p>

# Getting Started

* para buildar o projeto e necessario ter o docker e o docker-composer instalado 
    - para inciar o projeto utilize o script start.sh
    - as variaveis de ambiente padrao do sistema estao no arquivo .env.example

* Esse projeto usa o ecosistema laravel
    - docker esta configurado com laravel sail
    - os testes estao utiilizando php pest, com isso e possivel verificar o coverage, type coverage e testes de arquitetura
    - o email esta utilizando o mailpit, com isso caso queira testar emails, utilize a porta http://localhost:8025/
    - o banco de dados escolhido foi o sqlite, porem para utizar outro banco, basta adiciona-lo ao sail e alterar o env
    - a documentacao foi gerada com scribe para visualiza-la http://localhost/docs
    - a base url da api e http://localhost/api

* para executar os testes utilize o script run-tests.sh

## portas usadas
 - api: 80
 - mailpit: 8025|1025



