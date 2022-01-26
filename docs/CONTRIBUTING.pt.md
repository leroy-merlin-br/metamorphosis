## Contribuindo

## Começando

1. Faça um [clone](https://help.github.com/en/articles/cloning-a-repository) do [metamorphosis](https://github.com/leroy-merlin-br/metamorphosis)

2. Preparamos um ambiente docker com todas as dependências necessárias para tornar o processo mais suave. Compile
   a imagem e inicie os contêineres:

```bash
$ docker-compose build
```

3. Seguimos as recomendações de Padrões do PHP (PSRs) do [PHP Framework Interoperability Group](http://www.php-fig.org/). Se você não estiver familiarizado com esses padrões, [familiarize-se agora](https://github.com/php-fig/fig-standards).


## Branches

Para colaborar, crie um branch a partir do branch develop.
Use nomes objetivos para o branch. Se for difícil nomear um branch,
avalie a possibilidade de dividir a contribuição em branches mais objetivos.

**Exemplos**: `feat/add-payment-method` `fix/update-loyalty-acceptance-tests`

Alguns exemplos de prefixos que podemos usar: `feat`, `fix`, `ref`, `doc`, `chore`

## Commits

Usamos [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) como um guia para mensagens de commit.

Seu commit deve fazer apenas uma coisa no código, sem quebrar caso ele seja revertido.

Ex: Imagine que você vai alterar o retorno de um método que era `string`,
mas agora pode retornar `null`.
É interessante alterar a assinatura do método e o teste relacionado a esse metodo,
para garantir a integridade da funcionalidade. Dessa forma, caso seja necessário reverter esse commit,
os testes continuarão a passar a nada foi afetado.


## Descrição e comentários de PR do autor

Para facilitar a revisão de um *Pull Request*, é interessante escrever uma descrição.

Essa descrição deve explicar porque a mudança foi feita e ajuda a entender as escolhas feitas durante a implementação.

Caso o autor perceba que um trecho de código pode gerar dúvidas,
ele pode escrever um comentário sobre o trecho de código,
explicando os motivos que o levaram a realizar aquela implementação.

**Fonte**: https://smartbear.com/learn/code-review/best-practices-for-peer-code-review/


## Testes

Os testes pertencem ao diretório /tests. Existem dois tipos de testes: Unitário e Integração.

Para executar apenas testes de unidade:

```bash
$ docker-compose run --rm php vendor/bin/phpunit tests/Unit
 ```

Para executar apenas testes de integração:

```bash
$ docker-compose run --rm php vendor/bin/phpunit tests/Integration
 ```

Para executar todos os testes:

 ```bash
$ docker-compose run --rm php vendor/bin/phpunit
 ``` 

