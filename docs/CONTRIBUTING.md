## Contributing

## Getting Started

1. [Clone](https://help.github.com/en/articles/cloning-a-repository) [metamorphosis](https://github.com/leroy-merlin-br/metamorphosis)

2. We've prepare a docker environment with all required dependencies for making the process more smoothie. Build
   the image:

```bash 
$ docker-compose build
```

3. We follow PHP Standards Recommendations (PSRs) by [PHP Framework Interoperability Group](http://www.php-fig.org/). If you're not familiar with these standards, [familiarize yourself now](https://github.com/php-fig/fig-standards).


## Branches

To collaborate, create a new feature branch from the develop branch.
Use objective names for it. If it's hard to name the branch,
it could be a sign that it does a lot of things.
Evaluate the possibility of breaking your contribution into more objective branches.

**Examples**: `feat/add-payment-method` `fix/update-loyalty-acceptance-tests`

Some examples of prefixes we can use: `feat`, `fix`, `ref`, `doc`, `chore`

## Commits

We use [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) as a guide for commit messages.

Try to let your commit do only one thing in the code, while not breaking if it is rolled back.

Ex: Imagine that you are going to change the return of a method that was `string`, but now you can also return `null`. It's interesting that at the same time you change the method signature, you also fix the test related to this change.
That way, should you need to roll back that commit, you'll have confidence that the tests will continue to pass and nothing is affected.



## Description and PR comments by the author

To make easier the review of a *Pull Request*, it's interesting to write a description.

This description can explain why the change is being made and also help to understand the choices made during implementation.

If the author realizes that a certain piece of code may generate doubts, he can write a comment on the code snippet, explaining the reasons that led to carrying out that implementation.

**Source**: https://smartbear.com/learn/code-review/best-practices-for-peer-code-review/


## Tests

Tests belong in the /tests directory. There are two tests types: Unit and Integration.

To run only unit tests:

```bash 
$ docker-compose run -rm php vendor/bin/phpunit tests/Unit
 ```

To run only integration tests:

```bash 
$ docker-compose run -rm php vendor/bin/phpunit tests/Integration
 ```

To run all tests:

 ```bash 
$ docker-compose run -rm php vendor/bin/phpunit
 ```
