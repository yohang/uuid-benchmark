UUID Benchmark
================

This repository contains a benchmark suite for evaluating the performance of different UUID types as Primary Key in 
PHP / PostgreSQL applications. The benchmark tests the insertion and retrieval speed of various UUID types, via Doctrine ORM.

Setup
-----

The install & run process is automated via a Makefile. You need to have `docker`, `docker compose` and `mkcert` 
installed on your machine.

```php
 $ make run # Build & start services
 $ make benchmark # Run the benchmark tests

```

Doctrine Usage and Optimizations
--------------------------------

This repository also contains some Symfony controllers to illustrate some bad and good practices with doctrine.
You can access them via a web browser at `https://localhost`.
